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
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Yahoo.php 8055 2008-02-15 21:42:54Z thomas $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yahoo
{
    /**
     * Yahoo Developer Application ID
     *
     * @var string
     */
    public $appId;

    /**
     * Reference to the REST client
     *
     * @var Zend_Rest_Client
     */
    protected $_rest;


    /**
     * Sets the application ID and instantiates the REST client
     *
     * @param  string $appId specified the developer's appid
     * @return void
     */
    public function __construct($appId)
    {
        $this->appId = (string) $appId;
        /**
         * @see Zend_Rest_Client
         */
        require_once 'Zend/Rest/Client.php';
        $this->_rest = new Zend_Rest_Client('http://api.search.yahoo.com');
    }


    /**
     * Perform a search of images.  The most basic query consists simply
     * of a plain text search, but you can also specify the type of
     * image, the format, color, etc.
     *
     * The specific options are:
     * 'type'       => (all|any|phrase)  How to parse the query terms
     * 'results'    => int  How many results to return, max is 50
     * 'start'      => int  The start offset for search results
     * 'format'     => (any|bmp|gif|jpeg|png)  The type of images to search for
     * 'coloration' => (any|color|bw)  The coloration of images to search for
     * 'adult_ok'   => bool  Flag to allow 'adult' images.
     *
     * @param  string $query   the query to be run
     * @param  array  $options an optional array of query options
     * @return Zend_Service_Yahoo_ImageResultSet the search results
     */
    public function imageSearch($query, array $options = array())
    {
        static $defaultOptions = array('type'       => 'all',
                                       'results'    => 10,
                                       'start'      => 1,
                                       'format'     => 'any',
                                       'coloration' => 'any');

        $options = $this->_prepareOptions($query, $options, $defaultOptions);

        $this->_validateImageSearch($options);

        $this->_rest->getHttpClient()->resetParameters();
        $this->_rest->setUri('http://api.search.yahoo.com');
        $response = $this->_rest->restGet('/ImageSearchService/V1/imageSearch', $options);

        if ($response->isError()) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('An error occurred sending request. Status code: ' .
                                             $response->getStatus());
        }

        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());

        self::_checkErrors($dom);

        /**
         * @see Zend_Service_YahooImageResultSet
         */
        require_once 'Zend/Service/Yahoo/ImageResultSet.php';
        return new Zend_Service_Yahoo_ImageResultSet($dom);
    }


    /**
     * Perform a search on local.yahoo.com.  The basic search
     * consists of a query and some fragment of location information;
     * for example zipcode, latitude/longitude, or street address.
     *
     * Query options include:
     * 'results'    => int  How many results to return, max is 50
     * 'start'      => int  The start offset for search results
     * 'sort'       => (relevance|title|distance|rating) How to order your results
     *
     * 'radius'     => float  The radius (in miles) in which to search
     *
     * 'longitude'  => float  The longitude of the location to search around
     * 'latitude'   => float  The latitude of the location to search around
     *
     * 'zip'        => string The zipcode to search around
     *
     * 'street'     => string  The street address to search around
     * 'city'       => string  The city for address search
     * 'state'      => string  The state for address search
     * 'location'   => string  An adhoc location string to search around
     *
     * @param  string $query    The query string you want to run
     * @param  array  $options  The search options, including location
     * @return Zend_Service_Yahoo_LocalResultSet The results
     */
    public function localSearch($query, array $options = array())
    {
        static $defaultOptions = array('results' => 10,
                                       'start'   => 1,
                                       'sort'    => 'distance',
                                       'radius'  => 5);

        $options = $this->_prepareOptions($query, $options, $defaultOptions);

        $this->_validateLocalSearch($options);

        $this->_rest->getHttpClient()->resetParameters();
        $this->_rest->setUri('http://api.local.yahoo.com');
        $response = $this->_rest->restGet('/LocalSearchService/V1/localSearch', $options);

        if ($response->isError()) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('An error occurred sending request. Status code: ' .
                                             $response->getStatus());
        }

        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());

        self::_checkErrors($dom);

        /**
         * @see Zend_Service_Yahoo_LocalResultSet
         */
        require_once 'Zend/Service/Yahoo/LocalResultSet.php';
        return new Zend_Service_Yahoo_LocalResultSet($dom);
    }


    /**
     * Execute a search on news.yahoo.com. This method minimally takes a
     * text query to search on.
     *
     * Query options coonsist of:
     *
     * 'results'    => int  How many results to return, max is 50
     * 'start'      => int  The start offset for search results
     * 'sort'       => (rank|date)  How to order your results
     * 'language'   => lang  The target document language to match
     * 'type'       => (all|any|phrase)  How the query should be parsed
     * 'site'       => string  A site to which your search should be restricted
     *
     * @param  string $query    The query to run
     * @param  array  $options  The array of optional parameters
     * @return Zend_Service_Yahoo_NewsResultSet  The query return set
     */
    public function newsSearch($query, array $options = array())
    {
        static $defaultOptions = array('type'     => 'all',
                                       'start'    => 1,
                                       'sort'     => 'rank',
                                       'language' => 'en');

        $options = $this->_prepareOptions($query, $options, $defaultOptions);

        $this->_validateNewsSearch($options);

        $this->_rest->getHttpClient()->resetParameters();
        $this->_rest->setUri('http://api.search.yahoo.com');
        $response = $this->_rest->restGet('/NewsSearchService/V1/newsSearch', $options);

        if ($response->isError()) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('An error occurred sending request. Status code: ' .
                                             $response->getStatus());
        }

        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());

        self::_checkErrors($dom);

        /**
         * @see Zend_Service_Yahoo_NewsResultSet
         */
        require_once 'Zend/Service/Yahoo/NewsResultSet.php';
        return new Zend_Service_Yahoo_NewsResultSet($dom);
    }


    /**
     * Perform a web content search on search.yahoo.com.  A basic query
     * consists simply of a text query.  Additional options that can be
     * specified consist of:
     * 'results'    => int  How many results to return, max is 50
     * 'start'      => int  The start offset for search results
     * 'language'   => lang  The target document language to match
     * 'type'       => (all|any|phrase)  How the query should be parsed
     * 'site'       => string  A site to which your search should be restricted
     * 'format'     => (any|html|msword|pdf|ppt|rss|txt|xls)
     * 'adult_ok'   => bool  permit 'adult' content in the search results
     * 'similar_ok' => bool  permit similar results in the result set
     * 'country'    => string  The country code for the content searched
     * 'license'    => (any|cc_any|cc_commercial|cc_modifiable)  The license of content being searched
     *
     * @param  string $query    the query being run
     * @param  array  $options  any optional parameters
     * @return Zend_Service_Yahoo_WebResultSet  The return set
     */
    public function webSearch($query, array $options = array())
    {
        static $defaultOptions = array('type'     => 'all',
                                       'start'    => 1,
                                       'language' => 'en',
                                       'license'  => 'any',
                                       'results'  => 10,
                                       'format'   => 'any');

        $options = $this->_prepareOptions($query, $options, $defaultOptions);
        $this->_validateWebSearch($options);

        $this->_rest->getHttpClient()->resetParameters();
        $this->_rest->setUri('http://api.search.yahoo.com');
        $response = $this->_rest->restGet('/WebSearchService/V1/webSearch', $options);

        if ($response->isError()) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('An error occurred sending request. Status code: ' .
                                             $response->getStatus());
        }

        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());

        self::_checkErrors($dom);

        /**
         * @see Zend_Service_Yahoo_WebResultSet
         */
        require_once 'Zend/Service/Yahoo/WebResultSet.php';
        return new Zend_Service_Yahoo_WebResultSet($dom);
    }


    /**
     * Returns a reference to the REST client
     *
     * @return Zend_Rest_Client
     */
    public function getRestClient()
    {
        return $this->_rest;
    }


    /**
     * Validate Image Search Options
     *
     * @param  array $options
     * @throws Zend_Service_Exception
     * @return void
     */
    protected function _validateImageSearch(array $options)
    {
        $validOptions = array('appid', 'query', 'type', 'results', 'start', 'format', 'coloration', 'adult_ok');

        $this->_compareOptions($options, $validOptions);

        if (isset($options['type'])) {
            switch($options['type']) {
                case 'all':
                case 'any':
                case 'phrase':
                    break;
                default:
                    /**
                     * @see Zend_Service_Exception
                     */
                    require_once 'Zend/Service/Exception.php';
                    throw new Zend_Service_Exception("Invalid value for option 'type': '{$options['type']}'");
            }
        }

        /**
         * @see Zend_Validate_Between
         */
        require_once 'Zend/Validate/Between.php';
        $between = new Zend_Validate_Between(1, 50, true);

        if (isset($options['results']) && !$between->setMin(1)->setMax(50)->isValid($options['results'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'results': {$options['results']}");
        }

        if (isset($options['start']) && !$between->setMin(1)->setMax(1000)->isValid($options['start'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'start': {$options['start']}");
        }

        if (isset($options['format'])) {
            switch ($options['format']) {
                case 'any':
                case 'bmp':
                case 'gif':
                case 'jpeg':
                case 'png':
                    break;
                default:
                    /**
                     * @see Zend_Service_Exception
                     */
                    require_once 'Zend/Service/Exception.php';
                    throw new Zend_Service_Exception("Invalid value for option 'format': {$options['format']}");
            }
        }

        if (isset($options['coloration'])) {
            switch ($options['coloration']) {
                case 'any':
                case 'color':
                case 'bw':
                    break;
                default:
                    /**
                     * @see Zend_Service_Exception
                     */
                    require_once 'Zend/Service/Exception.php';
                    throw new Zend_Service_Exception("Invalid value for option 'coloration': "
                                                   . "{$options['coloration']}");
            }
        }
    }


    /**
     * Validate Local Search Options
     *
     * @param  array $options
     * @throws Zend_Service_Exception
     * @return void
     */
    protected function _validateLocalSearch(array $options)
    {
        $validOptions = array('appid', 'query', 'results', 'start', 'sort', 'radius', 'street',
                              'city', 'state', 'zip', 'location', 'latitude', 'longitude');

        $this->_compareOptions($options, $validOptions);

        /**
         * @see Zend_Validate_Between
         */
        require_once 'Zend/Validate/Between.php';
        $between = new Zend_Validate_Between(1, 20, true);

        if (isset($options['results']) && !$between->setMin(1)->setMax(20)->isValid($options['results'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'results': {$options['results']}");
        }

        if (isset($options['start']) && !$between->setMin(1)->setMax(1000)->isValid($options['start'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'start': {$options['start']}");
        }

        if (isset($options['longitude']) && !$between->setMin(-90)->setMax(90)->isValid($options['longitude'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'longitude': {$options['longitude']}");
        }

        if (isset($options['latitude']) && !$between->setMin(-180)->setMax(180)->isValid($options['latitude'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'latitude': {$options['latitude']}");
        }

        if (isset($options['zip']) && !preg_match('/(^\d{5}$)|(^\d{5}-\d{4}$)/', $options['zip'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'zip': {$options['zip']}");
        }

        $hasLocation = false;
        $locationFields = array('street', 'city', 'state', 'zip', 'location');
        foreach ($locationFields as $field) {
            if (isset($options[$field]) && $options[$field] != '') {
                $hasLocation = true;
                break;
            }
        }

        if (!$hasLocation && (!isset($options['latitude']) || !isset($options['longitude']))) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('Location data are required but missing');
        }

        if (!in_array($options['sort'], array('relevance', 'title', 'distance', 'rating'))) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'sort': {$options['sort']}");
        }
    }


    /**
     * Validate News Search Options
     *
     * @param  array $options
     * @throws Zend_Service_Exception
     * @return void
     */
    protected function _validateNewsSearch(array $options)
    {
        $validOptions = array('appid', 'query', 'results', 'start', 'sort', 'language', 'type', 'site');

        $this->_compareOptions($options, $validOptions);

        /**
         * @see Zend_Validate_Between
         */
        require_once 'Zend/Validate/Between.php';
        $between = new Zend_Validate_Between(1, 50, true);

        if (isset($options['results']) && !$between->setMin(1)->setMax(50)->isValid($options['results'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'results': {$options['results']}");
        }

        if (isset($options['start']) && !$between->setMin(1)->setMax(1000)->isValid($options['start'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'start': {$options['start']}");
        }

        $this->_validateLanguage($options['language']);

        $this->_validateInArray('sort', $options['sort'], array('rank', 'date'));
        $this->_validateInArray('type', $options['type'], array('all', 'any', 'phrase'));
    }


    /**
     * Validate Web Search Options
     *
     * @param  array $options
     * @throws Zend_Service_Exception
     * @return void
     */
    protected function _validateWebSearch(array $options)
    {
        $validOptions = array('appid', 'query', 'results', 'start', 'language', 'type', 'format', 'adult_ok',
                              'similar_ok', 'country', 'site', 'subscription', 'license');

        $this->_compareOptions($options, $validOptions);

        /**
         * @see Zend_Validate_Between
         */
        require_once 'Zend/Validate/Between.php';
        $between = new Zend_Validate_Between(1, 100, true);

        if (isset($options['results']) && !$between->setMin(1)->setMax(100)->isValid($options['results'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'results': {$options['results']}");
        }

        if (isset($options['start']) && !$between->setMin(1)->setMax(1000)->isValid($options['start'])) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option 'start': {$options['start']}");
        }

        $this->_validateLanguage($options['language']);

        $this->_validateInArray('type', $options['type'], array('all', 'any', 'phrase'));
        $this->_validateInArray('format', $options['format'], array('any', 'html', 'msword', 'pdf', 'ppt', 'rss',
                                                                    'txt', 'xls'));
        $this->_validateInArray('license', $options['license'], array('any', 'cc_any', 'cc_commercial',
                                                                      'cc_modifiable'));
    }


    /**
     * Prepare options for sending to Yahoo!
     *
     * @param  string $query          Search Query
     * @param  array  $options        User specified options
     * @param  array  $defaultOptions Required/Default options
     * @return array
     */
    protected function _prepareOptions($query, array $options, array $defaultOptions = array())
    {
        $options['appid'] = $this->appId;
        $options['query'] = (string) $query;

        return array_merge($defaultOptions, $options);
    }


    /**
     * Throws an exception if the chosen language is not supported
     *
     * @param  string $lang Language code
     * @throws Zend_Service_Exception
     * @return void
     */
    protected function _validateLanguage($lang)
    {
        $languages = array('ar', 'bg', 'ca', 'szh', 'tzh', 'hr', 'cs', 'da', 'nl', 'en', 'et', 'fi', 'fr', 'de', 'el',
            'he', 'hu', 'is', 'id', 'it', 'ja', 'ko', 'lv', 'lt', 'no', 'fa', 'pl', 'pt', 'ro', 'ru', 'sk', 'sr', 'sl',
            'es', 'sv', 'th', 'tr'
            );
        if (!in_array($lang, $languages)) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("The selected language '$lang' is not supported");
        }
    }


    /**
     * Utility function to check for a difference between two arrays.
     *
     * @param  array $options      User specified options
     * @param  array $validOptions Valid options
     * @throws Zend_Service_Exception if difference is found (e.g., unsupported query option)
     * @return void
     */
    protected function _compareOptions(array $options, array $validOptions)
    {
        $difference = array_diff(array_keys($options), $validOptions);
        if ($difference) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('The following parameters are invalid: ' . join(', ', $difference));
        }
    }


    /**
     * Check that a named value is in the given array
     *
     * @param  string $name  Name associated with the value
     * @param  mixed  $value Value
     * @param  array  $array Array in which to check for the value
     * @throws Zend_Service_Exception
     * @return void
     */
    protected function _validateInArray($name, $value, array $array)
    {
        if (!in_array($value, $array)) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Invalid value for option '$name': $value");
        }
    }


    /**
     * Check if response is an error
     *
     * @param  DOMDocument $dom DOM Object representing the result XML
     * @throws Zend_Service_Exception Thrown when the result from Yahoo! is an error
     * @return void
     */
    protected static function _checkErrors(DOMDocument $dom)
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('yapi', 'urn:yahoo:api');

        if ($xpath->query('//yapi:Error')->length >= 1) {
            $message = $xpath->query('//yapi:Error/yapi:Message/text()')->item(0)->data;
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception($message);
        }
    }
}
