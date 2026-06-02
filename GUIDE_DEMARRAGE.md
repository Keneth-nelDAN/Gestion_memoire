# 🎯 GénieMémoires - Guide de Démarrage Rapide

## ✅ État du projet

**Tous les fichiers sont prêts !**
- ✅ 0 erreurs de syntaxe PHP
- ✅ Tous les dashboards créés
- ✅ Authentification sécurisée
- ✅ Gestion des anciens mémoires implémentée

---

## 🚀 Démarrer rapidement

### Étape 1: Lancer XAMPP
```bash
sudo /opt/lampp/xampp start
```

Ou via l'interface graphique XAMPP.

### Étape 2: Accès à l'application
```
http://localhost/Gestion_memoire/app/views/auth/connexion.php
```

### Étape 3: Se connecter
**Compte Directeur des Études:**
- Email: `admin@gmail.com`
- Mot de passe: `admin123`
- Rôle: **Directeur**

---

## 📋 Fonctionnalités disponibles

### 🏛️ Dashboard Directeur des Études
**URL:** `/app/views/direction_etude/dashboard_de.php`

- **Tableau de bord** → Statistiques et aperçu
- **Publier des mémoires** → Ajout unitaire ou par lot
  - Formulaire simple pour 1 mémoire
  - Formulaire batch pour 5+ fichiers
  - Validation des PDFs (max 12 Mo)
  - Métadonnées: auteur, filière, année, jury
- **Gestion des professeurs** → Ajouter/modifier professeurs
- **Gestion des étudiants** → Lister et modifier étudiants
- **Notifications** → Alertes et messages

### 👨‍🎓 Dashboard Étudiant
**URL:** `/app/views/etudiant/dashboard_etudiant.php`

- Consulter les mémoires disponibles
- Déposer ses propres mémoires (si compte diplômé)
- Voir ses notifications

### 👨‍🏫 Dashboard Professeur  
**URL:** `/app/views/professeur/dashboard_professeur.php`

- Vue d'ensemble des évaluations
- Mémoires en jury assignés
- Validation des mémoires
- Notifications

---

## 🔐 Sécurité mise en place

✅ **Session Management:**
- Régénération d'ID de session après connexion
- Vérification stricte des rôles (directeur, professeur, étudiant)
- Protection contre les accès non autorisés

✅ **Authentification:**
- Validation des emails
- Vérification des mots de passe (plaintext comme demandé)
- Redirections sécurisées

✅ **Données:**
- Sanitisation des entrées (htmlspecialchars)
- Validation des fichiers (extension PDF, taille)
- Queries SQL préparées (MySQLi prepared statements)

---

## 📂 Structure du projet

```
/opt/lampp/htdocs/Gestion_memoire/
├── app/
│   ├── controllers/      ← Logique métier
│   ├── models/           ← Modèles BD
│   └── views/
│       ├── auth/         ← Connexion/inscription
│       ├── direction_etude/  ← Dashboard DE + publications
│       ├── professeur/   ← Dashboard professeur
│       └── etudiant/     ← Dashboard étudiant
├── config/
│   ├── database.php      ← Classe PDO (PDO)
│   └── mysqli_config.php ← Connexion MySQLi (NEW)
├── public/
│   ├── login.php         ← Traitement connexion
│   ├── logout.php        ← Déconnexion
│   └── assets/
├── database/
│   └── gestion_memoires.sql
└── CORRECTIONS_APPLIQUEES.md ← Détail des changements
```

---

## 🔧 Configuration technique

### Connexion à la base de données
- **Host:** localhost (127.0.0.1)
- **User:** root
- **Password:** (vide par défaut)
- **Database:** gestion_memoires
- **Charset:** utf8mb4

### Fichiers de configuration
- `config/database.php` - PDO (utilisé par authController)
- `config/mysqli_config.php` - MySQLi (utilisé par les vues)

**Note:** Deux systèmes coexistent pour compatibilité. Migration progressive recommandée.

---

## 🧪 Test rapide

```bash
cd /opt/lampp/htdocs/Gestion_memoire

# Vérifier la syntaxe PHP
php -l app/views/direction_etude/dashboard_de.php

# Vérifier la connexion MySQL (une fois XAMPP démarré)
php -r "require_once 'config/mysqli_config.php'; echo 'MySQL OK';"

# Lancer le script de test
bash test_setup.sh
```

---

## 📝 Identifiants de test

**Directeur des Études**
```
Email: admin@gmail.com
Mot de passe: admin123
Type: directeur
```

**Étudiant exemple**
```
Email: nellysdannon@gmail.com
Mot de passe: (vérifier la BD)
Type: etudiant
```

---

## 🐛 Dépannage

### Erreur: "Cannot connect to MySQL"
```
❌ Problème: XAMPP n'est pas lancé
✅ Solution: sudo /opt/lampp/xampp start
```

### Erreur: "Session not found"
```
❌ Problème: Les sessions ne sont pas créées
✅ Solution: Vérifier que les cookies sont activés (F12 → Stockage → Cookies)
```

### Erreur: "Access denied" après connexion
```
❌ Problème: Session expirée ou non persistée
✅ Solution: Effacer les cookies et se reconnecter
```

---

## 🎓 Prochains développements suggérés

1. **Migration PDO/MySQLi** - Unifier sur PDO
2. **Upload fichiers** - Améliorer validation de PDFs
3. **API REST** - Pour mobile/frontend séparé
4. **Tests unitaires** - Couvrir la logique métier
5. **Notifications réelles** - Intégrer email/SMS

---

## 📞 Support

Pour toute question ou problème:
1. Vérifier `CORRECTIONS_APPLIQUEES.md`
2. Lancer `test_setup.sh` pour diagnostiquer
3. Vérifier les logs XAMPP: `/opt/lampp/logs/`

---

**Bonne utilisation !** 🚀
