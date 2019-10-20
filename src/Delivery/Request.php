<?php
namespace Src\Delivery;

use ReflectionClass;

class Request {

	/**
	 * @var  string $method GET, POST, PUT, DELETE, HEAD, etc
	 */
	protected $method = 'GET';

	/**
	 * @var  string $protocol HTTP/1.1, FTP, CLI, etc
	 */
	protected $protocol = 'HTTP/1.1';

	/**
	 * @var  string $body
	 */
	protected $body;

	/**
	 * @var array   $get query parameters
	 */
	protected $get = [];

	/**
	 * @var array   $post post parameters
	 */
	protected $post = [];

	/**
		* @var array  $parameters app dependencies
	 */
	protected $parameters = [];

	/**
	 * @var  string  $prefix  namespace prefix
	 */
	protected $prefix;

	/**
	 * @var  string  $controller  controller to be executed
	 */
	protected $controller;

	/**
	 * @var  string  $action  action to be executed in the controller
	 */
	protected $action;

	/**
	 * @var  string  $uri  the URI of the request
	 */
	protected $uri;

	protected $routes = [];

	/**
	 * Creates a new request instance
	 */
	public function __construct(array $parameters = [])
 	{
		$this->parameters = $parameters;
		if (isset($_SERVER['SERVER_PROTOCOL']))
		{
			$this->protocol = strtoupper($_SERVER['SERVER_PROTOCOL']);
		}

		if (isset($_SERVER['REQUEST_METHOD']))
		{
			$this->method = strtoupper($_SERVER['REQUEST_METHOD']);
		}

		if ($this->method !== 'GET')
		{
			// Ensure the raw body is saved for future use
			$this->body = file_get_contents('php://input');
		}

		// Query and post parameters
		$this->get = $_GET;
		$this->post = $_POST;

		$this->uri = $this->detect_uri();

		if (isset($parameters['routes']))
		{
			$this->routes = $parameters['routes'];
		}

		if (isset($parameters['base_ur']))
		{
			$parameters['base_ur'] = rtrim($parameters['base_url'], '/').'/';
		}
	}

	/**
	 * Execute request controller
	 */
	public function execute()
	{
		$response = new Response([ 'protocol' => $this->protocol ]);
		$prefix = '';
		$controller = '';
		foreach ($this->routes as $route => $route_params)
		{
            $controller = $this->routes[$route]['controller'];
            $prefix = '\\'.$this->routes[$route]['prefix'].'\\';
            $action = $this->routes[$route]['action'];
		}
        $newAction = explode('/',$this->uri());
        $action = isset($newAction[2])?$newAction[2]:$action;
		if (empty($controller) || ! class_exists($prefix.$controller))
		{
			throw new Exception(
				'The requested URL :uri was not found on this server.',
				[ ':uri' => $this->uri() ]
			);
		}

		$this->prefix = $prefix;
		$this->controller = $controller;
		$this->action = $action;
		// Load the controller using reflection
		$class = new ReflectionClass($prefix.$controller);

		if ($class->isAbstract())
		{
			throw new Exception(
				'Cannot create instances of abstract :controller',
				[ ':controller' => $prefix.$controller ]
			);
		}

		// Create a new instance of the controller (req, res, params)

		$controller = $class->newInstance($this, $response, $this->parameters);
		// Run the controller's execute() method

		$response = $class->getMethod('execute')->invokeArgs($controller, [ $class ]);

		if ( ! $response instanceof Response)
		{
			// Controller failed to return a Response.
			throw new Exception('Controller failed to return a Response');
		}
		return $response;
	}

	/**
	 * Sets and gets the uri from the request.
	 *
	 * @param   string $uri
	 * @return  mixed
	 */
	public function uri($uri = NULL)
	{
		if ($uri === NULL)
		{
			// Act as a getter
			return empty($this->uri) ? '/' : $this->uri;
		}

		// Act as a setter
		$this->uri = $uri;

		return $this;
	}

