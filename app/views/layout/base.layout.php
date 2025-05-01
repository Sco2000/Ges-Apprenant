<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
require_once CheminPage::SESSION_SERVICE->value;
demarrer_session();
if (empty($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}
// Empêcher le cache navigateur
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
$url="http://".$_SERVER["HTTP_HOST"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - Promotions</title>
    <link rel="stylesheet" href="<?= $url."/assets/css/layout/layout.css" ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <?php if ($_GET['page'] === 'liste_apprenants'): ?>
        <link rel="stylesheet" href="<?= $url."/assets/css/apprenant/liste_apprenant.css" ?>" />
    <?php endif; ?>
    <style>
        .content{
            height: 700px;
        }
    </style>
</head>

<body>
<input type="checkbox" id="menu-toggle" hidden>
<label for="menu-toggle" class="toggle-btn">
    <i class="fas fa-bars"></i>
</label>

<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="orange">
            <div class="logo">
                <div class="img">


                </div>
            </div>
            <div class="p25">
                <?= !empty($promo_active_nom) ? htmlspecialchars($promo_active_nom) : 'Aucune promotion active' ?>
            </div>
        </div>
        <nav class="menu">
            <a href="#"><i class='bx bx-home-alt' style='color:#969393'></i> Tableau de bord<label for="menu-toggle" class="menu-closer"></label></a>
            <a href="index.php?page=liste_promo&view=grid"><i class="fa-regular fa-folder" style="color: #707275;"></i> Promotions<label for="menu-toggle" class="menu-closer"></label></a>
            <a href="index.php?page=referenciel"><i class="fas fa-book"></i> Référentiels<label for="menu-toggle" class="menu-closer"></label></a>
            <a href="index.php?page=liste_apprenants"><i class="fas fa-users"></i> Apprenants<label for="menu-toggle" class="menu-closer"></label></a>
            <a href="#"><i class='bx bx-file'></i> Gestion des présences<label for="menu-toggle" class="menu-closer"></label></a>
            <a href="#"><i class="fas fa-laptop"></i> Kits & Laptops<label for="menu-toggle" class="menu-closer"></label></a>
            <a href="#"><i class='bx bx-signal-3'></i> Rapports & Stats<label for="menu-toggle" class="menu-closer"></label></a>
        </nav>
        <div class="foot">
            <div class="log-out">
            <?php  ?>
                <a href="?page=logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Deconnexion</a>
                
            </div>
        </div>

    </aside>

    <!-- Main -->
    <main class="main">

        <header class="topbar">

            <div class="search-box">
                <form method="GET" action="index.php">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input 
                            type="text" 
                            name="global_search" 
                            placeholder="Rechercher une promotion ou un référentiel..."
                            value="<?= htmlspecialchars($_GET['global_search'] ?? '') ?>"
                        />
                        <input type="hidden" name="page" value="search_results">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="user-info">
                <div class="notif"><i class="fa-regular fa-bell"></i></div>
                <div class="avatar"><i class="fas fa-user-circle"></i></div>
                <?php
                // Démarrer la session
                require_once CheminPage::SESSION_SERVICE->value;
                demarrer_session();
                $user = recuperer_session('user');
                ?>
                <div>
                    <div><?= htmlspecialchars($user['login'] ?? 'Bonjour  inconnu') ?></div>
                    <div><?= htmlspecialchars($user['profil'] ?? 'Profil inconnu') ?></div>
                </div>

            </div>

        </header>

    </main>

</div>

<div class="content">
    
    <?= $contenu ?>



</div>
</body>
</html>