<?php

class SimpleEmailService
{    
    private $_region;
    private $_host;
    private $_endpoint;
    private $_amz_date;
    private $_date;
    private $_method;
    
	protected $__accessKey; // AWS Access key
	protected $__secretKey; // AWS Secret key
	protected $__host;

	public function getAccessKey() { return $this->__accessKey; }
	public function getSecretKey() { return $this->__secretKey; }
	public function getHost() { return $this->__host; }

	protected $__verifyHost = 1;
	protected $__verifyPeer = 0;

	// verifyHost and verifyPeer determine whether curl verifies ssl certificates.
	// It may be necessary to disable these checks on certain systems.
	// These only have an effect if SSL is enabled.
	public function verifyHost() { return $this->__verifyHost; }
	public function enableVerifyHost($enable = true) { $this->__verifyHost = $enable; }

	public function verifyPeer() { return $this->__verifyPeer; }
	public function enableVerifyPeer($enable = true) { $this->__verifyPeer = $enable; }

	/**
	* Constructor
	*
	* @param string $accessKey Access key
	* @param string $secretKey Secret key
	* @return void
	*/
	public function __construct($accessKey = null, $secretKey = null, $host = 'email.us-east-1.amazonaws.com') {
		if ($accessKey !== null && $secretKey !== null) {
			$this->setAuth($accessKey, $secretKey);
		}
		$this->__host = $host;
	}

	/**
	* Set AWS access key and secret key
	*
	* @param string $accessKey Access key
	* @param string $secretKey Secret key
	* @return void
	*/
	public function setAuth($accessKey, $secretKey) {
		$this->__accessKey = $accessKey;
		$this->__secretKey = $secretKey;
	}

