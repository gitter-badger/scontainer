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
	
	private $_factories = array();
	
	private $_invokables = array();
	
	public function getMethodParams($class, $method ='__invoke')
	{
		$reflectionClass = new \ReflectionClass($class);
		if(!$reflectionClass->hasMethod($method))
		{
			return false;
		}
		$invokeMethod = $reflectionClass->getMethod($method);
		$parameters = $invokeMethod->getParameters();
		return $parameters;
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
		if(isset($this->_factories[$id]))
		{
			$this->invokeByFactory($id);
		}
		if(isset($this->_invokables[$id]))
		{
			$this->invokeByClass($id);
		}
		if(!$this->has($id))
		{
			throw new ServiceNotFoundException(sprintf('Container does not have service with name %s', $id));
		}
		$result = $this->_services[$id];
		return $result;
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
		return ( isset($this->_services[$id]) || isset($this->_factories[$id]) || isset($this->_invokables[$id]) );
	}
	
	public function setInvokable($key, $value, $overwrite = true)
	{
		if($overwrite == false && isset($this->_invokables[$key]))
		{
			throw new \Exception('Invokable already declared');
		}
		$this->_invokables[$key] = $value;
	}

	public function setFactory($key, $value, $overwrite = true)
	{
		if($overwrite == false && isset($this->_factories[$key]))
		{
			throw new \Exception('Factory already declared');
		}
		$this->_factories[$key] = $value;
	}

	public function invokeByFactory($id)
	{
		$factory = new $this->_factories[$id]();
		$reflectionParams = $this->getMethodParams($factory, '__invoke');
		$parameterList = array();
		foreach($reflectionParams as $parameter)
		{
			$parameterClass = $parameter->getClass();
			if($parameterClass != null)
			{
				$parameterList[$parameter->name] = $this->get($parameterClass->name);
			}
			else
			{
				$parameterList[$parameter->name] = $this->get($parameter->name);
			}
		}
		$reflectionClass = new \ReflectionClass($factory);
		$invokeMethod = $reflectionClass->getMethod('__invoke');
		$invoked = $invokeMethod->invokeArgs($factory, $parameterList);
		$this->setService($id, $invoked);
		return true;
	}

	public function invokeByClass($id)
	{
		$invokable = $this->_invokables[$id];
		$this->_services[$id] = new $invokable; 
		return true;
	}
	
	private function _setDependencies($configuration)
	{
		if(isset($configuration['dependencies']['factories']))
		{
			foreach($configuration['dependencies']['factories'] as $className => $factoryName)
			{
				$this->setFactory($className, $factoryName);
			}
		}
		if(isset($configuration['dependencies']['invokables']))
		{
			foreach($configuration['dependencies']['invokables'] as $interface => $implementation)
			{
				$this->setInvokable($interface, $implementation);
			}
		}

	}
	
	/**
	 * constructor.
	 * @access public
	 * @param $config
	 * @param SContainer|null $router
	 */
	public function __construct($configuration = array())
	{
		$this->setService('config', $configuration);
		$this->setService(ContainerInterface::class, $this);
		$this->setService(self::class, $this);
		$this->setService('path', '/');
		
		$this->_setDependencies($configuration);
		foreach($configuration as $config)
		{
			$this->_setDependencies($config);
		}
	}
	
	public function setService($key, $value)
	{
		$this->_services[$key] = $value;
	}
}
