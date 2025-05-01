<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage; // <-- ajoute cette ligne
require_once CheminPage::SESSION_SERVICE->value;
demarrer_session();
$errors = recuperer_messages('errors');
$old = recuperer_messages('old');
?>

<div id="popup">
    <div class="modal">
        <a href="index.php?page=liste_promo" class="close-btn">&times;</a>
        <h2>Créer une nouvelle promotion</h2>
        <p class="subtitle">Remplissez les informations ci-dessous pour créer une nouvelle promotion.</p>

        <form method="POST" action="index.php?page=ajouter_promo" enctype="multipart/form-data">
            <input type="hidden" name="nouvelle_promo" value="1">

            <label>
                Nom de la promotion
                <input
                    type="text"
                    name="nom_promo"
                    placeholder="Ex: Promotion 2025"
                    value="<?= htmlspecialchars($old['nom_promo'] ?? '') ?>"
                />
                <?php if (!empty($errors['nom_promo'])): ?>
                    <p class="error-message"><?= htmlspecialchars($errors['nom_promo']) ?></p>
                <?php endif; ?>
            </label>
           

            <div class="date-fields">
                    <label>Date de début
                        <input type="text" name="date_debut" placeholder="YYYY-MM-DD"
                               value="<?= htmlspecialchars($old['date_debut'] ?? '') ?>"
                               class="<?= !empty($errors['date_debut']) ? 'alert' : '' ?>">
                        <?php if (!empty($errors['date_debut'])): ?>
                            <p class="error-message"><?= htmlspecialchars($errors['date_debut']) ?></p>
                        <?php endif; ?>
                    </label>
               

                <label>
                    Date de fin
                    <?php if (!empty($errors['date_fin'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['date_fin']) ?></p>
                    <?php endif; ?>
                    <input
                        type="text"
                        name="date_fin"
                        placeholder="YYYY-MM-DD"
                        class="<?= !empty($errors['date_fin']) ? 'alert' : '' ?>"
                        value="<?= htmlspecialchars($old['date_fin'] ?? '') ?>"
                    />
                </label>
            </div>

            <label class="file-upload">
                Photo de la promotion
                <?php if (!empty($errors['photo'])): ?>
                    <p class="error-message"><?= htmlspecialchars($errors['photo']) ?></p>
                <?php endif; ?>
                <div class="drop-zone">
                    <span class="drop-text">Ajouter<br><small>ou glisser</small></span>
                    <input
                        type="file"
                        name="photo"
                        accept="image/png, image/jpeg"
                        class="<?= !empty($errors['photo']) ? 'alert' : '' ?>"
                    />
                </div>
                <small class="file-hint">Format JPG, PNG. Taille max 2MB</small>
            </label>

            <label>
                Rechercher un référentiel
                <?php if (!empty($errors['referenciels'])): ?>
                    <p class="error-message"><?= htmlspecialchars($errors['referenciels']) ?></p>
                <?php endif; ?>
                <div class="ref-search">
                    <input
                        type="text"
                        id="ref-search"
                        placeholder="Rechercher un référentiel..."
                        autocomplete="off"
                        class="<?= !empty($errors['referenciels']) ? 'alert' : '' ?>"
                    />
                    <div class="ref-results" style="display:none;">
                        <!-- Les résultats de recherche s'afficheront ici -->
                    </div>
                </div>
            </label>

            <div class="selected-refs">
                <!-- Les référentiels sélectionnés s'afficheront ici -->
                <input type="hidden" name="referenciel_ids[]" value="">
            </div>

            <div class="modal-actions">
                <a href="index.php?page=liste_promo" class="cancel-btn">Annuler</a>
                <button type="submit" class="btn-orange">Créer la promotion</button>
            </div>
        </form>
    </div>
</div>

<style>
#popup {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    z-index: 1000;
    width: 100%;
}

.modal {
    background: #fff;
    border-radius: 14px;
    padding: 32px 32px 24px 32px;
    width: 100%;
    max-width: 420px;
    position: relative;
    font-family: 'Inter', Arial, sans-serif;
}

.modal h2 {
    font-size: 1.35rem;
    font-weight: 700;
    margin-bottom: 6px;
    color: #18181b;
}

.modal .subtitle {
    font-size: 0.98rem;
    color: #666;
    margin-bottom: 22px;
}

label {
    display: block;
    margin-bottom: 18px;
    font-size: 0.98rem;
    color: #18181b;
    font-weight: 500;
}

input[type="text"], input[type="date"] {
    width: 100%;
    padding: 11px 12px;
    margin-top: 7px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: #fafafa;
    font-size: 1rem;
    transition: border 0.2s;
}

input[type="text"]:focus, input[type="date"]:focus {
    border-color: #ff7900;
    outline: none;
}

.date-fields {
    display: flex;
    gap: 14px;
    margin-bottom: 0;
}

.date-fields label {
    flex: 1;
    margin-bottom: 0;
}

.file-upload {
    margin-bottom: 18px;
}

.file-upload .drop-zone {
    border: 2px dashed #e5e7eb;
    border-radius: 8px;
    text-align: center;
    padding: 22px 0;
    background: #fafafa;
    margin-top: 7px;
    cursor: pointer;
    position: relative;
    transition: border-color 0.2s;
}

.drop-zone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.drop-zone .drop-text {
    color: #ff7900;
    font-weight: 600;
    font-size: 1.05rem;
    line-height: 1.2;
}

.drop-zone small {
    color: #999;
    font-size: 0.85rem;
}

.file-hint {
    display: block;
    font-size: 0.82rem;
    color: #888;
    margin-top: 6px;
}

.ref-search input[type="text"] {
    width: 100%;
    padding: 11px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: #fafafa;
    font-size: 1rem;
    margin-top: 7px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 28px;
}

.cancel-btn {
    background: transparent;
    border: none;
    color: #18181b;
    font-size: 1rem;
    padding: 10px 18px;
    border-radius: 7px;
    cursor: pointer;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.2s;
}

.cancel-btn:hover {
    background-color: #f3f4f6;
}

.btn-orange, .submit-btn {
    background-color: #ff7900;
    color: white;
    font-weight: 600;
    border: none;
    padding: 10px 22px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.2s;
}

.btn-orange:hover, .submit-btn:hover {
    background-color: #e56d00;
}

.close-btn {
    position: absolute;
    top: 18px;
    right: 22px;
    text-decoration: none;
    font-size: 1.3rem;
    color: #999;
    font-weight: bold;
    transition: color 0.2s;
}

.close-btn:hover {
    color: #ff7900;
}

.error-message {
    color: #e53935;
    font-size: 0.92rem;
    margin-top: 4px;
    margin-bottom: 0;
}

@media (max-width: 520px) {
    .modal {
        padding: 18px 6px 12px 6px;
        max-width: 98vw;
    }
    .date-fields {
        flex-direction: column;
        gap: 0;
    }
}
</style>