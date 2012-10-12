<?php
class Commentaire {
	
	public $database;
	public $format_date_sql_fr = '%d/%m/%Y à %Hh%i et %ss';
	
	function setDatabase( $db )
	{
		$this->database = $db;
	}
	
	// retourne le nb de commentaires d'un billet
	function getNbCommentaires( $billet_id )
	{
		$reqCount = $this->database->prepare('
			SELECT COUNT(id) AS nb_commentaires
			FROM commentaires
			WHERE billet_id = :billet_id');
			
		$reqCount->bindParam(':billet_id', $billet_id, PDO::PARAM_INT);
		
		$reqCount->execute() or die( var_dump( $this->database->errorInfo() ));
		if($row = $reqCount->fetch( PDO::FETCH_ASSOC ))
		{
			// on retourne le nb de commentaires
			return $row['nb_commentaires'];
		}
	}
	
	// Retourne un billet selon son ID
	function getOneBillet( $billet_id )
	{
		$req = $this->database->prepare('
			SELECT id,
				titre,
				contenu,
				DATE_FORMAT(date_creation, \''.$this->format_date_sql_fr.'\') AS date_creation_fr
			FROM billets
			WHERE id = :billet_id');
			
		$req->bindParam(':billet_id', $billet_id, PDO::PARAM_INT);
		
		$req->execute() or die( var_dump( $this->database->errorInfo() ));
		
		return $req->fetchAll( PDO::FETCH_ASSOC );
	}
}

// require_once('../include/bdd.php');
// $billet = new Billet();
// $billet->setDatabase( $database );
// var_dump( $billet->getBillets(0, 10) );
// var_dump( $billet->getOneBillet( 2 ) );

