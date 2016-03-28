<?php

namespace GabiDJ\Expressive\SContainer;

class ServiceWrapper
{
	
	private $_service;
	public function __construct($service)
	{
		$this->_service = $service;
	}
	public function getService()
	{
		return $this->_service;
	}
	
	public function __invoke()
	{
		return $this->_service();
	}
}