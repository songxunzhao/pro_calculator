<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 12/15/2016
 * Time: 10:06 PM
 */
namespace App\Helpers;

class FileHelper {
    public static function move_input_file($input_name, $base_path, $name_prefix) {
        if(array_key_exists($input_name, $_FILES))
        {
            $filename = $_FILES[$input_name]["name"];
            $extension = end(explode(".", $filename));
            $filename_image = time() . "." . $extension;
            $user_img = $base_path . "/" . $name_prefix. basename($filename_image);
            move_uploaded_file($_FILES['user_image']['tmp_name'], $user_img);
            return $user_img;
        }
        else{
            return false;
        }
    }
    public static function get_folder_size($path) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $f = $path;
            $obj = new COM ( 'scripting.filesystemobject' );
            if ( is_object ( $obj ) )
            {
                $ref = $obj->getfolder ( $f );
                $obj = null;
                return $ref->size;
            }
            else
            {
                return null;
            }
        } else {
            $f = $path;
            $io = popen ( '/usr/bin/du -sk ' . $f, 'r' );
            $size = fgets ( $io, 4096);
            $size = substr ( $size, 0, strpos ( $size, "\t" ) );
            pclose ( $io );
            return $size;
        }

    }
}