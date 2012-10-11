<?php
include_once('db_inc.php');

$requete = $database->prepare('SELECT id, marque, portes, date_creation FROM voiture WHERE portes = :portes AND marque LIKE :marque');
$nbportes = 5;
$marque = 'peu%';
$requete->bindParam(':portes', $nbportes);
$requete->bindParam(':marque', $marque);
$requete->execute();

echo '<table border="1">';
while( $resultat = $requete->fetch( PDO::FETCH_ASSOC ) )
{
	echo '<tr>
			<td>'.$resultat['id'].'</td>
			<td>'.$resultat['marque'].'</td>
			<td>'.$resultat['portes'].'</td>
			<td>'.$resultat['date_creation'].'</td>
		</tr>';
}
echo '</table>';