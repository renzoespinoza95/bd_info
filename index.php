<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

define('VARPATH', dirname(__FILE__));
define ('DEFINITION', VARPATH . "/app/definition.php");

require_once VARPATH."/classes/Meekrodb2.class.php";
require_once VARPATH."/classes/WkHtmlToPdf.php";    
require_once VARPATH."/classes/Mustache.class.php";
require_once VARPATH."/classes/perso.class.php";
require_once VARPATH."/inc/config.inc.php";
require 'flight/Flight.php';
$version = 1;

if (class_exists('WkHtmlToPdf')) {

    //=== WKHTML ===

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        //echo 'This is a server using Windows!';
        $options['bin'] = 'C:\wkhtmltopdf\bin\wkhtmltopdf.exe';
    } else {
        //echo 'This is a server not using Windows!';
        $options['bin'] = '/usr/bin/wkhtmltopdf';
    }

    $wkh_pdf = new WkHtmlToPdf($options);
}

if (perso::_es_apache()) {
    // Headers CORS (una sola vez)
    if (!headers_sent()) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');
        header('Access-Control-Expose-Headers: Content-Length, Content-Range');
    }

    // Preflight OPTIONS (solo bajo Apache)
    Flight::route('OPTIONS *', function () {
        if (!headers_sent()) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 86400');
        }
        // 204 = No Content (mejor que 200 "OK" para preflight)
        Flight::halt(204);
    });
}

perso::config($varhost);

// Meekro
DB::$user = $username;
DB::$password = $password;
DB::$dbName = $dbname;
DB::$host = $host;
DB::query("SET NAMES utf8mb4");

require_once VARPATH."/app/gatti.control.php";

// var_dump($administrador_actual);
// exit;

Flight::set('flight.handle_errors', false);

Flight::start();

