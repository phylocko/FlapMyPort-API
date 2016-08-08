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

	function __construct($r)
	{
		$this->r = $r;
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
		$q = "SELECT COUNT(id) FROM `ports` 
			WHERE `ifIndex` = '$this->ifIndex'
			AND `host` = '$this->ipaddress'
			AND `time` > '$this->timeStart' AND `time` < '$this->timeEnd';";

		$db = $this->r->get('db');
		$data = $db->query($q);
		$this->flapCount = $data->fetchColumn();
	}
	public function getFirstFlap()
	{
		$q = "SELECT `time` FROM `ports` 
			WHERE `ifIndex` = '$this->ifIndex'
			AND `host` = '$this->ipaddress'
			AND `time` > '$this->timeStart'
			ORDER BY `time` ASC LIMIT 1;";

		$db = $this->r->get('db');
		$data = $db->query($q);
		$d = $data->fetch(PDO::FETCH_NAMED);
		$this->firstFlapTime = $d['time'];
	}

	public function getLastFlapAndOperStatusAndOldestFlapID()
	{
		$q = "SELECT `id`,`time`, `ifOperStatus` FROM `ports` 
			WHERE `ifIndex` = '$this->ifIndex'
			AND `host` = '$this->ipaddress'
			AND `time` < '$this->timeEnd'
			ORDER BY `time` DESC LIMIT 1;";

		$db = $this->r->get('db');
		$data = $db->query($q);
		$d = $data->fetch(PDO::FETCH_NAMED);
		$this->lastFlapTime = $d['time'];
		$this->ifOperStatus = $d['ifOperStatus'];
		if($d['id'] > $this->oldestFlapID)
		{
			$this->oldestFlapID = $d['id'];
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
