<?php

function creerLivre($db, $nom, $description, $disponible) {
    $stmt = $db->prepare("INSERT INTO livres (nom, description, disponible) VALUES (:nom, :description, :disponible)");
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':disponible', $disponible);
    $stmt->execute();
    return ["message" => "Création de livre réussie", "nom" => $nom];
}

function modifierLivre($db, $id, $nom, $description, $disponible) {
    $stmt = $db->prepare("UPDATE livres SET nom = :nom, description = :description, disponible = :disponible WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':disponible', $disponible);
    $stmt->execute();
    return ["message" => "Modification de livre réussie", "nom" => $nom];
}

function supprimerLivre($db, $id) {
    $stmt = $db->prepare("DELETE FROM livres WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return ["message" => "Suppression de livre réussie", "id" => $id];
}

function afficherLivres($db) {
    $livres = getLivres($db);
    if (empty($livres)) {
        return ["message" => "Aucun livre à afficher."];
    }
    $result = [];
    foreach ($livres as $livre) {
        $result[] = [
            "id" => $livre['id'],
            "nom" => $livre['nom'],
            "description" => $livre['description'],
            "disponible" => $livre['disponible'] ? "Oui" : "Non"
        ];
    }
    return $result;
}

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
            "disponible" => $livre['disponible'] ? "Oui" : "Non"
        ];
    } else {
        return ["message" => "Livre non trouvé."];
    }
}

function getLivres($db) {
    $stmt = $db->query("SELECT * FROM livres");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>