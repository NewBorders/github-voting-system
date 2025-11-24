# Feature Voting System - Handoff Document

## 🎉 Was wurde umgesetzt

### ✅ Vollständiges Voting-System mit UI
- **HTMX-basierte UI** für schnelles, reaktives Voting ohne Page Reload
- **Anonymes Voting** mit localStorage-basierter client_id (UUID)
- **Duplicate Vote Prevention** durch unique constraint auf (feature_id, client_id)
- **Responsive Design** mit TailwindCSS

### ✅ GitHub Integration
- **Automatischer Issue-Import** aus öffentlichen und privaten GitHub-Repositories
- **Issue-zu-Feature-Sync** mit Tracking von GitHub Issue Numbers
- **Manual Sync Button** im Admin-Panel
- **GitHub Token Support** für private Repos und höhere Rate Limits

### ✅ Admin Dashboard
- **Token-basierte Authentifizierung** (Session + Cookie)
- **Projekt-Management**: Erstellen, Bearbeiten, Aktivieren/Deaktivieren
- **Feature-Management**: Status ändern (submitted → planned → in_progress → done)
- **GitHub-Konfiguration** pro Projekt
- **Statistiken**: Feature-Count, Vote-Count, etc.

### ✅ Public Voting Interface
- **Projekt-Übersicht** mit allen aktiven Projekten
- **Feature-Liste** sortiert nach Votes (Top voted zuerst)
- **Feature einreichen** direkt über die UI
- **Upvote/Unvote** mit sofortiger Aktualisierung (HTMX)
- **GitHub Issue Links** sichtbar bei synchronisierten Features

---

## 🚀 Wie starte ich das System?

### 1. Docker Container starten
```bash
docker compose up -d
```

### 2. Dependencies installieren (falls noch nicht geschehen)
```bash
docker compose exec app composer install
```

### 3. .env Datei konfigurieren
```bash
# Falls noch nicht vorhanden:
cp .env.example .env

# APP_KEY generieren:
docker compose exec app php artisan key:generate

# Admin-Token setzen:
# Bearbeite .env und setze ADMIN_API_TOKEN=dein-sicheres-token
```

### 4. Datenbank migrieren
```bash
docker compose exec app php artisan migrate

# Optional: Beispieldaten laden
docker compose exec app php artisan db:seed
```

### 5. Zugriff
- **Public Voting UI**: http://localhost:8080
- **Admin Login**: http://localhost:8080/admin/login
- **API**: http://localhost:8080/api/v1

---

## 🔑 Admin-Zugang

1. Gehe zu http://localhost:8080/admin/login
2. Gib deinen `ADMIN_API_TOKEN` aus der `.env` ein
3. Du wirst in das Admin-Dashboard weitergeleitet

**Standard-Token** (in `.env.example`):
```
ADMIN_API_TOKEN=change-this-secure-token-in-production
```

---

## 🐙 GitHub Integration einrichten

### Option 1: Öffentliche Repositories (kein Token nötig)
1. Gehe zu **Admin → Projekt erstellen**
2. Fülle die Projekt-Details aus
3. Setze:
   - `GitHub Owner`: z.B. `facebook`
   - `Repository Name`: z.B. `react`
4. Speichere und klicke auf **Sync GitHub**

### Option 2: Private Repositories oder höhere Rate Limits
1. Erstelle einen GitHub Personal Access Token:
   - Gehe zu https://github.com/settings/tokens
   - Klicke **Generate new token (classic)**
   - Wähle Scopes: `repo` (für private Repos) oder nur `public_repo`
   - Kopiere den Token

2. **Pro-Projekt-Token** (empfohlen):
   - Füge den Token beim Projekt unter "GitHub Token" ein
   
3. **Globaler Token** (für alle Projekte):
   - Setze in `.env`: `GITHUB_TOKEN=dein-token`

---

## 📊 Workflows

### User-Story: Anonymer Nutzer votet
1. User besucht http://localhost:8080
2. Wählt ein Projekt aus
3. Sieht Feature-Liste (Top-Voted zuerst)
4. Klickt Upvote-Button → Vote wird gespeichert, client_id in localStorage
5. Kann Vote jederzeit zurückziehen

### Admin-Story: GitHub Issues importieren
1. Admin loggt sich ein
2. Erstellt neues Projekt mit GitHub-Konfiguration
3. Klickt "Sync GitHub"
4. Issues werden als Features importiert
5. Admin kann Status ändern (submitted → planned → done)
6. Community sieht Features und votet

---

## 🗂️ Datenbank-Schema

### projects
- `id`, `name`, `slug` (unique)
- `description`, `is_active`
- `github_owner`, `github_repo`, `github_token`, `auto_sync`

### features
- `id`, `project_id` (FK)
- `title`, `slug`, `description`, `status`, `vote_count`
- `github_issue_number`, `github_issue_url`
- `meta` (JSON)

### votes
- `id`, `feature_id` (FK), `client_id`
- Unique constraint: `(feature_id, client_id)`

---

## 🎨 UI-Komponenten

