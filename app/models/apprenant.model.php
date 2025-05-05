<?php
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Enums\CheminPage;

enum APPRENANTMETHODE
{
    case COMPTER_APPRENANTS;
    case FILTRER_APPRENANTS;
    case GET_APPRENANTS_PROMOTION_ACTIVE;
    case GET_REFERENTIELS_PROMOTION_ACTIVE;
    case IMPORTER_APPRENANTS; // Nouvelle méthode
}

global $apprenant_methodes;
$apprenant_methodes = [
    APPRENANTMETHODE::COMPTER_APPRENANTS->name => fn(array $data): int => array_reduce(
        array_filter($data['promotions'], fn($p) => $p['statut'] === 'Active'),
        fn($total, $promo) => $total + array_reduce(
            $promo['referentiel'],
            fn($sum, $ref) => $sum + (isset($ref['apprenant']) ? count($ref['apprenant']) : 0),
            0
        ),
        0
    ),

    APPRENANTMETHODE::FILTRER_APPRENANTS->name => function(array $data, ?string $search = null, ?string $referentiel_id = null, ?string $statut = null): array {
        $resultats = [];
        foreach ($data['promotions'] as $promo) {
            if ($promo['statut'] !== 'Active') continue;
            
            foreach ($promo['referentiel'] as $ref) {
                if (!isset($ref['apprenant'])) continue;
                if ($referentiel_id && $ref['id'] != $referentiel_id) continue;

                foreach ($ref['apprenant'] as $apprenant) {
                    if ($statut && $apprenant['statut'] !== $statut) continue;
                    if ($search && !str_contains(strtolower($apprenant['nom_complet']), strtolower($search))) continue;

                    $resultats[] = array_merge($apprenant, [
                        'referentiel_id' => $ref['id'],
                        'referentiel_nom' => $ref['nom']
                    ]);
                }
            }
        }
        return $resultats;
    },

    APPRENANTMETHODE::GET_APPRENANTS_PROMOTION_ACTIVE->name => function(array $data): array {
        foreach ($data['promotions'] as $promo) {
            if ($promo['statut'] === 'Active') {
                return $promo;
            }
        }
        return [];
    },

    APPRENANTMETHODE::GET_REFERENTIELS_PROMOTION_ACTIVE->name => function(array $data): array {
        $promo_active = [];
        foreach ($data['promotions'] as $promo) {
            if ($promo['statut'] === 'Active') {
                $promo_active = $promo;
                break;
            }
        }
        return $promo_active['referentiel'] ?? [];
    },
    
    // Nouvelle méthode pour importer des apprenants
    APPRENANTMETHODE::IMPORTER_APPRENANTS->name => function(array &$data, array $apprenants_importes): array {
        $resultat = [
            'retenus' => [],
            'attente' => [],
            'erreurs' => []
        ];
        
        // Récupérer la promotion active et ses référentiels
        $promotion_active = null;
        $referentiels = [];
        
        foreach ($data['promotions'] as &$promo) {
            if ($promo['statut'] === 'Active') {
                $promotion_active = &$promo;
                $referentiels = array_column($promo['referentiel'], 'nom', 'id');
                break;
            }
        }
        
        if (!$promotion_active) {
            $resultat['erreurs'][] = "Aucune promotion active trouvée";
            return $resultat;
        }
        
        // S'assurer que la structure pour la liste d'attente existe
        if (!isset($data['liste_attente'])) {
            $data['liste_attente'] = [];
        }
        
        // Traiter chaque apprenant importé
        foreach ($apprenants_importes as $apprenant) {
            // Vérifier si tous les champs requis sont présents
            $champs_requis = [
                'nom_complet', 'adresse', 'telephone', 'email', 'referentiel',
                'nom_complet_tuteur', 'lien_de_parente', 'adresse_du_tuteur', 'telephone_tuteur'
            ];
            
            $champs_manquants = [];
            foreach ($champs_requis as $champ) {
                if (empty($apprenant[$champ])) {
                    $champs_manquants[] = $champ;
                }
            }
            
            // Préparer l'objet apprenant
            $nouvel_apprenant = [
                'id' => uniqid(),
                'matricule' => 'AP' . date('Ym') . rand(1000, 9999),
                'nom_complet' => $apprenant['nom_complet'] ?? '',
                'adresse' => $apprenant['adresse'] ?? '',
                'telephone' => $apprenant['telephone'] ?? '',
                'email' => $apprenant['email'] ?? '',
                'photo' => '/assets/images/default-avatar.png',
                'statut' => 'actif',
                'tuteur' => [
                    'nom_complet' => $apprenant['nom_complet_tuteur'] ?? '',
                    'lien_de_parente' => $apprenant['lien_de_parente'] ?? '',
                    'adresse' => $apprenant['adresse_du_tuteur'] ?? '',
                    'telephone' => $apprenant['telephone_tuteur'] ?? ''
                ]
            ];
            
            // Si des champs sont manquants, ajouter à la liste d'attente
            if (!empty($champs_manquants)) {
                $nouvel_apprenant['raison_attente'] = 'Informations incomplètes: ' . implode(', ', $champs_manquants);
                $data['liste_attente'][] = $nouvel_apprenant;
                $resultat['attente'][] = $nouvel_apprenant;
                continue;
            }
            
            // Vérifier si le référentiel existe dans la promotion active
            $referentiel_trouve = false;
            $referentiel_id = null;
            
            foreach ($promotion_active['referentiel'] as &$ref) {
                if (strtolower($ref['nom']) === strtolower($apprenant['referentiel'])) {
                    $referentiel_trouve = true;
                    $referentiel_id = $ref['id'];
                    
                    // Ajouter l'apprenant au référentiel
                    if (!isset($ref['apprenant'])) {
                        $ref['apprenant'] = [];
                    }
                    
                    $ref['apprenant'][] = $nouvel_apprenant;
                    $resultat['retenus'][] = array_merge($nouvel_apprenant, [
                        'referentiel_id' => $ref['id'],
                        'referentiel_nom' => $ref['nom']
                    ]);
                    break;
                }
            }
            
            // Si le référentiel n'existe pas, ajouter à la liste d'attente
            if (!$referentiel_trouve) {
                $nouvel_apprenant['raison_attente'] = 'Référentiel non trouvé dans la promotion active';
                $data['liste_attente'][] = $nouvel_apprenant;
                $resultat['attente'][] = $nouvel_apprenant;
            }
        }
        
        return $resultat;
    }
];

// On retourne aussi le tableau pour maintenir la compatibilité
return $apprenant_methodes;
