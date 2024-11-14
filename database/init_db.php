<?php
$db = new PDO('sqlite:database.db');

// Créer la table items si elle n'existe pas
$db->exec("CREATE TABLE IF NOT EXISTS items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
);   CREATE TABLE IF NOT EXISTS livres (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        description TEXT,
        disponible BOOLEAN NOT NULL
    );");

echo "Table 'items' créée avec succès.";
echo "Table 'livres' créée avec succès.";

?> 