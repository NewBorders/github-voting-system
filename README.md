# Feature Voting Backend

A lightweight, anonymous feature-voting backend built with PHP 8.2+ and Laravel 10. This system allows users to submit feature ideas and vote on them without authentication, while providing basic rate limiting to prevent abuse.

## Features

- 🗳️ **Anonymous Voting** - No user accounts required
- 🚀 **Multi-Project Support** - Manage features for multiple projects
- 🔒 **Admin API** - Token-based authentication for administrative tasks
- ⚡ **Rate Limiting** - Built-in protection against abuse
- 🐳 **Docker Ready** - Complete Docker setup included
- 🧪 **Tested** - Comprehensive test suite included

## Tech Stack

- PHP 8.2+
- Laravel 10 (LTS)
- MySQL 8.0 (InnoDB, utf8mb4)
- Docker & Docker Compose
- Nginx

## Data Model

### Projects
A project represents a tool or application for which features are collected.

### Features
Feature ideas/suggestions within a project. Can be created anonymously and voted on.

### Votes
Anonymous upvotes on features. Uses client-side generated UUIDs to prevent duplicate voting.

## Quick Start

### Prerequisites

- Docker
- Docker Compose

### Installation

1. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

2. **Update the admin token in `.env`**
   ```bash
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

5. **Generate application key into .env**
   ```bash
   docker compose exec app php artisan key:generate
   ```

6. **Run migrations**
   ```bash
   docker compose exec app php artisan migrate
   ```

7. **Seed database with sample data (optional)**
   ```bash
   docker compose exec app php artisan db:seed
   ```

The application will be available at `http://localhost:8085`

**Note:** For production deployment with Traefik, see [DEPLOYMENT.md](DEPLOYMENT.md)

## API Documentation

Base URL: `http://localhost:8085/api/v1`

### Public Endpoints (No Authentication)

#### List Projects

```http
GET /api/v1/projects
```

Query parameters:
- `active_only` (boolean, default: true) - Only show active projects

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Minecraft Hosting Helper",
      "slug": "minecraft-hosting-helper",
      "description": "A tool to help manage Minecraft servers",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

#### List Features for a Project

```http
GET /api/v1/projects/{project-slug}/features
```

Query parameters:
- `status` (string or array) - Filter by status: `submitted`, `accepted`, `planned`, `in_progress`, `done`, `rejected`
- `sort` (string) - Sort order: `top` (default), `newest`, `oldest`, `random`
- `limit` (int, max 100, default 20) - Results per page
- `page` (int) - Page number

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Add automatic backup functionality",
      "slug": "add-automatic-backup-functionality",
      "description": "Automatically backup worlds at regular intervals.",
      "status": "accepted",
      "vote_count": 15,
      "meta": null,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ],
  "links": { ... },
  "meta": { ... }
}
```

#### Get Single Feature

```http
GET /api/v1/features/{feature-id}
```

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "project": {
      "id": 1,
      "slug": "minecraft-hosting-helper",
      "name": "Minecraft Hosting Helper"
    },
    "title": "Add automatic backup functionality",
    "slug": "add-automatic-backup-functionality",
    "description": "Automatically backup worlds at regular intervals.",
    "status": "accepted",
    "vote_count": 15,
    "meta": null,
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-01T00:00:00Z"
  }
}
```

#### Create Feature

```http
POST /api/v1/projects/{project-slug}/features
Content-Type: application/json
```

**Rate Limit:** 10 requests per hour per IP

