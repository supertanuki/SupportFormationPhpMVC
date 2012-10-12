<?php
class Billet {
	
	public $database;
	public $nbbillet = 0;
	public $format_date_sql_fr = '%d/%m/%Y à %Hh%i et %ss';
	
	function setDatabase( $db )
	{
		$this->database = $db;
	}
	
	// retourne les billets de blog et paginés
	function getBillets($from, $byPage) {
		$req = $this->database->prepare('
			SELECT id,
				titre,
				contenu,
				DATE_FORMAT(date_creation, \''.$this->format_date_sql_fr.'\') AS date_creation_fr
			FROM billets
			ORDER BY date_creation DESC
			LIMIT :from, :byPage');
			
		$req->bindParam(':from', $from, PDO::PARAM_INT);
		$req->bindParam(':byPage', $byPage, PDO::PARAM_INT);
		
		$req->execute() or die( var_dump( $this->database->errorInfo() ));
		
		return $req->fetchAll( PDO::FETCH_ASSOC );
	}
	
	// retourne le nb de billets de blog
	function getNbBillets()
	{
		// si on n'a pas la valeur on requete la base
		if($this->nbbillet == 0)
		{
			// echo "<li>je cherche en base</li>";
			$reqCount = $this->database->query('
				SELECT COUNT(id) AS nb_billets
				FROM billets')
				or die( var_dump( $this->database->errorInfo() ));
			if($row = $reqCount->fetch( PDO::FETCH_ASSOC ))
			{
				$this->nbbillet = $row['nb_billets'];
			}
		}
		
		// on retourne le nb de billets
		return $this->nbbillet;
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

