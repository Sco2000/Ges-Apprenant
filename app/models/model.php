<?php
declare(strict_types=1);
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\JSONMETHODE;

require_once CheminPage::MODEL_ENUM->value;

// Fonction anonyme pour vérifier si un fichier existe
$fichier_existe = function(string $cheminFichier): bool {
    return file_exists($cheminFichier);
};

// Fonction anonyme pour lire le contenu d'un fichier
$lire_contenu_fichier = function(string $cheminFichier): string {
    return file_get_contents($cheminFichier) ?: '';
};

// Fonction anonyme pour écrire dans un fichier
$ecrire_dans_fichier = function(string $cheminFichier, string $contenu): bool {
    return file_put_contents($cheminFichier, $contenu) !== false;
};

// Fonction anonyme pour encoder un tableau en JSON
$encoder_json = function(array $tableau): string {
    return json_encode($tableau, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
};

// Fonction anonyme pour décoder du JSON en tableau
$decoder_json = function(string $json): array {
    $tableau = json_decode($json, true);
    return is_array($tableau) ? $tableau : [];
};

// Fonction anonyme pour extraire une clé d'un tableau
$extraire_cle_tableau = function(array $tableau, ?string $cle): array {
    if ($cle !== null && array_key_exists($cle, $tableau)) {
        return $tableau[$cle];
    }
    return $tableau;
};

$model_tab = [
    // Convertit un tableau en JSON et l'enregistre dans un fichier
    JSONMETHODE::ARRAYTOJSON->value => function(array $tableau, string $cheminFichier) use ($encoder_json, $ecrire_dans_fichier): bool {
        $json = $encoder_json($tableau);
        return $ecrire_dans_fichier($cheminFichier, $json);
    },
    
    // Lit un fichier JSON et retourne le tableau complet OU une partie via une clé
    JSONMETHODE::JSONTOARRAY->value => function(string $cheminFichier, ?string $cle = null) use ($fichier_existe, $lire_contenu_fichier, $decoder_json, $extraire_cle_tableau): array {
        if (!$fichier_existe($cheminFichier)) {
            return [];
        }
        
        $contenu = $lire_contenu_fichier($cheminFichier);
        $tableau = $decoder_json($contenu);
        
        return $extraire_cle_tableau($tableau, $cle);
    }
];