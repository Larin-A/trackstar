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
 * @version    $Id: Simpy.php 8055 2008-02-15 21:42:54Z thomas $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy
{
    /**
     * Base URI to which API methods and parameters will be appended
     *
     * @var string
     */
    protected $_baseUri = 'http://simpy.com';

    /**
     * Zend_Service_Rest object
     *
     * @var Zend_Service_Rest
     */
    protected $_rest;

    /**
     * Constructs a new Simpy (free) REST API Client
     *
     * @param  string $username Username for the Simpy user account
     * @param  string $password Password for the Simpy user account
     * @return void
     */
    public function __construct($username, $password)
    {
        /**
         * @see Zend_Service_Rest
         */
        require_once 'Zend/Rest/Client.php';
        $this->_rest = new Zend_Rest_Client($this->_baseUri);
        $this->_rest->getHttpClient()
            ->setAuth($username, $password);
    }

    /**
     * Sends a request to the REST API service and does initial processing
     * on the response.
     *
     * @param  string       $op    Name of the operation for the request
     * @param  string|array $query Query data for the request (optional)
     * @throws Zend_Service_Exception
     * @return DOMDocument Parsed XML response
     */
    protected function _makeRequest($op, $query = null)
    {
        if ($query != null) {
            $query = array_diff($query, array_filter($query, 'is_null'));
        }

        $response = $this->_rest->restGet('/simpy/api/rest/' . $op . '.do', $query);

        if ($response->isSuccessful()) {
            $doc = new DOMDocument();
            $doc->loadXML($response->getBody());
            $xpath = new DOMXPath($doc);
            $list = $xpath->query('/status/code');

            if ($list->length > 0) {
                $code = $list->item(0)->nodeValue;

                if ($code != 0) {
                    $list = $xpath->query('/status/message');
                    $message = $list->item(0)->nodeValue;
                    /**
                     * @see Zend_Service_Exception
                     */
                    require_once 'Zend/Service/Exception.php';
                    throw new Zend_Service_Exception($message, $code);
                }
            }

            return $doc;
        }

        /**
         * @see Zend_Service_Exception
         */
        require_once 'Zend/Service/Exception.php';
        throw new Zend_Service_Exception('HTTP ' . $response->getStatus());
    }

    /**
     * Returns a list of all tags and their counts, ordered by count in
     * decreasing order
     *
     * @param  int $limit Limits the number of tags returned (optional)
     * @see    http://www.simpy.com/doc/api/rest/GetTags
     * @throws Zend_Service_Exception
     * @return Zend_Service_Simpy_TagSet
     */
    public function getTags($limit = null)
    {
        $query = array(
            'limit' => $limit
        );

        $doc = $this->_makeRequest('GetTags', $query);

        /**
         * @see Zend_Service_Simpy_TagSet
         */
        require_once 'Zend/Service/Simpy/TagSet.php';
        return new Zend_Service_Simpy_TagSet($doc);
    }

    /**
     * Removes a tag.
     *
     * @param  string $tag Tag to be removed
     * @see    http://www.simpy.com/doc/api/rest/RemoveTag
     * @return Zend_Service_Simpy Provides a fluent interface
     */
    public function removeTag($tag)
    {
        $query = array(
            'tag' => $tag
        );

        $this->_makeRequest('RemoveTag', $query);

        return $this;
    }

    /**
     * Renames a tag.
     *
     * @param  string $fromTag Tag to be renamed
     * @param  string $toTag   New tag name
     * @see    http://www.simpy.com/doc/api/rest/RenameTag
     * @return Zend_Service_Simpy Provides a fluent interface
     */
    public function renameTag($fromTag, $toTag)
    {
        $query = array(
            'fromTag' => $fromTag,
            'toTag' => $toTag
        );

        $this->_makeRequest('RenameTag', $query);

        return $this;
    }

    /**
     * Merges two tags into a new tag.
     *
     * @param  string $fromTag1 First tag to merge.
     * @param  string $fromTag2 Second tag to merge.
     * @param  string $toTag    Tag to merge the two tags into.
     * @see    http://www.simpy.com/doc/api/rest/MergeTags
     * @return Zend_Service_Simpy Provides a fluent interface
     */
    public function mergeTags($fromTag1, $fromTag2, $toTag)
    {
        $query = array(
            'fromTag1' => $fromTag1,
            'fromTag2' => $fromTag2,
            'toTag' => $toTag
        );

        $this->_makeRequest('MergeTags', $query);

        return $this;
    }

    /**
     * Splits a single tag into two separate tags.
     *
     * @param  string $tag    Tag to split
     * @param  string $toTag1 First tag to split into
     * @param  string $toTag2 Second tag to split into
     * @see    http://www.simpy.com/doc/api/rest/SplitTag
     * @return Zend_Service_Simpy Provides a fluent interface
     */
    public function splitTag($tag, $toTag1, $toTag2)
    {
        $query = array(
            'tag' => $tag,
            'toTag1' => $toTag1,
            'toTag2' => $toTag2
        );

        $this->_makeRequest('SplitTag', $query);

        return $this;
    }

    /**
     * Performs a query on existing links and returns the results or returns all
     * links if no particular query is specified (which should be used sparingly
     * to prevent overloading Simpy servers)
     *
     * @param  Zend_Service_Simpy_LinkQuery $q Query object to use (optional)
     * @return Zend_Service_Simpy_LinkSet
     */
    public function getLinks(Zend_Service_Simpy_LinkQuery $q = null)
    {
        if ($q != null) {
            $query = array(
                'q'          => $q->getQueryString(),
                'limit'      => $q->getLimit(),
                'date'       => $q->getDate(),
                'afterDate'  => $q->getAfterDate(),
                'beforeDate' => $q->getBeforeDate()
            );

            $doc = $this->_makeRequest('GetLinks', $query);
        } else {
            $doc = $this->_makeRequest('GetLinks');
        }

        /**
         * @see Zend_Service_Simpy_LinkSet
         */
        require_once 'Zend/Service/Simpy/LinkSet.php';
        return new Zend_Service_Simpy_LinkSet($doc);
    }

    /**
     * Saves a given link.
     *
     * @param  string $title       Title of the page to save
     * @param  string $href        URL of the page to save
     * @param  int    $accessType  ACCESSTYPE_PUBLIC or ACCESSTYPE_PRIVATE
     * @param  mixed  $tags        String containing a comma-separated list of
     *                             tags or array of strings containing tags
     *                             (optional)
     * @param  string $urlNickname Alternative custom title (optional)
     * @param  string $note        Free text note (optional)
     * @link   Zend_Service_Simpy::ACCESSTYPE_PUBLIC
     * @link   Zend_Service_Simpy::ACCESSTYPE_PRIVATE
     * @see    http://www.simpy.com/doc/api/rest/SaveLink
     * @return Zend_Service_Simpy Provides a fluent interface
     */
    public function saveLink($title, $href, $accessType, $tags = null, $urlNickname = null, $note = null)
    {
        if (is_array($tags)) {
            $tags = implode(',', $tags);
        }

        $query = array(
            'title'       => $title,
            'href'        => $href,
            'accessType'  => $accessType,
            'tags'        => $tags,
            'urlNickname' => $urlNickname,
            'note'        => $note
        );

        $this->_makeRequest('SaveLink', $query);

        return $this;
    }

    /**
     * Deletes a given link.
     *
     * @param  string $href URL of the bookmark to delete
     * @see    http://www.simpy.com/doc/api/rest/DeleteLink
     * @return Zend_Service_Simpy Provides a fluent interface
     */
    public function deleteLink($href)
    {
        $query = array(
            'href' => $href
        );

        $this->_makeRequest('DeleteLink', $query);

        return $this;
    }

    /**
     * Return a list of watchlists and their meta-data, including the number
     * of new links added to each watchlist since last login.
     *
     * @see    http://www.simpy.com/doc/api/rest/GetWatchlists
     * @return Zend_Service_Simpy_WatchlistSet
     */
    public function getWatchlists()
    {
        $doc = $this->_makeRequest('GetWatchlists');

        /**
         * @see Zend_Service_Simpy_WatchlistSet
         */
        require_once 'Zend/Service/Simpy/WatchlistSet.php';
        return new Zend_Service_Simpy_WatchlistSet($doc);
    }

    /**
     * Returns the meta-data for a given watchlist.
     *
     * @param  int $watchlistId ID of the watchlist to retrieve
     * @see    http://www.simpy.com/doc/api/rest/GetWatchlist
     * @return Zend_Service_Simpy_Watchlist
     */
    public function getWatchlist($watchlistId)
    {
        $query = array(
            'watchlistId' => $watchlistId
        );

        $doc = $this->_makeRequest('GetWatchlist', $query);

        /**
         * @see Zend_Service_Simpy_Watchlist
         */
        require_once 'Zend/Service/Simpy/Watchlist.php';
        return new Zend_Service_Simpy_Watchlist($doc->documentElement);
    }

    /**
     * Returns all notes in reverse chronological order by add date or by
     * rank.
     *
     * @param  string $q     Query string formatted using Simpy search syntax
     *                       and search fields (optional)
     * @param  int    $limit Limits the number notes returned (optional)
     * @see    http://www.simpy.com/doc/api/rest/GetNotes
     * @see    http://www.simpy.com/simpy/FAQ.do#searchSyntax
     * @see    http://www.simpy.com/simpy/FAQ.do#searchFieldsLinks
     * @return Zend_Service_Simpy_NoteSet
     */
    public function getNotes($q = null, $limit = null)
    {
        $query = array(
            'q'     => $q,
            'limit' => $limit
        );

        $doc = $this->_makeRequest('GetNotes', $query);

        /**
         * @see Zend_Service_Simpy_NoteSet
         */
        require_once 'Zend/Service/Simpy/NoteSet.php';
        return new Zend_Service_Simpy_NoteSet($doc);
    }

    /**
     * Saves a note.
     *
     * @param  string $title       Title of the note
     * @param  mixed  $tags        String containing a comma-separated list of
     *                             tags or array of strings containing tags
     *                             (optional)
     * @param  string $description Free-text note (optional)
     * @param  int    $noteId      Unique identifier for an existing note to
     *                             update (optional)
     * @see    http://www.simpy.com/doc/api/rest/SaveNote
     * @return Zend_Service_Simpy Provides a fluent interface
     */
    public function saveNote($title, $tags = null, $description = null, $noteId = null)
    {
        if (is_array($tags)) {
            $tags = implode(',', $tags);
        }

        $query = array(
            'title'       => $title,
            'tags'        => $tags,
            'description' => $description,
            'noteId'      => $noteId
        );

        $this->_makeRequest('SaveNote', $query);

        return $this;
    }

    /**
     * Deletes a given note.
     *
     * @param  int $noteId ID of the note to delete
     * @see    http://www.simpy.com/doc/api/rest/DeleteNote
     * @return Zend_Service_Simpy Provides a fluent interface
     */
    public function deleteNote($noteId)
    {
        $query = array(
            'noteId' => $noteId
        );

        $this->_makeRequest('DeleteNote', $query);

        return $this;
    }
}
