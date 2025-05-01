<?php
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;
use App\Models\JSONMETHODE;

require_once CheminPage::SESSION_SERVICE->value;
require_once CheminPage::CONTROLLER->value;
require_once CheminPage::MODEL->value;
require_once CheminPage::MODEL_ENUM->value;

global $model_tab;
demarrer_session();

if (!session_existe('user')) {
    redirect_to_route('index.php', ['page' => 'login']);
    exit;
}

// On appelle directement la fonction render avec le contenu de la page
render(CheminPage::VIEW_PROMO->value);