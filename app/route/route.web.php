<?php
require_once __DIR__ . '/../enums/chemin_page.php';
use App\Enums\CheminPage;
require_once CheminPage::CONTROLLER->value;
require_once CheminPage::MODEL->value;
require_once CheminPage::PROMO_CONTROLLER->value;

$page = $_GET['page'] ?? 'login';


match ($page) {
    'login' => (function () {
        require_once CheminPage::AUTH_CONTROLLER->value;
        voir_page_login();
    })(),
    'resetPassword' => (function () {
        require_once CheminPage::AUTH_CONTROLLER->value;
        voir_page_reset_password();
    })(),

    'liste_promo' => (function () {
        require_once CheminPage::PROMO_CONTROLLER->value;
        afficher_promotions();
    })(),
    'layout' => (function () {
        require_once CheminPage::LAYOUT_CONTROLLER->value;
    })(),

    'referenciel' => (function() {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
        afficher_referentiels();
    })(),
    'affecter_referentiel' => (function() {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
        affecter_referentiel();
    })(),
    'desaffecter_referentiel' => (function() {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
        desaffecter_referentiel();
    })(),

    'all_referenciel' => (function() {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
        afficher_tous_referentiels();
    })(),
    'creer_referentiel' => (function() {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
        creerReferentiel();
    })(),
    'toggle_promo_status' => (function() {
        require_once CheminPage::PROMO_CONTROLLER->value;
        togglePromoStatus();
    })(),
    'add_promo' => (function() {
        render('promo/add_promo');
    })(),
    'ajouter_promo' => (function() {
        require_once __DIR__ . '/../controllers/promo.controller.php';
        ajouterPromotion();
    })(),
    'error' => (function () {
        require_once __DIR__ . '/../controllers/error.controller.php';
        showError("Page introuvable");
    })(),

    'liste_apprenants' => (function() {
        require_once CheminPage::APPRENANT_CONTROLLER->value;
        
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'importer':
                    importer_apprenants();
                    break;
                case 'export':
                    export_apprenants($_GET['format'] ?? 'pdf');
                    break;
                case 'ajouter':
                    ajouter_apprenant();
                    break;
                default:
                    liste_apprenants();
                    break;
            }
        } else {
            liste_apprenants();
        }
    })(),

    default => (function () use ($page) {
        require_once CheminPage::ERROR_CONTROLLER->value;
        showError("404 - Page '$page' non reconnue");
    })(),
    'logout'=> (function () {
        require_once CheminPage::AUTH_CONTROLLER->value;
            logout();
    })(),
};





