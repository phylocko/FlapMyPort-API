<?php

Class Registry
{
	private $vars = array();

	function set($key, $value)
	{
		if ( !isset($this->vars[$key]) )
		{
			$this->vars[$key] = $value;
		}
	}

	function get($key)
	{
		return $this->vars[$key];
	}

	function removeKey($key)
	{
		unset ($this->vars[$key]);
	}

}

?>
