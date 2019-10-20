<?php
namespace Src\Core\Usecase\Comment;

use Src\Core\Data;
use Src\Core\Repository;
use Exception;

class Create {

	protected $data;
	protected $comment;
	protected $repo;

	public function __construct(
		Data\Comment $comment,
		Repository\Comment $repo
	)
	{
		$this->comment = $comment;
		$this->repo = $repo;
	}

	public function set(array $data)
	{
		$this->data = $data;

		return $this;
	}

	public function execute()
	{
		$this->repo->hydrate($this->comment, $this->data);

		// Validation
		if (empty($this->comment->email))
			throw new Exception('Email is mandatory');

		// Write to persistency layer
		$this->repo->create($this->comment);

		// Return simple data fields
		return get_object_vars($this->comment);
	}

}
