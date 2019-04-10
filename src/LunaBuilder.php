<?php 
namespace LunaDev\Utility;

use Ixudra\Curl\Builder;

class LunaBuilder extends Builder {
    
    public function to($url)
    {
        $response = $this->withCurlOption( 'URL', $url );

        return $response;
    }
    
    protected function send()
    {
        // Add JSON header if necessary
        if( $this->packageOptions[ 'asJsonRequest' ] ) {
            $this->withHeader( 'Content-Type: application/json' );
        }

        if( $this->packageOptions[ 'enableDebug' ] ) {
            $debugFile = fopen( $this->packageOptions[ 'debugFile' ], 'w');
            $this->withOption('STDERR', $debugFile);
        }

        // Create the request with all specified options
        $this->curlObject = curl_init();
        $options = $this->forgeOptions();
        curl_setopt_array( $this->curlObject, $options );

        // Send the request
        $response = curl_exec( $this->curlObject );

        $responseHeader = null;
        if( $this->curlOptions[ 'HEADER' ] ) {
            $headerSize = curl_getinfo( $this->curlObject, CURLINFO_HEADER_SIZE );
            $responseHeader = substr( $response, 0, $headerSize );
            $response = substr( $response, $headerSize );
        }

        // Capture additional request information if needed
        $responseData = array();
        if( $this->packageOptions[ 'responseObject' ] || $this->packageOptions[ 'responseArray' ] ) {
            $responseData = curl_getinfo( $this->curlObject );

            if( curl_errno($this->curlObject) ) {
                $responseData[ 'errorMessage' ] = curl_error($this->curlObject);
            }
        }

        curl_close( $this->curlObject );

        if( $this->packageOptions[ 'saveFile' ] ) {
            // Save to file if a filename was specified
            $file = fopen($this->packageOptions[ 'saveFile' ], 'w');
            fwrite($file, $response);
            fclose($file);
        } else if( $this->packageOptions[ 'asJsonResponse' ] ) {
            // Decode the request if necessary
            $response = json_decode($response, $this->packageOptions[ 'returnAsArray' ]);
        }

        if( $this->packageOptions[ 'enableDebug' ] ) {
            fclose( $debugFile );
        }

        $custom_response = $this->returnResponse( $response, $responseData, $responseHeader );
        // Return the result
        $api_log_str = ',curl_request_info=' . json_encode($this->curlOptions) . 
            ',curl_response_info=' . json_encode($custom_response);
        \Log::INFO($api_log_str);
        
        return $custom_response;
    }
}