	/**
	* Lists the email addresses that have been verified and can be used as the 'From' address
	* 
	* @return An array containing two items: a list of verified email addresses, and the request id.
	*/
	public function ListIdentities() {
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'ListIdentities');

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('ListIdentities', $rest->error);
			return false;
		}

		$response = array();
		if(!isset($rest->body)) {
			return $response;
		}

		$addresses = array();
		foreach($rest->body->ListIdentitiesResult->Identities->member as $address) {
			$addresses[] = (string)$address;
		}

		$response['Addresses'] = $addresses;
		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;

		return $response;
	}
	
	public function getIdentityVerificationAttributes($identity) {
		$from_email_domain_array = explode('@', $identity);
		$from_email_domain = $from_email_domain_array[1];
		
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'GetIdentityVerificationAttributes');
		$rest->setParameter('Identities.member.1', $identity);
		$rest->setParameter('Identities.member.2', $from_email_domain);

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('GetIdentityVerificationAttributes', $rest->error);
			return false;
		}

		$response = array();
		if(!isset($rest->body)) {
			return $response;
		}
		
		$statuses = array();
		foreach($rest->body->GetIdentityVerificationAttributesResult->VerificationAttributes->entry as $entry) {
			$statuses[] = (string)$entry->value->VerificationStatus;
		}
		
		$response['VerificationStatus'] = $statuses;
		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;

		return $response;
	}

	/**
	* Requests verification of the provided email address, so it can be used
	* as the 'From' address when sending emails through SimpleEmailService.
	*
	* After submitting this request, you should receive a verification email
	* from Amazon at the specified address containing instructions to follow.
	*
	* @param string email The email address to get verified
	* @return The request id for this request.
	*/
	public function verifyEmailAddress($email) {
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'VerifyEmailAddress');
		$rest->setParameter('EmailAddress', $email);

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('verifyEmailAddress', $rest->error);
			return false;
		}

		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;
		return $response;
	}
	
	/**
	* Create a custom verification email template that will be sent to the user to verify 
	* their 'From email' address.
	*/
	public function createCustomVerificationEmailTemplate($email, $app_path, $content_title, $content_body) {
		
		$template_name = 'SendyVerificationTemplate';
		$template_subject = 'Please verify your email address';
		$template_content = "<p><h2><img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAlCAYAAAAwYKuzAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RDc0RDY5RjlGMjgwMTFFN0IzRjNFQzIwRTlGN0UyOTEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDc0RDY5RkFGMjgwMTFFN0IzRjNFQzIwRTlGN0UyOTEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpENzRENjlGN0YyODAxMUU3QjNGM0VDMjBFOUY3RTI5MSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpENzRENjlGOEYyODAxMUU3QjNGM0VDMjBFOUY3RTI5MSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PlW/dT4AAAGMSURBVHjaYmYYnEAWiE8CseRgdFw5EP+H4j2DyWEKQHwFyXGHgZhxsDguDclhILx9MIXcKjTHbRwsDpMC4tuD1XHOQPwXzXGbBmt6G1S5tQmL404PFsdNw+K4B0DMNxgctwiL474BsdpgcNxyLI4DYafB4LilOByXMhgctwCH4/oGg+Mm43DcoChOGnE47hUQiwy047JwOA6EXQfacf54HNc00I4zxuO4AwPtOFEgfoHDcb+AWJlSC8KBOB/aDyAHHMETernUCIECJAObSdQ7FY/jdlEzmiqQDH4PxFFE6EnG47h/QKxO7bQ0Bc2S60BsjkOtAR7H/Yd6mCZgIxbLtgCxNJIaFiC+gcdx52idK8/gsLiHiHQHwo60diCoOnqGw/L30HYcLsfNp1fZpoelU0MIfwdiGXoWwF4kOrBmsFX+6H0L1oGqynqIcOCAt5BX4HHc2cHSbTyGw4HBg8WB/ED8EM1xxxkGGdAA4h+DMfSQgQvUcVfoYRkzGXruMUBGPkHtwJO0diBAgAEA2soDsei9CHUAAAAASUVORK5CYII=\" width=\"20\"/> $content_title</h2></p><p>$content_body:</p>";
		$app_path = substr($app_path, -1)=='/' ? substr($app_path, 0, -1) : $app_path;
		$success_url = $app_path.'/verification-status?success';
		$failed_url = $app_path.'/verification-status?failed';
		
		$rest = new SimpleEmailServiceRequest($this, 'POST');
		$rest->setParameter('Action', 'CreateCustomVerificationEmailTemplate');
		$rest->setParameter('FromEmailAddress', $email);
		$rest->setParameter('TemplateName', $template_name);
		$rest->setParameter('TemplateSubject', $template_subject);
		$rest->setParameter('TemplateContent', stripslashes($template_content));
		$rest->setParameter('SuccessRedirectionURL', $success_url);
		$rest->setParameter('FailureRedirectionURL', $failed_url);

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('CreateCustomVerificationEmailTemplate', $rest->error);
			return false;
		}

		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;
		return $response;
	}
	
	/**
	* Delete custom verification email template  
	*/
	public function deleteCustomVerificationEmailTemplate($template_name) {		
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'DeleteCustomVerificationEmailTemplate');
		$rest->setParameter('TemplateName', $template_name);

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('DeleteCustomVerificationEmailTemplate', $rest->error);
			return false;
		}

		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;
		return $response;
	}
	
	/**
	* Requests verification of the provided email address using a custom template, 
	* so it can be used as the 'From' address when sending emails through SimpleEmailService.
	*
	* After submitting this request, you should receive a verification email
	* from Amazon at the specified address containing instructions to follow.
	*
	* @param string email The email address to get verified
	* @return The request id for this request.
	*/
	public function sendCustomVerificationEmail($email) {
		
		$template_name = 'SendyVerificationTemplate';
		
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'SendCustomVerificationEmail');
		$rest->setParameter('EmailAddress', $email);
		$rest->setParameter('TemplateName', $template_name);

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('SendCustomVerificationEmail', $rest->error);
			return false;
		}

		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;
		return $response;
	}

	/**
	* Removes the specified email address from the list of verified addresses.
	*
	* @param string email The email address to remove
	* @return The request id for this request.
	*/
	public function deleteIdentity($email) {
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'DeleteIdentity');
		$rest->setParameter('Identity', $email);

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('DeleteIdentity', $rest->error);
			return false;
		}

		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;
		return $response;
	}
	
	/**
	* Given an identity (an email address or a domain), sets the Amazon Simple Notification Service (Amazon SNS) topic to which Amazon SES will publish bounce, complaint, and/or delivery notifications for emails sent with that identity as the Source..
	*
	*/
	public function SetIdentityNotificationTopic($identity, $sns_topic, $notification_type) {
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'SetIdentityNotificationTopic');
		$rest->setParameter('Identity', $identity);
		$rest->setParameter('SnsTopic', $sns_topic);
		$rest->setParameter('NotificationType', $notification_type);

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('SetIdentityNotificationTopic', $rest->error);
			return false;
		}

		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;
		return $response;
	}

	/**
	* Retrieves information on the current activity limits for this account.
	* See http://docs.amazonwebservices.com/ses/latest/APIReference/API_GetSendQuota.html
	*
	* @return An array containing information on this account's activity limits.
	*/
	public function getSendQuota() {
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'GetSendQuota');

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('getSendQuota', $rest->error);
			$err_code = $rest->error['Error']['Code'];
			return $err_code;
		}

		$response = array();
		if(!isset($rest->body)) {
			return $response;
		}

		$response['Max24HourSend'] = (string)$rest->body->GetSendQuotaResult->Max24HourSend;
		$response['MaxSendRate'] = (string)$rest->body->GetSendQuotaResult->MaxSendRate;
		$response['SentLast24Hours'] = (string)$rest->body->GetSendQuotaResult->SentLast24Hours;
		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;

		return $response;
	}
	
	public function getAccountSendingEnabled() {
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'GetAccountSendingEnabled');

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('getAccountSendingEnabled', $rest->error);
			$err_code = $rest->error['Error']['Code'];
			return $err_code;
		}

		$response = array();
		if(!isset($rest->body)) {
			return $response;
		}

		if($rest->body->GetAccountSendingEnabledResult)
			return 'Enabled';
		else
			return 'Disabled';
	}

	/**
	* Retrieves statistics for the last two weeks of activity on this account.
	* See http://docs.amazonwebservices.com/ses/latest/APIReference/API_GetSendStatistics.html
	*
	* @return An array of activity statistics.  Each array item covers a 15-minute period.
	*/
	public function getSendStatistics() {
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'GetSendStatistics');

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('getSendStatistics', $rest->error);
			return false;
		}

		$response = array();
		if(!isset($rest->body)) {
			return $response;
		}

		$datapoints = array();
		foreach($rest->body->GetSendStatisticsResult->SendDataPoints->member as $datapoint) {
			$p = array();
			$p['Bounces'] = (string)$datapoint->Bounces;
			$p['Complaints'] = (string)$datapoint->Complaints;
			$p['DeliveryAttempts'] = (string)$datapoint->DeliveryAttempts;
			$p['Rejects'] = (string)$datapoint->Rejects;
			$p['Timestamp'] = (string)$datapoint->Timestamp;

			$datapoints[] = $p;
		}

		$response['SendDataPoints'] = $datapoints;
		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;

		return $response;
	}
	
	/**
	* Retrieves statistics for the last two weeks of activity on this account.
	* See http://docs.amazonwebservices.com/ses/latest/APIReference/API_GetSendStatistics.html
	*
	* @return An array of activity statistics.  Each array item covers a 15-minute period.
	*/
	public function setIdentityFeedbackForwardingEnabled($from_email, $val) {
		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'SetIdentityFeedbackForwardingEnabled');
		$rest->setParameter('ForwardingEnabled', $val);
		$rest->setParameter('Identity', $from_email);
		
		$rest = $rest->getResponse();
		/*
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('SetIdentityFeedbackForwardingEnabled', $rest->error);
			return false;
		}
		*/

		$response = array();
		if(!isset($rest->body)) {
			return $response;
		}

		return $response;
	}


	/**
	* Given a SimpleEmailServiceMessage object, submits the message to the service for sending.
	*
	* @return An array containing the unique identifier for this message and a separate request id.
	*         Returns false if the provided message is missing any required fields.
	*/
	public function sendEmail($sesMessage) {
		if(!$sesMessage->validate()) {
			$this->__triggerError('sendEmail', 'Message failed validation.');
			return false;
		}

		$rest = new SimpleEmailServiceRequest($this, 'GET');
		$rest->setParameter('Action', 'SendEmail');

		$i = 1;
		foreach($sesMessage->to as $to) {
			$rest->setParameter('Destination.ToAddresses.member.'.$i, $to);
			$i++;
		}

		if(is_array($sesMessage->cc)) {
			$i = 1;
			foreach($sesMessage->cc as $cc) {
				$rest->setParameter('Destination.CcAddresses.member.'.$i, $cc);
				$i++;
			}
		}

		if(is_array($sesMessage->bcc)) {
			$i = 1;
			foreach($sesMessage->bcc as $bcc) {
				$rest->setParameter('Destination.BccAddresses.member.'.$i, $bcc);
				$i++;
			}
		}

		if(is_array($sesMessage->replyto)) {
			$i = 1;
			foreach($sesMessage->replyto as $replyto) {
				$rest->setParameter('ReplyToAddresses.member.'.$i, $replyto);
				$i++;
			}
		}

		$rest->setParameter('Source', $sesMessage->from);

		if($sesMessage->returnpath != null) {
			$rest->setParameter('ReturnPath', $sesMessage->returnpath);
		}

		if($sesMessage->subject != null && strlen($sesMessage->subject) > 0) {
			$rest->setParameter('Message.Subject.Data', $sesMessage->subject);
			if($sesMessage->subjectCharset != null && strlen($sesMessage->subjectCharset) > 0) {
				$rest->setParameter('Message.Subject.Charset', $sesMessage->subjectCharset);
			}
		}


		if($sesMessage->messagetext != null && strlen($sesMessage->messagetext) > 0) {
			$rest->setParameter('Message.Body.Text.Data', $sesMessage->messagetext);
			if($sesMessage->messageTextCharset != null && strlen($sesMessage->messageTextCharset) > 0) {
				$rest->setParameter('Message.Body.Text.Charset', $sesMessage->messageTextCharset);
			}
		}

		if($sesMessage->messagehtml != null && strlen($sesMessage->messagehtml) > 0) {
			$rest->setParameter('Message.Body.Html.Data', $sesMessage->messagehtml);
			if($sesMessage->messageHtmlCharset != null && strlen($sesMessage->messageHtmlCharset) > 0) {
				$rest->setParameter('Message.Body.Html.Charset', $sesMessage->messageHtmlCharset);
			}
		}

		$rest = $rest->getResponse();
		if($rest->error === false && $rest->code !== 200) {
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		}
		if($rest->error !== false) {
			$this->__triggerError('sendEmail', $rest->error);
			return false;
		}

		$response['MessageId'] = (string)$rest->body->SendEmailResult->MessageId;
		$response['RequestId'] = (string)$rest->body->ResponseMetadata->RequestId;
		return $response;
	}

	/**
	* Trigger an error message
	*
	* @internal Used by member functions to output errors
	* @param array $error Array containing error information
	* @return string
	*/
	public function __triggerError($functionname, $error)
	{
		if($error == false) {
			trigger_error(sprintf("SimpleEmailService::%s(): Encountered an error, but no description given", $functionname), E_USER_WARNING);
		}
		else if(isset($error['curl']) && $error['curl'])
		{
			trigger_error(sprintf("SimpleEmailService::%s(): %s %s", $functionname, $error['code'], $error['message']), E_USER_WARNING);
		}
		else if(isset($error['Error']))
		{
			$e = $error['Error'];
			$message = sprintf("SimpleEmailService::%s(): %s - %s: %s\nRequest Id: %s\n", $functionname, $e['Type'], $e['Code'], $e['Message'], $error['RequestId']);
			trigger_error($message, E_USER_WARNING);
		}
		else {
			trigger_error(sprintf("SimpleEmailService::%s(): Encountered an error: %s", $functionname, $error), E_USER_WARNING);
		}
	}
}

