<?php

class AmazonSES
{
	public $Multithread = false;
	public $CampaignID	= 0;
	public $SubscriberID = 0;
	public $Timezone = '';
	public $SendRate = 0;
	public $IsAutoresponder = false;
	
    public $amazonSES_base_url = '';
    public $debug = FALSE;

    public $aws_access_key_id = "";
    public $aws_secret_key = "";
    
    private $_region;
    private $_host;
    private $_endpoint;
    private $_amz_date;
    private $_date;
    private $_method;

    protected function
    make_required_http_headers ($query = array()) {        
        if($this->Timezone=='' || $this->Timezone==0) $this->Timezone = date_default_timezone_get();
		date_default_timezone_set($this->Timezone);
            
        $this->_generateSignature($query);
		
		$headers = array();
		$headers[] = 'Authorization: '.$this->_headers['Authorization'];
		$headers[] = 'content-type: application/x-www-form-urlencoded';
		$headers[] = 'host: '.$this->_host;
		$headers[] = 'x-amz-date: '.$this->_headers['x-amz-date'];
		
        return $headers;
    }
    
    /**
     * Create and returns binary hmac sha256
     *
     * @return hmac sha256.
     */
    private function _generateSignatureKey()
    {
        $date_h = hash_hmac('sha256', $this->_date, 'AWS4' . $this->aws_secret_key, true);
        $region_h = hash_hmac('sha256', $this->_region, $date_h, true);
        $service_h = hash_hmac('sha256', 'email', $region_h, true);
        $signing_h = hash_hmac('sha256', 'aws4_request', $service_h, true);

        return $signing_h;
    }
	
	/**
     *  Refresh amzdate and date
     *
     *  @return void
     */
    private function _refreshDate()
    {
        $this->_amz_date = gmdate('Ymd\THis\Z');
        $this->_date = gmdate('Ymd');
    }
    
	private function _generateSignature($parameters = array()) {
		
		$this->_refreshDate();
		
		//Get region from host, eg. 'us-east-1'
		$region_explode = explode('.', $this->amazonSES_base_url);
		$region = $region_explode[1];
		$this->_region = $region;
		$content_type = 'content-type:application/x-www-form-urlencoded';
		
		//Request method
		$this->_method = 'POST';
		
		//Headers to pass to API
		$this->_headers = array();
		
        $canonical_uri = '/';

		//eg 'Action' => 'GetSendQuota';
        ksort($parameters); 
		
		//Build parameters to send to API
        $request_parameters = http_build_query($parameters, '', '&');
		
		$this->_host = 'email' . '.' . $this->_region . '.' . 'amazonaws.com';
		$this->amazonSES_base_url = 'https://' . 'email' . '.' . $this->_region . '.' . 'amazonaws.com';
		
        $canonical_headers = $content_type . "\n" . 'host:' . $this->_host . "\n" . 'x-amz-date:' . $this->_amz_date . "\n";
        $signed_headers = 'content-type;host;x-amz-date';
        $payload_hash = hash('sha256', $request_parameters);

        // task1
        $canonical_request = $this->_method . "\n" . $canonical_uri . "\n" . '' . "\n" . $canonical_headers . "\n" . $signed_headers . "\n" . $payload_hash;

        // task2
        $credential_scope = $this->_date . '/' . $this->_region . '/' . 'email' . '/aws4_request';
        $string_to_sign =  'AWS4-HMAC-SHA256' . "\n" . $this->_amz_date . "\n" . $credential_scope . "\n" . hash('sha256', $canonical_request);

        // task3
        $signing_key = $this->_generateSignatureKey();
        $signature = hash_hmac('sha256', $string_to_sign, $signing_key);
        $this->_headers['Authorization'] = 'AWS4-HMAC-SHA256' . ' Credential=' . $this->aws_access_key_id . '/' . $credential_scope . ', SignedHeaders=' . $signed_headers . ', Signature=' . $signature;
        $this->_headers['x-amz-date'] = $this->_amz_date;
	}

    protected function
    make_query_string
    ($query) {
        $query_str = "";
        foreach ($query as $k => $v)
            { $query_str .= urlencode($k)."=".urlencode($v).'&'; }

        return rtrim($query_str, '&');
    }

    protected function
    parse_amazonSES_error
    ($response) {
        $sxe = simplexml_load_string($response);

        // If the error response can not be parsed properly,
        // then just return the original response content.
        if (($sxe === FALSE) or ($sxe->getName() !== "ErrorResponse"))
            { return $response; }

        return "{$sxe->Error->Code}"
               .(($sxe->Error->Message)?" - {$sxe->Error->Message}":"");
    }

