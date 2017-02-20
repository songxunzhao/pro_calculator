<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 12/12/2016
 * Time: 9:00 PM
 */
namespace App\Helpers;

class ImageResize
{
    var $set_img_max_width;
    var $set_img_max_height;
    var $img_new_width;
    var $img_new_height;
    var $mime;
    var $image;
    var $img_width;
    var $img_height;
    var $img_path;
    var $img_save_path;
    var $img;

    function image_path($img_path)
    {
        $this->img_path = $img_path;
    }
    function image_save_path($img_save_path)
    {
        $this->img_save_path = $img_save_path;
    }
    function set_img_max_width($img_width)
    {
        $this->set_img_max_width = $img_width;
    }
    function set_img_max_height($img_height)
    {
        $this->set_img_max_height = $img_height;
    }
    function get_mime()
    {
        $img_data = getimagesize($this->img_path);
        $this->mime = $img_data['mime'];
    }
    function image_create()
    {
        switch($this->mime)
        {
            case 'image/jpeg':
                $this->image = imagecreatefromjpeg($this->img_path);
                break;

            case 'image/gif':
                $this->image = imagecreatefromgif($this->img_path);
                break;

            case 'image/png':
                $this->image = imagecreatefrompng($this->img_path);
                break;
        }
    }
    function img_resize()
    {
        set_time_limit(0);
        $this->get_mime();
        $this->image_create();
        $this->img_width = imagesx($this->image);
        $this->img_height = imagesy($this->image);
        $this->img_set_dimension();
        $resized_image = imagecreatetruecolor($this->img_new_width,$this->img_new_height);
        imagecopyresampled($resized_image, $this->image, 0, 0, 0, 0, $this->img_new_width, $this->img_new_height,$this->img_width, $this->img_height);
        imagejpeg($resized_image,$this->img_save_path);

    }


    function img_set_dimension()
    {

        if($this->img_width==$this->img_height)
        {
            $case = 'c1';
        }
        elseif($this->img_width > $this->img_height)
        {
            $case = 'c2';
        }
        else
        {
            $case = 'c3';
        }



        if($this->img_width>$this->set_img_max_width && $this->img_height>$this->set_img_max_height)
        {
            $cond = 'c1';
        }
        elseif($this->img_width>$this->set_img_max_width && $this->img_height<=$this->set_img_max_height)
        {
            $cond = 'c1';
        }
        else
        {
            $cond = 'c3';
        }

        switch($case)
        {
            case 'c1':
                $this->img_new_width = $this->set_img_max_width;
                $this->img_new_height = $this->set_img_max_height;
                break;
            case 'c2':
                $img_ratio = $this->img_width/$this->img_height;
                $amount = $this->img_width - $this->set_img_max_width;
                $this->img_new_width = $this->img_width - $amount;
                $this->img_new_height = $this->img_height - ($amount/$img_ratio);
                break;
            case 'c3':
                $img_ratio = $this->img_height/$this->img_width;
                $amount = $this->img_height - $this->set_img_max_height;
                $this->img_new_height = $this->img_height - $amount;
                $this->img_new_width = $this->img_width - ($amount/$img_ratio);
                break;
        }

    }
}
