<?php
require_once __DIR__ . '/../enums/chemin_page.php';
use App\Enums\CheminPage;
require_once CheminPage::AUTH_MODEL->value;
require_once CheminPage::SESSION_SERVICE->value;
require_once CheminPage::MESSAGE_FR->value;
require_once CheminPage::VALIDATOR_SERVICE->value;
use App\ENUM\VALIDATOR\VALIDATORMETHODE;
use App\ENUM\ERREUR\ErreurEnum;
use App\Models\AUTHMETHODE;
use app\Models\JSONMETHODE;
use App\ENUM\MESSAGE\MSGENUM;

demarrer_session();


// === PAGE LOGIN ===
function voir_page_login(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        traiter_connexion();
    } else {
        
        render('login/login', [], layout: null);
    }
}

function traiter_connexion(): void {
    global $validator, $auth_model;
    $chemin_data = CheminPage::DATA_JSON->value;
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    $erreurs = $validator[VALIDATORMETHODE::USER->value]($login, $password);
    if (!empty($erreurs)) {
        stocker_session('errors', $erreurs);
        render('login/login', [], layout: null);
        return;
    }

    $user = $auth_model[AUTHMETHODE::LOGIN->value]($login, $password, $chemin_data);

    if ($user) {
        stocker_session('user', $user);
        demarrer_session();
        redirect_to_route('index.php', ['page' => 'liste_promo']);
        exit;
    } else {
        stocker_session('errors', ['login' => ErreurEnum::LOGIN_INCORRECT->value]);
        render('login/login', [], layout: null);
    }
}

// === PAGE RESET PASSWORD ===
function voir_page_reset_password(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        traiter_reset_password();
    } else {
        render('login/reset_password', [], layout: null);
    }
}

function traiter_reset_password(): void {
    global $auth_model;
    $chemin_data = CheminPage::DATA_JSON->value;

    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        stocker_session('error', 'Email et mot de passe sont requis');
        render('login/reset_password', [], layout: null);
        return;
    }
    $success = $auth_model[AUTHMETHODE::RESET_PASSWORD->value]($login, $password, $chemin_data);

    if ($success) {
        stocker_session('success', 'Mot de passe modifié avec succès');
        // render('login/login', ['page' => 'login'], layout: null);
        redirect_to_route('index.php');
        exit;
    } else {
        stocker_session('error', 'Email introuvable ou erreur de sauvegarde');
        render('login/reset_password', [], layout: null);
    }
}


// === DECONNEXION ===
function logout(): void {
    
    demarrer_session();
    detruire_session();
    
    redirect_to_route('index.php');
    exit;
}
