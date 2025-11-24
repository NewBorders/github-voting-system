# Feature Voting Backend - Deployment Guide

## Deployment with Traefik and Ansible

### Overview

This application uses a layered Docker Compose setup:
- **`docker-compose.yml`** - Base configuration (version controlled)
- **`docker-compose.override.yml`** - Production overrides (managed by Ansible, NOT in git)
- **`.env`** - Environment variables (managed by Ansible, NOT in git)

### Architecture

```
Git Repository (docker-compose.yml)
         ↓
Ansible deploys override file + .env
         ↓
Docker Compose merges both files
         ↓
Traefik routes traffic to container
```

## Ansible Deployment

### 1. Create docker-compose.override.yml Template

Create an Ansible template at `templates/githubvoting/docker-compose.override.yml.j2`:

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

  db:
    environment:
      MYSQL_ROOT_PASSWORD: "{{ githubvoting_db_root_password }}"
      MYSQL_PASSWORD: "{{ githubvoting_db_password }}"

networks:
  traefik_network:
    external: true
```

### 2. Create .env Template

Create `templates/githubvoting/.env.j2`:

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

### 3. Ansible Playbook

```yaml
- name: Deploy GitHub Voting System
  hosts: production_servers
  vars:
    githubvoting_host: "voting.yourdomain.com"
    githubvoting_app_dir: "/opt/githubvoting"
    githubvoting_db_user: "voting"
    githubvoting_db_password: "{{ vault_githubvoting_db_password }}"
    githubvoting_db_root_password: "{{ vault_githubvoting_db_root_password }}"
    githubvoting_admin_token: "{{ vault_githubvoting_admin_token }}"
    githubvoting_app_key: "{{ vault_githubvoting_app_key }}"
  
  tasks:
    - name: Clone repository
      git:
        repo: "https://github.com/Muardin/github-voting-system.git"
        dest: "{{ githubvoting_app_dir }}"
        version: main
        force: yes
      notify: restart githubvoting

    - name: Create .env file
      template:
        src: templates/githubvoting/.env.j2
        dest: "{{ githubvoting_app_dir }}/.env"
        mode: '0600'
      notify: restart githubvoting

    - name: Create docker-compose.override.yml
      template:
        src: templates/githubvoting/docker-compose.override.yml.j2
        dest: "{{ githubvoting_app_dir }}/docker-compose.override.yml"
        mode: '0644'
      notify: restart githubvoting

    - name: Ensure traefik network exists
      docker_network:
        name: traefik_network
        state: present

    - name: Start services
      community.docker.docker_compose:
        project_src: "{{ githubvoting_app_dir }}"
        state: present
        pull: yes
      register: output

    - name: Run database migrations
      command: docker compose exec -T app php artisan migrate --force
      args:
        chdir: "{{ githubvoting_app_dir }}"
      when: output.changed

  handlers:
    - name: restart githubvoting
      community.docker.docker_compose:
        project_src: "{{ githubvoting_app_dir }}"
        restarted: yes
```

### 4. Ansible Vault for Secrets

```bash
# Create encrypted secrets file
ansible-vault create group_vars/production/githubvoting_vault.yml
```

Content of `githubvoting_vault.yml`:
```yaml
vault_githubvoting_db_password: "secure_password_here"
vault_githubvoting_db_root_password: "secure_root_password_here"
vault_githubvoting_admin_token: "secure_admin_token_here"
vault_githubvoting_app_key: "base64:generated_laravel_key_here"
```

### 5. Generate Laravel App Key

```bash
# On your local machine or server
docker compose up -d
docker compose exec app php artisan key:generate --show
```

Copy the output (starts with `base64:`) to your Ansible vault.

## How Docker Compose Override Works

When you run `docker compose up`, Docker automatically:
1. Loads `docker-compose.yml` (base config)
2. Loads `docker-compose.override.yml` if it exists
3. **Merges** both configurations
4. Arrays (like `labels`) are **concatenated**
5. Single values are **overridden**

Example merge:
```yaml
# docker-compose.yml
services:
  app:
    image: myapp
    ports:
      - "8080:80"

# docker-compose.override.yml  
services:
  app:
    labels:
      - "traefik.enable=true"
    networks:
      - traefik_network

# Result after merge:
services:
  app:
    image: myapp
    ports:
      - "8080:80"
    labels:
      - "traefik.enable=true"
    networks:
      - traefik_network
```

## Advantages of This Approach

✅ **Clean Separation**: Git doesn't know about Traefik  
✅ **Flexible**: Easy to switch hosting methods  
✅ **Secure**: All secrets in Ansible Vault  
✅ **Standard**: Uses Docker Compose's built-in override mechanism  
✅ **Simple**: No custom scripts needed  
✅ **Local Dev Friendly**: Works without override file

## Local Development

For local development without Traefik:
```bash
docker compose up
```

The override file doesn't exist locally, so it's ignored automatically.

---

## Manual Production Deployment (Alternative)

If you prefer manual deployment without Ansible:

### 4. Setup Nginx/Apache Reverse Proxy

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 5. Setup Database Backups

```bash
# Create backup script
cat > /usr/local/bin/backup-voting-db.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/voting"
mkdir -p $BACKUP_DIR

