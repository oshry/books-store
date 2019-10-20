<?php
namespace Src\Controller;

use Src\Core\Usecase;
use Src\Traits;
use Src\View;

/**
 * Browser endpoint for HTML rendering
 */
class Comment extends HTML {

	// Render HTML from PHP templates
	use Traits\Render;

	/**
	 * Comment list
	 */
	public function action_index(
		Usecase\Comment\Read $usecase,
        View\Comment\Read $view
	)
	{
		$output = $usecase->execute();
		$this->template = 'comment/read';
//		echo '<pre>',print_r($view->set($output)),'</pre>';
		$this->view = $view->set($output);
	}

	/**
	 * Comment creation
	 */
	public function action_create(
		Usecase\Comment\Create $usecase,
		View\Comment\Create $view
	)
	{

		$comment = $this->request->post();

		$output = $usecase
			->set($comment)
			->execute();

		$this->template = 'comment/create';
		$this->view = $view->set($output);
	}

}
