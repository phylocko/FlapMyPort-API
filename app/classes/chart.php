<?php

Class Chart
{
	private $timeline;
	function __construct($timeline)
	{
		$this->timeline = $timeline;
	}

	function image()
	{

		$height = 10;
		$width = 333;

		$img = imagecreatetruecolor($width, $height);

		$orange = imagecolorallocate($img, 255, 128, 0);
		$red = imagecolorallocate($img, 205, 51, 51);
		$green = imagecolorallocate($img, 196, 242, 179);
		$black = imagecolorallocate($img, 230, 230, 230);
		imagefill($img, 0, 0, $black);


		if($this->timeline !== null)
		{
			

			for($i = 0; $i <334; $i++)
			{
				$startPoint = $i;
				$endPoint = $startPoint + 1;
				$color = $orange;
				if($this->timeline[$i] == 'up')	$color = $green;
				if($this->timeline[$i] == 'down') $color = $red;
				if($this->timeline[$i] == 2) $color = $red;
				if($this->timeline[$i] > 2) $color = $orange;

				imagefilledrectangle($img,      $startPoint,     $height,        $endPoint,     0,      $color);

			}
		}

		header('Content-Type: image/png');
		imagepng($img);
		imagedestroy($img);
		
	}
}
?>
