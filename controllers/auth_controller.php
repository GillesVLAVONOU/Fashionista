<?php
// ============================================================
//  controllers/auth_controller.php
//  Handles POST for login & register via redirect
// ============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/index.php');
}

$action = $_POST['action'] ?? '';

// ============================================================
// REGISTER
// ============================================================
if ($action === 'register') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de securite invalide. Veuillez reessayer.');
        redirect(SITE_URL . '/register.php');
    }

    $username  = trim($_POST['username'] ?? '');
    $fullName  = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $bio       = trim($_POST['bio'] ?? '');

    $errors = [];
    if (empty($username) || !preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $errors[] = 'Le pseudo doit comporter 3-50 caracteres (lettres, chiffres, _).';
    }
    if (empty($fullName) || mb_strlen($fullName) < 2) {
        $errors[] = 'Le nom complet est requis.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresse email invalide.';
    }
    if (mb_strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit comporter au moins 8 caracteres.';
    }
    if ($password !== $password2) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if ($errors) {
        setFlash('error', implode('<br>', $errors));
        redirect(SITE_URL . '/register.php');
    }

    $userModel = new User();

    if ($userModel->findByEmail($email)) {
        setFlash('error', 'Cet email est deja utilise.');
        redirect(SITE_URL . '/register.php');
    }

    if ($userModel->findByUsername($username)) {
        setFlash('error', 'Ce pseudo est deja pris.');
        redirect(SITE_URL . '/register.php');
    }

    $avatarFilename = null;
    if (!empty($_FILES['avatar']['name'])) {
        try {
            $avatarFilename = uploadImage($_FILES['avatar'], UPLOAD_AVATARS);
        } catch (RuntimeException $e) {
            setFlash('error', $e->getMessage());
            redirect(SITE_URL . '/register.php');
        }
    }

    $newId = $userModel->create([
        'username'  => $username,
        'full_name' => $fullName,
        'email'     => $email,
        'password'  => $password,
        'bio'       => $bio,
        'avatar'    => $avatarFilename,
    ]);

    $user = $userModel->findById($newId);
    loginUser($user);
    setFlash('success', 'Bienvenue sur Fashionista, ' . $fullName . ' !');
    redirect(SITE_URL . '/dashboard.php');
}

// ============================================================
// LOGIN
// ============================================================
if ($action === 'login') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de securite invalide.');
        redirect(SITE_URL . '/login.php');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('error', 'Veuillez remplir tous les champs.');
        redirect(SITE_URL . '/login.php');
    }

    $userModel = new User();
    $user      = $userModel->findByEmail($email);

    if (!$user || !$userModel->verifyPassword($password, $user['password'])) {
        setFlash('error', 'Email ou mot de passe incorrect.');
        redirect(SITE_URL . '/login.php');
    }

    loginUser($user);

    $redirectTo = $_POST['redirect'] ?? '';
    $safe = filter_var($redirectTo, FILTER_VALIDATE_URL) ? $redirectTo : SITE_URL . '/dashboard.php';
    redirect($safe);
}

// ============================================================
// REQUEST PASSWORD RESET
// ============================================================
if ($action === 'request_reset') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de securite invalide.');
        redirect(SITE_URL . '/forgot_password.php');
    }

    $email = trim($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Veuillez saisir une adresse email valide.');
        redirect(SITE_URL . '/forgot_password.php');
    }

    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if ($user) {
        $resetModel = new PasswordReset();
        $token = $resetModel->createForUser((int)$user['id']);
        $resetLink = SITE_URL . '/reset_password.php?token=' . urlencode($token);
        $sent = sendPasswordResetEmail($user['email'], $user['full_name'], $resetLink);
        if (!$sent) {
            $_SESSION['password_reset_debug_link'] = $resetLink;
        }
    }

    setFlash('success', 'Si un compte existe pour cet email, un lien de reinitialisation a ete genere.');
    redirect(SITE_URL . '/forgot_password.php');
}

// ============================================================
// RESET PASSWORD
// ============================================================
if ($action === 'reset_password') {

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de securite invalide.');
        redirect(SITE_URL . '/forgot_password.php');
    }

    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($token === '') {
        setFlash('error', 'Lien de reinitialisation invalide.');
        redirect(SITE_URL . '/forgot_password.php');
    }

    if (mb_strlen($password) < 8) {
        setFlash('error', 'Le mot de passe doit comporter au moins 8 caracteres.');
        redirect(SITE_URL . '/reset_password.php?token=' . urlencode($token));
    }

    if ($password !== $password2) {
        setFlash('error', 'Les mots de passe ne correspondent pas.');
        redirect(SITE_URL . '/reset_password.php?token=' . urlencode($token));
    }

    $resetModel = new PasswordReset();
    $resetRow = $resetModel->findValidToken($token);

    if (!$resetRow) {
        setFlash('error', 'Ce lien de reinitialisation est invalide ou expire.');
        redirect(SITE_URL . '/forgot_password.php');
    }

    $userModel = new User();
    $userModel->updatePassword((int)$resetRow['user_id'], $password);
    $resetModel->markUsed((int)$resetRow['id']);

    setFlash('success', 'Votre mot de passe a ete reinitialise. Vous pouvez maintenant vous connecter.');
    redirect(SITE_URL . '/login.php');
}

redirect(SITE_URL . '/index.php');
