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
if (isset($_SESSION['idVisiteurSelectionne']) && isset($_SESSION['moisSelectionne'])) {
    $idVisiteurSelectionne = $_SESSION['idVisiteurSelectionne'];
    $moisFicheSelectionne = $_SESSION['moisSelectionne'];
}


$lesMois = $pdo->getTousLesMois();
$lesVisiteurs = $pdo->getLesVisiteurs();

/* On récupère l'id du frais hors forfait à corriger, reporter ou refuser
 * et on indique l'action et le traitement à effectuer en récupérant l'attribut 
 * "name" du bouton sur lequel le comptable a cliqué
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
if ($idFraisHorsForfaitACorriger != null
    || $idFraisHorsForfaitAReporter != null
    || $idFraisHorsForfaitARefuser != null
) {
    $action = 'modification';       
    if ($idFraisHorsForfaitACorriger != null) {
        $traitementAEffectuer = 'corriger';
        $idFraisHorsForfait = $idFraisHorsForfaitACorriger;
    } elseif ($idFraisHorsForfaitAReporter != null) {
        $traitementAEffectuer = 'reporter';
        $idFraisHorsForfait = $idFraisHorsForfaitAReporter;
    } elseif ($idFraisHorsForfaitARefuser != null) {
        $traitementAEffectuer = 'refuser';
        $idFraisHorsForfait = $idFraisHorsForfaitARefuser;
    }
} else {
        $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
}
require 'vues/v_listeVisiteur.php';
switch($action) {
case 'afficherFrais':
    $nomEtPrenomVisiteur = $pdo->getNomEtPrenomVisiteur(
        $idVisiteurSelectionne
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
case 'modification':
    $idFraisHorsForfait = (int)$idFraisHorsForfait;
    $dateFrais = filter_input(
        INPUT_POST, 'dateFrais-corrige',
        FILTER_SANITIZE_STRING
    );
    $libelleFrais = filter_input(
        INPUT_POST, 'libelle-corrige', 
        FILTER_SANITIZE_STRING
    );
    $montantFrais = filter_input(
        INPUT_POST, 'montant-corrige', 
        FILTER_VALIDATE_FLOAT
    );
    /* Si le frais est à refuser, on ajoute le texte 'REFUSE' devant 
     * le libellé du frais hors forfait afin de savoir qu'il a été refusé
     * et qu'il ne sera pas pris en compte dans les remboursements.
     * */
    if ($traitementAEffectuer == 'refuser') {
        if (substr($libelleFrais, 0, 6) != 'REFUSE') {
            $libelleFrais = 'REFUSE ' . $libelleFrais;
        }
    }
    /* Si le frais est à reporter, on doit vérifier que la fiche
     * dans laquelle on reporte le frais est bien créée. Si ce n'est
     * pas le cas, on la créée. On supprime le frais de la fiche
     * actuelle.
    */
    if ($traitementAEffectuer == 'reporter') {
        $mois = getMois(date('d/m/Y'));
        if ($pdo->estPremierFraisMois($idVisiteurSelectionne, $mois)) {
            $pdo->creeNouvellesLignesFrais(
                $idVisiteurSelectionne, 
                $mois
            );
        }
        $pdo->supprimerFraisHorsForfait($idFraisHorsForfait);
    }
    $moisFicheCree = $pdo->dernierMoisSaisi($idVisiteurSelectionne);
    valideInfosFrais($dateFrais, $libelleFrais, $montantFrais);
    if (nbErreurs() != 0) {
        include 'vues/v_listeFraisForfait.php';
        include 'vues/v_erreurs.php';
        include 'vues/v_listeFraisHorsForfait.php';
    } else {
        if ($traitementAEffectuer == 'corriger' 
            || $traitementAEffectuer == 'refuser'
        ) {
            $pdo->majFraisHorsForfait(
                $idFraisHorsForfait,
                $idVisiteurSelectionne,
                $moisFicheSelectionne,
                $libelleFrais,
                $dateFrais,
                $montantFrais
            );
        } elseif ($traitementAEffectuer == 'reporter') {
            $pdo->creeNouveauFraisHorsForfait(
                $idVisiteurSelectionne,
                $moisFicheCree,
                $libelleFrais,
                $dateFrais,
                $montantFrais
            );
        }
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
    include 'vues/v_listeFraisForfait.php';
    include 'vues/v_listeFraisHorsForfait.php';
}

