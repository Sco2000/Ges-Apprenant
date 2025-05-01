<!DOCTYPE html>
<html lang="fr">
<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_ref = CheminPage::CSS_REFERENCIEL->value;
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Référentiels de la promotion</title>
    <link rel="stylesheet" href="/assets/css/referenciel/referenciel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ref-container {
    position: absolute;
    width: 100%;
}

header {
    margin-bottom: 2rem;
}

header h1 {
    color: #333;
    font-size: 1.8rem;
    margin-bottom: 5rem;
}

header p {
    color: #666;
    font-size: 1rem;
}

.search-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.search-form {
    flex: 1;
    margin-right: 1rem;
}

.search-input {
    position: relative;
    max-width: 400px;
}

.search-input input {
    width: 100%;
    padding: 0.8rem 1rem 0.8rem 2.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.search-input i.fa-search {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.actions {
    display: flex;
    gap: 1rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1.2rem;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.9rem;
}

.btn-orange {
    background-color: #FF6B00;
    color: white;
}

.btn-green {
    background-color: #00BA88;
    color: white;
}

.ref-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.ref-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ref-image {
    height: 160px;
    overflow: hidden;
}

.ref-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ref-content {
    padding: 1.5rem;
}

.ref-content h3 {
    color: #333;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.modules {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.description {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.student-count {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.9rem;
}

.no-results {
    grid-column: 1/-1;
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 8px;
    color: #666;
}

.no-results i {
    font-size: 2rem;
    color: #999;
    margin-bottom: 1rem;
}

/* Modal styles */
/* Styles de base pour le modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal:target {
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Styles pour le contenu du modal */
.modal-content {
    background: white;
    padding: 20px;
    border-radius: 5px;
    width: 80%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.close-btn {
    font-size: 24px;
    font-weight: bold;
    text-decoration: none;
    color: #333;
}

/* Styles pour le formulaire intégré */
.form-container {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.form-container h2 {
    color: var(--primary-color, #00857c);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.add-ref-form .form-group {
    margin-bottom: 1.5rem;
}

.add-ref-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.search-ref {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.ref-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 1rem 0;
    max-height: 200px;
    overflow-y: auto;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.tag {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    background: #E3F2FD;
    color: #1565C0;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.tag:hover {
    background: #1565C0;
    color: white;
}

.tag input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.tag input[type="radio"]:checked + span {
    font-weight: bold;
}

.tag:has(input[type="radio"]:checked) {
    background: #1565C0;
    color: white;
    box-shadow: 0 2px 4px rgba(21, 101, 192, 0.2);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.8rem;
    margin-top: 1.5rem;
}

.btn-cancel {
    padding: 0.8rem 1.2rem;
    background: #f8f9fa;
    border: none;
    border-radius: 4px;
    color: #666;
    text-decoration: none;
}

.btn-submit {
    padding: 0.8rem 1.2rem;
    background: var(--primary-color, #00857c);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .form-container {
        padding: 1.5rem;
    }
}

.add-ref-container {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}

.form-container {
    flex: 1;
    min-width: 0;
}

.selected-refs-container {
    flex: 1;
    min-width: 0;
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.selected-refs-container h2 {
    color: var(--primary-color, #00857c);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.selected-refs-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.no-refs-message {
    text-align: center;
    padding: 2rem;
    color: #666;
}

.no-refs-message i {
    font-size: 2rem;
    color: #999;
    margin-bottom: 1rem;
}

.no-refs-message .hint {
    font-size: 0.9rem;
    color: #999;
    margin-top: 0.5rem;
}

.selected-ref-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    transition: transform 0.2s;
}

.selected-ref-card:hover {
    transform: translateX(4px);
}

.selected-ref-card img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
}

.ref-details {
    flex: 1;
    min-width: 0;
}

.ref-details h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: #333;
}

.ref-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.ref-stats span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ref-stats i {
    font-size: 0.9rem;
    color: var(--primary-color, #00857c);
}
     /* Mise à jour des styles pour la nouvelle disposition */
     .add-ref-container {
                display: flex;
                gap: 2rem;
                align-items: flex-start;
                padding: 0 1rem;
            }

            .available-refs-container,
            .selected-refs-container {
                flex: 1;
                min-width: 0;
                background: white;
                border-radius: 8px;
                padding: 2rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .available-refs-container h2,
            .selected-refs-container h2 {
                color: var(--primary-color, #00857c);
                margin-bottom: 1.5rem;
                font-size: 1.5rem;
            }

            .available-refs-list,
            .selected-refs-list {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                max-height: 600px;
                overflow-y: auto;
                padding: 0.5rem;
                margin: -0.5rem;
            }

            .available-ref-card,
            .selected-ref-card {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1rem;
                background: #f8f9fa;
                border-radius: 8px;
                transition: all 0.2s ease;
                border: 1px solid transparent;
            }

            .available-ref-card {
                border: none;
                width: 100%;
                cursor: pointer;
            }

            .available-ref-card:hover,
            .selected-ref-card:hover {
                transform: translateX(4px);
                background: #f0f0f0;
                border-color: #e0e0e0;
            }

            .available-ref-card img,
            .selected-ref-card img {
                width: 60px;
                height: 60px;
                border-radius: 8px;
                object-fit: cover;
            }

            .ref-details {
                flex: 1;
                min-width: 0;
            }

            .ref-details h3 {
                margin: 0 0 0.5rem 0;
                font-size: 1rem;
                color: #333;
            }

            .ref-stats {
                display: flex;
                gap: 1rem;
                font-size: 0.9rem;
                color: #666;
            }

            .ref-stats span {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .ref-stats i {
                font-size: 0.9rem;
                color: var(--primary-color, #00857c);
            }

            .add-ref-btn,
            .remove-ref-btn {
                background: var(--primary-color, #00857c);
                color: white;
                border: none;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .remove-ref-btn {
                background: #dc3545;
            }

            .add-ref-btn:hover {
                background: #006b63;
                transform: scale(1.1);
            }

            .remove-ref-btn:hover {
                background: #bd2130;
                transform: scale(1.1);
            }

            .selection-actions {
                display: flex;
                justify-content: flex-end;
                gap: 1rem;
                margin-top: 2rem;
                padding-top: 1rem;
                border-top: 1px solid #e5e7eb;
            }

            .btn-cancel,
            .btn-validate {
                padding: 0.8rem 1.5rem;
                border-radius: 6px;
                font-weight: 500;
                text-decoration: none;
                transition: all 0.2s ease;
            }

            .btn-cancel {
                background: #f8f9fa;
                color: #666;
                border: 1px solid #e5e7eb;
            }

            .btn-validate {
                background: var(--primary-color, #00857c);
                color: white;
                border: 1px solid transparent;
            }

            .btn-cancel:hover {
                background: #e9ecef;
                border-color: #dde0e3;
            }

            .btn-validate:hover {
                background: #006b63;
            }

            @media (max-width: 1024px) {
                .add-ref-container {
                    flex-direction: column;
                    padding: 0;
                }

                .available-refs-container,
                .selected-refs-container {
                    width: 100%;
                }
            }

@media (max-width: 1024px) {
    .add-ref-container {
        flex-direction: column;
    }

    .form-container,
    .selected-refs-container {
        width: 100%;
    }
}
    </style>
</head>
<body>
    <div class="ref-container">
        <header>
            <h1>Référentiels de la promotion en cours</h1>
            <p>Gérez les référentiels de votre promotion</p>
        </header>

        <!-- Barre de recherche et actions -->
        <div class="search-bar">
            <form method="GET" class="search-form">
                <input type="hidden" name="page" value="referenciel">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Rechercher un référentiel..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                        autocomplete="off"
                    >
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="?page=referenciel" class="clear-search">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            <div class="actions">
                <a href="?page=all_referenciel" class="btn btn-orange">
                    <i class="fas fa-list"></i> Tous les référentiels
                </a>
                <a href="?page=referenciel&action=add" class="btn btn-green">
                    <i class="fas fa-plus"></i> Ajouter à la promotion
                </a>
            </div>
        </div>

        <!-- Après la div search-bar -->

        <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
            <div class="add-ref-container">
                <!-- Section des référentiels disponibles -->
                <div class="available-refs-container">
                    <h2>Référentiels disponibles</h2>
                    <form method="GET" class="search-input">
                        <input type="hidden" name="page" value="referenciel">
                        <input type="hidden" name="action" value="add">
                        <i class="fas fa-search"></i>
                        <input 
                            type="text" 
                            name="search_available" 
                            placeholder="Rechercher un référentiel..."
                            value="<?= htmlspecialchars($_GET['search_available'] ?? '') ?>"
                        >
                    </form>

                    <div class="available-refs-list">
                        <?php
                        $assigned_ids = array_map(function($ref) { 
                            return $ref['id']; 
                        }, $referentiels);
                        
                        $available_refs = array_filter($all_referentiels, function($ref) use ($assigned_ids) {
                            return !in_array($ref['id'], $assigned_ids);
                        });

                        if (!empty($_GET['search_available'])) {
                            $search = strtolower($_GET['search_available']);
                            $available_refs = array_filter($available_refs, function($ref) use ($search) {
                                return stripos($ref['nom'], $search) !== false;
                            });
                        }
                        ?>

                        <?php if (empty($available_refs)): ?>
                            <div class="no-refs-message">
                                <i class="fas fa-info-circle"></i>
                                <p>Aucun référentiel disponible</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($available_refs as $ref): ?>
                                <form method="POST" action="?page=affecter_referentiel" class="available-ref-card">
                                    <input type="hidden" name="ref_ids[]" value="<?= $ref['id'] ?>">
                                    <img src="<?= htmlspecialchars($ref['photo']) ?>" alt="<?= htmlspecialchars($ref['nom']) ?>">
                                    <div class="ref-details">
                                        <h3><?= htmlspecialchars($ref['nom']) ?></h3>
                                        <p class="ref-stats">
                                            <span><i class="fas fa-users"></i> <?= $ref['apprenants'] ?> apprenants</span>
                                            <span><i class="fas fa-book"></i> <?= $ref['modules'] ?> modules</span>
                                        </p>
                                    </div>
                                    <button type="submit" class="add-ref-btn" title="Ajouter à la promotion">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section des référentiels de la promotion -->
                <div class="selected-refs-container">
                    <h2>Référentiels de la promotion</h2>
                    <div class="selected-refs-list">
                        <?php if (empty($referentiels)): ?>
                            <div class="no-refs-message">
                                <i class="fas fa-info-circle"></i>
                                <p>Aucun référentiel associé</p>
                                <p class="hint">Sélectionnez un référentiel à gauche pour l'ajouter</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($referentiels as $ref): ?>
                                <div class="selected-ref-card">
                                    <img src="<?= htmlspecialchars($ref['photo']) ?>" alt="<?= htmlspecialchars($ref['nom']) ?>">
                                    <div class="ref-details">
                                        <h3><?= htmlspecialchars($ref['nom']) ?></h3>
                                        <p class="ref-stats">
                                            <span><i class="fas fa-users"></i> <?= $ref['apprenants'] ?> apprenants</span>
                                            <span><i class="fas fa-book"></i> <?= $ref['modules'] ?> modules</span>
                                        </p>
                                    </div>
                                    <form method="POST" action="?page=desaffecter_referentiel" style="margin: 0;">
                                        <input type="hidden" name="ref_id" value="<?= $ref['id'] ?>">
                                        <button type="submit" class="remove-ref-btn" title="Retirer de la promotion">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="selection-actions">
                        <a href="?page=referenciel" class="btn-cancel">Annuler</a>
                        <a href="?page=referenciel" class="btn-validate">Terminer</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Grille des référentiels -->
            <div class="ref-grid">
                <?php if (empty($referentiels)): ?>
                    <div class="no-results">
                        <i class="fas fa-info-circle"></i>
                        <p><?= $message ?? 'Aucun référentiel trouvé' ?></p>
                        <?php if (!isset($message)): ?>
                            <p>Activez une promotion pour voir ses référentiels associés</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php 
                    // Ensure $referentiels is an array before foreach
                    $referentiels = is_array($referentiels) ? $referentiels : [];
                    foreach ($referentiels as $ref): 
                    ?>
                        <?php 
                        // Verify that $ref is an array and contains necessary keys
                        if (!is_array($ref)) continue;
                        $ref = array_merge([
                            'nom' => 'Sans nom',
                            'description' => 'Aucune description',
                            'photo' => '/assets/images/referenciel/default.jpg',
                            'modules' => 0,
                            'apprenants' => 0
                        ], $ref);
                        ?>
                        <div class="ref-card">
                            <div class="ref-image">
                                <img src="<?= htmlspecialchars($ref['photo']) ?>" 
                                     alt="<?= htmlspecialchars($ref['nom']) ?>">
                            </div>
                            <div class="ref-content">
                                <h3><?= htmlspecialchars($ref['nom']) ?></h3>
                                <p class="modules"><?= htmlspecialchars($ref['modules']) ?> modules</p>
                                <p class="description"><?= htmlspecialchars($ref['description']) ?></p>
                                <div class="student-count">
                                    <i class="fas fa-users"></i>
                                    <span><?= htmlspecialchars($ref['apprenants']) ?> apprenants</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