	/**
	 * Sets and gets the action for the controller.
	 *
	 * @param   string   $action  Action to execute the controller from
	 * @return  mixed
	 */
	public function action($action = NULL)
	{
		if ($action === NULL)
		{
			// Act as a getter
			return $this->action;
		}

		// Act as a setter
		$this->action = (string) $action;

		return $this;
	}

	/**
	 * Automatically detects the URI of the main request using PATH_INFO,
	 * REQUEST_URI, PHP_SELF or REDIRECT_URL.
	 *
	 * @return  string
	 */
	public function detect_uri()
	{
		if ( ! empty($_SERVER['PATH_INFO']))
		{
			// PATH_INFO does not contain the docroot or index
			$uri = $_SERVER['PATH_INFO'];
		}
		else
		{
			// REQUEST_URI and PHP_SELF include the docroot and index

			if (isset($_SERVER['REQUEST_URI']))
			{
				/**
				 * We use REQUEST_URI as the fallback value. The reason
				 * for this is we might have a malformed URL such as:
				 *
				 *  http://localhost/http://example.com/judge.php
				 *
				 * which parse_url can't handle. So rather than leave empty
				 * handed, we'll use this.
				 */
				$uri = $_SERVER['REQUEST_URI'];

				if ($request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
				{
					// Valid URL path found, set it.
					$uri = $request_uri;
				}

				// Decode the request URI
				$uri = rawurldecode($uri);
			}
			elseif (isset($_SERVER['PHP_SELF']))
			{
				$uri = $_SERVER['PHP_SELF'];
			}
			elseif (isset($_SERVER['REDIRECT_URL']))
			{
				$uri = $_SERVER['REDIRECT_URL'];
			}
			else
			{
				throw new Exception('Unable to detect the URI using PATH_INFO, REQUEST_URI, PHP_SELF or REDIRECT_URL');
			}

			// Get the path from the base URL, including the index file
			$base_url = isset($this->parameters['base_url'])
				? parse_url($this->parameters['base_url'], PHP_URL_PATH)
				: '/';

			if (strpos($uri, $base_url) === 0)
			{
				// Remove the base URL from the URI
				$uri = (string) substr($uri, strlen($base_url));
			}

			$index_file = isset($this->parameters['index_file'])
				? $this->parameters['index_file']
				: FALSE;

			if ($index_file && strpos($uri, $index_file) === 0)
			{
				// Remove the index file from the URI
				$uri = (string) substr($uri, strlen($index_file));
			}
		}

		return $uri;
	}

	/**
	 * Gets or sets HTTP query string.
	 *
	 * @param   mixed   $key    Key or key value pairs to set
	 * @param   string  $value  Value to set to a key
	 * @return  mixed
	 */
	public function query($key = NULL, $value = NULL)
	{
		if (is_array($key))
		{
			// Act as a setter, replace all query strings
			$this->get = $key;

			return $this;
		}

		if ($key === NULL)
		{
			// Act as a getter, all query strings
			return $this->get;
		}
		elseif ($value === NULL)
		{
			// Act as a getter, single query string
			return $this->get[$key];
		}

		// Act as a setter, single query string
		$this->get[$key] = $value;

		return $this;
	}

	/**
	 * Gets or sets HTTP POST parameters to the request.
	 *
	 * @param   mixed  $key    Key or key value pairs to set
	 * @param   string $value  Value to set to a key
	 * @return  mixed
	 */
	public function post($key = NULL, $value = NULL)
	{
		if (is_array($key))
		{
			// Act as a setter, replace all fields
			$this->post = $key;

			return $this;
		}

		if ($key === NULL)
		{
			// Act as a getter, all fields
			return $this->post;
		}
		elseif ($value === NULL)
		{
			// Act as a getter, single field
			return $this->post[$key];
		}

		// Act as a setter, single field
		$this->post[$key] = $value;

		return $this;
	}

}
