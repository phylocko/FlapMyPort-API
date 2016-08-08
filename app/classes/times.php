<?php

Class Times
{
	private $timeStart;
	private $timeEnd;

	function __construct()
	{

		$this->setDefaultTImes();

		// 'interval' has higher priority than 'start' and 'and' do;
		if($this->pickInterval())
		{
			return;
		}

		$this->pickStartTimeFromGet();
		$this->pickEndTimeFromGet();
	}

	private function setDefaultTImes()
	{
		// Default starttime is on hour before now;
		$this->timeStart = new DateTime();
		$this->timeStart->modify('-1 hour');

		// Default endtime is now;
		$this->timeEnd        = new DateTime();
	}
	private function pickInterval()
	{
		if( isset($_GET['interval']) AND is_numeric($_GET['interval']))
		{
			$interval = $_GET['interval'];

			if ($interval >= 1 AND $interval < 31104000)
			{
				$timeStart = new DateTime();
				$dateInterval = new DateInterval('PT'.$interval.'S');
				$this->timeStart = $timeStart->sub($dateInterval);
				return true;
			}
		}
		return false;
	}

	private function pickStartTimeFromGet()
	{
		if(isset($_GET['start']))
		{
			if( $this->dateCorrect($_GET['start']) )
			{
				if($timeStart = strtotime($_GET['start']))
				{
					$this->timeStart->setTimestamp($timeStart);
				}
			}
		}

		return false;
	}

	private function pickEndTimeFromGet()
	{

		if(isset($_GET['end']))
		{
			if( $this->dateCorrect($_GET['end']) )
			{
				if($timeEnd = strtotime($_GET['end']))
				{
					$this->timeEnd->setTimestamp($timeEnd);
				}
			}
		}

		return false;
	}

	public function getTimeStartString()
	{
		return $this->timeStart->format("Y-m-d H:i:s");
	}
	public function getTimeEndString()
	{
		return $this->timeEnd->format("Y-m-d H:i:s");
	}
	private function dateCorrect()
	{
		// Don't forget to implement the method;
		return true;
	}

}
?>
