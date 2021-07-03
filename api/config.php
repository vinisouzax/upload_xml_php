<?php
ob_start("ob_gzhandler");
error_reporting(0);
session_start();

/* DATABASE CONFIGURATION */
define("BASE_URL", "");
define("API_BASE_URL", "");
define("UPLOAD_PATH", "uploads/");
define("IMAGE_PATH", "imagens/");

function getConn(){
    $dbhost="localhost";
    $dbuser="root";
    $dbpass="6675";
    $dbname="carebr";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);  
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}


/*SMTP CONFIGURATIONS */
define("SMTP_CONNECTION", "0"); //On "1" Off "0"
define("SMTP_USERNAME", "");
define("SMTP_PASSWORD", "");
define("SMTP_HOST", "");
define("SMTP_PORT", "");
define("SMTP_FROM_EMAIL", ""); //Your website supprot email.
define("SMTP_FROM_TITLE", "CARE-BR"); //eg: Support WebsiteName
/*SMTP CONFIGURATIONS END*/

?>
