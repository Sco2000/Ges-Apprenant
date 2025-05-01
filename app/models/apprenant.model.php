<?php
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\APPRENANTMETHODE;

require_once CheminPage::MODEL_ENUM->value;

global $apprenant_methodes;
$apprenant_methodes = [];

// Fonction anonyme pour filtrer les promotions actives
$filtrer_promotions_actives = fn(array $promotions): array => 
    array_filter($promotions, fn($p) => $p['statut'] === 'Active');

// Fonction anonyme pour compter les apprenants d'un référentiel
$compter_apprenants_referentiel = fn(array $referentiel): int => 
    isset($referentiel['apprenant']) ? count($referentiel['apprenant']) : 0;

// Fonction anonyme pour calculer le total d'apprenants d'un ensemble de référentiels
$calculer_total_apprenants_referentiels = function(array $referentiels) use ($compter_apprenants_referentiel): int {
    return array_reduce(
        $referentiels,
        fn($sum, $ref) => $sum + $compter_apprenants_referentiel($ref),
        0
    );
};

// Fonction anonyme pour calculer le total d'apprenants de toutes les promotions actives
$calculer_total_apprenants_promotions = function(array $promotions) use ($filtrer_promotions_actives, $calculer_total_apprenants_referentiels): int {
    $promotions_actives = $filtrer_promotions_actives($promotions);
    
    return array_reduce(
        $promotions_actives,
        fn($total, $promo) => $total + $calculer_total_apprenants_referentiels($promo['referentiel'] ?? []),
        0
    );
};

// Fonction anonyme pour vérifier si un apprenant correspond aux critères de recherche
$apprenant_correspond_criteres = function(array $apprenant, ?string $search, ?string $statut): bool {
    if ($statut && $apprenant['statut'] !== $statut) return false;
    if ($search && !str_contains(strtolower($apprenant['nom_complet']), strtolower($search))) return false;
    return true;
};

// Fonction anonyme pour extraire les apprenants d'un référentiel avec les critères de filtrage
$extraire_apprenants_referentiel = function(array $referentiel, ?string $search, ?string $statut) use ($apprenant_correspond_criteres): array {
    if (!isset($referentiel['apprenant'])) return [];
    
    $resultats = [];
    foreach ($referentiel['apprenant'] as $apprenant) {
        if ($apprenant_correspond_criteres($apprenant, $search, $statut)) {
            $resultats[] = array_merge($apprenant, [
                'referentiel_id' => $referentiel['id'],
                'referentiel_nom' => $referentiel['nom']
            ]);
        }
    }
    
    return $resultats;
};

// Fonction anonyme pour extraire les apprenants d'une promotion avec les critères de filtrage
$extraire_apprenants_promotion = function(array $promotion, ?string $search, ?string $statut, ?string $referentiel_id) use ($extraire_apprenants_referentiel): array {
    if ($promotion['statut'] !== 'Active') return [];
    
    $resultats = [];
    foreach ($promotion['referentiel'] as $ref) {
        if ($referentiel_id && $ref['id'] != $referentiel_id) continue;
        
        $apprenants_referentiel = $extraire_apprenants_referentiel($ref, $search, $statut);
        $resultats = array_merge($resultats, $apprenants_referentiel);
    }
    
    return $resultats;
};

// Fonction anonyme pour trouver la promotion active
$trouver_promotion_active = function(array $promotions): ?array {
    $promotions_actives = array_filter($promotions, fn($p) => $p['statut'] === 'Active');
    return !empty($promotions_actives) ? reset($promotions_actives) : null;
};

// Implémentation de COMPTER_APPRENANTS
$apprenant_methodes[APPRENANTMETHODE::COMPTER_APPRENANTS->value] = function(array $data) use ($calculer_total_apprenants_promotions): int {
    return $calculer_total_apprenants_promotions($data['promotions'] ?? []);
};

// Implémentation de FILTRER_APPRENANTS
$apprenant_methodes[APPRENANTMETHODE::FILTRER_APPRENANTS->value] = function(array $data, ?string $search = null, ?string $referentiel_id = null, ?string $statut = null) use ($extraire_apprenants_promotion): array {
    $resultats = [];
    
    foreach ($data['promotions'] as $promo) {
        $apprenants_promotion = $extraire_apprenants_promotion($promo, $search, $statut, $referentiel_id);
        $resultats = array_merge($resultats, $apprenants_promotion);
    }
    
    return $resultats;
};

// Implémentation de GET_APPRENANTS_PROMOTION_ACTIVE
$apprenant_methodes[APPRENANTMETHODE::GET_APPRENANTS_PROMOTION_ACTIVE->value] = function(array $data) use ($trouver_promotion_active): array {
    $promo_active = $trouver_promotion_active($data['promotions'] ?? []);
    return $promo_active ?: [];
};

// Implémentation de GET_REFERENTIELS_PROMOTION_ACTIVE
$apprenant_methodes[APPRENANTMETHODE::GET_REFERENTIELS_PROMOTION_ACTIVE->value] = function(array $data) use ($trouver_promotion_active): array {
    $promo_active = $trouver_promotion_active($data['promotions'] ?? []);
    return $promo_active['referentiel'] ?? [];
};

// On retourne aussi le tableau pour maintenir la compatibilité
return $apprenant_methodes;