<?php
require_once __DIR__ . '/../../../enums/erreur.enum.php';

use App\ENUM\ERREUR\ErreurEnum;

/**
 * Tableau de traduction des messages d'erreur en français
 * Les clés correspondent aux valeurs de l'énumération ErreurEnum
 */
$error = [
    // Erreurs liées à l'authentification
    ErreurEnum::LOGIN_REQUIRED->value => "L'email est requis.",
    ErreurEnum::LOGIN_EMAIL->value => "L'email doit être une adresse email valide.",
    ErreurEnum::PASSWORD_REQUIRED->value => "Le mot de passe est requis.",
    ErreurEnum::PASSWORD_INVALID->value => "Le mot de passe doit contenir au moins 6 caractères.",
    ErreurEnum::LOGIN_INCORRECT->value => "L'email ou le mot de passe est incorrect.",

    // Erreurs liées aux promotions
    ErreurEnum::PROMO_ID_REQUIRED->value => "L'identifiant de la promotion est requis.",
    ErreurEnum::PROMO_NAME_REQUIRED->value => "Le nom de la promotion est requis.",
    ErreurEnum::PROMO_DATE_REQUIRED->value => "Les dates de début et de fin sont requises.",
    ErreurEnum::PROMO_DATE_INVALID_RANGE->value => "La date de début doit être antérieure à la date de fin.",
    ErreurEnum::PROMO_DATE_INVALID_FORMAT->value => "Les dates doivent être au format YYYY-MM-DD.",
    ErreurEnum::PROMO_ADD_FAILED->value => "Échec de l'ajout de la promotion.",
    ErreurEnum::PROMO_ACTIVATION_FAILED->value => "Échec de l'activation de la promotion.",
    ErreurEnum::PROMO_AUCUNE_ACTIVE->value => "Aucune promotion active.",

    // Erreurs liées aux référentiels
    ErreurEnum::REF_NOM_REQUIRED->value => "Le nom du référentiel est requis.",
    ErreurEnum::REF_NOM_LENGTH->value => "Le nom doit contenir au moins 3 caractères.",
    ErreurEnum::REF_DESCRIPTION_REQUIRED->value => "La description est requise.",
    ErreurEnum::REF_CAPACITE_REQUIRED->value => "La capacité est requise.",
    ErreurEnum::REF_CAPACITE_INVALID->value => "La capacité doit être un nombre positif.",
    ErreurEnum::REF_SESSIONS_REQUIRED->value => "Le nombre de sessions est requis.",

    ErreurEnum::REF_SESSIONS_INVALID->value => "Le nombre de sessions doit être entre 1 et 4.",
    ErreurEnum::REF_PHOTO_REQUIRED->value => "La photo est requise.",
    ErreurEnum::REF_PHOTO_FORMAT_INVALID->value => "Format invalide (JPG/PNG uniquement).",
    ErreurEnum::REF_PHOTO_SIZE_INVALID->value => "La taille de l'image ne doit pas dépasser 2MB.",
    ErreurEnum::REF_PHOTO_UPLOAD_FAILED->value => "Erreur lors de l'upload de la photo.",
    ErreurEnum::REF_ID_REQUIRED->value => "ID de référentiel manquant.",
    ErreurEnum::REF_AUCUN_SELECTIONNE->value => "Aucun référentiel sélectionné.",
    ErreurEnum::REF_CREATION_FAILED->value => "Erreur lors de la création du référentiel.",
    ErreurEnum::REF_AFFECTATION_FAILED->value => "Certains référentiels n'ont pas pu être affectés.",
    ErreurEnum::REF_DESAFFECTATION_FAILED->value => "Erreur lors de la désaffectation."
];
