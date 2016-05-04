<?php

/**
*
* @package Football
* @version $Id: chart_rank.php 1 2010-05-17 22:09:43Z football $
* @copyright (c) 2010 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

$numb_users = ( isset($_GET['t']) ) ? intval($_GET['t']) : 0;
$matchdays 	= ( isset($_GET['m']) ) ? intval($_GET['m']) : 0;
$Values1 = ( isset($_GET['v1']) ) ? $_GET['v1'] : 0;
$Values2 = ( isset($_GET['v2']) ) ? $_GET['v2'] : 0;
$Values3 = ( isset($_GET['v3']) ) ? $_GET['v3'] : 0;
$Values4 = ( isset($_GET['v4']) ) ? $_GET['v4'] : 0;
$caption = ( isset($_GET['c']) ) ? $_GET['c'] : '';

$graphValues1 = explode(",",$Values1);
$graphValues2 = explode(",",$Values2);
$graphValues3 = explode(",",$Values3);
$graphValues4 = explode(",",$Values4);

// Define .PNG image
header("Content-type: image/png");
$horz = 15;
$vert = 24;
$imgWidth=$matchdays * $vert + 10;
$imgHeight=$numb_users * $horz + 20;

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
for ($i = 1; $i <= $numb_users; $i++)
{
	imageline($image, $vert, $i * $horz, $imgWidth - 10, $i * $horz, $colorGrey);
	if ($i < 10)
		imagestring($image,3,10,$i * $horz - 6,$i,$colorBlack);
	else
		imagestring($image,3,3,$i * $horz - 6,$i,$colorBlack);
}
imagestring($image, 1, 0, 0, $caption, $colorBlack);

// Create grid
for ($i = 1; $i <= $matchdays; $i++)
{
	$label = $i + 1;
	imageline($image, $i * $vert, $horz, $i * $vert, $imgHeight - 20, $colorGrey);
	if ($i < 10)
		imagestring($image, 3,$i * $vert - 3,$imgHeight - $horz, $i, $colorBlack);
	else
		imagestring($image, 3,$i * $vert - 6,$imgHeight - $horz, $i, $colorBlack);
}

imagesetthickness($image, 2);

$count_values=count($graphValues1);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, ($graphValues1[$i - 1] * $horz), ($i + 1) * $vert, ($graphValues1[$i] * $horz), $colorBlue);
}

$count_values=count($graphValues2);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, ($graphValues2[$i - 1] * $horz), ($i + 1) * $vert, ($graphValues2[$i] * $horz), $colorGreen);
}

$count_values=count($graphValues3);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, ($graphValues3[$i - 1] * $horz), ($i + 1) * $vert, ($graphValues3[$i] * $horz), $colorAzur);
}

$count_values=count($graphValues4);
// Create line graph
for ($i = 1; $i < $count_values; $i++)
{
	imageline($image, $i * $vert, ($graphValues4[$i - 1] * $horz), ($i + 1) * $vert, ($graphValues4[$i] * $horz), $colorRed);
}

// Output graph and clear image from memory
imagepng($image);
imagedestroy($image);
?>
