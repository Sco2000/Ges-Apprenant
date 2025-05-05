<?php
require_once __DIR__ . '/../enums/chemin_page.php';


use App\Enums\CheminPage;
use App\Models\JSONMETHODE;

require_once CheminPage::CONTROLLER->value;
require_once CheminPage::APPRENANT_MODEL->value;
require_once CheminPage::MODEL_ENUM->value;
require_once CheminPage::SESSION_SERVICE->value;
require_once CheminPage::VENDOR->value;


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
    $onglet = $_GET['onglet'] ?? 'retenus';

    try {
        // Récupérer la liste d'attente et appliquer les filtres si nécessaire
        $liste_attente = isset($data['liste_attente']) ? $data['liste_attente'] : [];
        
        // Appliquer les filtres à la liste d'attente si nécessaire
        if ($search) {
            $liste_attente = array_filter($liste_attente, function($apprenant) use ($search) {
                return stripos($apprenant['nom_complet'], $search) !== false;
            });
        }
        
        if ($statut) {
            $liste_attente = array_filter($liste_attente, function($apprenant) use ($statut) {
                return $apprenant['statut'] === $statut;
            });
        }
        
        // Préparation des données pour la vue
        $view_data = [
            'apprenants' => $apprenant_methodes['FILTRER_APPRENANTS']($data, $search, $referentiel_id, $statut),
            'total_apprenants' => $apprenant_methodes['COMPTER_APPRENANTS']($data),
            'referentiels' => $apprenant_methodes['GET_REFERENTIELS_PROMOTION_ACTIVE']($data),
            'liste_attente' => $liste_attente
        ];

        // Affichage de la vue
        render('apprenant/liste_apprenant', $view_data);
    } catch (Exception $e) {
        stocker_session('error', 'Une erreur est survenue lors du chargement des apprenants: ' . $e->getMessage());
        render('apprenant/liste_apprenant', ['apprenants' => [], 'total_apprenants' => 0, 'referentiels' => [], 'liste_attente' => []]);
    }
}

function importer_apprenants(): void {
    global $model_tab, $apprenant_methodes;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ?page=liste_apprenants');
        exit;
    }
    
    // Vérifier si un fichier a été uploadé
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        stocker_session('error', 'Erreur lors de l\'upload du fichier');
        header('Location: ?page=liste_apprenants');
        exit;
    }
    
    $file_tmp = $_FILES['excel_file']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
    // Vérifier l'extension du fichier
    if (!in_array($file_ext, ['xlsx', 'xls', 'csv'])) {
        stocker_session('error', 'Format de fichier non supporté. Utilisez Excel (.xlsx, .xls) ou CSV');
        header('Location: ?page=liste_apprenants');
        exit;
    }
    
    try {
        // Charger le fichier Excel avec PhpSpreadsheet (à installer via Composer)
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        if ($file_ext === 'csv') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } elseif ($file_ext === 'xlsx') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }
        
        $spreadsheet = $reader->load($file_tmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Supprimer la première ligne (en-têtes)
        $headers = array_shift($rows);
        
        // Mapper les en-têtes aux indices de colonnes
        $header_map = array_flip($headers);
        
        // Préparer les données des apprenants
        $apprenants_importes = [];
        
        foreach ($rows as $row) {
            if (empty($row[0])) continue; // Ignorer les lignes vides
            
            $apprenant = [
                'nom_complet' => $row[$header_map['nom_complet']] ?? '',
                'adresse' => $row[$header_map['adresse']] ?? '',
                'telephone' => $row[$header_map['telephone']] ?? '',
                'email' => $row[$header_map['e-mail']] ?? '',
                'referentiel' => $row[$header_map['referentiel']] ?? '',
                'nom_complet_tuteur' => $row[$header_map['nom_complet_tuteur']] ?? '',
                'lien_de_parente' => $row[$header_map['lien_de_parente']] ?? '',
                'adresse_du_tuteur' => $row[$header_map['adresse_du_tuteur']] ?? '',
                'telephone_tuteur' => $row[$header_map['telephone_tuteur']] ?? ''
            ];
            
            $apprenants_importes[] = $apprenant;
        }
        // var_dump($apprenants_importes); die;
        // Charger les données JSON existantes
        $json = CheminPage::DATA_JSON->value;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($json);
        
        // Importer les apprenants
        $resultat = $apprenant_methodes['IMPORTER_APPRENANTS']($data, $apprenants_importes);
        
        // Sauvegarder les modifications dans le fichier JSON
        $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $json);
        liste_apprenants();
        
        // Afficher un message de succès
        $message = sprintf(
            "Importation réussie: %d apprenants ajoutés, %d en liste d'attente",
            count($resultat['retenus']),
            count($resultat['attente'])
        );
        
        stocker_session('success', $message);
        
    } catch (Exception $e) {
        stocker_session('error', 'Erreur lors de l\'importation: ' . $e->getMessage());
        header('Location: ?page=liste_apprenants');
        exit;
    }
}

function export_apprenants($format): void {
    // TODO: Implémenter l'export en PDF ou Excel
}

function ajouter_apprenant(): void {
    // TODO: Implémenter l'ajout d'un apprenant
}
?>