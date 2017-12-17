<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext;

class Control
{
	/**
	 * MvcCore - version:
	 * Comparation by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '4.3.1';
	/**
	 * Request object - parsed uri, query params, app paths...
	 * @var \MvcCore\Request
	 */
	protected $request;

	/**
	 * Response object - headers and rendered body
	 * @var \MvcCore\Response
	 */
	protected $response;

	/**
	 * Requested controller name - dashed
	 * @var string
	 */
	protected $controller = '';

	/**
	 * Requested action name - dashed
	 * @var string
	 */
	protected $action = '';

	/**
	 * Boolean about ajax request
	 * @var boolean
	 */
	protected $ajax = FALSE;

	/**
	 * Class store object for view properties
	 * @var \MvcCore\View
	 */
	protected $view = NULL;

	/**
	 * Layout name to render html wrapper around rendered view
	 * @var string
	 */
	protected $layout = 'layout';

	/**
	 * Boolean about disabled or enabled view to render at last
	 * @var boolean
	 */
	protected $viewEnabled = TRUE;

	/**
	 * Registered controls instances.
	 * @var \MvcCore\Ext\Control[]|array
	 */
	protected $controls = array();

	/**
	 * Boolean about disabled or enabled view to render at last
	 * @var \MvcCore\Ext\Auth\Virtual\User|mixed
	 */
	protected $user = NULL;

	/**
	 * Controller instance given in constructor.
	 * @var \MvcCore\Controller|mixed
	 */
	private $_controller;

	/**
	 * Control dispatching state (0 created, 1 initialized, 2 predispatched)
	 * @var int
	 */
	private $_state = 0;

	/**
	 * Controls scripts directory placed in '/App' dir. For read & write.
	 * @var string
	 */
	public static $ControlsDir = 'Controls';

	/**
	 * Controls templates directory placed in '/App/Views' dir. For read & write.
	 * @var string
	 */
	public static $ControlsTemplatesDir = 'Controls';

	/**
	 * Controls templates directory placed in '/App/Views' dir. For read & write.
	 * @var string
	 */
	protected static $viewClass = \MvcCore\Ext\Control\View::class;

	/**
	 * Set controls view class, extended from \MvcCore\View.
	 * \MvcCore\Ext\Control core configuration method.
	 * @param string $viewClass
	 * @return void
	 */
	public static function SetViewClass ($viewClass) {
		@class_exists($viewClass); // load the class
		static::$viewClass = $viewClass;
	}

	/**
	 * Get application view class, extended from \MvcCore\View.
	 * @return string
	 */
	public static function GetViewClass () {
		return static::$viewClass;
	}

