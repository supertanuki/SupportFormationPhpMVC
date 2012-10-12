<?php
class Billet {
	
	const tableName = 'billets';
	protected $database;
	protected $commentaire;
	protected $nbbillet = 0;
	protected $format_date_sql_fr = '%d/%m/%Y à %Hh%i et %ss';
	
	function __tostring()
	{
		return "Ceci est une instance de la classe Billet";
	}
	
	function setDatabase( $db )
	{
		$this->database = $db;
	}
	
	function setCommentaireRessource( $commentaireRessource )
	{
		$this->commentaire = $commentaireRessource;
	}
	
	// retourne les billets de blog et paginés
	function getBillets($from, $byPage) {
		$req = $this->database->prepare('
			SELECT id,
				titre,
				contenu,
				DATE_FORMAT(date_creation, \''.$this->format_date_sql_fr.'\') AS date_creation_fr
			FROM ' . Billet::tableName . '
			ORDER BY date_creation DESC
			LIMIT :from, :byPage');
			
		$req->bindParam(':from', $from, PDO::PARAM_INT);
		$req->bindParam(':byPage', $byPage, PDO::PARAM_INT);
		
		$req->execute() or die( var_dump( $req->errorInfo() ));
		
		$billets = $req->fetchAll( PDO::FETCH_ASSOC );
		
		foreach($billets as $key => $b)
		{
			$billets[ $key ]['nb_commentaires'] = $this->commentaire->getNbCommentaires( $b['id'] );
			// équivalent à :
			// $billets[ $key ]['nb_commentaires'] = $this->commentaire->getNbCommentaires( $billets[ $key ]['id'] );
		}
		
		return $billets;
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
				FROM ' . Billet::tableName )
				or die( var_dump( $reqCount->errorInfo() ));
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
			FROM ' . Billet::tableName . '
			WHERE id = :billet_id');
			
		$req->bindParam(':billet_id', $billet_id, PDO::PARAM_INT);
		
		$req->execute() or die( var_dump( $req->errorInfo() ));
		
		$billet = $req->fetchAll( PDO::FETCH_ASSOC );
		
		$billet[ 0 ]['nb_commentaires'] = $this->commentaire->getNbCommentaires( $billet[ 0 ]['id'] );
		
		return $billet;
	}
}

// require_once('../include/bdd.php');
// $billet = new Billet();
// $billet->setDatabase( $database );
// var_dump( $billet->getBillets(0, 10) );
// var_dump( $billet->getOneBillet( 2 ) );

