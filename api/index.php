<?php
ob_start();
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *'); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Slim/Slim.php';
require_once 'config.php';
include_once 'utils.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

include_once 'controllers/XmlController.php';

$app->post("/upload_xml", function () {

    $request = \Slim\Slim::getInstance()->request();
    XmlController::upload_xml($_FILES['file']['tmp_name']);

});

$app->run();

?>
