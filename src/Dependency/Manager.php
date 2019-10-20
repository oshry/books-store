<?php
namespace Src\Dependency;

use Exception;

trait Manager {

	protected $dependencies;

	public function set($key, $value)
	{
		$this->dependencies[$key] = $value;
	}

	public function get($key)
	{
		return $this->dependencies[$key];
	}

	/**
	 * Build a concrete instance of a class
	 *
	 * @param  string  $concrete  The name of the class to build
	 * @return mixed   The instantiated class
	 */
	public function build($concrete)
	{
		// Get function's arguments and remove the first arg
		$user_args = func_get_args();
		array_shift($user_args);

		$reflection = new \ReflectionClass($concrete);

		if ( ! $reflection->isInstantiable())
			throw new Exception(
					'Class :name is not instantiable.', [ ':name' => $concrete ]
			);

		$constructor = $reflection->getConstructor();

		if (is_null($constructor))
		{
			return new $concrete;
		}

		$dependencies = $this->get_dependencies($constructor, $user_args);

		return $reflection->newInstanceArgs($dependencies);
	}

	/**
	 * Recursively collect the dependency list for the provided method
	 * Use user_args array for any extra arguments for method invocation
	 *
	 * @param  \ReflectionMethod  $method     Reflection method to reveal
	 * @param  array              $user_args  Optional arguments for new instance
	 * @return array
	 */
	public function get_dependencies(\ReflectionMethod $method, array $user_args = [])
	{
//	    echo '<pre>',print_r($method),'</pre>';
		$dependencies = [];
		foreach ($method->getParameters() as $param)
		{
			if (isset($this->dependencies[$param->name]))
			{
				// Fetch available DI container item (by param name)
				$dependencies[] = $this->dependencies[$param->name];
			}
			elseif ($dependency = $param->getClass())
			{
				// If parameter is a class, build it (recursively)
				$dependencies[] = $this->build($dependency->name);
			}
			else
			{
				if ($param->isOptional())
				{
					// Use default optional value
					$dependencies[] = $param->getDefaultValue();
				}
				else
				{
					// Otherwise use provided argument in order
					$dependencies[] = array_shift($user_args);
				}
			}
		}

		return $dependencies;
	}

}
