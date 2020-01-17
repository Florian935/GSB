<?php

use PdoGsb\PdoGsb;
use PDO;
require_once 'includes/fct.inc.php';

/**
 * Classe de tests de la classe d'accès aux données.
 *
 * Utilisation de la classe phpUnit afin de pouvoir effectuer
 * des tests sur les fonctions de la classe d'accès aux données
 * class.pdogsb.inc. La création d'une BDD test a été effectuée afin
 * de pouvoir effectuer des tests de modification des données (update, delete,
 *  create) sur cette dernière. La BDD test est une réplique conforme de la 
 * véritable BDD.
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
    private static $_pdoGsbTest; // contient l'objet PDO de la classe PdoGsbTest
    private static $_pdoGsb; // contient l'unique objet PDO de la classe PdoGsb
    private static $_monPdoGsb; // unique instance de la classe PdoGsb
    private static $_monPdoGsbTest; // unique instance de la classe PdoGsbTest

    /** 
     * Appel automatique par phpUnit de la méthode setUpBeforeClass 
     * qui permet l'initialisation, en remplacement du constructor
     *
     * @return void
     */
    static function setUpBeforeClass() : void
    {
        PdoGsbTest::$_pdoGsbTest = new PDO(
            PdoGsbTest::$_serveur . ';' . PdoGsbTest::$_bdd,
            PdoGsbTest::$_user,
            PdoGsbTest::$_mdp
        );
        PdoGsbTest::$_pdoGsbTest->query('SET CHARACTER SET utf8');
        PdoGsbTest::$_monPdoGsb = PdoGsb::getPdoGsb();
        PdoGsbTest::$_pdoGsb = PdoGsb::getMonPdo();


        /* Initialisation de toutes les requêtes d'insertion et de suppression
         * sur la BDD de test afin de pouvoir effectuer des tests.
         */
        $requetePrepare = PdoGsbTest::$_pdoGsbTest->prepare(
            'INSERT INTO fichefrais (idvisiteur, mois) '
            . "VALUES ('a131', '202505')"
        );
        $requetePrepare->execute();

        $requetePrepare = PdoGsbTest::$_pdoGsbTest->prepare(
            'INSERT INTO lignefraisforfait (idvisiteur, mois, '
            . 'idfraisforfait, quantite) '
            . "VALUES ('a131', '202505', 'ETP', '5'), "
            . "('a131', '202505', 'KM', '50'), "
            . "('a131', '202505', 'NUI', '6'), "
            . "('a131', '202505', 'REP', '2')"
        );
        $requetePrepare->execute();
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
     * Teste que la fonction getInfosComptable retourne l'id du comptable
     * associé au login et au mdp fourni en paramètre.
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
     * Teste que la fonction getInfosComptable retourne le nom du comptable
     * associé au login et au mdp fourni en paramètre.
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
     * Teste que la fonction getInfosComptable retourne le prénom du comptable
     * associé au login et au mdp fourni en paramètre.
     * 
     * @return null
     */
    public function testGetInfosComptablePrenomCorrect()
    {
        $comptable = PdoGsbTest::$_monPdoGsb->getInfosComptable('fgoudet', 'bcjh7');
        $prenom = $comptable['prenom'];
        $this->assertEquals('Françoise', $prenom);
    }

    /**
     * Teste que la fonction getInfosComptable retourne null si le mdp fourni par le 
     * comptable, après cryptage, ne correspond pas à celui stocké dans la BDD
     * 
     * @return null
     */
    public function testGetInfosComptableMdpIncorrect()
    {
        $comptable = PdoGsbTest::$_monPdoGsb->getInfosComptable('fgoudet', 'abcde');
        $this->assertEquals(null, $comptable);
    }

    /**
     * Teste que la fonction getInfosVisiteur retourne l'id du visiteur associé
     * au login et au mdp fourni en paramètre.
     * 
     * @return null
     */
    public function testGetInfosVisiteurIdCorrect()
    {
        $visiteur = PdoGsbTest::$_monPdoGsb->getInfosVisiteur('dandre', 'oppg5');
        $id = $visiteur['id'];
        $this->assertEquals('a17', $id);
    }

    /**
     * Teste que la fonction getInfosVisiteur retourne le nom du visiteur associé
     * au login et au mdp fourni en paramètre.
     * 
     * @return null
     */
    public function testGetInfosVisiteurNomCorrect()
    {
        $visiteur = PdoGsbTest::$_monPdoGsb->getInfosVisiteur('dandre', 'oppg5');
        $nom = $visiteur['nom'];
        $this->assertEquals('Andre', $nom);
    }

    /**
     * Teste que la fonction getInfosVisiteur retourne le prénom du visiteur associé
     * au login et au mdp fourni en paramètre.
     * 
     * @return null
     */
    public function testGetInfosVisiteurPrenomCorrect()
    {
        $visiteur = PdoGsbTest::$_monPdoGsb->getInfosVisiteur('dandre', 'oppg5');
        $prenom = $visiteur['prenom'];
        $this->assertEquals('David', $prenom);
    }

    /**
     * Teste que la fonction getInfosVisiteur retourne null si le mdp fourni par le 
     * comptable, après cryptage, ne correspond pas à celui stocké dans la BDD
     * 
     * @return null
     */
    public function testGetInfosVisiteurMdpIncorrect()
    {
        $visiteur = PdoGsbTest::$_monPdoGsb->getInfosVisiteur('dandre', 'abcde');
        $this->assertEquals(null, $visiteur);
    }

    /**
     * Teste que la fonction getNomEtPrenomVisiteur retourne le bon prénom associé
     * à l'id passé en paramètre
     * 
     * @return null
     */
    public function testGetNomEtPrenomVisiteurPrenomCorrect()
    {
        $visiteur = PdoGsbTest::$_monPdoGsb->getNomEtPrenomVisiteur('a17');
        $prenom = $visiteur['prenom'];
        $this->assertEquals('David', $prenom);
    }

    /**
     * Teste que la fonction getNomEtPrenomVisiteur retourne le bon nom associé
     * à l'id passé en paramètre
     * 
     * @return null
     */
    public function testGetNomEtPrenomVisiteurNomCorrect()
    {
        $visiteur = PdoGsbTest::$_monPdoGsb->getNomEtPrenomVisiteur('a17');
        $nom = $visiteur['nom'];
        $this->assertEquals('Andre', $nom);
    }

    /**
     * Teste que la fonction getNomEtPrenomVisiteur retourne null lorsque
     * l'id passé en paramètre n'est pas présent dans la table visiteur
     * 
     * @return null
     */
    public function testGetNomEtPrenomVisiteurIdIncorrect() 
    {
        $visiteur = PdoGsbTest::$_monPdoGsb->getNomEtPrenomVisiteur('a1');
        $this->assertEquals(null, $visiteur);
    }

    /**
     * Teste que la fonction getLesVisiteurs retourne un tableau associatif
     * contenant tous les visiteurs de la table visiteur
     * 
     * @return null
     */
    public function testGetLesVisiteursRetourneTousLesVisiteurs()
    {
        $testTousLesVisiteurs = PdoGsbTest::$_monPdoGsb->getLesVisiteurs();

        /* On selectionne tous les visiteurs et on stocke le résultat 
         * dans la variable $testsTousLesVisiteurs
        */
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT visiteur.id AS id, visiteur.nom AS nom, '
            . 'visiteur.prenom AS prenom '
            . 'FROM visiteur '
        );
        $requetePrepare->execute();
        $touslesVisiteurs = $requetePrepare->fetchAll();
        /* Comparaison des 2 arrays qui doivent contenir tous les deux 
         * l'ensemble des visiteurs
         */
        $this->assertEquals($touslesVisiteurs, $testTousLesVisiteurs);
    }

    /**
     * Teste que la fonction getLesFraisHorsForfait retourne un tableau associatif
     * contenant tous les frais hors forfaits pour un visiteur et un mois donné
     * 
     * @return null
     */
    public function testGetLesFraisHorsForfaitRetourneLesBonsFraisHorsForfaits()
    {
        $testTousLesFraisHorsForfait = PdoGsbTest::$_monPdoGsb->
        getLesFraisHorsForfait(
            'a17', 
            '201703'
        );
        /* On selectionne tous les frais hors forfaits et on stocke le résultat 
         * dans la variable $tousLesFraisHorsForfait
        */
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT * '
            . 'FROM lignefraishorsforfait '
            . "WHERE idvisiteur = 'a17' AND mois = '201703'"
        );
        $requetePrepare->execute();
        $tousLesFraisHorsForfait = $requetePrepare->fetchAll();
        /* Conversion des dates anglaises en dates française pour permettre
         * la comparaison
         */
        for ($i = 0; $i < count($tousLesFraisHorsForfait); $i++) {
            $date = $tousLesFraisHorsForfait[$i]['date'];
            $tousLesFraisHorsForfait[$i]['date'] = dateAnglaisVersFrancais($date);
        }
        /* Comparaison des 2 arrays qui doivent contenir tous les deux 
         * l'ensemble des frais hors forfait pour le visiteur et le mois fourni
         */
        $this->assertEquals($tousLesFraisHorsForfait, $testTousLesFraisHorsForfait);
    }

    /**
     * Teste que la fonction getLesFraisForfait retourne un tableau associatif
     * contenant tous les frais hors forfaits pour un visiteur et un mois donné
     * 
     * @return null
     */
    public function testGetLesFraisForfaitRetourneLesBonsFraisForfait()
    {
        $testTousLesFraisForfait = PdoGsbTest::$_monPdoGsb->
        getLesFraisForfait(
            'a17', 
            '201705'
        );
        /* On selectionne tous les frais forfaits et on stocke le résultat 
         * dans la variable $tousLesFraisForfait
        */
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT fraisforfait.id as idfrais, '
            . 'fraisforfait.libelle as libelle, '
            . 'lignefraisforfait.quantite as quantite '
            . 'FROM lignefraisforfait '
            . 'INNER JOIN fraisforfait '
            . 'ON fraisforfait.id = lignefraisforfait.idfraisforfait '
            . "WHERE lignefraisforfait.idvisiteur = 'a17' "
            . "AND lignefraisforfait.mois = '201705' "
            . 'ORDER BY lignefraisforfait.idfraisforfait'
        );
        $requetePrepare->execute();
        $tousLesFraisForfait = $requetePrepare->fetchAll();
        /* Comparaison des 2 arrays qui doivent contenir tous les deux 
         * l'ensemble des frais hors forfait pour le visiteur et le mois fourni
         */
        $this->assertEquals($tousLesFraisForfait, $testTousLesFraisForfait);
    }

    /**
     * Teste que la fonction getNbjustificatifs retourne le bon nombre de 
     * justificatifs fourni à la fiche de frais pour un visiteur et un mois donné 
     * 
     * @return null
     */
    public function testGetNbjustificatifsNombreCorrect()
    {
        $nbJustificatifs = PdoGsbTest::$_monPdoGsb->getNbJustificatifs(
            'a17', 
            '201705'
        );
        $this->assertEquals(4, $nbJustificatifs);
    }

    /**
     * Teste que la fonction estPremierFraisMois retourne false lorsque
     * le visiteur passsé en paramètre pour un mois donné possède déjà
     * des frais et donc ce ne sont pas les premiers frais du mois.
     * 
     * @return null
     */
    public function testEstPremierFraisMoisFraisExistant()
    {
        $possedeFiche = PdoGsbTest::$_monPdoGsb->estPremierFraisMois(
            'a17', 
            "201704"
        );
        $this->assertEquals(false, $possedeFiche);
    }

    /**
     * Teste que la fonction estPremierFraisMois retourne true lorsque
     * le visiteur passsé en paramètre pour un mois donné ne possède pas
     * de frais et donc ce sont les premiers frais du mois.
     * 
     * @return null
     */
    public function testEstPremierFraisMoisFraisNonExistant()
    {
        $possedeFiche = PdoGsbTest::$_monPdoGsb->estPremierFraisMois(
            'a17', 
            "202501"
        );
        $this->assertEquals(true, $possedeFiche);
    }

    /** 
     * Teste que la fonction dernierMoisSaisi retourne bien le dernier mois
     * en cours d'un visiteur 
     * 
     * @return null
     */
    public function testDernierMoisSaisiRetourneDernierMois()
    {
        $testDernierMois = PdoGsbTest::$_monPdoGsb->dernierMoisSaisi('a17');

        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT MAX(mois) as dernierMois '
            . 'FROM fichefrais '
            . "WHERE fichefrais.idvisiteur = 'a17'"
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        $dernierMois = $laLigne['dernierMois'];

        // On compare les 2 résultats obtenus qui doivent être identiques
        $this->assertEquals($dernierMois, $testDernierMois);
    }

    
}

