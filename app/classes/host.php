<?php

Class Host extends defaultClass
{
	private $r;
	public $name;
	public $ipaddress;
	public $ports = array();
	public $timeStart;
	public $timeEnd;
	private $filter;

	function __construct($r)
	{
		$this->r = $r;
		$this->times = $this->r->get('times');
		$this->timeStart = $this->times->getTimeStartString();
		$this->timeEnd = $this->times->getTimeEndString();
	}

	public function setFilter($filter)
	{
		$this->filter = $filter;
	}

	public function fetchPorts()
	{
		$q = "SELECT `id`, `time` , `ifName`, `ifAlias`, `ifIndex`, `ifOperStatus` FROM `ports`
			WHERE `host` = '$this->ipaddress' AND `time` > '$this->timeStart'
			AND `time` < '$this->timeEnd'
			AND `ifName` not like '%.%'
			$this->filter
			GROUP BY `ifIndex`
			ORDER BY `time` DESC;";

		$db = $this->r->get('db');
		if(!$data = $db->query($q))
		{
			print_r($db->errorInfo());
		}

		while($d = $data->fetch(PDO::FETCH_NAMED))
		{
			$port                   = new Port($this->r);
			$port->ipaddress	= $this->ipaddress;
			$port->ifIndex          = $d['ifIndex'];
			$port->ifName           = $d['ifName'];
			$port->ifAlias          = $d['ifAlias'];
			$port->checkBlacklisted();

			$port->getFlapCount();
			$port->getFirstFlap();
			$port->getLastFlapAndOperStatusAndOldestFlapID();
			array_push($this->ports, $port);
		}

	}

	function fetchFlaps()
	{
		foreach ($this->ports as $port)
		{
			$port->fetchFlaps();
		}
	}
}

?>
