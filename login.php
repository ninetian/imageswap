<?php
	/*  Copyright 2014, Philippe Gray
	This file is part of Image Swap.

    Image Swap is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Image Swap is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Image Swap.  If not, see <http://www.gnu.org/licenses/>.
*/
	require_once('config.php');
	require_once('recaptchalib.php');
	
	session_start();
	$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name.';port='.$db_port.';charset=utf8',$db_user,$db_pass);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);	
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$auth_check = $db -> prepare('SELECT * FROM user WHERE auth_key=?');
	$auth_check -> execute(array(session_id()));
	$auth = $auth_check -> fetchAll(PDO::FETCH_ASSOC);
	
	if(time()-$auth[0]['auth_time']<60*15 AND $ip_string == $auth[0]['auth_ip'])
	{
		header('Location: account.php');
		die();
	}
	if(isset($_GET['action']))
	{
		$pass_check = $db -> prepare('SELECT pass FROM user WHERE user=?');
		$pass_check -> execute(array($_POST['user']));
		$pass = $pass_check -> fetchAll(PDO::FETCH_ASSOC);
		$resp = recaptcha_check_answer ($privatekey,
						$_SERVER["REMOTE_ADDR"],
						$_POST["recaptcha_challenge_field"],
						$_POST["recaptcha_response_field"]);
		if(!$resp->is_valid)
		{
			echo '<script>window.location="account.php?action=1";</script>';
			die('What do you think that CAPTCHA is for, decoration?');
		}
		if($pass[0]['pass'] == hash('sha256',$_POST['pass']))
		{
			session_regenerate_id();
			$push_auth = $db -> prepare('UPDATE user SET auth_time=?, auth_key=?, auth_ip=? WHERE user=?');
			$push_auth -> execute(array(time(),session_id(),$ip_string,$_POST['user']));
			//echo 'good pass';
			header('Location: account.php');
			die();
			
		}
	}
				
				
?>




<!DOCTYPE html>
<html>
	<head>
	<meta charset="UTF-8">
	<title>ImageSwap</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="style.css">
	<link href='http://fonts.googleapis.com/css?family=Duru+Sans' rel='stylesheet' type='text/css'>
	</head>
	
	<body>
		<script type="text/javascript">
		var RecaptchaOptions = {
		theme : 'white'
		};
		</script>
	<div id="header" style="text-align:center;padding:30px;">
		<a href="upload.php"><img src="logo.png" ></a>
	</div>
	<div id="wrapper">
		<div id="content" style="text-align:center;">

				
				
			
				<form action="login.php?action=1" method="post">
				<input type="text" name="user" placeholder="user name" style="border-radius:0px;"/>
				<input type="password" name="pass" placeholder="passphrase" style="border-radius:0px;"/>
				<input type="submit" value="Enter" class="button1"/>
				<br/><br/><center>
				<?php 
				echo recaptcha_get_html($publickey); 
				?>
				</center>
				</form>
			
				
				
				
	

		
		</div>
	</div>
	</body>
</html>





