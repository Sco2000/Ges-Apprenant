<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;

$url = "http://" . $_SERVER["HTTP_HOST"];
$css_promo = CheminPage::CSS_PROMO->value;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Promotions</title>
    <link rel="stylesheet" href="<?= $url."/assets/css/promo/promo.css "?>" />
</head>
<body>
<div class="promo-container">
    <header class="header">
        <h2>Promotion</h2>
        <p>Gérer les promotions de l'école</p>
    </header>

    <!-- Affichage des statistiques -->
    <div class="stats">
        <div class="stat orange">
            <div class="stat-content">
                <strong class="stat-value"><?= $stats['total_apprenants'] ?></strong>
                <span class="stat-label">Apprenants</span>
            </div>
            <div class="icon"><img src="/assets/images/icone1.png" alt=""></div>
        </div>
        <div class="stat orange">
            <div class="stat-content">
                <strong class="stat-value"><?= $stats['total_referentiels'] ?></strong>
                <span class="stat-label">Référentiels</span>
            </div>
            <div class="icon"><img src="/assets/images/ICONE2.png" alt=""></div>
        </div>
        <div class="stat orange">
            <div class="stat-content">
                <strong class="stat-value"><?= $stats['promotions_actives'] ?></strong>
                <span class="stat-label">Promotions actives</span>
            </div>
            <div class="icon"><img src="/assets/images/ICONE3.png" alt=""></div>
        </div>
        <div class="stat orange">
            <div class="stat-content">
                <strong class="stat-value"><?= $stats['total_promotions'] ?></strong>
                <span class="stat-label">Total promotions</span>
            </div>
            <div class="icon"><img src="/assets/images/ICONE4.png" alt=""></div>
        </div>
        <a href="index.php?page=add_promo" class="add-btn">+ Ajouter une promotion</a>
    </div>

    <div class="search-filter">
        <form method="GET" action="" style="display:inline;">
            <input 
                type="text" 
                name="search" 
                placeholder="Rechercher une promotion..." 
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
            />
            <input type="hidden" name="page" value="liste_promo">
            <input type="hidden" name="view" value="<?= $view ?>">
        </form>
        
        <!-- Modification du select uniquement -->
        <form method="GET" action="" style="display:inline;">
            <input type="hidden" name="page" value="liste_promo">
            <input type="hidden" name="view" value="<?= $view ?>">
            <?php if(isset($_GET['search'])): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <?php endif; ?>
            <select name="status">
                <option value="tous" <?= (!isset($_GET['status']) || $_GET['status'] === 'tous') ? 'selected' : '' ?>>Tous</option>
                <option value="active" <?= (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : '' ?>>Actives</option>
                <option value="inactive" <?= (isset($_GET['status']) && $_GET['status'] === 'inactive') ? 'selected' : '' ?>>Inactives</option>
            </select>
            <button type="submit" class="filter-button">Filtrer</button>
        </form>
        
        <div class="view-toggle">
            <a href="?page=liste_promo&view=grid<?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?><?= isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : '' ?>" class="<?= $view === 'grid' ? 'active' : '' ?>">Grille</a>
            <a href="?page=liste_promo&view=list<?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?><?= isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : '' ?>" class="<?= $view === 'list' ? 'active' : '' ?>">Liste</a>
        </div>
    </div>

    <?php if ($view === 'grid'): ?>
        <div class="card-grid">
            <?php foreach ($paginatedPromos as $promo): ?>
                <div class="promo-card">
                    <div class="toggle-container">
                        <form method="POST" action="index.php?page=toggle_promo_status" class="toggle-form">
                            <input type="hidden" name="id" value="<?= $promo['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $promo['statut'] ?>">
                            <input type="hidden" name="view" value="<?= $view ?>">
                            <button type="submit" class="toggle-label <?= $promo['statut'] === 'Active' ? 'active' : '' ?>">
                                <div class="status-pill">
                                    <?= $promo['statut'] === 'Active' ? 'Active' : 'Inactive' ?>
                                </div>
                                <div class="power-button">
                                    <span class="power-dot"></span>
                                </div>
                            </button>
                        </form>
                    </div>

                    <div class="promo-body">
                        <div class="promo-image">
                            <img src="<?= $promo['photo'] ?>" alt="<?= $promo['nom'] ?>">
                        </div>
                        <div class="promo-details">
                            <h3><?= $promo['nom'] ?></h3>
                            <p class="promo-date"><?= date("d/m/Y", strtotime($promo['dateDebut'])) ?> - <?= date("d/m/Y", strtotime($promo['dateFin'])) ?></p>
                        </div>
                    </div>

                    <div class="student">
                        <div class="promo-students">
                            <p class="p"><?= $promo['nbrApprenant'] ?> apprenant<?= $promo['nbrApprenant'] > 1 ? "s" : "" ?></p>
                        </div>
                    </div>

                    <div class="promo-footer">
                        <button class="details-btn">Voir détails ></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Promotion</th>
                    <th>Date de début</th>
                    <th>Date de fin</th>
                    <th>Référentiel</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($paginatedPromos as $promo): ?>
                <tr>
                    <td class='photo-cell'><img src='<?= $promo["photo"] ?>' alt='photo' width='50'></td>
                    <td class='promo-cell'><?= $promo["nom"] ?></td>
                    <td class='date-cell'><?= $promo["dateDebut"] ?></td>
                    <td class='date-cell'><?= $promo["dateFin"] ?></td>
                    <td>
                        <div class='tag'>
                            <?php if (!empty($promo['referentiels'])): ?>
                                <?php foreach ($promo['referentiels'] as $ref): ?>
                                    <span class='tag dev-web'><?= htmlspecialchars($ref['nom']) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class='tag inactive'>Aucun référentiel</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <form method="POST" action="index.php?page=toggle_promo_status" class="toggle-form">
                            <input type="hidden" name="id" value="<?= $promo['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $promo['statut'] ?>">
                            <input type="hidden" name="view" value="<?= $view ?>">
                            <button type="submit" class="toggle-label <?= $promo['statut'] === 'Active' ? 'active' : '' ?>">
                                <div class="status-pill">
                                    <?= $promo['statut'] === 'Active' ? 'Active' : 'Inactive' ?>
                                </div>
                                <div class="power-button">
                                    <span class="power-dot"></span>
                                </div>
                            </button>
                        </form>
                    </td>
                    <td class='action-cell'><span class='dots'>•••</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Pagination commune aux deux vues -->
    <div class="pagination">
        <div class="page-size">
            <span>Afficher</span>
            <form method="GET" style="display: inline;">
                <input type="hidden" name="page" value="liste_promo">
                <select name="limit">
                    <option value="8" <?= $pagination['perPage'] == 8 ? 'selected' : '' ?>>8</option>
                    <option value="16" <?= $pagination['perPage'] == 16 ? 'selected' : '' ?>>16</option>
                    <option value="24" <?= $pagination['perPage'] == 24 ? 'selected' : '' ?>>24</option>
                </select>
                <?php if(isset($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <?php endif; ?>
                <input type="hidden" name="view" value="<?= $view ?>">
                <button type="submit" class="apply-limit">Appliquer</button>
            </form>
            <span>éléments</span>
        </div>

        <div class="page-info">
            Affichage de <?= $pagination['start'] + 1 ?> 
            à <?= min($pagination['start'] + $pagination['perPage'], $pagination['total']) ?> 
            sur <?= $pagination['total'] ?> éléments
        </div>

        <div class="page-controls">
            <?php if ($pagination['currentPage'] > 1): ?>
                <a href="?page=liste_promo&p=<?= $pagination['currentPage'] - 1 ?>&limit=<?= $pagination['perPage'] ?>&view=<?= $view ?><?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?>">
                    <button><i class="fa fa-angle-left"></i></button>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                <a href="?page=liste_promo&p=<?= $i ?>&limit=<?= $pagination['perPage'] ?>&view=<?= $view ?><?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?>">
                    <button class="<?= $i == $pagination['currentPage'] ? 'active' : '' ?>"><?= $i ?></button>
                </a>
            <?php endfor; ?>

            <?php if ($pagination['currentPage'] < $pagination['pages']): ?>
                <a href="?page=liste_promo&p=<?= $pagination['currentPage'] + 1 ?>&limit=<?= $pagination['perPage'] ?>&view=<?= $view ?><?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?>">
                    <button><i class="fa fa-angle-right"></i></button>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>