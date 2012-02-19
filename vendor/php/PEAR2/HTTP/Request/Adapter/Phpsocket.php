<?php
namespace PEAR2\HTTP\Request\Adapter;
use PEAR2\HTTP\Request;
class Phpsocket_Socket
{
    public $lineLength = 2048;
    private $_handle;

    public function __construct($handle)
    {
        $this->_handle = $handle;
    }

    public function readLine()
    {
        $line = '';
        while(!$this->eof()) {
            $line .= @fgets($this->_handle, $this->lineLength);
            if (substr($line, -1) == "\n") {
                return rtrim($line, "\r\n");
            }
        }
        return false;
    }

    public function read($size)
    {
        if ($this->eof()) {
            return false;
        }
        return @fread($this->_handle,$size);
    }

    public function write($payload)
    {
        return fwrite($this->_handle,$payload,strlen($payload));
    }

    public function eof()
    {
        return feof($this->_handle);
    }
}

/**
 * A class which represents an Http Reponse
 * Handles parsing cookies and headers
 *
 * Based on PEAR HTTP_Response
 *
 * @version $Revision: 1.52 $
 */
class Phpsocket extends Request\Adapter
{   
    /**
     * Used by _readChunked(): remaining length of the current chunk
     * @var string
     */
    private $_chunkLength = 0;

    /**
     * Bytes left to read from message-body
     * @var null|int
     */
    private $_toRead = null;

    /**
     * Raw Response to be parsed
     */
    private $_stream;

    public function sendRequest()
    {
        $payload = $this->_buildHeaders($this->uri->path,$this->uri->host,$this->headers,strlen($this->body));
        $payload .= $this->body;
        $this->body = '';

        $errno    = 0;
        $errstr   = '';
        $handle   = @fsockopen($this->uri->host, $this->uri->port, $errno, $errstr, 30);

        if (!is_resource($handle)) {
            throw new Request\Exception("Couldn't connection to host using Phpsocket Adapter, fsockopen errors($errstr,$errno)");
        }
        stream_set_timeout($handle,10);

        $this->_stream = new Phpsocket_Socket($handle);

        $this->_stream->write($payload);

        $this->parse();
        $this->_notify('disconnect');

        $details['code'] = $this->code;
        $details['httpVersion'] = $this->httpVersion;


        $response = new Request\Response(
            $details,$this->body,new Request\Headers($this->headers),$this->cookies);
        return $response;
    }

    /**
     * Parse a HTTP response
     * 
     * This extracts response code, headers, cookies and decodes body if it 
     * was encoded in some way
     *
     * @access public
     * @param  bool      Whether to store response body in object property, set
     *           this to false if downloading a LARGE file and using a Listener.
     *           This is assumed to be true if body is gzip-encoded.
     * @param  bool      Whether the response can actually have a message-body.
     *           Will be set to false for HEAD requests.
     * @throws Exception
     * @return boolean     true on success
     */
    public function parse($saveBody = true, $canHaveBody = true)
    {
    do {
        $line = $this->_stream->readLine();
        $code = $this->parseResponseCode($line);
        $this->httpVersion = 'HTTP/' . $code['httpVersion'];
        $this->code     = $code['code'];

        while ('' !== ($header = $this->_stream->readLine())) {
            $this->processHeader($header);
        }
    } while ($this->code == 100);

    // RFC 2616, section 4.4:
    // 1. Any response message which "MUST NOT" include a message-body ... 
    // is always terminated by the first empty line after the header fields 
    // 3. ... If a message is received with both a
    // Transfer-Encoding header field and a Content-Length header field,
    // the latter MUST be ignored.
    $canHaveBody = $canHaveBody && $this->code >= 200 && 
               $this->code != 204 && $this->code != 304;

    // If response body is present, read it and decode
    $chunked = isset($this->headers['transfer-encoding']) && ('chunked' == $this->headers['transfer-encoding']);
    $gzipped = isset($this->headers['content-encoding']) && ('gzip' == $this->headers['content-encoding']);
    $hasBody = false;
    if ($canHaveBody && ($chunked || !isset($this->headers['content-length'])
        || 0 != $this->headers['content-length'])) {
        if ($chunked || !isset($this->headers['content-length'])) {
            $this->_toRead = null;
        } else {
            $this->_toRead = $this->headers['content-length'];
        }
        while (!$this->_stream->eof() && (is_null($this->_toRead) || $this->_toRead > 0)) {
            if ($chunked) {
                $data = $this->_readChunked();
            } elseif (is_null($this->_toRead)) {
                $data = $this->_stream->read(4096);
            } else {
                $data = $this->_stream->read(min(4096, $this->_toRead));
                $this->_toRead -= strlen($data);
            }
            if ($data == '') {
                break;
            } else {
                $hasBody = true;
                if ($saveBody || $gzipped) {
                    $this->body .= $data;
                }
            }
        }
    }

    if ($hasBody) {
        // Uncompress the body if needed
        if ($gzipped) {
            $body = $this->_decodeGzip($this->body);
            // FIXME: PEAR::isError?!?!?!
            if (PEAR::isError($body)) {
                return $body;
            }
            $this->body = $body;
        }
    }
    return true;
    }