docker-compose exec -T db mysqldump -u voting -psecret voting > \
  $BACKUP_DIR/voting_backup_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "voting_backup_*.sql" -mtime +30 -delete
EOF

chmod +x /usr/local/bin/backup-voting-db.sh

# Add to crontab (daily at 2 AM)
echo "0 2 * * * /usr/local/bin/backup-voting-db.sh" | crontab -
```

### 6. Setup Log Rotation

```bash
# /etc/logrotate.d/voting-backend
/var/www/githubvoting/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### 7. Monitoring Setup

#### Health Check Endpoint

The API includes a built-in health check at `/up`

```bash
# Add to monitoring service
curl https://api.yourdomain.com/up
```

#### Application Monitoring

```bash
# View logs
docker-compose logs -f app

# Monitor containers
docker-compose ps

# Check database connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### 8. Security Hardening

#### Update docker-compose.yml

```yaml
services:
  db:
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    networks:
      - voting-internal
    # Don't expose port publicly
    # ports:
    #   - "3306:3306"

networks:
  voting-internal:
    driver: bridge
    internal: true  # Not accessible from outside
```

#### Setup Fail2Ban for Rate Limit Protection

```bash
# /etc/fail2ban/filter.d/voting-api.conf
[Definition]
failregex = .*"POST /api/v1/.*" 429.*
ignoreregex =

# /etc/fail2ban/jail.local
[voting-api]
enabled = true
port = http,https
filter = voting-api
logpath = /var/www/githubvoting/storage/logs/laravel.log
maxretry = 5
bantime = 3600
```

### 9. SSL/TLS with Let's Encrypt

```bash
# Install certbot
apt-get install certbot python3-certbot-nginx

# Get certificate
certbot --nginx -d api.yourdomain.com

# Auto-renewal (runs twice daily)
systemctl enable certbot.timer
systemctl start certbot.timer
```

### 10. Performance Optimization

#### Enable OPcache (in Dockerfile)

```dockerfile
RUN docker-php-ext-install opcache

# php.ini settings
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

#### Database Optimization

```sql
-- Add after deployment
OPTIMIZE TABLE projects;
OPTIMIZE TABLE features;
OPTIMIZE TABLE votes;
```

### 11. Scaling Considerations

#### Horizontal Scaling

- Use external database (RDS, managed MySQL)
- Setup load balancer
- Use Redis for session/cache storage
- Enable queue workers for background jobs

#### Vertical Scaling

```yaml
# docker-compose.yml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          memory: 512M
```

### 12. Disaster Recovery

#### Quick Restore Procedure

```bash
# 1. Restore database
docker-compose exec -T db mysql -u voting -psecret voting < backup.sql

# 2. Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear

# 3. Verify
curl https://api.yourdomain.com/api/v1/projects
```

## Zero-Downtime Deployment

```bash
#!/bin/bash
# deploy.sh

set -e

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Build new image with timestamp tag
TAG=$(date +%Y%m%d_%H%M%S)
docker-compose build --no-cache
docker tag voting-app:latest voting-app:$TAG

# Run migrations
docker-compose exec app php artisan migrate --force

# Update caches
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Reload PHP-FPM gracefully
docker-compose exec app kill -USR2 1

echo "Deployment completed successfully!"
```

## Troubleshooting Production Issues

### High Memory Usage

```bash
# Check PHP memory limit
docker-compose exec app php -i | grep memory_limit

# Monitor container stats
docker stats voting-app
```

### Database Connection Pool Exhausted

```sql
-- Check connections
SHOW PROCESSLIST;
SHOW STATUS LIKE 'Threads_connected';
SHOW VARIABLES LIKE 'max_connections';

-- Increase if needed
SET GLOBAL max_connections = 200;
```

### Slow API Responses

```bash
# Enable query logging
docker-compose exec app php artisan debugbar:enable

# Check slow query log
docker-compose exec db tail -f /var/log/mysql/slow-query.log

# Add database indexes if needed
docker-compose exec app php artisan tinker
>>> DB::connection()->enableQueryLog();
```

## Maintenance Mode

```bash
# Enable maintenance mode
docker-compose exec app php artisan down --secret="maintenance-bypass-token"

# Perform updates
git pull
docker-compose exec app composer install
docker-compose exec app php artisan migrate

# Disable maintenance mode
docker-compose exec app php artisan up
```

## Rollback Procedure

```bash
# 1. Revert code
git revert HEAD
git push

# 2. Rollback database
docker-compose exec app php artisan migrate:rollback --step=1

# 3. Rebuild
docker-compose up -d --build

# 4. Verify
curl https://api.yourdomain.com/api/v1/projects
```
