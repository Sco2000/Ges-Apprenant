<?php
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\REFMETHODE;
use App\Models\JSONMETHODE;
use App\ENUM\VALIDATOR\VALIDATORMETHODE;

require_once CheminPage::REF_MODEL->value;
require_once CheminPage::MODEL->value;
require_once CheminPage::VALIDATOR_SERVICE->value;
require_once CheminPage::SESSION_SERVICE->value;
require_once CheminPage::MODEL_ENUM->value;

function afficher_referentiels(): void {
    global $ref_model;
    
    // Récupérer le terme de recherche
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Récupérer les référentiels de la promotion active
    $referentiels = $ref_model[REFMETHODE::GET_BY_ACTIVE_PROMO->value]();
    
    // Récupérer TOUS les référentiels pour la modal d'affectation
    $all_referentiels = $ref_model[REFMETHODE::GET_ALL->value]();
    
    // Appliquer le filtre de recherche si présent
    if (!empty($searchTerm)) {
        $referentiels = array_filter($referentiels, function($ref) use ($searchTerm) {
            return stripos($ref['nom'], $searchTerm) !== false;
        });
        $referentiels = array_values($referentiels);
    }
    
    render('referenciel/referenciel', [
        'referentiels' => $referentiels,
        'all_referentiels' => $all_referentiels,
        'message' => empty($referentiels) ? 'Aucun référentiel associé à la promotion active' : null
    ]);
}

function afficher_tous_referentiels(): void {
    global $ref_model;
    demarrer_session();
    // Get search term
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Get all referentials
    $referentiels = $ref_model[REFMETHODE::GET_ALL->value]();
    
    // Apply search filter if present
    if (!empty($searchTerm)) {
        $referentiels = array_filter($referentiels, function($ref) use ($searchTerm) {
            return stripos($ref['nom'], $searchTerm) !== false;
        });
        $referentiels = array_values($referentiels); // Reset array keys
    }

    // Pagination logic
    $items_per_page = 10;
    $total_items = count($referentiels);
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = isset($_GET['p']) ? max(1, min((int)$_GET['p'], $total_pages)) : 1;
    $offset = ($current_page - 1) * $items_per_page;
    
    // Get items for current page
    $referentiels_page = array_slice($referentiels, $offset, $items_per_page);
    
    render('referenciel/all_referenciel', [
        'referentiels' => $referentiels_page,
        'pagination' => [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_items' => $total_items,
            'items_per_page' => $items_per_page
        ]
    ]);
}

function ajouter_referenciel(): void {
    global $ref_model;
    
    // Validation basique
    if (empty($_POST['nom']) || empty($_POST['capacite'])) {
        return;
    }
    
    // Gestion de l'upload de la photo
    $uploadDir = __DIR__ . '/../../public/assets/images/referenciel/';
    $photoPath = uploadPhoto($_FILES['photo'], $uploadDir);

    if ($photoPath === null) {
        stocker_session('errors', ['photo' => "Erreur lors de l'upload de la photo."]);
        return;
    }

    // Création du nouveau référentiel
    $nouveau_ref = [
        'id' => time(),
        'nom' => $_POST['nom'],
        'capacite' => (int)$_POST['capacite'],
        'photo' => $photoPath,
        'modules' => 0,
        'apprenants' => 0
    ];
    
    if ($ref_model[REFMETHODE::AJOUTER->value]($nouveau_ref)) {
        redirect_to_route('?page=referenciel');
        exit;
    }
}

