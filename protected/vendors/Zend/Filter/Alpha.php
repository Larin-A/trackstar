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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Alpha.php 8055 2008-02-15 21:42:54Z thomas $
 */


/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Alpha implements Zend_Filter_Interface
{
    /**
     * Whether to allow white space characters; off by default
     *
     * @var boolean
     */
    public $allowWhiteSpace;

    /**
     * Is PCRE is compiled with UTF-8 and Unicode support
     *
     * @var mixed
     **/
    protected static $_unicodeEnabled;

    /**
     * Sets default option values for this instance
     *
     * @param  boolean $allowWhiteSpace
     * @return void
     */
    public function __construct($allowWhiteSpace = false)
    {
        $this->allowWhiteSpace = (boolean) $allowWhiteSpace;
        if (null === self::$_unicodeEnabled) {
            self::$_unicodeEnabled = (@preg_match('/\pL/u', 'a')) ? true : false;
        }
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value, removing all but alphabetic characters
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $whiteSpace = $this->allowWhiteSpace ? '\s' : '';
        if (!self::$_unicodeEnabled) {
            // POSIX named classes are not supported, use alternative a-zA-Z match
            $pattern = '/[^a-zA-Z' . $whiteSpace . ']/';
        } else {
            $pattern = '/[^\p{L}' . $whiteSpace . ']/u';
        }

        return preg_replace($pattern, '', (string) $value);
    }
}
