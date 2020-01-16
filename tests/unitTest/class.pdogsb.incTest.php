<?php

use PdoGsb\PdoGsb;
use PDO;

/**
 * Classe de tests de la classe d'accès aux données.
 *
 * Utilisation de la classe phpUnit afin de pouvoir effectuer
 * des tests sur les fonctions de la classe d'accès aux données
 * class.pdogsb.inc. La création d'une BDD test a été effectuée afin
 * de pouvoir effectuer les tests sur cette dernière. La BDD test
 * est une réplique conforme de la véritable BDD.
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

class PdoGsbTest extends PHPUnit\Framework\TestCase
{
    private static $_serveur = 'mysql:host=localhost';
    private static $_bdd = 'dbname=id11601272_appligsb_test';
    private static $_user = 'id11601272_usergsb';
    private static $_mdp = 'PPEappliBDD97531';
    private static $_monPdo;
    private static $_monPdoGsb;
    private static $_monPdoGsbTest;

    /** 
     * Appel automatique par phpUnit de la méthode setUpBeforeClass 
     * qui permet l'initialisation, en remplacement du constructor
     *
     * @return void
     */
    static function setUpBeforeClass() : void
    {
        PdoGsbTest::$_monPdo = new PDO(
            PdoGsbTest::$_serveur . ';' . PdoGsbTest::$_bdd,
            PdoGsbTest::$_user,
            PdoGsbTest::$_mdp
        );
        PdoGsbTest::$_monPdo->query('SET CHARACTER SET utf8');
        PdoGsbTest::$_monPdoGsb = PdoGsb::getPdoGsb();
    }

    /**
     * Méthode appelée par phpUnit avant l'execution de chaque tests définis
     */
    function setUp() : void
    {
        if (PdoGsbTest::$_monPdoGsb == null) {
            PdoGsbTest::$_monPdoGsb = new PdoGsbTest();
        }
    }

    /**
     * Test que l'id retourné par la fonction getInfosComptable est bien correct
     * 
     * @return null
     */
    public function testGetInfosComptableIdCorrect()
    {
        $comptable = PdoGsbTest::$_monPdoGsb->getInfosComptable('fgoudet', 'bcjh7');
        $id = $comptable['id'];
        $this->assertEquals('c001', $id);
    }

    /**
     * Test que le nom retourné par la fonction getInfosComptable est bien correct
     * 
     * @return null
     */
    public function testGetInfosComptableNomCorrect()
    {
        $comptable = PdoGsbTest::$_monPdoGsb->getInfosComptable('fgoudet', 'bcjh7');
        $nom = $comptable['nom'];
        $this->assertEquals('Goudet', $nom);
    }

    /**
     * Test que le prénom retourné par la fonction getInfosComptable est bien correct
     * 
     * @return null
     */
    public function testGetInfosComptablePrenomCorrect()
    {
        $comptable = PdoGsbTest::$_monPdoGsb->getInfosComptable('fgoudet', 'bcjh7');
        $prenom = $comptable['prenom'];
        $this->assertEquals('Françoise', $prenom);
    }
}

