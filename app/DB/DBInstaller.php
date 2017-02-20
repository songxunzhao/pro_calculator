<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 9/29/2016
 * Time: 1:15 AM
 */
namespace App\DB;
use \PDO;
use \PDOException;

class DBInstaller
{

    public function run($db_host, $db_user, $db_pass, $db_name)
    {
        $deploy_path = __DIR__;

        $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);

        $db_deploy_file = file_get_contents($deploy_path . DIRECTORY_SEPARATOR . 'deploy.rc');
        $sql_file_list = explode("\n", $db_deploy_file);

        foreach($sql_file_list as $path) {
            $path = trim($path);
            // remove comment from path
            $pos = strpos($path, '#');
            if($pos !== false) {
                $path = substr($path, 0, $pos);
            }

            if($path === '' )
                continue;
            $path = $deploy_path . DIRECTORY_SEPARATOR . $path;
            $sql = file_get_contents($path, false);

            try {
                $db->exec($sql);
            }
            catch (PDOException $e)
            {
                echo "Error while deploying file: " . $path;
                echo $e->getMessage();
                die();
            }
        }

        $db = null;
    }
}