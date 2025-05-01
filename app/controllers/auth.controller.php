<?php
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\ENUM\VALIDATOR\VALIDATORMETHODE;
use App\ENUM\ERREUR\ErreurEnum;
use App\Models\AUTHMETHODE;
use app\Models\JSONMETHODE;
use App\ENUM\MESSAGE\MSGENUM;

require_once CheminPage::AUTH_MODEL->value;
require_once CheminPage::SESSION_SERVICE->value;
require_once CheminPage::MESSAGE_FR->value;
require_once CheminPage::VALIDATOR_SERVICE->value;

demarrer_session();

// === FONCTIONS DE ROUTAGE ===

/**
 * Affiche la page de connexion ou traite le formulaire de connexion
 */
function voir_page_login(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        traiter_connexion();
    } else {
        afficher_page_login();
    }
}

/**
 * Affiche la page de réinitialisation de mot de passe ou traite le formulaire
 */
function voir_page_reset_password(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        traiter_reset_password();
    } else {
        afficher_page_reset_password();
    }
}

// === FONCTIONS D'AFFICHAGE ===

/**
 * Affiche la page de connexion
 */
function afficher_page_login(): void {
    render('login/login', [], layout: null);
}

/**
 * Affiche la page de réinitialisation de mot de passe
 */
function afficher_page_reset_password(): void {
    render('login/reset_password', [], layout: null);
}

// === FONCTIONS DE TRAITEMENT DES FORMULAIRES ===

/**
 * Récupère les données du formulaire de connexion
 */
function get_login_form_data(): array {
    return [
        'login' => $_POST['login'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
}

/**
 * Valide les données du formulaire de connexion
 */
function validate_login_form(array $formData): array {
    global $validator;
    return $validator[VALIDATORMETHODE::USER->value]($formData['login'], $formData['password']);
}

/**
 * Authentifie un utilisateur
 */
function authenticate_user(string $login, string $password): ?array {
    global $auth_model;
    $chemin_data = CheminPage::DATA_JSON->value;
    return $auth_model[AUTHMETHODE::LOGIN->value]($login, $password, $chemin_data);
}

/**
 * Stocke les informations de l'utilisateur en session
 */
function store_user_in_session(array $user): void {
    stocker_session('user', $user);
    demarrer_session();
}

/**
 * Traite le formulaire de connexion
 */
function traiter_connexion(): void {
    $formData = get_login_form_data();
    $erreurs = validate_login_form($formData);
    
    if (!empty($erreurs)) {
        stocker_session('errors', $erreurs);
        afficher_page_login();
        return;
    }

    $user = authenticate_user($formData['login'], $formData['password']);

    if ($user) {
        store_user_in_session($user);
        redirect_to_route('index.php', ['page' => 'liste_promo']);
        exit;
    } else {
        stocker_session('errors', ['login' => ErreurEnum::LOGIN_INCORRECT->value]);
        afficher_page_login();
    }
}

/**
 * Récupère les données du formulaire de réinitialisation de mot de passe
 */
function get_reset_password_form_data(): array {
    return [
        'login' => $_POST['login'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
}

/**
 * Valide les données du formulaire de réinitialisation de mot de passe
 */
function validate_reset_password_form(array $formData): bool {
    return !empty($formData['login']) && !empty($formData['password']);
}

/**
 * Réinitialise le mot de passe d'un utilisateur
 */
function reset_user_password(string $login, string $password): bool {
    global $auth_model;
    $chemin_data = CheminPage::DATA_JSON->value;
    return $auth_model[AUTHMETHODE::RESET_PASSWORD->value]($login, $password, $chemin_data);
}

/**
 * Traite le formulaire de réinitialisation de mot de passe
 */
function traiter_reset_password(): void {
    $formData = get_reset_password_form_data();
    
    if (!validate_reset_password_form($formData)) {
        stocker_session('error', 'Email et mot de passe sont requis');
        afficher_page_reset_password();
        return;
    }
    
    $success = reset_user_password($formData['login'], $formData['password']);

    if ($success) {
        stocker_session('success', 'Mot de passe modifié avec succès');
        redirect_to_route('index.php');
        exit;
    } else {
        stocker_session('error', 'Email introuvable ou erreur de sauvegarde');
        afficher_page_reset_password();
    }
}

/**
 * Déconnecte l'utilisateur
 */
function logout(): void {
    demarrer_session();
    detruire_session();
    
    redirect_to_route('index.php');
    exit;
}
