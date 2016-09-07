<?php

Class Port extends defaultClass
{
	private $r;
	public $timeStart;
	public $timeEnd;
	public $ipaddress;
	public $ifAlias;
	public $ifName;
	public $ifIndex;
	public $ifOperStatus = "?";
	public $lastFlapTime;
	public $firstFlapTime;
	public $isBlacklisted = false;
	public $flapCount;
	public $flaps = array();
	public $oldestFlapID = 0;
	public $raw = array();

	function __construct($r, $raw)
	{
		$this->r = $r;
		$this->raw = $raw;
		$this->times = $this->r->get('times');
		$this->timeStart = $this->times->getTimeStartString();
		$this->timeEnd = $this->times->getTimeEndString();
	}

	public function checkBlacklisted()
	{
		$this->isBlacklisted = $this->isBlacklisted();
	}

	public function getFlapCount()
	{
		$i = 0;

		foreach ($this->raw as $rawString)
		{
			if ($rawString['host'] == $this->ipaddress AND $rawString['ifIndex'] == $this->ifIndex)
			{
				$i++;
			}
		}

		$this->flapCount = (string) $i;
	}
	public function getFirstFlap()
	{

		$minDate = 0;

		foreach ($this->raw as $rawString)
		{
			if($rawString['host'] == $this->ipaddress AND $rawString['ifIndex'] == $this->ifIndex)
			{
				$date = new DateTime($rawString['time']);
				if ( is_numeric($minDate) || $minDate->getTimestamp() > $date->getTimestamp())
				{
					$minDate = $date;
					$this->firstFlapTime = $rawString['time'];
				}
			}
		}
	}

	public function getLastFlapAndOperStatusAndOldestFlapID()
	{

		$maxDate = 0;
		$maxID = 0;

		foreach ($this->raw as $rawString)
		{
			if($rawString['host'] == $this->ipaddress AND $rawString['ifIndex'] == $this->ifIndex)
			{
				$date = new DateTime($rawString['time']);
				if($rawString['id'] > $maxID)
				{
					$maxID = $rawString['id'];
					$maxDate = $date;
					$this->lastFlapTime = $rawString['time'];
					$this->ifOperStatus = $rawString['ifOperStatus'];
					if ($rawString['id'] > $this->oldestFlapID )
					{
						$this->oldestFlapID = $rawString['id'];
					}
				}
			}
		}

	}
	
	public function fetchFlaps()
	{
		$q = "SELECT `time`, `ifOperStatus` FROM `ports`
			WHERE `ifIndex` = '$this->ifIndex'
			AND `host` = '$this->ipaddress'
			AND `time` > '$this->timeStart'
			AND `time` < '$this->timeEnd' 
			AND `ifName` not like '%.%' ORDER BY `time` ASC ;";

		$db = $this->r->get('db');
		if(!$data = $db->query($q))
		{
			print_r($db->errorInfo());
		}

		while($d = $data->fetch(PDO::FETCH_NAMED))
		{
			$flap = new Flap($this->r);
			$flap->time = $d['time'];
			$flap->ifOperStatus = $d['ifOperStatus'];
			array_push($this->flaps, $flap);
		}

	}
	public function flapChart()
	{
		$this->showChart($this->getTimeline());
	}
	private function showChart($timeline)
	{
		include "app/classes/chart.php";
		$chart = new Chart($timeline);
		$chart->image();
	}
	private function getTimeline()
	{
		if(count($this->flaps)==0)
		{
			return null;
		}

		$this->percentageFlaps();

		$timeline = array();

		$timeline[0] = $this->flaps[0]->ifOperStatus=="down" ? "up" : "down";

		$candidate = $timeline[0];

		for ($i = 1; $i <= 333; $i++)
		{
			$currentFlaps = array();
			$currentFlaps = $this->getFlapsByPercent($i);

			if(count($currentFlaps) == 0)
			{
				$timeline[$i] = $candidate;
			}
			else if(count($currentFlaps) == 1)
			{
				$timeline[$i] = $currentFlaps[0]->ifOperStatus;
				$candidate = $currentFlaps[0]->ifOperStatus;
			}
			else
			{
				
				$timeline[$i] = count($currentFlaps);
				$candidate = $currentFlaps[$timeline[$i]-1]->ifOperStatus;
			}
		}
		return $timeline;
	}
	function getFlapsByPercent($i)
	{
		$flaps = array();
		foreach ($this->flaps as $flap)
		{
			if($flap->percent == $i)
			{
				array_push($flaps, $flap);
			}
		}
		return $flaps;
	}
	function percentageFlaps()
	{

		$utimeStart = strtotime($this->timeStart);
		$utimeEnd = strtotime($this->timeEnd);

		$period = $utimeEnd - $utimeStart;

		$this->period = $period;
		$this->period = $period;

		$percent = $period / 333;
		$this->percent = $percent;

		foreach ($this->flaps as $flap)
		{
			$flap->percentage($period, $percent, $utimeStart);
		}
	}
	function showFlaps()
	{
		$flaps_arr = array();
		foreach($this->flaps as $flap)
		{
			$flap_arr = array('time'=>$flap->time, 'ifOperStatus'=>$flap->ifOperStatus);
			array_push($flaps_arr, $flap_arr);
		}
		return $flaps_arr;
	}

	private function isBlacklisted()
	{
		$q = "select COUNT(`id`) from `blacklist` WHERE `host`='$this->ipaddress' AND `ifIndex`='$this->ifIndex';";

		$db = $this->r->get('db');
		$data = $db->query($q);

		return ($data->fetchColumn()==0) ? false : true;
	}
}

?>
