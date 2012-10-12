<?php
// Charger la classe Commentaire (notre modele)
require_once('modele/Commentaire.php');

// instancier un objet commentaire
$commentaire = new Commentaire();
$commentaire->setDatabase( $database );

// charger les 10 derniers commentaires
$comments = $commentaire->getLastCommentaires();

// var_dump($comments);

// charger le template dans le dossier "vue"
echo $twig->render('commentaire/Commentaire.list.twig.html', array(
	'comments' => $comments,
	));