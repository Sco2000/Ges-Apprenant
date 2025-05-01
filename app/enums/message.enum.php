<?php
namespace App\ENUM\MESSAGE;

/**
 * Énumération des messages de succès utilisés dans l'application
 * Les valeurs sont les messages directement affichables à l'utilisateur
 */
enum MSGENUM: string
{
    // Messages généraux
    case OPERATION_REUSSIE = 'Opération réalisée avec succès';
    
    // Messages liés à l'authentification
    case AUTH_LOGIN_REUSSI = 'Connexion réussie';
    case AUTH_LOGOUT_REUSSI = 'Déconnexion réussie';
    case AUTH_PASSWORD_RESET = 'Mot de passe réinitialisé avec succès';

    // Messages liés aux promotions
    case PROMO_AJOUT_REUSSI = 'La promotion a été ajoutée avec succès';
    case PROMO_ACTIVATION_REUSSIE = 'La promotion a été activée avec succès';
    case PROMO_DESACTIVATION_REUSSIE = 'La promotion a été désactivée avec succès';
    
    // Messages liés aux référentiels
    case REF_AJOUT_REUSSI = 'Le référentiel a été ajouté avec succès';
    case REF_AFFECTATION_REUSSIE = 'Les référentiels ont été affectés avec succès';
    case REF_DESAFFECTATION_REUSSIE = 'Référentiel désaffecté avec succès';
}
