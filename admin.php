<?php
	include_once("config.php");

	if (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
		if (!(($_COOKIE['username'] === $user_name) && ($_COOKIE['password'] === md5($pass_word)))) {
			header('Location: login.php');
		}
	} else {
		header('Location: login.php');
	}
?>

<!DOCTYPE html> 
<html>
<head>
<title>hMailServer 2FA Admin</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Style-Type" content="text/css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" media="all" href="adminstylesheet.css">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet"> 
</head>
<body>

<div class="header">
	<div class="banner"><h1><a href="./admin.php">hMailServer 2FA Admin</a></h1></div>
</div>

<div class="wrapper">
<div class="section">

<?php
	include_once("config.php");
	include_once("functions.php");

	if (isset($_GET['page'])) {$page = $_GET['page'];} else {$page = 1;}
	if (isset($_GET['search'])) {$search = $_GET['search'];} else {$search = "";}
	if (isset($_POST['accountid'])) {$accountid = $_POST['accountid'];} else {$accountid = "";}
	if (isset($_POST['updatemobilenumber'])){
		$pdo->exec("UPDATE hm_pw_change SET mobilenumber = '".$_POST['updatemobilenumber']."' WHERE accountid='".$accountid."';");
		header("Location: ".$_SERVER["REQUEST_URI"]);
	}
	if (isset($_POST['updatealtemail'])){
		$pdo->exec("UPDATE hm_pw_change SET altemail = '".$_POST['updatealtemail']."' WHERE accountid='".$accountid."';");
		header("Location: ".$_SERVER["REQUEST_URI"]);
	}

	if ($search==""){
		$search_sql = "";
	} else {
		$search_sql = " WHERE accountaddress LIKE '%".$search."%'";
	}

	echo "<form action='".$_SERVER["REQUEST_URI"]."' method='GET'>";
	echo "Search Accounts:<br><input type='text' size='30' name='search' value='".$search."'><input type='submit' name='submit' value='Search' >";
	echo "</form>";
	echo "<br>";

	$pdo->exec("
		CREATE TABLE IF NOT EXISTS hm_pw_change (
			accountid int(11) NOT NULL,
			mobilenumber varchar(10) NOT NULL,
			altemail varchar(36) NOT NULL,
			onetimecode int(6) NOT NULL DEFAULT 0,
			initpwchange datetime NOT NULL DEFAULT '1970-01-01 01:00:00',
			PRIMARY KEY (accountid)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");

	$pdo->exec("
		INSERT INTO hm_pw_change(accountid)
		SELECT hm_accounts.accountid 
		FROM hm_accounts 
		WHERE hm_accounts.accountid NOT IN (
			SELECT hm_pw_change.accountid 
			FROM hm_pw_change
		)
	");

	$offset = ($page-1) * $no_of_records_per_page;
	$total_pages_sql = $pdo->prepare("
		SELECT Count( * ) AS count 
		FROM hm_accounts
		".$search_sql
	);
	$total_pages_sql->execute();
	$total_rows = $total_pages_sql->fetchColumn();
	$total_pages = ceil($total_rows / $no_of_records_per_page);

	$sql = $pdo->prepare("
		SELECT	
			a.accountid,
			a.username,
			a.domainname,
			a.accountaddress,
			b.mobilenumber,
			b.altemail,
			b.onetimecode,
			b.initpwchange
		FROM 
		(
			SELECT 
				accountid, 
				SUBSTRING_INDEX(accountaddress, '@', 1) AS username,
				SUBSTRING_INDEX(accountaddress, '@', -1) AS domainname,
				accountaddress
			FROM hm_accounts
		)  a
		LEFT JOIN
		(
			SELECT 
				accountid,
				mobilenumber,
				altemail,
				onetimecode,
				initpwchange
			FROM hm_pw_change
		)  b
		ON a.accountid = b.accountid
		".$search_sql." 
		ORDER BY domainname ASC, username ASC
		LIMIT ".$offset.", ".$no_of_records_per_page
	);
	$sql->execute();
	
	echo "<table class='section' width='100%'>
		<tr>
			<th colspan='6' style='text-align:left;'>ACCOUNTS: ".$total_rows."</th>
		</tr>
		<tr>
			<th>ID</th>
			<th>Account</th>
			<th>Mobile Number</th>
			<th>Alternate Email</th>
			<th>Last PW Change</th>
			<th>OTC</th>
		</tr>";

	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		echo "<tr style='text-align:center;'>";

			echo "<td>".$row['accountid']."</td>";
			echo "<td style='text-align:left;padding-left:5px;'>".$row['accountaddress']."</td>";
			echo "<td>
					<form action='".$_SERVER["REQUEST_URI"]."' method='POST' onsubmit='return confirm(\"Are you sure you want to change the mobile number?\");'>
						<input type='text' size='12' name='updatemobilenumber' placeholder='".displayMobileNumber($row['mobilenumber'])."'>
						<input type='hidden' name='accountid' value='".$row['accountid']."'>
						<input type='submit' name='submit' value='Edit' >
					</form>
				  </td>";
			echo "<td>
					<form action='".$_SERVER["REQUEST_URI"]."' method='POST' onsubmit='return confirm(\"Are you sure you want to change the alternate email address?\");'>
						<input type='text' size='30' name='updatealtemail' placeholder='".$row['altemail']."'>
						<input type='hidden' name='accountid' value='".$row['accountid']."'>
						<input type='submit' name='submit' value='Edit' >
					</form>
				  </td>";
			echo "<td>".date("Y/n/j G:i:s", strtotime($row['initpwchange']))."</td>";
			echo "<td>".$row['onetimecode']."</td>";

		echo "</tr>";
	}

	echo "</table>";
	echo "<br>";
	
	if ($search == ""){$searchpage = "";} else {$searchpage = "&search=".$search;}
	if ($total_rows > $no_of_records_per_page) {
		if ($page <= 1){echo "<li>First </li>";} else {echo "<li><a href=\"?page=1".$searchpage."\">First </a><li>";}
		if ($page <= 1){echo "<li>Prev </li>";} else {echo "<li><a href=\"?page=".($page - 1).$searchpage."\">Prev </a></li>";}
		if ($page >= $total_pages){echo "<li>Next </li>";} else {echo "<li><a href=\"?page=".($page + 1).$searchpage."\">Next </a></li>";}
		if ($page >= $total_pages){echo "<li>Last</li>";} else {echo "<li><a href=\"?page=".$total_pages.$searchpage."\">Last</a></li>";}
	}

?>

</div> <!-- end of section -->
<div class="footer"></div>
</div> <!-- end WRAPPER -->
</body></html>