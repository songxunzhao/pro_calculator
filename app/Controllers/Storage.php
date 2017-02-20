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

        $uuid = $request->getHeader('http_uuid');
        $type = $request->getHeader('http_type');
        $user = $user_model->list_one_by_uuid_hash($user_model->get_uuid_hash($uuid));
        if(!$user) {
            return $response->withJson([
                'success'   => false,
                'message'   => 'You are not registered yet'
            ]);
        }
        $dir_path = $config['file_dir'] . $uuid . DIRECTORY_SEPARATOR;
        if(!file_exists($dir_path))
            mkdir($dir_path);

        $dir_size = FileHelper::get_folder_size($dir_path);
        $file_size = $request->getHeader('file-size');
        if($config['space_limit'] < $file_size + $dir_size)
        {
            return $response->withJson([
                'success'   => false,
                'message'   => 'File size is too big.'
            ]);
        }

        $file_streamer = new FileStreamer();
        $file_streamer->setDestination($dir_path);
        $file_streamer->setFileName($type);

        $file_streamer->receive();
        return $response->withJson([
            'success'   => true,
            'message'   => 'File was saved'
        ]);
    }

    public function download(Request $request, Response $response) {
        $config = $this->ci->get('config');
        $db = $this->ci->get('db');
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
}