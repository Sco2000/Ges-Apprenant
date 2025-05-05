<?php
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\JSONMETHODE;
require_once CheminPage::CONTROLLER->value;
require_once CheminPage::MODEL->value;
require_once CheminPage::MODEL_ENUM->value;
require_once CheminPage::SESSION_SERVICE->value;

function save_photo(array $file, string $destination): ?string {
    // Vérifier s'il y a une erreur
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    // Extraire l'extension
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

    // Générer un nom unique
    $nomFichier = uniqid('photo_', true) . '.' . strtolower($extension);

    // Créer le chemin complet
    $cheminComplet = rtrim($destination, '/') . '/' . $nomFichier;

    // Déplacer le fichier temporaire vers le dossier final
    if (move_uploaded_file($file['tmp_name'], $cheminComplet)) {
        return $nomFichier; // On retourne juste le nom, pas tout le chemin
    }

    return null;
}

function move_photo(string $sourcePath, string $newDestination): bool {
    if (!file_exists($sourcePath)) {
        return false;
    }

    // Créer le nouveau chemin complet
    $nomFichier = basename($sourcePath);
    $nouveauChemin = rtrim($newDestination, '/') . '/' . $nomFichier;

    return rename($sourcePath, $nouveauChemin);
}

/**
 * Gère l'upload de la photo et retourne le chemin relatif ou un chemin par défaut.
 */
function uploadPhoto(array $file, string $uploadDir, string $defaultPath = "assets/images/referenciel/default.jpg"): ?string {
    // Vérifier si le dossier existe, sinon le créer
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $photoName = basename($file['name']);
        $photoPath = $uploadDir . $photoName;
        $relativePath = "assets/images/referenciel/" . $photoName;

        if (move_uploaded_file($file['tmp_name'], $photoPath)) {
            return $relativePath;
        }
        return null;
    }

    return $defaultPath;
}


/**
 * Calcule le prochain ID pour une nouvelle promotion.
 */

function getNextPromoId(array $promotions): int {
    $lastId = 0;
    foreach ($promotions as $promo) {
        if ($promo['id'] > $lastId) {
            $lastId = $promo['id'];
        }
    }
    return $lastId + 1;
}

function render(string $vue, array $donnees = [], ?string $layout = 'base.layout'): void {
    $baseViewPath = dirname(__DIR__) . '/views/';
    $baseLayoutPath = $baseViewPath . 'layout/';

    // Gestion du chemin absolu ou relatif
    $cheminVue = str_ends_with($vue, '.php') ? $vue : $baseViewPath . trim($vue, '/') . '.view.php';

    if (!file_exists($cheminVue)) {
        throw new Exception("Vue '$cheminVue' introuvable.");
    }

    // Création d'une copie des données pour le layout
    $layoutData = $donnees;

    // Générer le contenu de la vue
    extract($donnees);
    ob_start();
    require $cheminVue;
    $contenu = ob_get_clean();

    if ($layout !== null) {
        // Récupérer le nom de la promotion active
        if (!isset($layoutData['promo_active_nom'])) {
            global $model_tab;
            if (!isset($_SESSION['active_promo_name'])) {
                $json_data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
                if (isset($json_data['promotions'])) {
                    foreach ($json_data['promotions'] as $promo) {
                        if (($promo['statut'] ?? '') === 'Active') {
                            $_SESSION['active_promo_name'] = $promo['nom'];
                            break;
                        }
                    }
                }
            }
            $layoutData['promo_active_nom'] = $_SESSION['active_promo_name'] ?? 'Aucune promotion active';
        }

        $cheminLayout = $baseLayoutPath . trim($layout, '/') . '.php';
        if (!file_exists($cheminLayout)) {
            throw new Exception("Layout '$layout' introuvable.");
        }

        // Extraire les variables pour le layout
        extract($layoutData);
        require $cheminLayout;
    } else {
        echo $contenu;
    }
}

function redirect_to_route(string $route, array $params = []): void {
    // Si on a des paramètres à passer dans l'URL
    if (!empty($params)) {
        $query = http_build_query($params);
        $route .= (strpos($route, '?') === false ? '?' : '&') . $query;
    }

    header("Location: $route");
    exit(); // Toujours arrêter l'exécution après une redirection
}

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
    }

    // Recherche dans les référentiels
    if (isset($data['referenciel'])) {
        $resultats['referenciel'] = array_filter($data['referenciel'], function($item) use ($terme) {
            return stripos($item['nom'], $terme) !== false;
        });
    }

    // Recherche dans les utilisateurs (apprenants)
    if (isset($data['utilisateurs'])) {
        $resultats['utilisateurs'] = array_filter($data['utilisateurs'], function($item) use ($terme) {
            return stripos($item['nom'], $terme) !== false || 
                   stripos($item['login'], $terme) !== false;
        });
    }

    return $resultats;
}

function traiter_recherche_globale(): void {
    if (isset($_GET['global_search']) && !empty($_GET['global_search'])) {
        try {
            $terme = $_GET['global_search'];
            $resultats = recherche_globale($terme);
            // stocker_session('resultats_recherche', $resultats);
        } catch (Exception $e) {
            // Handle the error appropriately
            error_log($e->getMessage());
        }
    }
}

traiter_recherche_globale();
?>