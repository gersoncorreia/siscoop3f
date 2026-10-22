# Guia de Configuração de Servidor VPS (Hostinger) & Docker para Siscoop3F

Este guia prático descreve detalhadamente o passo a passo para preparar o servidor VPS na Hostinger, configurar o ambiente de produção isolado e de alta performance utilizando Docker, e estruturar o projeto com suporte a multi-tenancy (wildcard subdomains).

---

## 1. Preparação da VPS Hostinger (Sistema Operacional: Ubuntu 22.04 LTS)

### Passo 1.1: Acesso SSH e Atualização do Sistema
1. Acesse o terminal do seu servidor via SSH:
   ```bash
   ssh root@IP_DO_SEU_SERVIDOR
   ```
2. Atualize a lista de pacotes do Ubuntu para garantir a última versão de segurança:
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

### Passo 1.2: Configurar o Firewall Basico (UFW)
Para proteger o servidor de acessos não autorizados:
```bash
# Permitir conexões SSH, HTTP e HTTPS
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Habilitar o firewall
sudo ufw enable
```

---

## 2. Instalação do Docker e Docker Compose na VPS

Execute a sequência de comandos abaixo para instalar o Docker Engine oficial do repositório oficial da Docker:

```bash
# Instalar dependências necessárias
sudo apt install apt-transport-https ca-certificates curl gnupg lsb-release -y

# Adicionar a chave GPG oficial do Docker
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Adicionar o repositório estável
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Instalar o Docker e Docker Compose CLI
sudo apt update
sudo apt install docker-ce docker-ce-cli containerd.io docker-compose-plugin -y

# Verificar instalações
docker --version
docker compose version
```

---

## 3. Estruturação do Docker para Siscoop3F (Arquivos de Configuração)

No diretório raiz da sua aplicação no servidor (`/var/www/siscoop3f`), você deve criar os seguintes arquivos de configuração para orquestrar os containers.

### 3.1 `Dockerfile` (PHP 8.2 + Extensões Necessárias + Supervisor)
Crie este arquivo na raiz do seu projeto:

```dockerfile
FROM php:8.2-fpm

# Instalar extensões de sistema necessárias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libxpm-dev \
    libfreetype6-dev \
    zip \
    unzip \
    supervisor \
    nginx

# Limpar o cache de pacotes
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensões do PHP (incluindo GD com suporte a WebP para otimização)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets

# Instalar phpredis para suporte a sessões/cache de alto throughput no Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos da aplicação
COPY . /var/www/html

# Ajustar permissões para o usuário padrão do servidor web (www-data)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### 3.2 `docker-compose.yml` (Orquestração de Containers)
Crie o arquivo na raiz do seu projeto para iniciar o PHP-FPM, Nginx, MySQL, Redis e o Supervisor Worker:

```yaml
version: '3.8'

services:
  # Aplicação principal PHP-FPM
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: siscoop3f-app
    restart: unless-stopped
    tty: true
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
    networks:
      - siscoop3f-network

  # Servidor Web Nginx
  webserver:
    image: nginx:alpine
    container_name: siscoop3f-webserver
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/conf.d/:/etc/nginx/conf.d/
      - /etc/letsencrypt:/etc/letsencrypt # Para SSL automatizado com Certbot
    networks:
      - siscoop3f-network
    depends_on:
      - app

  # Banco de Dados MySQL
  db:
    image: mysql:8.0
    container_name: siscoop3f-mysql
    restart: unless-stopped
    ports:
      - "3306:3306"
    environment:
      MYSQL_DATABASE: siscoop3f
      MYSQL_ROOT_PASSWORD: SENHA_SEGURA_DO_BANCO_DE_DADOS
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - siscoop3f-network

  # Redis para Session, Cache e Fila Assíncrona
  redis:
    image: redis:alpine
    container_name: siscoop3f-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redisdata:/data
    networks:
      - siscoop3f-network

  # Worker do Fila do Laravel (Supervisor)
  queue-worker:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: siscoop3f-worker
    restart: unless-stopped
    command: php artisan queue:work --queue=default --sleep=3 --tries=3
    volumes:
      - .:/var/www/html
    networks:
      - siscoop3f-network
    depends_on:
      - app
      - redis

networks:
  siscoop3f-network:
    driver: bridge

volumes:
  dbdata:
    driver: local
  redisdata:
    driver: local
```

### 3.3 Configuração do Servidor Nginx (`docker/nginx/conf.d/app.conf`)
Para suportar o multi-tenancy dinâmico por domínio e subdomínio curinga (wildcard), crie o arquivo `docker/nginx/conf.d/app.conf`:

```nginx
server {
    listen 80;
    # Substitua pelo seu domínio principal e seu curinga. 
    # Ex: siscoop3f.com.br e *.siscoop3f.com.br
    server_name siscoop3f.com.br *.siscoop3f.com.br;

    root /var/www/html/public;
    index index.php index.html;

    client_max_body_size 100M; # Permite uploads maiores (como faturas e documentos)

    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## 4. Subindo o Sistema na VPS

Com os arquivos criados e o código clonado em `/var/www/siscoop3f` na sua VPS Hostinger:

1. **Configure o `.env` de Produção:**
   ```bash
   cp .env.production.example .env
   nano .env
   ```
   Ajuste as credenciais de produção:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://siscoop3f.com.br

   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=siscoop3f
   DB_USERNAME=root
   DB_PASSWORD=SENHA_SEGURA_DO_BANCO_DE_DADOS

   # Configuração de Alta Concorrência (Redis)
   CACHE_STORE=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=redis

   REDIS_HOST=redis
   REDIS_PORT=6379
   ```

2. **Inicie os Containers Docker:**
   ```bash
   docker compose up -d --build
   ```

3. **Instale as dependências PHP e JS dentro do Container:**
   ```bash
   docker compose exec app composer install --no-dev --optimize-autoloader
   docker compose exec app php artisan key:generate
   ```

4. **Rode as Migrações Iniciais (Central):**
   ```bash
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan db:seed --force
   ```

5. **Garanta o Link de Armazenamento para os Tenants:**
   No ambiente multi-tenant, os arquivos públicos ficam em `storage/tenant<id>`.
   Rode o comando dentro do container para gerar o link simbólico correto:
   ```bash
   docker compose exec app php artisan storage:link
   ```

6. **Automatizar Let's Encrypt (SSL Wildcard Grátis):**
   Para automatizar e gerar o SSL gratuito para o domínio principal e todos os subdomínios cooperados de forma dinâmica, instale o certbot na VPS Hostinger:
   ```bash
   sudo apt install certbot -y
   # Gere o certificado wildcard usando validação de DNS (conforme instruções do seu registrador de domínio)
   sudo certbot certonly --manual --preferred-challenges=dns -d siscoop3f.com.br -d *.siscoop3f.com.br
   ```
   Depois de gerado, atualize seu `app.conf` para escutar na porta `443` apontando para a pasta `/etc/letsencrypt/live/siscoop3f.com.br/`.
