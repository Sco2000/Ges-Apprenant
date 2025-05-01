<?php
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\JSONMETHODE;

require_once CheminPage::MODEL->value;
require_once CheminPage::SESSION_SERVICE->value;

/**
 * Effectue une recherche globale dans les promotions et référentiels
 */
function recherche_globale(string $terme): array {
    global $model_tab;
    
    if (!isset($model_tab[JSONMETHODE::JSONTOARRAY->value])) {
        throw new Exception('Model methods not properly initialized');
    }

    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    $resultats = [];

    // Recherche dans les promotions
    if (isset($data['promotions'])) {
        $resultats['promotions'] = array_filter($data['promotions'], function($item) use ($terme) {
            return stripos($item['nom'], $terme) !== false;
        });
        $resultats['promotions'] = array_values($resultats['promotions']); // Réindexer le tableau
    }

    // Recherche dans les référentiels
    if (isset($data['referenciel'])) {
        $resultats['referenciel'] = array_filter($data['referenciel'], function($item) use ($terme) {
            return stripos($item['nom'], $terme) !== false;
        });
        $resultats['referenciel'] = array_values($resultats['referenciel']); // Réindexer le tableau
    }

    return $resultats;
}

/**
 * Affiche les résultats de recherche
 */
function afficher_resultats_recherche(): void {
    demarrer_session();
    
    $searchTerm = $_GET['global_search'] ?? '';
    
    if (empty($searchTerm)) {
        // Si aucun terme de recherche, rediriger vers la page d'accueil
        redirect_to_route('index.php?page=liste_promo');
        return;
    }
    
    try {
        $resultats = recherche_globale($searchTerm);
        render('search/search_results', [
            'resultats' => $resultats,
            'searchTerm' => $searchTerm
        ]);
    } catch (Exception $e) {
        // Gérer l'erreur
        stocker_session('error', "Une erreur est survenue lors de la recherche: " . $e->getMessage());
        redirect_to_route('index.php?page=liste_promo');
    }
}