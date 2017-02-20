<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 12/15/2016
 * Time: 10:50 PM
 */

namespace App\Library;
use \Interop\Container\ContainerInterface as ContainerInterface;

class AppController {
    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }
    public function __destruct() {
        $this->ci = null;
    }
}