final class SimpleEmailServiceRequest
{
	private $ses, $verb, $parameters = array();
	public $response;

	/**
	* Constructor
	*
	* @param string $ses The SimpleEmailService object making this request
	* @param string $action action
	* @param string $verb HTTP verb
	* @return mixed
	*/
	function __construct($ses, $verb) {
		$this->ses = $ses;
		$this->verb = $verb;
		$this->response = new STDClass;
		$this->response->error = false;
	}

	/**
	* Set request parameter
	*
	* @param string  $key Key
	* @param string  $value Value
	* @param boolean $replace Whether to replace the key if it already exists (default true)
	* @return void
	*/
	public function setParameter($key, $value, $replace = true) {
		if(!$replace && isset($this->parameters[$key]))
		{
			$temp = (array)($this->parameters[$key]);
			$temp[] = $value;
			$this->parameters[$key] = $temp;
		}
		else
		{
			$this->parameters[$key] = $value;
		}
	}

	/**
	* Get the response
	*
	* @return object | false
	*/
	public function getResponse() {

		$params = array();
		foreach ($this->parameters as $var => $value)
		{
			if(is_array($value))
			{
				foreach($value as $v)
				{
					$params[] = $var.'='.urlencode($v);
				}
			}
			else
			{
				$params[] = $var.'='.urlencode($value);
			}
		}

		sort($params, SORT_STRING);

		$query = implode('&', $params);
		
		$this->_generateSignature();
		$url = $this->_endpoint;
		
		$headers = array();
		$headers[] = 'Authorization: '.$this->_headers['Authorization'];
		$headers[] = 'content-type: application/x-www-form-urlencoded';
		$headers[] = 'host: '.$this->ses->getHost();
		$headers[] = 'x-amz-date: '.$this->_headers['x-amz-date'];

		// Basic setup
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERAGENT, 'SimpleEmailService/php');
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, ($this->ses->verifyHost() ? 2 : 0));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, ($this->ses->verifyPeer() ? 1 : 0));

		// Request types
		switch ($this->verb) {
			case 'GET':
				$url .= '?'.$query;
				break;
			case 'POST':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
			break;
			case 'DELETE':
				$url .= '?'.$query;
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
			default: break;
		}
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($curl, CURLOPT_WRITEFUNCTION, array(&$this, '__responseWriteCallback'));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		// Execute, grab errors
		if (curl_exec($curl)) {
			$this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		} else {
			$this->response->error = array(
				'curl' => true,
				'code' => curl_errno($curl),
				'message' => curl_error($curl),
				'resource' => $this->resource
			);
		}

		@curl_close($curl);

		// Parse body into XML
		if ($this->response->error === false && isset($this->response->body)) {
			$this->response->body = simplexml_load_string($this->response->body);

			// Grab SES errors
			if (!in_array($this->response->code, array(200, 201, 202, 204))
				&& isset($this->response->body->Error)) {
				$error = $this->response->body->Error;
				$output = array();
				$output['curl'] = false;
				$output['Error'] = array();
				$output['Error']['Type'] = (string)$error->Type;
				$output['Error']['Code'] = (string)$error->Code;
				$output['Error']['Message'] = (string)$error->Message;
				$output['RequestId'] = (string)$this->response->body->RequestId;

				$this->response->error = $output;
				unset($this->response->body);
			}
		}

		return $this->response;
	}

	/**
	* CURL write callback
	*
	* @param resource &$curl CURL resource
	* @param string &$data Data
	* @return integer
	*/
	private function __responseWriteCallback(&$curl, &$data) {
		$this->response->body .= $data;
		return strlen($data);
	}

	/**
	* Contributed by afx114
	* URL encode the parameters as per http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/index.html?Query_QueryAuth.html
	* PHP's rawurlencode() follows RFC 1738, not RFC 3986 as required by Amazon. The only difference is the tilde (~), so convert it back after rawurlencode
	* See: http://www.morganney.com/blog/API/AWS-Product-Advertising-API-Requires-a-Signed-Request.php
	*
	* @param string $var String to encode
	* @return string
	*/
	private function __customUrlEncode($var) {
		return str_replace('%7E', '~', rawurlencode($var));
	}

	/**
	* Generate the auth string using Hmac-SHA256
	*
	* @internal Used by SimpleDBRequest::getResponse()
	* @param string $string String to sign
	* @return string
	*/
	
	/**
     * Create and returns binary hmac sha256
     *
     * @return hmac sha256.
     */
    private function _generateSignatureKey()
    {
        $date_h = hash_hmac('sha256', $this->_date, 'AWS4' . $this->ses->getSecretKey(), true);
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
    
	private function _generateSignature() {
		
		$this->_refreshDate();
		
		//Get region from host, eg. 'us-east-1'
		$region_explode = explode('.', $this->ses->getHost());
		$region = $region_explode[1];
		$this->_region = $region;
		$content_type = 'content-type:application/x-www-form-urlencoded';
		
		//Request method (eg. GET, POST)
		$this->_method = $this->verb;
		
		//Headers to pass to API
		$this->_headers = array();
		
        $canonical_uri = '/';

		//eg 'Action' => 'GetSendQuota';
        ksort($this->parameters); 
		
		//Build parameters to send to API
        $request_parameters = http_build_query($this->parameters, '', '&');
		
		$this->_host = 'email' . '.' . $this->_region . '.' . 'amazonaws.com';
		$this->_endpoint = 'https://' . 'email' . '.' . $this->_region . '.' . 'amazonaws.com';
		
        $canonical_headers = $content_type . "\n" . 'host:' . $this->_host . "\n" . 'x-amz-date:' . $this->_amz_date . "\n";
        $signed_headers = 'content-type;host;x-amz-date';
        
        if($this->_method=='GET')
	        $payload_hash = hash('sha256', '');
	    else if($this->_method=='POST')
	    	$payload_hash = hash('sha256', $request_parameters);

        // task1
        if($this->_method=='GET')
	        $canonical_request = $this->_method . "\n" . $canonical_uri . "\n" . $request_parameters . "\n" . $canonical_headers . "\n" . $signed_headers . "\n" . $payload_hash;
	    else if($this->_method=='POST')
			$canonical_request = $this->_method . "\n" . $canonical_uri . "\n" . '' . "\n" . $canonical_headers . "\n" . $signed_headers . "\n" . $payload_hash;

        // task2
        $credential_scope = $this->_date . '/' . $this->_region . '/' . 'email' . '/aws4_request';
        $string_to_sign =  'AWS4-HMAC-SHA256' . "\n" . $this->_amz_date . "\n" . $credential_scope . "\n" . hash('sha256', $canonical_request);

        // task3
        $signing_key = $this->_generateSignatureKey();
        $signature = hash_hmac('sha256', $string_to_sign, $signing_key);
        $this->_headers['Authorization'] = 'AWS4-HMAC-SHA256' . ' Credential=' . $this->ses->getAccessKey() . '/' . $credential_scope . ', SignedHeaders=' . $signed_headers . ', Signature=' . $signature;
        $this->_headers['x-amz-date'] = $this->_amz_date;
	}
}


