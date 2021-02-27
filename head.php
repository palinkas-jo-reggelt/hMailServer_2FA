<!DOCTYPE html> 
<html>
<head>
<title>Password Changer</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Style-Type" content="text/css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" media="all" href="stylesheet.css">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet"> 
<script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript">
$(function(){
    $('#jq-password [type="password"]').keyup(function(){
        //Store the field objects into variables ...
        var password = $('#password3');
        var confirm  = $('#confirm3');
        var message  = $('#confirm-message3');
        
        //Set the colors we will be using ...
        var good_color = "#66cc66";
        var bad_color  = "#ff6666";

        if(password.val() == confirm.val()){
            confirm.css('background-color', good_color);
            message.css('color', good_color).html("Passwords Match!");
        } else {
            confirm.css('background-color', bad_color);
            message.css('color', bad_color).html("Passwords Do Not Match!");
        }
    });
});
</script></head>
<body>

<div class="header">
	<div class="banner">
		<h1>Password Reset</h1>
	</div>
</div>

<div class="wrapper">
