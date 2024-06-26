<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Measure
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Temperature.php 8055 2008-02-15 21:42:54Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Implement needed classes
 */
require_once 'Zend/Measure/Exception.php';
require_once 'Zend/Measure/Abstract.php';
require_once 'Zend/Locale.php';


/**
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Temperature
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Temperature extends Zend_Measure_Abstract
{
    // Temperature definitions
    const STANDARD = 'KELVIN';

    const CELSIUS    = 'CELSIUS';
    const FAHRENHEIT = 'FAHRENHEIT';
    const RANKINE    = 'RANKINE';
    const REAUMUR    = 'REAUMUR';
    const KELVIN     = 'KELVIN';

    protected $_UNITS = array(
        'CELSIUS'    => array(array('' => '1', '+' => '273.15'),'°C'),
        'FAHRENHEIT' => array(array('' => '1', '-' => '32', '/' => '1.8', '+' => '273.15'),'°F'),
        'RANKINE'    => array(array('' => '1', '/' => '1.8'),'°R'),
        'REAUMUR'    => array(array('' => '1', '*' => '1.25', '+' => '273.15'),'°r'),
        'KELVIN'     => array(1,'°K'),
        'STANDARD'   => 'KELVIN'
    );
}
