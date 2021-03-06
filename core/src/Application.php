<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file core/lib/Application.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup core
 */
namespace Starbug\Core;
class Application implements ApplicationInterface {

	protected $controllers;
	protected $models;
	protected $router;
	protected $request;
	protected $response;
	protected $config;
	protected $user;
	protected $locator;

	use \Psr\Log\LoggerAwareTrait;

	/**
	 * constructor.
	 */
	public function __construct(
		ControllerFactoryInterface $controllers,
		ModelFactoryInterface $models,
		RouterInterface $router,
		SettingsInterface $settings,
		ResourceLocatorInterface $locator,
		UserInterface $user,
		Response $response
	) {
		$this->controllers = $controllers;
		$this->models = $models;
		$this->router = $router;
		$this->settings = $settings;
		$this->locator = $locator;
		$this->user = $user;
		$this->response = $response;
	}

	public function handle(Request $request) {
		$this->user->startSession();

		if (empty($request->path)) {
			$request->path = $this->settings->get("default_path");
			$this->logger->addInfo("Request path is empty. Routing to default path: ".$request->path);
		} else {
			$this->logger->addInfo("Request path - ".$request->path);
		}

		$this->response->assign("request", $request);
		$route = $this->router->route($request);

		if (empty($route['theme'])) $route['theme'] = $this->settings->get("theme");
		if (empty($route['layout'])) $route['layout'] = empty($route['type']) ? "views" : $route['type'];
		if (empty($route['template'])) $route['template'] = $request->format;
		$this->locator->set("theme", "app/themes/".$route['theme']);

		foreach ($route as $k => $v) {
			$this->response->{$k} = $v;
		}
		$this->logger->addInfo("Loading ".$route['controller'].' -> '.$route['action']);
		$controller = $this->controllers->get($route['controller']);

		if (isset($controller->routes[$route['action']])) {
			$template = $controller->routes[$route['action']];
			if (false === ($values = $this->router->validate($request, $route, $template))) {
				$route['action'] = 'missing';
			} else if (is_array($values)) {
				$route['arguments'] = $values;
			}
		}

		if (empty($route['arguments'])) $route['arguments'] = array();

		$controller->start($request, $this->response);
		$permitted = $this->check_post($request->data, $request->cookies);
		if ($permitted) $controller->action($route['action'], $route['arguments']);
		else $controller->forbidden();
		$this->response = $controller->finish();
		return $this->response;
	}
	/**
	* run a model action if permitted
	* @param string $key the model name
	* @param string $value the function name
	*/
	protected function post_action($key, $value, $post = array()) {
		$this->logger->addInfo("Attempting action ".$key.' -> '.$value);
		if ($object = $this->models->get($key)) {
			return $object->post($value, $post);
		}
	}

	/**
	* check $_POST['action'] for posted actions and run them through post_act
	*/
	protected function check_post($post, $cookies) {
		if (!empty($post['action']) && is_array($post['action'])) {
			//validate csrf token for authenticated requests
			if ($this->user->loggedIn()) {
				$validated = false;
				if (!empty($cookies['oid']) && !empty($post['oid']) && $cookies['oid'] === $post['oid']) $validated = true;
				if (true !== $validated) {
					return false;
				}
			}
			//execute post actions
			foreach ($post['action'] as $key => $val) return $this->post_action(normalize($key), normalize($val), $post[$key]);
		}
		return true;
	}
}
