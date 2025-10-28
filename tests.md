# URLs de Test pour l'API GestionCompte

## Environnements
- **Local** : http://localhost:8000
- **Production** : https://abdoulaye.diallo.api

## Endpoints

### Gestion des Comptes

#### Lister tous les comptes
- **Local** : `http://localhost:8000/api/v1/comptes`
- **Production** : `https://abdoulaye.diallo.api/api/v1/comptes`
- **Méthode** : GET
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

#### Créer un compte
- **Local** : `http://localhost:8000/api/v1/accounts`
- **Production** : `https://abdoulaye.diallo.api/api/v1/accounts`
- **Méthode** : POST
- **Headers** :
  ```
  Content-Type: application/json
  Authorization: Bearer {votre_token}
  ```
- **Body** :
  ```json
  {
    "user_id": "uuid",
    "type": "epargne|cheque",
    "solde": 1000,
    "devise": "FCFA"
  }
  ```

#### Mes Comptes (Comptes de l'utilisateur connecté)
- **Local** : `http://localhost:8000/api/v1/comptes/mes-comptes`
- **Production** : `https://abdoulaye.diallo.api/api/v1/comptes/mes-comptes`
- **Méthode** : GET
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

#### Bloquer un compte par numéro
- **Local** : `http://localhost:8000/api/v1/comptes/numero/{numero}/bloquer`
- **Production** : `https://abdoulaye.diallo.api/api/v1/comptes/numero/{numero}/bloquer`
- **Méthode** : POST
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

#### Supprimer un compte
- **Local** : `http://localhost:8000/api/v1/comptes/{compteId}`
- **Production** : `https://abdoulaye.diallo.api/api/v1/comptes/{compteId}`
- **Méthode** : DELETE
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

#### Bloquer un compte
- **Local** : `http://localhost:8000/api/v1/comptes/{compte}/bloquer`
- **Production** : `https://abdoulaye.diallo.api/api/v1/comptes/{compte}/bloquer`
- **Méthode** : POST
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

#### Débloquer un compte
- **Local** : `http://localhost:8000/api/v1/comptes/{compte}/debloquer`
- **Production** : `https://abdoulaye.diallo.api/api/v1/comptes/{compte}/debloquer`
- **Méthode** : POST
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

#### Mettre à jour un compte
- **Local** : `http://localhost:8000/api/v1/comptes/{identifiant}`
- **Production** : `https://abdoulaye.diallo.api/api/v1/comptes/{identifiant}`
- **Méthode** : PATCH
- **Headers** :
  ```
  Content-Type: application/json
  Authorization: Bearer {votre_token}
  ```
- **Body** :
  ```json
  {
    "solde": 2000,
    "statut": "actif"
  }
  ```

#### Archiver un compte
- **Local** : `http://localhost:8000/api/v1/comptes/{id}/archive`
- **Production** : `https://abdoulaye.diallo.api/api/v1/comptes/{id}/archive`
- **Méthode** : POST
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

#### Obtenir un compte par numéro
- **Local** : `http://localhost:8000/api/v1/comptes/{numeroCompte}`
- **Production** : `https://abdoulaye.diallo.api/api/v1/comptes/{numeroCompte}`
- **Méthode** : GET
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

### Gestion des Utilisateurs

#### Lister les administrateurs
- **Local** : `http://localhost:8000/api/v1/users/admins`
- **Production** : `https://abdoulaye.diallo.api/api/v1/users/admins`
- **Méthode** : GET
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

#### Lister les clients
- **Local** : `http://localhost:8000/api/v1/users/clients`
- **Production** : `https://abdoulaye.diallo.api/api/v1/users/clients`
- **Méthode** : GET
- **Headers** :
  ```
  Authorization: Bearer {votre_token}
  ```

### Messages

#### Envoyer un message
- **Local** : `http://localhost:8000/api/v1/messages`
- **Production** : `https://abdoulaye.diallo.api/api/v1/messages`
- **Méthode** : POST
- **Headers** :
  ```
  Content-Type: application/json
  Authorization: Bearer {votre_token}
  ```
- **Body** :
  ```json
  {
    "destinataire": "user_id",
    "contenu": "Votre message ici"
  }
  ```

### Santé de l'API

#### Vérifier l'état de l'API
- **Local** : `http://localhost:8000/api/v1/health`
- **Production** : `https://abdoulaye.diallo.api/api/v1/health`
- **Méthode** : GET

## Notes importantes

1. Remplacez `{votre_token}` par un token d'authentification valide
2. Remplacez les valeurs entre accolades (`{}`) par les valeurs réelles
3. Tous les endpoints (sauf /health) nécessitent une authentification
4. Les réponses sont au format JSON
5. En cas d'erreur, vérifiez :
   - Le token d'authentification
   - Le format des données envoyées
   - Les permissions de l'utilisateur

## Codes de statut HTTP

- 200 : Succès
- 201 : Création réussie
- 400 : Requête invalide
- 401 : Non authentifié
- 403 : Non autorisé
- 404 : Ressource non trouvée
- 422 : Erreur de validation
- 500 : Erreur serveur