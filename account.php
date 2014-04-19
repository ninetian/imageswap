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

	session_start();
	$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name.';port='.$db_port.';charset=utf8',$db_user,$db_pass);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);	
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$auth_check = $db -> prepare('SELECT * FROM user WHERE auth_key=?');
	$auth_check -> execute(array(session_id()));
	$auth = $auth_check -> fetchAll(PDO::FETCH_ASSOC);
					
	if(time()-$auth[0]['auth_time']<60*15 AND $ip_string == $auth[0]['auth_ip'])
	{
		//authenticated all stuff here
		if(isset($_GET['logout']))
		{
			$logout = $db -> prepare('UPDATE user SET auth_time=0 WHERE auth_key=?');
			$logout -> execute(array(session_id()));
			session_destroy();
			header('Location: login.php');
			die('logged out');
		}
		
		
		
		
	}
	else
	{
		header('Location: login.php');
		die();
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
		
		<div style="width:500px;text-align:left;">
			<br/><a href="account.php?logout=1" class="button1">Log out</a><br/><br/>
			<form action="account.php" method="post">
				<input type="text" placeholder="Image ID" name="imageid" style="border-radius:0px;"/>
				<input type="submit" class="button1" value="Search"/>
				</form><br/>
			<form action="account.php" method="post">
				<input type="text" placeholder="IP address" name="imageip" style="border-radius:0px;"/>
				<input type="submit" class="button1" value="Search"/><br/>
			</form>
			
			<?php
			if(isset($_POST['delete']))
			{
				
				$delete_image = $db -> prepare('DELETE FROM res WHERE name=?');
				$delete_image -> execute(array($_POST['delete']));
				array_map('unlink', glob('res/'.$_POST['delete'].'*'));
				array_map('unlink', glob('res/'.$_POST['delete'].'_thumb*'));

				
			}
			if(isset($_POST['deleteall']))
			{
				
				
				$get_images = $db -> prepare('SELECT name FROM res WHERE ip=?');
				$get_images -> execute(array($_POST['deleteall']));
				$image = $get_images -> fetchAll(PDO::FETCH_ASSOC);
				$count = 0;
				while(isset($image[$count]['name']))
				{
					array_map('unlink', glob('res/'.$image[$count]['name'].'*'));
					array_map('unlink', glob('res/'.$image[$count]['name'].'_thumb*'));
					$count++;
					
				}
				
				$delete_images = $db -> prepare('DELETE FROM res WHERE ip=?');
				$delete_images -> execute(array($_POST['deleteall']));
				

			}
			if(isset($_POST['ban']))
			{
				$ban_ip = $db -> prepare('INSERT INTO ban (ip,time) VALUES (?,?)');
				$ban_ip -> execute(array($_POST['ban'],time()));
				
				
			}
			if(strlen($_POST['imageid'])>2)
			{
				$get_image = $db -> prepare('SELECT * FROM res WHERE name=?');
				$get_image -> execute(array($_POST['imageid']));
				$image = $get_image -> fetchAll(PDO::FETCH_ASSOC);
				
				if(isset($image[0]['name']))
				{	
					echo '<div style="width:200px;border: solid 1px;padding:5px;">';
					echo '<a href="http://'.$domain.'/res/'.$image[0]['name'].'_thumb.'.$image[0]['ext'].'" target="_blank">'.$image[0]['name'].'.'.$image[0]['ext'].'</a> Uploaded by <br/>'.$image[0]['ip'].'<br/><br/> 
					<form action="account.php" method="post"><input type="hidden" name="delete" value="'.$image[0]['name'].'"/><input type="submit" value="Delete" class="button1"/></form>
					</div>';
				}
				
			}
			
			if(strlen($_POST['imageip'])>2)
			{	
				$get_image = $db -> prepare('SELECT * FROM res WHERE ip=? ORDER BY id DESC');
				$get_image -> execute(array($_POST['imageip']));
				$image = $get_image -> fetchAll(PDO::FETCH_ASSOC);
				echo '<br/><form action="account.php" method="post"><input type="hidden" name="ban" value="'.$_POST['imageip'].'"/> <input type="submit" value="Ban IP" class="button1"/> '.$image[0]['ip'].'</form> ';
				echo '<br/><form action="account.php" method="post"><input type="hidden" name="deleteall" value="'.$_POST['imageip'].'"/> <input type="submit" value="Delete all" class="button1"/> </form> ';
				
				echo '<br/><br/><div style="width:200px;border: solid 1px;padding:5px;">';
				$count=0;
				
				while(isset($image[$count]['id']))
				{
					echo '<p><a href="http://'.$domain.'/res/'.$image[$count]['name'].'_thumb.'.$image[$count]['ext'].'" target="_blank">'.$image[$count]['name'].'.'.$image[$count]['ext'].'</a> 
					<form action="account.php" method="post"><input type="hidden" name="imageip" value="'.$image[$count]['ip'].'"/><input type="hidden" name="delete" value="'.$image[$count]['name'].'"/> <input type="submit" value="Delete" class="button1"/></form>';
					
					++$count;
				}
				echo '</div>';
			}
			
			echo '<br/><br/>Browse New <form action="account.php" method="post"><input type="text" name="page" placeholder="# of results"/><input type="submit" value="View" class="button1"/></form>';
			
			$get_image = $db -> prepare('SELECT * FROM res ORDER BY id DESC LIMIT 0,?');
			$get_image -> execute(array($_POST['page']));
			$image = $get_image -> fetchAll(PDO::FETCH_ASSOC);
			$count = 0;
			while(isset($image[$count]['id']))
			{
				echo '<p><a href="http://'.$domain.'/res/'.$image[$count]['name'].'_thumb.'.$image[$count]['ext'].'" target="_blank">'.$image[$count]['name'].'.'.$image[$count]['ext'].'</a> 
					<form action="account.php" method="post"><input type="hidden" name="delete" value="'.$image[0]['name'].'"/> '.$image[0]['ip'].' <input type="submit" value="Delete" class="button1"/></form>';
					
					++$count;
			}
				
			?>
		
							
		</div>
		



		
		</div>
	</div>
	</body>
</html>