**Request Body:**
```json
{
  "title": "Dark mode support",
  "description": "Add a dark theme option to the interface",
  "client_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

Fields:
- `title` (required, string, 5-200 chars)
- `description` (optional, string, max 5000 chars)
- `client_id` (optional, string, 5-100 chars) - If provided, automatically casts one vote

**Example Response:**
```json
{
  "data": {
    "id": 8,
    "title": "Dark mode support",
    "slug": "dark-mode-support",
    "description": "Add a dark theme option to the interface",
    "status": "submitted",
    "vote_count": 1,
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

#### Vote for Feature

```http
POST /api/v1/features/{feature-id}/vote
Content-Type: application/json
```

**Rate Limit:** 60 requests per minute per IP

**Request Body:**
```json
{
  "client_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Example Response:**
```json
{
  "feature_id": 8,
  "vote_count": 16,
  "voted": true
}
```

#### Remove Vote

```http
DELETE /api/v1/features/{feature-id}/vote
Content-Type: application/json
```

**Rate Limit:** 60 requests per minute per IP

**Request Body:**
```json
{
  "client_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Example Response:**
```json
{
  "feature_id": 8,
  "vote_count": 15,
  "voted": false
}
```

### Admin Endpoints (Token Required)

All admin endpoints require the `X-Admin-Token` header:

```http
X-Admin-Token: your-secure-random-token-here
```

#### Create Project

```http
POST /api/v1/admin/projects
Content-Type: application/json
X-Admin-Token: your-token
```

**Request Body:**
```json
{
  "name": "My New Project",
  "slug": "my-new-project",
  "description": "Project description here",
  "is_active": true
}
```

#### Update Project

```http
PATCH /api/v1/admin/projects/{project-id}
Content-Type: application/json
X-Admin-Token: your-token
```

**Request Body:**
```json
{
  "name": "Updated Project Name",
  "is_active": false
}
```

#### Update Feature

```http
PATCH /api/v1/admin/features/{feature-id}
Content-Type: application/json
X-Admin-Token: your-token
```

**Request Body:**
```json
{
  "status": "accepted",
  "title": "Updated title",
  "description": "Updated description",
  "meta": {
    "priority": "high",
    "tags": ["ui", "ux"]
  }
}
```

Allowed status values: `submitted`, `accepted`, `planned`, `in_progress`, `done`, `rejected`

#### Delete Feature

```http
DELETE /api/v1/admin/features/{feature-id}
X-Admin-Token: your-token
```

#### Get Statistics

```http
GET /api/v1/admin/stats
X-Admin-Token: your-token
```

**Example Response:**
```json
{
  "projects": {
    "total": 2,
    "active": 2
  },
  "features": {
    "total": 7,
    "by_status": {
      "submitted": 3,
      "accepted": 1,
      "planned": 1,
      "in_progress": 1,
      "done": 1
    }
  },
  "votes": {
    "total": 153
  },
  "top_features": [
    {
      "id": 6,
      "title": "Mobile app version",
      "slug": "mobile-app-version",
      "project": "Game Tool XYZ",
      "vote_count": 45,
      "status": "submitted"
    }
  ]
}
```

## Configuration

### Environment Variables

Key variables in `.env`:

```bash
APP_NAME="Feature Voting Backend"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=voting
DB_USERNAME=voting
DB_PASSWORD=secret

ADMIN_API_TOKEN=change-this-secure-token-in-production
```

### Rate Limiting

Configured in `app/Providers/RouteServiceProvider.php`:

- **Feature Submissions:** 10 requests per hour per IP
- **Voting Actions:** 60 requests per minute per IP
- **General API:** 120 requests per minute per IP

## Anonymous Voting Implementation

The system implements anonymous voting without storing personal data:

1. **Client ID Generation:** Frontend generates a UUID and stores it in localStorage/cookie
2. **Vote Storage:** Only the opaque `client_id` is stored in the database
3. **Duplicate Prevention:** Unique constraint on `(feature_id, client_id)` prevents double voting
4. **Rate Limiting:** IP-based rate limiting (IPs not stored in DB) prevents mass-voting abuse
5. **No Personal Data:** No emails, usernames, or identifiable information collected

## Development

### Running Tests

```bash
docker-compose exec app php artisan test
```

Or specific test suites:

```bash
docker-compose exec app php artisan test --filter FeatureCreationTest
docker-compose exec app php artisan test --filter VotingTest
docker-compose exec app php artisan test --filter AdminAuthenticationTest
```

### Database Commands

**Create fresh database:**
```bash
docker-compose exec app php artisan migrate:fresh
```

**Seed with sample data:**
```bash
docker-compose exec app php artisan db:seed
```

**Reset and seed:**
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

### Logs

**View application logs:**
```bash
docker-compose logs -f app
```

**View nginx logs:**
```bash
docker-compose logs -f web
```

**View database logs:**
```bash
docker-compose logs -f db
```

### Container Management

```bash
# Stop containers
docker-compose down

# Stop and remove volumes
docker-compose down -v

# Rebuild containers
docker-compose up -d --build

# Execute commands in app container
docker-compose exec app php artisan [command]
```

## Troubleshooting

### Database Connection Issues

```bash
# Check if database container is running
docker-compose ps

# Check database logs
docker-compose logs db

# Verify database credentials in .env match docker-compose.yml
```

## License

MIT

## Support

For issues or questions, please open an issue in the repository.
