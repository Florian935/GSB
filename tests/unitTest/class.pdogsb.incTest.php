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
    private static $_pdoGsb; // contient l'unique objet PDO de la classe PdoGsb
    private static $_monPdoGsb; // unique instance de la classe PdoGsb

    /** 
     * Appel automatique par phpUnit de la méthode setUpBeforeClass 
     * qui permet l'initialisation, en remplacement du constructor
     *
     * @return void
     */
    static function setUpBeforeClass() : void
    {
        PdoGsbTest::$_monPdoGsb = PdoGsb::getPdoGsb('PdoGsbTest');
        PdoGsbTest::$_pdoGsb = PdoGsb::getMonPdo();


        /* Initialisation de toutes les requêtes d'insertion et de suppression
         * sur la BDD de test afin de pouvoir effectuer des tests.
         */
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'INSERT INTO fichefrais (idvisiteur, mois, nbjustificatifs, idetat) '
            . "VALUES ('a131', '202505', 7, 'CR')"
        );
        $requetePrepare->execute();

        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
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
     * Traitements effectués lorsque tous les tests sont 
     * terminés.
     * Ces traitements sont nécessaires afin de retrouver la 
     * BDD de test initial et ainsi avoir la BDD dans un état 
     * cohérent.
     * 
     * @return void
     */
    public static function tearDownAfterClass() : void
    { 
        /* Suppression des frais forfaits qui ont été ajoutés
         * lors du début des tests
         */
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'DELETE FROM lignefraisforfait '
            . "WHERE idvisiteur = 'a131' AND mois IN ('202505', '202506')"
        );
        $requetePrepare->execute();

        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'DELETE FROM lignefraishorsforfait '
            . "WHERE idvisiteur = 'a131' AND mois ='202505'"
        );
        $requetePrepare->execute();

        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'UPDATE fichefrais '
            . "SET idetat = 'CL', "
            . 'idcomptable = null '
            . "WHERE idvisiteur = 'a131' AND mois ='201909'"
        );
        $requetePrepare->execute();

        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'UPDATE fichefrais '
            . "SET montant = 3457.98 "
            . "WHERE idvisiteur = 'a131' AND mois ='201910'"
        );
        $requetePrepare->execute();

        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'DELETE FROM fichefrais '
            . "WHERE idvisiteur = 'a131' AND mois IN ('202505', '202506')"
        );
        $requetePrepare->execute();
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

    /**
     * Teste que la fonction majFraisForfait met bien à jour la ligne de frais
     * forfait pour un visiteur et un mois donné
     * 
     * @return null
     */
    public function testMajFraisForfaitMetAJourLesFrais()
    {
        // Simulation des frais entrés par l'utilisateur
        $lesFrais = array(
            'ETP' => '14',
            'KM' => '121',
            'NUI' => '7',
            'REP' => '6'
        );
        PdoGsbTest::$_monPdoGsb->majFraisForfait('a131', '202505', $lesFrais);

        /* Requête permettant de selectionner les valeurs qui ont été
         * insérées.
         */
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT lignefraisforfait.idfraisforfait AS id, '
            . 'lignefraisforfait.quantite AS qte '
            . 'FROM lignefraisforfait '
            . " WHERE idvisiteur = 'a131' AND mois = '202505'"
        );
        $requetePrepare->execute();
        $lesLignesRetournes = $requetePrepare->fetchAll();
        $lesFraisRetournes = array();
        // Création du tableau qui est retourné par la requête
        foreach ($lesLignesRetournes as $unFrais) {
            $lesFraisRetournes[] = array(
                $unFrais['id'] => $unFrais['qte'],
            );
        }

        // Tableau attendu qui doit être retourné par la requête précédente
        $lesFraisAttendus = array(
            0 => array(
                'ETP' => '14',
            ),
            1 => array(
                'KM' => '121',
            ),
            2 => array(
                'NUI' => '7',
            ),
            3 => array(
                'REP' => '6'
            )
        );

        $this->assertEquals($lesFraisAttendus, $lesFraisRetournes);   
    }

    /**
     * Teste que la fonction majNbJustificatifs met bien à jour le nombre de
     * justificatifs sur une fiche de frais pour un visiteur et un mois donné
     * 
     * @return null
     */
    public function testMajNbJustificatifsMetAJourLeNbDeJustificatifs()
    {
        PdoGsbTest::$_monPdoGsb->majNbJustificatifs('a131', '202505', 20);
        $lesInfosFicheFrais = PdoGsbTest::$_monPdoGsb->getLesInfosFicheFrais(
            'a131', 
            '202505'
        );
        // On récupère le nombre de justificatifs du visiteur
        $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
        
        $this->assertEquals($nbJustificatifs, 20);
    }

    /**
     * Teste que la fonction creeNouvellesLignesFrais créée une nouvelle fiche
     * de frais pour un visiteur et un mois donné
     * 
     * @return null
     */
    public function testCreeNouvellesLignesFraisRetourneNouvelleFicheFrais()
    {
        // Création de la nouvelle fiche de frais
        PdoGsbTest::$_monPdoGsb->creeNouvellesLignesFrais('a131', '202506');

        // On récupère la nouvelle fiche de frais créée
        $ficheFraisRetournee = PdoGsbTest::$_monPdoGsb->getLesInfosFicheFrais(
            'a131', 
            '202506'
        );

        /* On créé un tableau contenant le résultat attendu de la fiche de frais 
         * créée
         */
        $ficheFraisAttendue = array(
            'idEtat' => 'CR',
            'dateModif' => date("Y-m-d"),
            'nbJustificatifs' => '0',
            'montantValide' => '0.00',
            'libEtat' => 'Fiche créée, saisie en cours',
        );

        /* On vérifie que la fiche de frais créée est identique à la fiche de frais
         * attendue dans la variable ficheFraisAttendue
         */
        $this->assertEquals($ficheFraisAttendue, $ficheFraisRetournee);
    }

    /**
     * Teste que la fonction creeNouvellesLignesFrais met bien à l'état
     * clôturé (idEtat = 'CL') la fiche de frais du mois précédent
     * 
     * @return null
     */
    public function testCreeNouvellesLignesFraisEtatFicheFraisPrecedenteCloturee()
    {
        /* Récupération de l'état de la fiche de frais qui précède la fiche de 
         * frais la plus récente
         */
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT fichefrais.idetat AS idetat '
            . 'FROM fichefrais '
            . 'WHERE mois = (SELECT MAX(mois) '
            .               'FROM fichefrais '
            .               'WHERE mois <> '
            .                     '(SELECT MAX(mois) '
            .                     'FROM fichefrais'
            .                     ')'
            .                ')'
        );
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch(PDO::FETCH_ASSOC);
        $idEtatRetourne = $laLigne['idetat'];

        $this->assertEquals('CL', $idEtatRetourne);
    }

    /**
     * Teste que la fonction creeNouvellesLignesFrais créée bien des nouvelles
     * lignes de frais forfait pour le mois et le visiteur concerné
     * 
     * @return null
     */
    public function testCreeNouvellesLignesFraisNouvellesLigneFraisForfaitCreee()
    {
        // Lignes de frais forfaits retournées
        $fraisForfaitsRetournes = PdoGsbTest::$_monPdoGsb->getLesFraisForfait(
            'a131', 
            '202506'
        );

        // Tableau attendus contenant les lignes de frais forfaits créées
        $fraisForfaitsAttendus = array(
            0 => array(
                'idfrais' => 'ETP',
                0 => 'ETP',
                'libelle' => 'Forfait Etape',
                1 => 'Forfait Etape',
                'quantite' => '0',
                2 => '0'
            ),
            1 => array(
                'idfrais' => 'KM',
                0 => 'KM',
                'libelle' => 'Frais Kilométrique',
                1 => 'Frais Kilométrique',
                'quantite' => '0',
                2 => '0'
            ),
            2 => array(
                'idfrais' => 'NUI',
                0 => 'NUI',
                'libelle' => 'Nuitée Hôtel',
                1 => 'Nuitée Hôtel',
                'quantite' => '0',
                2 => '0'
            ),
            3 => array(
                'idfrais' => 'REP',
                0 => 'REP',
                'libelle' => 'Repas Restaurant',
                1 => 'Repas Restaurant',
                'quantite' => '0',
                2 => '0'
            )
        );

        $this->assertEquals($fraisForfaitsAttendus, $fraisForfaitsRetournes);
    }

    /**
     * Teste que la fonction creeNouveauFraisHorsForfait créée bien une nouvelle
     * ligne de frais hors forfait
     * 
     * @return null
     */
    public function testCreeNouveauFraisHorsForfaitCreationDuFraisHorsForfait() 
    {
        PdoGsbTest::$_monPdoGsb->creeNouveauFraisHorsForfait(
            'a131', 
            '202505', 
            'Avion', 
            '10/05/2025', 
            65
        );

        $fraisHorsForfaitsRetournes = PdoGsbTest::$_monPdoGsb->
        getLesFraisHorsForfait(
            'a131', 
            '202505'
        );

        // Selection du dernier id du frais hors forfait venant d'être ajouté
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT MAX(id) as dernierId '
            . 'FROM lignefraishorsforfait '
            . "WHERE idvisiteur = 'a131' AND mois = '202505'"
        );
        $requetePrepare->execute();
        $idFraisHorsForfait = $requetePrepare->fetch(PDO::FETCH_ASSOC);

        $fraisHorsForfaitsAttendus = array(
            0 => array(
                'id' => $idFraisHorsForfait['dernierId'],
                0 => $idFraisHorsForfait['dernierId'],
                'idvisiteur' => 'a131',
                1 => 'a131',
                'mois' => '202505',
                2 => '202505',
                'libelle' => 'Avion',
                3 => 'Avion',
                'date' => '10/05/2025',
                4 => '2025-05-10',
                'montant' => '65.00',
                5 => '65.00'
            ),
        );
        $this->assertEquals($fraisHorsForfaitsAttendus, $fraisHorsForfaitsRetournes);
    }

    /**
     * Teste que la fonction supprimerFraisHorsForfait supprime bien la
     * ligne de frais hors forfait
     * 
     * @return null
     */
    public function testSupprimerFraisHorsForfaitSupprimeLeFraisHorsForfait()
    {
        /* Selection du dernier id du frais hors forfait ayant été ajouté dans
         * le test précédent
         */        
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT MAX(id) as dernierId '
            . 'FROM lignefraishorsforfait '
            . "WHERE idvisiteur = 'a131' AND mois = '202505'"
        );
        $requetePrepare->execute();
        $idFraisHorsForfait = $requetePrepare->fetch(PDO::FETCH_ASSOC);
        PdoGsbTest::$_monPdoGsb->supprimerFraisHorsForfait(
            $idFraisHorsForfait['dernierId']
        );

        /* Récupération de l'id de la ligne de frais hors forfait ajoutée dans le 
         * test précédent et qui vient d'être supprimé dans ce test. Le résultat 
         * attendue de la requête doit donc être null.
         */
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT id '
            . 'FROM lignefraishorsforfait '
            . "WHERE id = :unIdFrais"
        );
        $requetePrepare->bindParam(
            ':unIdFrais', 
            $idFraisHorsForfait['dernierId'], 
            PDO::PARAM_STR
        );
        $requetePrepare->execute();
        // Retourne un résultat vide
        $fraisHorsForfaitSupprime = $requetePrepare->fetch();


        $this->assertEquals(null, $fraisHorsForfaitSupprime);
    }

    /**
     * Teste que la fonction getLesMoisFicheClotureVisiteur retourne bien les
     * fiches de frais à l'état clôturée pour un visiteur donné
     * 
     * @return null
     */
    public function testGetLesMoisFicheClotureVisiteurRetourneLesFichesCloturees()
    {
        $ficheFraisRetournees = PdoGsbTest::$_monPdoGsb->
        getLesMoisFicheClotureVisiteur('a131');

        // Résultat attendu
        $ficheFraisAttendues = array(
            0 => array(
                'mois' => '202505',
                'numAnnee' => '2025',
                'numMois' => '05'
            ),
            1 => array(
                'mois' => '201912',
                'numAnnee' => '2019',
                'numMois' => '12'
            ),
            2 => array(
                'mois' => '201911',
                'numAnnee' => '2019',
                'numMois' => '11'
            ),
            3 => array(
                'mois' => '201910',
                'numAnnee' => '2019',
                'numMois' => '10'
            ),
            4 => array(
                'mois' => '201909',
                'numAnnee' => '2019',
                'numMois' => '09'
            )
        );

        $this->assertEquals($ficheFraisAttendues, $ficheFraisRetournees);
    }

    /**
     * Teste que la fonction getLesInfosFicheFrais retourne bien les
     * informations de la fiche de frais pour un visiteur et un
     * mois donné
     * 
     * @return null
     */
    public function testGetLesInfosFicheFraisRetourneLesInformations()
    {
        $infosFicheFraisRetournees = PdoGsbTest::$_monPdoGsb->getLesInfosFicheFrais(
            'a131', 
            '201705'
        );

        $infosFicheFraisAttendues = array(
            'idEtat' => 'RB',
            'dateModif' => '2017-07-01',
            'nbJustificatifs' => '0',
            'montantValide' => '4340.88',
            'libEtat' => 'Remboursée'
        );

        $this->assertEquals($infosFicheFraisAttendues, $infosFicheFraisRetournees);
    }

    /**
     * Teste que la fonction majEtatFicheFrais met bien à jour
     * l'état d'une fiche en fonction du visiteur, du mois et de 
     * l'état passés en paramètre
     * 
     * @return null
     */
    public function testMajEtatFicheFraisMetAJourLetatDeLaFiche()
    {
        PdoGsbTest::$_monPdoGsb->majEtatFicheFrais('a131', '202505', 'RB');

        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT idetat '
            . 'FROM fichefrais '
            . "WHERE idvisiteur = 'a131' AND mois = '202505'"
        );
        $requetePrepare->execute();
        $resultatRequete = $requetePrepare->fetch(PDO::FETCH_ASSOC);
        $idEtatRetourne = $resultatRequete['idetat'];

        $idEtatAttendu = 'RB';

        $this->assertEquals($idEtatAttendu, $idEtatRetourne);
    }

    /**
     * Teste que la fonction majFraisHorsForfait met bien à jour
     * une ligne de frais hors forfait
     * 
     * @return null
     */
    public function testMajFraisHorsForfaitRetourneLeFraisHorsForfaitMaj()
    {
        // Création d'un nouveau frais hors forfait pour le test
        PdoGsbTest::$_monPdoGsb->creeNouveauFraisHorsForfait(
            'a131', 
            '202505', 
            'Billet de train', 
            '10/05/2025', 
            65
        );

        /* Selection du dernier id du frais hors forfait venant d'être ajouté
         */        
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT MAX(id) as dernierId '
            . 'FROM lignefraishorsforfait '
            . "WHERE idvisiteur = 'a131' AND mois = '202505'"
        );
        $requetePrepare->execute();
        $idFraisHorsForfait = $requetePrepare->fetch(PDO::FETCH_ASSOC);
        $dernierIdFraisHorsForfait = $idFraisHorsForfait['dernierId'];

        // Mise à jour du frais hors forfait créé en début de test
        PdoGsbTest::$_monPdoGsb->majFraisHorsForfait(
            $dernierIdFraisHorsForfait,
            'a131',
            '202505',
            'Conférence',
            '10/05/2025',
            70
        );

        // Récupération des informations de la ligne de frais hors forfait maj
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT * '
            . 'FROM lignefraishorsforfait '
            . 'WHERE id = :unIdFrais'
        );
        $requetePrepare->bindParam(
            ':unIdFrais', 
            $dernierIdFraisHorsForfait,
            PDO::PARAM_INT
        );
        $requetePrepare->execute();
        $fraisHorsForfaitRetourne = $requetePrepare->fetch(PDO::FETCH_ASSOC);

        $fraisHorsForfaitAttendu = array(
            'id' => $dernierIdFraisHorsForfait,
            'idvisiteur' => 'a131',
            'mois' => '202505',
            'libelle' => 'Conférence',
            'date' => '2025-05-10',
            'montant' => '70.00'
        );

        $this->assertEquals($fraisHorsForfaitAttendu, $fraisHorsForfaitRetourne);
    }

    /**
     * Teste que la fonction validerLaFiche met bien à jour
     * l'état d'une fiche de frais à l'état 'validée'
     * 
     * @return null
     */
    public function testValiderLaFicheMajDeLetat()
    {
        // Passage de l'état de la fiche à 'validée'
        PdoGsbTest::$_monPdoGsb->validerLaFiche('a131', 'c001', '201909');

        // Récupération de l'état de la fiche mise à jour
        $requetePrepare = PdoGsbTest::$_pdoGsb->prepare(
            'SELECT idetat '
            . 'FROM fichefrais '
            . "WHERE idvisiteur = 'a131' AND mois = '201909'"
        );
        $requetePrepare->execute();
        $resultatRequete = $requetePrepare->fetch(PDO::FETCH_ASSOC);
        $idEtatRetourne = $resultatRequete['idetat'];

        // Résultat attendu de l'idetat de la fiche: 'VA'
        $idEtatAttendu = 'VA';

        $this->assertEquals($idEtatAttendu, $idEtatRetourne);
    }

    /**
     * Teste que la fonction getMontantValideHorsFraisRefuses retourne
     * le bon montant validé d'une fiche sans compter les frais refusés
     * 
     * @return null
     */
    public function testGetMontantValideHorsFraisRefusesRetourneLeBonMontant()
    {
        $montantValideRetourne = PdoGsbTest::$_monPdoGsb->
        getMontantValideHorsFraisRefuses('a131', '201910');

        $montantValideAttendu = 4137.24;

        $this->assertEquals($montantValideAttendu, $montantValideRetourne);
    }

    /**
     * Teste que la fonction majMontantValide met à jour
     * le montant validé d'une fiche
     * 
     * @return null
     */
    public function testMajMontantValideMetAJourLeMontantValide()
    {
        PdoGsbTest::$_monPdoGsb->majMontantValide('a131', '201910', 1000);

        $lesInfosFicheFrais = PdoGsbTest::$_monPdoGsb->getLesInfosFicheFrais(
            'a131', 
            '201910'
        );
        $montantValideMajRetourne = $lesInfosFicheFrais['montantValide'];

        $montantValideMajAttendu = 1000;

        $this->assertEquals($montantValideMajAttendu, $montantValideMajRetourne);
    }
}

