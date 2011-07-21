<?php
/**
 * This PEAR2_HTTP_Request listener implements a download progress bar
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * This PEAR2_HTTP_Request listener implements a download progress bar
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

namespace Pyrus;
class DownloadProgressListener extends \PEAR2\HTTP\Request\Listener
{
    protected $filesize;
    protected $preview;

    /**
    * This method is called when Listener is notified of an event
    *
    * @access   public
    * @param    object  an object the listener is attached to
    * @param    string  Event name
    * @param    mixed   Additional data
    * @abstract
    */
    public function update($subject, $event, $data = null)
    {
        switch ($event) {
            case 'connect' :
                $this->preview = "";
                $this->done = false;
                break;
            case 'disconnect' :
                // Length of meter "[==...=>]" + " 100% (/ kb)" + twice the filesize.
                $length = 103 + 12 + strlen(sprintf("%2d", $this->filesize/1024)) * 2;
                Logger::log(0, str_repeat("\010", $length) . "\r");
                break;
            case 'redirect' :
                $this->preview .= 'Redirected to ' . $data . "\n";
                break;
            case 'filesize' :
                $this->filesize = $data;
                break;
            case 'mime-type' :
                $this->preview .= 'Mime-type: ' . $data . "\n";
                break;
            case 'downloadprogress' :
                // borrowed from fetch.php in php-src
                if ($data > 0) {
                    if ($this->preview) {
                        Logger::log(0, rtrim($this->preview, "\n"));
                        $this->preview = '';
                    }

                    if (!isset($this->filesize) || $this->filesize==0) {
                        Logger::log(0, sprintf("Unknown filesize.. %2d kb done..\r", $data/1024));
                    } else {
                        $length = (int)(($data/$this->filesize)*100);
                        Logger::log(0,
                                sprintf("[%-100s] %d%% (%2d/%2d kb)\r", str_repeat("=", $length). ">", $length,
                               ($data/1024), $this->filesize/1024));
                    }
                }
                break;
        }
    }
}