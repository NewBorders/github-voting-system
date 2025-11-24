# Feature Voting System - Handoff Document

## 🎉 Latest Changes (November 24, 2025)

**Simplified Workflow**: Features are now **exclusively managed via GitHub Issues**. No manual feature creation needed - everything syncs from GitHub!

---

## ✅ What Has Been Implemented

### Core System
- **HTMX-based UI** for fast, reactive voting without page reloads
- **Anonymous Voting** with localStorage-based client_id (UUID)
- **Duplicate Vote Prevention** via unique constraint on (feature_id, client_id)
- **Responsive Design** with TailwindCSS

### GitHub Integration (Simplified)
- **Features come exclusively from GitHub Issues** - no separate workflow
- **Automatic Issue Import** from GitHub repositories
- **No Token Required** for public repos (works out of the box)
- **Manual Sync Button** in Admin Panel
- **Issue Tracking**: GitHub issue numbers and URLs are stored with each feature

### Admin Dashboard
- **Token-based Authentication** (Session + Cookie)
- **Project Management**: Create, Edit, Activate/Deactivate
- **GitHub Configuration**: Owner + Repo **required** for all projects
- **Feature Management**: Change status (submitted → planned → in_progress → done)
- **One-Click Sync**: Import all open issues with a single button

### Public Voting Interface
- **Project Overview** with all active projects
- **Feature List** sorted by votes (top voted first)
- **Direct GitHub Link**: Users are redirected to GitHub to create new issues
- **Upvote/Unvote** with immediate HTMX updates
- **GitHub Issue Links** visible on all synced features

---

## 🚀 Quick Start

### 1. Start Docker Containers
```bash
docker compose up -d
```

### 2. Install Dependencies (if needed)
```bash
docker compose exec app composer install
```

### 3. Configure Environment
```bash
# Copy .env if not exists
cp .env.example .env

# Generate APP_KEY
docker compose exec app php artisan key:generate

# Set admin token in .env
# ADMIN_API_TOKEN=your-secure-token-here
```

### 4. Run Migrations
```bash
docker compose exec app php artisan migrate

# Optional: Load sample data
docker compose exec app php artisan db:seed
```

### 5. Access
- **Public Voting UI**: http://localhost:8080
- **Admin Login**: http://localhost:8080/admin/login
- **API**: http://localhost:8080/api/v1

---

## 🔑 Admin Access

1. Go to http://localhost:8080/admin/login
2. Enter your `ADMIN_API_TOKEN` from `.env`
3. You'll be redirected to the Admin Dashboard

**Default Token** (from `.env.example`):
```
ADMIN_API_TOKEN=change-this-secure-token-in-production
```

---

## 🐙 GitHub Integration Setup

### Example: Public Repository (No Token Needed)

**Test Repository**: `NewBorders/galactic-tycoon-calculator`

1. Go to **Admin → Create Project**
2. Fill in:
   - **Name**: Galactic Tycoon Calculator
   - **Slug**: galactic-tycoon-calculator
   - **GitHub Owner**: NewBorders
   - **Repository Name**: galactic-tycoon-calculator
   - **GitHub Token**: (leave empty for public repos)
3. Click **Create Project**
4. System automatically syncs all open issues
5. Users can now vote on the imported features!

### For Private Repositories

1. Create a GitHub Personal Access Token:
   - Go to https://github.com/settings/tokens
   - Click **Generate new token (classic)**
   - Select scopes: `repo` (for private repos)
   - Copy the token

2. **Per-Project Token** (recommended):
   - Add token when creating/editing the project
   
3. **Global Token** (for all projects):
   - Set in `.env`: `GITHUB_TOKEN=your-token`

---

## 📊 Workflows

### User Story: Anonymous User Votes
1. User visits http://localhost:8080
2. Selects a project
3. Sees feature list (top voted first)
4. Clicks upvote button → Vote is saved, client_id stored in localStorage
5. Can remove vote anytime by clicking again

### User Story: Suggesting New Features
1. User goes to project page
2. Clicks "Create GitHub Issue" button
3. Gets redirected to GitHub to create an issue
4. Admin syncs issues in Admin Panel
5. New issue appears as votable feature

### Admin Story: Import GitHub Issues
1. Admin logs in
2. Creates new project with GitHub configuration
3. System auto-syncs issues on project creation
4. Admin can manually re-sync anytime with "Sync GitHub" button
5. Admin can change feature status (planned, in_progress, done)
6. Community sees features and votes

---

## 🗂️ Database Schema

### projects
- `id`, `name`, `slug` (unique)
- `description`, `is_active`
- `github_owner` (**required**), `github_repo` (**required**)
- `github_token` (optional), `auto_sync`

