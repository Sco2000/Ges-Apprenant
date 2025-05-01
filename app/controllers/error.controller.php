<?php

require_once __DIR__ . '/../enums/chemin_page.php';
use App\Enums\CheminPage;

/**
 * Affiche une page d'erreur avec le message spécifié
 * 
 * @param string $message Le message d'erreur à afficher
 * @return void
 */
function showError(string $message): void {
    $error = [
        'message' => $message
    ];
    
    // Afficher la vue d'erreur sans layout
    render('error/error', ['error' => $error], layout: null);
}