<?php
namespace Src;

use Src\Dependency\Manager;
use Src\Storage\MySQL;

class AppManager {
	use Manager;

	public function __construct(array $config)
	{
		// Create a singleton database and inject it to container
		$this->set('database', MySQL\Database::instance($config));
	}

}
