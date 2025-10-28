# 🧪 API Test Links - Gestion des Clients & Comptes

**Base URL:** `https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1`

## 🔑 Authentification Endpoints

### POST - Login
```bash
curl -X POST https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"login": "votre_login", "password": "votre_mot_de_passe"}'
```

### POST - Register
```bash
curl -X POST https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "login": "nouveau_login",
    "password": "mot_de_passe123",
    "nom": "Jean Dupont",
    "nci": "123456789012",
    "email": "jean.dupont@email.com",
    "telephone": "+221771234567",
    "adresse": "Dakar, Sénégal"
  }'
```

### POST - Logout
```bash
curl -X POST https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/logout \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

## 👤 Users Endpoints

### GET - List Users
```bash
curl -X GET "https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/users?page=1&limit=10&role=client" \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

### POST - Create User
```bash
curl -X POST https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/users \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Marie Sow",
    "nci": "987654321098",
    "email": "marie.sow@email.com",
    "telephone": "+221782345678",
    "adresse": "Thiès, Sénégal",
    "role_id": "uuid_du_role"
  }'
```

### GET - Get User by ID
```bash
curl -X GET https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/users/{user_id} \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

### PATCH - Update User
```bash
curl -X PATCH https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/users/{user_id} \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Marie Sow Junior",
    "telephone": "+221783456789"
  }'
```

### DELETE - Delete User
```bash
curl -X DELETE https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/users/{user_id} \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

## 💳 Comptes Endpoints

### GET - List Comptes
```bash
curl -X GET "https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/comptes?page=1&limit=10&type=epargne&statut=actif" \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

### POST - Create Compte
```bash
curl -X POST https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/comptes \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": "uuid_utilisateur",
    "type": "epargne",
    "solde": 100000,
    "devise": "FCFA",
    "statut": "actif"
  }'
```

### GET - Get Compte by ID
```bash
curl -X GET https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/comptes/{compte_id} \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

### PATCH - Update Compte (avec blocage)
```bash
# Bloquer un compte avec date d'expiration
curl -X PATCH https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/comptes/{compte_id} \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "statut": "bloque",
    "date_fin_blocage": "2025-10-27T12:00:00Z",
    "metadonnees": {
      "derniereModification": "2025-10-27T09:00:00Z",
      "version": 2,
      "raisonBlocage": "Suspicion d'activité frauduleuse"
    }
  }'
```

### PATCH - Update Compte (simple)
```bash
curl -X PATCH https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/comptes/{compte_id} \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "solde": 150000,
    "statut": "actif",
    "metadonnees": {
      "derniereModification": "2025-10-27T09:00:00Z",
      "version": 2
    }
  }'
```

### DELETE - Archiver Compte
```bash
curl -X DELETE https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/comptes/{compte_id} \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

## 📦 Archives Endpoints

### GET - List Comptes Archivés
```bash
curl -X GET "https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/comptes/archives?page=1&limit=10&statut=bloque&raisonArchivage=Blocage%20expiré" \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

### POST - Restaurer Compte Archivé
```bash
curl -X POST https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1/comptes/{compte_id}/restaurer \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

### Test avec Postman - Archives

#### Collection Postman: "Gestion Comptes Archivage"

**Variables de Collection:**
- `base_url`: `https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1`
- `token`: (sera défini après login)

**Requêtes d'Archives:**

1. **Lister Comptes Archivés**
   - Method: `GET`
   - URL: `{{base_url}}/comptes/archives?page=1&limit=10&statut=bloque`
   - Headers: `Authorization: Bearer {{token}}`

2. **Restaurer Compte Archivé**
   - Method: `POST`
   - URL: `{{base_url}}/comptes/{{compte_id}}/restaurer`
   - Headers: `Authorization: Bearer {{token}}`
   - Body: (vide)

## 📋 Test Script Automatisé

```bash
#!/bin/bash
BASE_URL="https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1"

# Test d'inscription
echo "Test inscription..."
curl -X POST $BASE_URL/register \
  -H "Content-Type: application/json" \
  -d '{
    "login": "testuser",
    "password": "password123",
    "nom": "Test User",
    "nci": "123456789012",
    "email": "test@example.com",
    "telephone": "+221771234567",
    "adresse": "Test Address"
  }'

