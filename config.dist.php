<?php

/*###   ADMIN   #########################################*/
$user_name       = 'admin';                 // Admin Page Username
$pass_word       = 'supersecretpassword';   // Admin Page Password
$cookie_duration = 90;                      // Admin Page Cookie Duration in days
$no_of_records_per_page = 25;               // Pagination: Number of account records per page 


/*###   ONE TIME CODE DELIVERY   ########################*/
$send_SMS        = true;                    // Send OTC message by gammu sms
$send_Email      = true;                    // Send OTC message by email
$codeExpiry      = 10;                      // Number of minutes before one time code expires - applies to entire password change process from start to finish

/*###   OTC EMAIL   #####################################*/
$otcFromAddress  = 'notify@mydomain.tld';   // From address when sending one time code by email
$otcSubject      = 'Password Reset Code';   // Subject when sending one time code by email
$adminEmail      = 'postmaster@domain.com'; // Postmaster account for users to contact in case of error

/*###   OTC TWILIO   ####################################*/
$use_Twilio      = true;                    // If true, use Twilio to send SMS
$account_sid     = 'Twilio-Account-SID';    // Twilio https://www.twilio.com/en-us/messaging/channels/sms
$auth_token      = 'Twilio-Auth-Token';     // Twilio https://www.twilio.com/en-us/messaging/channels/sms
$twilio_number   = "+12125551212";          // Twilio number you own

/*###   OTC GAMMU   #####################################*/
$use_Gammu       = false;                   // If true, use Gammu to send SMS
$smsdrc          = 'C:\\gammu\\bin\\smsdrc';// Location of gammu smsdrc (double backslashes, please)

/*###   PASSWORD VALIDATION   ###########################*/
$pwMinLength         = 10;                 // Minimum password length
$pwValidateLowerCase = true;               // Lower case letters required
$pwValidateUpperCase = true;               // Upper case letters required
$pwValidateNumeric   = true;               // Numbers required
$pwValidateSymbols   = true;               // Symbols required
$pwSymbols           = "\!\#\$\%\^\&\*\(\)\_\-\+\=\<\>\,\.\?";   //Acceptable symbols (escape all characters to avoid conflict with php)


/*###   SCRIPT VARIABLES   ##############################*/
$webmailurl = "https://webmail.mydomain.tld/";  // Webmail URL

/*	hMailServer Database Variables 

	MySQL only!
	
	For MySQL connection string use 'driver' = 'mysql'
	For ODBC  connection string use 'driver' = 'odbc'
*/
$Database = array (
	'host'        => 'localhost',
	'username'    => 'hmailserver',
	'password'    => 'supersecretpassword',
	'dbname'      => 'hmailserver',
	'driver'      => 'mysql',
	'port'        => '3306',
	'dsn'         => 'MariaDB ODBC 3.1 Driver'
);

// hMailServer COM Authentication: "Administrator" Password
$hMSAdminPass = 'supersecretpassword';

/*  TimeZone required to determine if one time password has expired
	Expiry period applies to entire password change process - after code expires, user will not be able to change password and will have to start the process again
    TimeZone offset required to compare datetime of OTC in database against current time
    https://www.php.net/manual/en/timezones.php
*/
$TimeZone = 'America/New_York';

?>