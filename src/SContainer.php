<?php

namespace GabiDJ\Expressive\SContainer;

use Interop\Container\ContainerInterface as ContainerInterface;
use Interop\Container\Exception;
use GabiDJ\Expressive\SContainer\Exception\ServiceNotFoundException;

class SContainer implements ContainerInterface
{
	/**
	 * The array containing the services
	 * @var unknown $_services
	 */
	private $_services = array();
	
	/**
	 * The array containing the services factories (lazyload)
	 * @var array
	 */
	private $_factories = array();
	
	/**
	 * The array containing the invokable services name (lazyload)
	 * @var array
	 */
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
		if(!isset($this->_services[$id]))
		{
			if(isset($this->_factories[$id]))
			{
				$this->invokeByFactory($id);
				unset($this->_factories[$id]);
			}
			if(isset($this->_invokables[$id]))
			{
				$this->invokeByClass($id);
				unset($this->_invokables[$id]);
			}
		}
		if(!$this->has($id))
		{
			throw new ServiceNotFoundException(sprintf('Container does not have service with name %s',$id));
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
	
	/**
	 * Add invokable service to be loaded
	 * @param string $key
	 * @param mixed $value
	 * @param bool $overwrite
	 * @throws \Exception
	 */
	public function setInvokable($key, $value, $overwrite = true)
	{
		if($overwrite == false && isset($this->_invokables[$key]))
		{
			throw new \Exception('Invokable already declared');
		}
		$this->_invokables[$key] = $value;
	}
	
	/**
	 * Add invokable service to be loaded via its Factory
	 * @param string $key
	 * @param mixed $value
	 * @param bool $overwrite
	 * @throws \Exception
	 */
	public function setFactory($key, $value, $overwrite = true)
	{
		if($overwrite == false && isset($this->_factories[$key]))
		{
			throw new \Exception('Factory already declared');
		}
		$this->_factories[$key] = $value;
	}

	/**
	 * Invoke service using factory
	 * @param string $id (also reffered as $key in other methods) 
	 * @throws \Exception
	 */
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

	/**
	 * Invoke service by creating a new instance
	 * @param string $id (also reffered as $key in other methods)
	 * @throws \Exception
	 */
	public function invokeByClass($id)
	{
		$invokable = $this->_invokables[$id];
		$this->_services[$id] = new $invokable; 
		return true;
	}
	
	/**
	 * Parse configuration array to add dependencies to be loaded
	 * @param array $configuration
	 */
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
		
		$this->_setDependencies($configuration);
		foreach($configuration as $config)
		{
			$this->_setDependencies($config);
		}
	}
	
	/**
	 * Add service to service array
	 * @param string $key
	 * @param mixed $value
	 * @return bool $success
	 */
	public function setService($key, $value, $overwrite = true)
	{
		if($overwrite == true || !isset($this->_services[$key]))
		{
			$this->_services[$key] = $value;
			return true;
		}
		return false;
	}
}
