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

$idVisiteur = $_SESSION['idUtilisateur'];
$mois = getMois(date('d/m/Y'));
/* Afin de pouvoir pré-remplir le combo avec une liste des mois, on génère les 
* mois disponibles pour un visiteur choisi au hasard.
*/
$idVisiteurSelectionne = filter_input(INPUT_POST, 'lstVisiteur', FILTER_SANITIZE_STRING);
$moisFicheSelectionne = filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING);
if ($moisFicheSelectionne == false || $moisFicheSelectionne == null) {
    $lesMois = $pdo->getLesMoisDisponibles('a17');
} else {
    $lesMois = $pdo->getLesMoisDisponibles($idVisiteurSelectionne);
}
$lesVisiteurs = $pdo->getLesVisiteurs();
$nomEtPrenomVisiteur = $pdo->getNomEtPrenomVisiteur($idVisiteurSelectionne);
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteurSelectionne, $moisFicheSelectionne);
$lesFraisForfait = $pdo->getLesFraisForfait($idVisiteurSelectionne, $moisFicheSelectionne);
require 'vues/v_listeVisiteur.php';
switch($action) {
case 'afficherFrais':
    $lesMoisDuVisiteur = $pdo->getLesMoisDisponibles($idVisiteurSelectionne);
    $ficheCree = false;
    foreach ($lesMoisDuVisiteur as $unMois) {
        if ($moisFicheSelectionne == $unMois['mois']) {
            $ficheCree = true;
        }
    }
    if ($ficheCree) {
        include 'vues/v_listeFraisForfait.php';
        include 'vues/v_listeFraisHorsForfait.php';
    } else {
        ajouterErreur('Pas de fiche de frais pour ce visiteur ce mois, veuillez en choisir une autre.');
        include 'vues/v_erreurs.php';
    }
    break;
}
