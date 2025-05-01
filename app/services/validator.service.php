<?php
declare(strict_types=1);
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\ENUM\VALIDATOR\VALIDATORMETHODE;
use App\ENUM\ERREUR\ErreurEnum;

require_once CheminPage::ERROR_ENUM->value;
require_once CheminPage::VALIDATOR_ENUM->value;

global $validator;
$validator = [
    // Vérifie si le login est un email valide
    VALIDATORMETHODE::EMAIL->value => function (string $email): ?string {
        if (empty($email)) {
            return ErreurEnum::LOGIN_REQUIRED->value;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ErreurEnum::LOGIN_EMAIL->value;
        }
        return null;
    },

    // Vérifie si le mot de passe est valide
    VALIDATORMETHODE::PASSWORD->value => function (string $password): ?string {
        if (empty($password)) {
            return ErreurEnum::PASSWORD_REQUIRED->value;
        }
        if (strlen($password) < 6) {
            return 'password.invalid';
        }
        return null;
    },

    // Combine les deux vérifications
    VALIDATORMETHODE::USER->value => function (string $email, string $password) use (&$validator): array {
        $erreurs = [];

        $email_error = $validator[VALIDATORMETHODE::EMAIL->value]($email);
        if ($email_error) {
            $erreurs['login'] = $email_error;
        }

        $password_error = $validator[VALIDATORMETHODE::PASSWORD->value]($password);
        if ($password_error) {
            $erreurs['password'] = $password_error;
        }

        return $erreurs;
    },

    VALIDATORMETHODE::PROMO->value => function (string $promo_name): ?string {
        if (empty($promo_name)) {
            return ErreurEnum::PROMO_NAME_REQUIRED->value;
        }
        return null;
    },
    VALIDATORMETHODE::PROMO_DATE->value => function (string $date): ?string {
        if (empty($date)) {
            return ErreurEnum::PROMO_DATE_REQUIRED->value;
        }
        return null;
    },

    VALIDATORMETHODE::PROMO_date_valide->value => function (string $dateDebut, string $dateFin): ?string {
        $startDate = DateTime::createFromFormat('d-m-y', $dateDebut);
        $endDate = DateTime::createFromFormat('d-m-y', $dateFin);

        if (!$startDate || !$endDate) {
            return ErreurEnum::PROMO_date_norme->value;
        }

        if ($startDate > $endDate) {
            return ErreurEnum::PROMO_date_inferieur->value;
        }

        return null;
    },

    VALIDATORMETHODE::REF_NOM->value => function(?string $nom): ?string {
        if (empty($nom)) {
            return ErreurEnum::REF_NOM_REQUIRED->value;
        }
        if (strlen(trim($nom)) < 3) {
            return ErreurEnum::REF_NOM_LENGTH->value;
        }
        return null;
    },

    VALIDATORMETHODE::REF_DESCRIPTION->value => function(?string $description): ?string {
        if (empty($description)) {
            return ErreurEnum::REF_DESCRIPTION_REQUIRED->value;
        }
        return null;
    },

    VALIDATORMETHODE::REF_CAPACITE->value => function($capacite): ?string {
        if (empty($capacite)) {
            return ErreurEnum::REF_CAPACITE_REQUIRED->value;
        }
        if (!is_numeric($capacite) || (int)$capacite <= 0) {
            return ErreurEnum::REF_CAPACITE_INVALID->value;
        }
        return null;
    },

    VALIDATORMETHODE::REF_SESSIONS->value => function($sessions): ?string {
        if (empty($sessions)) {
            return ErreurEnum::REF_SESSIONS_REQUIRED->value;
        }
        if (!is_numeric($sessions) || (int)$sessions <= 0 || (int)$sessions > 4) {
            return ErreurEnum::REF_SESSIONS_INVALID->value;
        }
        return null;
    },
    VALIDATORMETHODE::VALID_GENERAL->value => function(array $data): array {
        $errors = [];

        // Nom obligatoire
        if (!isset($data['nom_promo']) || $data['nom_promo'] === null || trim($data['nom_promo']) === '') {
            $errors['nom_promo'] = 'Le nom de la promotion est obligatoire.';
        } else {
            // Unicité du nom
            $chemin = \App\Enums\CheminPage::DATA_JSON->value;
            if (file_exists($chemin)) {
                $contenu = json_decode(file_get_contents($chemin), true);
                $promos = $contenu['promotions'] ?? [];

                $nom_saisi = strtolower(trim($data['nom_promo']));
                $doublon = array_filter($promos, function($p) use ($nom_saisi) {
                    return isset($p['nom']) && $p['nom'] !== null && strtolower($p['nom']) === $nom_saisi;
                });

                if (!empty($doublon)) {
                    $errors['nom_promo'] = 'Ce nom de promotion existe déjà.';
                }
            }
        }

        // Dates obligatoires + format
        if (!isset($data['date_debut']) || $data['date_debut'] === null || trim($data['date_debut']) === '') {
            $errors['date_debut'] = 'La date de début est obligatoire.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_debut'])) {
            $errors['date_debut'] = 'La date doit être au format YYYY-MM-DD.';
        }

        if (!isset($data['date_fin']) || $data['date_fin'] === null || trim($data['date_fin']) === '') {
            $errors['date_fin'] = 'La date de fin est obligatoire.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_fin'])) {
            $errors['date_fin'] = 'La date doit être au format YYYY-MM-DD.';
        }

        // Comparaison des dates
        if (!empty($data['date_debut']) && !empty($data['date_fin'])) {
            $dateDebut = strtotime($data['date_debut']);
            $dateFin = strtotime($data['date_fin']);
            if ($dateDebut && $dateFin && $dateDebut >= $dateFin) {
                $errors['date_fin'] = 'La date de fin doit être postérieure à la date de début.';
            }
        }

        // Photo - rendre optionnelle mais valider si présente
        if (isset($data['photo']) && $data['photo'] !== null) {
            if (is_array($data['photo']) && $data['photo']['error'] !== UPLOAD_ERR_OK && $data['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $errors['photo'] = 'Erreur lors du téléchargement de la photo.';
            }
        }

        // Initialiser les valeurs par défaut pour les champs optionnels
        if (!isset($data['referenciels']) || $data['referenciels'] === null) {
            $data['referenciels'] = [];
        }

        if (!isset($data['nbr_apprenants']) || $data['nbr_apprenants'] === null) {
            $data['nbr_apprenants'] = 0;
        } elseif (!is_numeric($data['nbr_apprenants']) || (int)$data['nbr_apprenants'] < 0) {
            $errors['nbr_apprenants'] = 'Le nombre d\'apprenants doit être un nombre positif ou nul.';
        }

        return $errors;
    }
];


