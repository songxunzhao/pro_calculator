<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 1/5/2017
 * Time: 5:43 PM
 */

namespace App\Library;


class Serializer {
    public function __construct($obj, $data, $options = []) {
        $default_options = [
            'is_multi'  => false,
            'fields'    => 'all'
        ];

        $this->obj = $obj;
        $this->data = $data;
        $this->options = array_merge($default_options, $options);
    }

    public function serialize() {
    }

    public function validate() {
    }

    public function save() {
    }

    public function __destruct()
    {
        $this->obj = null;
        $this->data = null;
        $this->options = null;
    }
}