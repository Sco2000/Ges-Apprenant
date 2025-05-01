<?php
require_once __DIR__ . '/../../../enums/message.enum.php';

use App\ENUM\MESSAGE\MSGENUM;

/**
 * Tableau de traduction des messages de succès en français
 * Les clés correspondent aux valeurs de l'énumération MSGENUM
 */
$message = [
    // Messages généraux
    MSGENUM::OPERATION_REUSSIE->value => "Opération réalisée avec succès",
    
    // Messages liés à l'authentification
    MSGENUM::AUTH_LOGIN_REUSSI->value => "Connexion réussie",
    MSGENUM::AUTH_LOGOUT_REUSSI->value => "Déconnexion réussie",
    MSGENUM::AUTH_PASSWORD_RESET->value => "Mot de passe réinitialisé avec succès",

    // Messages liés aux promotions
    MSGENUM::PROMO_AJOUT_REUSSI->value => "La promotion a été ajoutée avec succès",
    MSGENUM::PROMO_ACTIVATION_REUSSIE->value => "La promotion a été activée avec succès",
    MSGENUM::PROMO_DESACTIVATION_REUSSIE->value => "La promotion a été désactivée avec succès",
    
    // Messages liés aux référentiels
    MSGENUM::REF_AJOUT_REUSSI->value => "Le référentiel a été ajouté avec succès",
    MSGENUM::REF_AFFECTATION_REUSSIE->value => "Les référentiels ont été affectés avec succès",
    MSGENUM::REF_DESAFFECTATION_REUSSIE->value => "Référentiel désaffecté avec succès"
];
