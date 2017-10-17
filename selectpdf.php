<?php

// Copyright (C) 2014-2017 selectpdf.com
// https://selectpdf.com
// 
// Permission is hereby granted, free of charge, to any person
// obtaining a copy of this software and associated documentation
// files (the "Software"), to deal in the Software without
// restriction, including without limitation the rights to use,
// copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the
// Software is furnished to do so, subject to the following
// conditions:
// 
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
// OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
// OTHER DEALINGS IN THE SOFTWARE.

namespace {

//
// Custom exception thrown when an error occurs.
// 
class SelectPdfException extends Exception {
    public function __toString() {
        if ($this->code) {
            return "[{$this->code}] {$this->message}\n";
        } else {
            return "{$this->message}\n";
        }
    }
}

//
// SelectPdf HTML to PDF REST API - PHP client.
// 
class SelectPdf {
    //
    // SelectPdf API client constructor.
    // 
    // $key  - SelectPdf REST API key
    // 
    function __construct($key){
        $this->fields = array(
            'key' => $key
		);
    }

    //
    // Converts a html string to PDF.
    //
    // $html      - a string containing a html document
    // $outstream - output stream, if null then the return value is a string
    //              containing the PDF
    // 
    function convertHtmlString($html, $base_url='', $outstream=null){
        if (!$html) {
            throw new SelectPdfException("convertHtmlString(): the html parameter must not be empty");
        }
        
        $this->fields['html'] = $html;
        if (!$base_url) {
			$this->fields['base_url'] = $base_url;
		}
        $postfields = http_build_query($this->fields, '', '&');
        return $this->http_post(self::$api_endpoint_convert, $postfields, $outstream);
    }

    //
    // Converts a web page to PDF.
    //
    // $url       - a web page URL
    // $outstream - output stream, if null then the return value is a string
    //              containing the PDF
    // 
    function convertUrl($url, $outstream=null){
        $url = trim($url);
        if (!$url) {
            throw new SelectPdfException("convertUrl(): the url parameter must not be empty");
        }
        if (!preg_match("/^https?:\/\/.*/i", $url)) {
            throw new SelectPdfException("convertUrl(): the URL must start with http:// or https:// (currently '$url')");
        }
        
        $this->fields['url'] = $url;
        $postfields = http_build_query($this->fields, '', '&');
        return $this->http_post(self::$api_endpoint_convert, $postfields, $outstream);
    }

    //
    // Returns the number of available conversions.
    // 
    function availableConversions() {
        $arr = array('key' => $this->fields['key']);
        $postfields = http_build_query($arr, '', '&');
        $response = $this->http_post(self::$api_endpoint_usage, $postfields, NULL);
		$json = json_decode($response);
        return (int)$json->{'available'};
    }

	//
    // Returns the usage details as an associative array.
    // 
    function usageDetails($get_history = false) {
        $arr = array('key' => $this->fields['key']);
		if ($get_history) {
			$arr['get_history'] = "True";
		}
        $postfields = http_build_query($arr, '', '&');
        $response = $this->http_post(self::$api_endpoint_usage, $postfields, NULL);
		$json_array = json_decode($response, true);
        return $json_array;
    }

	//
	// Specifies the page size of the generated pdf document. The default value is A4. All possible values are: A1, A2, A3, A4, A5, Letter, HalfLetter, Ledger, Legal.
	//
    function setPageSize($value) {
        $this->fields['page_size'] = $value;
    }
    
	//
	// 	Specifies the page orientation of the generated pdf document. The default value is Portrait. All possible values are: Portrait, Landscape.
	//
    function setPageOrientation($value) {
        $this->fields['page_orientation'] = $value;
    }

	//
	// Set page margins. The margins are specified in points. 1 point is 1 / 72 inch. By default all margins are 5pt.
	//    
    function setHorizontalMargin($value) {
        $this->fields['margin_right'] = $this->fields['margin_left'] = $value;
    }
    
