<?php

include "config.php";
include "app/functions.php";
include "app/classes/registry.php";

if(!$db = new PDO('mysql:host=localhost;dbname=' . $dbname, $dbuser, $dbpassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")))
{
	echo "DB Error;";
}

$r = new Registry;
$r->set('db', $db);

include "app/classes/times.php";
$times = new Times();
$r->set('times', $times);

Abstract Class defaultClass
{
}

include "app/classes/flap.php";
include "app/classes/port.php";
include "app/classes/host.php";
include "app/classes/review.php";

$fr = new Review($r);

$output = "";

if(isset($_GET['review']))
{
	if(isset($_GET['filter']) AND !empty($_GET['filter']))
	{
		$fr->setFilter($_GET['filter']);
	}

	$fr->getHosts();
	
	$fr->fetchHostsPorts();
	// $fr->fetchFlaps();
	$output = $fr->showHosts();
	

}
else
if(isset($_GET['flapchart']) || isset($_GET['flaphistory']) )
{
	if(isset($_GET['ifindex']) AND is_numeric($_GET['ifindex']))
	{
		if(isset($_GET['host']))
		{
			$ifIndex = $_GET['ifindex'];
			$port = new Port($r);
			$port->ipaddress = $_GET['host'];
			$port->ifIndex = $ifIndex;
			$port->fetchFlaps();
			if ( isset($_GET['flapchart']) )
			{
				$port->flapChart();
			}
			else
			{
				$output = $port->showFlaps();
			}
		}
		else
		{
			$output = showError('Wrong/missing host');
		}
	}
	else
	{
		$output = showError('Wrong/missing ifindex');
	}

}
else
if(isset($_GET['check']))
{
	sleep(2);
	$output = array('checkResult' => "flapmyport");
}

if(isset($_GET['format']))
{
	$format = $_GET['format'];
	switch($format)
	{
		case "json":
			echo json_encode($output);
		break;

		case "text":
			print_r($output);
		break;

		default:
			echo json_encode($output);
		break;
	}
}
else
{
	echo json_encode($output);
}

?>
