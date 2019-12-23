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
$estFicheValidee = false;
$idComptable = $_SESSION['idUtilisateur'];
/*
* On récupère l'id du visiteur selectionné et le mois de la fiche selectionnée.
* On stocke l'id et le mois dans une variable de session afin que ces 2 variables
* soient accessibles dans toutes les vues et les autres contrôleurs.
*/
if (filter_input(
    INPUT_POST, 
    'lstVisiteur', 
    FILTER_SANITIZE_STRING
)
) {
    $idVisiteurSelectionne = filter_input(
        INPUT_POST, 
        'lstVisiteur', 
        FILTER_SANITIZE_STRING
    );
}

if (filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING)) {
    $moisFicheSelectionne = filter_input(
        INPUT_POST, 
        'lstMois', 
        FILTER_SANITIZE_STRING
    );
}
if (isset($idVisiteurSelectionne) && isset($moisFicheSelectionne)) {
    
    setIdVisiteurEtMoisSelectionnes($idVisiteurSelectionne, $moisFicheSelectionne);
} 
if (isset($_SESSION['idVisiteurSelectionne']) 
    && isset($_SESSION['moisSelectionne'])
) {
    $idVisiteurSelectionne = $_SESSION['idVisiteurSelectionne'];
    $moisFicheSelectionne = $_SESSION['moisSelectionne'];
}

$lesMois = $pdo->getTousLesMois();
$lesVisiteurs = $pdo->getLesVisiteurs();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);

require 'vues/v_listeVisiteur.php';
switch($action) {
case 'selectionnerMois':
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
case 'voirEtatFrais':
    $lesMoisDuVisiteur = $pdo->getLesMoisDisponibles($idVisiteurSelectionne);
    foreach ($lesMoisDuVisiteur as $unMois) {
        if ($moisFicheSelectionne == $unMois['mois']) {
            $ficheExistante = true;
        }
    }
    /*
     * Si la fiche selectionnée pour le visiteur en question existe, on
     * génère les frais forfaitaires et hors forfaitaires du visiteur
     * selectionné et pour la fiche selectionnée
    */
    if ($ficheExistante) {
        $nomEtPrenomVisiteur = $pdo->getNomEtPrenomVisiteur(
            $idVisiteurSelectionne
        );
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait(
            $idVisiteurSelectionne, 
            $moisFicheSelectionne
        );
        $lesFraisForfait = $pdo->getLesFraisForfait(
            $idVisiteurSelectionne, 
            $moisFicheSelectionne
        );
        $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais(
            $idVisiteurSelectionne, 
            $moisFicheSelectionne
        );
        $libEtat = $lesInfosFicheFrais['libEtat'];
        $idEtat = $lesInfosFicheFrais['idEtat'];
        $dateModif = dateAnglaisVersFrancais($lesInfosFicheFrais['dateModif']);
        $montantValide = $lesInfosFicheFrais['montantValide'];
        $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
        $leMois = filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING);
        $numAnnee = substr($leMois, 0, 4);
        $numMois = substr($leMois, 4, 2);
        include 'vues/v_etatFrais.php';
    } else {
        ajouterErreur(
            'Pas de fiche de frais pour ce visiteur ce mois,
             veuillez en choisir une autre.'
        );
        include 'vues/v_erreurs.php';
    }
    break;
case 'rembourserFrais':
    $pdo->majEtatFicheFrais($idVisiteurSelectionne, $moisFicheSelectionne, 'RB');
    break;
}

