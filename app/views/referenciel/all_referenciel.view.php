<!DOCTYPE html>
<html lang="fr">
<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_ref = '/assets/css/referenciel/all_referenciel.css';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Référentiels</title>
    <link rel="stylesheet" href="<?= $url . $css_ref ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ref-container{
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="ref-container">
        <div class="ref-header">
            <a href="?page=referenciel" class="back-link">
                <i class="fas fa-arrow-left"></i> 
                Retour aux référentiels actifs
            </a>
            <h1>Tous les Référentiels</h1>
            <p class="subtitle">Liste complète des référentiels de formation</p>
        </div>

        <div class="search-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <form method="GET">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Rechercher un référentiel..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                    >
                    <input type="hidden" name="page" value="all_referenciel">
                    <!-- Préserver la session en ajoutant l'ID de session si nécessaire -->
                    <?php if (session_id()): ?>
                        <input type="hidden" name="PHPSESSID" value="<?= session_id() ?>">
                    <?php endif; ?>
                </form>
            </div>
            <a class="create-btn" href="?page=all_referenciel&action=create">
                <i class="fas fa-plus"></i>
                Créer un référentiel
            </a>
        </div>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'create'): ?>
            <!-- Formulaire de création -->
            <div class="form-container">
                <h2>Créer un nouveau référentiel</h2>
                
                <form method="POST" action="?page=creer_referentiel" enctype="multipart/form-data">
                    <div class="form-group">
                        <div class="upload-wrapper">
                            <label for="photo" class="upload-label">
                                <div class="upload-placeholder" id="preview">
                                    <img src="/assets/images/upload-placeholder.svg" alt="Upload" class="upload-icon">
                                    <span class="upload-text">Cliquez pour ajouter une photo</span>
                                </div>
                            </label>
                            <input 
                                type="file" 
                                id="photo" 
                                name="photo" 
                                accept="image/jpeg,image/png" 
                                class="file-input"
                            >
                        </div>
                        <?php if (isset($_SESSION['errors']['photo'])): ?>
                            <span class="error"><?= $_SESSION['errors']['photo'] ?></span>
                        <?php endif; ?>
                        <small>Format accepté : JPG, PNG - Max 2MB</small>
                    </div>

                    <div class="form-group">
                        <label for="nom">Nom*</label>
                        <input 
                            type="text" 
                            id="nom" 
                            name="nom" 
                            value="<?= htmlspecialchars($_SESSION['old']['nom'] ?? '') ?>"
                            placeholder="Ex: Référent Digital"
                        >
                        <?php if (isset($_SESSION['errors']['nom'])): ?>
                            <span class="error"><?= $_SESSION['errors']['nom'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="description">Description*</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="4"
                            placeholder="Description du référentiel..."
                        ><?= htmlspecialchars($_SESSION['old']['description'] ?? '') ?></textarea>
                        <?php if (isset($_SESSION['errors']['description'])): ?>
                            <span class="error"><?= $_SESSION['errors']['description'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="capacite">Capacité*</label>
                            <input 
                                type="number" 
                                id="capacite" 
                                name="capacite" 
                                min="1"
                                value="<?= htmlspecialchars($_SESSION['old']['capacite'] ?? '30') ?>"
                                placeholder="Ex: 30"
                            >
                            <?php if (isset($_SESSION['errors']['capacite'])): ?>
                                <span class="error"><?= $_SESSION['errors']['capacite'] ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="nb_sessions">Nombre de sessions*</label>
                            <select id="nb_sessions" name="nb_sessions">
                                <?php 
                                $selected = $_SESSION['old']['nb_sessions'] ?? 1;
                                for($i = 1; $i <= 4; $i++): 
                                ?>
                                    <option value="<?= $i ?>" <?= $selected == $i ? 'selected' : '' ?>>
                                        <?= $i ?> session<?= $i > 1 ? 's' : '' ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <?php if (isset($_SESSION['errors']['nb_sessions'])): ?>
                                <span class="error"><?= $_SESSION['errors']['nb_sessions'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="?page=all_referenciel" class="btn-cancel">Annuler</a>
                        <button type="submit" class="btn-submit">Créer le référentiel</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Grille des référentiels -->
            <div class="ref-grid">
                <?php if (empty($referentiels)): ?>
                    <div class="no-results">
                        <i class="fas fa-info-circle"></i>
                        <p>Aucun référentiel trouvé</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($referentiels as $ref): ?>
                    <div class="ref-card">
                        <div class="ref-image">
                            <img src="<?= htmlspecialchars($ref['photo']) ?>" alt="<?= htmlspecialchars($ref['nom']) ?>">
                        </div>
                        <div class="ref-content">
                            <h3><?= htmlspecialchars($ref['nom']) ?></h3>
                            <p class="description"><?= htmlspecialchars($ref['description'] ?? 'Description du référentiel...') ?></p>
                            <div class="capacity">
                                <span>Capacité: <?= $ref['capacite'] ?> places</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <div class="pagination" style="border: none;">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=all_referenciel&p=<?= $pagination['current_page'] - 1 ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" 
                           class="page-link" aria-label="Page précédente">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <a href="?page=all_referenciel&p=<?= $i ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" 
                           class="page-link <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=all_referenciel&p=<?= $pagination['current_page'] + 1 ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" 
                           class="page-link" aria-label="Page suivante">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>