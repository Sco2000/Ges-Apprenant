<?php
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\JSONMETHODE;

require_once CheminPage::CONTROLLER->value;
require_once CheminPage::MODEL->value;
require_once CheminPage::MODEL_ENUM->value;
require_once CheminPage::SESSION_SERVICE->value;


/**
 * Vérifie si un fichier uploadé est valide
 */
function is_valid_upload(array $file): bool {
    return $file['error'] === UPLOAD_ERR_OK;
}

/**
 * Génère un nom de fichier unique pour une photo
 */
function generate_unique_filename(string $originalName): string {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid('photo_', true) . '.' . strtolower($extension);
}

/**
 * Crée le chemin complet pour un fichier
 */
function build_complete_path(string $destination, string $filename): string {
    return rtrim($destination, '/') . '/' . $filename;
}

/**
 * Sauvegarde une photo uploadée
 */
function save_photo(array $file, string $destination): ?string {
    // Vérifier s'il y a une erreur
    if (!is_valid_upload($file)) {
        return null;
    }
    
    // Générer un nom unique
    $nomFichier = generate_unique_filename($file['name']);

    // Créer le chemin complet
    $cheminComplet = build_complete_path($destination, $nomFichier);

    // Déplacer le fichier temporaire vers le dossier final
    if (move_uploaded_file($file['tmp_name'], $cheminComplet)) {
        return $nomFichier; // On retourne juste le nom, pas tout le chemin
    }

    return null;
}

/**
 * Vérifie si un fichier existe
 */
function file_exists_safe(string $path): bool {
    return file_exists($path);
}

/**
 * Déplace une photo d'un emplacement à un autre
 */
function move_photo(string $sourcePath, string $newDestination): bool {
    if (!file_exists_safe($sourcePath)) {
        return false;
    }

    // Créer le nouveau chemin complet
    $nomFichier = basename($sourcePath);
    $nouveauChemin = build_complete_path($newDestination, $nomFichier);

    return rename($sourcePath, $nouveauChemin);
}

/**
 * Crée un répertoire s'il n'existe pas
 */
function ensure_directory_exists(string $dir): bool {
    if (!is_dir($dir)) {
        return mkdir($dir, 0775, true);
    }
    return true;
}

/**
 * Gère l'upload de la photo et retourne le chemin relatif ou un chemin par défaut
 */
function uploadPhoto(array $file, string $uploadDir, string $defaultPath = "assets/images/referenciel/default.jpg"): ?string {
    // Vérifier si le dossier existe, sinon le créer
    ensure_directory_exists($uploadDir);

    if (isset($file) && is_valid_upload($file)) {
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
 * Calcule le prochain ID pour une nouvelle promotion
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

/**
 * Vérifie si un fichier existe
 */
function check_file_exists(string $path): bool {
    return file_exists($path);
}

/**
 * Génère le contenu d'une vue
 */
function generate_view_content(string $viewPath, array $data): string {
    extract($data);
    ob_start();
    require $viewPath;
    return ob_get_clean();
}

/**
 * Récupère le nom de la promotion active
 */
function get_active_promo_name(): string {
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
    
    return $_SESSION['active_promo_name'] ?? 'Aucune promotion active';
}

/**
 * Construit le chemin absolu d'une vue
 */
function build_view_path(string $vue, string $baseViewPath): string {
    return str_ends_with($vue, '.php') ? $vue : $baseViewPath . trim($vue, '/') . '.view.php';
}

/**
 * Construit le chemin absolu d'un layout
 */
function build_layout_path(string $layout, string $baseLayoutPath): string {
    return $baseLayoutPath . trim($layout, '/') . '.php';
}

/**
 * Affiche une vue avec ou sans layout
 */
function render(string $vue, array $donnees = [], ?string $layout = 'base.layout'): void {
    $baseViewPath = dirname(__DIR__) . '/views/';
    $baseLayoutPath = $baseViewPath . 'layout/';

    // Gestion du chemin absolu ou relatif
    $cheminVue = build_view_path($vue, $baseViewPath);

    if (!check_file_exists($cheminVue)) {
        throw new Exception("Vue '$cheminVue' introuvable.");
    }

    // Création d'une copie des données pour le layout
    $layoutData = $donnees;

    // Générer le contenu de la vue
    $contenu = generate_view_content($cheminVue, $donnees);

    if ($layout !== null) {
        // Récupérer le nom de la promotion active
        if (!isset($layoutData['promo_active_nom'])) {
            $layoutData['promo_active_nom'] = get_active_promo_name();
        }

        $cheminLayout = build_layout_path($layout, $baseLayoutPath);
        if (!check_file_exists($cheminLayout)) {
            throw new Exception("Layout '$layout' introuvable.");
        }

        // Extraire les variables pour le layout
        extract($layoutData);
        require $cheminLayout;
    } else {
        echo $contenu;
    }
}

/**
 * Redirige vers une route avec des paramètres optionnels
 */
function redirect_to_route(string $route, array $params = []): void {
    // Si on a des paramètres à passer dans l'URL
    if (!empty($params)) {
        $query = http_build_query($params);
        $route .= (strpos($route, '?') === false ? '?' : '&') . $query;
    }

    header("Location: $route");
    exit(); // Toujours arrêter l'exécution après une redirection
}

?>