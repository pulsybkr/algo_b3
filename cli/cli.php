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
    system('clear');
    
    // Obtenir la largeur du terminal
    $terminalWidth = exec('tput cols');
    $terminalWidth = $terminalWidth ?: 80; // Valeur par défaut si non détectable
    
    // Fonction helper pour centrer le texte
    $centerText = function($text) use ($terminalWidth) {
        $padding = max(0, ($terminalWidth - strlen(strip_tags($text))) / 2);
        return str_repeat(' ', (int)$padding) . $text;
    };
    
    // En-tête adaptatif
    $headerText = "SYSTÈME DE BIBLIOTHÈQUE";
    $padding = str_repeat(' ', max(0, ($terminalWidth - strlen($headerText)) / 2));
    $io->writeln([
        "\n<bg=blue;fg=white;options=bold>" . str_repeat(' ', $terminalWidth) . "</>",
        "<bg=blue;fg=white;options=bold>" . $padding . $headerText . $padding . "</>",
        "<bg=blue;fg=white;options=bold>" . str_repeat(' ', $terminalWidth) . "</>\n"
    ]);

    // Date et heure alignées à droite
    $date = date('d/m/Y H:i');
    $io->writeln(str_repeat(' ', max(0, $terminalWidth - strlen($date))) . "<fg=gray>$date</>\n");

    // Menu principal avec mise en page adaptative
    $io->writeln("<fg=blue;options=bold>📚 MENU PRINCIPAL</>\n");
    
    // Définir la largeur maximale pour la description
    $maxDescWidth = max(20, min(40, (int)($terminalWidth * 0.4)));
    
    // Menu avec icônes pour une meilleure compréhension
    $choice = $io->choice('Choisissez une action', [
        1 => '📖 Afficher les livres',
        2 => '👁️ Afficher un livre',
        3 => '➕ Ajouter un livre',
        4 => '✏️ Modifier un livre',
        5 => '🗑️ Supprimer un livre',
        6 => '🔄 Trier les livres',
        7 => '🔍 Rechercher des livres',
        8 => '📜 Historique des actions',
        9 => '❌ Quitter'
    ], 1);

    // Enlever les émojis pour le switch case
    $choice = preg_replace('/^[^\s]+ /', '', $choice);

    switch ($choice) {
        case 'Afficher les livres':
            $livres = afficherLivres($db);
            $io->section('📚 Liste des Livres');
            
            if (empty($livres)) {
                $io->warning('Aucun livre dans la bibliothèque');
            } else {
                $tableHeaders = ['ID', 'Nom', 'Description', 'Statut'];
                $tableRows = [];
                
                foreach ($livres as $livre) {
                    $status = $livre['disponible'] == 1 ? 
                        '🟢' : 
                        '🔴';
                    
                    // Tronquer le nom et la description si nécessaire
                    $nomMax = max(10, min(20, (int)($terminalWidth * 0.2)));
                    $descMax = max(20, min(40, (int)($terminalWidth * 0.4)));
                    
                    $nom = strlen($livre['nom']) > $nomMax ? 
                        substr($livre['nom'], 0, $nomMax-3) . '...' : 
                        $livre['nom'];
                    
                    $description = wordwrap(
                        strlen($livre['description']) > $descMax ? 
                            substr($livre['description'], 0, $descMax-3) . '...' : 
                            $livre['description'],
                        $descMax,
                        "\n",
                        true
                    );
                    
                    $tableRows[] = [
                        "<fg=blue>{$livre['id']}</>",
                        "<fg=yellow>{$nom}</>",
                        $description,
                        $status
                    ];
                }
                
                $io->table($tableHeaders, $tableRows);
            }
            
            $io->note('Appuyez sur Entrée pour revenir au menu principal');
            readline();
            break;

        case 'Afficher un livre':
            $io->section('👁️ Afficher un livre');
            $id = $io->ask('Entrez l\'ID du livre à afficher');
            $livre = afficherLivre($db, $id);
            
            if (isset($livre['id'])) {
                $descMax = max(20, min(40, (int)($terminalWidth * 0.6)));
                
                $description = wordwrap(
                    $livre['description'],
                    $descMax,
                    "\n",
                    true
                );
                
                $io->writeln([
                    "",
                    "<fg=blue;options=bold>📚 Détails du livre :</>\n",
                    "<fg=yellow>ID          :</> {$livre['id']}",
                    "<fg=yellow>Nom         :</> {$livre['nom']}",
                    "<fg=yellow>Description :</> {$description}",
                    "<fg=yellow>Statut      :</> " . ($livre['disponible'] ? '🟢 Disponible' : '🔴 Indisponible'),
                    ""
                ]);
                
                // Ajouter à l'historique
                enregistrerHistorique("Consultation du livre ID: {$livre['id']} - {$livre['nom']}");
            } else {
                $io->error('Livre non trouvé.');
            }
            
            $io->note('Appuyez sur Entrée pour revenir au menu principal');
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
            $io->section('✏️ Modification d\'un livre');
            $id = $io->ask('Entrez l\'ID du livre à modifier');
            $livre = afficherLivre($db, $id);
            
            if (isset($livre['id'])) {
                $io->writeln("\n<fg=yellow>Livre actuel :</>");
                $io->table(
                    ['ID', 'Nom', 'Description', 'Statut'],
                    [[
                        "<fg=blue>{$livre['id']}</>",
                        $livre['nom'],
                        wordwrap($livre['description'], min(40, (int)($terminalWidth * 0.4))),
                        $livre['disponible'] ? '🟢' : '🔴'
                    ]]
                );
                
                $nom = $io->ask('Nouveau nom du livre', $livre['nom']);
                $description = $io->ask('Nouvelle description', $livre['description']);
                $disponible = $io->confirm('Le livre est-il disponible ?', $livre['disponible']);
                $io->success(modifierLivre($db, $id, $nom, $description, $disponible ? 1 : 0));
            } else {
                $io->error('Livre non trouvé.');
            }
            
            $io->note('Appuyez sur Entrée pour revenir au menu principal');
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
            $io->section("📊 Livres triés par '$colonne'");
            
            if (empty($sortedLivres)) {
                $io->warning('Aucun livre dans la bibliothèque');
            } else {
                $tableHeaders = ['ID', 'Nom', 'Description', 'Statut'];
                $tableRows = [];
                
                foreach ($sortedLivres as $livre) {
                    $nomMax = max(10, min(20, (int)($terminalWidth * 0.2)));
                    $descMax = max(20, min(40, (int)($terminalWidth * 0.4)));
                    
                    $nom = strlen($livre['nom']) > $nomMax ? 
                        substr($livre['nom'], 0, $nomMax-3) . '...' : 
                        $livre['nom'];
                    
                    $description = wordwrap(
                        strlen($livre['description']) > $descMax ? 
                            substr($livre['description'], 0, $descMax-3) . '...' : 
                            $livre['description'],
                        $descMax,
                        "\n",
                        true
                    );
                    
                    $status = $livre['disponible'] ? '🟢' : '🔴';
                    
                    $tableRows[] = [
                        "<fg=blue>{$livre['id']}</>",
                        "<fg=yellow>{$nom}</>",
                        $description,
                        $status
                    ];
                }
                
                $io->table($tableHeaders, $tableRows);
            }
            
            $io->note('Appuyez sur Entrée pour revenir au menu principal');
            readline();
            break;

        case 'Rechercher des livres':
            $colonne = $io->choice('Choisissez la colonne pour la recherche', ['nom', 'description', 'disponible', 'id']);
            $valeur = $io->ask("Entrez la valeur à rechercher dans '$colonne'");
            $resultat = rechercherLivre($db, $colonne, $valeur);
            
            if ($resultat['success']) {
                $io->section('🔍 Résultats de la recherche');
                
                if (empty($resultat['resultats'])) {
                    $io->warning('Aucun livre trouvé');
                } else {
                    $tableHeaders = ['ID', 'Nom', 'Description', 'Statut'];
                    $tableRows = [];
                    
                    foreach ($resultat['resultats'] as $livre) {
                        $nomMax = max(10, min(20, (int)($terminalWidth * 0.2)));
                        $descMax = max(20, min(40, (int)($terminalWidth * 0.4)));
                        
                        $nom = strlen($livre['nom']) > $nomMax ? 
                            substr($livre['nom'], 0, $nomMax-3) . '...' : 
                            $livre['nom'];
                        
                        $description = wordwrap(
                            strlen($livre['description']) > $descMax ? 
                                substr($livre['description'], 0, $descMax-3) . '...' : 
                                $livre['description'],
                            $descMax,
                            "\n",
                            true
                        );
                        
                        $status = $livre['disponible'] ? '🟢' : '🔴';
                        
                        $tableRows[] = [
                            "<fg=blue>{$livre['id']}</>",
                            "<fg=yellow>{$nom}</>",
                            $description,
                            $status
                        ];
                    }
                    
                    $io->table($tableHeaders, $tableRows);
                }
            } else {
                $io->error($resultat['message']);
            }
            
            $io->note('Appuyez sur Entrée pour revenir au menu principal');
            readline();
            break;

        case 'Historique des actions':
            $historyFile = '/var/www/html/database/historique.txt';
            $io->section('📜 Historique des actions');
            
            if (file_exists($historyFile)) {
                $historique = file_get_contents($historyFile);
                $lignes = explode("\n", $historique);
                
                // Adapter le texte à la largeur de l'écran
                $maxWidth = max(40, min(80, (int)($terminalWidth * 0.8)));
                
                foreach ($lignes as $ligne) {
                    if (!empty(trim($ligne))) {
                        $io->writeln(wordwrap($ligne, $maxWidth, "\n", true));
                    }
                }
            } else {
                $io->warning('Aucun historique trouvé.');
            }
            
            $io->note('Appuyez sur Entrée pour revenir au menu principal');
            readline();
            break;

        case 'Quitter':
            $io->writeln([
                "\n<bg=red;fg=white;options=bold>                                                    </>",
                "<bg=red;fg=white;options=bold>                   Au revoir ! 👋                     </>",
                "<bg=red;fg=white;options=bold>                                                    </>\n"
            ]);
            sleep(1);
            $running = false;
            break;
    }
}
?>