<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE.txt, and
 * is available through the world-wide-web at the following URL:
 * http://framework.zend.com/license/new-bsd. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Url.php 8055 2008-02-15 21:42:54Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Controller_Action_Helper_Abstract */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/** Zend_Controller_Front */
require_once 'Zend/Controller/Front.php';

/**
 * Helper for creating URLs for redirects and other tasks
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_Url extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Create URL based on default route
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array $params
     * @return string
     */
    public function simple($action, $controller = null, $module = null, array $params = null)
    {
        $request = $this->getRequest();

        if (null === $controller) {
            $controller = $request->getControllerName();
        }

        if (null === $module) {
            $module = $request->getModuleName();
        }

        $url = $controller . '/' . $action;
        if ($module != Zend_Controller_Front::getInstance()->getDispatcher()->getDefaultModule()) {
            $url = $module . '/' . $url;
        }

        if (null !== $params) {
            $paramPairs = array();
            foreach ($params as $key => $value) {
                $paramPairs[] = urlencode($key) . '/' . urlencode($value);
            }
            $paramString = implode('/', $paramPairs);
            $url .= '/' . $paramString;
        }

        $url = '/' . ltrim($url, '/');

        return $url;
    }

    /**
     * Assembles a URL based on a given route
     *
     * This method will typically be used for more complex operations, as it
     * ties into the route objects registered with the router.
     *
     * @param  array $urlOptions Options passed to the assemble method of the Route object.
     * @param  mixed $name The name of a Route to use. If null it will use the current Route
     * @return string Url for the link href attribute.
     */
    public function url($urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        $front  = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        if (empty($name)) {
            try {
                $name = $router->getCurrentRouteName();
            } catch (Zend_Controller_Router_Exception $e) {
                $name = 'default';
            }
        }

        if ($encode) {
            foreach ($urlOptions as $key => $option) {
	        $urlOptions[$key] = urlencode($option);
            }
        }

        $route   = $router->getRoute($name);

        $url  = rtrim($front->getBaseUrl(), '/') . '/';
        $url .= $route->assemble($urlOptions, $reset);

        return $url;
    }

    /**
     * Perform helper when called as $this->_helper->url() from an action controller
     *
     * Proxies to {@link simple()}
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array $params
     * @return string
     */
    public function direct($action, $controller = null, $module = null, array $params = null)
    {
        return $this->simple($action, $controller, $module, $params);
    }
}
