# 🚀 Guia Oficial de Deploy com Docker e PostgreSQL na VPS Hostinger (Siscoop3F)

Este guia prático e detalhado contém o passo a passo completo para configurar, containerizar e implantar a aplicação **Siscoop3F** em um servidor **VPS Hostinger** com sistema operacional Ubuntu 22.04 LTS / 24.04 LTS, utilizando **Docker**, **PostgreSQL** e **Nginx** com SSL Wildcard via Let's Encrypt.

---

## 📋 Pré-requisitos
1. Servidor **VPS Hostinger** ativo (IP: `72.60.248.191`).
2. Domínio **`siscoop3f.com.br`** registrado no Registro.br com DNS delegado na Hostgator.
3. Configuração dos Registros **Tipo A** no cPanel Hostgator (Zone Editor):

| Nome do Registro (Name) | Tipo (Type) | Destino (Record / IP) | Função |
| :--- | :---: | :--- | :--- |
| `siscoop3f.com.br.` | **A** | `72.60.248.191` | Domínio Principal (Master Admin / Landing Page) |
| `www.siscoop3f.com.br.` | **A** | `72.60.248.191` | Subdomínio WWW |
| `*.siscoop3f.com.br.` | **A** | `72.60.248.191` | Wildcard para todos os Tenants (`santacasa`, `stfsaude`, etc.) |

> ⚠️ **Importante (Serviço de E-mail Titan/Hostgator)**: Os registros de e-mail (MX `mx1.titan.email`, TXT SPF e DKIM `titan1._domainkey`) **NÃO DEVEM SER ALTERADOS**, garantindo que seus e-mails continuem funcionando perfeitamente!

---

## 🛠️ PASSO 1: Acesso Inicial e Atualização do Servidor

1. Abra seu terminal (PowerShell / Bash) e conecte via SSH:
   ```bash
   ssh root@IP_DA_SUA_VPS
   ```

2. Atualize os pacotes do sistema operacional:
   ```bash
   apt update && apt upgrade -y
   ```

3. Configure o fuso horário para o Brasil (América/São_Paulo):
   ```bash
   timedatectl set-timezone America/Sao_Paulo
   ```

---

## 🐳 PASSO 2: Instalação do Docker e Docker Compose

1. Remova possíveis versões antigas do Docker:
   ```bash
   apt remove -y docker docker-engine docker.io containerd runc
   ```

2. Instale as dependências de transporte HTTPS e ferramentas essenciais:
   ```bash
   apt install -y ca-certificates curl gnupg lsb-release git ufw nano unzip
   ```

3. Instale o Docker Engine oficial:
   ```bash
   curl -fsSL https://get.docker.com -o get-docker.sh
   sh get-docker.sh
   ```

4. Verifique a instalação do Docker e Docker Compose:
   ```bash
   docker --version
   docker compose version
   ```

5. Habilite o serviço do Docker para iniciar automaticamente no boot:
   ```bash
   systemctl enable docker
   systemctl start docker
   ```

---

## 🛡️ PASSO 3: Configuração do Firewall (UFW)

Liberar as portas necessárias para SSH, HTTP e HTTPS:
```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

---

## 📁 PASSO 4: Clonar o Repositório e Configurar o `.env`

1. Crie o diretório da aplicação e clone o repositório:
   ```bash
   mkdir -p /var/www
   cd /var/www
   git clone https://github.com/SEU_USUARIO/Siscoop3F.git siscoop3f
   cd /var/www/siscoop3f
   ```

2. Crie o arquivo `.env` de produção a partir do `.env.example`:
   ```bash
   cp .env.example .env
   nano .env
   ```

3. Configure o arquivo `.env` para produção e PostgreSQL:
   ```env
   APP_NAME="Siscoop3F"
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_URL=https://seu-dominio.com.br
   CENTRAL_DOMAIN=seu-dominio.com.br

   LOG_CHANNEL=daily
   LOG_LEVEL=error

   # Conexão PostgreSQL (Docker)
   DB_CONNECTION=pgsql
   DB_HOST=db
   DB_PORT=5432
   DB_DATABASE=siscoop3f
   DB_USERNAME=postgres
   DB_PASSWORD=SuaSenhaSuperSeguraPostgres2026!

   BROADCAST_DRIVER=log
   CACHE_DRIVER=file
   FILESYSTEM_DISK=public
   QUEUE_CONNECTION=database
   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   ```

---

## ⚙️ PASSO 5: Arquivos Docker do Projeto

Certifique-se de que os seguintes arquivos existem no projeto:

### `Dockerfile`:
```dockerfile
FROM php:8.3-fpm-alpine

