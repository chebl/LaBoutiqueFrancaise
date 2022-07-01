# LaBoutiqueFrancaise
Commandes Symfony:
1-Creation projet Symfony 5: symfony new my_project_directory --version=5.4 --webapp 
2- Lancement projet : php -S 127.0.0.1:8000 -t public/
3-Creation Controller: symfony console make:controller FilmController
4-Creation Entity: symfony console make:entity Category
5-Create Migration : php bin/console make:migration
6-Lancement du Serveur du BDD: docker-compose up -d




* Les utilisateurs pourront:

 s'inscrire, se connecter, filtrer les produits, les mettre dans le panier, accéder au tunnel d'achat, payer et recevoir les emails de confirmation.

* Les administrateurs pourront:

suivre les commandes, gérer les utilisateurs et les produits à travers une interface dédiée : le backoffice.


