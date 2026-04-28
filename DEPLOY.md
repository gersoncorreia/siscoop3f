# Guia de Deploy — Siscoop3F (HostGator Plano M)

Este guia explica como colocar o sistema no ar na HostGator usando o cPanel.

## 1. Preparação no cPanel (HostGator)

### Subdomínio Wildcard
Como o sistema usa subdomínios dinâmicos (ex: `coop1.seu-dominio.com.br`), você precisa configurar um wildcard:
1. No cPanel, vá em **Domínios**.
2. Clique em **Criar um Novo Domínio**.
3. No campo de nome, digite `*.seu-dominio.com.br` (substitua pelo seu domínio).
4. O "Document Root" deve apontar para a pasta onde você subirá o sistema, especificamente para a pasta `/public`. Exemplo: `/public_html/siscoop3f/public`.

### Banco de Dados
1. Crie um banco de dados principal (central) no cPanel (ex: `prefixo_siscoop_central`).
2. Crie um usuário para este banco e dê todas as permissões.
3. **Importante**: Na HostGator Shared, você precisará criar os bancos das cooperativas manualmente ou usar o modo de "Single Database" (se precisar de ajuda para mudar para Single Database, me avise).

---

## 2. Subindo o Código

### Via Git (Recomendado)
Se você tiver acesso SSH na HostGator:
1. Entre via terminal SSH.
2. Clone o repositório: `git clone https://github.com/gersoncorreia/siscoop3f.git`.

### Via FTP/Gerenciador de Arquivos
1. Suba todos os arquivos para uma pasta (ex: `/public_html/siscoop3f`).
2. **NÃO suba as pastas `node_modules` e `vendor`** (elas devem ser instaladas no servidor ou subidas separadamente se o servidor não tiver Composer).

---

## 3. Configuração do Ambiente

1. Copie o arquivo `.env.production.example` para `.env`.
2. Edite o `.env` com os dados do banco que você criou no cPanel.
3. Gere a chave do app: `php artisan key:generate`.
4. Rode as migrações: `php artisan migrate`.
5. Rode o seeder de produção: `php artisan db:seed --class=ProductionSeeder`.

---

## 4. Build do Frontend

Como usamos Vue + Inertia + Vite:
1. No seu computador local, rode: `npm run build`.
2. Isso vai gerar a pasta `public/build`.
3. Certifique-se de que essa pasta `public/build` foi enviada para o servidor.

---

## 5. Dicas Importantes para HostGator

*   **Versão do PHP**: Garanta que o domínio está usando **PHP 8.2** ou superior (mude no "MultiPHP Manager" do cPanel).
*   **Symlink**: Rode `php artisan storage:link` para que os documentos e logos funcionem.
*   **Acesso SSH**: Se o seu plano permite, habilite o SSH no suporte da HostGator, facilita 100% o deploy.
