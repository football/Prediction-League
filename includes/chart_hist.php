<?php

/**
*
* @package Football
* @version $Id: chart_hist.php 1 2010-05-17 22:09:43Z football $
* @copyright (c) 2010 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

$values1 = ( isset($_GET['v1']) ) ? $_GET['v1'] : 0;
$graphvalues1 = explode(",",$values1);
$matchdays = sizeof($graphvalues1);

// Define .PNG image
header("Content-type: image/png");
$horz = 15;
$vert = 9;
$start = 25;
$imgWidth= $matchdays * $vert + 20;
$imgHeight=90;

// Create image and define colors
$image 				= imagecreate($imgWidth, $imgHeight);
$colorBackground	= imagecolorallocate($image, 236, 240, 246);
$colorWhite			= imagecolorallocate($image, 255, 255, 255);
$colorBlack			= imagecolorallocate($image, 0, 0, 0);
$colorGrey			= imagecolorallocate($image, 106, 106, 106);
$colorBlue			= imagecolorallocate($image, 0, 0, 255);
$colorRed			= imagecolorallocate($image, 176, 0, 0);
$colorGreen			= imagecolorallocate($image, 0, 176, 0);

imagefill($image, 0, 0, $colorBackground);

imageline($image, 0, 45, $imgWidth, 45, $colorGrey);
imagestring($image,4, 5, 15, 'H', $colorBlack);
imagestring($image,4, 5, 60, 'A', $colorBlack);

imagesetthickness($image, 5);

$count_values=count($graphvalues1);
// Create line graph
for ($i = 0; $i < $count_values; $i++)
{
	if (substr($graphvalues1[$i],0,1) == '-')
	{
		imageline($image, $start + ($i * $vert), 45, $start + ($i * $vert), substr($graphvalues1[$i], 1, strlen($graphvalues1[$i]) - 1), $colorRed);
	}
	else
	{
		if (substr($graphvalues1[$i],0,1) == ' ')
		{
			imageline($image, $start + ($i * $vert), 45, $start + ($i * $vert), substr($graphvalues1[$i], 1, strlen($graphvalues1[$i]) - 1), $colorGreen);
		}
		else
		{
			imageline($image, $start + ($i * $vert), 45, $start + ($i * $vert), substr($graphvalues1[$i], 0, strlen($graphvalues1[$i])), $colorGrey);
		}
	}
}

// Output graph and clear image from memory
imagepng($image);
imagedestroy($image);
?>