	/**
	 * Create \MvcCore\Ext\Form instance.
	 * Please don't forget to configure at least $form->Id, $form->Action,
	 * any control to work with and finaly any button:submit/input:submit
	 * to submit the form to any url defined in $form->Action.
	 * @param \MvcCore\Controller|mixed $controller
	 */
	public function __construct (\MvcCore\Controller & $controller) {
		$this->_controller = $controller;
		$this->request = & $controller->GetRequest();
		$this->response = & $controller->GetResponse();
		$this->controller = $this->request->Params['controller'];
		$this->action = $this->request->Params['action'];
		$this->layout = $controller->GetLayout();
		$this->ajax = $controller->IsAjax();
		$this->viewEnabled = $controller->IsViewEnabled();
		$type = new \ReflectionClass($controller);
		if ($type->hasProperty("user")) {
			/** @var $userProp \ReflectionProperty */
			$userProp = $type->getProperty('user');
			$this->user = $userProp->getValue($controller);
		}
		$this->_controller->AddControl($this);
	}
	/**
	 * Application controls initialization.
	 * This is best time to initialize language, locale or session.
	 * @return void
	 */
	public function Init () {
		if ($this->_state > 0) return;
		foreach ($this->controls as $control) $control->Init();
		$this->_state = 1;
	}
	/**
	 * Application pre render common action - always used in application controls.
	 * This is best time to define any common properties or common view properties.
	 * @return void
	 */
	public function PreDispatch () {
		if ($this->_state > 1) return;
		if ($this->_state == 0) $this->Init();
		if ($this->viewEnabled) {
			$viewClass = \MvcCore\Ext\Control::GetViewClass();
			$this->view = new $viewClass($this);
		}
		foreach ($this->controls as $control) $control->PreDispatch();
		$this->_state = 2;
	}
	/**
	 * Controller instance given in constructor.
	 * @return \MvcCore\Controller|mixed
	 */
	public function GetController () {
		return $this->_controller;
	}
	/**
	 * Get param value, filtered for characters defined as second argument to use them in preg_replace().
	 * Shortcut for $this->request->GetParam();
	 * @param string $name
	 * @param string $pregReplaceAllowedChars
	 * @return string
	 */
	public function GetParam ($name = "", $pregReplaceAllowedChars = "a-zA-Z0-9_/\-\.\@") {
		return $this->request->GetParam($name, $pregReplaceAllowedChars);
	}
	/**
	 * Get current application request object as reference.
	 * @return \MvcCore\Request
	 */
	public function & GetRequest () {
		return $this->request;
	}
	/**
	 * Get current application request object, rarely used.
	 * @param \MvcCore\Request $request
	 * @return \MvcCore\Controller
	 */
	public function SetRequest (\MvcCore\Request & $request) {
		$this->request = $request;
		return $this;
	}
	/**
	 * Get current application response object as reference.
	 * @return \MvcCore\Response
	 */
	public function & GetResponse () {
		return $this->response;
	}
	/**
	 * Get current application response object, rarely used.
	 * @param \MvcCore\Request $response
	 * @return \MvcCore\Controller
	 */
	public function SetResponse (\MvcCore\Response & $response) {
		$this->response = $response;
		return $this;
	}
	/**
	 * Boolean about ajax request
	 * @return bool
	 */
	public function IsAjax () {
		return $this->ajax;
	}
	/**
	 * Boolean about disabled or enabled view to render at last
	 * @return bool
	 */
	public function IsViewEnabled () {
		return $this->viewEnabled;
	}
	/**
	 * Return current controller view object if any.
	 * Before PreDispatch() should be still NULL.
	 * @return \MvcCore\View|NULL
	 */
	public function & GetView () {
		return $this->view;
	}
	/**
	 * Set current controller view object, rarely used.
	 * @param \MvcCore\View $view
	 * @return \MvcCore\Controller
	 */
	public function SetView (\MvcCore\View & $view) {
		$this->view = $view;
		return $this;
	}
	/**
	 * Get layout name: 'front' | 'admin' | 'account' ...
	 * @return string
	 */
	public function GetLayout () {
		return $this->layout;
	}
	/**
	 * Set layout name
	 * @param string $layout
	 * @return \MvcCore\Controller
	 */
	public function SetLayout ($layout = '') {
		$this->layout = $layout;
		return $this;
	}
	/**
	 * Register control to process dispatching on it
	 * @return \MvcCore\Controller
	 */
	public function AddControl (/*\MvcCore\Ext\Control*/ & $control) {
		$this->controls[] = & $control;
		return $this;
	}
	/**
	 * Disable view rendering - always called in text or ajax responses.
	 * @return void
	 */
	public function DisableView () {
		$this->viewEnabled = FALSE;
	}
	/**
	 * Generates url by:
	 * - 'Controller:Action' name and params array
	 *   (for routes configuration when routes array has keys with 'Controller:Action' strings
	 *   and routes has not controller name and action name defined inside)
	 * - route name and params array
	 *	 (route name is key in routes configuration array, should be any string
	 *	 but routes must have information about controller name and action name inside)
	 * Result address should have two forms:
	 * - nice rewrited url by routes configuration
	 *   (for apps with .htaccess supporting url_rewrite and when first param is key in routes configuration array)
	 * - for all other cases is url form: index.php?controller=ctrlName&action=actionName
	 *	 (when first param is not founded in routes configuration array)
	 * @param string $controllerActionOrRouteName	Should be 'Controller:Action' combination or just any route name as custom specific string
	 * @param array  $params						optional
	 * @return string
	 */
	public function Url ($controllerActionOrRouteName = 'Index:Index', $params = array()) {
		return \MvcCore\Router::GetInstance()->Url($controllerActionOrRouteName, $params);
	}
	/**
	 * Return asset path or single file mode url
	 * @param string $path
	 * @return string
	 */
	public function AssetUrl ($path = '') {
		return \MvcCore::GetInstance()->Url('Controller:Asset', array('path' => $path));
	}
	/**
	 * Renders and echoes control instance to html code to use it anywhere in template.
	 * @return string
	 */
	public function Render ($controlsRelativePath = "") {
		if ($this->_state == 0) $this->Init();
		if ($this->_state == 1) $this->PreDispatch();
		if ($this->viewEnabled) {
			if (!$controlsRelativePath) {
				$className = get_class($this);
				$controlsNamespaceSubstr = '\\' . static::$ControlsDir . '\\';
				$controlsPos = mb_strpos($className, $controlsNamespaceSubstr);
				if ($controlsPos !== FALSE) {
					$className = mb_substr($className, $controlsPos + strlen($controlsNamespaceSubstr));
					$controlsRelativePath = '/' . str_replace('\\', '/', $className);
					$controlsRelativePath = \MvcCore\Tool::GetDashedFromPascalCase($controlsRelativePath);
				}
			} else {
				$controlsRelativePath = '/' . trim($controlsRelativePath, '/');
			}
			// render content string
			return $this->view->Render(static::$ControlsTemplatesDir, $controlsRelativePath);
		}
		return "";
	}
	public function __toString () {
		echo $this->Render();
	}
	/**
	 * Send rendered html output to user.
	 * @param mixed $output
	 * @return void
	 */
	public function HtmlResponse ($output = "") {
		$contentTypeHeaderValue = strpos(\MvcCore\View::$Doctype, \MvcCore\View::DOCTYPE_XHTML) !== FALSE ? 'application/xhtml+xml' : 'text/html' ;
		$this->response
			->SetHeader('Content-Type', $contentTypeHeaderValue . '; charset=utf-8')
			->SetBody($output);
	}
	/**
	 * Send any php value serialized in json to user.
	 * @param mixed $data
	 * @return void
	 */
	public function JsonResponse ($data = array()) {
		$output = \MvcCore\Tool::EncodeJson($data);
		$this->response
			->SetHeader('Content-Type', 'text/javascript; charset=utf-8')
			->SetHeader('Content-Length', strlen($output))
			->SetBody($output);
	}
	/**
	 * Terminate request. Write session, send headers if possible and echo response body.
	 * @return void
	 */
	public function Terminate () {
		\MvcCore::GetInstance()->Terminate();
	}
	/**
	 * Redirect user browser to another location.
	 * @param string $location
	 * @param int    $code
	 * @return void
	 */
	public static function Redirect ($location = '', $code = \MvcCore\Response::SEE_OTHER) {
		\MvcCore::GetInstance()->GetResponse()
			->SetCode($code)
			->SetHeader('Location', $location);
		\MvcCore::GetInstance()->Terminate();
	}
}