	//
	// Set page margins. The margins are specified in points. 1 point is 1 / 72 inch. By default all margins are 5pt.
	//    
    function setVerticalMargin($value) {
        $this->fields['margin_top'] = $this->fields['margin_bottom'] = $value;
    }

	//
	// Set page margins. The margins are specified in points. 1 point is 1 / 72 inch. By default all margins are 5pt.
	//    
    function setPageMargins($top, $right, $bottom, $left) {
      $this->fields['margin_top'] = $top;
      $this->fields['margin_right'] = $right;
      $this->fields['margin_bottom'] = $bottom;
      $this->fields['margin_left'] = $left;
    }

	//
	// Set page margins. The margins are specified in points. 1 point is 1 / 72 inch. By default all margins are 5pt.
	//    
    function setAllMargins($value) {
      $this->fields['margin_top'] = $value;
      $this->fields['margin_right'] = $value;
      $this->fields['margin_bottom'] = $value;
      $this->fields['margin_left'] = $value;
    }

    function setTimeout($timeout) {
        if (is_int($timeout) && $timeout > 0) {
            $this->curlopt_timeout = $timeout;
        }
    }
    
    


    // ----------------------------------------------------------------------
    //
    //                        Private stuff
    //

    private $fields, $curlopt_timeout;

    public static $api_endpoint_convert = "https://selectpdf.com/api2/convert/";
    public static $api_endpoint_usage = "https://selectpdf.com/api2/usage/";

    private static $missing_curl_error = 'selectpdf.php requires cURL which is not installed on your system.

How to install:
  Windows: uncomment/add the "extension=php_curl.dll" line in php.ini
  Linux:   should be a part of the distribution, 
           e.g. on Debian/Ubuntu run "sudo apt-get install php5-curl"

You need to restart your web server after installation.

Links:
 Installing the PHP/cURL binding:  <https://curl.haxx.se/libcurl/php/install.html>
 PHP/cURL documentation:           <http://www.php.net/manual/en/book.curl.php>';


    private function http_post($url, $postfields, $outstream) {
        if (!function_exists("curl_init")) {
            throw new SelectPdfException(self::$missing_curl_error);
        }

		$ch = curl_init($url);
        //curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
		//curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
		if (isset($this->curlopt_timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->curlopt_timeout);
        }
        if ($outstream) {
            $this->outstream = $outstream;
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'receive_to_stream'));
        }

		$request = curl_getinfo($ch);
		//var_dump($request);

        $this->http_code = 0;
        $this->error = "";

        $response = curl_exec($ch);

        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error_str = curl_error($ch);
        $error_nr = curl_errno($ch);
        curl_close($ch);

        if ($error_nr != 0) {
            throw new SelectPdfException($error_str, $error_nr);            
        }
        else if ($this->http_code == 200) {
            if ($outstream == NULL) {
                return $response;
            }
        } else {
            throw new SelectPdfException($this->error ? $this->error : $response, $this->http_code);
        }
    }

    private function receive_to_stream($curl, $data) {
        if ($this->http_code == 0) {
            $this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }

        if ($this->http_code >= 400) {
            $this->error = $this->error . $data;
            return strlen($data);
        }
        
        $written = fwrite($this->outstream, $data);
        if ($written != strlen($data)) {
            if (get_magic_quotes_runtime()) {
                throw new SelectPdfException("Cannot write the PDF file because the 'magic_quotes_runtime' setting is enabled.
Please disable it either in your php.ini file, or in your code by calling 'set_magic_quotes_runtime(false)'.");
            } else {
                throw new SelectPdfException('Writing the PDF file failed. An error occurred.');
            }
        }
        return $written;
    }

    private function set_or_unset($val, $field) {
        if ($val)
            $this->fields[$field] = $val;
        else
            unset($this->fields[$field]);
    }
}

}

?>
