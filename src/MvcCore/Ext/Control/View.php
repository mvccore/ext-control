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

namespace MvcCore\Ext\Control;

require_once(__DIR__.'/../Control.php');

class View extends \MvcCore\View
{
	/**
	 * Rendered content
	 * @var \MvcCore\Ext\Control|mixed
	 */
	public $Control;

	/**
	 * Originaly declared dynamic properties to protect from __set() magic method
	 * @var string
	 */
	protected static $originalyDeclaredProperties = array(
		'Control'			=> 1,
		'Controller'		=> 1,
		'_content'			=> 1,
		'_renderedFullPaths'=> 1,
	);

	/**
	 * Create new view instance.
	 * @param \MvcCore\Ext\Control $control
	 */
	public function __construct (\MvcCore\Ext\Control & $control) {
		$this->Control = $control;
		$this->Controller = $control->GetController();
	}
}