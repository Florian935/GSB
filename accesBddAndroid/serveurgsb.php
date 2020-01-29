<?php

header('Content-Type: application/json; charset=utf-8');

/**
 * Script php permettant à l'application Android d'accéder
 * à la BDD de l'application GSB.
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Florian MARTIN <florian.martin63000@gmail.com>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */

include "../includes/class.pdogsb.inc.php";
use PdoGsb\PdoGsb;

$pdo = PdoGsb::getPdoGsb('PdoGsb');

// contrôle de réception de paramètre
if (isset($_POST["operation"])) {

    // Récupération des informations du visiteur
    if ($_REQUEST["operation"]=="recupInfos") {

        try {
            print ("recupInfos%");
            // Récupération des données en post
            $lesdonnees = $_REQUEST["lesdonnees"];
            $donnee = json_decode($lesdonnees);
            $login = $donnee[0];
            $mdp = $donnee[1];

            $laRequete = "SELECT * FROM visiteur WHERE login ='"; 
            $laRequete.= $login ."' AND mdp ='" . $mdp . "'";

            // Execution et envoi de la requête
            $cnx = PdoGsb::getMonPdo('PdoGsb');
            $req = $cnx->prepare($laRequete);
            $req->execute();
            // Si la requête a réussi, on retourne les informations
            if ($ligne = $req->fetch(PDO::FETCH_ASSOC)) {
                print(json_encode($ligne));
            }

        }catch(PDOException $e){
            print "Erreur !%".$e->getMessage();
            die();
        }
    } elseif ($_REQUEST["operation"]=="getFraisForfait") {

        try{
            // récupération des données en post
            print("getFraisForfait%");
            $lesdonnees = $_REQUEST["lesdonnees"];
            $donnee = json_decode($lesdonnees);
            $idVisiteur = $donnee[0];
            $mois = $donnee[1];

            if ($pdo->estPremierFraisMois($idVisiteur, $mois)) {
                $pdo->creeNouvellesLignesFrais($idVisiteur, $mois);
            };

            // Préparation de la requête
            $laRequete = "SELECT * FROM lignefraisforfait WHERE idvisiteur ='";
            $laRequete .= $idVisiteur . "' AND mois = '" . $mois . "'";

            // Execution et envoi de la requête
            $cnx = PdoGsb::getMonPdo('PdoGsb');
            $req = $cnx->prepare($laRequete);
            $req->execute();
            $ligne = $req->fetchAll();
            print(json_encode($ligne));

        }catch(PDOException $e){
            print "Erreur !%".$e->getMessage();
            die();
        }
    } elseif ($_REQUEST["operation"]=="getFraisHF") {

        try{
            // récupération des données en post
            print("getFraisHF%");
            $lesdonnees = $_REQUEST["lesdonnees"];
            $donnee = json_decode($lesdonnees);
            $idVisiteur = $donnee[0];
            $mois = $donnee[1];

            if ($pdo->estPremierFraisMois($idVisiteur, $mois)) {
                $pdo->creeNouvellesLignesFrais($idVisiteur, $mois);
            };

            // Préparation de la requête
            $laRequete = "SELECT * FROM lignefraishorsforfait WHERE idvisiteur ='";
            $laRequete.= $idVisiteur . "' AND mois = '" . $mois . "'";

            // Execution et envoi de la requête
            $cnx = PdoGsb::getMonPdo('PdoGsb');
            $req = $cnx->prepare($laRequete);
            $req->execute();
            $ligne = $req->fetchAll(PDO::FETCH_ASSOC);

            print(json_encode(
                $ligne, 
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ));

        }catch(PDOException $e){
            print "Erreur !%".$e->getMessage();
            die();
        }
    } elseif ($_REQUEST["operation"]=="updateFraisForfait") {

        try{
            // récupération des données en post
            print("updateFraisForfait%");
            $lesdonnees = $_REQUEST["lesdonnees"];
            $donnee = json_decode($lesdonnees);
            $idVisiteur = $donnee[0];
            $mois = $donnee[1];
            $idFrais = $donnee[2];
            $quantite = $donnee[3];

            // Préparation de la requête
            $laRequete = "UPDATE lignefraisforfait SET quantite =" .$quantite; 
            $laRequete .= " WHERE mois = '" . $mois . "' AND idvisiteur = '" ;
            $laRequete .= $idVisiteur . "' AND idfraisforfait = '" . $idFrais . "'";

            // Execution et envoi de la requête
            $cnx = PdoGsb::getMonPdo('PdoGsb');
            $req = $cnx->prepare($laRequete);
            $req->execute();
            print(json_encode($laRequete));


        }catch(PDOException $e){
            print "Erreur !%".$e->getMessage();
            die();
        }
    } elseif ($_REQUEST["operation"]=="ajoutFraisHF") {

        try{
            // récupération des données en post
            print("ajoutFraisHF%");
            $lesdonnees = $_REQUEST["lesdonnees"];
            $donnee = json_decode($lesdonnees);
            $idVisiteur = $donnee[0];
            $mois = $donnee[1];
            $libelle = $donnee[2];
            $date = $donnee[3];
            $montant = $donnee[4];

            // Préparation de la requête
            $laRequete = "INSERT INTO lignefraishorsforfait (idvisiteur, ";
            $laRequete .= "mois, libelle, date, montant) ";
            $laRequete .= "VALUES ('".$idVisiteur."', '".$mois."', '".$libelle;
            $laRequete .= "', '".$date. "', ".$montant.")";

            // Execution et envoi de la requête
            $cnx = PdoGsb::getMonPdo('PdoGsb');
            $req = $cnx->prepare($laRequete);
            $req->execute();

            /* Dès que l'ajout est effectué, on récupère le dernier id du frais 
             * hors forfait de la table lignefraishorsforfait et on le retourne
             */
            $laRequete = "SELECT idMax, libelle, date, montant ";
            $laRequete .="FROM (SELECT MAX(id) AS 'idMax', libelle, date, montant ";
            $laRequete .= "FROM lignefraishorsforfait ";
            $laRequete .= "GROUP BY libelle, date, montant) AS req ";
            $laRequete .= "WHERE idMax = (SELECT MAX(id) ";
            $laRequete .= " FROM lignefraishorsforfait)";

            // Execution et envoi de la requête
            $cnx = PdoGsb::getMonPdo('PdoGsb');
            $req = $cnx->prepare($laRequete);
            $req->execute();
            $ligne = $req->fetch(PDO::FETCH_ASSOC);
            print(json_encode($ligne));


        }catch(PDOException $e){
            print "Erreur !%".$e->getMessage();
            die();
        }
    } elseif ($_REQUEST["operation"]=="suppressionFraisHF") {

        try{
            // récupération des données en post
            print("suppressionFraisHF%");
            $lesdonnees = $_REQUEST["lesdonnees"];
            $donnee = json_decode($lesdonnees);
            $idFraisHF = $donnee[0];

            $laRequete = "SELECT id FROM lignefraishorsforfait ";
            $laRequete .= "WHERE id = " . $idFraisHF;

            // Execution et envoi de la requête
            $cnx = PdoGsb::getMonPdo('PdoGsb');
            $req = $cnx->prepare($laRequete);
            $req->execute();
            $ligne = $req->fetch(PDO::FETCH_ASSOC);
            print(json_encode($ligne));

            // Préparation de la requête
            $laRequete = "DELETE FROM lignefraishorsforfait ";
            $laRequete .= "WHERE id = " . $idFraisHF;

            // Execution et envoi de la requête
            $cnx = PdoGsb::getMonPdo('PdoGsb');
            $req = $cnx->prepare($laRequete);
            $req->execute();


        }catch(PDOException $e){
            print "Erreur !%".$e->getMessage();
            die();
        }
    }
}
