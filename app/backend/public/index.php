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

$app->get('/curl/', function (Request $request, Response $response) {
    $ch = curl_init('//mysql/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $html = curl_exec($ch);
    curl_close($ch);
    pre($html);
    $response->withStatus(200)->write($html);
    return $response;
});

$app->get('/db/', function (Request $request, Response $response) {
    $db = new ORM();
    $params = $request->getParams();
    switch ($params['action']) {
        case null:
            echo "no action recieved";
            break;
        case 'Add':
            $db->add($params);
            break;
        case 'List':
            $list = $db->getList();
            break;
        case 'Update':
            $db->update($params['id'], $params);
            break;
        case 'Delete':
            $db->delete($params['id']);
            break;
    };
	$newResponse = $response->withJson($db->arResult);
    return $newResponse;
});


class ORM
{
    public $arResult = [];
    protected static $table = 'app_users';

    public function __construct()
    {
        $this->arResult['connection'] = $this->dbConnect();
    }

    protected function dbConnect(){
        $host='mysql';
        $user='crud';
        $pass='YWRtaW4=';
        $dbname='mainDb';
        $pdo= new PDO("mysql:host=$host; dbname=$dbname", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }

    public function getList()
    {
        $sql = "SELECT * FROM ".self::$table;
        $res = $this->arResult['connection']->query($sql);
        $users = $res->fetchAll(PDO::FETCH_OBJ);
        $this->arResult['result'] = $users;
        return $users;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM ".self::$table." WHERE id = ".$id." LIMIT 1";
        $res = $this->arResult['connection']->query($sql);
    }

    public function update($id, $values)
    {
        $setter = '';
        unset($values['action']);
        unset($values['id']);
        foreach($values as $key=>$value) {
            $i++;
            $and = $i == count($values) ? '' : ',';
            $setter .= $key.' = "'.$value.'"'.$and.' ';
        }
        $sql = "UPDATE ".self::$table." SET ".$setter." WHERE id = ".$id;
        //pre($sql);
        $res = $this->arResult['connection']->query($sql);
    }

    public function add($values)
    {
        if($values['name'] && $values['email'])
        {
            $date = date('Y-m-d', time());
            $sql = "INSERT INTO ".self::$table." VALUES (NULL,'".$values['name']."','".$values['email']."','".$date."');";
            $res = $this->arResult['connection']->query($sql);
            return $res;
        }
        else
        {
            return false;
        }
    }

}

function pre($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

$app->run();