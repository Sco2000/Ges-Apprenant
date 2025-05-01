<?php
namespace App\ENUM\ERREUR;

/**
 * Énumération des codes d'erreur utilisés dans l'application
 * Les valeurs sont des identifiants uniques qui seront utilisés pour récupérer
 * les messages d'erreur correspondants dans les fichiers de traduction
 */
enum ErreurEnum: string
{
    // Erreurs liées à l'authentification
    case LOGIN_REQUIRED = 'auth.login.required';
    case LOGIN_EMAIL = 'auth.login.invalid_email';
    case PASSWORD_REQUIRED = 'auth.password.required';
    case PASSWORD_INVALID = 'auth.password.invalid';
    case LOGIN_INCORRECT = 'auth.login.incorrect';
    
    // Erreurs liées aux promotions
    case PROMO_ID_REQUIRED = 'promo.id.required';
    case PROMO_NAME_REQUIRED = 'promo.name.required';
    case PROMO_DATE_REQUIRED = 'promo.date.required';
    case PROMO_DATE_INVALID_RANGE = 'promo.date.invalid_range';
    case PROMO_DATE_INVALID_FORMAT = 'promo.date.invalid_format';
    case PROMO_ADD_FAILED = 'promo.add.failed';
    case PROMO_ACTIVATION_FAILED = 'promo.activation.failed';
    case PROMO_AUCUNE_ACTIVE = 'promo.aucune_active';

    // Erreurs liées aux référentiels
    case REF_NOM_REQUIRED = 'ref.nom.required';
    case REF_NOM_LENGTH = 'ref.nom.length';
    case REF_DESCRIPTION_REQUIRED = 'ref.description.required';
    case REF_CAPACITE_REQUIRED = 'ref.capacite.required';
    case REF_CAPACITE_INVALID = 'ref.capacite.invalid';
    case REF_SESSIONS_REQUIRED = 'ref.sessions.required';
    case REF_SESSIONS_INVALID = 'ref.sessions.invalid';
    case REF_PHOTO_REQUIRED = 'ref.photo.required';
    case REF_PHOTO_FORMAT_INVALID = 'ref.photo.format_invalid';
    case REF_PHOTO_SIZE_INVALID = 'ref.photo.size_invalid';
    case REF_PHOTO_UPLOAD_FAILED = 'ref.photo.upload_failed';
    case REF_ID_REQUIRED = 'ref.id.required';
    case REF_AUCUN_SELECTIONNE = 'ref.aucun_selectionne';
    case REF_CREATION_FAILED = 'ref.creation.failed';
    case REF_AFFECTATION_FAILED = 'ref.affectation.failed';
    case REF_DESAFFECTATION_FAILED = 'ref.desaffectation.failed';
    
}
