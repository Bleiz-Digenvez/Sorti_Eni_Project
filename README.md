# sorti_Eni_Project

- Projet de formation sortir.com de l'ENI, réalisé par Guillaume Lombard, Baptiste Tielles et Edward Noel
- Ce projet est réalisé sur Symfony 5.4, avec PHP 7.4
- Nous utilisons une base MySQL, géré avec Doctrine

## Pour éxecuter le code
### Dans le terminal
- Utiliser la commande `composer install` pour installer les dépendances
- Utiliser la commande `php bin/console doctrine:database:create` pour créer la base de données
- Utiliser la commande `php bin/console doctrine:database:update --force` pour créer toute les table

### Dans PhpMyAdmin
- Dans le dossier fichierBDD à la racine du projet il y a le script de création (Inutile avec les commandes)
- Dans ce même dossier, il faut éxecuter le script JeuDonnees.sql dans la Base de données (OBLIGATOIRE pour le fonctionnement des tests)
- L'utilisateur Administrateur sera crée, il suffit ensuite de vous connecter avec le pseudo admin, et le mot de passe Password
- Pour des raisons de sécurité, après la premiere connexion, nous vous conseillons de changer de mot de passe 

### Parametrage du server
- Pour éxécuter le plus facilement possible l'application merci de créer deux virtual Host : sortir.com & m.sortir.com
- Une fois cela fait vous pouvez accéder à la partie desktop et la partie Mobile de l'application
