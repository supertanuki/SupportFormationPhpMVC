<?php
// variables d'environnements
// var_dump( $_SERVER );

// $str = 'Bonjour tout le monde';
// $replace = array('bonjour', 'le');
// $by = array('Salut', 'la');

// echo $str;
// echo '<br />';
// echo str_ireplace($replace, $by, $str);


$str = '"Bonjour";"Salut";"Hello";"Good Morning !!!";';

if( $str[0] == '"' ) $str = substr($str, 1, strlen( $str ));

if( substr($str, -2, 2) == '";' ) $str = substr($str, 0, strlen( $str ) -2);

$tabStr = explode('";"', $str);
var_dump( $tabStr );



$text = "Portez ce vieux whisky au juge blond qui fume.";
$text2 = "Portez ce  vieux whisky au juge blond qui fume.";
$newtext = wordwrap($text, 20, "<br />\n");
echo $newtext;

$sel = "Portez ce vieux whisky au juge blond qui fume.";
$password = "passpass";
echo '<li>' . md5( $password . 'hello' . $sel . '#' );


$str = 'й_ий" зий-"_и';
echo '<li>' . ucfirst($str);

$nombre = 1250.3;
echo "<li>$nombre = " . str_replace( ' ', '&nbsp;', number_format ( $nombre, 2, ',', ' '));








