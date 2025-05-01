<?php
declare(strict_types=1);
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Models\REFMETHODE;
use App\Models\JSONMETHODE;
use App\Enums\CheminPage;

require_once CheminPage::MODEL_ENUM->value;
require_once CheminPage::MODEL->value;

global $model_tab; // Declare global at the top level
global $ref_model;

// Fonctions utilitaires pour les référentiels
$charger_donnees = function(string $chemin, ?string $cle = null): array {
    global $model_tab;
    return $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin, $cle);
};

$sauvegarder_donnees = function(array $donnees, string $chemin): bool {
    global $model_tab;
    return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($donnees, $chemin);
};

$trouver_referentiel_par_id = function(array $referentiels, int $id): ?array {
    foreach ($referentiels as $ref) {
        if ($ref['id'] === $id) {
            return $ref;
        }
    }
    return null;
};

$trouver_promotion_active = function(array $promotions): ?array {
    foreach ($promotions as $promo) {
        if ($promo['statut'] === 'Active') {
            return $promo;
        }
    }
    return null;
};

$calculer_total_apprenants = function(array $referentiels): int {
    $total = 0;
    foreach ($referentiels as $ref) {
        $total += isset($ref['apprenants']) ? (int)$ref['apprenants'] : 0;
    }
    return $total;
};

$verifier_annee_promotion = function(array $promo): bool {
    $promoYear = date('Y', strtotime($promo['dateDebut']));
    $currentYear = date('Y');
    return $promoYear == $currentYear;
};

$ref_model = [
    // Get all referentiels
    REFMETHODE::GET_ALL->value => function() use ($charger_donnees): array {
        return $charger_donnees(CheminPage::DATA_JSON->value, 'referenciel');
    },

    // Get referentiel by ID
    REFMETHODE::GET_BY_ID->value => function(int $id) use ($charger_donnees, $trouver_referentiel_par_id): ?array {
        $referentiels = $charger_donnees(CheminPage::DATA_JSON->value, 'referenciel');
        return $trouver_referentiel_par_id($referentiels, $id);
    },

    // Get referentiels by active promotion
    REFMETHODE::GET_BY_ACTIVE_PROMO->value => function() use ($charger_donnees, $trouver_promotion_active): array {
        $data = $charger_donnees(CheminPage::DATA_JSON->value);
        
        // Trouver la promotion active
        $active_promo = null;
        if (isset($data['promotions'])) {
            $active_promo = $trouver_promotion_active($data['promotions']);
        }
        
        // Si pas de promotion active ou pas de référentiels
        if (!$active_promo || empty($active_promo['referentiel'])) {
            return [];
        }
        
        // Retourner directement les référentiels de la promotion active
        return $active_promo['referentiel'];
    },

    // Add new referentiel
    REFMETHODE::AJOUTER->value => function(array $referentiel) use ($charger_donnees, $sauvegarder_donnees): bool {
        $data = $charger_donnees(CheminPage::DATA_JSON->value);
        
        if (!isset($data['referenciel'])) {
            $data['referenciel'] = [];
        }
        
        $data['referenciel'][] = $referentiel;
        return $sauvegarder_donnees($data, CheminPage::DATA_JSON->value);
    },

    REFMETHODE::UPDATE_NBR_APPRENANT->value => function(int $ref_id, int $nb_apprenants) use ($charger_donnees, $sauvegarder_donnees): bool {
        $data = $charger_donnees(CheminPage::DATA_JSON->value);
        
        // Mettre à jour le référentiel principal
        foreach ($data['referenciel'] as &$ref) {
            if ($ref['id'] === $ref_id) {
                $ref['apprenants'] = $nb_apprenants;
                break;
            }
        }
        
        return $sauvegarder_donnees($data, CheminPage::DATA_JSON->value);
    },

    REFMETHODE::GET_TOTAL_APPRENANTS->value => function(array $referentiels) use ($calculer_total_apprenants): int {
        return $calculer_total_apprenants($referentiels);
    },
];

global $ref_promo_model;

