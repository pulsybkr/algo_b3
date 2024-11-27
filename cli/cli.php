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
        3 => 'Modifier un livre',
        4 => 'Supprimer un livre',
        5 => 'Trier les livres',
        6 => 'Rechercher des livres',
        7 => 'Historique des actions',
        8 => 'Quitter'
    ], 1);

    switch ($choice) {
        case 'Afficher les livres':
            $livres = afficherLivres($db);
            $io->section('Liste des Livres');
            
            // Préparation des données pour le tableau
            $tableHeaders = ['ID', 'Nom', 'Description', 'Statut'];
            $tableRows = [];
            
            foreach ($livres as $livre) {
                $status = $livre['disponible'] ? 
                    '<fg=green>Disponible</>' : 
                    '<fg=red>Indisponible</>';
                
                $tableRows[] = [
                    $livre['id'],
                    "<fg=yellow>{$livre['nom']}</>",
                    $livre['description'],
                    $status
                ];
            }
            
            // Affichage du tableau
            $io->table($tableHeaders, $tableRows);
            
            $io->writeln("\n<fg=blue>Appuyez sur Entrée pour continuer...</>");
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

            case 'Modifier un livre':
            $id = $io->ask('Entrez l\'ID du livre à modifier');
            $livre = afficherLivre($db, $id);
            if (isset($livre['id'])) {
                $nom = $io->ask('Entrez le nouveau nom du livre', $livre['nom']);
                $description = $io->ask('Entrez la nouvelle description du livre', $livre['description']);
                $disponible = $io->confirm('Le livre est-il disponible ?', $livre['disponible']);
                $io->success(modifierLivre($db, $id, $nom, $description, $disponible ? 1 : 0));
            } else {
                $io->error('Livre non trouvé.');
            }
            $io->writeln("\nAppuyez sur Entrée pour continuer...");
            readline();
            break;

        case 'Supprimer un livre':
            $id = $io->ask('Entrez l\'ID du livre à supprimer');
            $livre = afficherLivre($db, $id);
            if (isset($livre['id'])) {
                $confirmation = $io->confirm("Êtes-vous sûr de vouloir supprimer le livre '{$livre['nom']}' ?", false);
                if ($confirmation) {
                    $io->success(supprimerLivre($db, $id));
                } else {
                    $io->writeln("<fg=yellow>Suppression annulée.</>");
                }
            } else {
                $io->error('Livre non trouvé.');
            }
            $io->writeln("\nAppuyez sur Entrée pour continuer...");
            readline();
            break;

        case 'Trier les livres':
            $colonne = $io->choice('Choisissez la colonne pour trier', ['nom', 'description', 'disponible']);
            $sortedLivres = trierLivres($db, $colonne);
            $io->section("Livres triés par '$colonne'");
            
            // Préparation des données pour le tableau
            $tableHeaders = ['ID', 'Nom', 'Description', 'Statut'];
            $tableRows = [];
            
            foreach ($sortedLivres as $livre) {
                $status = $livre['disponible'] ? 
                    '<fg=green>Disponible</>' : 
                    '<fg=red>Indisponible</>';
                
                $tableRows[] = [
                    $livre['id'],
                    "<fg=yellow>{$livre['nom']}</>",
                    $livre['description'],
                    $status
                ];
            }
            
            // Affichage du tableau
            $io->table($tableHeaders, $tableRows);
            
            $io->writeln("\n<fg=blue>Appuyez sur Entrée pour continuer...</>");
            readline();
            break;

        case 'Rechercher des livres':
            $colonne = $io->choice('Choisissez la colonne pour la recherche', ['nom', 'description', 'disponible', 'id']);
            $valeur = $io->ask("Entrez la valeur à rechercher dans '$colonne'");
            $resultat = rechercherLivre($db, $colonne, $valeur);
            
            if (isset($resultat['id'])) {
                $io->section('Livre trouvé');
                $io->table(['ID', 'Nom', 'Description', 'Statut'], [
                    [
                        $resultat['id'],
                        $resultat['nom'],
                        $resultat['description'],
                        $resultat['disponible'] ? 'Disponible' : 'Indisponible'
                    ]
                ]);
            } else {
                $io->error('Livre non trouvé.');
            }
            
            $io->writeln("\n<fg=blue>Appuyez sur Entrée pour continuer...</>");
            readline();
            break;

        case 'Historique des actions':
            $historyFile = '/var/www/html/database/historique.txt';
            if (file_exists($historyFile)) {
                $historique = file_get_contents($historyFile);
                $io->section('Historique des actions');
                $io->writeln($historique);
            } else {
                $io->writeln("<fg=yellow>Aucun historique trouvé.</>");
            }
            $io->writeln("\n<fg=blue>Appuyez sur Entrée pour continuer...</>");
            readline();
            break;

        case 'Quitter':
            $io->success('Au revoir!');
            $running = false;
            break;
    }
}
?>