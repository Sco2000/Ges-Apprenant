<?php
require_once __DIR__ . '/../enums/chemin_page.php';

use App\ENUM\ERREUR\ErreurEnum;
use App\Enums\CheminPage;
use App\Models\PROMOMETHODE;
use App\Models\REFMETHODE;
use App\Models\JSONMETHODE;
use App\ENUM\MESSAGE\MSGENUM;
use App\ENUM\VALIDATOR\VALIDATORMETHODE;
require_once CheminPage::PROMO_MODEL->value;
require_once CheminPage::MODEL_ENUM->value;
require_once CheminPage::MESSAGE_ENUM->value;
require_once CheminPage::ERROR_ENUM->value;
require_once CheminPage::VALIDATOR_SERVICE->value;
require_once CheminPage::SESSION_SERVICE->value;
require_once CheminPage::REF_MODEL->value;

function search_promotions($promotions, $searchTerm) {
    if (empty($searchTerm)) return $promotions;
    return array_filter($promotions, function($promo) use ($searchTerm) {
        return stripos($promo['nom'], $searchTerm) !== false;
    });
}

function paginate_items($items, $page = 1, $perPage = 10) {
    // Convertir $page en entier
    $page = (int)$page;
    
    // S'assurer que $items est un tableau
    if (!is_array($items)) {
        $items = [];
    }
    
    $total = count($items);
    $pages = max(1, ceil($total / $perPage));
    
    // S'assurer que la page demandée est valide
    $page = max(1, min($page, $pages));
    
    $offset = ($page - 1) * $perPage;
    
    return [
        'items' => array_slice($items, $offset, $perPage),
        'total' => $total,
        'pages' => $pages,
        'current' => $page
    ];
}

function afficher_promotions($message = null, $errors = []): void {
    global $promos;
    
    // Mettre à jour la session avec le nom de la promotion active
    update_active_promo_session();
    
    // Le reste du code reste inchangé...
    $perPage = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
    $currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $view = $_GET['view'] ?? 'grid';
    $searchTerm = $_GET['search'] ?? '';
    
    // Récupérer toutes les promotions
    $promotions = $promos["get_all"]();
    
    // Utiliser la fonction du modèle pour gérer la pagination
    $paginationData = getPromosWithActiveFirst($promotions, $perPage, $currentPage, $searchTerm);
    
    // Ajouter les détails des référentiels pour chaque promotion
    $paginatedPromos = array_map(function($promo) {
        $promo['referentiels'] = get_referenciel_details($promo['referenciel_ids'] ?? []);
        return $promo;
    }, $paginationData['promos']);
    
    render("promo/promo", [
        "promotions" => $promotions,
        "paginatedPromos" => $paginatedPromos,
        "pagination" => $paginationData,
        "stats" => get_statistics($promotions),
        "view" => $view,
        "message" => $message,
        "errors" => $errors
    ]);
}

function get_referenciel_details($referenciel_ids) {
    global $ref_model;
    if (empty($referenciel_ids)) {
        return [];
    }
    $all_refs = $ref_model[REFMETHODE::GET_ALL->value]();
    if (!is_array($all_refs)) {
        return [];
    }
    $ref_details = [];
    
    foreach ($referenciel_ids as $ref_id) {
        foreach ($all_refs as $ref) {
            if ($ref['id'] === $ref_id) {
                $ref_details[] = $ref;
                break;
            }
        }
    }
    
    return $ref_details;
}

function get_statistics($promotions) {
    global $ref_model;
    
    // Trouver la promotion active
    $activePromo = array_filter($promotions, fn($p) => $p['statut'] === 'Active');
    $activePromo = reset($activePromo); // Obtenir le premier élément ou false si aucun

    $total_apprenants = 0;
    $total_referentiels = 0;

    if ($activePromo) {
        if (isset($activePromo['referentiel'])) {
            $total_apprenants = $ref_model[REFMETHODE::GET_TOTAL_APPRENANTS->value]($activePromo['referentiel']);
            $total_referentiels = count($activePromo['referentiel']);
        }
    }

    return [
        'total_apprenants' => $total_apprenants,
        'total_referentiels' => $total_referentiels,
        'promotions_actives' => count(array_filter($promotions, fn($p) => $p['statut'] === 'Active')),
        'total_promotions' => count($promotions)
    ];
}

function save_promotions(array $promotions): bool {
    global $model_tab;
    $chemin = CheminPage::DATA_JSON->value;
    
    // Charger toutes les données actuelles
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
    
    // Mettre à jour seulement les promotions
    $data['promotions'] = $promotions;
    
    // Sauvegarder dans le fichier JSON
    return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $chemin);
}

