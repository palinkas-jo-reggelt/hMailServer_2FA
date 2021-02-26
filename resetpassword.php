<?php include("head.php") ?>

<?php
	include_once("config.php");
	include_once("functions.php");


	if (isset($_POST['email'])) {$email = $_POST['email'];} else {$email = "";}
	if (isset($_POST['accountid'])) {$accountid = $_POST['accountid'];} else {$accountid = "";}
	if (isset($_POST['code'])) {$code = $_POST['code'];} else {$code = "";}
	if (isset($_POST['password'])) {$password = $_POST['password'];} else {$password = "";}
	$onetimecode = NULL;
	$initpwchange = NULL;

	$getcode = $pdo->prepare("
		SELECT 
			a.accountid,
			a.accountaddress,
			b.onetimecode,
			b.initpwchange
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
				onetimecode,
				initpwchange
			FROM hm_pw_change
		)  b
		ON a.accountid = b.accountid
		WHERE accountaddress = '".$email."'
	");
	$getcode->execute();
	while($row = $getcode->fetch(PDO::FETCH_ASSOC)){
		$onetimecode = $row['onetimecode'];
		$initpwchange = $row['initpwchange'];
	}

	$otc_init = strtotime($initpwchange." ".$TimeZone);
	if ((time() - $otc_init) < ($codeExpiry * 60)) {$temptime = true;} else {$temptime = false;}
	
	if (($email) && ($accountid) && ($code == $onetimecode) && ($temptime)) {

		echo "
			<div class='section'>
				<div class='secleft'>
					<div class='sectitle'>Step 3<br>Enter New Password</div>
				</div>
				<div class='secright'>
					<form id='jq-password' action='./confirm.php' method='POST'>
						<table>
							<tr>
								<td><label for='password'>New Password:</label></td>
								<td><input type='password' name='password' id='password3' autocomplete='off'></td>
							</tr>
							<tr>
								<td><label for='confirm'>Confirm Password:</label></td>
								<td><input type='password' name='confirm' id='confirm3' autocomplete='off'></td>
								<span id='confirm-message3' class='confirm-message'></span>
							</tr>
							<tr>
								<td></td>
								<input type='hidden' name='email' value='".$email."'>
								<input type='hidden' name='code' value='".$code."'>
								<input type='hidden' name='accountid' value='".$accountid."'>
								<td style='text-align:right;'><input type='submit' value='Submit'></td>
							</tr>
						</table>
					</form>";

		echo "<br><b>Minimum password attributes:</b><br>";			
		echo "<br>The minimum length is ".$pwMinLength." characters.";
		if ($pwValidateLowerCase) {echo "<br>Lower case letters are required";}
		if ($pwValidateUpperCase) {echo "<br>Upper case letters are required";}
		if ($pwValidateNumeric) {echo "<br>Numbers are required";}
		if ($pwValidateSymbols) {echo "<br>Symbols are required";}

		echo	"</div>
				<div class='clear'></div>
			</div>
		";

	} else {
		if (!$temptime) {
			echo "
				<div class='section'>
					<div class='secleft'>
						<div class='sectitle'>Code Expired!</div>
					</div>
					<div class='secright'>
						Your one time code expired. Please <a href='./'><b>start over</b></a> to receive a new code.<br><br>
					</div>
					<div class='clear'></div>
				</div>
			";
		} else {

			echo "
				<div class='section'>
					<div class='secleft'>
						<div class='sectitle'>Step 3<br>Incorrect Code!</div>
					</div>
					<div class='secright'>
						The code you entered is incorrect. Please re-enter the code below or <a href='./'><b>start over</b></a> to receive a new code.<br><br>
						<form action='./resetpassword.php' method='POST'>
							<input type='text' size='6' name='code' value='".$code."' pattern='^[0-9]{6}$' />
							<input type='hidden' name='email' value='".$email."'>
							<input type='hidden' name='accountid' value='".$accountid."'>
							<input type='submit' value='Submit'>
						</form>
					</div>
					<div class='clear'></div>
				</div>
			";

		}
	}

?>
