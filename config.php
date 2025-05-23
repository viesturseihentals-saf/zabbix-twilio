<?php
// **********************************************
// *** SCRIPT ***
// **********************************************
// Directory where the SCRIPT is installed
$SCRIPT_DIR = '/usr/lib/zabbix/alertscripts/zabbix-twilio/';
// URL where the zabbix-twilio.php script is installed (URL accessible from Twilio)
$SCRIPT_URL = 'http://<Server IP>/zabbix-twilio/zabbix-twilio.php';


// **********************************************
// *** Log ***
// **********************************************
// Log file
$LOG_DIR= $SCRIPT_DIR . '/logs';
// Debug log output (true: enabled, false: disabled)
$DEBUG_FLG = false;


// **********************************************
// *** Twilio ***
// **********************************************
// Twilio REST API version
$TWILIO_API_VERSION = '2010-04-01';
// ACCOUNT SID
$ACCOUNT_SID = '<ACCOUNT_SID>';
// AUTH TOKEN
$AUTH_TOKEN = '<AUTH_TOKEN>';
// Calling number
$TWILIO_NUMBER = '+81<TWILIO_NUMBER>';
// Call time
$CALL_TIME = 12;


// **********************************************
// *** IVR ***
// **********************************************
// Message for automatic notification (message played when calling for the first time)
// Using $$MESSAGE$$ will replace it with the value of voice: specified in the Zabbix action message.
// Using $$REGKEY$$ will replace it with the value of the Zabbix registration confirmation key variable $REGKEY.
$MESSAGE_NOTICE = 'This is an automatic notification from the monitoring center. $$MESSAGE$$ has occurred. If you can respond, press $$DIGITS$$. If it is difficult to respond, please hang up.';
// Message when the confirmation key does not match
// Using $$REGKEY$$ will replace it with the value of the Zabbix registration confirmation key variable $REGKEY.
$MESSAGE_RETYPE = 'The entered number does not match or could not be recognized. Please enter again. If you can respond, press $$DIGITS$$. If it is difficult to respond, please hang up.';
// Message when Zabbix registration is OK
$MESSAGE_REG_OK = 'Registration to Zabbix has been completed. The call will end.';
// Message when Zabbix registration is NG
$MESSAGE_REG_NG = 'Registration to Zabbix has failed. The call will end.';
// Confirmation key. Specify a numerical value from 1-9. If it matches, register with Zabbix. The number of digits can be specified with REGKEY.
$DIGITS = 1;
// Number of digits for the confirmation key.
$DIGITS_NUM = 1;
// Waiting time for entering the confirmation key
$DIGITS_TIMEOUT = 20;


// **********************************************
// *** ZABBIX ***
// **********************************************
// Zabbix server API URL
$ZABBIX_API = 'http://localhost/zabbix/api_jsonrpc.php';
// Name of the Zabbix user to use when registering events
$ZABBIX_USER = '<username>';
// Password for the above user
$ZABBIX_PASS = '<password>';
?>
