<?php
$user = 'eliteuser';
$pass = 'passpass';
$dbname = 'formation';

try {
	$database = new PDO('mysql:host=localhost;dbname='.$dbname, $user, $pass);
	
} catch(PDOException $e) {
	die( "Erreur de connexion : ".$e->getMessage() );
}