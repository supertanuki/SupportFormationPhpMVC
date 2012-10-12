<?php
class Commentaire {
	
	const tableName = 'commentaires';
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
			FROM ' . Commentaire::tableName . '
			WHERE billet_id = :billet_id');
			
		$reqCount->bindParam(':billet_id', $billet_id, PDO::PARAM_INT);
		
		$reqCount->execute() or die( var_dump( $reqCount->errorInfo() ));
		if($row = $reqCount->fetch( PDO::FETCH_ASSOC ))
		{
			// on retourne le nb de commentaires
			return $row['nb_commentaires'];
		}
	}
	
	// Retourne tous les commentaires d'un billet selon un ID
	function getCommentairesDuBillet( $billet_id )
	{
		$req = $this->database->prepare('
			SELECT id,
				auteur,
				message,
				DATE_FORMAT(date_message, \''.$this->format_date_sql_fr.'\') AS date_message_fr
			FROM ' . Commentaire::tableName . '
			WHERE billet_id = :billet_id'
			);
		$req->bindParam(':billet_id', $billet_id, PDO::PARAM_INT);
		$req->execute() or die( var_dump( $req->errorInfo() ));
		return $req->fetchAll( PDO::FETCH_ASSOC );
	}
	
	// Retourne les derniers commentaires
	function getLastCommentaires()
	{
		$req = $this->database->query('
			SELECT id,
				auteur,
				message,
				DATE_FORMAT(date_message, \''.$this->format_date_sql_fr.'\') AS date_message_fr
			FROM ' . Commentaire::tableName . '
			ORDER BY date_message ASC
			LIMIT 10'
			) or die( var_dump( $req->errorInfo() ));
			
		return $req->fetchAll( PDO::FETCH_ASSOC );
	}
	
	// Insérer un commentaire
	function setCommentaire( $billet_id, $auteur, $message )
	{
		if( isset($billet_id) && isset($auteur) && isset($message)
			&& $billet_id && $auteur && $message )
		{
			$req = $this->database->prepare('
				INSERT INTO ' . Commentaire::tableName . '
					( auteur, message, date_message, billet_id )
				VALUES
					( :auteur, :message, NOW(), :billet_id )
				');
			$req->bindParam(':billet_id', $billet_id, PDO::PARAM_INT);
			$req->bindParam(':auteur', $auteur, PDO::PARAM_STR);
			$req->bindParam(':message', $message, PDO::PARAM_STR);
			
			$req->execute() or die( var_dump( $req->errorInfo() ));
			
			// nb d'enregistrements insérés par la requete (0 ou 1 ici)
			if( $req->rowCount() ) return true;
		}
		
		return false;
	}
}
