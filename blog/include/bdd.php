<?php
$user = 'bloguser';
$pass = 'xdWrRjzj8t4xAxuh';
$dbname = 'blog';

try {
	$database = new PDO('mysql:host=localhost;dbname='.$dbname, $user, $pass);
	
} catch(PDOException $e) {
	die( "Erreur de connexion : ".$e->getMessage() );
}