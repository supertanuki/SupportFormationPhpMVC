<?php
// Appeller en chemin absolu
// require_once($_SERVER['DOCUMENT_ROOT'] . '/modele/Billet.php');

// Charger la classe Billet (notre modele)
require_once('modele/Billet.php');

// Charger la classe Commentaire (notre modele)
require_once('modele/Commentaire.php');

$billet = new Billet();
$billet->setDatabase( $database );

$commentaire = new Commentaire();
$commentaire->setDatabase( $database );
var_dump( "getNbCommentaires = " . $commentaire->getNbCommentaires( $billet_id = 1 ) );

// doit on afficher un lien retour à la liste (retour page d'accueil)
$afficher_retour_liste = false;

// Afficher un billet
if( isset( $_GET['billet'] ) && (int) $_GET['billet'] > 0 )
{
	$billets = $billet->getOneBillet( $_GET['billet'] );
	$afficher_retour_liste = true;
	
// Afficher la liste des billets et pagination
} else {

	// Charger la classe Pagination
	require_once('classes/Pagination.php');
	$pagination = new pagination();
	$pagination->byPage = 2;
	$pagination->rows = $billet->getNbBillets();
	$pages = $pagination->pages();

	// charger les billets
	$billets = $billet->getBillets( $pagination->fromPagination(), $pagination->byPage );
}

// Charger la vue
require_once('vue/Billet.index.php');

