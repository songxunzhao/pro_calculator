<?php
namespace App\Controllers;

use App\DB\Models\User;
use Valitron\Validator;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use App\DB\Models\User as UserModel;
use App\DB\Models\Session as SessionModel;
use App\Helpers\SIPHelper;
use App\Helpers\FileHelper;
use App\Library\AppController;
class Account extends AppController{
    private function generate_temp_code() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function signup(Request $request, Response $response) {
        $parsed_body = $request->getParsedBody();
        $validator = new Validator($parsed_body);
        $validator->rule('required', ['uuid', 'email']);
        $validator->rule('email', 'email');
        $validator->rule('alphaNum', 'uuid');

        if($validator->validate())
        {
            $user_model = new UserModel($this->ci->get('db'));

            $uuid_hash  = $user_model->get_uuid_hash($parsed_body['uuid']);
            $parsed_body['uuid_hash'] = $uuid_hash;
            $user = $user_model->list_one_by_uuid_hash($uuid_hash);

            if (!$user) {
                $user = $user_model->create(
                    $parsed_body
                );
                return $response->withJson([
                    'success'   => true,
                    'data'      => $user,
                    'message'   => 'Successfully registered recovery email'
                ]);
            }
            else {
                return $response->withJson([
                    'success'   => false,
                    'message'   => 'This uuid was already used by another'
                ]);
            }
        }
        else{
            return $response->withJson([
                'success'   => false,
                'data'      => $validator->errors(),
                'message'   => 'Request is not valid'
            ]);
        }
    }

    public function update(Request $request, Response $response)
    {
        $parsed_body = $request->getParsedBody();
        $validator = new Validator($parsed_body);
        $validator->rule('required', ['uuid', 'email']);
        $validator->rule('alphaNum', 'uuid');
        if($validator->validate()) {
            $user_model = new UserModel($this->ci->get('db'));

            $uuid_hash  = password_hash($parsed_body['uuid'], PASSWORD_DEFAULT);
            $parsed_body['uuid_hash'] = $uuid_hash;
            $user = $user_model->update($parsed_body);
            if($user == false) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'User doesn\'t exist'
                ]);
            }
            else {
                return $response->withJson([
                    'success'   => true,
                    'data'      => $user,
                    'message'   => 'Successfully updated user'
                ]);
            }
        }
        else {
            return $response->withJson([
                'success'   => false,
                'data'      => $validator->errors(),
                'message'   => 'Request is not valid'
            ]);
        }
    }

    public function recover_email(Request $request, Response $response) {
        $parsed_body = $request->getParsedBody();
        $validator = new Validator($parsed_body);
        $validator->rule('required', ['uuid']);
        $validator->rule('alphaNum', 'uuid');
        if($validator->validate()) {
            $user_model = new UserModel($this->ci->get('db'));
            $uuid_hash  = $user_model->get_uuid_hash($parsed_body['uuid']);
            $user = $user_model->list_one_by_uuid_hash($uuid_hash);
            if($user == false) {
                return $response->withJson([
                    'success' => false,
                    'message' => 'User doesn\'t exist'
                ]);
            }

            $temp_code = generate_temp_code();
            // Send email

            // Return temporary code
            return $response->withJson([
                'success'   => true,
                'data'      => [
                    'temp_code' => $temp_code
                ]
            ]);
        }
        else {
            return $response->withJson([
                'success'   => false,
                'data'      => $validator->errors(),
                'message'   => 'Request is not valid'
            ]);
        }
    }

}