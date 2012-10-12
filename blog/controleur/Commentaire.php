<?php
// Charger la classe Commentaire (notre modele)
require_once('modele/Commentaire.php');

// instancier un objet commentaire
$commentaire = new Commentaire();
$commentaire->setDatabase( $database );

echo $twig->render('commentaire/Commentaire.list.twig.html', array('helloworld' => 'Hello World !!! Bonjour !!!!'));