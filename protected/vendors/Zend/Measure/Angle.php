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
 * @version    $Id: Angle.php 8055 2008-02-15 21:42:54Z thomas $
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
 * @subpackage Zend_Measure_Angle
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Angle extends Zend_Measure_Abstract
{
    // Angle definitions
    const STANDARD = 'RADIAN';

    const RADIAN      = 'RADIAN';
    const MIL         = 'MIL';
    const GRAD        = 'GRAD';
    const DEGREE      = 'DEGREE';
    const MINUTE      = 'MINUTE';
    const SECOND      = 'SECOND';
    const POINT       = 'POINT';
    const CIRCLE_16   = 'CIRCLE_16';
    const CIRCLE_10   = 'CIRCLE_10';
    const CIRCLE_8    = 'CIRCLE_8';
    const CIRCLE_6    = 'CIRCLE_6';
    const CIRCLE_4    = 'CIRCLE_4';
    const CIRCLE_2    = 'CIRCLE_2';
    const FULL_CIRCLE = 'FULL_CIRCLE';

    protected $_UNITS = array(
        'RADIAN'      => array('1','rad'),
        'MIL'         => array(array('' => M_PI,'/' => '3200'),   'mil'),
        'GRAD'        => array(array('' => M_PI,'/' => '200'),    'gr'),
        'DEGREE'      => array(array('' => M_PI,'/' => '180'),    '°'),
        'MINUTE'      => array(array('' => M_PI,'/' => '10800'),  "'"),
        'SECOND'      => array(array('' => M_PI,'/' => '648000'), '"'),
        'POINT'       => array(array('' => M_PI,'/' => '16'),     'pt'),
        'CIRCLE_16'   => array(array('' => M_PI,'/' => '8'),      'per 16 circle'),
        'CIRCLE_10'   => array(array('' => M_PI,'/' => '5'),      'per 10 circle'),
        'CIRCLE_8'    => array(array('' => M_PI,'/' => '4'),      'per 8 circle'),
        'CIRCLE_6'    => array(array('' => M_PI,'/' => '3'),      'per 6 circle'),
        'CIRCLE_4'    => array(array('' => M_PI,'/' => '2'),      'per 4 circle'),
        'CIRCLE_2'    => array(M_PI,                            'per 2 circle'),
        'FULL_CIRCLE' => array(array('' => M_PI,'*' => '2'),      'cir'),
        'STANDARD'    => 'RADIAN'
    );
}
