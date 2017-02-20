<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 12/12/2016
 * Time: 10:23 PM
 */
namespace App\DB\Models;

use \PDO;
use App\Library\DBModel;

class User extends DBModel{
    private function param_array_create() {
        return [
            'uuid_hash',
            'email'
        ];
    }

    public function create($data) {
        $query_params = $this->param_array_create();
        $query = "CALL fx_user_create(?, ?)";
        return $this->fetch_one($query, $query_params, $data);
    }

    public function list_one_by_uuid_hash($uuid_hash) {
        $query_params = ['uuid_hash'];
        $query = "CALL fx_user_list_one_by_uuid_hash(?)";
        return $this->fetch_one($query, $query_params, ['uuid_hash' => $uuid_hash]);
    }

    public function update($data) {
        $query_params = [
            'uuid_hash',
            'email'
        ];
        $query = "CALL fx_user_update(?, ?)";
        return $this->fetch_one($query, $query_params, $data);
    }

    public function get_uuid_hash($uuid) {
        return hash('sha512', $uuid);
    }
}