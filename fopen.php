<?php
$filename = 'test.txt';

$handle = fopen($filename, 'a');
fwrite($handle, "Hello\r\n");
echo "Exécuté !";
fclose($handle);

$hread = fopen($filename, 'r');
echo '<pre>' . fread($hread, filesize($filename)) . '</pre>';
