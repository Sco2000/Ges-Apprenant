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

$ref_model = [
    // Get all referentiels
    REFMETHODE::GET_ALL->value => function(): array {
        global $model_tab;
        return $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value, 'referenciel');
    },

    // Get referentiel by ID
    REFMETHODE::GET_BY_ID->value => function(int $id): ?array {
        global $model_tab;
        $referentiels = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value, 'referenciel');
        foreach ($referentiels as $ref) {
            if ($ref['id'] === $id) {
                return $ref;
            }
        }
        return null;
    },

    // Get referentiels by active promotion
    REFMETHODE::GET_BY_ACTIVE_PROMO->value => function(): array {
        global $model_tab;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
        
        // Trouver la promotion active
        $active_promo = null;
        if (isset($data['promotions'])) {
            foreach ($data['promotions'] as $promo) {
                if ($promo['statut'] === 'Active') {
                    $active_promo = $promo;
                    break;
                }
            }
        }
        
        // Si pas de promotion active ou pas de référentiels
        if (!$active_promo || empty($active_promo['referentiel'])) {
            return [];
        }
        
        // Retourner directement les référentiels de la promotion active
        return $active_promo['referentiel'];
    },

    // Add new referentiel
    REFMETHODE::AJOUTER->value => function(array $referentiel): bool {
        global $model_tab;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
        
        if (!isset($data['referenciel'])) {
            $data['referenciel'] = [];
        }
        
        $data['referenciel'][] = $referentiel;
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, CheminPage::DATA_JSON->value);
    },

    REFMETHODE::UPDATE_NBR_APPRENANT->value => function(int $ref_id, int $nb_apprenants) use ($model_tab): bool {
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
        
        // Mettre à jour le référentiel principal
        foreach ($data['referenciel'] as &$ref) {
            if ($ref['id'] === $ref_id) {
                $ref['apprenants'] = $nb_apprenants;
                break;
            }
        }
        
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, CheminPage::DATA_JSON->value);
    },

    REFMETHODE::GET_TOTAL_APPRENANTS->value => function(array $referentiels): int {
        $total = 0;
        foreach ($referentiels as $ref) {
            $total += isset($ref['apprenants']) ? (int)$ref['apprenants'] : 0;
        }
        return $total;
    },
];

global $ref_promo_model;

$ref_promo_model = [
    'get_active_promo' => function() use ($model_tab): ?array {
        global $model_tab; // Also declare inside functions that use it
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
        return array_filter($data['promotions'] ?? [], fn($p) => $p['statut'] === 'Active');
    },
    
    'get_refs_by_promo' => function(int $promo_id) use ($model_tab): array {
        global $model_tab;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
        
        foreach ($data['promotions'] ?? [] as $promo) {
            if ($promo['id'] === $promo_id) {
                return $promo['referentiel'] ?? [];
            }
        }
        
        return [];
    },
    
    'affecter_ref' => function(int $promo_id, int $ref_id) use ($model_tab): bool {
        global $model_tab;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
        
        // Trouver la promotion
        $promo = null;
        foreach ($data['promotions'] as $p) {
            if ($p['id'] === $promo_id) {
                $promo = $p;
                break;
            }
        }
        
        if (!$promo) {
            return false;
        }
        
        // Vérifier si l'année de la promotion correspond à l'année en cours
        $promoYear = date('Y', strtotime($promo['dateDebut']));
        $currentYear = date('Y');
        
        if ($promoYear != $currentYear) {
            return false;
        }
        
        // Trouver le référentiel complet
        $referentiel = null;
        foreach ($data['referenciel'] as $ref) {
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
                $exists = false;
                foreach ($promotion['referentiel'] as $ref) {
                    if ($ref['id'] === $ref_id) {
                        $exists = true;
                        break;
                    }
                }
                
                if (!$exists) {
                    $promotion['referentiel'][] = $referentiel;
                }
                
                // Supprimer l'ancienne structure si elle existe
                unset($promotion['referenciel_ids']);
                unset($promotion['referenciel_id']);
                break;
            }
        }
        
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, CheminPage::DATA_JSON->value);
    },
    
    'desaffecter_ref' => function(int $promo_id, int $ref_id) use ($model_tab): bool {
        global $model_tab;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
        
        // Trouver la promotion
        $promo = null;
        foreach ($data['promotions'] as $p) {
            if ($p['id'] === $promo_id) {
                $promo = $p;
                break;
            }
        }
        
        if (!$promo) {
            return false;
        }
        
        // Vérifier si l'année de la promotion correspond à l'année en cours
        $promoYear = date('Y', strtotime($promo['dateDebut']));
        $currentYear = date('Y');
        
        if ($promoYear != $currentYear) {
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
                return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, CheminPage::DATA_JSON->value);
            }
        }
        
        return false;
    }
];

