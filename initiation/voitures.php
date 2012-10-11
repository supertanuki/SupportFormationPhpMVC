<?php
$time_start = microtime_float();

include_once('db_inc.php');

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function getPromotions() {
	global $database;
	$promotions = array();
	// Liste des promotions
	$requete = $database->prepare('SELECT * FROM promotion ORDER BY libelle');
	$requete->execute();
	while( $resultat = $requete->fetch( PDO::FETCH_ASSOC ) ) {
		$promotions[] = $resultat;
	}
	return $promotions;
}

function gestion_erreur_sql( $execute, $requete )
{
	if( !$execute ) {
		$result = $requete->errorInfo();
		die( $result[2] );
	}
}


// ASTUCE : trouver les promotions qui ne sont pas utilisées par les voitures ?
/*
SELECT  `promotion` . * 
FROM  `promotion` 
LEFT JOIN voiture ON promotion.id = voiture.promotion_id
WHERE voiture.id IS NULL 
*/

// var_dump( getPromotions() );


// Gestion de l'ajout et de la modification
if( isset($_POST['marque'])
	&& strlen( $_POST['marque'] ) > 3
	&& (int) $_POST['portes'] >= 2
	)
{
	$database->beginTransaction();
	
	// Vérifier que l'info n'est pas déjà en base
	$sql = 'SELECT * FROM voiture WHERE marque = :marque AND portes = :portes';
	
	$params = array(
				':marque' => $_POST['marque'],
				':portes' => (int) $_POST['portes'],
				':promotion_id' => (int) $_POST['promotion_id'] == 0 ? NULL : (int) $_POST['promotion_id'],
			);
	
	// si modification => on exclut la voiture (elle-même) de la requête de vérification
	if((int) $_POST['id'])
	{	
		$sql .= ' AND id != :id';
		$params[':id'] = $_POST['id'];
	}
	
	$params_verification = $params;
	unset( $params_verification[':promotion_id'] );
	$requete = $database->prepare( $sql );
	$requete->execute( $params_verification );
	if( $voiture_trouvee = $requete->fetch( PDO::FETCH_ASSOC ) )
	{
		echo '<p>' . htmlspecialchars( 'La voiture a déjà été trouvée : ' . $voiture_trouvee['marque'] . ', '. $voiture_trouvee['portes'] . ' portes' ) . '</p>';
	} else {
	
		// modification
		if((int) $_POST['id'])
		{
			$requete = $database->prepare('UPDATE voiture SET marque = :marque, portes = :portes, promotion_id = :promotion_id WHERE id = :id');
			
		// Ajout
		} else {
			$requete = $database->prepare('INSERT voiture(marque, portes, promotion_id) VALUES(:marque, :portes, :promotion_id)');
		}
		
		$requete->execute( $params );
		
		$nb_rows_updated = $requete->rowCount();
		
		$newid = $database->lastInsertId();
		if( $nb_rows_updated )
		{
			if( $database->commit() ) // équivalent à == true
			{
				echo 'L\'enregistrement de ' . htmlspecialchars( $_POST['marque'] ) . ' s\'est bien fait (id = '.$newid.'). '.$nb_rows_updated;
			} else {
				echo 'Erreur !';
			}
		} else {
			echo 'Rien n\'a été ajouté ou modifié !';
		}
	}
}

// suppression
if(isset($_GET['delete']) && isset($_GET['id']))
{
	$requete = $database->prepare('DELETE FROM voiture WHERE id = :id');
	if( $requete->execute( array(':id' => (int) $_GET['id']) ) )
	{
		if( $requete->rowCount() >= 1 )
		{
			echo '<p>Voiture supprimée !</p>';
		}
	}
}

// Récupérer une voiture // Lien modifier
$voiture = false;
if(isset($_GET['edit']) && isset($_GET['id']))
{
	$requete = $database->prepare('SELECT * FROM voiture WHERE id = :id');
	$requete->execute( array('id' => $_GET['id']) );
	$voiture = $requete->fetch( PDO::FETCH_ASSOC );
	echo '<p>Modifier la voiture '.htmlspecialchars( $voiture['marque'] ).'</p>';
}
?>

