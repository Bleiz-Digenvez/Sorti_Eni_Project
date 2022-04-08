# sorti_Eni_Project

- Projet de formation sortir.com de l'ENI, réalisé par Guillaume Lombard, Baptiste Tielles et Edward Noel
- Ce projet est réalisé sur Symfony 5.4, avec PHP 7.4
- Nous utilisons une base MySQL, géré avec Doctrine

## Pour executer le code
### Dans le terminal
- Utiliser la commande `composer install` pour installer les dépendance
- Utiliser la commande `php bin/console doctrine:database:create` pour créer la base de données
- Utiliser la commande `php bin/console doctrine:database:update --force` pour créer toute les table

### Dans PhpMyAdmin
- Créer un utilisateur avec les droit d'admin, et actif dans la base de données
- Pour le mot de passe vous pouvait utiliser cette chaine : $2y$13$Stn1E.QiL2ZYnJYSHxc6HOOQotfx0xuFdcXGlcJyXCtXNDSTDbeZi  -> qui correspond au mot de passe Password en crypté
- Dans le dossier fichierBDD à la racine du projet il y a le script de création (Unutile avec les commande)
- Dans ce même dossier, il faut éxecuté le script JeuDonnees.sql dans la Base de données (OBLIGATOIRE pour le fonctionnement des test)

### Parametrage du server
- Pour éxécuter le plus facilement possible l'application merci de créer deux virtual Host : sortir.com & m.sortir.com
- Une fois cela fait vous pouvez accéder à la parti desktop et la parti Mobile de l'application
- 
