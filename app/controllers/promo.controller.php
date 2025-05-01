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
require_once CheminPage::ERROR_ENUM->value;
require_once CheminPage::MESSAGE_ENUM->value;
require_once CheminPage::SESSION_SERVICE->value;
require_once CheminPage::VALIDATOR_SERVICE->value;
require_once CheminPage::REF_MODEL->value;



/**
 * Filtre les promotions selon un terme de recherche
 */
function search_promotions($promotions, $searchTerm) {
    if (empty($searchTerm)) return $promotions;
    return array_filter($promotions, function($promo) use ($searchTerm) {
        return stripos($promo['nom'], $searchTerm) !== false;
    });
}

/**
 * Pagine une liste d'éléments
 */
function paginate_items($items, $page = 1, $perPage = 10) {
    $page = (int)$page;
    
    if (!is_array($items)) {
        $items = [];
    }
    
    $total = count($items);
    $pages = max(1, ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'items' => array_slice($items, $offset, $perPage),
        'total' => $total,
        'pages' => $pages,
        'current' => $page
    ];
}

/**
 * Récupère les détails des référentiels associés à une promotion
 */
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

/**
 * Calcule les statistiques pour les promotions
 */
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

/**
 * Sauvegarde les promotions dans le fichier JSON
 */
function save_promotions(array $promotions): bool {
    global $model_tab;
    $chemin = CheminPage::DATA_JSON->value;
    
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
    $data['promotions'] = $promotions;
    
    return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $chemin);
}

/**
 * Récupère les paramètres de pagination et de recherche depuis la requête
 */
function get_pagination_params() {
    return [
        'perPage' => isset($_GET['limit']) ? (int)$_GET['limit'] : 8,
        'currentPage' => isset($_GET['p']) ? (int)$_GET['p'] : 1,
        'view' => $_GET['view'] ?? 'grid',
        'searchTerm' => $_GET['search'] ?? ''
    ];
}

/**
 * Prépare les données des promotions pour l'affichage
 */
function prepare_promotions_data($params) {
    global $promos;
    
    $promotions = $promos["get_all"]();
    $paginationData = getPromosWithActiveFirst(
        $promotions, 
        $params['perPage'], 
        $params['currentPage'], 
        $params['searchTerm']
    );
    
    // Ajouter les détails des référentiels pour chaque promotion
    $paginatedPromos = array_map(function($promo) {
        $promo['referentiels'] = get_referenciel_details($promo['referenciel_ids'] ?? []);
        return $promo;
    }, $paginationData['promos']);
    
    return [
        'promotions' => $promotions,
        'paginatedPromos' => $paginatedPromos,
        'pagination' => $paginationData,
        'stats' => get_statistics($promotions)
    ];
}

/**
 * Affiche la liste des promotions
 */
function afficher_promotions($message = null, $errors = []): void {
    global $promos;
    
    // Mettre à jour la session avec le nom de la promotion active
    update_active_promo_session();
    
    $perPage = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
    $currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $view = $_GET['view'] ?? 'grid';
    $searchTerm = $_GET['search'] ?? '';
    $statusFilter = $_GET['status'] ?? 'tous';
    
    // Récupérer toutes les promotions
    $promotions = $promos["get_all"]();
    
    // Appliquer le filtre de statut si nécessaire
    if ($statusFilter !== 'tous') {
        $statusValue = $statusFilter === 'active' ? 'Active' : 'Inactive';
        $promotions = array_filter($promotions, function($promo) use ($statusValue) {
            return $promo['statut'] === $statusValue;
        });
        $promotions = array_values($promotions); // Réindexer le tableau
    }
    
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
        "errors" => $errors,
        "statusFilter" => $statusFilter
    ]);
}

/**
 * Change le statut d'une promotion (active/inactive)
 */
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

/**
 * Valide les données du formulaire d'ajout de promotion
 */
function validate_promotion_form() {
    global $validator;
    
    $form_data = [
        'nom_promo' => trim($_POST['nom_promo'] ?? ''),
        'date_debut' => trim($_POST['date_debut'] ?? ''),
        'date_fin' => trim($_POST['date_fin'] ?? ''),
        'referenciels' => $_POST['referenciels'] ?? [],
        'nbr_apprenants' => $_POST['nbr_apprenants'] ?? null,
        'photo' => $_FILES['photo'] ?? null
    ];

    return [
        'form_data' => $form_data,
        'errors' => $validator[VALIDATORMETHODE::VALID_GENERAL->value]($form_data)
    ];
}

