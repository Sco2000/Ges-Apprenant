<div class="container">
    <header>
        <div class="title">
            <h1>Apprenants</h1>
            <span class="counter"><?= $total_apprenants ?> apprenants</span>
        </div>
    </header>
    
    <form method="GET" action="" class="search-filters">
        <input type="hidden" name="page" value="liste_apprenants">
        
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
                <a href="?page=liste_apprenants" class="btn btn-secondary">Réinitialiser</a>
            <?php endif; ?>
            
            <button type="button" class="btn btn-dark">
                <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                Télécharger la liste
            </button>
            <a href="?page=liste_apprenants&action=ajouter" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Ajouter apprenant
            </a>
        </div>
    </form>
    
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
</div>