<?php

/*
 * File: SimpleImage.php
 * Author: Simon Jarvis
 * Copyright: 2006 Simon Jarvis
 * Date: 08/11/06
 * Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details:
 * http://www.gnu.org/licenses/gpl.html
 */
class SimpleImage
{

    public $image;

    public $image_type;

    public $filename;
    
    function load($filename)
    {
     
          
        if(!file_exists($filename)) return;
         $this->filename = $filename;
      
        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        
        if ($this->image_type == IMAGETYPE_JPEG) {
            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {
            
            $this->image = imagecreatefromgif($filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            
            $this->image = imagecreatefrompng($filename);
        }
        
        $exif = false;
        try {   
            $exif = exif_read_data($this->filename);
            if(isset($exif['Orientation'])) {
                switch($exif['Orientation']) {
                    case 3:
                        $this->image = imagerotate($this->image, 180, 0);
                        break;
                    case 6:
                        $this->image = imagerotate($this->image, -90, 0);
                        break;
                    case 8:
                        $this->image = imagerotate($this->image, 90, 0);
                        break;
                }
                 
            }
        
        } catch(Exception $e) {
            
        }
        
        
        
        
    }

    function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        if(!$this->image) return;
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            
            imagepng($this->image, $filename);
        }
        if ($permissions != null) {
            
            chmod($filename, $permissions);
        }
    }

    function output($image_type = IMAGETYPE_JPEG)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {
            
            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {
            
            imagepng($this->image);
        }
    }

    function getWidth()
    {
        return imagesx($this->image);
    }

    function getHeight()
    {
        return imagesy($this->image);
    }

    function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }

    function scale($scale)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }

    function resize($width, $height)
    {
        
        if(!$this->filename) return;
        
        
        $new_image = imagecreatetruecolor($width, $height);
        
        imagealphablending($new_image, false);
        $colorTransparent = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
        imagefill($new_image, 0, 0, $colorTransparent);
        imagesavealpha($new_image, true);
         
        //imagefilledrectangle($new_image, 0, 0, $width, $height, $alpha);
        list ($orig_w, $orig_h) = getimagesize($this->filename);
        if ($orig_h > $orig_w) {
            $scale = $height/$orig_h;
        } else {
            $scale = $width/$orig_w;
        }
        $new_w =  $orig_w * $scale;
        $new_h =  $orig_h * $scale;
        
        $offset_x = ($width - $new_w) / 2.0;
        $offset_y = ($height - $new_h) / 2.0;
        
        if($offset_x>0) $offset_x-1;
        if($offset_y>0) $offset_y-1;
        
        
        imagecopyresampled($new_image, $this->image, $offset_x, $offset_y, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
        
        $this->image = $new_image;
    }
}
?>