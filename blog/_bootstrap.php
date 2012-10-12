<?php
require_once dirname(__FILE__).'/include/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('vue');
$twig = new Twig_Environment($loader, array(
    'cache' => 'cache',
));

$users = array(array('name' => 'Bonjour'), array('name' => 'Jean'));
echo $twig->render('test.twig.html', array('users' => $users));