# Test connexion
echo "Test connexion..."
TOKEN=$(curl -s -X POST $BASE_URL/login \
  -H "Content-Type: application/json" \
  -d '{
    "login": "testuser",
    "password": "password123"
  }' | jq -r '.data.token')

# Test GET users
echo "Test GET users..."
curl -X GET "$BASE_URL/users?page=1&limit=5" \
  -H "Authorization: Bearer $TOKEN"

# Test GET comptes
echo "Test GET comptes..."
curl -X GET "$BASE_URL/comptes?page=1&limit=5" \
  -H "Authorization: Bearer $TOKEN"

# Test logout
echo "Test logout..."
curl -X POST $BASE_URL/logout \
  -H "Authorization: Bearer $TOKEN"
```

## 🔗 Liens Utiles

- **Swagger UI:** `https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/documentation`
- **API Base:** `https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1`
- **Serveur:** `https://gestioncompte-api.onrender.com`

## 🕒 Jobs de Gestion des Comptes

### Exécuter tous les jobs
```bash
php artisan accounts:run-jobs
```

### Exécuter seulement l'archivage
```bash
php artisan accounts:run-jobs --type=archive
```

### Exécuter seulement le désarchivage
```bash
php artisan accounts:run-jobs --type=unarchive
```

### Vérifier les comptes bloqués expirés
```bash
# Comptes à archiver (bloqués avec date_fin_blocage dépassée)
php artisan tinker --execute="
\App\Models\Compte::where('statut', 'bloque')
    ->whereNotNull('date_fin_blocage')
    ->where('date_fin_blocage', '<=', now())
    ->get()
    ->each(function(\$compte) {
        echo 'Compte à archiver: ' . \$compte->numero_compte . ' - Fin blocage: ' . \$compte->date_fin_blocage . PHP_EOL;
    });
"
```

### Vérifier les comptes archivés à désarchiver
```bash
# Comptes archivés à désarchiver
php artisan tinker --execute="
\App\Models\Compte::onlyTrashed()
    ->where('statut', 'bloque')
    ->whereNotNull('date_fin_blocage')
    ->where('date_fin_blocage', '<=', now()->addDays(30))
    ->get()
    ->each(function(\$compte) {
        echo 'Compte à désarchiver: ' . \$compte->numero_compte . ' - Fin blocage: ' . \$compte->date_fin_blocage . PHP_EOL;
    });
"
```

## 🗂️ Tests d'Archivage Automatique

### Test Complet d'Archivage
```bash
#!/bin/bash
BASE_URL="https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1"

# 1. Connexion admin
echo "🔑 Connexion admin..."
TOKEN=$(curl -s -X POST $BASE_URL/login \
  -H "Content-Type: application/json" \
  -d '{"login": "admin", "password": "admin123"}' | jq -r '.data.token')

if [ "$TOKEN" = "null" ] || [ -z "$TOKEN" ]; then
    echo "❌ Échec de connexion"
    exit 1
fi
echo "✅ Token obtenu: ${TOKEN:0:20}..."

# 2. Créer un compte de test
echo "💳 Création compte de test..."
COMPTE_RESPONSE=$(curl -s -X POST $BASE_URL/comptes \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": "b8b6614c-f597-3aea-a763-1bcb4a555161",
    "type": "epargne",
    "solde": 50000,
    "devise": "FCFA",
    "statut": "actif"
  }')

COMPTE_ID=$(echo $COMPTE_RESPONSE | jq -r '.data.id')
if [ "$COMPTE_ID" = "null" ] || [ -z "$COMPTE_ID" ]; then
    echo "❌ Échec création compte"
    exit 1
fi
echo "✅ Compte créé: $COMPTE_ID"

# 3. Bloquer le compte avec date d'expiration passée
echo "🚫 Blocage du compte avec expiration immédiate..."
curl -X PATCH $BASE_URL/comptes/$COMPTE_ID \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "statut": "bloque",
    "date_fin_blocage": "'$(date -u +%Y-%m-%dT%H:%M:%SZ)'"
  }'

# 4. Vérifier que le compte est bloqué
echo "🔍 Vérification du blocage..."
curl -X GET $BASE_URL/comptes/$COMPTE_ID \
  -H "Authorization: Bearer $TOKEN" | jq '.data.statut'

# 5. Exécuter le job d'archivage
echo "📦 Exécution du job d'archivage..."
php artisan accounts:run-jobs --type=archive

# 6. Vérifier que le compte n'est plus dans la liste active
echo "✅ Vérification que le compte est archivé..."
COMPTES_COUNT=$(curl -s -X GET "$BASE_URL/comptes?page=1&limit=100" \
  -H "Authorization: Bearer $TOKEN" | jq '.data | length')

echo "Nombre de comptes actifs: $COMPTES_COUNT"

# 7. Vérifier dans la base Neon
echo "🗄️ Vérification dans les archives Neon..."
psql 'postgresql://neondb_owner:npg_nmGJz3oHRWV1@ep-cold-flower-ahmlgg4s-pooler.c-3.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require' \
  -c "SELECT id, numero_compte, statut, raison_archivage FROM archived_comptes WHERE id = '$COMPTE_ID';"

echo "🎉 Test d'archivage terminé!"
```

