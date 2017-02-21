<?php
namespace App\Controllers;

use App\DB\Models\User;
use Valitron\Validator;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use App\DB\Models\User as UserModel;
use App\DB\Models\Session as SessionModel;
use App\Helpers\SIPHelper;
use App\Library\AppController;

use Mailgun\Mailgun;

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
                $user = $user_model->update($parsed_body);
                return $response->withJson([
                    'success'   => true,
                    'data'      => $user,
                    'message'   => 'Successfully updated recovery email'
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

            $temp_code = $this->generate_temp_code();
            $this->sendRecoverEmail($temp_code, $user['email']);
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

    private function sendRecoverMailgunEmail($temp_code, $email) {
        // Send email
        $config = $this->ci->get('config');
        $mgClient = new Mailgun($config['mailgun_api_key']);
        $domain = "calculator.dakyuz.com";
        $result = $mgClient->sendMessage($domain, array(
            'from'    => '"Calculator Pro" <mailgun@calculator.dakyuz.com>',
            'to'      => "You <$email>",
            'subject' => 'Here is your recovery code',
            'text'    => 'Hi. Here is recovery code ' . $temp_code
        ));

    }

    private function sendRecoverEmail($temp_code, $email) {
        // Send email
        $config = $this->ci->get('config');
        $headers = 'From: "Calculator Pro" <no-reply@calculator.dakyuz.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        $message = 'Hi. Here is recovery code ' . $temp_code;
        mail("You <$email>", "Here is your recovery code", $message, $headers);
    }
}