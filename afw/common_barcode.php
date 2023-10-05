<?php

function generate_barcode() 
{
	return substr(number_format(time() * mt_rand(),0,'',''),0,13);
}

function printBarcode($code) 
{
        $file_dir_name = dirname(__FILE__);
        include_once("$file_dir_name/../inc/php-barcode-2.0.3/php/php-barcode.php");
	
	$fontSize = 10;   // GD1 in px ; GD2 in point
	$marge    = 10;   // between barcode and hri in pixel
	$x        = 125;  // barcode center
	$y        = 125;  // barcode center
	$height   = 50;   // barcode height in 1D ; module size in 2D
	$width    = 2;    // barcode height in 1D ; not use in 2D
	$angle    = 0;   // rotation in degrees : nb : non horizontable barcode might not be usable because of pixelisation

	$code = !empty($code) ? $code : 12345678; // assign default value
	$type     = 'code128';

	$im     = imagecreatetruecolor(300, 300);
	$black  = ImageColorAllocate($im,0x00,0x00,0x00);
	$white  = ImageColorAllocate($im,0xff,0xff,0xff);
	$red    = ImageColorAllocate($im,0xff,0x00,0x00);
	$blue   = ImageColorAllocate($im,0x00,0x00,0xff);
	imagefilledrectangle($im, 0, 0, 300, 300, $white);

	$data = Barcode::gd($im, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);

	ob_start();
	header('Content-Type: image/gif');
	imagegif($im);
	imagedestroy($im);  
	$i = ob_get_clean();

	$return = "<img src='data:image/jpeg;base64," . base64_encode( $i )."' />";
	$return .= "\n" . $code;

	return $return;
}
?>