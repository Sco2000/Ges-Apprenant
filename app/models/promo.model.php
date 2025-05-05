<?php
global $model_tab;
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\JSONMETHODE;
use App\Models\PROMOMETHODE;

require_once CheminPage::MODEL_ENUM->value;

$json = CheminPage::DATA_JSON->value;
$jsontoarray = $model_tab[JSONMETHODE::JSONTOARRAY->value];

global $promos;
$promos = [
    "get_all" => fn() => $jsontoarray($json, "promotions"),
    
    "exists" => function(string $nom) use ($jsontoarray, $json): bool {
        $data = $jsontoarray($json);
        return !empty(array_filter($data['promotions'] ?? [], fn($p) => strtolower($p['nom']) === strtolower($nom)));
    },

    "activer" => function(int $promoId) use ($model_tab, $json): bool {
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($json);
        if (!isset($data['promotions'])) return false;
        
        foreach ($data['promotions'] as &$promo) {
            if ($promo['id'] === $promoId) {
                $promo['statut'] = 'Active';
                
                // Calculer le nombre total d'apprenants des référentiels
                if (isset($promo['referentiel']) && is_array($promo['referentiel'])) {
                    $total_apprenants = 0;
                    foreach ($promo['referentiel'] as $ref) {
                        $total_apprenants += isset($ref['apprenants']) ? (int)$ref['apprenants'] : 0;
                    }
                    $promo['nbrApprenant'] = $total_apprenants;
                }
                break;
            }
        }
        
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $json);
    },

    "desactiver_tout" => function() use ($model_tab, $json): bool {
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($json);
        if (!isset($data['promotions'])) return false;
        
        foreach ($data['promotions'] as &$promo) {
            $promo['statut'] = 'Inactive';
        }
        
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $json);
    },

    PROMOMETHODE::AJOUTER_PROMO->value => function(array $nouvelle_promo) use ($model_tab, $json): bool {
        // Vérifier que tous les champs requis sont présents et non null
        $required_fields = ['nom', 'dateDebut', 'dateFin'];
        foreach ($required_fields as $field) {
            if (!isset($nouvelle_promo[$field]) || $nouvelle_promo[$field] === null) {
                return false;
            }
        }

        // S'assurer que les champs optionnels ont des valeurs par défaut
        $nouvelle_promo['photo'] = $nouvelle_promo['photo'] ?? '';
        $nouvelle_promo['statut'] = $nouvelle_promo['statut'] ?? 'Inactive';
        $nouvelle_promo['nbrApprenant'] = $nouvelle_promo['nbrApprenant'] ?? 0;

        // Charger les données existantes
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($json);
        
        // Récupérer les référentiels complets
        if (!empty($nouvelle_promo['referenciel_ids'])) {
            $referentiels = [];
            foreach ($data['referenciel'] as $ref) {
                if (in_array($ref['id'], $nouvelle_promo['referenciel_ids'])) {
                    $referentiels[] = $ref;
                }
            }
            $nouvelle_promo['referentiel'] = $referentiels;
        } else {
            $nouvelle_promo['referentiel'] = [];
        }
        
        // Supprimer l'ancienne structure
        unset($nouvelle_promo['referenciel_ids']);
        unset($nouvelle_promo['referenciel_id']);

        if (!isset($data['promotions'])) {
            $data['promotions'] = [];
        }
        $data['promotions'][] = $nouvelle_promo;
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $json);
    }   
];

function getPromosWithActiveFirst(array $promotions, int $perPage, int $currentPage, string $searchTerm = ''): array {
    // Trier pour mettre la promo active en premier
    usort($promotions, function($a, $b) {
        if (($a['statut'] ?? '') === 'Active' && ($b['statut'] ?? '') !== 'Active') return -1;
        if (($a['statut'] ?? '') !== 'Active' && ($b['statut'] ?? '') === 'Active') return 1;
        return 0;
    });

    // Extraire la promo active
    $promoActive = null;
    foreach ($promotions as $index => $promo) {
        if (($promo['statut'] ?? '') === 'Active') {
            $promoActive = $promo;
            unset($promotions[$index]);
            break;
        }
    }

    // Appliquer la recherche si nécessaire
    if (!empty($searchTerm)) {
        if ($promoActive && stripos($promoActive['nom'], $searchTerm) === false) {
            $promoActive = null;
        }
        $promotions = array_filter($promotions, function($promo) use ($searchTerm) {
            return stripos($promo['nom'], $searchTerm) !== false;
        });
        $promotions = array_values($promotions); // Réindexer le tableau
    }

    // Calculer la pagination
    $total = count($promotions) + ($promoActive ? 1 : 0);
    $pages = max(1, ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $pages));
    
    // Si on a une promo active, on ajuste le nombre d'éléments à paginer
    $itemsPerPage = $promoActive ? $perPage - 1 : $perPage;
    $start = ($currentPage - 1) * $itemsPerPage;
    
    // Préparer les promotions paginées
    $paginatedPromos = [];
    if ($promoActive) {
        $paginatedPromos[] = $promoActive;
    }
    
    // Ajouter les autres promotions
    $paginatedPromos = array_merge(
        $paginatedPromos,
        array_slice($promotions, $start, $itemsPerPage)
    );

    return [
        'promos' => $paginatedPromos,
        'total' => $total,
        'pages' => $pages,
        'currentPage' => $currentPage,
        'start' => $start,
        'perPage' => $perPage
    ];
}
