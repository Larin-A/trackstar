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
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: WatchlistFilterSet.php 8055 2008-02-15 21:42:54Z thomas $
 */


/**
 * @see Zend_Service_Simpy_WatchlistFilter
 */
require_once 'Zend/Service/Simpy/WatchlistFilter.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy_WatchlistFilterSet implements IteratorAggregate
{
    /**
     * List of filters in the set
     *
     * @var array of Zend_Service_Simpy_WatchlistFilter objects
     */
    protected $_filters = array();

    /**
     * Adds a filter to the set
     *
     * @param  Zend_Service_Simpy_WatchlistFilter $filter Filter to be added
     * @return void
     */
    public function add(Zend_Service_Simpy_WatchlistFilter $filter)
    {
        $this->_filters[] = $filter;
    }

    /**
     * Returns an iterator for the watchlist filter set
     *
     * @return IteratorIterator
     */
    public function getIterator()
    {
        $array = new ArrayObject($this->_filters);
        return $array->getIterator();
    }

    /**
     * Returns the number of filters in the set
     *
     * @return int
     */
    public function getLength()
    {
        return count($this->_filters);
    }
}
