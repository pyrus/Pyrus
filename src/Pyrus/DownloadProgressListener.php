<?php
/**
 * This PEAR2_HTTP_Request listener implements a download progress bar
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * This PEAR2_HTTP_Request listener implements a download progress bar
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

class PEAR2_Pyrus_DownloadProgressListener extends PEAR2_HTTP_Request_Listener
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
                $this->preview = "Connected...\n";
                break;
            case 'disconnect' :
                if (!$this->preview) {
                    echo "done\n";
                }
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
                    echo $this->preview;
                    $this->preview = '';
                    if (!isset($this->filesize)) {
                        printf("\rUnknown filesize.. %2d kb done..", $data/1024);
                    } else {
                        $length = (int)(($data/$this->filesize)*100);
                        printf("\r[%-100s] %d%% (%2d/%2d kb)", str_repeat("=", $length). ">", $length,
                               ($data/1024), $this->filesize/1024);
                    }
                }
                break;
        }
    }
}
?>

