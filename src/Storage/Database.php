<?php
namespace Src\Storage;

abstract class Database {

	const SELECT =  1;
	const INSERT =  2;
	const UPDATE =  3;
	const DELETE =  4;

	public static $instances = [];

	public static $default = 'default';

	public static function instance(array $config, $name = NULL)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = static::$default;
		}

		if ( ! isset(static::$instances[$name]))
		{
			static::$instances[$name] = new static($config[$name], $name);
		}
		return static::$instances[$name];
	}

	protected $connection;
	protected $instance;
	protected $config;

	public function __construct(array $config, $name)
	{
		$this->instance = $name;
		$this->config = $config;
	}

	/**
	 * Prevent from being cloned
	 */
	protected function __clone()
	{
	}

	/**
	 * Prevent from being unserialized
	 */
	protected function __wakeup()
	{
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	abstract public function connect();

	public function disconnect()
	{
		unset(static::$instances[$this->instance]);

		return TRUE;
	}

	abstract public function set_charset($charset);

	abstract public function query($type, $sql, $as_object = FALSE, array $params = NULL);

	abstract public function escape($value);

	/**
	 * Quote a value for an SQL query
	 *
	 * @param   mixed   $value  any value to quote
	 * @return  string
	 */
	public function quote($value)
	{
		if ($value === NULL)
		{
			return 'NULL';
		}
		elseif ($value === TRUE)
		{
			return "'1'";
		}
		elseif ($value === FALSE)
		{
			return "'0'";
		}
		elseif (is_array($value))
		{
			return '('.implode(', ', array_map(array($this, __FUNCTION__), $value)).')';
		}
		elseif (is_int($value))
		{
			return (int) $value;
		}
		elseif (is_float($value))
		{
			// Convert to non-locale aware float to prevent possible commas
			return sprintf('%F', $value);
		}

		return $this->escape($value);
	}

}
