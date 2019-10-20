<?php
namespace Src\View\Comment;

use Src\View\Layout;

class Read extends Layout {

	protected $comments;

	// TODO Should be implemented as partials in a template rendering engine.
//	const PARTIALS = [
//		'{{smiley}}' => '<img src="../../app/media/img/smiley.png" />'
//	];

	public function set($comments)
	{
		$this->comments = $comments;

		return $this;
	}

	public function comments()
	{
		$partials = array(
		'{{smiley}}' => '<img src="../../app/media/img/smiley.png" />'
	);

		$result = [];
		foreach ($this->comments as $comment)
		{
			$item = get_object_vars($comment);
			$item['body'] = strtr($item['body'], $partials);
			$result[] = $item;
		}

		return $result;
	}

}
