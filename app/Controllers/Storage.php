<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 2/19/2017
 * Time: 9:23 AM
 */

namespace App\Controllers;


use App\Helpers\FileHelper;
use App\Helpers\FileStreamer;
use App\Library\AppController;
use App\DB\Models\User as UserModel;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Storage extends AppController{
    public function upload(Request $request, Response $response) {

        $config = $this->ci->get('config');
        $db     = $this->ci->get('db');

        $user_model = new UserModel($db);

        $uuid = $request->getHeader('http_uuid')[0];
        $user = $user_model->list_one_by_uuid_hash($user_model->get_uuid_hash($uuid));
        if(!$user) {
            return $response->withJson([
                'success'   => false,
                'message'   => 'You are not registered yet'
            ]);
        }

        $dir_path = $config['file_dir'] . $uuid;
        if(!file_exists($dir_path))
            mkdir($dir_path);

        $file_path = FileHelper::get_file_name('file', $dir_path);

        $dir_size = FileHelper::get_folder_size($dir_path);
        if(file_exists($file_path))
            $old_file_size = FileHelper::get_folder_size($file_path);
        else
            $old_file_size = 0;
        $file_size = $request->getHeader('content-length')[0] / 1024;

        if($config['space_limit'] < $file_size + $dir_size - $old_file_size)
        {
            return $response->withJson([
                'success'   => false,
                'message'   => 'File size is too big.'
            ]);
        }

        FileHelper::move_input_file('file', $file_path);

//        $file_streamer = new FileStreamer();
//        $file_streamer->setDestination($dir_path);
//        $file_streamer->setFileName($type);
//        $file_streamer->receive();

        return $response->withJson([
            'success'   => true,
            'message'   => 'File was saved',
            'data'      => [
                'file_path' => $file_path
            ]
        ]);
    }

    public function download(Request $request, Response $response) {
        $config = $this->ci->get('config');
        $parsed_body = $request->getParsedBody();
        $uuid = $parsed_body['uuid'];
        $type = $parsed_body['type'];
        $dir_path = $config['file_dir'] . $uuid;
        $file_name = $dir_path . DIRECTORY_SEPARATOR . $type;
        if(file_exists($file_name))
        {
            $fh = fopen($file_name, 'rb');
            $stream = new \Slim\Http\Stream($fh);

            $response   = $response->withHeader('Content-Type', 'application/octet-stream');
            $response   = $response->withHeader('Content-Description', 'File Transfer');
            $response   = $response->withHeader('Content-Disposition', 'attachment; filename="' .$type . '"');
            $response   = $response->withHeader('Content-Transfer-Encoding', 'binary');
            $response   = $response->withHeader('Expires', '0');
            $response   = $response->withHeader('Cache-Control', 'must-revalidate');
            $response   = $response->withHeader('Pragma', 'public');
            $response   = $response->withHeader('Content-Length', filesize($file_name));
            return $response->withBody($stream);
        }
        else {
            return $response->withStatus(404);
        }
    }

    public function status(Request $request, Response $response) {
        $approved = false;
        $config = $this->ci->get('config');
        $db     = $this->ci->get('db');

        $user_model = new UserModel($db);

        $parsed_body = $request->getParsedBody();
        $uuid = $parsed_body['uuid'];
        if(array_key_exists('type', $parsed_body))
            $type = $parsed_body['type'];
        else
            $type = "";

        $user = $user_model->list_one_by_uuid_hash($user_model->get_uuid_hash($uuid));
        if(!$user) {
            return $response->withJson([
                'success'   => false,
                'approved'  => false,
                'message'   => 'You are not registered yet'
            ]);
        }

        $dir_path = $config['file_dir'] . $uuid;
        if(!file_exists($dir_path))
            mkdir($dir_path);

        $dir_size = FileHelper::get_folder_size($dir_path);

        $old_file_size = 0;
        if($type != "") {
            $file_path = $dir_path . DIRECTORY_SEPARATOR . $type;
            if (file_exists($file_path))
                $old_file_size = FileHelper::get_folder_size($file_path);
        }

        $available_size = $config['space_limit'] - $dir_size + $old_file_size - 1;
        $free_size = $config['space_limit'] - $dir_size;
        return $response->withJson([
            'success'   => true,
            'approved'  => $approved,
            'data'      => [
                'used'      => $dir_size * 1024,
                'free'      =>  $free_size * 1024,
                'available' => $available_size * 1024
            ]
        ]);
    }
}