// Fonctions utilitaires pour les référentiels de promotion
$trouver_promotion_par_id = function(array $promotions, int $id): ?array {
    foreach ($promotions as $promo) {
        if ($promo['id'] === $id) {
            return $promo;
        }
    }
    return null;
};

$verifier_referentiel_existe = function(array $referentiels, int $ref_id): bool {
    foreach ($referentiels as $ref) {
        if ($ref['id'] === $ref_id) {
            return true;
        }
    }
    return false;
};

$ref_promo_model = [
    'get_active_promo' => function() use ($charger_donnees, $trouver_promotion_active): ?array {
        $data = $charger_donnees(CheminPage::DATA_JSON->value);
        $promotions = $data['promotions'] ?? [];
        return $trouver_promotion_active($promotions);
    },
    
    'get_refs_by_promo' => function(int $promo_id) use ($charger_donnees, $trouver_promotion_par_id): array {
        $data = $charger_donnees(CheminPage::DATA_JSON->value);
        $promotions = $data['promotions'] ?? [];
        $promo = $trouver_promotion_par_id($promotions, $promo_id);
        
        return $promo ? ($promo['referentiel'] ?? []) : [];
    },
    
    'affecter_ref' => function(int $promo_id, int $ref_id) use ($charger_donnees, $sauvegarder_donnees, $trouver_promotion_par_id, $verifier_annee_promotion, $verifier_referentiel_existe): bool {
        $data = $charger_donnees(CheminPage::DATA_JSON->value);
        
        // Trouver la promotion
        $promotions = $data['promotions'] ?? [];
        $promo = $trouver_promotion_par_id($promotions, $promo_id);
        
        if (!$promo) {
            return false;
        }
        
        // Vérifier si l'année de la promotion correspond à l'année en cours
        if (!$verifier_annee_promotion($promo)) {
            return false;
        }
        
        // Trouver le référentiel complet
        $referentiels = $data['referenciel'] ?? [];
        $referentiel = null;
        foreach ($referentiels as $ref) {
            if ($ref['id'] === $ref_id) {
                $referentiel = $ref;
                break;
            }
        }
        
        if (!$referentiel) {
            return false;
        }
        
        // Ajouter le référentiel à la promotion
        foreach ($data['promotions'] as &$promotion) {
            if ($promotion['id'] === $promo_id) {
                if (!isset($promotion['referentiel'])) {
                    $promotion['referentiel'] = [];
                }
                
                // Vérifier si le référentiel n'est pas déjà affecté
                $exists = $verifier_referentiel_existe($promotion['referentiel'], $ref_id);
                
                if (!$exists) {
                    $promotion['referentiel'][] = $referentiel;
                }
                
                // Supprimer l'ancienne structure si elle existe
                unset($promotion['referenciel_ids']);
                unset($promotion['referenciel_id']);
                break;
            }
        }
        
        return $sauvegarder_donnees($data, CheminPage::DATA_JSON->value);
    },
    
    'desaffecter_ref' => function(int $promo_id, int $ref_id) use ($charger_donnees, $sauvegarder_donnees, $trouver_promotion_par_id, $verifier_annee_promotion): bool {
        $data = $charger_donnees(CheminPage::DATA_JSON->value);
        
        // Trouver la promotion
        $promotions = $data['promotions'] ?? [];
        $promo = $trouver_promotion_par_id($promotions, $promo_id);
        
        if (!$promo) {
            return false;
        }
        
        // Vérifier si l'année de la promotion correspond à l'année en cours
        if (!$verifier_annee_promotion($promo)) {
            return false;
        }
        
        // Trouver la promotion et retirer le référentiel
        foreach ($data['promotions'] as &$promotion) {
            if ($promotion['id'] === $promo_id) {
                $promotion['referentiel'] = array_filter(
                    $promotion['referentiel'] ?? [], 
                    fn($ref) => $ref['id'] !== $ref_id
                );
                $promotion['referentiel'] = array_values($promotion['referentiel']); // Réindexer le tableau
                return $sauvegarder_donnees($data, CheminPage::DATA_JSON->value);
            }
        }
        
        return false;
    }
];
