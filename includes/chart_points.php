<?php

/**
*
* @package Football
* @version $Id: chart_points.php 1 2010-05-17 22:09:43Z football $
* @copyright (c) 2010 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


$matchdays = ( isset($_GET['m']) ) ? intval($_GET['m']) : 0;
$values1 = ( isset($_GET['v1']) ) ? $_GET['v1'] : 0;
$values2 = ( isset($_GET['v2']) ) ? $_GET['v2'] : 0;
$values3 = ( isset($_GET['v3']) ) ? $_GET['v3'] : 0;
$values4 = ( isset($_GET['v4']) ) ? $_GET['v4'] : 0;
$valuesmin = ( isset($_GET['min']) ) ? $_GET['min'] : 0;
$valuesmax = ( isset($_GET['max']) ) ? $_GET['max'] : 0;
$caption = ( isset($_GET['c']) ) ? $_GET['c'] : '';

$graphvalues1 = explode(",", $values1);
$graphvalues2 = explode(",", $values2);
$graphvalues3 = explode(",", $values3);
$graphvalues4 = explode(",", $values4);
$graphvaluesmin = explode(",", $valuesmin);
$graphvaluesmax = explode(",", $valuesmax);
$caption_lang = explode(",", $caption);

// Define .PNG image
header("Content-type: image/png");
$horz = 20;
$vert = 24;
$horzp = 4;
$maximum = max($graphvaluesmax);
$rows = (int) ($maximum / 5) + 2;
$maximum = ($rows - 1) * 5;
$imgWidth = $matchdays * $vert + 10;
$imgHeight = $rows * $horz + 50;

if ($imgWidth < 106)
	$imgWidth = 106;

// Create image and define colors
$image=imagecreate($imgWidth, $imgHeight);
$colorWhite=imagecolorallocate($image, 255, 255, 255);
$colorBlack=imagecolorallocate($image, 0, 0, 0);
$colorGrey=imagecolorallocate($image, 192, 192, 192);
$colorBlue=imagecolorallocate($image, 0, 0, 255);
$colorRed=imagecolorallocate($image, 255, 0, 0);
$colorGreen=imagecolorallocate($image, 0, 255, 0);
$colorAzur=imagecolorallocate($image, 0, 255, 255);

// Create grid
for ($i = 1; $i <= $rows; $i++)
{
	imageline($image, $vert, $i * $horz, $imgWidth - 10, $i * $horz, $colorGrey);
	if ($i > ($rows - 2))
		imagestring($image, 3, 10, $i * $horz - 6, $maximum - (($i - 1) * 5), $colorBlack);
	else
		imagestring($image, 3, 3,$i * $horz - 6, $maximum - (($i - 1) * 5), $colorBlack);
}
imagestring($image, 1, 0, 0, $caption_lang[0] , $colorBlack);


// Create grid
for ($i = 1; $i <= $matchdays; $i++)
{
	$label = $i + 1;
	imageline($image, $i * $vert, $horz, $i * $vert, $imgHeight - 50, $colorGrey);
	if ($i < 10)
		imagestring($image,3,$i * $vert - 3, $imgHeight - 40, $i, $colorBlack);
	else
		imagestring($image, 3, $i * $vert - 6, $imgHeight - 40, $i, $colorBlack);
}

imageline($image, 3, $imgHeight - $horz + 6, 15, $imgHeight - $horz + 6, $colorBlack);
imagestring($image, 3, 20, $imgHeight - $horz, $caption_lang[1] , $colorBlack);

imagesetthickness($image, 2);

$count_values=count($graphvalues1);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, (($maximum - $graphvalues1[$i - 1]) * $horzp + $horz), ($i + 1) * $vert, (($maximum - $graphvalues1[$i]) * $horzp + $horz), $colorBlue);
}

$count_values=count($graphvalues2);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, (($maximum - $graphvalues2[$i - 1]) * $horzp + $horz), ($i + 1) * $vert, (($maximum - $graphvalues2[$i]) * $horzp + $horz), $colorGreen);
}

$count_values=count($graphvalues3);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, (($maximum - $graphvalues3[$i - 1]) * $horzp + $horz), ($i + 1) * $vert, (($maximum - $graphvalues3[$i]) * $horzp + $horz), $colorAzur);
}

$count_values=count($graphvalues4);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, (($maximum - $graphvalues4[$i - 1]) * $horzp + $horz), ($i + 1) * $vert, (($maximum - $graphvalues4[$i]) * $horzp + $horz), $colorRed);
}

$count_values=count($graphvaluesmin);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, (($maximum - $graphvaluesmin[$i - 1]) * $horzp + $horz), ($i + 1) * $vert, (($maximum - $graphvaluesmin[$i]) * $horzp + $horz), $colorBlack);
}

$count_values=count($graphvaluesmax);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, (($maximum - $graphvaluesmax[$i - 1]) * $horzp + $horz), ($i + 1) * $vert, (($maximum - $graphvaluesmax[$i]) * $horzp + $horz), $colorBlack);
}

// Output graph and clear image from memory
imagepng($image);
imagedestroy($image);
