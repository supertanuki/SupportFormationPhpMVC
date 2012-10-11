<?php
include('classes/Voiture.php');

$audi = new Voiture( 'Audi' );
$audi->setNbPortes( 3 );

$renault = new Voiture();
$renault->setNbPortes( 4 );

// echo $mon_vehicule->marque;

// echo $mon_vehicule->getMarque();

// echo '<p>....bla bla bla</p>';

// echo '<p>Valeur de carburant diesel ? ' . Voiture::CARBURANT_DIESEL;

// $mon_vehicule->affiche_quelquechose( 'bonjour tout le monde 1' );

// Voiture::affiche_quelquechose( 'bonjour tout le monde 2' );

var_dump( $audi );
var_dump( $renault );
