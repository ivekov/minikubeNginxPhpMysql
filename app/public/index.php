<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require '../vendor/autoload.php';
$app = new \Slim\App();

$app->get('/', function (Request $request, Response $response) {
    $response->withStatus(200)->write("Api Webgate v1");
    return $response;
});

$app->get('/health/', function (Request $request, Response $response) {
    $response->withStatus(200)->write("Ok");
    return $response;
});

$app->get('/version/', function (Request $request, Response $response) {
    $response->withStatus(200)->write("v1");
    return $response;
});

$app->get('/db/', function (Request $request, Response $response) {
	$objectStatus = ['result' => dbConnect()];
	$newResponse = $response->withJson($objectStatus);
    return $newResponse;
});

function dbConnect(){
    $host='127.0.0.1';
    $user='root';
    $pass='';
    $dbname='testdbmysql';
    $pdo= new PDO("mysql:host=$host; dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

$app->run();