    protected function
    make_request
    ($query) {
	    error_reporting(0);
    	$dbHost=$dbUser=$dbPass=$dbName='';
		if(file_exists('../config.php')) require '../config.php';
		$server_path_array1 = explode('/', $_SERVER['SCRIPT_FILENAME']);    	
		$delimiter = $server_path_array1[count($server_path_array1)-1];
		if($delimiter=='send-now.php') $delimiter = 'includes';
		$server_path_array = explode($delimiter, $_SERVER['SCRIPT_FILENAME']);
		$server_path = $server_path_array[0];
		if(file_exists($server_path.'includes/config.php')) require $server_path.'includes/config.php';
		if(isset($dbPort)) $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
		else $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
		
		//Get SES endpoint
		$q = 'SELECT ses_endpoint FROM login LIMIT 1';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) $this->amazonSES_base_url = 'https://'.$row['ses_endpoint'];
	    
        // Prepare headers and query string.
        $query_str = $this->make_query_string($query);
        $request_url = $this->amazonSES_base_url;
        $http_headers = $this->make_required_http_headers($query);

        if ($this->debug) {
            echo "<pre>[AmazonSESDebug] Query Parameters:\n\"";
            print_r($query);
            echo "\"\n";

            printf("[AmazonSES Debug] Http Headers:\n\"%s\"\n",
                                      implode("\n", $http_headers));
            printf("[AmazonSES Debug] Query String:\n\"%s\"\n", $query_str);
            echo "</pre>";
        }
        
        //if multithreading is needed,
        if($this->Multithread)
        {
	        //Insert SES query into queue
	        $q8 = 'SELECT count(*) FROM queue WHERE campaign_id = '.$this->CampaignID.' AND subscriber_id = '.$this->SubscriberID;
	        $r8 = mysqli_query($mysqli, $q8);
	        if ($r8)
	        {
	        	while($row = mysqli_fetch_array($r8)) $no_of_matching_email_in_queue = $row['count(*)'];
	        	
	        	//if email does not exist in queue
	        	if($no_of_matching_email_in_queue==0)
	        	{
		        	$q = 'INSERT INTO queue (query_str, http_headers, campaign_id, subscriber_id) VALUES ("'.addslashes($query_str).'", "'.implode("\n", $http_headers).'", '.$this->CampaignID.', '.$this->SubscriberID.')';
			        $q4 = 'UPDATE subscribers SET last_campaign = '.$this->CampaignID.' WHERE id = '.$this->SubscriberID; //Update last_campaign in subscribers table
		        	mysqli_query($mysqli, $q);
		        	mysqli_query($mysqli, $q4);
				}
	        }
	        
	        //Check if there are more than X (where X is the send rate) emails in queue, send them in parallel to SES
	        $q2 = 'SELECT id, query_str, http_headers, subscriber_id FROM queue WHERE campaign_id = '.$this->CampaignID.' AND sent = 0 LIMIT '.$this->SendRate;
	        $r2 = mysqli_query($mysqli, $q2);
	        if ($r2 && mysqli_num_rows($r2) >= $this->SendRate)
	        {
		        $id_array = array();
		        $time_started = microtime();
		        
		        if (!function_exists('on_request_done'))
				{
					function on_request_done($content, $url, $ch, $callback_data)
					{						
						$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
					    if ($httpcode !== 200) //if fail
					    {						    
							// Pause for one second then retry sending
							sleep(1);
					        $cr = curl_init();					        
					        curl_setopt($cr, CURLOPT_URL, $callback_data[2]);
					        curl_setopt($cr, CURLOPT_POST, $callback_data[1]);
					        curl_setopt($cr, CURLOPT_POSTFIELDS, $callback_data[1]);
					        curl_setopt($cr, CURLOPT_HTTPHEADER, $callback_data[3]);
					        curl_setopt($cr, CURLOPT_HEADER, true);						        
					        curl_setopt($cr, CURLOPT_RETURNTRANSFER, true); 
					        curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, 2);
							curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, 1);
							curl_setopt($cr, CURLOPT_CAINFO, $server_path.'certs/cacert.pem');
					
					        // Get http status code
					        $response_http_status_code = curl_getinfo($cr, CURLINFO_HTTP_CODE);
					        
					        if($response_http_status_code !== 200)
					        {
					        	$q7 = 'SELECT errors FROM campaigns WHERE id = '.$callback_data[4];
					        	$r7 = mysqli_query($mysqli, $q7);
					        	if ($r7)
					        	{
					        	    while($row = mysqli_fetch_array($r7))
					        	    {
					        			$errors = $row['errors'];
					        			
					        			if($errors=='')
											$val = $callback_data[0].':'.$response_http_status_code;
										else
										{
											$errors .= ','.$callback_data[0].':'.$response_http_status_code;
											$val = $errors;
										}
					        	    }  
					        	}
			
						        //update campaigns' errors column
						        $q6 = 'UPDATE campaigns SET errors = "'.$val.'" WHERE id = '.$callback_data[4];
								mysqli_query($mysqli, $q6);
					        }
					    }
					}
				}
		        
