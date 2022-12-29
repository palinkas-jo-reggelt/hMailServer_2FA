<?php

	If ($Database['driver'] == 'mysql') {
		$pdo = new PDO("mysql:host=".$Database['host'].";port=".$Database['port'].";dbname=".$Database['dbname'], $Database['username'], $Database['password']);
	} ElseIf ($Database['driver'] == 'odbc') {
		$pdo = new PDO("odbc:Driver={".$Database['dsn']."};Server=".$Database['host'].";Port=".$Database['port'].";Database=".$Database['dbname'].";User=".$Database['username'].";Password=".$Database['password'].";");
	} Else {
		echo "Configuration Error - No database driver specified";
	}

	Function displayMobileNumber($mobilenumber){
		if (preg_match('/^(\d{3})(\d{3})(\d{4})$/', $mobilenumber,  $matches )){
			$result = '('.$matches[1].') '.$matches[2].'-'.$matches[3];
		} else {
			$result = $mobilenumber;
		}
		return $result;
	}

	Function sendOTCbySMS($mobilenumber, $otcmessage){
		global $smsdrc;
		global $account_sid;
		global $auth_token;
		global $twilio_number;
		global $use_Twilio;
		global $use_Gammu;

		$otcmsg = str_replace("\n", "", $otcmessage);
		$len = strlen($otcmsg);
		$fullnum = "+1".$mobilenumber;

		if ($use_Twilio) {
			$url = "https://api.twilio.com/2010-04-01/Accounts/".$account_sid."/Messages.json";
			$data = array (
				'From' => $twilio_number,
				'To' => $fullnum,
				'Body' => $otcmsg
			);
			$post = http_build_query($data);
			$x = curl_init($url );
			curl_setopt($x, CURLOPT_POST, true);
			curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($x, CURLOPT_USERPWD, "$account_sid:$auth_token");
			curl_setopt($x, CURLOPT_POSTFIELDS, $post);
			$y = curl_exec($x);
			curl_close($x);
			// var_dump($post);  // For debugging
			// echo "<br><br>";  // For debugging
			// var_dump($y);     // For debugging
		}
		
		if ($use_Gammu) {
			exec('gammu-smsd-inject.exe -c '.$smsdrc.' TEXT '.$fullnum.'  -len '.$len.' -text "'.$otcmsg.'"');
		}
	}
	
	Function sendOTCbyEmail($email, $otcmessage){
		global $hMSAdminPass;
		global $otcFromAddress;
		global $otcSubject;
		$hMS = new COM("hMailServer.Application");
		$hMS->Authenticate("Administrator", $hMSAdminPass);
		$hMSmsg = new COM("hMailServer.Message");
		$hMSmsg->From = $otcFromAddress;
		$hMSmsg->FromAddress = $otcFromAddress;
		$hMSmsg->AddRecipient($email, $email);
		$hMSmsg->Subject = $otcSubject;
		$hMSmsg->Body = $otcmessage;
		$hMSmsg->Save();
	}

	Function setCode(){
		$otc = array(); 
		for ($i = 0; $i < 6; $i++) {
			$n = rand(0, 9);
			$otc[] = $n;
		}
		return implode($otc); 
	}

	Function changePassword($email, $newPassword){
		global $hMSAdminPass;
		$hMS = new COM("hMailServer.Application");
		$hMS->Authenticate("Administrator", $hMSAdminPass);
		$Domain = explode("@", $email)[1];
		$hMSDomain = $hMS->Domains->ItemByName($Domain);
		$hMSAccount = $hMSDomain->Accounts->ItemByAddress($email);
		$hMSAccount->Password = $newPassword;
		$hMSAccount->Save();
	}

	Function testPassword($email, $newPassword){
		global $hMSAdminPass;
		$hMS = new COM("hMailServer.Application");
		$hMS->Authenticate("Administrator", $hMSAdminPass);
		$Domain = explode("@", $email)[1];
		$hMSDomain = $hMS->Domains->ItemByName($Domain);
		$hMSAccount = $hMSDomain->Accounts->ItemByAddress($email);
		return $hMSAccount->ValidatePassword($newPassword);
	}

	Function validatePassword($password){
		global $pwMinLength;
		global $pwValidateLowerCase;
		global $pwValidateUpperCase;
		global $pwValidateNumeric;
		global $pwValidateSymbols;
		global $pwSymbols;
		$pwLenRegex = "/^.{".$pwMinLength.",}$/";
		$pwSymbolRegex = "/[".$pwSymbols."]/";
		
		$pwml = $pwlc = $pwuc = $pwn = $pws = "";
		
		if (!preg_match($pwLenRegex,$password)) {
			$pwml = "<br>* Password has too few characters. The minimum length is ".$pwMinLength." characters.";
		}
		if ($pwValidateLowerCase) {
			if (!preg_match("/[a-z]/",$password)) {
				$pwlc = "<br>* Password has no lower case letters. At least one lower case letter is required.";
			}
		}
		if ($pwValidateUpperCase) {
			if (!preg_match("/[A-Z]/",$password)) {
				$pwuc = "<br>* Password has no upper case letters. At least one upper case letter is required.";
			}
		}
		if ($pwValidateNumeric) {
			if (!preg_match("/[0-9]/",$password)) {
				$pwn = "<br>* Password has no numbers. At least one number is required.";
			}
		}
		if ($pwValidateSymbols) {
			if (!preg_match($pwSymbolRegex,$password)) {
				$pws = "<br>* Password has no symbols. At least one symbol is required.";
			}
		}

		return $pwml.$pwlc.$pwuc.$pwn.$pws;
	}

?>
