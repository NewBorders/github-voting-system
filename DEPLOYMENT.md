# Feature Voting Backend - Deployment Guide

## Production Deployment Steps

### 1. Prepare Environment

```bash
# Clone repository
git clone <your-repo-url>
cd githubvoting

# Copy and configure environment
cp .env.example .env
nano .env
```

### 2. Update .env for Production

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Generate a strong random token
ADMIN_API_TOKEN=$(openssl rand -base64 32)

# Production database credentials
DB_HOST=your-production-db-host
DB_DATABASE=voting_production
DB_USERNAME=voting_user
DB_PASSWORD=strong-password-here
```

### 3. Deploy with Docker

```bash
# Build for production
docker-compose -f docker-compose.prod.yml up -d --build

# Run migrations
docker-compose exec app php artisan migrate --force

# Cache configuration
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Optimize autoloader
docker-compose exec app composer install --optimize-autoloader --no-dev
```

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
