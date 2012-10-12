<?php
// Appeller en chemin absolu
// require_once($_SERVER['DOCUMENT_ROOT'] . '/modele/Billet.php');

// Charger la classe Billet (notre modele)
require_once('modele/Billet.php');

// Charger la classe Commentaire (notre modele)
require_once('modele/Commentaire.php');

// instancier un objet commentaire
$commentaire = new Commentaire();
$commentaire->setDatabase( $database );

// instancier un objet billet
$billet = new Billet();
$billet->setDatabase( $database );
// transmettre l'objet commentaire à l'objet billet
$billet->setCommentaireRessource( $commentaire );

// Test du __tostring()
// echo $billet;
// exit;

// Variable de message ou message d'erreur
$message_de_service = array();

// Afficher un billet
if( isset( $_GET['billet'] ) && (int) $_GET['billet'] > 0 )
{
	// Récupérer le billet
	$oneBillet = $billet->getOneBillet( $_GET['billet'] );
	if( ! $oneBillet )
	{
		// si le billet n'existe pas
		$message_de_service[] = 'Le billet que vous cherchez n\'existe pas !';
		require_once('vue/404.php');
		exit;
	}
	
	// L'utilisateur a t-il posté un commentaire ? Alors, l'insérer
	if( isset($_POST['auteur']) && isset($_POST['message']) )
	{
		if( $commentaire->setCommentaire( $_GET['billet'], $_POST['auteur'], $_POST['message'] ) )
		{
			$message_de_service[] = 'Votre commentaire a bien été propulsé !';
			$message_de_service[] = 'Il est publié en bas de cette page.';
		}
	}
	
	// Récupérer le array des commentaires de ce billet
	$oneBillet[ 0 ]['commentaires'] = $commentaire->getCommentairesDuBillet( $oneBillet[ 0 ]['id'] );
	
	// Charger la vue
	require_once('vue/Billet.view.php');
	
// Afficher la liste des billets et pagination
} else {
	
	// Si p différente d'un entier > 0, alors p = 1
	if( isset($_GET['p']) && (int) $_GET['p'] == 0) $_GET['p'] = 1;
	
	// Charger la classe Pagination
	require_once('classes/Pagination.php');
	$pagination = new pagination();
	$pagination->byPage = 2;
	$pagination->rows = $billet->getNbBillets();
	$pages = $pagination->pages();
	
	// charger les billets
	$billets = $billet->getBillets( $pagination->fromPagination(), $pagination->byPage );

	// Charger la vue
	require_once('vue/Billet.index.php');
}



