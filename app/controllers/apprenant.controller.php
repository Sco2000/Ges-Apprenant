<?php
require_once __DIR__ . '/../enums/chemin_page.php';
require_once __DIR__ . '/../enums/model.enum.php';
require_once __DIR__ . '/../models/apprenant.model.php';
require_once __DIR__ . '/controller.php';

use App\Enums\CheminPage;
use App\Models\JSONMETHODE;

require_once CheminPage::MODEL->value;
require_once CheminPage::SESSION_SERVICE->value;

function liste_apprenants(): void {
    global $model_tab, $apprenant_methodes;
    
    if (!isset($apprenant_methodes)) {
        stocker_session('error', 'Erreur lors du chargement des apprenants');
        render('apprenant/liste_apprenant', ['apprenants' => [], 'total_apprenants' => 0, 'referentiels' => []]);
        return;
    }
    
    $json = CheminPage::DATA_JSON->value;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($json);

    // Récupération des paramètres de filtrage
    $search = $_GET['search'] ?? null;
    $referentiel_id = $_GET['referentiel'] ?? null;
    $statut = $_GET['statut'] ?? null;

    try {
        // Préparation des données pour la vue
        $view_data = [
            'apprenants' => $apprenant_methodes['FILTRER_APPRENANTS']($data, $search, $referentiel_id, $statut),
            'total_apprenants' => $apprenant_methodes['COMPTER_APPRENANTS']($data),
            'referentiels' => $apprenant_methodes['GET_REFERENTIELS_PROMOTION_ACTIVE']($data)
        ];

        // Affichage de la vue
        render('apprenant/liste_apprenant', $view_data);
    } catch (Exception $e) {
        stocker_session('error', 'Une erreur est survenue lors du chargement des apprenants');
        render('apprenant/liste_apprenant', ['apprenants' => [], 'total_apprenants' => 0, 'referentiels' => []]);
    }
}

function export_apprenants($format): void {
    // TODO: Implémenter l'export en PDF ou Excel
}

function ajouter_apprenant(): void {
    // TODO: Implémenter l'ajout d'un apprenant
}
?>