final class SimpleEmailServiceMessage {

	// these are public for convenience only
	// these are not to be used outside of the SimpleEmailService class!
	public $to, $cc, $bcc, $replyto;
	public $from, $returnpath;
	public $subject, $messagetext, $messagehtml;
	public $subjectCharset, $messageTextCharset, $messageHtmlCharset;

	function __construct() {
		$this->to = array();
		$this->cc = array();
		$this->bcc = array();
		$this->replyto = array();

		$this->from = null;
		$this->returnpath = null;

		$this->subject = null;
		$this->messagetext = null;
		$this->messagehtml = null;

		$this->subjectCharset = null;
		$this->messageTextCharset = null;
		$this->messageHtmlCharset = null;
	}


	/**
	* addTo, addCC, addBCC, and addReplyTo have the following behavior:
	* If a single address is passed, it is appended to the current list of addresses.
	* If an array of addresses is passed, that array is merged into the current list.
	*/
	function addTo($to) {
		if(!is_array($to)) {
			$this->to[] = $to;
		}
		else {
			$this->to = array_merge($this->to, $to);
		}
	}

	function addCC($cc) {
		if(!is_array($cc)) {
			$this->cc[] = $cc;
		}
		else {
			$this->cc = array_merge($this->cc, $cc);
		}
	}

