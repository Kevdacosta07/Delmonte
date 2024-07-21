## Prérequis

- Lien d'installation [PHP](https://www.php.net/downloads.php)
- Lien d'installation [GIT](https://git-scm.com/downloads)
- Lien d'installation [SYMFONY](https://symfony.com/doc/current/setup.html)

### Assurez-vous d'avoir les éléments suivants installés sur votre système
    PHP (vérifiez avec > php -v)
    Symfony (vérifiez avec > symfony check:requirements)
    Git (vérifiez avec > git --version)

## Installer les extensions PHP
Pour activer des extensions PHP, vous devez vous rendre dans votre fichier php.ini, dans ce même fichier vous trouverez un peu plus bas des extensions écrite sous cette forme :
> ;extension:pdo_mysql

Il vous suffit d'enlever le ";" au début du mot "extension" pour activer l'extension n'oubliez pas d'enregistrer le fichier avant de le fermer.

Extensions à activer :
>    fileinfo / openssl / pdo_mysql / curl

## Étapes d'installation
### Clonez le dépôt
    git clone git@github.com:Kevdacosta07/Delmonte.git (Nom du dossier)

Ensuite accédez au répertoire du projet (Avec le terminal)


### Installation des dépendances côté backend & frontend
> composer install

## Configuration du serveur
Modifiez le fichier **.env** présent dans le dossier avec vos paramètres spécifiques, tels que la configuration de la base de données (Chemin vers le fichier : **Delmonte/.env**).


## Lancement de l'application
### Lancez le serveur Symfony
    symfony server:start
L'application devrait maintenant être accessible à l'adresse http://localhost:8000/ dans votre navigateur internet.


### Pour stopper le serveur Symfony
    symfony server:stop

### Migrer les tables vers votre base de donnée
    php bin/console make:migration
    php bin/console d:m:m

### En cas d'erreur
En cas de problème, supprimez tous les fichiers présent dans **migrations** puis réessayez, il est possible qu'une erreur s'affiche mais que les tables ont été créées avec succès donc vérifiez également la base de donnée, si toutes les tables ont été installées, vous devriez avoir minimum 6 tables.

## Configuration de l'email
Rendez-vous dans le **.env** vous pourrez configurer les champs suivants :

> MESSENGER_TRANSPORT_DSN
> 
> MAILER_DSN

Cependant je vous recommande de laisser le MESSENGER_TRANSPORT_DSN par défaut et de modifier uniquement le MAILER_DSN, l'application a été conçue uniquement en phase de test vous pouvez donc ajouter uniquement des e-mail provenant de [MAILTRAP](https://mailtrap.io/)

## Compte utilisateurs
Pour générer les comptes utilisateurs veuillez-vous rendre à cette adresse une fois le site lancé
> http://localhost/8fhskru2jsk

### Compte administrateur
    email: admin@mail.ch
    mot de passe: password

### Compte non administrateur
    email: user@mail.ch
    mot de passe: password


## Pannel Administrateur
Le pannel administrateur est seulement disponible au format ordinateur.