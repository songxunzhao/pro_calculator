<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 12/15/2016
 * Time: 10:54 PM
 */
namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\DB\Models\Session;
use App\DB\Models\User;
use \PDO;
class Authenticaton {
    public function __construct(PDO $conn) {
        $this->db = $conn;
    }
    public function __invoke(Request $request, Response $response, $next)
    {
        $token_list = $request->getHeader('authorization');
        $token_hash = hash('sha256', $token_list[0]);

        $session_model = new Session($this->db);
        $session = $session_model->list_one_by_token_hash($token_hash);

        if($session) {
            $user_model = new User($this->db);
            $user = $user_model->list_one($session['user_id']);
            if($user) {
                $request = $request->withAttribute('user', $user);
                return $next($request, $response);
            }
        }
        return $response->withJson(
            ['success' => false, 'data' => 'Authentication failure']
        );
    }
}