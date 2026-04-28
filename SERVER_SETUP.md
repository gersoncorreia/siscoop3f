# Guia de Configuração Manual da VPS (Ubuntu 22.04/24.04) — Siscoop3F

Este guia contém todos os comandos necessários para configurar sua VPS do zero para hospedar múltiplos projetos Laravel com suporte a Multi-tenancy (subdomínios dinâmicos).

---

## Passo 1: Atualização e Segurança Inicial
Acesse sua VPS via SSH: `ssh root@seu_ip`

```bash
# Atualizar repositórios e sistema
apt update && apt upgrade -y

# Configurar Firewall
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable
```

---

## Passo 2: Instalar Stack LEMP (Nginx, PHP 8.3, MySQL)

```bash
# Instalar Nginx
apt install nginx -y

# Instalar PHP 8.3 e extensões necessárias
add-apt-repository ppa:ondrej/php -y
apt update
apt install php8.3-fpm php8.3-mysql php8.3-igbinary php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-curl php8.3-intl php8.3-gd php8.3-redis -y

# Instalar MySQL
apt install mysql-server -y
```

### Configurar Usuário Master do MySQL
Entre no MySQL: `mysql`
```sql
-- Criar usuário com permissão para criar bancos de dados (necessário para Multi-tenancy)
CREATE USER 'siscoop_admin'@'localhost' IDENTIFIED BY 'SUA_SENHA_SEGURA';
GRANT ALL PRIVILEGES ON *.* TO 'siscoop_admin'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EXIT;
```

---

## Passo 3: Ferramentas e Composer

```bash
# Instalar Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Instalar Node.js e NPM (para o build do Vue)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install nodejs -y
```

---

## Passo 4: Deploy do Projeto

```bash
# Criar pasta do projeto e clonar
mkdir -p /var/www/siscoop3f
cd /var/www/siscoop3f
git clone https://github.com/gersoncorreia/siscoop3f.git .

# Instalar dependências PHP
composer install --no-dev --optimize-autoloader

# Configurar permissões (CRUCIAL)
chown -R www-data:www-data /var/www/siscoop3f
chmod -R 775 /var/www/siscoop3f/storage
chmod -R 775 /var/www/siscoop3f/bootstrap/cache
```

---

## Passo 5: Configuração do Nginx (Suporte a Subdomínios)

Crie o arquivo de configuração: `nano /etc/nginx/sites-available/siscoop3f`

**Cole o conteúdo abaixo (ajuste o domínio):**
```nginx
server {
    listen 80;
    # Aceita o domínio principal e QUALQUER subdomínio
    server_name siscoop3f.com.br *.siscoop3f.com.br;
    root /var/www/siscoop3f/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Ative o site:
```bash
ln -s /etc/nginx/sites-available/siscoop3f /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## Passo 6: SSL Wildcard (HTTPS)

```bash
# Instalar Certbot
apt install certbot python3-certbot-nginx -y

# Gerar certificado (Siga as instruções na tela)
# Nota: Para wildcard (*.dominio), o certbot pode pedir uma entrada TXT no seu DNS
certbot --nginx -d siscoop3f.com.br -d *.siscoop3f.com.br
```

---

## Passo 7: Fila (Queue) com Supervisor
Para garantir que e-mails e processos de fundo funcionem:

```bash
apt install supervisor -y
nano /etc/supervisor/conf.d/siscoop-worker.conf
```

**Cole isto:**
```ini
[program:siscoop-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/siscoop3f/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/siscoop3f/storage/logs/worker.log
stopwaitsecs=3600
```

Ative o supervisor:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start siscoop-worker:*
```

---
**PRONTO!** Seu servidor está configurado para o Siscoop3F e preparado para escalar.
