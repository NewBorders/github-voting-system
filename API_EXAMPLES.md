# API Examples

This document provides curl examples for all API endpoints.

## Public Endpoints

### List all projects

```bash
curl -X GET http://localhost:8080/api/v1/projects
```

### List features for a project

```bash
curl -X GET "http://localhost:8080/api/v1/projects/minecraft-hosting-helper/features?sort=top&limit=10"
```

### Get a single feature

```bash
curl -X GET http://localhost:8080/api/v1/features/1
```

### Create a new feature

```bash
curl -X POST http://localhost:8080/api/v1/projects/minecraft-hosting-helper/features \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Add plugin marketplace",
    "description": "A marketplace for browsing and installing plugins directly from the interface",
    "client_id": "550e8400-e29b-41d4-a716-446655440000"
  }'
```

### Vote for a feature

```bash
curl -X POST http://localhost:8080/api/v1/features/1/vote \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "550e8400-e29b-41d4-a716-446655440000"
  }'
```

### Remove a vote

```bash
curl -X DELETE http://localhost:8080/api/v1/features/1/vote \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "550e8400-e29b-41d4-a716-446655440000"
  }'
```

## Admin Endpoints

Replace `YOUR_ADMIN_TOKEN` with your actual admin token from `.env`.

### Create a project

```bash
curl -X POST http://localhost:8080/api/v1/admin/projects \
  -H "Content-Type: application/json" \
  -H "X-Admin-Token: YOUR_ADMIN_TOKEN" \
  -d '{
    "name": "My New Project",
    "slug": "my-new-project",
    "description": "This is a new project for collecting feature ideas",
    "is_active": true
  }'
```

### Update a project

```bash
curl -X PATCH http://localhost:8080/api/v1/admin/projects/1 \
  -H "Content-Type: application/json" \
  -H "X-Admin-Token: YOUR_ADMIN_TOKEN" \
  -d '{
    "name": "Updated Project Name",
    "is_active": false
  }'
```

### Update a feature status

```bash
curl -X PATCH http://localhost:8080/api/v1/admin/features/1 \
  -H "Content-Type: application/json" \
  -H "X-Admin-Token: YOUR_ADMIN_TOKEN" \
  -d '{
    "status": "accepted"
  }'
```

### Update feature with metadata

```bash
curl -X PATCH http://localhost:8080/api/v1/admin/features/1 \
  -H "Content-Type: application/json" \
  -H "X-Admin-Token: YOUR_ADMIN_TOKEN" \
  -d '{
    "status": "in_progress",
    "meta": {
      "priority": "high",
      "assigned_to": "dev-team-1",
      "tags": ["backend", "performance"],
      "estimated_hours": 40
    }
  }'
```

### Delete a feature

```bash
curl -X DELETE http://localhost:8080/api/v1/admin/features/1 \
  -H "X-Admin-Token: YOUR_ADMIN_TOKEN"
```

### Get statistics

```bash
curl -X GET http://localhost:8080/api/v1/admin/stats \
  -H "X-Admin-Token: YOUR_ADMIN_TOKEN"
```

## Testing Rate Limits

### Test feature submission rate limit (10 per hour)

```bash
for i in {1..12}; do
  echo "Request $i:"
  curl -X POST http://localhost:8080/api/v1/projects/minecraft-hosting-helper/features \
    -H "Content-Type: application/json" \
    -d "{\"title\": \"Test Feature $i\", \"description\": \"Testing rate limit\"}"
  echo ""
done
```

### Test voting rate limit (60 per minute)

```bash
for i in {1..65}; do
  echo "Vote $i:"
  curl -X POST http://localhost:8080/api/v1/features/1/vote \
    -H "Content-Type: application/json" \
    -d "{\"client_id\": \"test-client-$i\"}"
  echo ""
done
```

## Frontend Integration Example (JavaScript)

```javascript
// Generate and store client ID
function getClientId() {
  let clientId = localStorage.getItem('voting_client_id');
  if (!clientId) {
    clientId = crypto.randomUUID();
    localStorage.setItem('voting_client_id', clientId);
  }
  return clientId;
}

// Vote for a feature
async function voteForFeature(featureId) {
  const clientId = getClientId();
  
  const response = await fetch(`http://localhost:8080/api/v1/features/${featureId}/vote`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ client_id: clientId })
  });
  
  const data = await response.json();
  console.log('Vote result:', data);
  return data;
}

// Remove vote
async function removeVote(featureId) {
  const clientId = getClientId();
  
  const response = await fetch(`http://localhost:8080/api/v1/features/${featureId}/vote`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ client_id: clientId })
  });
  
  const data = await response.json();
  console.log('Remove vote result:', data);
  return data;
}

// Create new feature
async function createFeature(projectSlug, title, description) {
  const clientId = getClientId();
  
  const response = await fetch(`http://localhost:8080/api/v1/projects/${projectSlug}/features`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      title,
      description,
      client_id: clientId
    })
  });
  
  const data = await response.json();
  console.log('Feature created:', data);
  return data;
}

// List features
async function listFeatures(projectSlug, options = {}) {
  const params = new URLSearchParams();
  if (options.sort) params.append('sort', options.sort);
  if (options.status) params.append('status', options.status);
  if (options.limit) params.append('limit', options.limit);
  if (options.page) params.append('page', options.page);
  
  const url = `http://localhost:8080/api/v1/projects/${projectSlug}/features?${params}`;
  const response = await fetch(url);
  const data = await response.json();
  
  return data;
}
```

## Error Responses

### Validation Error (422)

```json
{
  "message": "The title field is required. (and 1 more error)",
  "errors": {
    "title": [
      "The title field is required."
    ],
    "client_id": [
      "The client id must be at least 5 characters."
    ]
  }
}
```

### Unauthorized (401)

```json
{
  "message": "Unauthorized"
}
```

### Rate Limit Exceeded (429)

```json
{
  "message": "Too Many Attempts."
}
```

### Not Found (404)

```json
{
  "message": "No query results for model [App\\Models\\Feature] 999"
}
```
