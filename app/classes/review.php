<?php
Class Review extends defaultClass
{
	public  $type;
	public  $params= array();
	private $hosts = array();
	private $timeStart;
	private $timeEnd;
	private $r;
	private $filter;

	function __construct($r)
	{
		$this->r = $r;
		$this->times = $this->r->get('times');
		$this->timeStart = $this->times->getTimeStartString();
		$this->timeEnd = $this->times->getTimeEndString();

		$this->params['timeStart'] = $this->times->getTimeStartString();
		$this->params['timeEnd'] = $this->times->getTimeEndString();
		$this->params['oldestFlapID'] = 0;
	}

	public function setFilter($f)
	{
		$f = preg_replace("/[^A-Za-z0-9\/\-\:\.\_]/", '', $f);
		$this->filter = "AND ( `ifName` LIKE '%$f%' OR `ifAlias` LIKE '%$f%' OR `hostname` LIKE '%$f%' OR host LIKE '%$f%' )";
		$this->params['filter'] = $f;
	}

	private function findFlapTimes()
	{

		if( count($this->hosts) == 0 )
		{
			return;
		}

		$startCandidate = Null;
		$endCandidate   = DateTime::createFromFormat('U', 0);

		foreach ($this->hosts as $host)
		{
			foreach($host->ports as $port)
			{
				$start = DateTime::createFromFormat('Y-m-d H:i:s', $port->firstFlapTime);
				$end   = DateTime::createFromFormat('Y-m-d H:i:s', $port->lastFlapTime);
				
				if( $end > $endCandidate )
				{
					$endCandidate = $end;
				}

				if( $startCandidate == Null || $start < $startCandidate )
				{
					$startCandidate = $start;
				}

				if($port->oldestFlapID > $this->params['oldestFlapID'])
				{
					$this->params['oldestFlapID'] = $port->oldestFlapID;
				}
			}
		}

		$this->params['firstFlapTime'] = $startCandidate == Null ? "" : $startCandidate->format('Y-m-d H:i:s');
		$this->params['lastFlapTime'] = $endCandidate->format('Y-m-d H:i:s');
	}

	public function getHosts()
	{
		$timeStart = $this->times->getTimeStartString();
		$timeEnd = $this->times->getTimeEndString();

		$q = "SELECT `hostname`, `host` 
			FROM `ports` WHERE `time` > '$timeStart' AND `time` < '$timeEnd'
			AND `ifName` not like '%.%'
			$this->filter
			GROUP BY `host`;";


		$db = $this->r->get('db');
		if(!$data = $db->query($q))
		{
			print_r($db->errorInfo());
		}

		while($d = $data->fetch(PDO::FETCH_NAMED))
		{
			$host = new Host($this->r);
			$host->setFilter($this->filter);
			$host->name = $d['hostname'];
			$host->ipaddress = $d['host'];
			array_push($this->hosts, $host);
		}

	}
	function fetchHostsPorts()
	{
		foreach ($this->hosts as $host)
		{
			$host->fetchPorts();
		}
		$this->findFlapTimes();
	}
	function fetchFlaps()
	{
		foreach ($this->hosts as $host)
		{
			$host->fetchFlaps();
		}
	}
	function showHosts()
	{
		$review_arr = array();

		$hosts_arr = array();

		foreach ($this->hosts as $host)
		{
			$host_arr = array();
			$host_arr['name']       = $host->name;
			$host_arr['ipaddress']  = $host->ipaddress;

			$ports_arr = array();
			foreach ($host->ports as $port)
			{
				$port_arr = array();
				$port_arr['ifIndex'] = $port->ifIndex;
				$port_arr['ifName'] = $port->ifName;
				$port_arr['ifAlias'] = $port->ifAlias;
				$port_arr['ifOperStatus'] = $port->ifOperStatus;
				$port_arr['flapCount'] = $port->flapCount;
				$port_arr['firstFlapTime'] = $port->firstFlapTime;
				$port_arr['lastFlapTime'] = $port->lastFlapTime;
				$port_arr['isBlacklisted'] = $port->isBlacklisted;

				$flaps_arr = array();
				foreach ($port->flaps as $flap)
				{
					$flap_arr = array();
					$flap_arr['time'] = $flap->time;
					$flap_arr['ifOperStatus'] = $flap->ifOperStatus;
					$flaps_arr[] = $flap_arr;
				}

				$port_arr['flaps'] = $flaps_arr;

				$ports_arr[] = $port_arr;
			}
			$host_arr['ports'] = $ports_arr;

			// Push the host to array
			$hosts_arr[] = $host_arr;

		}

		$review_arr['params'] = $this->params;
		$review_arr['hosts'] = $hosts_arr;
		return $review_arr;
	}
}

?>
