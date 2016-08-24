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

	function __construct($r, $raw)
	{
		$this->r = $r;
		$this->raw = $raw;
		$this->times = $this->r->get('times');
		$this->timeStart = $this->times->getTimeStartString();
		$this->timeEnd = $this->times->getTimeEndString();
	}

	public function setFilter($filter)
	{
		$this->filter = $filter;
	}

	private function portAlreadyExists($rawString, $ports_array)
	{
		foreach ($ports_array as $port_array)
		{
			if( $port_array['ifIndex'] == $rawString['ifIndex'])
			{
				return true;
			}
		}
		return false;

	}

	public function fetchPorts()
	{

		$ports_array = array();

		foreach ($this->raw as $rawString)
		{
			if($rawString['host'] == $this->ipaddress)
			{
				if ($this->portAlreadyExists($rawString, $ports_array) == false)
				{
					$ports_array[] = $rawString;
				}
			}
		}

		foreach ( $ports_array as $d)
		{
			$port                   = new Port($this->r, $this->raw);
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
