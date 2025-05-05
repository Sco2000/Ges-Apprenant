<?php
namespace App\Models;

enum JSONMETHODE: string
{
    case ARRAYTOJSON = 'array_to_json';
    case JSONTOARRAY = 'json_to_array';
}

enum AUTHMETHODE: string
{
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case REGISTER = 'register';
    case FORGOT_PASSWORD = 'forgot_password';
    case RESET_PASSWORD = "reset_password";
}

enum PROMOMETHODE: string {
    case ACTIVER_PROMO = 'activer_promo';
    case AJOUTER_PROMO = 'ajouter_promo'; // Nouvelle constante ajoutée
    case GET_ALL_WITH_ACTIVE_FIRST = 'getPromosWithActiveFirst';
}

enum REFMETHODE: string {
    case GET_ALL = 'get_all_referentiels';
    case GET_BY_ID = 'get_referentiel_by_id';
    case GET_BY_ACTIVE_PROMO = 'get_referentiels_by_active_promo';
    case AJOUTER = 'ajouter_referentiel';
    case SEARCH = 'search_referentiels';
    case PAGINATE = 'paginate_referentiels';
    case VALIDATE = 'validate_referentiel';
    case UPDATE_NBR_APPRENANT = 'update_nbr_apprenant';
    case GET_TOTAL_APPRENANTS = 'get_total_apprenants';
}

enum REFPROMETHODE: string {
    case GET_REFS_BY_PROMO = 'get_refs_by_promo';
    case AFFECTER_REF = 'affecter_ref';
    case GET_ACTIVE_PROMO = 'get_active_promo';
}
