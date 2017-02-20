<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 12/15/2016
 * Time: 6:27 PM
 */

namespace App\Library;
use \PDO;
class DBModel {
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    function __destruct() {
        $this->pdo = null;
    }

    protected function execute_query($query, $param_names, $data_dict) {
        $data = [];
        foreach($param_names as $param_name) {
            $data[] = $data_dict[$param_name];
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($data);
        return $stmt;
    }

    protected function fetch_all($query, $param_names, $data_dict) {
        $stmt = $this->execute_query($query, $param_names, $data_dict);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result;
    }

    protected function fetch_one($query, $param_names, $data_dict) {
        $stmt = $this->execute_query($query, $param_names, $data_dict);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if(!$result)
            return null;
        else
            return $result;
    }

    public function pdo() {
        return $this->pdo;
    }
}