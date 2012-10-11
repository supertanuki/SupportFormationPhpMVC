<?php
// fonctions mysql
// http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_year

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
// affichage date du jour
// echo date('j d/m/Y à H:i:s');

// echo '<li>'.microtime();

$time_start = microtime_float();

// vérifier date de naissance existe
echo '<br />' . checkdate ( 2 , 30, 1964 );

$date_du_passe = mktime(0, 0, 0, 12, 5, 2012);
echo date( 'd/m/Y à H:i:s (t \j\o\u\r\s \d\a\n\s \c\e \m\o\i\s)', $date_du_passe );


$time_end = microtime_float();
$time = $time_end - $time_start;

echo "<li>Ce script a mis : $time secondes\n";