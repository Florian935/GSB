<?php
/**
 * Vue Accueil
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */
?>
<div id="accueil">
    <h2>
        Gestion des frais<small> - 
        <?php if ($typeUtilisateur == 'visiteur') { 
            ?> Visiteur : 
        <?php } else { 
            ?> Comptable : <?php 
        } ?>
            <?php 
            echo $_SESSION['prenom'] . ' ' . $_SESSION['nom']
            ?></small>
    </h2>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <span class="glyphicon glyphicon-bookmark"></span>
                    Navigation
                </h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <?php if ($typeUtilisateur == 'visiteur') { ?>
                        <a href="index.php?uc=gererFrais&action=saisirFrais"
                           class="btn btn-success btn-lg" role="button">
                        <?php } else { ?>
                        <a href="index.php?uc=validerFrais&action=validationFrais"
                           class="btn btn-success btn-lg" role="button">
                        <?php } ?>
                            <span <?php if ($typeUtilisateur == 'visiteur') { 
                                ?> 
                            class="glyphicon glyphicon-list-alt" <?php } else {
                                        ?> 
                            class="glyphicon glyphicon-ok" <?php } ?>></span>
                            <br>
                            <?php if ($typeUtilisateur == 'visiteur') { 
                                ?> Renseigner la fiche de frais 
                            <?php } else { 
                                ?> Valider les fiches de frais <?php 
                            } ?></a>
                        <?php if ($typeUtilisateur == 'visiteur') { ?>
                        <a href="index.php?uc=etatFrais&action=selectionnerMois"
                           class="btn btn-primary btn-lg" role="button">
                        <?php } else { ?>
                        <a href="index.php?uc=suivreFrais"
                            class="btn btn-primary btn-lg" role="button">
                        <?php } ?>
                            <span <?php if ($typeUtilisateur == 'visiteur') { 
                                ?> 
                            class="glyphicon glyphicon-list-alt" <?php } else { 
                                        ?> 
                            class="glyphicon glyphicon-euro" <?php } ?>></span>
                            <br>
                            <?php if ($typeUtilisateur == 'visiteur') { 
                                ?> Afficher mes fiches de frais
                            <?php } else { 
                                ?> Suivre le paiement des fiches de frais <?php 
                            } 
                            ?></a>
                            
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>