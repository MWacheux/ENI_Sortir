
## Installation du projet
L'application est écrite en PHP avec le framework Symfony.\
Elle sera exécutée dans un environnement web local ou serveur compatible PHP.\
Il est impératif d'avoir PHP, Composer et un serveur web (ex. Apache ou Nginx) installés pour pouvoir lancer et utiliser l'application.

● **Cloner les repositories (backend et frontend) avec la ligne de commande**\
```
git clone https://github.com/MWacheux/ENI_Sortir.git
```

## configuration
● Installer les dépendances PHP avec Composer
```
symfony composer install
```

**Compiler les assets**
L’application utilise Symfony AssetMapper pour gérer les fichiers front (CSS, JS, images…).
Après avoir cloné le projet et installé les dépendances, lancez la commande suivante :
```
symfony console asset-map:compile
```
👉 Cette commande permet de copier les fichiers du dossier assets/ vers public/.\
C’est nécessaire pour que les ressources (styles, scripts, images) soient accessibles par le navigateur et que l’application s’affiche correctement.


● Créer le fichier .env.dev.local en copiant le .env puis configurer la connexion à la base de données

● Créer la base de données
```
symfony console doctrine:database:create
```

● Lancer les migrations (création et application des tables) :

1. Générer la migration :
```
symfony console make:migration
```
2.Exécuter la migration :
```
symfony console doctrine:migrations:migrate
```
⚠️ En cas d'échec : supprimer les fichiers de migration présents dans le dossier migrations importés lors du clonage, puis relancer les commandes ci-dessus.

## 📦 Chargement des données de test (Fixtures)
Pour remplir la base avec des utilisateurs, sites, villes, lieux et sorties de démonstration, lancer la commande :
```
symfony console doctrine:fixtures:load
```
⚠️ Cette commande efface toutes les données existantes avant d’insérer les nouvelles.

**Contenu généré par les fixtures :**
Sites : Nantes, Rennes, Niort, Quimper, En ligne\
Utilisateurs :\
Admin : admin@test.fr (ROLE_ADMIN)\
Utilisateurs classiques : mwacheux@test.fr, zozo@test.fr, nhervieu@test.fr (ROLE_USER)\
États des sorties : Créée, Ouverte, Clôturée, En cours, Passée, Annulée, Archivée\
Villes : Brest, Caen, Percy, Saint-Herblain\
Lieux : LockQuest (Escape game), L’usine (Paintball), Cinéma Pathé, Vertical Art (Escalade), Bowling\
Sorties : diverses sorties avec différents états (Ouverte, Passée, Archivée, etc.)

## ▶️ Lancer le serveur Symfony
```
symfony serve
```

## Accéder à l’application

- En local (développement) : http://127.0.0.1:8000  
- En production : https://sortir.patateparty.fr


## ✅ Exemple de compte pour tester
**Admin** : \
Email : admin@test.fr\
Mot de passe : azerty\
**Utilisateur classique** : \
Email : mwacheux@test.fr\
Mot de passe : azerty\
Email : zozo@test.fr\
Mot de passe : @Monchatminette44800
