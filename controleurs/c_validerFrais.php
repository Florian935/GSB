<?php
/**
 * Gestion des frais
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


$ficheExistante = false;
/*
* On récupère l'id du visiteur selectionné et le mois de la fiche selectionnée.
* On stocke l'id et le mois dans une variable de session afin que ces 2 variables
* soient accessibles dans toutes les vues et les autres contrôleurs.
*/
$idVisiteurSelectionne = filter_input(INPUT_POST, 'lstVisiteur', FILTER_SANITIZE_STRING);
$moisFicheSelectionne = filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING);
if(isset($idVisiteurSelectionne) && isset($moisFicheSelectionne)) {
    setIdVisiteurEtMoisSelectionnes($idVisiteurSelectionne, $moisFicheSelectionne);
};

$lesMois = $pdo->getTousLesMois();
$lesVisiteurs = $pdo->getLesVisiteurs();

/* On récupère l'id du frais hors forfait à corriger, reporter ou refuser
* et on indique l'action à effectuer en récupérant l'attribut "name" du bouton
* sur lequel le comptable a cliqué
*/
$idFraisHorsForfaitACorriger = filter_input(
    INPUT_POST, 'corriger',
    FILTER_SANITIZE_STRING
);
$idFraisHorsForfaitAReporter = filter_input(
    INPUT_POST, 'reporter',
    FILTER_SANITIZE_STRING
);
$idFraisHorsForfaitARefuser = filter_input(
    INPUT_POST, 'refuser',
    FILTER_SANITIZE_STRING
);
if ($idFraisHorsForfaitACorriger != null) {
    $action = 'corriger';
} elseif ($idFraisHorsForfaitAReporter != null) {
    $action = 'reporter';
} elseif ($idFraisHorsForfaitARefuser != null) {
    $action = 'refuser';
} else {
    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
}
require 'vues/v_listeVisiteur.php';
switch($action) {
case 'afficherFrais':
    $nomEtPrenomVisiteur = $pdo->getNomEtPrenomVisiteur(
        $_SESSION['idVisiteurSelectionne']
    );
    $lesMoisDuVisiteur = $pdo->getLesMoisDisponibles($idVisiteurSelectionne);
    foreach ($lesMoisDuVisiteur as $unMois) {
        if ($moisFicheSelectionne == $unMois['mois']) {
            $ficheExistante = true;
        }
    }
    if (!$ficheExistante) {
        ajouterErreur(
            'Pas de fiche de frais pour ce visiteur ce mois,
             veuillez en choisir une autre.'
        );
        include 'vues/v_erreurs.php';
    }
    break;
case 'corriger':
    $dateFraisCorrigee = filter_input(
        INPUT_POST, 'dateFrais-corrige',
        FILTER_SANITIZE_STRING
    );
    $libelleFraisCorrige = filter_input(
        INPUT_POST, 'libelle-corrige', 
        FILTER_SANITIZE_STRING
    );
    $montantFraisCorrige = filter_input(
        INPUT_POST, 'montant-corrige', 
        FILTER_VALIDATE_FLOAT
    );
    valideInfosFrais($dateFraisCorrigee, $libelleFraisCorrige, $montantFraisCorrige);
    if (nbErreurs() != 0) {
        include 'vues/v_listeFraisForfait.php';
        include 'vues/v_erreurs.php';
        include 'vues/v_listeFraisHorsForfait.php';
    } else {
        $idFraisHorsForfaitACorriger = (int)$idFraisHorsForfaitACorriger;
        $pdo->majFraisHorsForfait(
            $idFraisHorsForfaitACorriger,
            $_SESSION['idVisiteurSelectionne'],
            $_SESSION['moisSelectionne'],
            $libelleFraisCorrige,
            $dateFraisCorrigee,
            $montantFraisCorrige
        );
        $estMajFraisHorsForfait = true;
        $ficheExistante = true;
    }
    break;
case 'refuser':
    $dateFraisRefusee = filter_input(
        INPUT_POST, 'dateFrais-corrige',
        FILTER_SANITIZE_STRING
    );
    $libelleFraisRefuse = filter_input(
        INPUT_POST, 'libelle-corrige', 
        FILTER_SANITIZE_STRING
    );
    if (substr($libelleFraisRefuse, 0, 6) != 'REFUSE') {
        $libelleFraisRefuse = 'REFUSE ' . $libelleFraisRefuse;
    }
    $montantFraisRefuse = filter_input(
        INPUT_POST, 'montant-corrige', 
        FILTER_VALIDATE_FLOAT
    );
    valideInfosFrais($dateFraisRefusee, $libelleFraisRefuse, $montantFraisRefuse);
    if (nbErreurs() != 0) {
        include 'vues/v_listeFraisForfait.php';
        include 'vues/v_erreurs.php';
        include 'vues/v_listeFraisHorsForfait.php';
    } else {
        $idFraisHorsForfaitARefuser = (int)$idFraisHorsForfaitARefuser;
        $pdo->majFraisHorsForfait(
            $idFraisHorsForfaitARefuser,
            $_SESSION['idVisiteurSelectionne'],
            $_SESSION['moisSelectionne'],
            $libelleFraisRefuse,
            $dateFraisRefusee,
            $montantFraisRefuse
        );
        $estMajFraisHorsForfait = true;
        $ficheExistante = true;
    }
    break;
}
/*
* Si la fiche selectionnée pour le visiteur en question existe, on
* génère les frais forfaitaires et hors forfaitaires du visiteur
* selectionné et pour la fiche selectionnée
*/
if ($ficheExistante) {
    $nomEtPrenomVisiteur = $pdo->getNomEtPrenomVisiteur(
        $_SESSION['idVisiteurSelectionne']
    );
    $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait(
        $_SESSION['idVisiteurSelectionne'], 
        $_SESSION['moisSelectionne']
    );
    $lesFraisForfait = $pdo->getLesFraisForfait(
        $_SESSION['idVisiteurSelectionne'], 
        $_SESSION['moisSelectionne']
    );
    include 'vues/v_listeFraisForfait.php';
    include 'vues/v_listeFraisHorsForfait.php';
}