### features
- `id`, `project_id` (FK)
- `title`, `slug`, `description`, `status`, `vote_count`
- `github_issue_number`, `github_issue_url`
- `meta` (JSON)

### votes
- `id`, `feature_id` (FK), `client_id`
- Unique constraint: `(feature_id, client_id)`

---

## 🎨 Key Files

### Backend Core
- `app/Models/Project.php` - Project Model with GitHub fields
- `app/Models/Feature.php` - Feature Model with voting logic
- `app/Services/GitHubService.php` - GitHub API Integration
- `app/Http/Controllers/Web/AdminController.php` - Admin logic
- `app/Http/Controllers/Web/VotingController.php` - Public voting

### Views
- `resources/views/voting/index.blade.php` - Project overview
- `resources/views/voting/show.blade.php` - Feature list with voting
- `resources/views/admin/index.blade.php` - Admin dashboard
- `resources/views/admin/projects/create.blade.php` - Create project
- `resources/views/admin/projects/edit.blade.php` - Edit + GitHub sync

### Routes
- `routes/web.php` - Web UI Routes (Voting + Admin)
- `routes/api.php` - REST API Routes

### Config
- `config/services.php` - Admin Token + GitHub Token
- `.env.example` - Example configuration

---

## 🔧 API Endpoints (Still Available)

### Public API
- `GET /api/v1/projects` - List all projects
- `GET /api/v1/projects/{slug}/features` - Features of a project
- `POST /api/v1/features/{id}/vote` - Cast a vote
- `DELETE /api/v1/features/{id}/vote` - Remove vote

### Admin API (Token in Header: `X-Admin-Token`)
- `POST /api/v1/admin/projects` - Create project
- `PATCH /api/v1/admin/projects/{id}` - Update project
- `PATCH /api/v1/admin/features/{id}` - Change feature status
- `DELETE /api/v1/admin/features/{id}` - Delete feature
- `GET /api/v1/admin/stats` - Statistics

---

## 🧪 Testing

### Manual Test Flow

```bash
# 1. Create project via Admin UI
# - Login at http://localhost:8080/admin/login
# - Create project with GitHub repo
# - System auto-syncs issues

# 2. Vote on features
# - Go to http://localhost:8080
# - Click on a project
# - Upvote features
# - Check that vote count increases

# 3. GitHub Issue → Feature
# - Create new issue on GitHub
# - Click "Sync GitHub" in Admin Panel
# - New issue appears as feature
```

### Automated Tests
```bash
# Run all tests
docker compose exec app php artisan test

# Specific tests
docker compose exec app php artisan test --filter=VotingTest
docker compose exec app php artisan test --filter=AdminAuthenticationTest
```

---

## 🐛 Known Limitations & Future Ideas

### ✅ Completed
- ✅ HTMX-based UI
- ✅ GitHub Issue Import
- ✅ Admin Dashboard
- ✅ Anonymous Voting with Duplicate Prevention
- ✅ Simplified workflow (GitHub Issues only)

### 🔄 Not Yet Implemented (Nice-to-have)
- ⏳ **Auto-Sync** (Scheduled job for regular GitHub sync)
- ⏳ **Webhook Support** (GitHub webhook for automatic updates)
- ⏳ **Email Notifications** (Notify admin on new features)
- ⏳ **Feature Comments** (Discussion threads)
- ⏳ **GitHub Labels**: Import tags/labels from issues

---

## 🎯 Next Steps

### Production Deployment
1. Change `ADMIN_API_TOKEN` to a secure value
2. Set `APP_DEBUG=false` and `APP_ENV=production`
3. Configure Nginx/Caddy reverse proxy with SSL
4. Optional: Set `GITHUB_TOKEN` for all projects

### Enable Auto-Sync (Optional)
```php
// In app/Console/Kernel.php:
protected function schedule(Schedule $schedule): void
{
    $schedule->call(function () {
        $projects = Project::where('auto_sync', true)
            ->where('github_repo', '!=', null)
            ->get();
        
        foreach ($projects as $project) {
            app(GitHubService::class)->syncIssues($project);
        }
    })->hourly();
}
```

---

## 💡 Important Notes

- **No Token Needed** for public repositories
- **Features are read-only**: Users cannot create features directly - they must create GitHub issues
- **Admin controls status**: Only admins can change feature status (submitted → planned → done)
- **Voting is persistent**: Client IDs are stored in localStorage and survive browser restarts
- **Rate Limiting**: GitHub API has rate limits (60 requests/hour without token, 5000 with token)

---

**Date**: November 24, 2025  
**Status**: ✅ Fully Functional  
**Version**: 1.0.0 with UI + Simplified GitHub Integration