<form method="post" action="voitures.php">
	Marque : <input type="text" name="marque" value="<?php if($voiture) echo htmlspecialchars( $voiture['marque'] ); ?>" /><br />
	Nb de portes : <input type="text" name="portes" value="<?php if($voiture) echo htmlspecialchars( $voiture['portes'] ); ?>" /><br />
	
	<?php
	$promos = getPromotions();
	if(is_array($promos) && count($promos))
	{
		?>
		Promotion : <select name="promotion_id">
			<option value="">Aucune</option>
			<?php
			foreach( $promos as $value) {
				?>
				<option value="<?php echo $value['id']; ?>" <?php if($voiture && $voiture['promotion_id'] == $value['id']) echo 'selected'; ?>><?php echo htmlspecialchars( $value['libelle'] ); ?></option>
				<?php
			}
			?>
		</select><br />
		<?php
	}
	?>
	
	<input type="hidden" name="id" value="<?php if($voiture) echo $voiture['id']; ?>" />
	<input type="submit" value="Enregistrer" />
</form>

<?php
// Liste des résultats
$sql = 'SELECT `voiture`.`id`,
				`voiture`.`marque`,
				`voiture`.`portes`,
				`voiture`.`date_creation`,
				`voiture`.`promotion_id`,
				`promotion`.`libelle`,
				`promotion`.`remise`,
				YEAR( `voiture`.`date_creation` ) AS `year_creation`
		FROM `voiture`
		LEFT JOIN promotion ON promotion.id = voiture.promotion_id';
		
if(isset($_GET['filtre_promotion_id'])
	&& $_GET['filtre_promotion_id'] !== ''
	&& (int) $_GET['filtre_promotion_id'] >= 0
	)
{
	$filtre_promotion_id = $_GET['filtre_promotion_id'];
	if( $filtre_promotion_id == 0 )
	{
		$sql .= ' WHERE voiture.promotion_id IS NULL';
	} else {
		$sql .= ' WHERE voiture.promotion_id = '.$database->quote( $filtre_promotion_id );
	}
}
		
$sql .= ' ORDER BY marque';
$requete = $database->prepare( $sql );
$execute = $requete->execute();
gestion_erreur_sql( $execute, $requete );
?>

<?php
$promos = getPromotions();
if(is_array($promos) && count($promos))
{
	?>
	<form method="get" action="voitures.php">
		Filtrer par promotion : <br />
		<label>
			<input name="filtre_promotion_id" type="radio" value=""
			<?php if( !isset($_GET['filtre_promotion_id']) || $_GET['filtre_promotion_id'] == '' ) echo 'checked'; ?>
			/> Toutes les voitures
		</label><br />
		<label>
			<input name="filtre_promotion_id" type="radio" value="0"
			<?php if( isset($_GET['filtre_promotion_id']) && $_GET['filtre_promotion_id'] == '0' ) echo 'checked'; ?>
			/> Aucune promotion
		</label><br />
		<?php
		foreach( $promos as $value) {
			?>
			<label>
				<input name="filtre_promotion_id" type="radio" value="<?php echo $value['id']; ?>"
				<?php if( isset($_GET['filtre_promotion_id']) && $_GET['filtre_promotion_id'] == $value['id'] ) echo 'checked'; ?>
				/>
				<?php echo htmlspecialchars( $value['libelle'] ); ?>
			</label><br />
			<?php
		}
		?>
		<input type="submit" value="Filtrer" />
	</form>
	<?php
}
?>

<table border="1" cellpadding="2" cellspacing="0">
<?php
while( $resultat = $requete->fetch( PDO::FETCH_ASSOC ) )
{
	?><tr>
			<td><?php echo htmlspecialchars($resultat['id']); ?></td>
			<td><?php echo htmlspecialchars($resultat['marque']); ?></td>
			<td><?php echo htmlspecialchars($resultat['portes']); ?>p</td>
			<td><small><?php echo ($resultat['libelle'] ? htmlspecialchars($resultat['libelle']) : '-'); ?></small></td>
			<td><?php echo ( $resultat['remise'] ? htmlspecialchars($resultat['remise'].'%') : '-'); ?></td>
			<!--<td><?php echo $resultat['date_creation']; ?></td>-->
			<td><?php echo $resultat['year_creation']; ?></td>
			<td><a href="voitures.php?edit=1&id=<?php echo $resultat['id']; ?>">Edit</a></td>
			<td><a href="voitures.php?delete=1&id=<?php echo $resultat['id']; ?>">Del</a></td>
		</tr>
	<?php
}
?>
</table>


<?php
$time_end = microtime_float();
$time = $time_end - $time_start;

echo "<li>Ce script a mis : $time secondes\n";