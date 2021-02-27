<?php include("head.php") ?>

<?php
	include_once("config.php");
	include_once("functions.php");

	if (isset($_POST['email'])) {
		$email = $_POST['email'];
		$entercode = "Enter the code:";
	} else {
		$email = "";
		$entercode = "";
	}

	if (isset($_POST['code'])) {
		$code = $_POST['code'];
		$enterpassword = "Enter new password:";
	} else {
		$code = "";
		$enterpassword = "";
	}
	if (isset($_POST['badaddress'])) {$badaddress = true;} else {$badaddress = false;}

	if (($send_SMS) && ($send_Email)) {
		$notifier = "phone by text message and to your alternate email address.";
	} elseif ($send_SMS) {
		$notifier = "phone by text message.";
	} elseif ($send_Email) {
		$notifier = "alternate email account.";
	}
?>

<div class="section">
	<div class="secleft">
		<div class="sectitle">Quick Guide</div>
	</div>
	<div class="secright">
		Enter your email address below and a one time code will be sent to your <?php echo $notifier; ?> Enter the code to authorize a password change.<br><br>
	</div>
	<div class="clear"></div>
</div>

<div class="section">
	<div class="secleft">
		<div class="sectitle">Step 1<br>Enter your email</div>
	</div>
	<div class="secright">
		Enter your email address.<br><br>
		<?php 
			echo "<form action='./' method='POST'>";
				echo "<input type='text' size='30' name='email' value='".$email."' pattern='^[a-zA-Z0-9.!#$%&*+/=?^_{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$' />";
				echo "<input type='submit' value='Submit'>";
			echo "</form>";
		?>
	</div>
	<div class="clear"></div>
</div>

<?php
	include_once("config.php");
	include_once("functions.php");

	if ($email) {
		
		$emailexists = $pdo->query("SELECT COUNT(*) FROM hm_accounts WHERE accountaddress = '".$email."';")->fetchColumn();
		if ($emailexists > 0) {
		
			$find_email_sql = $pdo->prepare("
				SELECT 
					a.accountid,
					a.accountaddress,
					b.mobilenumber,
					b.altemail
				FROM 
				(
					SELECT
						accountid, 
						accountaddress
					FROM hm_accounts
				) a
				LEFT JOIN
				(
					SELECT 
						accountid,
						mobilenumber,
						altemail
					FROM hm_pw_change
				)  b
				ON a.accountid = b.accountid
				WHERE accountaddress = '".$email."'
			");
			$find_email_sql->execute();
			while($row = $find_email_sql->fetch(PDO::FETCH_ASSOC)){
				$accountid = $row['accountid'];
				$mobilenumber = $row['mobilenumber'];
				$altemail = $row['altemail'];
			}

			if ((($send_Email) && (strlen($altemail) == 0)) || (($send_SMS) && (strlen($mobilenumber) == 0))) {
				if ((!$send_Email) && ((strlen($mobilenumber) == 0))) {
					$noalt = "No mobile number associated with this account. Please notify administrator: ".$adminEmail;
				}
				if ((!$send_SMS) && ((strlen($altemail) == 0))) {
					$noalt = "No alternate email address associated with this account. Please notify administrator: ".$adminEmail;
				}
				if (($send_SMS) && ($send_Email) && (strlen($altemail) == 0) && (strlen($mobilenumber) == 0)) {
					$noalt = "No alternate email address or mobile number associated with this account. Please notify administrator: ".$adminEmail;
				}

				echo "
					<div class='section'>
						<div class='secleft'>
							<div class='sectitle'>Error!</div>
						</div>
						<div class='secright'>
							".$noalt."
						</div>
						<div class='clear'></div>
					</div>
				";

			} else {

				$setnewcode = setCode();
				$pdo->exec("UPDATE hm_pw_change SET onetimecode = '".$setnewcode."', initpwchange = NOW() WHERE accountid = '".$accountid."';");
				$otcmessage = "A password change has been requested for your email. If you did not request this change, you can safely ignore this message. \n\nTemporary reset code: ".$setnewcode;

				if (($send_SMS) && ($mobilenumber != "")) {
					sendOTCbySMS($mobilenumber, $otcmessage);
				}
				if (($send_Email) && ($altemail != "")) {
					sendOTCbyEmail($altemail, $otcmessage);
				}

				echo "
					<div class='section'>
						<div class='secleft'>
							<div class='sectitle'>Step 2<br>Enter the code</div>
						</div>
						<div class='secright'>
							A one-time code has been sent to your ".$notifier." Enter the code below.<br><br>
							<form action='./resetpassword.php' method='POST'>
								<input type='text' size='6' name='code' value='".$code."' pattern='^[0-9]{6}$' autocomplete='off' />
								<input type='hidden' name='email' value='".$email."'>
								<input type='hidden' name='accountid' value='".$accountid."'>
								<input type='submit' value='Submit'>
							</form>
						</div>
						<div class='clear'></div>
					</div>
				";
			}
		} else {
			echo "
				<div class='section'>
					<div class='secleft'>
					</div>
					<div class='secright'>
						The email entered was not found on our system. Please check the spelling and re-enter your email address.
					</div>
					<div class='clear'></div>
				</div>
			";
		}
	}
?>


<?php include("foot.php") ?>