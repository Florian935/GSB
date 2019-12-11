<?php
/**
 * Vue Liste des frais au forfait
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
<div class="row">
    <h2>
    <?php if ($typeUtilisateur == 'visiteur') { ?> Renseigner ma fiche de frais du mois 
    <?php echo $numMois . '-' . $numAnnee; } else { ?> Valider la fiche de frais de 
    <?php echo htmlspecialchars($nomEtPrenomVisiteur['nom']) . ' '
               . htmlspecialchars($nomEtPrenomVisiteur['prenom']); } ?> 
    </h2>
    <h3>Eléments forfaitisés</h3>
    <?php if (isset($estMajFraisForfait) && $estMajFraisForfait) { ?>
        <p>Les Modifications ont été prises en compte.</p>
    <?php } ?>
    <div class="col-md-4">
        <form method="post" 
              action="index.php?uc=gererFrais&action=validerMajFraisForfait" 
              role="form"
              class="form-group">
            <fieldset>       
                <?php
                foreach ($lesFraisForfait as $unFrais) {
                    $idFrais = htmlspecialchars($unFrais['idfrais']);
                    $libelle = htmlspecialchars($unFrais['libelle']);
                    $quantite = htmlspecialchars($unFrais['quantite']); ?>
                    <div class="form-group">
                        <label for="idFrais"><?php echo $libelle ?></label>
                        <input type="text" id="idFrais" 
                               name="lesFrais[<?php echo $idFrais ?>]"
                               size="10" maxlength="5" 
                               value="<?php echo $quantite ?>" 
                               class="form-control">
                    </div>
                    <?php
                }
                ?>
                <button class="btn btn-success" type="submit">
                <?php if ($typeUtilisateur == 'visiteur') { ?> Ajouter
                <?php } else { ?> Corriger <?php } ?>
                </button>
                <button class="btn btn-danger" type="reset">
                <?php if ($typeUtilisateur == 'visiteur') { ?> Effacer
                <?php } else { ?> Réinitialiser <?php } ?>
                </button>
            </fieldset>
        </form>
    </div>
</div>
