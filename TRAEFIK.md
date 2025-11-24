# Traefik Deployment - Quick Reference

## File Overview

- `docker-compose.yml` - **IN GIT** - Base configuration
- `docker-compose.override.yml` - **NOT IN GIT** - Traefik labels (managed by Ansible)
- `.env` - **NOT IN GIT** - Production secrets (managed by Ansible)
- `docker-compose.override.yml.example` - **IN GIT** - Example for reference

## Quick Start for Ansible

### 1. Ansible Template: `templates/githubvoting/docker-compose.override.yml.j2`

```yaml
services:
  app:
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.githubvoting.rule=Host(`{{ githubvoting_host }}`)"
      - "traefik.http.routers.githubvoting.entrypoints=websecure"
      - "traefik.http.routers.githubvoting.tls.certresolver=le"
      - "traefik.http.services.githubvoting.loadbalancer.server.port=80"
    networks:
      - traefik_network
      - default

  web:
    labels:
      - "traefik.enable=false"

  db:
    environment:
      MYSQL_ROOT_PASSWORD: "{{ githubvoting_db_root_password }}"
      MYSQL_PASSWORD: "{{ githubvoting_db_password }}"

networks:
  traefik_network:
    external: true
```

### 2. Ansible Template: `templates/githubvoting/.env.j2`

```env
APP_NAME="Feature Voting"
APP_ENV=production
APP_KEY={{ githubvoting_app_key }}
APP_DEBUG=false
APP_URL=https://{{ githubvoting_host }}

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=voting
DB_USERNAME={{ githubvoting_db_user }}
DB_PASSWORD={{ githubvoting_db_password }}

ADMIN_API_TOKEN={{ githubvoting_admin_token }}
```

### 3. Minimal Ansible Playbook

```yaml
- name: Deploy GitHub Voting
  hosts: production
  vars:
    app_dir: "/opt/githubvoting"
    
  tasks:
    - name: Clone repo
      git:
        repo: "https://github.com/Muardin/github-voting-system.git"
        dest: "{{ app_dir }}"
        version: main

    - name: Deploy .env
      template:
        src: githubvoting/.env.j2
        dest: "{{ app_dir }}/.env"
        mode: '0600'

    - name: Deploy override
      template:
        src: githubvoting/docker-compose.override.yml.j2
        dest: "{{ app_dir }}/docker-compose.override.yml"

    - name: Start services
      community.docker.docker_compose:
        project_src: "{{ app_dir }}"
        state: present

    - name: Run migrations
      command: docker compose exec -T app php artisan migrate --force
      args:
        chdir: "{{ app_dir }}"
```

### 4. Ansible Vault Variables

```yaml
# group_vars/production/githubvoting.yml
githubvoting_host: "voting.yourdomain.com"
githubvoting_db_user: "voting"

# group_vars/production/vault.yml (encrypted)
vault_githubvoting_db_password: "SecurePass123!"
vault_githubvoting_db_root_password: "RootPass456!"
vault_githubvoting_admin_token: "admin_token_here"
vault_githubvoting_app_key: "base64:xxx..."
```

## Why This Works

1. Docker Compose **automatically merges** `docker-compose.yml` + `docker-compose.override.yml`
2. No changes needed to base `docker-compose.yml` in git
3. Ansible manages production-specific files
4. Local dev just uses `docker-compose.yml` (no override = no Traefik)

## Network Setup

The app container needs to be in **two networks**:
- `voting-network` (internal, defined in docker-compose.yml)
- `traefik_network` (external, added via override)

Traefik must be on the same `traefik_network` to route traffic.

## Testing Locally

```bash
# Create a test override
cat > docker-compose.override.yml <<EOF
services:
  app:
    labels:
      - "traefik.enable=true"
    networks:
      - traefik_network
      - voting-network

networks:
  traefik_network:
    external: true
EOF

# Start
docker compose up
```

## See Also

- Full deployment guide: [DEPLOYMENT.md](DEPLOYMENT.md)
- Docker Compose override docs: https://docs.docker.com/compose/extends/