# Dependências do sistema e utilitários de build
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    icu-dev

# Extensões PHP essenciais (PDO PostgreSQL, GD, BCMath, Zip, Intl, OpCache)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd zip bcmath intl opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Permissões do Storage e Bootstrap Cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### `docker-compose.yml`:
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: siscoop_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - .:/var/www
    networks:
      - siscoop_net
    depends_on:
      - db

  web:
    image: nginx:alpine
    container_name: siscoop_web
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./docker/certbot/conf:/etc/letsencrypt
      - ./docker/certbot/www:/var/www/certbot
    networks:
      - siscoop_net
    depends_on:
      - app

  db:
    image: postgres:16-alpine
    container_name: siscoop_db
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE:-siscoop3f}
      POSTGRES_USER: ${DB_USERNAME:-postgres}
      POSTGRES_PASSWORD: ${DB_PASSWORD:-SuaSenhaSuperSeguraPostgres2026!}
    volumes:
      - siscoop_pgdata:/var/lib/postgresql/data
    networks:
      - siscoop_net

networks:
  siscoop_net:
    driver: bridge

volumes:
  siscoop_pgdata:
```

### `docker/nginx/default.conf`:
```nginx
server {
    listen 80;
    server_name seu-dominio.com.br *.seu-dominio.com.br;
    root /var/www/public;

    index index.php index.html;

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## 📦 PASSO 6: Subir os Containers e Inicializar o Laravel

1. Compile e inicie os containers em modo daemon:
   ```bash
   docker compose up -d --build
   ```

2. Verifique o status dos containers:
   ```bash
   docker compose ps
   ```

3. Instale as dependências do Composer em produção:
   ```bash
   docker compose exec app composer install --optimize-autoloader --no-dev
   ```

4. Gere a chave única de criptografia do Laravel:
   ```bash
   docker compose exec app php artisan key:generate
   ```

5. Execute o link simbólico do storage:
   ```bash
   docker compose exec app php artisan storage:link
   ```

6. Execute as migrações e seeders no banco PostgreSQL:
   ```bash
   # Migrações da base central (Landlord)
   docker compose exec app php artisan migrate --force

   # Migrações dos tenants
   docker compose exec app php artisan tenants:migrate

   # Seeder inicial (se houver)
   docker compose exec app php artisan db:seed --force
   ```

7. Otimize os caches do Laravel:
   ```bash
   docker compose exec app php artisan config:cache
   docker compose exec app php artisan route:cache
   docker compose exec app php artisan view:cache
   ```

---

## 🔒 PASSO 7: Configurar SSL Gratuito (Let's Encrypt Wildcard HTTPS)

Para permitir suporte seguro HTTPS tanto para o domínio principal quanto para todos os tenants em subdomínios (`*.seu-dominio.com.br`):

1. Instale o Certbot na máquina Host:
   ```bash
   apt install -y certbot python3-certbot-nginx
   ```

2. Emita o certificado com validação Nginx:
   ```bash
   certbot --nginx -d seu-dominio.com.br -d "*.seu-dominio.com.br" --agree-tos -m seu-email@exemplo.com
   ```

3. Teste a renovação automática:
   ```bash
   certbot renew --dry-run
   ```

---

## 🛠️ Comandos de Manutenção no Servidor VPS

- **Ver logs em tempo real do App**:
  ```bash
  docker compose logs -f app
  ```
- **Ver logs do Banco PostgreSQL**:
  ```bash
  docker compose logs -f db
  ```
- **Reiniciar os serviços**:
  ```bash
  docker compose restart
  ```
- **Atualizar o código (Deploy Continuo)**:
  ```bash
  git pull origin main
  docker compose exec app composer install --optimize-autoloader --no-dev
  docker compose exec app php artisan migrate --force
  docker compose exec app php artisan tenants:migrate
  docker compose exec app php artisan config:cache
  docker compose exec app php artisan route:cache
  docker compose exec app php artisan view:cache
  ```

---
**Siscoop3F v1.0** — Sistema Multi-tenant de Cooperativas e Fundações de Saúde.
