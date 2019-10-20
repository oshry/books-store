<?php
namespace Src\Core\Repository;

use Src\Storage\MySQL\Repository;
use Src\Storage\Database;

class Comment extends Repository {

	public function get($data)
	{
		throw new \Exception('Not implemented.');
	}

	public function find_all($filters, $object)
	{
		return $this->database->query(
			Database::SELECT,
			'SELECT * FROM comments order by `id` DESC',
			get_class($object)
		);
	}

	public function create($object)
	{
		$params = [
			':parent_id' => $object->parent_id,
			':email' => $object->email,
			':body' => $object->body,
		];

		array_walk($params, function (& $value, $key) {
			$value = $this->database->quote($value);
		});

		$sql = strtr('
			INSERT INTO comments (parent_id, email, body)
			VALUES (:parent_id, :email, :body)
		', $params);

		return $this->database->query(Database::INSERT, $sql);
	}

	public function delete($data)
	{
		throw new \Exception('Not implemented.');
	}

}