### Test de Restauration
```bash
#!/bin/bash
BASE_URL="https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1"

# Connexion admin
TOKEN=$(curl -s -X POST $BASE_URL/login \
  -H "Content-Type: application/json" \
  -d '{"login": "admin", "password": "admin123"}' | jq -r '.data.token')

# Restaurer un compte archivé (remplacer COMPTE_ID)
curl -X POST $BASE_URL/comptes/COMPTE_ID/restaurer \
  -H "Authorization: Bearer $TOKEN"
```

### Test avec Postman

#### Collection Postman: "Gestion Comptes Archivage"

**Variables de Collection:**
- `base_url`: `https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1`
- `token`: (sera défini après login)

**Requêtes:**

1. **Login Admin**
   - Method: `POST`
   - URL: `{{base_url}}/login`
   - Body:
     ```json
     {
       "login": "admin",
       "password": "admin123"
     }
     ```
   - Test Script:
     ```javascript
     if (pm.response.code === 200) {
         const response = pm.response.json();
         pm.collectionVariables.set("token", response.data.token);
     }
     ```

2. **Créer Compte Test**
   - Method: `POST`
   - URL: `{{base_url}}/comptes`
   - Headers: `Authorization: Bearer {{token}}`
   - Body:
     ```json
     {
       "user_id": "b8b6614c-f597-3aea-a763-1bcb4a555161",
       "type": "epargne",
       "solde": 100000,
       "devise": "FCFA",
       "statut": "actif"
     }
     ```
   - Test Script:
     ```javascript
     if (pm.response.code === 201) {
         const response = pm.response.json();
         pm.collectionVariables.set("compte_id", response.data.id);
     }
     ```

3. **Bloquer Compte**
   - Method: `PATCH`
   - URL: `{{base_url}}/comptes/{{compte_id}}`
   - Headers: `Authorization: Bearer {{token}}`
   - Body:
     ```json
     {
       "statut": "bloque",
       "date_fin_blocage": "2025-10-27T09:15:00Z",
       "metadonnees": {
         "raisonBlocage": "Test archivage automatique"
       }
     }
     ```

4. **Vérifier Blocage**
   - Method: `GET`
   - URL: `{{base_url}}/comptes/{{compte_id}}`
   - Headers: `Authorization: Bearer {{token}}`

5. **Lister Comptes Archivés**
   - Method: `GET`
   - URL: `{{base_url}}/comptes/archives?page=1&limit=10`
   - Headers: `Authorization: Bearer {{token}}`

6. **Restaurer Compte**
   - Method: `POST`
   - URL: `{{base_url}}/comptes/{{compte_id}}/restaurer`
   - Headers: `Authorization: Bearer {{token}}`

## � Notes

- Remplacer `{user_id}` et `{compte_id}` par des UUIDs valides
- Remplacer `VOTRE_TOKEN` par le token obtenu lors de la connexion
- Le serveur doit être démarré avec `php artisan serve`
- Les jobs peuvent être exécutés manuellement ou programmés avec un cron