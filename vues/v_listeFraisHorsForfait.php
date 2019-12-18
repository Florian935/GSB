<?php
/**
 * Vue Liste des frais hors forfait
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
<hr>
<?php if ((isset($estMajFraisHorsForfait) && $estMajFraisHorsForfait) 
    || (isset($FraisHorsForfaitSupprime) && $FraisHorsForfaitSupprime)
) { ?>
        <p class="alert alert-success">Les Modifications ont été prises en compte.</p>
<?php } ?>
<div class="row">
    
    <div class="panel panel-info">
        <div class="panel-heading">Descriptif des éléments hors forfait</div>
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th class="date">Date</th>
                    <th class="libelle">Libellé</th>  
                    <th class="montant">Montant</th>  
                    <th class="action">&nbsp;</th> 
                </tr>
            </thead>  
            <tbody>
            <?php
            foreach ($lesFraisHorsForfait as $unFraisHorsForfait) {
                $libelle = htmlspecialchars($unFraisHorsForfait['libelle']);
                $date = htmlspecialchars($unFraisHorsForfait['date']);
                $montant = htmlspecialchars($unFraisHorsForfait['montant']);
                $idFraisHorsForfait = htmlspecialchars(
                    $unFraisHorsForfait['id']
                ); ?>     

                <?php 
                if ($typeUtilisateur == 'visiteur') { ?>
                <tr>
                    <td id="td-utilisateur"><?php echo $date ?></td>
                    <td id="td-utilisateur"><?php echo $libelle ?></td>
                    <td id="td-utilisateur"><?php echo $montant ?></td>
                    <td id="td-utilisateur">
                        <a href="index.php?uc=gererFrais&action=supprimerFrais&idFrais=<?php echo $idFraisHorsForfait ?>" 
                           onclick="return confirm('Voulez-vous vraiment supprimer ce frais?');">Supprimer ce frais</a></td>
                </tr>
                <?php } elseif ($typeUtilisateur == 'comptable') { ?>
                    <form action="index.php?uc=validerFrais" 
                          method="post" role="form">
                        <tr>
                            <td id="td-utilisateur"> 
                            <input type="text" 
                                   id="form-control-comptable" 
                                   name="dateFrais-corrige" 
                                   class="form-control" id="text"
                                   value="<?php echo $date?>">
                            </td>
                            <td id="td-utilisateur">
                            <input type="text" 
                                   id="form-control-comptable" 
                                   name="libelle-corrige" 
                                   class="form-control" id="text"
                                   value="<?php echo $libelle?>">
                            </td>
                            <td id="td-utilisateur"> 
                            <input type="text" 
                                   id="form-control-comptable" 
                                   name="montant-corrige" 
                                   class="form-control" id="text"
                                   value="<?php echo $montant?>">
                            </td>
                            <td id="td-utilisateur"> 
                                <button class="btn btn-success" name="corriger" 
                                        value="<?php echo $idFraisHorsForfait ?>" 
                                        type="submit">Corriger
                                </button>
                                <button class="btn btn-danger" name="reporter" 
                                        value="<?php echo $idFraisHorsForfait ?>" 
                                        type="submit">Reporter
                                </button>
                                <button class="btn btn-danger" name="refuser" 
                                        value="<?php echo $idFraisHorsForfait ?>" 
                                        type="submit">Refuser
                                </button>
                            </td>
                        </tr>
                    </form>
                <?php } ?>
            <?php } ?>
            </tbody>  
        </table>
    </div>
</div>
<?php if ($typeUtilisateur == 'visiteur') { ?>
<div class="row">
    <h3>Nouvel élément hors forfait</h3>
    <div class="col-md-4">
        <form action="index.php?uc=gererFrais&action=validerCreationFrais" 
              method="post" role="form">
            <div class="form-group">
                <label for="txtDateHF">Date (jj/mm/aaaa): </label>
                <input type="text" id="txtDateHF" name="dateFrais" 
                       class="form-control" id="text">
            </div>
            <div class="form-group">
                <label for="txtLibelleHF">Libellé</label>             
                <input type="text" id="txtLibelleHF" name="libelle" 
                       class="form-control" id="text">
            </div> 
            <div class="form-group">
                <label for="txtMontantHF">Montant : </label>
                <div class="input-group">
                    <span class="input-group-addon">€</span>
                    <input type="text" id="txtMontantHF" name="montant" 
                           class="form-control" value="">
                </div>
            </div>
            <button class="btn btn-success" type="submit">Ajouter</button>
            <button class="btn btn-danger" type="reset">Effacer</button>
        </form>
    </div>
</div>
<?php 
} if ($typeUtilisateur == 'comptable') { ?>
    <form action="index.php?uc=validerFrais&action=validerNbJustificatifs" 
          method="post" role="form" class="form-justificatif">
        <div class="choix-justificatif">
            <label class="label-justificatif" for="nb-justificatif">Nombre de justificatifs : </label>
            <input type="text" id="nb-justificatif" 
                name="nbJustificatif"
                size="10" maxlength="5" 
                value="" 
                class="form-control">
            <button class="btn btn-success" type="submit">Valider les justificatifs</button>
        </div>
    </form>
    <form action="index.php?uc=validerFrais&action=validerFiche" 
          method="post" role="form" class="form-group">
          <button class="btn btn-success" type="submit">Valider la fiche</button>
    </form>
<?php } ?>
