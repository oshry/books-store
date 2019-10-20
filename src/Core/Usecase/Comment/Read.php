<?php
namespace Src\Core\Usecase\Comment;

use Src\Core\Data;
use Src\Core\Repository;


class Read {

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
		$collection = $this->repo->find_all([], $this->comment);
		return $collection;
	}

}
