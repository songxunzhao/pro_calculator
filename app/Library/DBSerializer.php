<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 1/5/2017
 * Time: 6:04 PM
 */

namespace App\Library;


class DBSerializer extends Serializer{
    public function __construct($obj, $data, $db, $options = []) {
        parent::__construct($obj, $data, $options);
        $this->db = $db;
    }
    public function __destruct() {
        $this->db = null;
    }
}