#!/bin/bash

echo "🔍 Vérification de la configuration Passport..."

# 1. Vérifier les clés Passport
echo "\n📝 Vérification des clés Passport :"
if [ -f "storage/oauth-private.key" ] && [ -f "storage/oauth-public.key" ]; then
    echo "✅ Les clés Passport existent"
else
    echo "❌ Les clés Passport sont manquantes"
fi

# 2. Vérifier les clients OAuth
echo "\n📝 Vérification des clients OAuth :"
php artisan tinker --execute="
\$clients = DB::table('oauth_clients')->get();
echo 'Nombre total de clients: ' . \$clients->count() . \"\n\";
echo 'Personal Access Clients: ' . \$clients->where('personal_access_client', 1)->count() . \"\n\";
echo 'Password Grant Clients: ' . \$clients->where('password_client', 1)->count() . \"\n\";
"

# 3. Tester la création d'un token
echo "\n📝 Test de création de token :"
php artisan tinker --execute="
try {
    \$user = \App\Models\User::first();
    if(!\$user) {
        echo \"❌ Aucun utilisateur trouvé dans la base de données\n\";
    } else {
        \$token = \$user->createToken('Test Token');
        echo \"✅ Token créé avec succès pour l'utilisateur {$user->login}\n\";
        echo \"Token: \" . \$token->accessToken . \"\n\";
    }
} catch(\Exception \$e) {
    echo \"❌ Erreur lors de la création du token: \" . \$e->getMessage() . \"\n\";
}
"

# 4. Vérifier la configuration auth
echo "\n📝 Vérification de la configuration auth :"
if grep -q "'api' => \['driver' => 'passport'" config/auth.php; then
    echo "✅ Configuration auth.php correcte"
else
    echo "❌ Configuration auth.php incorrecte"
fi

echo "\n✨ Vérification terminée"