   /**
    * Read a part of response body encoded with chunked Transfer-Encoding
    * 
    * @access private
    * @return string
    */
    private function _readChunked()
    {
        // at start of the next chunk?
        if (0 == $this->_chunkLength) {
            $line = $this->_stream->readLine();
            if (preg_match('/^([0-9a-f]+)/i', $line, $matches)) {
                $this->_chunkLength = hexdec($matches[1]); 
                // Chunk with zero length indicates the end
                if (0 == $this->_chunkLength) {
                    $this->_stream->readLine(); // make this an eof()
                    return '';
                }
            } else {
                return '';
            }
        }
        $data = $this->_stream->read($this->_chunkLength);
        $this->_chunkLength -= strlen($data);
        if (0 == $this->_chunkLength) {
            $this->_stream->readLine(); // Trailing CRLF
        }
        return $data;
    }

   /**
    * Decodes the message-body encoded by gzip
    *
    * The real decoding work is done by gzinflate() built-in function, this
    * method only parses the header and checks data for compliance with
    * RFC 1952  
    *
    * @access   private
    * @param    string  gzip-encoded data
    * @return   string  decoded data
    */
    private function _decodeGzip($data)
    {
        $length = strlen($data);
        // If it doesn't look like gzip-encoded data, don't bother
        if ($length < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
            return $data;
        }
        $method = ord(substr($data, 2, 1));
        if ($method != 8) {
            throw new Request\Exception('_decodeGzip(): unknown compression method');
        }

        $flags = ord(substr($data, 3, 1));

        if ($flags & 224) {
            throw new Request\Exception('_decodeGzip(): reserved bits are set');
        }

        // header is 10 bytes minimum. may be longer, though.
        $headerLength = 10;
        // extra fields, need to skip 'em
        if ($flags & 4) {
            if ($length - $headerLength - 2 < 8) {
                throw new Request\Exception('_decodeGzip(): data too short');
            }
        
            $extraLength = unpack('v', substr($data, 10, 2));
            if ($length - $headerLength - 2 - $extraLength[1] < 8) {
                throw new Request\Exception('_decodeGzip(): data too short');
            }

            $headerLength += $extraLength[1] + 2;
        }
        // file name, need to skip that
        if ($flags & 8) {
            if ($length - $headerLength - 1 < 8) {
                throw new Request\Exception('_decodeGzip(): data too short');
            }
            $filenameLength = strpos(substr($data, $headerLength), chr(0));
            if (false === $filenameLength || $length - $headerLength - $filenameLength - 1 < 8) {
                throw new Request\Exception('_decodeGzip(): data too short');
            }
            $headerLength += $filenameLength + 1;
        }
        // comment, need to skip that also
        if ($flags & 16) {
            if ($length - $headerLength - 1 < 8) {
                throw new Request\Exception('_decodeGzip(): data too short');
            }
            $commentLength = strpos(substr($data, $headerLength), chr(0));
            if (false === $commentLength || $length - $headerLength - $commentLength - 1 < 8) {
                throw new Request\Exception('_decodeGzip(): data too short');
            }
            $headerLength += $commentLength + 1;
        }
        // have a CRC for header. let's check
        if ($flags & 1) {
            if ($length - $headerLength - 2 < 8) {
                throw new Request\Exception('_decodeGzip(): data too short');
            }
            $crcReal   = 0xffff & crc32(substr($data, 0, $headerLength));
            $crcStored = unpack('v', substr($data, $headerLength, 2));
            if ($crcReal != $crcStored[1]) {
                throw new Request\Exception('_decodeGzip(): header CRC check failed');
            }
            $headerLength += 2;
        }
        // unpacked data CRC and size at the end of encoded data
        $tmp = unpack('V2', substr($data, -8));
        $dataCrc  = $tmp[1];
        $dataSize = $tmp[2];

        // finally, call the gzinflate() function
        $unpacked = @gzinflate(substr($data, $headerLength, -8), $dataSize);
        if (false === $unpacked) {
            throw new Request\Exception('_decodeGzip(): gzinflate() call failed');
        } elseif ($dataSize != strlen($unpacked)) {
            throw new Request\Exception('_decodeGzip(): data size check failed');
        } elseif ($dataCrc != crc32($unpacked)) {
            throw new Request\Exception('_decodeGzip(): data CRC check failed');
        }
        return $unpacked;
    }

    private function _buildHeaders($path, $host, $headers,$bodySize)
    {
        $httpRequest  = "$this->verb $path $this->httpVersion\r\n";
        $httpRequest .= "Host: $host\r\n";
        foreach($headers as $key => $value) {
            $httpRequest .= "$key: $value\r\n";
        }
        if ($bodySize > 0) {
            $httpRequest .= "Content-Length:".$bodySize."\r\n";
        }
        $httpRequest .= "\r\n";

        return $httpRequest;
    }
} // End class PEAR2_HTTP_Response
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
?>
