<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 2/19/2017
 * Time: 9:25 AM
 */
namespace App\Helpers;
use Exception;

class FileStreamer
{
    private $_fileName;
    private $_contentLength;
    private $_destination;
    public function __construct()
    {
        if (!isset($_SERVER['HTTP_X_FILE_NAME'])
            && !isset($_SERVER['CONTENT_LENGTH'])) {
            throw new Exception("No headers found!");
        }
        $this->_fileName = $_SERVER['HTTP_X_FILE_NAME'];
        $this->_contentLength = $_SERVER['CONTENT_LENGTH'];
    }
    public function isValid()
    {
        if (($this->_contentLength > 0)) {
            return true;
        }
        return false;
    }
    public function getContentSize()
    {
        return $this->_contentLength;
    }
    public function setDestination($destination)
    {
        $this->_destination = $destination;
    }
    public function setFileName($p_fileName) {
        $this->_fileName = $p_fileName;
    }

    public function receive()
    {
        if (!$this->isValid()) {
            throw new Exception('No file uploaded!');
        }
        $fileReader = fopen('php://input', "r");
        $fileWriter = fopen($this->_destination . $this->_fileName, "w+");
        while(true) {
            $buffer = fgets($fileReader, 4096);
            if (strlen($buffer) == 0) {
                fclose($fileReader);
                fclose($fileWriter);
                return true;
            }
            fwrite($fileWriter, $buffer);
        }
        return false;
    }
}