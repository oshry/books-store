<?php
namespace Src\Storage\MySQL;

use Src\Storage\DataMapper;

class Repository {
	use DataMapper;

	public function __construct(Database $database)
	{
		$this->database = $database;
	}

}
