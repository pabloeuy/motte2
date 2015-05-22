<?php
/**
 * mteImage
 *
 * @filesource
 * @package motte
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */

define('IMAGE_COMPRESSION', 90);
include_once(DIR_MOTTE.'/lib/simpleImage.php');

class mteImage {

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

	public static function resizeToWidth($scr, $target, $w) {
		$image = new SimpleImage();
		$image->load($scr);
		$image->resizeToWidth($w);
		$image->save($target, IMAGETYPE_JPEG, IMAGE_COMPRESSION, 0666);
	}

	public static function getWidth($scr) {
		$image = new SimpleImage();
		$image->load($scr);
		return $image->getWidth();
	}

	public static function getHeight($scr) {
		$image = new SimpleImage();
		$image->load($scr);
		return $image->getHeight();
	}

	public static function resize($scr, $target, $w, $h) {
		$image = new SimpleImage();
		$image->load($scr);
		$image->resize($w, $h);
		$image->save($target, IMAGETYPE_JPEG, IMAGE_COMPRESSION, 0666);
	}

	public static function cropImage($file, $width, $height) {
		$image = new SimpleImage();
		$image->load($file);
		if ($image->getWidth() != $width || $image->getHeight() != $height) {
			$imagick = new Imagick($file);
			$imagick->cropThumbnailImage($width, $height);
			$imagick->enhanceImage();
			$imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
			$imagick->setImageCompressionQuality(IMAGE_COMPRESSION);
			$imagick->writeImage($file);
		}
	}
}
?>