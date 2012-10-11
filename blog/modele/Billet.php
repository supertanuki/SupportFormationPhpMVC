<?php
class Billet {
	
	public $database;
	public $nbbillet = 0;
	
	function setDatabase( $db )
	{
		$this->database = $db;
	}
	
	// retourne les billets de blog et paginés
	function getBillets($from, $byPage) {
		$req = $this->database->query('
			SELECT id, titre, contenu, DATE_FORMAT(date_creation, \'%d/%m/%Y à %Hh%imin%ss\') AS date_creation_fr
			FROM billets
			ORDER BY date_creation DESC
			LIMIT ' . $from . ', ' . $byPage)
			or die( var_dump( $this->database->errorInfo() ));
		
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
}

// require_once('../include/bdd.php');
// $billet = new Billet();
// $billet->setDatabase( $database );
// var_dump( $billet->getBillets(0, 10) );
// var_dump( $billet->getNbBillets() );