	function addBCC($bcc) {
		if(!is_array($bcc)) {
			$this->bcc[] = $bcc;
		}
		else {
			$this->bcc = array_merge($this->bcc, $bcc);
		}
	}

	function addReplyTo($replyto) {
		if(!is_array($replyto)) {
			$this->replyto[] = $replyto;
		}
		else {
			$this->replyto = array_merge($this->replyto, $replyto);
		}
	}

	function setFrom($from) {
		$this->from = $from;
	}

	function setReturnPath($returnpath) {
		$this->returnpath = $returnpath;
	}

	function setSubject($subject) {
		$this->subject = $subject;
	}

	function setSubjectCharset($charset) {
		$this->subjectCharset = $charset;
	}

	function setMessageFromString($text, $html = null) {
		$this->messagetext = $text;
		$this->messagehtml = $html;
	}

	function setMessageFromFile($textfile, $htmlfile = null) {
		if(file_exists($textfile) && is_file($textfile) && is_readable($textfile)) {
			$this->messagetext = file_get_contents($textfile);
		} else {
			$this->messagetext = null;
		}
		if(file_exists($htmlfile) && is_file($htmlfile) && is_readable($htmlfile)) {
			$this->messagehtml = file_get_contents($htmlfile);
		} else {
			$this->messagehtml = null;
		}
	}

