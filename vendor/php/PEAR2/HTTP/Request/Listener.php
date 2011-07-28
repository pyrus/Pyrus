<?php
/**
 * Listener for PEAR2_HTTP_Request and PEAR2_HTTP_Response objects
 *
 * PHP version 5
 * 
 * LICENSE:
 *
 * Copyright (c) 2002-2007, Richard Heyes
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * o Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * o Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * o The names of the authors may not be used to endorse or promote
 *   products derived from this software without specific prior written
 *   permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    PEAR2::HTTP
 * @package     PEAR2_HTTP_Request
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2002-2007 Richard Heyes
 * @license     http://opensource.org/licenses/bsd-license.php New BSD License 
 */

/**
* Listener for PEAR2_HTTP_Request and PEAR2_HTTP_Response objects
*
* This class implements the Observer part of a Subject-Observer
* design pattern.
*
* @category    HTTP
* @package     HTTP_Request
* @author      Alexey Borzov <avb@php.net>
* @version     Release: @package_version@
*/
namespace PEAR2\HTTP\Request;
class Listener 
{
    /**
    * A listener's identifier
    *
    * @var string
    */
    private $_id;

    /**
     * A current log of the activities
     */
    private $_log = array(
        'notifications' => array(),
        'extraData'     => array(),
    );

    /**
    * Constructor, sets the object's identifier
    *
    * @access public
    */
    public function __construct()
    {
        $this->_id = md5(sha1('http_request_') . time());
    }


    /**
    * Returns the listener's identifier
    *
    * @access public
    * @return string
    */
    public function getId()
    {
        return $this->_id;
    }

    /**
    * This method is called when Listener is notified of an event
    *
    * @access   public
    * @param    object  an object the listener is attached to Is that even used?
    * @param    string  Event name - connect|sentRequest|disconnect|gotHeaders|gzTick|tick|gotBody
    * @param    mixed   Additional data
    * @abstract
    */
    public function update($subject, $event, $data = null)
    {
        $this->_log[$this->_id]['notifications'][] = "Notified of event: '$event'\n";
        if ($data !== null) {
            $tmpMessage  = "Additional data: ";
            $tmpMessage .= print_r($data, true);

            $this->_log[$this->_id]['extraData'][] = $tmpMessage;
        }
    }
}
?>
