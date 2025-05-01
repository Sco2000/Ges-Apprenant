<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;

$url = "http://" . $_SERVER["HTTP_HOST"];
?>

<div class="search-results-container">
    <h2>Résultats de recherche pour "<?= htmlspecialchars($_GET['global_search'] ?? '') ?>"</h2>
    
    <?php if (empty($resultats['promotions']) && empty($resultats['referenciel'])): ?>
        <div class="no-results">
            <p>Aucun résultat trouvé. Veuillez essayer avec d'autres termes de recherche.</p>
        </div>
    <?php else: ?>
        <!-- Affichage des promotions trouvées -->
        <?php if (!empty($resultats['promotions'])): ?>
            <div class="search-section">
                <h3>Promotions (<?= count($resultats['promotions']) ?>)</h3>
                <div class="search-grid">
                    <?php foreach ($resultats['promotions'] as $promo): ?>
                        <div class="search-card promo-card">
                            <div class="search-card-image">
                                <img src="<?= htmlspecialchars($promo['photo'] ?? '/assets/images/promo/default.jpg') ?>" alt="<?= htmlspecialchars($promo['nom']) ?>">
                            </div>
                            <div class="search-card-content">
                                <h4><?= htmlspecialchars($promo['nom']) ?></h4>
                                <p class="search-card-date">
                                    <?= date("d/m/Y", strtotime($promo['dateDebut'])) ?> - 
                                    <?= date("d/m/Y", strtotime($promo['dateFin'])) ?>
                                </p>
                                <p class="search-card-status <?= $promo['statut'] === 'Active' ? 'active' : 'inactive' ?>">
                                    <?= $promo['statut'] ?>
                                </p>
                                <a href="index.php?page=liste_promo&view=grid&search=<?= urlencode($promo['nom']) ?>" class="search-card-link">
                                    Voir les détails
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Affichage des référentiels trouvés -->
        <?php if (!empty($resultats['referenciel'])): ?>
            <div class="search-section">
                <h3>Référentiels (<?= count($resultats['referenciel']) ?>)</h3>
                <div class="search-grid">
                    <?php foreach ($resultats['referenciel'] as $ref): ?>
                        <div class="search-card ref-card">
                            <div class="search-card-image">
                                <img src="<?= htmlspecialchars($ref['photo'] ?? '/assets/images/referenciel/default.jpg') ?>" alt="<?= htmlspecialchars($ref['nom']) ?>">
                            </div>
                            <div class="search-card-content">
                                <h4><?= htmlspecialchars($ref['nom']) ?></h4>
                                <p class="search-card-stats">
                                    <span><i class="fas fa-users"></i> <?= $ref['apprenants'] ?? 0 ?> apprenants</span>
                                    <span><i class="fas fa-book"></i> <?= $ref['modules'] ?? 0 ?> modules</span>
                                </p>
                                <a href="index.php?page=all_referenciel&search=<?= urlencode($ref['nom']) ?>" class="search-card-link">
                                    Voir les détails
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.search-results-container {
    padding: 20px;
    max-width: 100%;
    margin: 0 auto;
    margin-top: 20px;
}

.search-results-container h2 {
    margin-bottom: 20px;
    color: #333;
    font-size: 1.8rem;
}

.no-results {
    background-color: #f8f9fa;
    padding: 30px;
    text-align: center;
    border-radius: 8px;
    margin: 20px 0;
}

.search-section {
    margin-bottom: 30px;
}

.search-section h3 {
    margin-bottom: 15px;
    color: #ff7900;
    font-size: 1.4rem;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.search-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.search-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: white;
}

.search-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
}

.search-card-image {
    height: 160px;
    overflow: hidden;
}

.search-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.search-card-content {
    padding: 15px;
}

.search-card-content h4 {
    margin: 0 0 10px;
    font-size: 1.2rem;
    color: #333;
}

.search-card-date, .search-card-stats {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.search-card-stats span {
    margin-right: 10px;
}

.search-card-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    margin-bottom: 10px;
}

.search-card-status.active {
    background-color: #e6f7e6;
    color: #28a745;
}

.search-card-status.inactive {
    background-color: #f8f9fa;
    color: #6c757d;
}

.search-card-link {
    display: inline-block;
    color: #ff7900;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
}

.search-card-link:hover {
    text-decoration: underline;
}

.search-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
}
</style>