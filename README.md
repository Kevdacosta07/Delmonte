## Prérequis

- Lien d'installation [PHP](https://www.php.net/downloads.php)
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
En cas de problème, supprimez tous les fichiers présent dans **migrations** puis réessayez, il est possible qu'une erreur s'affiche mais que les tables ont été créées avec succès donc vérifiez également la base de donnée, si toutes les tables ont été installées avec succès vous devriez avoir minimum 5 tables. 

## Compte utilisateurs
Pour générer les comptes utilisateurs veuillez-vous rendre à cette adresse une fois le site lancé
> http://localhost/8fhskru2jsk

### Compte administrateur
    email: admin@mail.ch
    mot de passe: password

### Compte non administrateur
    email: user@mail.ch
    mot de passe: password