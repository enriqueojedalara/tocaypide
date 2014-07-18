<?php
error_reporting(E_ALL);
require_once('config.php');

if(!isset($_SESSION)) {session_start();}
ini_set('session.gc_maxlifetime', '28800');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE');


//Autloader register
require_once dirname(__FILE__) . '/core/Autoloader.class.php';
spl_autoload_register('AutoLoader::autoload');

//Output header
header('Content-Type: application/json');
try{
    $router = new Router;
    $router->get('/test', 'User->login', false);
    $router->post('/test', 'User->login', false);
    $router->put('/test', 'User->login', false);
    $router->delete('/test', 'User->login', false);
    $router->run();
}
catch(Error $error){
    header('HTTP/1.1 ' . $error->getHttpCode() . ' ' .$error->getMsg());
    closelog();
    die(json_encode($error->getArray()));
}
