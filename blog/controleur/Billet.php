<?php
// Charger la classe Billet (notre modele)
require_once('modele/Billet.php');

// Appeller en chemin absolu
// require_once($_SERVER['DOCUMENT_ROOT'] . '/modele/Billet.php');

$billet = new Billet();
$billet->setDatabase( $database );

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