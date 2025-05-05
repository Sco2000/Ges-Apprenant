<div class="lcontainer">
    <header>
        <div class="title">
            <h1>Apprenants</h1>
            <span class="counter"><?= $total_apprenants ?> apprenants</span>
        </div>
    </header>
    
    <form method="GET" action="" class="search-filters">
        <input type="hidden" name="page" value="liste_apprenants">
        <?php if (isset($_GET['onglet'])): ?>
            <input type="hidden" name="onglet" value="<?= htmlspecialchars($_GET['onglet']) ?>">
        <?php endif; ?>
        
        <div class="search-bar">
            <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" fill="#777" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        
        <div class="filter-dropdown">
            <select name="referentiel" class="form-select">
                <option value="">Filtre par Réféentiel</option>
                <?php foreach ($referentiels as $referentiel): ?>
                    <option value="<?= $referentiel['id'] ?>" <?= isset($_GET['referentiel']) && $_GET['referentiel'] == $referentiel['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($referentiel['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-dropdown">
            <select name="statut" class="form-select">
                <option value="">Filtre par status</option>
                <option value="actif" <?= isset($_GET['statut']) && $_GET['statut'] === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="inactif" <?= isset($_GET['statut']) && $_GET['statut'] === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                <option value="remplacé" <?= isset($_GET['statut']) && $_GET['statut'] === 'remplacé' ? 'selected' : '' ?>>Remplacé</option>
            </select>
        </div>
        
        <div class="buttons">
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
                Rechercher
            </button>
            
            <?php if (!empty($_GET['search']) || !empty($_GET['referentiel']) || !empty($_GET['statut'])): ?>
                <a href="?page=liste_apprenants<?= isset($_GET['onglet']) ? '&onglet=' . htmlspecialchars($_GET['onglet']) : '' ?>" class="btn btn-secondary">Réinitialiser</a>
            <?php endif; ?>
        
                <div class="download-options">
                    <a href="#import-modal" class="download-option">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8.354 1.646a.5.5 0 0 0-.708 0l-3 3a.5.5 0 0 0 .708.708L7.5 3.207V11.5a.5.5 0 0 0 1 0V3.207l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3z"/>
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                        Importer un fichier Excel
                    </a>
                </div>
            </div>
            <a href="?page=liste_apprenants&action=ajouter<?= isset($_GET['onglet']) ? '&onglet=' . htmlspecialchars($_GET['onglet']) : '' ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Ajouter apprenant
            </a>
        </div>
    </form>
    
    <!-- Formulaire d'importation caché -->
    <form id="import-form" method="POST" action="?page=liste_apprenants&action=importer" enctype="multipart/form-data" style="display: none;">
        <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv">
    </form>
    
    <!-- Onglets navigation -->
    <ul class="tab-menu">
        <li class="<?= (!isset($_GET['onglet']) || $_GET['onglet'] === 'retenus') ? 'active' : '' ?>">
            <a href="?page=liste_apprenants&onglet=retenus<?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= isset($_GET['referentiel']) ? '&referentiel=' . urlencode($_GET['referentiel']) : '' ?><?= isset($_GET['statut']) ? '&statut=' . urlencode($_GET['statut']) : '' ?>">Liste des retenus</a>
        </li>
        <li class="<?= (isset($_GET['onglet']) && $_GET['onglet'] === 'attente') ? 'active' : '' ?>">
            <a href="?page=liste_apprenants&onglet=attente<?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= isset($_GET['referentiel']) ? '&referentiel=' . urlencode($_GET['referentiel']) : '' ?><?= isset($_GET['statut']) ? '&statut=' . urlencode($_GET['statut']) : '' ?>">Liste d'attente</a>
        </li>
    </ul>

    <?php if (!isset($_GET['onglet']) || $_GET['onglet'] === 'retenus'): ?>
        <!-- TABLE DES RETENUS -->
        <table>
            <thead>
                <tr>
                    <th class="avatar-container">Photo</th>
                    <th width="8%">Matricule</th>
                    <th width="15%">Nom Complet</th>
                    <th width="25%">Adresse</th>
                    <th width="8%">Téléphone</th>
                    <th width="12%">Référentiel</th>
                    <th width="7%">Statut</th>
                    <th width="5%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apprenants as $apprenant): ?>
                    <tr>
                        <td class="avatar-container">
                            <img src="<?= htmlspecialchars($apprenant['photo']) ?>" alt="Photo de <?= htmlspecialchars($apprenant['nom_complet']) ?>" class="avatar">
                        </td>
                        <td><?= htmlspecialchars($apprenant['matricule']) ?></td>
                        <td><?= htmlspecialchars($apprenant['nom_complet']) ?></td>
                        <td><?= htmlspecialchars($apprenant['adresse']) ?></td>
                        <td><?= htmlspecialchars($apprenant['telephone']) ?></td>
                        <td><span class="badge badge-<?= strtolower(str_replace(['/', ' '], '', $apprenant['referentiel_nom'])) ?>"><?= htmlspecialchars($apprenant['referentiel_nom']) ?></span></td>
                        <td><span class="badge badge-<?= $apprenant['statut'] === 'actif' ? 'success' : 'danger' ?>"><?= ucfirst(htmlspecialchars($apprenant['statut'])) ?></span></td>
                        <td class="actions">•••</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($apprenants)): ?>
            <div class="text-center mt-4">
                <p class="text-muted">Aucun apprenant ne correspond aux critères de recherche.</p>
            </div>
        <?php endif; ?>
    <?php elseif ($_GET['onglet'] === 'attente'): ?>
        <!-- TABLE LISTE D'ATTENTE -->
        <table>
            <thead>
                <tr>
                    <th class="avatar-container">Photo</th>
                    <th width="8%">Matricule</th>
                    <th width="15%">Nom Complet</th>
                    <th width="20%">Adresse</th>
                    <th width="8%">Téléphone</th>
                    <th width="25%">Raison d'attente</th>
                    <th width="7%">Statut</th>
                    <th width="5%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($liste_attente)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Aucun apprenant en liste d'attente pour le moment.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($liste_attente as $apprenant): ?>
                        <tr>
                            <td class="avatar-container">
                                <img src="<?= htmlspecialchars($apprenant['photo']) ?>" alt="Photo de <?= htmlspecialchars($apprenant['nom_complet']) ?>" class="avatar">
                            </td>
                            <td><?= htmlspecialchars($apprenant['matricule']) ?></td>
                            <td><?= htmlspecialchars($apprenant['nom_complet']) ?></td>
                            <td><?= htmlspecialchars($apprenant['adresse']) ?></td>
                            <td><?= htmlspecialchars($apprenant['telephone']) ?></td>
                            <td><?= htmlspecialchars($apprenant['raison_attente'] ?? 'Non spécifiée') ?></td>
                            <td><span class="badge badge-<?= $apprenant['statut'] === 'actif' ? 'success' : 'danger' ?>"><?= ucfirst(htmlspecialchars($apprenant['statut'])) ?></span></td>
                            <td class="actions">•••</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<style>
