# Guia de Deploy Contínuo — Siscoop3F (VPS Ubuntu / Hostinger)

Este guia explica como atualizar o sistema no servidor de produção sempre que novas funcionalidades forem adicionadas ao GitHub.

## 1. Acesso ao Servidor
Acesse sua VPS via SSH:
```bash
ssh root@seu_ip_da_vps
```

## 2. Puxando as Atualizações (Git)
Navegue até a pasta do projeto e baixe as últimas alterações:
```bash
cd /var/www/siscoop3f
git pull origin main
```

## 3. Atualizando Dependências
Se houveram mudanças em pacotes PHP ou Node, rode:
```bash
# Dependências do Backend (PHP)
composer install --no-dev --optimize-autoloader

# Dependências do Frontend (Vue/Inertia)
npm install
```

## 4. Banco de Dados (Migrations)
Sempre que criarmos novas tabelas ou colunas (como fizemos com a Cota Capital), você deve rodar as migrations.
Como o Siscoop3F usa Multi-Tenancy (uma base de dados para cada cooperativa), use o comando:
```bash
# Roda as migrations na base Central e em TODAS as cooperativas automaticamente
php artisan migrate --force
php artisan tenants:migrate --force
```

## 5. Build do Frontend
Para que as alterações visuais (telas novas, botões) apareçam, você deve recompilar o Vite:
```bash
npm run build
```

## 6. Limpeza de Cache e Otimização
Por fim, limpe o cache do Laravel e reinicie a fila para aplicar as novas configurações:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reinicia os workers (fila de e-mails, processamentos em background)
supervisorctl restart siscoop-worker:*
```

---

## 💡 Resumo Rápido (Copia e Cola)
Se você tem certeza que não houve erro no `git pull`, pode rodar tudo de uma vez colando isso no terminal da VPS:

```bash
cd /var/www/siscoop3f && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
npm install && \
npm run build && \
php artisan migrate --force && \
php artisan tenants:migrate --force && \
php artisan optimize:clear && \
supervisorctl restart siscoop-worker:*
```
