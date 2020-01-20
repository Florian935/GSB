<?php
/**
 * Hashage de tous les mdp de la BDD qui n'ont pas encore été hashés
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Réseau CERTA <contact@reseaucerta.org>
 * @author    Florian MARTIN <florian.martin63000@gmail.com>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */


$pdo = new PDO(
    'mysql:host=localhost;dbname=id11601272_appligsb', 
    'id11601272_usergsb', 
    'PPEappliBDD97531'
);
$pdo->query('SET CHARACTER SET utf8');
$nbMdpHache = 0;

$requetePrepare = $pdo->prepare(
    'SELECT visiteur.mdp as mdp, visiteur.id as id '
    . 'FROM visiteur '
);
$requetePrepare->execute();
$lesInfosDesVisiteurs = $requetePrepare->fetchAll();

// Hâchage des mots de passe des visiteurs
foreach ($lesInfosDesVisiteurs as $infosDuVisiteur) {
    /*
    * Il ne faut pas hasher les mdp qui ont déjà été hashés. Etant donné
    * que les mdp générés aléatoirement par le script "majGSB.php" ne génère que 
    * des mdp d'une taille de 5 caractères, si la taille du mdp est égale à 5, 
    * alors on crypte le mdp. Sinon, c'est que le mdp a déjà été hashé donc on 
    * n'effectue pas de hashage.
    */
    if (strlen($infosDuVisiteur['mdp']) == 5) {
        hacherMotDePasseDansBdd($infosDuVisiteur['id'], $infosDuVisiteur['mdp']);
        $nbMdpHache += 1;
    }
}

$requetePrepare = $pdo->prepare(
    'SELECT comptable.mdp as mdp, comptable.id as id '
    . 'FROM comptable '
);
$requetePrepare->execute();
$lesInfosDesComptables = $requetePrepare->fetchAll();


// Hâchage des mots de passe des comptables qui n'ont pas encore été haché
foreach ($lesInfosDesComptables as $infosDuComptable) {
    if (strlen($infosDuComptable['mdp']) == 5) {
        hacherMotDePasseDansBdd($infosDuComptable['id'], $infosDuComptable['mdp']);
        $nbMdpHache += 1;
    }
}

/**
 * Permet d'hasher un mdp pour un utilisateur donné dans la bdd grâce à un 
 * algorithme de cryptage
 * 
 * @param String $idUtilisateur id de l'utilisateur
 * @param String $mdp           mdp entrée par l'utilisateur
 * 
 * @return null
 */
function hacherMotDePasseDansBdd($idUtilisateur, $mdp) 
{
    $pdo = new PDO(
        'mysql:host=localhost;dbname=id11601272_appligsb', 
        'id11601272_usergsb', 
        'PPEappliBDD97531'
    );
    $pdo->query('SET CHARACTER SET utf8');
    $requetePrepare = $pdo->prepare(
        'SELECT visiteur.id as id '
        . 'FROM visiteur '
        . 'WHERE id = :unIdinUtilisateur'
    );
    $requetePrepare->bindParam(
        ':unIdinUtilisateur', 
        $idUtilisateur, 
        PDO::PARAM_STR
    );
    $requetePrepare->execute();
    $idVisiteur = $requetePrepare->fetch();

    $mdpCrypte = hash("sha256", $mdp);
    if (is_array($idVisiteur)) {
        $requetePrepare = $pdo->prepare(
            'UPDATE visiteur '
            . "SET mdp = '" . $mdpCrypte . "' "
            . 'WHERE id = :unIdUtilisateur'
        );
        $requetePrepare->bindParam(
            ':unIdUtilisateur', 
            $idUtilisateur, 
            PDO::PARAM_STR
        );
        $requetePrepare->execute();
    } else {
        $requetePrepare = $pdo->prepare(
            'UPDATE comptable '
            . "SET mdp = '" . $mdpCrypte . "' "
            . 'WHERE id = :unIdUtilisateur'
        );
        $requetePrepare->bindParam(
            ':unIdUtilisateur', 
            $idUtilisateur, 
            PDO::PARAM_STR
        );
        $requetePrepare->execute();
    }
}

echo $nbMdpHache . ' mot(s) de passe a/ont été crypté(s).';
