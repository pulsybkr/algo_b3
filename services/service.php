<?php
//  Function pour créer un livre
function creerLivre($db, $nom, $description, $disponible) {
    try {
        // Vérifier si la table existe, sinon la créer
        $db->exec("CREATE TABLE IF NOT EXISTS livres (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom TEXT NOT NULL,
            description TEXT NOT NULL,
            disponible BOOLEAN NOT NULL
        )");

        $stmt = $db->prepare("INSERT INTO livres (nom, description, disponible) VALUES (:nom, :description, :disponible)");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':disponible', $disponible);
        $stmt->execute();
        
        // Récupérer l'ID du livre créé
        $id = $db->lastInsertId();
        
        // Mettre à jour le fichier JSON avec les données actuelles
        $livres = getLivres($db);
        $json_data = json_encode($livres, JSON_PRETTY_PRINT);
        file_put_contents('/var/www/html/database/livres.json', $json_data);
        
        enregistrerHistorique("Création du livre : $nom");
        
        return ["message" => "Création de livre réussie", "nom" => $nom, "id" => $id];
    } catch (Exception $e) {
        return ["message" => "Erreur lors de la création du livre: " . $e->getMessage()];
    }
}

//  Function pour modifier un livre
function modifierLivre($db, $id, $nom, $description, $disponible) {
    $stmt = $db->prepare("UPDATE livres SET nom = :nom, description = :description, disponible = :disponible WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':disponible', $disponible);
    $stmt->execute();
    sauvegarderLivresDansJson($db); // Sauvegarde après modification
    enregistrerHistorique("Modification du livre avec ID : $id"); // Historique après action
    return ["message" => "Modification de livre réussie", "nom" => $nom];
}

//  Function pour supprimer un livre
function supprimerLivre($db, $id) {
    $stmt = $db->prepare("DELETE FROM livres WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    sauvegarderLivresDansJson($db); // Sauvegarde après suppression
    enregistrerHistorique("Suppression du livre avec ID : $id"); // Historique après action
    return ["message" => "Suppression de livre réussie", "id" => $id];
}

//  Funcion pour afficher des livres
function afficherLivres($db) {
    try {
        $stmt = $db->query("SELECT * FROM livres");
        $livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($livres)) {
            return ["message" => "Aucun livre à afficher."];
        }
        
        $result = [];
        foreach ($livres as $livre) {
            $result[] = [
                "id" => $livre['id'],
                "nom" => $livre['nom'],
                "description" => $livre['description'],
                "disponible" => $livre['disponible'] ? 1 : "Indisponible"
            ];
        }
        return $result;
    } catch (Exception $e) {
        return ["error" => "Erreur lors de la récupération des livres: " . $e->getMessage()];
    }
}

//  Function pour afficher un livre
function afficherLivre($db, $id) {
    $stmt = $db->prepare("SELECT * FROM livres WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($livre) {
        return [
            "id" => $livre['id'],
            "nom" => $livre['nom'],
            "description" => $livre['description'],
            "disponible" => $livre['disponible'] ? 1 : 0
        ];
    } else {
        return ["message" => "Livre non trouvé."];
    }
}

// Fonction pour trier les livres par une colonne spécifique (Tri fusion)
function mergeSort($livres, $key) {
    if (count($livres) <= 1) {
        return $livres;
    }

    $mid = count($livres) / 2;
    $left = array_slice($livres, 0, $mid);
    $right = array_slice($livres, $mid);

    $left = mergeSort($left, $key);
    $right = mergeSort($right, $key);

    return merge($left, $right, $key);
}

// Fonction de fusion pour le tri fusion
function merge($left, $right, $key) {
    $result = [];
    while (count($left) > 0 && count($right) > 0) {
        if (strcmp($left[0][$key], $right[0][$key]) < 0) {
            $result[] = array_shift($left);
        } else {
            $result[] = array_shift($right);
        }
    }
    return array_merge($result, $left, $right);
}

// Fonction pour trier les livres
function trierLivres($db, $colonne) {
    $livres = getLivres($db); 
    $sortedLivres = mergeSort($livres, $colonne); // Trier les livres par la colonne choisie
    enregistrerHistorique("Livres triés par $colonne"); // Historique après action
    return $sortedLivres;
}

// Fonction pour rechercher un livre par une colonne spécifique (Recherche binaire)
function rechercheBinaire($livres, $key, $valeur) {
    $low = 0;
    $high = count($livres) - 1;

    while ($low <= $high) {
        $mid = floor(($low + $high) / 2);
        if ($livres[$mid][$key] == $valeur) {
            return $livres[$mid]; // Livre trouvé
        } elseif ($livres[$mid][$key] < $valeur) {
            $low = $mid + 1;
        } else {
            $high = $mid - 1;
        }
    }
    return null; // Livre non trouvé
}

// Fonction pour rechercher un livre
function rechercherLivre($db, $colonne, $valeur) {
    $livres = getLivres($db);
    $sortedLivres = mergeSort($livres, $colonne); // Trier les livres
    $resultat = rechercheBinaire($sortedLivres, $colonne, $valeur); // Recherche binaire
    if ($resultat) {
        return $resultat;
    } else {
        return ["message" => "Livre non trouvé avec $colonne : $valeur"];
    }
}

// Fonction pour sauvegarder les livres dans un fichier JSON
function sauvegarderLivresDansJson($db) {
    $livres = getLivres($db);
    $json_data = json_encode($livres, JSON_PRETTY_PRINT);
    file_put_contents('/var/www/html/database/livres.json', $json_data); // Chemin absolu vers database
}

// Fonction pour enregistrer l'historique
function enregistrerHistorique($action) {
    $file = '/var/www/html/database/historique.txt'; // Chemin absolu vers database
    $current = file_exists($file) ? file_get_contents($file) : '';
    $current .= date('Y-m-d H:i:s') . " - " . $action . "\n";
    file_put_contents($file, $current);
}

// Fonction pour récupérer les livres
function getLivres($db) {
    try {
        // Vérifier si la table existe
        $db->exec("CREATE TABLE IF NOT EXISTS livres (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom TEXT NOT NULL,
            description TEXT NOT NULL,
            disponible BOOLEAN NOT NULL
        )");

        $stmt = $db->query("SELECT * FROM livres");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

?>