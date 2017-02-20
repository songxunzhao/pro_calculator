<?php
require __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';

use App\Config\Loader;
use App\DB\DBInstaller;

$loader = new Loader();
$config = $loader->load();

$db_installer = new DBInstaller();
$db_installer->run($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);

?>