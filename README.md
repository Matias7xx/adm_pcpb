# Portal PCPB

Portal institucional da Polícia Civil da Paraíba, desenvolvido com Laravel + Vue 3 + Inertia.js.

---

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.4 + Laravel 12 |
| Frontend | Vue 3 + Inertia.js 2.0 + Tailwind CSS |
| Banco de dados | PostgreSQL |
| Storage | MinIO (S3-compatible) |
| Servidor web | Apache (Docker) |
| Runtime JS | Node.js 22 |

---

## Funcionalidades

### Área Pública
- Páginas institucionais: História, Missão, Organograma, Regimento Interno
- Listagem e leitura de Notícias
- Veículos Apreendidos
- Vídeos e Banners institucionais

### Área Autenticada (Servidores)
- Registro e acompanhamento de Operações Policiais
- Resultados de Operações

### Painel Administrativo
- Gerenciamento de Usuários, Funções e Permissões (Spatie)
- Gestão de Notícias, Cursos, Banners e Vídeos
- Controle de Veículos Apreendidos
- Gestão de Menus
- Audit Log completo de ações administrativas

---

## Autenticação

O sistema utiliza um provedor customizado com dupla estratégia:

1. **Via API externa** — autenticação por matrícula/senha contra endpoint configurado em `API_LOGIN_URL`, com token em `API_TOKEN`.
2. **Fallback local** — em caso de falha de conexão, autentica contra o banco de dados local.

Novos usuários recebem automaticamente a role `servidor`. Roles disponíveis: `super-admin`, `admin`, `diop`, `servidor`.

---

## Storage (MinIO)

Dois buckets S3-compatíveis são utilizados:

| Bucket | Variável de ambiente | Conteúdo |
|---|---|---|
| `funcionais` | `AWS_BUCKET_FOTOS` | Fotos dos servidores |
| `veiculos` | `AWS_BUCKET` | Relação de veículos apreendidos |

---

## Configuração

### Pré-requisitos
- Docker e Docker Compose

### Variáveis de ambiente obrigatórias

```env
APP_KEY=
APP_URL=

DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_ENDPOINT=
AWS_BUCKET_FOTOS=funcionais
AWS_BUCKET=veiculos

API_TOKEN=
API_LOGIN_URL=
```

### Subindo o ambiente

```bash
# 1. Copiar e preencher os arquivos de configuração
cp .env.example .env
cp docker-compose.yml.example docker-compose.yml

# 2. Build da imagem (obrigatório na primeira vez ou após mudanças no Dockerfile)
docker compose build

# 3. Subir os containers em background
docker compose up -d

# 4. Gerar chave da aplicação
docker exec pcpb php artisan key:generate

# 5. Migrations e seeders rodam automaticamente pelo entrypoint.
#    Se precisar rodar manualmente:
docker exec pcpb php artisan migrate --force
docker exec pcpb php artisan db:seed --class=AdminCoreSeeder --force
```

> Para rebuild completo sem cache: `docker compose build --no-cache`

---

## Estrutura de rotas

```
/                        → Home
/historia                → História institucional
/missao                  → Missão, Visão e Valores
/noticias                → Listagem de notícias
/veiculos-apreendidos    → Veículos apreendidos
/operacoes               → Operações policiais (auth)
/painel/                 → Painel administrativo (admin)
```

---

## Auditoria

O sistema registra automaticamente todas as ações administrativas via `Trait Auditable`, aplicável a qualquer Model. Os eventos `created`, `updated` e `deleted` são persistidos na tabela `audit_logs` com dados anteriores, dados novos, usuário responsável, IP e rota.

Módulos auditados: Usuários, Notícias, Operações, Resultados de Operação, Veículos, Banners, Vídeos e Sistema.

---

## Seeders (primeira execução)

O entrypoint detecta automaticamente se o banco está vazio e executa os seeders iniciais:

| Seeder | Descrição |
|---|---|
| `AdminCoreSeeder` | Cria usuário super-admin, roles e permissões base |

---

## Licença

Uso restrito — Polícia Civil do Estado da Paraíba.
