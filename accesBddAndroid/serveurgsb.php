<?php
include "../includes/class.pdogsb.inc.php";
use PdoGsb\PdoGsb;

$pdo = PdoGsb::getPdoGsb('PdoGsbAndroid');

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

			$laRequete = "SELECT * FROM visiteur WHERE login ='" . $login ."' AND mdp ='" . $mdp . "'";
			
			// Execution et envoi de la requête
			$cnx = PdoGsb::getMonPdo('PdoGsbAndroid');
			$req = $cnx->prepare($laRequete);
			$req->execute();
			// Si la requête a réussi, on retourne les informations
			if($ligne = $req->fetch(PDO::FETCH_ASSOC)){
				print(json_encode($ligne));
			}

		}catch(PDOException $e){
			print "Erreur !%".$e->getMessage();
			die();
		}
	}elseif($_REQUEST["operation"]=="getFraisForfait"){

		try{
			// récupération des données en post
			print("getFraisForfait%");
			$lesdonnees = $_REQUEST["lesdonnees"];
			$donnee = json_decode($lesdonnees);
            $idVisiteur = $donnee[0];
			$mois = $donnee[1];

			// Préparation de la requête
			$laRequete = "SELECT * FROM lignefraisforfait WHERE idvisiteur ='" .$idVisiteur . "' AND mois = '" . $mois . "'";

			// Execution et envoi de la requête
			$cnx = PdoGsb::getMonPdo('PdoGsbAndroid');
			$req = $cnx->prepare($laRequete);
			$req->execute();
			$ligne = $req->fetchAll();
			print(json_encode($ligne));

		}catch(PDOException $e){
			print "Erreur !%".$e->getMessage();
			die();
		}
	}elseif($_REQUEST["operation"]=="getFraisHF"){

		try{
			// récupération des données en post
			print("getFraisHF%");
			$lesdonnees = $_REQUEST["lesdonnees"];
			$donnee = json_decode($lesdonnees);
			$idvisiteur = $donnee[0];
			$mois = $donnee[1];

			// Préparation de la requête
			$laRequete = "SELECT * FROM lignefraishorsforfait WHERE idvisiteur ='" .$idvisiteur . "' AND mois = '" . $mois . "'";
			
			// Execution et envoi de la requête
			$cnx = PdoGsb::getMonPdo('PdoGsbAndroid');
			$req = $cnx->prepare($laRequete);
			$req->execute();
			$ligne = $req->fetchAll(PDO::FETCH_ASSOC);
			// Encodage des accents
			for($i=0; $i<sizeof($ligne); $i++) {
				mb_detect_encoding($ligne[$i]['libelle'], "UTF-8") != "UTF-8" ? : $ligne[$i]['libelle'] = utf8_encode($ligne[$i]['libelle']);
			};
			
			print(json_encode($ligne, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

		}catch(PDOException $e){
			print "Erreur !%".$e->getMessage();
			die();
		}
	}elseif($_REQUEST["operation"]=="updateFraisForfait"){

		try{
			// récupération des données en post
			print("updateFraisForfait%");
			$lesdonnees = $_REQUEST["lesdonnees"];
			$donnee = json_decode($lesdonnees);
			$idVisiteur = $donnee[0];
			$mois = $donnee[1];
			$idFrais = $donnee[2];
			$quantite = $donnee[3];

			if ($pdo->estPremierFraisMois($idVisiteur, $mois)) {
				$pdo->creeNouvellesLignesFrais($idVisiteur, $mois);
			};


			// Préparation de la requête
			$laRequete = "UPDATE lignefraisforfait SET quantite =" .$quantite . " WHERE mois = '" . $mois . "' AND idvisiteur = '" . $idVisiteur . "' AND idfraisforfait = '" . $idFrais . "'";

			// Execution et envoi de la requête
			$cnx = PdoGsb::getMonPdo('PdoGsbAndroid');
			$req = $cnx->prepare($laRequete);
			$req->execute();
			print(json_encode($laRequete));


		}catch(PDOException $e){
			print "Erreur !%".$e->getMessage();
			die();
		}
	}elseif($_REQUEST["operation"]=="ajoutFraisHF"){

		try{
			// récupération des données en post
			print("ajoutFraisHF%");
			$lesdonnees = $_REQUEST["lesdonnees"];
			$donnee = json_decode($lesdonnees);
			$idVisiteur = $donnee[0];
			$mois = $donnee[1];
			// On décode le libellé pour récuperer les caractères spéciaux
			$libelle = utf8_decode($donnee[2]);
			$date = $donnee[3];
			$montant = $donnee[4];

			if ($pdo->estPremierFraisMois($idVisiteur, $mois)) {
				$pdo->creeNouvellesLignesFrais($idVisiteur, $mois);
			};

			// Préparation de la requête
			$laRequete = "INSERT INTO lignefraishorsforfait (idvisiteur, mois, libelle, date, montant) ";
			$laRequete .= "VALUES ('".$idVisiteur."', '".$mois."', '".$libelle. "', '".$date. "', ".$montant.")";

			// Execution et envoi de la requête
			$cnx = PdoGsb::getMonPdo('PdoGsbAndroid');
			$req = $cnx->prepare($laRequete);
			$req->execute();

			// Dès que l'ajout est effectué, on récupère le dernier id du frais hors forfait de la table lignefraishorsforfait
			// et on le retourne
			
			$laRequete = "SELECT idMax, libelle, date, montant ";
			$laRequete .="FROM (SELECT MAX(id) AS 'idMax', libelle, date, montant ";
			$laRequete .= "FROM lignefraishorsforfait ";
			$laRequete .= "GROUP BY libelle, date, montant) AS req ";
			$laRequete .= "WHERE idMax = (SELECT MAX(id) ";
			$laRequete .= " FROM lignefraishorsforfait)";

			// Execution et envoi de la requête
			$cnx = PdoGsb::getMonPdo('PdoGsbAndroid');
			$req = $cnx->prepare($laRequete);
			$req->execute();
			$ligne = $req->fetch(PDO::FETCH_ASSOC);
			print(json_encode($ligne));


		}catch(PDOException $e){
			print "Erreur !%".$e->getMessage();
			die();
		}
	}elseif($_REQUEST["operation"]=="suppressionFraisHF"){

		try{
			// récupération des données en post
			print("suppressionFraisHF%");
			$lesdonnees = $_REQUEST["lesdonnees"];
			$donnee = json_decode($lesdonnees);
			$idFraisHF = $donnee[0];

			$laRequete = "SELECT id FROM lignefraishorsforfait WHERE id = " . $idFraisHF;

			// Execution et envoi de la requête
			$cnx = PdoGsb::getMonPdo('PdoGsbAndroid');
			$req = $cnx->prepare($laRequete);
			$req->execute();
			$ligne = $req->fetch(PDO::FETCH_ASSOC);
			print(json_encode($ligne));

			// Préparation de la requête
			$laRequete = "DELETE FROM lignefraishorsforfait WHERE id = " . $idFraisHF;

			// Execution et envoi de la requête
			$cnx = PdoGsb::getMonPdo('PdoGsbAndroid');
			$req = $cnx->prepare($laRequete);
			$req->execute();


		}catch(PDOException $e){
			print "Erreur !%".$e->getMessage();
			die();
		}
	}
}