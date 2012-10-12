<?php
// Notre controleur global
require_once('include/bdd.php');

require_once('include/Twig/Autoloader.php');
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('vue'); // /path/to/templates
$twig = new Twig_Environment($loader, array(
    'cache' => 'cache', // /path/to/compilation_cache
));


if( isset( $_GET['afficher-commentaires'] ))
{
	// charger controleur de commentaire
	require_once('controleur/Commentaire.php');
	
} else {
	// charger controleur de billet
	require_once('controleur/Billet.php');
}