	function setMessageFromURL($texturl, $htmlurl = null) {
		if($texturl !== null) {
			$this->messagetext = file_get_contents($texturl);
		} else {
			$this->messagetext = null;
		}
		if($htmlurl !== null) {
			$this->messagehtml = file_get_contents($htmlurl);
		} else {
			$this->messagehtml = null;
		}
	}

	function setMessageCharset($textCharset, $htmlCharset = null) {
		$this->messageTextCharset = $textCharset;
		$this->messageHtmlCharset = $htmlCharset;
	}

	/**
	* Validates whether the message object has sufficient information to submit a request to SES.
	* This does not guarantee the message will arrive, nor that the request will succeed;
	* instead, it makes sure that no required fields are missing.
	*
	* This is used internally before attempting a SendEmail or SendRawEmail request,
	* but it can be used outside of this file if verification is desired.
	* May be useful if e.g. the data is being populated from a form; developers can generally
	* use this function to verify completeness instead of writing custom logic.
	*
	* @return boolean
	*/
	public function validate() {
		if(count($this->to) == 0)
			return false;
		if($this->from == null || strlen($this->from) == 0)
			return false;
		// messages require at least one of: subject, messagetext, messagehtml.
		if(($this->subject == null || strlen($this->subject) == 0)
			&& ($this->messagetext == null || strlen($this->messagetext) == 0)
			&& ($this->messagehtml == null || strlen($this->messagehtml) == 0))
		{
			return false;
		}

		return true;
	}
}
