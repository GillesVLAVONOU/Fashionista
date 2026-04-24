# 🎨 Fashionista University — Guide d'installation

Plateforme web universitaire dédiée à la mode, permettant aux étudiants de publier leurs créations, exposer leur portfolio et participer à des événements.

---

## 📋 Prérequis

- **XAMPP** (recommandé) ou WAMP / LAMP / MAMP
- PHP 7.4+ avec extensions : `pdo_mysql`, `fileinfo`, `mbstring`
- MySQL 5.7+ ou MariaDB 10.3+
- Apache avec `mod_rewrite` activé

---

## 🚀 Installation en 5 étapes

### 1. Copier le projet
Déplacez le dossier `fashionista/` dans :
```
C:\xampp\htdocs\fashionista\     (Windows)
/var/www/html/fashionista/        (Linux)
/Applications/XAMPP/htdocs/fashionista/  (macOS)
```

### 2. Importer la base de données
1. Démarrez **Apache** et **MySQL** via le panneau XAMPP
2. Ouvrez **phpMyAdmin** → `http://localhost/phpmyadmin`
3. Cliquez sur **Importer** → Choisissez le fichier `database.sql`
4. Cliquez sur **Exécuter**

### 3. Configurer la connexion
Editez `config/database.php` si nécessaire :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fashionista_db');
define('DB_USER', 'root');      // Votre utilisateur MySQL
define('DB_PASS', '');          // Votre mot de passe MySQL
```

### 4. Configurer l'URL du site
Editez `config/config.php` :
```php
define('SITE_URL', 'http://localhost/fashionista');
```

### 5. Permissions des dossiers uploads
Assurez-vous que ces dossiers sont accessibles en écriture :
```
uploads/posts/
uploads/avatars/
```
Sur Linux/macOS :
```bash
chmod -R 755 uploads/
```

---

## 🔑 Comptes de démonstration

| Rôle      | Email                      | Mot de passe |
|-----------|---------------------------|--------------|
| Admin     | admin@fashionista.edu      | password     |
| Étudiant  | sofia@fashionista.edu      | password     |
| Étudiant  | lucas@fashionista.edu      | password     |

---

## 📁 Structure du projet

```
fashionista/
├── assets/
│   ├── css/style.css          # Styles principaux
│   ├── js/main.js             # JavaScript (AJAX, interactions)
│   └── images/                # Placeholders par défaut
├── config/
│   ├── config.php             # Configuration globale
│   └── database.php           # Connexion PDO
├── controllers/
│   ├── auth_controller.php    # Login/Register
│   ├── like_controller.php    # AJAX likes
│   ├── comment_controller.php # AJAX commentaires
│   ├── event_controller.php   # AJAX participation
│   └── notification_controller.php
├── includes/
│   ├── auth.php               # Fonctions de session
│   ├── functions.php          # Utilitaires globaux
│   ├── header.php             # Navbar + HTML head
│   └── footer.php             # Footer + scripts
├── models/
│   ├── User.php               # Accès données utilisateurs
│   ├── Post.php               # Accès données publications
│   ├── Event.php              # Accès données événements
│   └── Notification.php       # Accès données notifications
├── views/
│   └── post_card.php          # Composant carte post
├── uploads/
│   ├── posts/                 # Images des créations
│   └── avatars/               # Photos de profil
├── index.php                  # Feed principal
├── login.php                  # Connexion
├── register.php               # Inscription
├── dashboard.php              # Tableau de bord
├── profile.php                # Profil utilisateur
├── create_post.php            # Publier une création
├── post_detail.php            # Détail d'une publication
├── events.php                 # Liste des événements
├── event_detail.php           # Détail d'un événement
├── edit_profile.php           # Modifier son profil
├── notifications.php          # Centre de notifications
├── search.php                 # Recherche
├── delete_post.php            # Supprimer une publication
├── logout.php                 # Déconnexion
└── database.sql               # Schéma + données de démo
```

---

## ✨ Fonctionnalités

- ✅ Authentification sécurisée (bcrypt, sessions, CSRF)
- ✅ Publication de créations (image + titre + description + catégorie)
- ✅ Feed style Instagram avec pagination
- ✅ Likes et commentaires en AJAX (sans rechargement de page)
- ✅ Profil utilisateur avec galerie
- ✅ Événements (inscription AJAX avec limite de places)
- ✅ Dashboard avec statistiques
- ✅ Notifications en temps réel
- ✅ Recherche créations + étudiants
- ✅ Upload d'images sécurisé (validation MIME + extension)
- ✅ Protection XSS avec `htmlspecialchars`
- ✅ Requêtes préparées PDO (anti-injection SQL)
- ✅ Design responsive (Bootstrap 5 + CSS custom)
- ✅ Barre de recherche avec filtres par catégorie

---

## 🛡️ Sécurité

- **Mots de passe** : bcrypt avec cost factor 12
- **Sessions** : Régénération d'ID à la connexion
- **XSS** : `htmlspecialchars()` sur toutes les sorties
- **SQL Injection** : Requêtes préparées PDO exclusivement
- **CSRF** : Token validé sur tous les formulaires
- **Upload** : Validation MIME via `finfo`, extension whitelist
- **PHP dans uploads** : Bloqué via `.htaccess`
- **Directory listing** : Désactivé via `.htaccess`

---

## 🎨 Technologies

| Composant   | Technologie                  |
|-------------|------------------------------|
| Backend     | PHP 7.4+ (procédural / MVC léger) |
| Base de données | MySQL / MariaDB + PDO   |
| Frontend    | HTML5, CSS3, Bootstrap 5.3   |
| Icons       | Bootstrap Icons 1.11         |
| Fonts       | Google Fonts (Playfair Display + Inter) |
| JS          | Vanilla JavaScript (AJAX fetch) |

---

## 📞 Support

Accédez à la plateforme : `http://localhost/fashionista`