/**
 * Gère l'upload de la photo de promotion
 */
function handle_photo_upload() {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        return '';
    }
    
    $uploadDir = 'assets/images/promo/';
    $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
        return $uploadPath;
    }
    
    return '';
}

/**
 * Crée une nouvelle promotion à partir des données du formulaire
 */
function create_promotion($form_data) {
    $photoPath = handle_photo_upload();
    
    // Charger les données JSON existantes
    $json_data = json_decode(file_get_contents(CheminPage::DATA_JSON->value), true);

    // Générer un nouvel ID unique
    $newId = 1;
    if (!empty($json_data['promotions'])) {
        $ids = array_column($json_data['promotions'], 'id');
        $newId = max($ids) + 1;
    }

    // Créer la nouvelle promotion
    return [
        'id' => $newId,
        'nom' => $form_data['nom_promo'],
        'dateDebut' => $form_data['date_debut'],
        'dateFin' => $form_data['date_fin'],
        'photo' => $photoPath,
        'statut' => 'Inactive',
        'nbrApprenant' => (int)($form_data['nbr_apprenants'] ?? 0),
        'referenciel_ids' => $form_data['referenciels'] ?? []
    ];
}

/**
 * Sauvegarde une nouvelle promotion dans le fichier JSON
 */
function save_new_promotion($nouvelle_promo) {
    $json_data = json_decode(file_get_contents(CheminPage::DATA_JSON->value), true);

    if (!isset($json_data['promotions'])) {
        $json_data['promotions'] = [];
    }
    $json_data['promotions'][] = $nouvelle_promo;

    return file_put_contents(CheminPage::DATA_JSON->value, json_encode($json_data, JSON_PRETTY_PRINT));
}

/**
 * Traite l'ajout d'une nouvelle promotion
 */
function ajouterPromotion(): void {
    demarrer_session();
    
    $validation = validate_promotion_form();
    $form_data = $validation['form_data'];
    $errors = $validation['errors'];

    if (!empty($errors)) {
        stocker_session('errors', $errors);
        stocker_session('old', $_POST);
        header('Location: index.php?page=add_promo');
        exit();
    }

    $nouvelle_promo = create_promotion($form_data);
    
    if (save_new_promotion($nouvelle_promo)) {
        stocker_session('success', 'La promotion a été créée avec succès.');
    } else {
        stocker_session('error', 'Une erreur est survenue lors de la création de la promotion.');
    }

    header('Location: index.php?page=liste_promo');
    exit();
}

/**
 * Gère la bascule du statut d'une promotion via le formulaire
 */
function togglePromoStatus(): void {
    global $promos;
    
    if (!isset($_POST['id'])) {
        stocker_session('error', "ID de promotion manquant");
        redirect_to_route('?page=liste_promo&view=' . ($_POST['view'] ?? 'grid'));
        return;
    }
    
    $promoId = (int)$_POST['id'];
    $view = $_POST['view'] ?? 'grid';
    
    // Désactiver toutes les promotions d'abord
    if (!$promos['desactiver_tout']()) {
        stocker_session('error', "Erreur lors de la désactivation des promotions");
        redirect_to_route('?page=liste_promo&view=' . $view);
        return;
    }
    
    // Activer la promotion sélectionnée si elle était inactive
    if ($_POST['current_status'] !== 'Active') {
        if (!$promos['activer']($promoId)) {
            stocker_session('error', "Erreur lors de l'activation de la promotion");
            redirect_to_route('?page=liste_promo&view=' . $view);
            return;
        }
    }
    
    // Mettre à jour la session avec le nom de la promotion active
    update_active_promo_session();
    
    // Rediriger vers la bonne vue
    redirect_to_route('?page=liste_promo&view=' . $view);
}

/**
 * Met à jour la session avec le nom de la promotion active
 */
function update_active_promo_session(): void {
    global $promos;
    demarrer_session();
    
    $promotions = $promos["get_all"]();
    
    $_SESSION['active_promo_name'] = 'Aucune promotion active';
    foreach ($promotions as $promo) {
        if ($promo['statut'] === 'Active') {
            $_SESSION['active_promo_name'] = $promo['nom'];
            break;
        }
    }
}

/**
 * Affiche le formulaire d'ajout de promotion
 */
function afficher_form_ajout_promo(): void {
    update_active_promo_session();
    render("promo/add_promo", [], "base.layout");
}