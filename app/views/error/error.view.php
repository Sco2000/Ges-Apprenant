<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur</title>
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        
        .error-title {
            font-size: 2rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        
        .error-message {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }
        
        .back-link {
            color: #0066cc;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 1px solid #0066cc;
            border-radius: 4px;
        }
        
        .back-link:hover {
            background-color: #0066cc;
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-title">Erreur</h1>
        <p class="error-message"><?= htmlspecialchars($error['message']) ?></p>
        <a href="?page=liste_promo" class="back-link">Retour à l'accueil</a>
    </div>
</body>
</html>