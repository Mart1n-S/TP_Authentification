# Projet d'authentification

Ce projet Symfony est configuré pour être exécuté dans des conteneurs Docker avec une base de données MySQL et une interface PhpMyAdmin pour la gestion de la base de données. Ce README explique comment construire et exécuter votre environnement de développement.

## Prérequis 🛠️

Assurez-vous d'avoir installé les éléments suivants sur votre machine :

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/)

## Configuration ⚙️

1. **Cloner le projet :**

   ```bash
   git clone <url-du-repo>
   cd <nom-du-repo>
2. **Configurer le fichier .env.local**

    Créer le fichier .env.local à la racine du projet pour définir vos informations de connexion à la base de données :
    ```bash
    MYSQL_DATABASE=app
    MYSQL_USER=user
    MYSQL_PASSWORD=password
    MYSQL_ROOT_PASSWORD=password_root
    MYSQL_SERVEUR_VERSION=X.X.X

    DATABASE_URL=mysql://${MYSQL_USER}:${MYSQL_PASSWORD}@mysql:3306/${MYSQL_DATABASE}?serverVersion=${MYSQL_SERVEUR_VERSION}
    ```
    Générez un nouveau APP_SECRET en exécutant la commande suivante : 
    ```bash
    php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"
    ```

    Puis, copiez la valeur obtenue dans votre fichier .env.local : 
    ```bash
    APP_SECRET=XXXXXX
    ```

    Remarque : Assurez-vous de garder ce fichier hors de votre dépôt Git en l'ajoutant à votre .gitignore.

## Construction et exécution des conteneurs 🐋

1. **Construire les conteneurs :**
   
   Exécutez la commande suivante pour construire les conteneurs :
    ```bash
   docker-compose up -d
   ```

2. **Vérifier l'état des conteneurs :**
   
   Vous pouvez vérifier que vos conteneurs fonctionnent correctement avec :
   ```bash
   docker-compose ps
   ```

3. **Accéder à l'application Symfony 🎼:**


   Exécutez la commande suivante pour lancer votre serveur Symfony :
    ```bash
   symfony serve -d
   ```
   
   Puis accéder à votre application Symfony en ouvrant votre navigateur et en allant à l'adresse suivante :

   - https://127.0.0.1:8000/
   
4. **Accéder à PhpMyAdmin 💻:**
   
   Pour gérer votre base de données via PhpMyAdmin, ouvrez votre navigateur et allez à :
   - http://localhost:8080/

5. **Accéder à MailPit 📧:**
   
   Pour accéder à MailPit pour tester l'authentification à 2 facteurs ou réinitialiser le mot de passe : 
   - http://localhost:8025/