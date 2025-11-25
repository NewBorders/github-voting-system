# Feature Voting System

A lightweight, anonymous feature-voting system built with PHP 8.2+ and Laravel 10. Features are synced from GitHub Issues, and users can vote without authentication.

## Features

- 🗳️ **Anonymous Voting** - No user accounts required
- 🚀 **Multi-Project Support** - Manage features for multiple projects
- 🔄 **GitHub Issue Sync** - Features imported directly from GitHub Issues
- 🔒 **Admin Dashboard** - Web-based admin panel for management
- 🐳 **Docker Ready** - Complete Docker setup included
- 🎨 **HTMX Frontend** - Fast, reactive UI without JavaScript frameworks

## Tech Stack

- PHP 8.2+
- Laravel 10 (LTS)
- MySQL 8.0
- Docker & Docker Compose
- Nginx + PHP-FPM
- HTMX + TailwindCSS

## Architecture

### Data Flow

```
GitHub Issues → Sync Button → Database → Web UI → Votes
```

### Key Concepts

- **Projects**: Linked to GitHub repositories
- **Features**: Synced from GitHub Issues (not manually created)
- **Votes**: Anonymous upvotes using client-side UUIDs

## Quick Start

### Prerequisites

- Docker
- Docker Compose

### Installation

1. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

2. **Update configuration in `.env`**
   ```bash
   APP_URL=http://localhost:8080  # For production: https://yourdomain.com
   ADMIN_API_TOKEN=your-secure-random-token-here
   ```

3. **Build and start containers**
   ```bash
   docker compose up -d --build
   ```

4. **Install dependencies**
   ```bash
   docker compose exec app composer install
   ```

5. **Generate application key**
   ```bash
   docker compose exec app php artisan key:generate
   ```

6. **Run migrations**
   ```bash
   docker compose exec app php artisan migrate
   ```

The application will be available at `http://localhost:8080`

**Note:** For production deployment with Traefik, see [DEPLOYMENT.md](DEPLOYMENT.md) and [TRAEFIK.md](TRAEFIK.md)

## Usage

### Admin Access

1. Navigate to `/admin/login`
2. Enter your `ADMIN_API_TOKEN` from `.env`
3. Create a project with GitHub owner/repo
4. Click "Sync GitHub Issues" to import features
5. Manage feature status (submitted → planned → in_progress → done)

### Public Voting

- Users visit `/vote` to see all projects
- Click a project to view and vote on features
- Features show GitHub issue links
- Vote counts update in real-time via HTMX

## Admin API (Optional)

The system includes a minimal Admin API for automation:

Base URL: `/api/v1/admin`

**Authentication:** Bearer token via `Authorization` header

### Endpoints

```http
POST   /api/v1/admin/projects          # Create project
PATCH  /api/v1/admin/projects/{id}     # Update project
PATCH  /api/v1/admin/features/{id}     # Update feature status
DELETE /api/v1/admin/features/{id}     # Delete feature
GET    /api/v1/admin/stats             # Get statistics
```

**Example:**
```bash
curl -H "Authorization: Bearer your-admin-token" \
     https://yourdomain.com/api/v1/admin/stats
```

## Development

### Running Tests

```bash
docker compose exec app php artisan test
```

Or specific test suites:

```bash
docker compose exec app php artisan test --filter FeatureCreationTest
docker compose exec app php artisan test --filter VotingTest
docker compose exec app php artisan test --filter AdminAuthenticationTest
```

### Database Commands

**Create fresh database:**
```bash
docker compose exec app php artisan migrate:fresh
```

**Seed with sample data:**
```bash
docker compose exec app php artisan db:seed
```

**Reset and seed:**
```bash
docker compose exec app php artisan migrate:fresh --seed
```

### Logs

**View application logs:**
```bash
docker compose logs -f app
```

**View nginx logs:**
```bash
docker compose logs -f web
```

**View database logs:**
```bash
docker compose logs -f db
```

### Container Management

```bash
# Stop containers
docker compose down

# Stop and remove volumes
docker compose down -v

# Rebuild containers
docker compose up -d --build

# Execute commands in app container
docker compose exec app php artisan [command]
```

## Troubleshooting

### Mixed Content Error (HTTPS)

If you see "Mixed Content" errors in production:

1. Set `APP_URL=https://yourdomain.com` in `.env`
2. TrustProxies middleware is already configured to trust all proxies
3. Restart containers: `docker compose restart`

### Database Connection Issues

```bash
# Check if database container is running
docker compose ps

# Check database logs
docker compose logs db

# Verify database credentials in .env match docker-compose.yml
```

## License

MIT

## Support

For issues or questions, please open an issue in the repository.
