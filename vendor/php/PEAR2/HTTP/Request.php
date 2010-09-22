<?php
/**
 * class to do http requests, uses a adapter based system for performing those requests
 *
 * Loosely Based on PEAR HTTP_Response
 *
 * @version $Id$
 */
namespace PEAR2\HTTP;
class Request 
{

    /**
     * The adapter that the requester uses.
     *
     * @see adapters
     */
    protected $adapter;

    /**
     * The listeners
     *
     * This variable contains the listeners that are
     * set (can be set) on this object.
     *
     * @var array $_listeners  The listeners
     */
    protected $_listeners = array();
    
    /**
     * Magic to retrieve items that are actually stored in the adapter
     *
     * @param  string $name name of var to get
     */
    public function __get($name)
    {
        if (isset($this->adapter->$name)) {
            return $this->adapter->$name;
        }
    }

    /**
     * Magic to set items that are actually stored in the adapter
     *
     * @param  string $name name of var to set
     * @param  mixed $value to give to var
     */
    public function __set($name, $value)
    {
        switch($name) {
            case 'verb':
                $this->adapter->verb = strtoupper($value);
                break;
            case 'uri':
            case 'url':
                $this->adapter->uri = new Request\Uri($value);
                break;
            case 'body':
            case 'content':
                if (is_array($value)) {
                    $this->adapter->body = http_build_query($value);
                    $this->setHeader('Content-Type','application/x-www-form-urlencoded');
                    if ($this->adapter->verb == 'GET') {
                        $this->adapter->verb = 'POST';
                    }
                } else {
                    $this->adapter->body = $value;
                }
                break;
            case 'requestTimeout':
                $this->adapter->$name = (int)$value;
                break;
            case 'proxy':
                $this->adapter->$name = new Request\Uri($value);
                break;
            default:
                $this->adapter->$name = $value;
                break;
        }
    }

    /**
     * sets up the adapter
     *
     * @param string                     $url      URL for this request
     * @param PEAR2\HTTP\Request\Adapter $instance The adapter to use
     */
    public function __construct($url = null, $instance = null) 
    {
        if (!is_null($instance) && $instance instanceof Request\Adapter) {
            $this->adapter = $instance;
        } elseif (extension_loaded('curl')) {
            $this->adapter = new Request\Adapter\Curl;
        } elseif (extension_loaded('http')) {
            $this->adapter = new Request\Adapter\Http;
        } elseif (ini_get('allow_url_fopen') == true) {
            $this->adapter = new Request\Adapter\Phpstream;
        } else {
            $this->adapter = new Request\Adapter\Phpsocket;
        }

        $this->adapter->setListeners($this->_listeners);

        if ($url) {
            $this->url = $url;
        }
    }

    /**
     * asks for a response class from the adapter
     *
     * @return PEAR2\HTTP\Request\Response
     */
    public function sendRequest() 
    {
        $response = $this->adapter->sendRequest();
        return $response;
    }

    /**
     * Sends a request storing the output to a file
     *
     * @param  string $file File to store too
     * @return PEAR2\HTTP\Request\Response with no body
     */
    public function requestToFile($file)
    {
        $response = $this->adapter->requestToFile($file);
        return $response;
    }

    /**
     * Setter for request headers
     * 
     * @see $this->adapter->headers
     */
    public function setHeader($header, $value) 
    {
        $this->adapter->headers[$header] = $value;
    }

    /**
     * Attach a listener
     *
     * This method adds a listener to the list of listeners that are 
     * notified of the object's events.
     *
     * Events sent by the HTTP\Request Object
     *  - 'connect'     : On connection to server
     *  - 'sentRequest' : After the request was sent to server
     *  - 'disconnect'  : Upon server disconnection
     *
     * Events sent by the HTTP\Response object
     *  - 'gotHeaders' : After receiving response header
     *  - 'tick'       : On receiving part of response
     *  - 'gzTick'     : On receiving a gzip-encoded part
     *  - 'gotBody'    : Upon receiving body of the message
     *
     *
     * @param  PEAR2\HTTP\Request\Listener $listener  The listener object
     * @return boolean Whether object is a listener or not
     */
    public function attach(Request\Listener $listener)
    {
        $this->_listeners[$listener->getId()] = $listener;
        $this->adapter->setListeners($this->_listeners);
        return true;
    }

    /**
     * Detach a listener
     *
     * This method will detach the listener that was set
     * to a request.
     *
     * @param  PEAR2\HTTP\Request\Listener $listener   The listener
     * @return bool true
     */
    public function detach(Request\Listener $listener)
    {
        if (isset($this->_listeners[$listener->getId()])) {
            $this->_listeners[$listener->getId()] = null;
        }
        $this->adapter->setListeners($this->_listeners);

        return true;
    }

    /**
     * Notify
     *
     * This method notifies all registered listeners of
     * the event that just happened.
     *
     * @param     string  $event  The event name
     * @param     mixed  $data   Additional data
     * @see       PEAR2\HTTP\Request->attach()
     * @return    void
     */
    protected function _notify($event, $data = null)
    {
        if (!empty($this->_listeners)) {
            foreach (array_keys($this->_listeners) as $id) {
                $this->_listeners[$id]->update($this, $event, $data);
            }
        }
    }

    /**
     * Get the class name of the adapter that is being used
     */
    public function getAdapterName()
    {
        return get_class($this->adapter);
    }
}
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
?>