### Public Views
- `voting/index.blade.php` - Projekt-Übersicht
- `voting/show.blade.php` - Feature-Liste mit Vote-UI
- `voting/partials/feature-item.blade.php` - Einzelnes Feature
- `voting/partials/vote-button.blade.php` - Vote-Button (HTMX)

### Admin Views
- `admin/login.blade.php` - Login-Formular
- `admin/index.blade.php` - Dashboard mit allen Projekten
- `admin/projects/create.blade.php` - Projekt erstellen
- `admin/projects/edit.blade.php` - Projekt bearbeiten + GitHub-Sync
- `admin/features.blade.php` - Feature-Management-Tabelle

### Layout
- `layouts/app.blade.php` - Haupt-Layout mit HTMX + TailwindCSS

---

## 🔧 API Endpoints (weiterhin verfügbar)

### Public API
- `GET /api/v1/projects` - Liste aller Projekte
- `GET /api/v1/projects/{slug}/features` - Features eines Projekts
- `POST /api/v1/projects/{slug}/features` - Feature einreichen
- `POST /api/v1/features/{id}/vote` - Vote abgeben
- `DELETE /api/v1/features/{id}/vote` - Vote zurückziehen

### Admin API (Token in Header: `X-Admin-Token`)
- `POST /api/v1/admin/projects` - Projekt erstellen
- `PATCH /api/v1/admin/projects/{id}` - Projekt aktualisieren
- `PATCH /api/v1/admin/features/{id}` - Feature-Status ändern
- `DELETE /api/v1/admin/features/{id}` - Feature löschen
- `GET /api/v1/admin/stats` - Statistiken

---

## 🧪 Testing

### Manuelle Tests
```bash
# Feature erstellen (Web UI)
# 1. Gehe zu http://localhost:8080/vote/test-project
# 2. Fülle Formular aus
# 3. Submit → Feature wird oben in Liste eingefügt (HTMX)

# Vote abgeben
# 1. Klicke Upvote-Button
# 2. Button wird blau/gefüllt (voted state)
# 3. Vote-Count erhöht sich

# Admin: GitHub Sync
# 1. Login als Admin
# 2. Erstelle Projekt mit GitHub-Repo
# 3. Klicke "Sync GitHub"
# 4. Issues werden als Features importiert
```

### Automated Tests
```bash
# Tests ausführen
docker compose exec app php artisan test

# Spezifische Tests
docker compose exec app php artisan test --filter=VotingTest
docker compose exec app php artisan test --filter=AdminAuthenticationTest
```

---

## 🐛 Known Limitations & TODOs

### ✅ Fertig
- ✅ HTMX-basierte UI
- ✅ GitHub Issue Import
- ✅ Admin Dashboard
- ✅ Anonymes Voting mit Duplicate Prevention

### 🔄 Noch nicht implementiert (Nice-to-have)
- ⏳ **Auto-Sync** (Scheduled Job für regelmäßigen GitHub-Sync)
- ⏳ **Webhook Support** (GitHub Webhook für automatische Updates)
- ⏳ **Email Notifications** (Admin benachrichtigen bei neuen Features)
- ⏳ **Feature Comments** (Diskussion zu Features)
- ⏳ **Tags/Labels** von GitHub Issues übernehmen

---

## 📝 Wichtige Dateien

### Backend Core
- `app/Models/Project.php` - Project Model mit GitHub-Feldern
- `app/Models/Feature.php` - Feature Model mit Vote-Logic
- `app/Services/GitHubService.php` - GitHub API Integration
- `app/Http/Controllers/Web/AdminController.php` - Admin-Logik
- `app/Http/Controllers/Web/VotingController.php` - Public Voting

### Migrations
- `database/migrations/*_add_github_fields_to_projects.php`
- `database/migrations/*_add_github_fields_to_features.php`

### Routes
- `routes/web.php` - Web UI Routes (Voting + Admin)
- `routes/api.php` - REST API Routes

### Config
- `config/services.php` - Admin Token + GitHub Token
- `.env.example` - Beispiel-Konfiguration

---

## 🎯 Nächste Schritte

### Production Deployment
1. Ändere `ADMIN_API_TOKEN` zu einem sicheren Wert
2. Setze `APP_DEBUG=false` und `APP_ENV=production`
3. Konfiguriere Nginx/Caddy Reverse Proxy mit SSL
4. Optional: GitHub Token für alle Projekte in `GITHUB_TOKEN` setzen

### Auto-Sync (optional)
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

## 🙋 Fragen & Support

- **Dokumentation**: Siehe `README.md`, `API_EXAMPLES.md`, `DEPLOYMENT.md`
- **Admin-Token vergessen?**: Schau in `.env` unter `ADMIN_API_TOKEN`
- **GitHub-Sync funktioniert nicht?**: Prüfe Token-Berechtigung und Rate Limits

---

**Datum**: November 24, 2025  
**Status**: ✅ Vollständig funktionsfähig  
**Version**: 1.0.0 mit UI + GitHub Integration
