<?php
global $model_tab;
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\JSONMETHODE;
use App\Models\PROMOMETHODE;

require_once CheminPage::MODEL_ENUM->value;

$json = CheminPage::DATA_JSON->value;
$jsontoarray = $model_tab[JSONMETHODE::JSONTOARRAY->value];

// Définition des fonctions utilitaires anonymes
$loadData = function($json) use ($model_tab) {
    return $model_tab[JSONMETHODE::JSONTOARRAY->value]($json);
};

$saveData = function($data, $json) use ($model_tab) {
    return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $json);
};

$calculateTotalApprenants = function($referentiels) {
    $total_apprenants = 0;
    if (is_array($referentiels)) {
        foreach ($referentiels as $ref) {
            $total_apprenants += isset($ref['apprenants']) ? (int)$ref['apprenants'] : 0;
        }
    }
    return $total_apprenants;
};

$validatePromoData = function($promoData) {
    $required_fields = ['nom', 'dateDebut', 'dateFin'];
    foreach ($required_fields as $field) {
        if (!isset($promoData[$field]) || $promoData[$field] === null) {
            return false;
        }
    }
    return true;
};

$preparePromoData = function($promoData, $allReferentiels) {
    // S'assurer que les champs optionnels ont des valeurs par défaut
    $promoData['photo'] = $promoData['photo'] ?? '';
    $promoData['statut'] = $promoData['statut'] ?? 'Inactive';
    $promoData['nbrApprenant'] = $promoData['nbrApprenant'] ?? 0;

    // Récupérer les référentiels complets
    if (!empty($promoData['referenciel_ids'])) {
        $referentiels = [];
        foreach ($allReferentiels as $ref) {
            if (in_array($ref['id'], $promoData['referenciel_ids'])) {
                $referentiels[] = $ref;
            }
        }
        $promoData['referentiel'] = $referentiels;
    } else {
        $promoData['referentiel'] = [];
    }
    
    // Supprimer l'ancienne structure
    unset($promoData['referenciel_ids']);
    unset($promoData['referenciel_id']);
    
    return $promoData;
};

$sortPromosByStatus = function($promos) {
    usort($promos, function($a, $b) {
        if (($a['statut'] ?? '') === 'Active' && ($b['statut'] ?? '') !== 'Active') return -1;
        if (($a['statut'] ?? '') !== 'Active' && ($b['statut'] ?? '') === 'Active') return 1;
        return 0;
    });
    return $promos;
};

$extractActivePromo = function($promos) {
    $activePromo = null;
    $remainingPromos = [];
    
    foreach ($promos as $promo) {
        if (($promo['statut'] ?? '') === 'Active') {
            $activePromo = $promo;
        } else {
            $remainingPromos[] = $promo;
        }
    }
    
    return ['activePromo' => $activePromo, 'remainingPromos' => $remainingPromos];
};

$filterPromosBySearchTerm = function($promos, $searchTerm) {
    if (empty($searchTerm)) {
        return $promos;
    }
    
    return array_filter($promos, function($promo) use ($searchTerm) {
        return stripos($promo['nom'], $searchTerm) !== false;
    });
};

$paginatePromos = function($promos, $activePromo, $perPage, $currentPage) {
    $total = count($promos) + ($activePromo ? 1 : 0);
    $pages = max(1, ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $pages));
    
    $itemsPerPage = $activePromo ? $perPage - 1 : $perPage;
    $start = ($currentPage - 1) * $itemsPerPage;
    
    $paginatedPromos = [];
    if ($activePromo) {
        $paginatedPromos[] = $activePromo;
    }
    
    $paginatedPromos = array_merge(
        $paginatedPromos,
        array_slice($promos, $start, $itemsPerPage)
    );

    return [
        'promos' => $paginatedPromos,
        'total' => $total,
        'pages' => $pages,
        'currentPage' => $currentPage,
        'start' => $start,
        'perPage' => $perPage
    ];
};

// Définition de l'API publique du modèle
global $promos;
$promos = [
    "get_all" => fn() => $jsontoarray($json, "promotions"),
    
    "exists" => function(string $nom) use ($jsontoarray, $json): bool {
        $data = $jsontoarray($json);
        return !empty(array_filter($data['promotions'] ?? [], fn($p) => strtolower($p['nom']) === strtolower($nom)));
    },

    "activer" => function(int $promoId) use ($loadData, $saveData, $calculateTotalApprenants, $json): bool {
        $data = $loadData($json);
        if (!isset($data['promotions'])) return false;
        
        $found = false;
        foreach ($data['promotions'] as &$promo) {
            if ($promo['id'] === $promoId) {
                $promo['statut'] = 'Active';
                $promo['nbrApprenant'] = $calculateTotalApprenants($promo['referentiel'] ?? []);
                $found = true;
                break;
            }
        }
        
        if (!$found) return false;
        return $saveData($data, $json);
    },

    "desactiver_tout" => function() use ($loadData, $saveData, $json): bool {
        $data = $loadData($json);
        if (!isset($data['promotions'])) return false;
        
        foreach ($data['promotions'] as &$promo) {
            $promo['statut'] = 'Inactive';
        }
        
        return $saveData($data, $json);
    },

    PROMOMETHODE::AJOUTER_PROMO->value => function(array $nouvelle_promo) use ($loadData, $saveData, $validatePromoData, $preparePromoData, $json): bool {
        // Validation
        if (!$validatePromoData($nouvelle_promo)) {
            return false;
        }

        // Charger les données existantes
        $data = $loadData($json);
        
        // Préparer les données de la promo
        $nouvelle_promo = $preparePromoData($nouvelle_promo, $data['referenciel'] ?? []);

        // Ajouter la nouvelle promo
        if (!isset($data['promotions'])) {
            $data['promotions'] = [];
        }
        $data['promotions'][] = $nouvelle_promo;
        
        // Sauvegarder
        return $saveData($data, $json);
    }   
];

// Fonction pour obtenir les promotions avec la promotion active en premier
$getPromosWithActiveFirst = function(array $promotions, int $perPage, int $currentPage, string $searchTerm = '') 
    use ($sortPromosByStatus, $extractActivePromo, $filterPromosBySearchTerm, $paginatePromos): array {
    
    // Trier pour mettre la promo active en premier
    $promotions = $sortPromosByStatus($promotions);

    // Extraire la promo active
    $result = $extractActivePromo($promotions);
    $promoActive = $result['activePromo'];
    $promotions = $result['remainingPromos'];

    // Appliquer la recherche si nécessaire
    if (!empty($searchTerm)) {
        if ($promoActive && stripos($promoActive['nom'], $searchTerm) === false) {
            $promoActive = null;
        }
        $promotions = $filterPromosBySearchTerm($promotions, $searchTerm);
        $promotions = array_values($promotions); // Réindexer le tableau
    }

    // Paginer les résultats
    return $paginatePromos($promotions, $promoActive, $perPage, $currentPage);
};

// Exposer la fonction getPromosWithActiveFirst globalement pour qu'elle soit accessible par le contrôleur
function getPromosWithActiveFirst(array $promotions, int $perPage, int $currentPage, string $searchTerm = ''): array {
    global $getPromosWithActiveFirst;
    return $getPromosWithActiveFirst($promotions, $perPage, $currentPage, $searchTerm);
}