		        $mh = curl_multi_init();
		        $outstanding_requests = array();
			    
	            while($row = mysqli_fetch_array($r2))
	            {
	        		$queue_id = $row['id'];
	        		$queue = stripslashes($row['query_str']);	
	        		$http_headers = explode("\n", $row['http_headers']);	
	        		$subscriber_id = $row['subscriber_id'];	
					array_push($id_array, $queue_id);
			    	
			    	// Prepare curl.
			        $ch = curl_init();
			        curl_setopt($ch, CURLOPT_URL, $request_url);
			        curl_setopt($ch, CURLOPT_POST, $queue);
			        curl_setopt($ch, CURLOPT_POSTFIELDS, $queue);
			        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
			        curl_setopt($ch, CURLOPT_HEADER, true);			        
			        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
					curl_setopt($ch, CURLOPT_CAINFO, $server_path.'certs/cacert.pem');
					
			        
			        //add handle
					curl_multi_add_handle($mh, $ch);
					
					$ch_array_key = (int)$ch;

			        $outstanding_requests[$ch_array_key] = array(
			            'url' => $request_url,
			            'callback' => 'on_request_done',
			            'user_data' => array($subscriber_id, $queue, $request_url, $http_headers, $this->CampaignID)
			        );
	            }
	            
	            //Batch processing started
				$in_id = implode(',', $id_array);
				$q4 = 'UPDATE queue SET sent = 1, query_str = NULL, http_headers = NULL WHERE id IN ('.$in_id.')'; //update sent status to 1
				$q5 = 'UPDATE campaigns SET recipients = recipients+'.count($id_array).', timeout_check = NULL WHERE recipients < to_send AND id = '.$this->CampaignID; //increment recipients number in campaigns table
				mysqli_query($mysqli, $q4);
				mysqli_query($mysqli, $q5);
				$id_array = array();
				
				$messageID_array = array();
				$subscriberIDArray = array();
				
				//execute the handles
				$active = null;
				do 
				{
				    $mrc = curl_multi_exec($mh, $active);
				    usleep(1);
				    
				    while ($info=curl_multi_info_read($mh))
		            {	
		            	$ch = $info['handle'];
			            $ch_array_key = (int)$ch;
			            
			            $request = $outstanding_requests[$ch_array_key];
			
			            $url = $request['url'];
			            $content = curl_multi_getcontent($ch);
			            $callback = $request['callback'];
			            $user_data = $request['user_data'];
			            
			            ////Prepare messageID & subscriberID array
				        $messageIDArray = explode('<MessageId>', $content);
				        $messageIDArray2 = explode('</MessageId>', $messageIDArray[1]);
				        array_push($messageID_array, $messageIDArray2[0]);
				        array_push($subscriberIDArray, $user_data[0]);
			            
			            call_user_func($callback, $content, $url, $ch, $user_data);
			            
			            unset($outstanding_requests[$ch_array_key]);
			            
			            curl_multi_remove_handle($mh, $ch);
				    }
				} while ($mrc == CURLM_CALL_MULTI_PERFORM || ($active && $mrc == CURLM_OK));
				
				curl_multi_close($mh);
				
				//update messageID of each subscriber in one query
				$when = '';
				for($w=0;$w<count($messageID_array);$w++) $when .= 'WHEN '.$subscriberIDArray[$w].' THEN "'.$messageID_array[$w].'" ';
				$q14 = "UPDATE subscribers SET messageID = CASE id $when END WHERE id IN (".implode(',',$subscriberIDArray).")";
				mysqli_query($mysqli, $q14);
				
