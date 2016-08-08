<?php
Class Flap extends defaultClass
{
	public $time;
	public $ifOperStatus;

	function percentage($period, $percent, $utimeStart)
	{
		$utime = strtotime($this->time) - $utimeStart;
		$this->percent = floor($utime / $percent);
	}
}

?>
