<?php
namespace Src\Delivery;

class Response {

	/**
	 * @var  integer     The response http status
	 */
	protected $status = 200;

	/**
	 * @var  string      The response body
	 */
	protected $body = '';

	/**
	 * @var  string      The response protocol
	 */
	protected $protocol;

	public function __construct(array $config = [])
	{
		foreach ($config as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
	}

	/**
	 * Outputs the body when cast to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->body;
	}

	/**
	 * Gets or sets the body of the response
	 *
	 * @return  mixed
	 */
	public function body($content = NULL)
	{
		if ($content === NULL)
			return $this->body;

		$this->body = (string) $content;

		return $this;
	}

	/**
	 * Returns the length of the body for use with content header
	 *
	 * @return  integer
	 */
	public function content_length()
	{
		return strlen($this->body());
	}

}
