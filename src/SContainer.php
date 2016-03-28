<?php

namespace GabiDJ\Expressive\SContainer;

use Interop\Container\ContainerInterface as ContainerInterface;
use Interop\Container\Exception;
use GabiDJ\Expressive\SContainer\Exception\ServiceNotFoundException;

class SContainer implements ContainerInterface
{
	/**
	 * 
	 * @var unknown $_services
	 */
	private $_services = array();
	
	/**
	 * 
	 * @var unknown
	 */
	private $_factories = array();
	
	/**
	 * 
	 * @var unknown
	 */
	private $_invokables = array();
	
	/**
	 * Injectable constructor.
	 * @access public
	 * @param $config 
	 * @param SContainer|null $router
	 */
	public function __construct($configuration = array(), SContainer $container = null)
	{
		if($container instanceof ContainerInterface)
		{
			return $container;
		}
		
		
		foreach($configuration['dependencies']['invokables'] as $interface => $implementation)
		{
			$this->_services[$interface] = new ServiceWrapper(new $implementation());
		}
		
		foreach($configuration['dependencies']['factories'] as $className => $factoryName)
		{
			$this->_services[$factoryName] = new $factoryName();
			$this->_services[$className] = new ServiceWrapper($this->_services[$factoryName]($this));
		}
		
	}
	
	public function __call($funcName, $params)
	{
		echo '<pre/>';
		echo $funcName;
		var_dump($params);
	}
	
	
	
	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @throws NotFoundException  No entry was found for this identifier.
	 * @throws ContainerException Error while retrieving the entry.
	 *
	 * @return mixed Entry.
	 */
	public function get($id)
	{
		if(!$this->has($id))
		{
			#exit('Container does not have service with name '.$id);
			throw new ServiceNotFoundException(sprintf('Container does not have service with name %s', $id));
		}
		$result = $this->_services[$id];
		return $result->getService();
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return boolean
	 */
	public function has($id)
	{
		if(isset($this->_services[$id]))
		{
			return true;
		}
		return false;
	}
	

	public function setService($serviceName, $service)
	{
		$this->_services[$serviceName] = $service;
	}
}