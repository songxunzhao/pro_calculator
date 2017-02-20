<?php
namespace App\Config;

class Loader
{
    public function load($config = 'default')
    {
        return array(
            'db_host' => 'localhost',
            'db_user' => 'calculat_mobile',
            'db_password' => '%U^!4(@/[_-?.jC',
            'db_name' => 'calculat_mobile',
            'file_dir'  => 'assets/files/',
            'space_limit'=> 512000
        );
    }
}
?>