/* Style pour le modal */
.lcontainer{
    margin-top: 50px;
}

.tab-menu{
    display: flex;
    justify-content: space-evenly;
    align-items: center;
    height: 50px;
    background-color: #fff;
}

.download-options{
    width: 250px;
    background-color: black;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    
}

.modal-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
    z-index: 2000;
}

.modal-container:target {
    opacity: 1;
    visibility: visible;
}

.modal-content {
    background-color: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.2rem;
    color: #333;
}

.modal-close {
    font-size: 1.5rem;
    color: #999;
    text-decoration: none;
    line-height: 1;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-text {
    display: block;
    margin-top: 5px;
    color: #777;
    font-size: 0.85rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}
</style>
<!-- Modal d'importation -->
<div id="import-modal" class="modal-container">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Importer des apprenants</h3>
            <a href="#" class="modal-close">&times;</a>
        </div>
        <div class="modal-body">
            <p>Sélectionnez un fichier Excel (.xlsx, .xls) ou CSV contenant les données des apprenants.</p>
            <form method="POST" action="?page=liste_apprenants&action=importer" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="excel_file_import">Fichier Excel</label>
                    <input type="file" name="excel_file" id="excel_file_import" accept=".xlsx,.xls,.csv" required />
                    <small class="form-text">Format attendu: nom_complet, adresse, telephone, e-mail, référentiel, etc.</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8.354 1.646a.5.5 0 0 0-.708 0l-3 3a.5.5 0 0 0 .708.708L7.5 3.207V11.5a.5.5 0 0 0 1 0V3.207l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3z"/>
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                        Importer
                    </button>
                    <a href="#" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>