<?php
require_once 'includes/fct.inc.php';

/**
 * Classe de tests de la classe contenant les fonctions utilisables par l'appli.
 *
 * Utilisation de la classe phpUnit afin de pouvoir effectuer
 * des tests sur le fichier fct.inc.php contenant les fonctions de l'application. 
 * La création d'une BDD test a été effectuée afin de pouvoir effectuer des tests
 * de modification des données (update, delete, create) sur cette dernière. La 
 * BDD test est une réplique conforme de la véritable BDD.
 * 
 * 
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Florian MARTIN <florian.martin63000@gmail.com>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   Release: 1.0
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

class FctIncTest extends PHPUnit\Framework\TestCase
{

    /**
     * Teste que la fonction typeUtilisateur retourne le type de 
     * l'utilisateur stockée la variable de session typeUtilisateur
     * 
     * @return null
     */
    public function testTypeUtilisateurRetourneLeType() 
    {
        $_SESSION['typeUtilisateur'] = 'Visiteur';
        $typeUtilisateur = typeUtilisateur();
        $this->assertEquals('Visiteur', $typeUtilisateur);
    }
    
    /**
     * Teste que la fonction typeUtilisateur retourne null si
     * la variable de session typeUtilisateur n'est pas initialisée
     * 
     * @return null
     */
    public function testTypeUtilisateurRetourneNullType() 
    {
        $_SESSION['typeUtilisateur'] = null;
        $typeUtilisateur = typeUtilisateur();
        $this->assertEquals(null, $typeUtilisateur);
    }

    /**
     * Test que la fonction estConnecte retourne true lorsque
     * la variable de session idUtilisateur est valorisée
     * 
     * @return null
     */
    public function testEstConnecteRetourneTrue()
    {
        $_SESSION['idUtilisateur'] = 'a17';
        $estConnecte = estConnecte();

        $this->assertEquals(true, $estConnecte);
    }

    /**
     * Test que la fonction estConnecte retourne false lorsque
     * la variable de session idUtilisateur n'est pas valorisée
     * 
     * @return null
     */
    public function testEstConnecteRetourneFalse()
    {
        $_SESSION['idUtilisateur'] = null;
        $estConnecte = estConnecte();

        $this->assertEquals(false, $estConnecte);
    }

    /**
     * Test que la fonction connecter enregistre dans une variable
     * session les infos de l'utilisateur
     * 
     * @return null
     */
    public function testConnecterEnregistreInfos()
    {
        $_SESSION['idUtilisateur'] = 'a17';
        $_SESSION['nom'] = 'Andre';
        $_SESSION['prenom'] = 'David';
        $_SESSION['typeUtilisateur'] = 'visiteur';

        connecter('a17', 'Andre', 'David', 'visiteur');

        $this->assertEquals('a17', $_SESSION['idUtilisateur']);
        $this->assertEquals('Andre', $_SESSION['nom']);
        $this->assertEquals('David', $_SESSION['prenom']);
        $this->assertEquals('visiteur', $_SESSION['typeUtilisateur']);
    }

    /**
     * Test que la fonction setIdVisiteurEtMoisSelectionnes enregistre
     * dans une variable de session l'id du visiteur et le mois selectionné
     * 
     * @return null
     */
    public function testSetIdVisiteurEtMoisSelectionnesEnregistreLesInfos()
    {
        setIdVisiteurEtMoisSelectionnes('a17', '201901');

        $this->assertEquals('a17', $_SESSION['idVisiteurSelectionne']);
        $this->assertEquals('201901', $_SESSION['moisSelectionne']);
    }

    /**
     * Test que la fonction dateFrancaisVersAnglais transforme une date 
     * au format français jj/mm/aaaa vers le format anglais aaaa-mm-jj
     * 
     * @return null
     */
    public function testDateFrancaisVersAnglaisRetourneLeBonFormat()
    {
        $dateAnglais = dateFrancaisVersAnglais('01/01/2019');

        $this->assertEquals('2019-01-01', $dateAnglais);
    }

    /**
     * Test que la fonction dateAnglaisVersFrancais transforme une date
     * au format anglais aaaa-mm-jj vers le format français jj/mm/aaaa
     * 
     * @return null
     */
    public function testDateAnglaisVersFrancaisRetourneLeBonFormat()
    {
        $dateFrancais = dateAnglaisVersFrancais('2019-01-01');

        $this->assertEquals('01/01/2019', $dateFrancais);
    }

    /**
     * Test que la fonction getMois retourne le mois sous forme aaaamm
     * en fonction d'une date passée en paramètre au format jj/mm/aaaa
     * 
     * @return null
     */
    public function testGetMoisRetourneMoisAuFormatSouhaite()
    {
        $mois = getMois('02/01/2019');

        $this->assertEquals('201901', $mois);
    }

    /** 
     * Teste que la fonction estEntierPositif retourne vrai ou faux
     * en fonction de si on lui fourni un entier en paramètre 
     * positif ou négatif
     * 
     * @return null
     */
    public function testEstEntierPositifiRetourneTrue()
    {
        $estEntierPositif_Avec0 = estEntierPositif(0);
        $estEntierPositif_Avec32 = estEntierPositif(32);
        $estEntierPositif_AvecDecimal = estEntierPositif(2.9);
        $estEntierPositif_AvecNegatif = estEntierPositif(-2);

        $this->assertEquals(true, $estEntierPositif_Avec0);
        $this->assertEquals(true, $estEntierPositif_Avec32);
        $this->assertEquals(false, $estEntierPositif_AvecDecimal);
        $this->assertEquals(false, $estEntierPositif_AvecNegatif);
    }

    /**
     * Test que la fonction estTableauEntiers retourne vrai lorsqu'un
     * tableau est constitué que d'entiers positifs et false dans le cas
     * contraire
     * 
     * @return null
     */
    public function testEstTableauEntiersRetournTrue()
    {
        $tabEntiers = [0, 2, 6, 12];
        $tabEntiersAvecNegatif = [-2, 2, 6, 12];
        $tabEntiersAvecDecimal = [0, 2, 6.2, 12];
        $tabAvecNegatifEtDecimal = [-4, 2, 6.2, 12];
        $estTabEntiers = estTableauEntiers($tabEntiers);
        $nonTabEntiers_Negatif = estTableauEntiers($tabEntiersAvecNegatif);
        $nonTabEntiers_Decimal = estTableauEntiers($tabEntiersAvecDecimal);
        $nonTabEntiers_NegatifEtDecimal = estTableauEntiers(
            $tabAvecNegatifEtDecimal
        );

        $this->assertEquals(true, $estTabEntiers);
        $this->assertEquals(false, $nonTabEntiers_Negatif);
        $this->assertEquals(false, $nonTabEntiers_Decimal);
        $this->assertEquals(false, $nonTabEntiers_NegatifEtDecimal);
    }
}
