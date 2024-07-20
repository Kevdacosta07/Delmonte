Vous pouvez essayer de créer un dossie# Stock Wizz
Ce projet a été réalisé dans le cadre d'un projet scolaire informatique, visant à renforcer les connaissances avec les technologies React & Node JS ainsi que toutes les librairies compatibles.


## Prérequis

- Lien d'installation [PHP](https://nodejs.org/en/download)
- Lien d'installation [GIT](https://git-scm.com/downloads)
- Lien d'installation [SYMFONY](https://symfony.com/doc/current/setup.html)

### Assurez-vous d'avoir les éléments suivants installés sur votre système
    PHP (vérifiez avec > php -v)
    Symfony (vérifiez avec > symfony check:requirements)
    Git (vérifiez avec > git --version)

### Extension PHP à installer (fichier php.ini qui se trouve dans votre dossier php)
>    fileinfo / openssl / pdo_mysql / curl

## Étapes d'installation
### Clonez le dépôt
    git clone git@github.com:Kevdacosta07/Delmonte.git (Nom du dossier)

### Accédez au répertoire du projet (Avec le terminal)
Vous pouvez essayer de créer un dossier
cd Delmonte


### Installation des dépendances côté backend & frontend
> composer install (Commande terminal)

## Configuration du serveur
Modifiez le fichier **.env** présent dans le dossier avec vos paramètres spécifiques, tels que la configuration de la base de données (Chemin vers le fichier : **Delmonte/backend/.env**).


## Lancement de l'application
### Lancez le serveur Symfony
    symfony server:start
L'application devrait maintenant être accessible à l'adresse http://localhost:8000/ dans votre navigateur internet.


### Pour stopper le serveur Symfony
    symfony server:stop

### Migrer les tables vers votre base de donnée
    php bin/console make:migration
    php bin/console d:m:m

## Compte utilisateurs
Pour générer les comptes utilisateurs veuillez-vous rendre à cette adresse une fois le site lancé
> http://localhost/8fhskru2jsk

### Compte administrateur
    email: admin@mail.ch
    mot de passe: password

### Compte non administrateur
    email: user@mail.ch
    mot de passe: password


## En cas d'erreur
En cas de problème, veuillez créer un dossier appelé **uploads** dans **frontend/public**