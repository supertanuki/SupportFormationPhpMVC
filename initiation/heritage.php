<?php
include('classes/Voiture.php');
include('classes/Moto.php');

$bmw = new Moto( 'BMW' );
$bmw->setNbPortes( 3 );

echo '<li> Le casque est fourni ? ';
if( $bmw->leCasqueEstIlFourni() ) echo 'oui'; else echo 'non';

var_dump( $bmw );