function affecter_referentiel(): void {
    global $ref_promo_model, $ref_model;
    
    if (!isset($_POST['ref_ids']) || !is_array($_POST['ref_ids'])) {
        stocker_session('error', "Aucun référentiel sélectionné");
        redirect_to_route('?page=referenciel&action=add');
        return;
    }
    
    $active_promo = current($ref_promo_model['get_active_promo']());
    
    if (!$active_promo) {
        stocker_session('error', "Aucune promotion active");
        redirect_to_route('?page=referenciel&action=add');
        return;
    }

    $success = true;
    foreach ($_POST['ref_ids'] as $ref_id) {
        $ref_id = (int)$ref_id;
        if (!$ref_promo_model['affecter_ref']($active_promo['id'], $ref_id)) {
            $success = false;
            continue;
        }
        $ref_model[REFMETHODE::UPDATE_NBR_APPRENANT->value]($ref_id, $active_promo['nbrApprenant']);
    }

    if ($success) {
        stocker_session('success', "Les référentiels ont été affectés avec succès");
        // Nettoyer la sélection temporaire après une affectation réussie
        unset($_SESSION['temp_selected_refs']);
        redirect_to_route('?page=referenciel&action=add');  // Modifié ici
    } else {
        stocker_session('error', "Certains référentiels n'ont pas pu être affectés");
        redirect_to_route('?page=referenciel&action=add');
    }
}

function desaffecter_referentiel(): void {
    global $ref_promo_model;
    
    if (!isset($_POST['ref_id'])) {
        stocker_session('error', "ID de référentiel manquant");
        redirect_to_route('?page=referenciel&action=add');
        return;
    }
    
    $ref_id = (int)$_POST['ref_id'];
    $active_promo = current($ref_promo_model['get_active_promo']());
    
    if (!$active_promo) {
        stocker_session('error', "Aucune promotion active");
        redirect_to_route('?page=referenciel&action=add');
        return;
    }
    
    if ($ref_promo_model['desaffecter_ref']($active_promo['id'], $ref_id)) {
        stocker_session('success', "Référentiel désaffecté avec succès");
    } else {
        stocker_session('error', "Erreur lors de la désaffectation");
    }
    
    redirect_to_route('?page=referenciel&action=add');
}

function creerReferentiel(): void {
    global $validator, $ref_model;

    // Démarrer la session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        render('referenciel/all_referenciel');
        return;
    }

    $errors = [];

    // Validation des champs
    if (empty($_POST['nom'])) {
        $errors['nom'] = "Le nom est requis";
    }
    if (empty($_POST['description'])) {
        $errors['description'] = "La description est requise";
    }
    if (empty($_POST['capacite']) || !is_numeric($_POST['capacite']) || $_POST['capacite'] <= 0) {
        $errors['capacite'] = "La capacité doit être un nombre positif";
    }
    if (empty($_POST['nb_sessions']) || !is_numeric($_POST['nb_sessions']) || $_POST['nb_sessions'] <= 0) {
        $errors['nb_sessions'] = "Le nombre de sessions doit être un nombre positif";
    }

    // Validation de la photo
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
        $errors['photo'] = "La photo est requise";
    } else {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
            $errors['photo'] = "Format invalide (JPG/PNG uniquement)";
        }
        if ($_FILES['photo']['size'] > $maxSize) {
            $errors['photo'] = "La taille de l'image ne doit pas dépasser 2MB";
        }
    }

    if (!empty($errors)) {
        stocker_session('errors', $errors);
        header('Location: ?page=all_referenciel&action=create');
        exit;
    }

    // Créer le dossier s'il n'existe pas
    $uploadDir = __DIR__ . '/../../public/assets/images/referenciel/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $photoPath = uploadPhoto($_FILES['photo'], $uploadDir);

    if ($photoPath === null) {
        stocker_session('errors', ['photo' => "Erreur lors de l'upload de la photo"]);
        render('referenciel/all_referenciel');
        return;
    }

    $nouveau_ref = [
        'id' => time(),
        'nom' => $_POST['nom'],
        'description' => $_POST['description'],
        'capacite' => (int)$_POST['capacite'],
        'nb_sessions' => (int)$_POST['nb_sessions'],
        'photo' => $photoPath,
        'statut' => 'inactif',
        'apprenants' => 0,
        'modules' => 0
    ];
    if ($ref_model[REFMETHODE::AJOUTER->value]($nouveau_ref)) {
        stocker_session('success', 'Référentiel créé avec succès');
        redirect_to_route('?page=all_referenciel');
    } else {
        stocker_session('errors', ['general' => "Erreur lors de la création du référentiel"]);
        render('referenciel/all_referenciel');
    }
}