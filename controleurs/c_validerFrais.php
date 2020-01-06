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
    $nbJustificatifsDeBase = $pdo->getNbjustificatifs(
        $idVisiteurSelectionne, 
        $moisFicheSelectionne
    );
}

if (isset($_SESSION['idVisiteurSelectionne'])) {
    $lesMois = $pdo->getLesMoisFicheClotureVisiteur($idVisiteurSelectionne);
} else {
    $lesMois = $pdo->getTousLesMois();
}

$lesVisiteurs = $pdo->getLesVisiteurs();

// On récupère l'id du frais hors forfait à corriger, reporter ou refuser
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
/*
 * Si une des variables est initialisées, c'est qu'on
 * doit soit corriger, reporter ou refuser un frais.
 * On valorise la variable $traitementAEffectuer pour
 * savoir quel traitement sera à effectuer sur le frais.
 * */
if (isset($idFraisHorsForfaitACorriger)
    || isset($idFraisHorsForfaitAReporter)
    || isset($idFraisHorsForfaitARefuser)
) {
    $action = 'modification';       
    if (isset($idFraisHorsForfaitACorriger)) {
        $traitementAEffectuer = 'corriger';
        $idFraisHorsForfait = $idFraisHorsForfaitACorriger;
    } elseif (isset($idFraisHorsForfaitAReporter)) {
        $traitementAEffectuer = 'reporter';
        $idFraisHorsForfait = $idFraisHorsForfaitAReporter;
    } elseif (isset($idFraisHorsForfaitARefuser)) {
        $traitementAEffectuer = 'refuser';
        $idFraisHorsForfait = $idFraisHorsForfaitARefuser;
    }
} else {
        $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
}
require 'vues/v_listeVisiteur.php';
switch($action) {
case 'afficherFrais':
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
    
    valideInfosFrais($dateFrais, $libelleFrais, $montantFrais);
    if (nbErreurs() != 0) {
        include 'vues/v_erreurs.php';
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
            /* Si le frais est à reporter, on doit vérifier que la fiche
            * dans laquelle on reporte le frais est bien créée. Si ce n'est
            * pas le cas, on la créée puis on reporte le frais. On supprime également
             le frais de la fiche actuelle.
            */
        } elseif ($traitementAEffectuer == 'reporter') {
            $mois = getMois(date('d/m/Y'));
            if ($pdo->estPremierFraisMois($idVisiteurSelectionne, $mois)) {
                    $pdo->creeNouvellesLignesFrais(
                        $idVisiteurSelectionne, 
                        $mois
                    );
            }
            $dernierMoisVisiteur = $pdo->dernierMoisSaisi($idVisiteurSelectionne);
            $pdo->supprimerFraisHorsForfait($idFraisHorsForfait);
            $pdo->creeNouveauFraisHorsForfait(
                $idVisiteurSelectionne,
                $dernierMoisVisiteur,
                $libelleFrais,
                $dateFrais,
                $montantFrais
            );
        }
        $estMajFraisHorsForfait = true;
        $ficheExistante = true;
    }
    break;
case 'validerNbJustificatifs':
    $nbJustificatifs = (int)filter_input(
        INPUT_POST, 
        'nbJustificatif', 
        FILTER_VALIDATE_FLOAT
    );
    $pdo->majNbJustificatifs(
        $idVisiteurSelectionne, 
        $moisFicheSelectionne, 
        $nbJustificatifs
    );
    $estMajFraisHorsForfait = true;
    $ficheExistante = true;
    $nbJustificatifsDeBase = $pdo->getNbjustificatifs(
        $idVisiteurSelectionne, 
        $moisFicheSelectionne
    );
    break;
case 'validerFiche':
    $pdo->validerLaFiche(
        $idVisiteurSelectionne, 
        $idComptable, 
        $moisFicheSelectionne
    );
    $montantValide = $pdo->getMontantValideHorsFraisRefuses(
        $idVisiteurSelectionne, 
        $moisFicheSelectionne
    );
    $pdo->majMontantValide(
        $idVisiteurSelectionne, 
        $moisFicheSelectionne, 
        $montantValide
    );
    $estFicheValidee = true;
    break;
}

if (isset($idVisiteurSelectionne) && isset($moisFicheSelectionne)) {
    $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais(
        $idVisiteurSelectionne, 
        $moisFicheSelectionne
    );
    $libEtat = $lesInfosFicheFrais['idEtat'];
}

/*
* Si la fiche selectionnée pour le visiteur en question existe et qu'elle 
* n'a pas encore été validée, on génère les frais forfaitaires et hors 
* forfaitaires du visiteur selectionné et pour la fiche selectionnée
*/
if ($ficheExistante && !$estFicheValidee && $libEtat == 'CL') {
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
} elseif ($ficheExistante && !$estFicheValidee && !$libEtat != 'CL') {
    ajouterErreur(
        'Cette fiche pour ce visiteur a déjà été validée, veuillez en 
        choisir une autre.'
    );
    include 'vues/v_erreurs.php';
}
if ($estFicheValidee) {
    $nomEtPrenomVisiteur = $pdo->getNomEtPrenomVisiteur(
        $idVisiteurSelectionne
    );
    $annee = substr($moisFicheSelectionne, 0, 4);
    $mois = substr($moisFicheSelectionne, 4, 2);
    $moisAnnee = $mois . '/' . $annee;
    include 'vues/v_listeVisiteur.php';
}

