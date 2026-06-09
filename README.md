# PrestaShop Docker Setup

Run PrestaShop locally with Docker in 2 commands.

## Quick Start

```bash
cp .env.example .env
docker compose up -d
```

Wait ~30s for MySQL health check, then open **http://localhost:8080**.

## Installation

1. Open the installer in your browser
2. Database server: `mysql-presta`
3. Database name: `prestashop`
4. User: `root`
5. Password: *(leave blank)* [set via DB_PASSWD in .env]

## Admin Access

After installation, back office is at:
- `http://localhost:8080/admin` (or the auto-generated directory)

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| DB_PASSWD | (empty) | MySQL root password |
| PS_DOMAIN | localhost:8080 | Shop domain |
| PS_FOLDER_ADMIN | admin | Admin directory name |
| ADMIN_MAIL | demo@prestashop.com | Admin email |
| ADMIN_PASSWD | changeme123 | Admin password |
| PS_LANGUAGE | en | Installation language |
| PS_COUNTRY | GB | Default country |

## Volumes

- `mysql_data` — database persistence
- `prestashop_data` — shop files & uploads