function toggle_promotion_status($promoId) {
    global $promos;
    $promotions = $promos["get_all"]();
    
    foreach ($promotions as &$promo) {
        if ($promo['id'] == $promoId) {
            $promo['statut'] = $promo['statut'] === 'Active' ? 'Inactive' : 'Active';
            break;
        }
    }
    
    return save_promotions($promotions);
}

function ajouterPromotion(): void {
    global $validator;
    demarrer_session();
    
    // Préparer les données du formulaire
    $form_data = [
        'nom_promo' => trim($_POST['nom_promo'] ?? ''),
        'date_debut' => trim($_POST['date_debut'] ?? ''),
        'date_fin' => trim($_POST['date_fin'] ?? ''),
        'referenciels' => $_POST['referenciels'] ?? [],
        'nbr_apprenants' => $_POST['nbr_apprenants'] ?? null,
        'photo' => $_FILES['photo'] ?? null
    ];

    // Utiliser le validateur général
    $errors = $validator[VALIDATORMETHODE::VALID_GENERAL->value]($form_data);

    if (!empty($errors)) {
        stocker_session('errors', $errors);
        stocker_session('old', $_POST);
        header('Location: index.php?page=add_promo');
        exit();
    }

    // Gérer l'upload de la photo
    $photoPath = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'assets/images/promo/';
        $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
        $uploadPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
            $photoPath = $uploadPath;
        }
    }

    // Charger les données JSON existantes
    $json_data = json_decode(file_get_contents(CheminPage::DATA_JSON->value), true);

    // Générer un nouvel ID unique
    $newId = 1;
    if (!empty($json_data['promotions'])) {
        $ids = array_column($json_data['promotions'], 'id');
        $newId = max($ids) + 1;
    }

    // Créer la nouvelle promotion
    $nouvelle_promo = [
        'id' => $newId,
        'nom' => $form_data['nom_promo'],
        'dateDebut' => $form_data['date_debut'],
        'dateFin' => $form_data['date_fin'],
        'photo' => $photoPath,
        'statut' => 'Inactive',
        'nbrApprenant' => (int)($form_data['nbr_apprenants'] ?? 0),
        'referenciel_ids' => $form_data['referenciels'] ?? []
    ];

    if (!isset($json_data['promotions'])) {
        $json_data['promotions'] = [];
    }
    $json_data['promotions'][] = $nouvelle_promo;

    // Sauvegarder dans le fichier JSON
    if (file_put_contents(CheminPage::DATA_JSON->value, json_encode($json_data, JSON_PRETTY_PRINT))) {
        stocker_session('success', 'La promotion a été créée avec succès.');
    } else {
        stocker_session('error', 'Une erreur est survenue lors de la création de la promotion.');
    }

    // Rediriger vers la page des promotions
    header('Location: index.php?page=liste_promo');
    exit();
}

function togglePromoStatus(): void {
    global $promos;
    
    if (!isset($_POST['id'])) {
        stocker_session('error', "ID de promotion manquant");
        redirect_to_route('?page=liste_promo&view=' . ($_POST['view'] ?? 'grid'));
        return;
    }
    
    $promoId = (int)$_POST['id'];
    $view = $_POST['view'] ?? 'grid';
    $currentStatus = $_POST['current_status'] ?? '';
    
    // Si la promotion est déjà active, ne rien faire
    if ($currentStatus === 'Active') {
        stocker_session('info', "Une promotion active ne peut pas être désactivée directement.");
        redirect_to_route('?page=liste_promo&view=' . $view);
        return;
    }
    
    // Désactiver toutes les promotions d'abord
    if (!$promos['desactiver_tout']()) {
        stocker_session('error', "Erreur lors de la désactivation des promotions");
        redirect_to_route('?page=liste_promo&view=' . $view);
        return;
    }
    
    // Activer la promotion sélectionnée
    if (!$promos['activer']($promoId)) {
        stocker_session('error', "Erreur lors de l'activation de la promotion");
        redirect_to_route('?page=liste_promo&view=' . $view);
        return;
    }
    
    // Mettre à jour la session avec le nom de la promotion active
    update_active_promo_session();
    
    // Rediriger vers la bonne vue
    redirect_to_route('?page=liste_promo&view=' . $view);
}

function update_active_promo_session(): void {
    global $promos;
    demarrer_session();
    
    // Récupérer toutes les promotions
    $promotions = $promos["get_all"]();
    
    // Rechercher la promotion active
    $_SESSION['active_promo_name'] = 'Aucune promotion active';
    foreach ($promotions as $promo) {
        if ($promo['statut'] === 'Active') {
            $_SESSION['active_promo_name'] = $promo['nom'];
            break;
        }
    }
}

function afficher_form_ajout_promo(): void {
    update_active_promo_session();
    render("promo/add_promo", [], "base.layout");
}