Gestion des mémoires universitaires

1. Description

Gestion des mémoires universitaires est une application web développée en PHP permettant aux étudiants de déposer leurs mémoires et aux enseignants de les valider via une plateforme sécurisée.

2. Le système permet :

le dépôt des mémoires en ligne
la validation par les professeurs et le jury
la publication des mémoires validés
la consultation des anciens mémoires
la gestion des commentaires et likes

3. Objectif du projet

L’objectif principal de cette application est de faciliter la gestion, la validation et la publication des mémoires universitaires à travers une plateforme centralisée.

4. Utilisateurs du système

Étudiant

L’étudiant peut :

créer un compte et se connecter
déposer un mémoire
consulter les mémoires publiés
commenter et liker les mémoires
suivre l’état de validation de son mémoire

Professeur

Le professeur peut :

se connecter à l’application
consulter les mémoires soumis
valider ou refuser un mémoire
ajouter des observations
participer au jury

Directeur des Études

Le directeur des études peut :

publier les mémoires validés
modifier ou supprimer une publication
gérer les contenus publiés

5. Fonctionnalités principales

Inscription et connexion des utilisateurs
Dépôt de mémoires (PDF)
Validation des mémoires par le jury
Publication automatique après validation
Consultation des mémoires publiés
Recherche de mémoires
Commentaires et likes
Notifications internes

6. Base de données

Le projet utilise une base de données MySQL contenant principalement les tables suivantes :

etudiants
professeurs
memoires
jury
commentaires
likes
publications
notifications

7. Technologies utilisées

HTML5
CSS3
JavaScript
PHP
MySQL
Git & GitHub

8. Structure du projet

/gestion_memoires
app
controllers
models
views
config
database
public
README.md

9. Installation du projet

Cloner le projet
git clone https://github.com/Keneth-nelDAN/Gestion_memoire.git
Placer dans le serveur local
Copier le projet dans WAMP (www) ou XAMPP (htdocs)
Importer la base de données
Ouvrir phpMyAdmin
Importer le fichier database/gestion_memoires.sql
Configurer la base de données
Modifier le fichier config/database.php

10. Lancer le projet

Accéder à
http://localhost/Gestion_memoire/public/

Collaboration Git

Récupérer le projet
git pull origin main

Envoyer des modifications
git add .
git commit -m "message"
git push origin main

11. Objectif du projet

Ce projet vise à digitaliser la gestion des mémoires académiques afin de faciliter :

le dépôt des travaux
la validation par les professeurs
la consultation des mémoires
la communication entre acteurs académiques