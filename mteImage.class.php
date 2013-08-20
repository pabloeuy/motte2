<?php
/**
 * mteCrud
 *
 * @filesource
 * @package motte
 * @subpackage app
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */

define('IMAGE_UPLOAD', 'upload');
define('MAX_IMAGE_DIR', 1000);
define('IMAGE_COMPRESSION', 90);
include_once(DIR_MOTTE.'/lib/simpleImage.php');

class mteImage {

	public static function prepareImageDir($prefix, $id, $modo = '') {
		$divisor = (int)($id/MAX_IMAGE_DIR);

		$dirs = array();
		$dirs[] = DIR_DATA.'/'.$prefix.'/'.$divisor;
		if ($modo != '') {
			$dirs[] = DIR_DATA.'/'.$prefix.'/'.$modo.'/'.$divisor;
		}

		// preparo
		foreach ($dirs as $dir) {
			if (!is_dir($dir)) {
				mkdir($dir, 0775, true);
			}
		}
	}

	public static function getImageBase($prefix, $id) {
		$file = mteImage::getTargetFile($prefix, $id);
		return (is_readable($file) && is_file($file))?$file:'';
	}

	public static function getImageModo($prefix, $id, $modo, $crop = true) {
		$file = mteImage::getTargetFile($prefix, $id, $modo);
		if (!is_readable($file) || !is_file($file)) {
			$imageBase = mteImage::getImageBase($prefix, $id);
			if ($imageBase != '') {
				// resize image
				mteImage::prepareImageDir($prefix, $id, $modo);
				$modo   = explode('x', strtolower($modo));
				$width  = $modo[0];
				$height = $modo[1];
				mteImage::resizeToHeight($imageBase, $file, $height);

				if (is_readable($file) && is_file($file)) {
					// crop
					$image = new SimpleImage();
    				$image->load($file);					
					if ($crop && ($image->getWidth() != $width || $image->getHeight() != $height)) {
						$imagick = new Imagick($file);
						$imagick->cropThumbnailImage($width, $height);
						$imagick->enhanceImage();
						$imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
						$imagick->setImageCompressionQuality(90);
						$imagick->writeImage($file);
					}
            	}
			}
		}
		return (is_readable($file) && is_file($file))?$file:'';
	}

	public static function getTargetFile($prefix, $id, $modo = '') {
		$result = '';
		if ($modo != '') {
			mteImage::prepareImageDir($prefix, $id, $modo);
			$result = DIR_DATA.'/'.$prefix.'/'.$modo.'/'.(int)($id/MAX_IMAGE_DIR).'/'.$id.'.jpg';
		}
		else {
			if ($id == 0) {
				$result = DIR_DATA.'/'.$prefix.'/0.jpg';
			}
			else {
				mteImage::prepareImageDir($prefix, $id);
				$result = DIR_DATA.'/'.$prefix.'/'.(int)($id/MAX_IMAGE_DIR).'/'.$id.'.jpg';
			}
		}
		return $result;
	}

	public static function delImage($file) {
		@unlink($file);
	}

	public static function imageResizeToMax($scr, $target, $w, $h) {
		$image = new SimpleImage();
    	$image->load($scr);
    	$image->resizeToMax($w, $h);
    	$image->save($target, IMAGETYPE_JPEG, IMAGE_COMPRESSION, 0666);
	}

	public static function resizeToHeight($scr, $target, $h) {
		$image = new SimpleImage();
    	$image->load($scr);
    	$image->resizeToHeight($h);
    	$image->save($target, IMAGETYPE_JPEG, IMAGE_COMPRESSION, 0666);
	}
	

	public static function uploadImage($prefix, $id, $file = 'Filedata') {
        $result = __("General error");
        if (!empty($_FILES)) {
            $fileTypes = array('jpg');
            $fileParts = pathinfo($_FILES['Filedata']['name']);

            if (in_array(strtolower($fileParts['extension']),$fileTypes)) {
                $targetFile = mteImage::getTargetFile($prefix, $id);
                mteImage::delImage($targetFile);
                move_uploaded_file($_FILES['Filedata']['tmp_name'], $targetFile);
                if (is_readable($targetFile)) {
                    $result = 1;
                }
                else {
                    $result = __("Move_up error");
                }
            }
            else {
                $result = __("Image type error");
            }
        }
        return $result;
    }
}
?>