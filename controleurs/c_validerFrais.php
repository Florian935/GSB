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

$idUtilisateur = $_SESSION['idUtilisateur'];
$mois = getMois(date('d/m/Y'));
$ficheCree = false;
$idVisiteurSelectionne = filter_input(INPUT_POST, 'lstVisiteur', FILTER_SANITIZE_STRING);
$moisFicheSelectionne = filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING);
if(isset($idVisiteurSelectionne) && isset($moisFicheSelectionne)) {
    setIdVisiteurEtMoisSelectionnes($idVisiteurSelectionne, $moisFicheSelectionne);
};
// $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($_SESSION['idVisiteurSelectionne'], $_SESSION['moisSelectionne']);
// $lesFraisForfait = $pdo->getLesFraisForfait($_SESSION['idVisiteurSelectionne'], $_SESSION['moisSelectionne']);

$lesMois = $pdo->getTousLesMois();
$lesVisiteurs = $pdo->getLesVisiteurs();


/* On récupère l'id du frais hors forfait à corriger, reporter ou refuser
* et on indique l'action à effectuer en récupérant l'attribut "name" du bouton
* sur lequel le comptable a cliqué
*/
$idFraisHorsForfaitACorriger = filter_input(INPUT_POST, 'corriger', FILTER_SANITIZE_STRING);
$idFraisHorsForfaitAReporter = filter_input(INPUT_POST, 'reporter', FILTER_SANITIZE_STRING);
$idFraisHorsForfaitARefuser = filter_input(INPUT_POST, 'refuser', FILTER_SANITIZE_STRING);
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
    $nomEtPrenomVisiteur = $pdo->getNomEtPrenomVisiteur($_SESSION['idVisiteurSelectionne']);
    $lesMoisDuVisiteur = $pdo->getLesMoisDisponibles($idVisiteurSelectionne);
    foreach ($lesMoisDuVisiteur as $unMois) {
        if ($moisFicheSelectionne == $unMois['mois']) {
            $ficheCree = true;
        }
    }
    if (!$ficheCree) {
        ajouterErreur(
            'Pas de fiche de frais pour ce visiteur ce mois,
             veuillez en choisir une autre.'
        );
        include 'vues/v_erreurs.php';
    }
    break;
case 'corriger':
    $dateFraisCorrigee = filter_input(INPUT_POST, 'dateFrais-corrige', FILTER_SANITIZE_STRING);
    $libelleFraisCorrige = filter_input(INPUT_POST, 'libelle-corrige', FILTER_SANITIZE_STRING);
    $montantFraisCorrige = filter_input(INPUT_POST, 'montant-corrige', FILTER_VALIDATE_FLOAT);
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
        $ficheCree = true;
    }
    break;
}
if ($ficheCree) {
    $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($_SESSION['idVisiteurSelectionne'], $_SESSION['moisSelectionne']);
    $lesFraisForfait = $pdo->getLesFraisForfait($_SESSION['idVisiteurSelectionne'], $_SESSION['moisSelectionne']);
    include 'vues/v_listeFraisForfait.php';
    include 'vues/v_listeFraisHorsForfait.php';
}

