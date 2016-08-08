<?php

function di($t)
{
	//return mf() -$t;
	return round(mf() - $t, 3);
}
function mf()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function pr($arr)
{
	if (LOG_LEVEL==1)
	{
		echo "\r\n<pre class='pre-scrollable'>\r\n";
		print_r($arr);
		echo "\r\n</pre>\r\n";
	}
}
function pl($text = '')
{
	if (LOG_LEVEL==1)
	{
		echo "<code> " . $text . "</code><br>\r\n";
	}
}
function showError($error)
{
	return array("error"=>array("text"=>$error)) ;
}
?>
