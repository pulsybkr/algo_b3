<?php
// cli/cli.php

require '/var/www/html/services/service.php';

$db = new PDO('sqlite:/var/www/html/database/database.db');

// Création de la table si elle n'existe pas
$db->exec("CREATE TABLE IF NOT EXISTS livres (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    description TEXT NOT NULL,
    disponible INTEGER NOT NULL
)");

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

// Remplacer l'initialisation de l'application par un menu interactif
$running = true;
$io = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());

while ($running) {
    $io->section('Menu Principal - Bibliothèque');
    $choice = $io->choice('Choisissez une action', [
        1 => 'Afficher les livres',
        2 => 'Ajouter un livre',
        3 => 'Quitter'
    ], 1);

    switch ($choice) {
        case 'Afficher les livres':
            $livres = afficherLivres($db);
            $io->success('Livres disponibles :');
            $io->writeln($livres);
            $io->writeln("\nAppuyez sur Entrée pour continuer...");
            readline();
            break;

        case 'Ajouter un livre':
            $nom = $io->ask('Entrez le nom du livre à ajouter');
            $description = $io->ask('Entrez la description du livre');
            $disponible = $io->confirm('Le livre est-il disponible ?', true);
            $io->success(creerLivre($db, $nom, $description, $disponible ? 1 : 0));
            $io->writeln("\nAppuyez sur Entrée pour continuer...");
            readline();
            break;

        case 'Quitter':
            $io->success('Au revoir!');
            $running = false;
            break;
    }
}

// Supprimer tout le code relatif à $application après ce point
?>