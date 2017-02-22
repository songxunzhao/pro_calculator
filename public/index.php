<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 12/11/2016
 * Time: 1:33 AM
 */
require '../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use App\Config\Loader;

$loader = new Loader();
$config = $loader->load();

// Connect db
$conn = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};port=3306", $config['db_user'], $config['db_password']);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Gearman client
$gmc= new GearmanClient();

# add the default server (localhost)
$gmc->addServer();

$app = new \Slim\App(
    [
        'settings'  => [
            'displayErrorDetails' => true
        ],
        'config'    => $config,
        'db'        => $conn,
        'gm_client' => $gmc
    ]
);

$app->group('/account', function (){
    $this->post('/signup',              'App\Controllers\Account:signup');
    $this->post('/recover_email',       'App\Controllers\Account:recover_email');
});

$app->group('/storage', function (){
    $this->post('/upload',              'App\Controllers\Storage:upload');
    $this->post('/download',            'App\Controllers\Storage:download');
    $this->post('/status',              'App\Controllers\Storage:status');
});

$app->run();
$conn = null;