<?php
declare(strict_types=1);
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\JSONMETHODE;
use App\Models\AUTHMETHODE;

require_once CheminPage::MODEL_ENUM->value;

global $auth_model;

// Fonction anonyme pour charger les utilisateurs depuis le fichier JSON
$charger_utilisateurs = function(string $chemin): array {
    global $model_tab;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
    return $data['utilisateurs'] ?? [];
};

// Fonction anonyme pour rechercher un utilisateur par son login
$rechercher_utilisateur_par_login = function(array $utilisateurs, string $login): ?array {
    $utilisateurs_filtres = array_values(array_filter($utilisateurs, fn($u) => $u['login'] === $login));
    return $utilisateurs_filtres[0] ?? null;
};

// Fonction anonyme pour vérifier si le mot de passe correspond à celui de l'utilisateur
$verifier_mot_de_passe = function(array $utilisateur, string $password): bool {
    return $utilisateur['password'] === $password;
};

// Fonction anonyme pour mettre à jour le mot de passe d'un utilisateur
$mettre_a_jour_mot_de_passe = function(array $utilisateurs, string $login, string $newPassword): array {
    return array_map(function ($u) use ($login, $newPassword) {
        if ($u['login'] === $login) {
            $u['password'] = $newPassword;
        }
        return $u;
    }, $utilisateurs);
};

// Fonction anonyme pour sauvegarder les données des utilisateurs dans le fichier JSON
$sauvegarder_utilisateurs = function(array $utilisateurs, string $chemin): bool {
    global $model_tab;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
    $data['utilisateurs'] = $utilisateurs;
    return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $chemin);
};

$auth_model = [
    AUTHMETHODE::LOGIN->value => function (string $login, string $password, string $chemin) use ($charger_utilisateurs, $rechercher_utilisateur_par_login, $verifier_mot_de_passe): ?array {
        $utilisateurs = $charger_utilisateurs($chemin);
        $utilisateur = $rechercher_utilisateur_par_login($utilisateurs, $login);
        
        if ($utilisateur && $verifier_mot_de_passe($utilisateur, $password)) {
            return $utilisateur;
        }
        
        return null;
    },
    
    AUTHMETHODE::RESET_PASSWORD->value => function (string $login, string $newPassword, string $chemin) use ($charger_utilisateurs, $rechercher_utilisateur_par_login, $mettre_a_jour_mot_de_passe, $sauvegarder_utilisateurs): bool {
        $utilisateurs = $charger_utilisateurs($chemin);
        $utilisateur = $rechercher_utilisateur_par_login($utilisateurs, $login);
        
        if (!$utilisateur) {
            return false;
        }
        
        $utilisateurs_mis_a_jour = $mettre_a_jour_mot_de_passe($utilisateurs, $login, $newPassword);
        return $sauvegarder_utilisateurs($utilisateurs_mis_a_jour, $chemin);
    }
];
