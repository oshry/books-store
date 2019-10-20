<?php
namespace Src\Storage\MySQL;

use Src\Storage;
use PDO;

class Database extends Storage\Database {

	public function connect()
	{
		if ($this->connection)
			return;

		// Extract the connection parameters, adding required variabels
		extract($this->config['connection'] + array(
			'dsn'        => '',
			'username'   => NULL,
			'password'   => NULL,
			'persistent' => FALSE,
		));

		// Clear the connection parameters for security
		unset($this->config['connection']);

		// Force PDO to use exceptions for all errors
		$options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

		if ( ! empty($persistent))
		{
			// Make the connection persistent
			$options[PDO::ATTR_PERSISTENT] = TRUE;
		}

		try
		{
			// Create a new PDO connection
			$this->connection = new PDO($dsn, $username, $password, $options);
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error',
				array(':error' => $e->getMessage()),
				$e->getCode());
		}

		if ( ! empty($this->config['charset']))
		{
			// Set the character set
			$this->set_charset($this->config['charset']);
		}
	}

	public function disconnect()
	{
		// Destroy the PDO object
		$this->connection = NULL;

		return parent::disconnect();
	}

	public function set_charset($charset)
	{
		// Make sure the database is connected
		$this->connection OR $this->connect();

		// This SQL-92 syntax is not supported by all drivers
		$this->connection->exec('SET NAMES '.$this->quote($charset));
	}

	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		// Make sure the database is connected
		$this->connection or $this->connect();

		try
		{
			$result = $this->connection->query($sql);
		}
		catch (Exception $e)
		{
			// Convert the exception in a database exception
			throw new Database_Exception(':error [ :query ]',
				array(
					':error' => $e->getMessage(),
					':query' => $sql
				),
				$e->getCode());
		}

		if ($type === Database::SELECT)
		{
			// Convert the result into an array, as PDOStatement::rowCount is not reliable
			if ($as_object === FALSE)
			{
				$result->setFetchMode(PDO::FETCH_ASSOC);
			}
			elseif (is_string($as_object))
			{
				$result->setFetchMode(PDO::FETCH_CLASS, $as_object, $params);
			}
			else
			{
				$result->setFetchMode(PDO::FETCH_CLASS, 'stdClass');
			}

			$result = $result->fetchAll();

			// Return an iterator of results
			return new Storage\ResultCached($result, $sql, $as_object, $params);
		}
		elseif ($type === Database::INSERT)
		{
			// Return a list of insert id and rows created
			return array(
				$this->connection->lastInsertId(),
				$result->rowCount(),
			);
		}
		else
		{
			// Return the number of rows affected
			return $result->rowCount();
		}
	}

	public function escape($value)
	{
		// Make sure the database is connected
		$this->connection or $this->connect();

		return $this->connection->quote($value);
	}

}