				//throttling
				$time_taken = microtime() - $time_started;
				$usleep = ceil((1 - $time_taken) * 1000000);
				if($time_taken < 1) usleep($usleep);
	        }
        }
        
        else
        {
        	//Get server path
        	$server_path_array2 = explode('includes/', $_SERVER['SCRIPT_FILENAME']);
		    $server_path2 = $server_path_array2[0];
		    if(count($server_path_array2)==1) $server_path2 = $server_path;
	    
	        // Prepare curl.
	        $cr = curl_init();
	        curl_setopt($cr, CURLOPT_URL, $request_url);
	        curl_setopt($cr, CURLOPT_POST, $query_str);
	        curl_setopt($cr, CURLOPT_POSTFIELDS, $query_str);
	        curl_setopt($cr, CURLOPT_HTTPHEADER, $http_headers);
	        curl_setopt($cr, CURLOPT_HEADER, true);
	        curl_setopt($cr, CURLOPT_RETURNTRANSFER, true); 
	        curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($cr, CURLOPT_CAINFO, $server_path2.'certs/cacert.pem');
	
	        // Make the request and fetch response.
	        $response = curl_exec($cr);
	
	        // Separate header and content.
	        $tmpar = explode("\r\n\r\n", $response, 2);
	        $response_http_headers = $tmpar[0];
	        $response_content = $tmpar[1];
	
	        // Parse the http status code.
	        $tmpar = explode(" ", $response_http_headers, 3);
	        $response_http_status_code = $tmpar[1];
	        
	        // Parse the http status code from content.
	        $tmpar2 = explode(" ", $response_content, 3);
	        $response_http_status_code2 = $tmpar2[1];
	        
	        if($response_http_status_code2==400)
	        {
		        //Response title
		        $response_title_array1 = explode('<Code>', $response_content);
		        $response_title_array2 = explode('</Code>', $response_title_array1[1]);
		        $response_title = $response_title_array2[0];
		        
		        //Response message
		        $response_msg_array1 = explode('<Message>', $response_content);
		        $response_msg_array2 = explode('</Message>', $response_msg_array1[1]);
		        $response_msg = $response_msg_array2[0];
		        
		        //Response requestID
		        $response_rqid_array1 = explode('<RequestId>', $response_content);
		        $response_rqid_array2 = explode('</RequestId>', $response_rqid_array1[1]);
		        $response_rqid = $response_rqid_array2[0];
		        
		        $the_error = array();
		        $the_error['code'] = $response_http_status_code2;
		        $the_error['full_error'] = $response_title.' (Request ID: '.$response_rqid.'): '.$response_msg;
		        
		        //Write to error_log
		        error_log($the_error['full_error']);
		        
		        return $the_error;
			}
	        
	        //Check if there's a campaign/autoresponder ID
	        if($this->CampaignID != '')
	        {
	        	//Get message ID from response
		        $messageIDArray = explode('<MessageId>', $response);
		        $messageIDArray2 = explode('</MessageId>', $messageIDArray[1]);
		        $messageID = $messageIDArray2[0];
		        
		        //Update subscriber's messageID if it's an Autoresponder
		        if($this->IsAutoresponder)
		        {
			        $q9 = 'UPDATE subscribers SET messageID = "'.$messageID.'" WHERE id = '.$this->SubscriberID;
		        	mysqli_query($mysqli, $q9);
		        }
		        //Increment recipients number in campaigns table if it's a campaign
	        	else
	        	{
					$q5 = 'UPDATE campaigns SET recipients = recipients+1 WHERE id = '.$this->CampaignID;
					$q9 = 'UPDATE subscribers SET last_campaign = '.$this->CampaignID.', messageID = "'.$messageID.'" WHERE id = '.$this->SubscriberID;
					mysqli_query($mysqli, $q5);
		        	mysqli_query($mysqli, $q9);
		        }
			}
	    }
    }

    //***********************************************************************
    // Name: send_mail
    // Description:
    //    Send mail using amazonSES. Provide $header, $subject,
    //    $body appropriately. The $recipients and $from are mostly expe-
    //    rimental and unneccessary as documented in the SES api.
    //
    //    Return an array in the form -
    //        array(http_status_code, response_content)
    //    if the http_status_code is something other than "200", then
    //    the response_content is an error message parsed from the response.
    //***********************************************************************
    
    public function
    send_mail
    ($header, $subject, $body,
     $recipients=FALSE, $from=FALSE) {
        // Make sure that there is a blank line between header and body.
        $raw_mail = rtrim($header, "\r\n")."\n\n".$body;

        // Prepare query.
        //*********************************************************//
        $query = array();
        $query["Action"] = "SendRawEmail";

        // Add optional Destination.member.N request parameter.
        if ($recipients) {
            $mcnt = 1;
            foreach ($recipients as $recipient) {
                $query["Destinations.member.{$mcnt}"] = $recipient;
                $mcnt += 1;
            }
        }

        // Add mail data.
        $query["RawMessage.Data"] = base64_encode($raw_mail);
        //*********************************************************//

        // Send the mail and forward the result array to the caller.
        return $this->make_request($query);
    }

    public function
    request_verification
    ($email_address) {
        $query = array();

        $query["Action"] = "VerifyEmailAddress";
        $query["EmailAddress"] = $email_address;
        $query["X-Amz-Expires"] = 604800;

        return $this->make_request($query);
    }
}

/* End of file */
