<?php
// **********************************************
// *** External files ***
// **********************************************
// config file
require_once '/usr/lib/zabbix/alertscripts/zabbix-twilio/config.php';
// Twilio PHP Library
require_once $SCRIPT_DIR . '/lib/twilio-php-latest/Services/Twilio.php';
// Logger class
require_once $SCRIPT_DIR . '/Logger.php';


// **********************************************
// *** Main ***
// **********************************************
$log = new Logger ( $LOG_DIR . '/' . basename($_SERVER['SCRIPT_FILENAME'], '.php') . '.log', $DEBUG_FLG );
$log->info ( 'Start' );

$log->debug ( $_REQUEST );

// Check for command specification
if (! isset ( $_REQUEST ['cmd'] )) {
	$log->error ( 'Invalid request.' );
	$log->error ( $_REQUEST );
	exit ();
}

Switch ($_REQUEST ['cmd']) {
	case "register" : // Register to Zabbix
		if (! isset ( $_REQUEST ['Digits'] )) {
			$log->info ( "Digits not entered" );
			exit ();
		}

		// Check input key
		Switch ($_REQUEST ['Digits']) {
			case $DIGITS :
				$log->info ( 'Start Zabbix registration process' );

				$log->debug ( "ZABBIX_API:$ZABBIX_API ZABBIX_USER:$ZABBIX_USER ZABBIX_PASS:$ZABBIX_PASS" );
				$log->debug ( "MESSAGE_REG_OK:$MESSAGE_REG_OK" );
				$log->debug ( "MESSAGE_REG_NG:$MESSAGE_REG_NG" );

				$response = new Services_Twilio_Twiml ();

				try {
					$api = new Zabbix_API ( $ZABBIX_API, $ZABBIX_USER, $ZABBIX_PASS );
					$api_res = $api->request('event.acknowledge', array (
							'eventids' => $_REQUEST ['eventid'],
							'message' => $_REQUEST ['name'] . ' has confirmed receipt of the call.'
					) );

					// Registration successful
					$response->say ( $MESSAGE_REG_OK, array (
							'voice' => 'woman',
							'language' => 'ja-JP'
					) );
					header ( 'Content-type: text/xml' );
					print $response;
					$log->info ( 'Successfully registered with Zabbix' );
					$log->debug ( $response );
				} catch ( Exception $e ) {
					// Registration failed
					$response->say ( $MESSAGE_REG_NG, array (
							'voice' => 'woman',
							'language' => 'ja-JP'
					) );
					header ( 'Content-type: text/xml' );
					print $response;
					$log->debug ( $response );

					$log->error ( 'Failed to register with Zabbix ' );
					$log->error ( $e->getMessage () );

					return $e->getMessage ();
				}

				break;
			case 0 :
			case 1 < $_REQUEST ['Digits'] and $_REQUEST ['Digits'] < 10 :
				$log->info ( "Digits mismatch Digits:$_REQUEST['Digits']" );
				$log->info ( "Start re-entry process" );

				$log->debug ( "MESSAGE_RETYPE:$MESSAGE_RETYPE" );

				$response = new Services_Twilio_Twiml ();
				$gather = $response->gather ( array (
						'action' => $SCRIPT_URL . '?cmd=register&eventid=' . $_REQUEST ['eventid'] . '&name=' . $_REQUEST ['name'],
						'timeout' => $DIGITS_TIMEOUT,
						'finishOnKey' => '#',
						'numDigits' => $DIGITS_NUM
				) );

				// Replace $$DIGITS$$
				$message = str_replace ( '$$DIGITS$$', $DIGITS, $MESSAGE_RETYPE );
				$gather->say ( $message, array (
						'voice' => 'woman',
						'language' => 'ja-JP'
				) );

				header ( 'Content-type: text/xml' );
				print $response;
				$log->debug ( $response );

				break;
		}
		break;
	case "notice" : // Alert notification
		$log->info ( "Start automatic notification process" );

		// URL decode
		$message = urldecode ( $_REQUEST ['message'] );
		// $$MESSAGE$$
		$message = str_replace ( '$$MESSAGE$$', $message, $MESSAGE_NOTICE );
		// Replace $$DIGITS$$
		$message = str_replace ( '$$DIGITS$$', $DIGITS, $message );

		$log->debug ( "MESSAGE:$message" );
		$log->debug ( "DIGITS_TIMEOUT:$DIGITS_TIMEOUT DIGITS_NUM:$DIGITS_NUM URL:$SCRIPT_URL" );

		$response = new Services_Twilio_Twiml ();
		$gather = $response->gather ( array (
				'action' => $SCRIPT_URL . '?cmd=register&eventid=' . $_REQUEST ['eventid'] . '&name=' . $_REQUEST ['name'],
				'timeout' => $DIGITS_TIMEOUT,
				'finishOnKey' => '#',
				'numDigits' => $DIGITS_NUM
		) );

		$gather->say ( $message, array (
				'voice' => 'woman',
				'language' => 'ja-JP'
		) );

		header ( 'Content-type: text/xml' );
		print $response;

		$log->debug ( $response );
		break;
	default :
}

exit ();


// ******************************************************************
// *** Zabbix_API ***
// ******************************************************************
class Zabbix_API  {
	private $api_url = '';
	private $auth = '';

	public function __construct($api_url, $user='', $password='') {
		$this->api_url = $api_url;

		if (!empty($user) and !empty($password)) {
			$this->userLogin(array('user' => $user, 'password' => $password));
		}
	}

	public function userLogin($params) {
		$res = $this->request('user.login', $params);
		$this->auth = $res['result'];
	}

	public function request($method, $params) {
		// JSON encode the API
		$content = json_encode( array (
				'jsonrpc' => '2.0',
				'method'  => $method,
				'params'  => $params,
				'auth'    => $this->auth,
				'id'      => '1'
		));

		// Request options
		$context = stream_context_create(array('http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/json-rpc; charset=UTF-8' . "\r\n",
				'content' => $content,
				'ignore_errors' => true
		)));

		// Request
		$res = @file_get_contents ( $this->api_url, false, $context);
		if (!$res) {
			throw new Exception('Could not retrieve data from "' . $this->api_url . '".');
		}

		// JSON decode
		$api_res = json_decode($res, true);

		if (!$api_res) {
			$msg = print_r($res, true);
			throw new Exception('Response data is not in JSON format.' . "\n\n" . $msg);
		}

		// Check API return value
		if (array_key_exists('error', $api_res)) {
			$msg = print_r($api_res, true);
			throw new Exception('An API error has occurred.' . "\n\n" . $msg);
		}
		return $api_res;
	}
}
?>