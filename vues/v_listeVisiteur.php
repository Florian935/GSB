<?php
/**
 * Vue Liste des visiteurs 
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

if ($typeUtilisateur == 'comptable') { ?>
    <form action="index.php?uc=validerFrais&action=afficherFrais" 
              method="post" role="form" class="choix-comptable">
        <div class="choix-fiche">
            <label class="label-visiteur" for="lstVisiteur" accesskey="n">Choisir le visiteur : </label>
            <select id="lstVisiteur" name="lstVisiteur" class="form-control">
            <?php 
                    foreach ($lesVisiteurs as $unVisiteur) { 
                        $id = htmlspecialchars($unVisiteur['id']);
                        $nom = htmlspecialchars($unVisiteur['nom']);
                        $prenom = htmlspecialchars($unVisiteur['prenom']);
                        if ($id == $_SESSION['idVisiteurSelectionne']) {
                            ?>
                            <option selected value = "<?php echo $id ?>">
                                <?php echo $nom . ' ' . $prenom ?> </option>
                            <?php
                        } else {
                            ?>
                        <option value="<?php echo $id ?>">
                            <?php echo $nom . ' ' . $prenom ?> </option>
                        <?php 
                        }
                    } 
                    ?>
            </select>
        </div>
        <div class="choix-fiche">
            <label class="label-mois" for="lstMois" accesskey="n">Mois : </label>
            <select id="lstMois" name="lstMois" class="form-control">
            <?php
                    foreach ($lesMois as $unMois) {
                        $mois = $unMois['mois'];
                        $numAnnee = $unMois['numAnnee'];
                        $numMois = $unMois['numMois'];
                        if ($mois == $_SESSION['moisSelectionne']) {
                            ?>
                            <option selected value="<?php echo $mois ?>">
                                <?php echo $numMois . '/' . $numAnnee ?> </option>
                            <?php
                        } else {
                            ?>
                            <option value="<?php echo $mois ?>">
                                <?php echo $numMois . '/' . $numAnnee ?> </option>
                            <?php
                        }
                    }
                    ?>    
            </select>
        </div>
        <input id="ok" type="submit" value="Valider" class="btn btn-success" 
                   role="button">
    </form>
<?php } ?>