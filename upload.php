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
var sc_project=9746558;
var sc_invisible=1;
var sc_security="e41ce88e";
var scJsHost = (("https:" == document.location.protocol) ?
"https://secure." : "http://www.");
document.write("<sc"+"ript type='text/javascript' src='" +
scJsHost+
"statcounter.com/counter/counter.js'></"+"script>");
</script>


	<div id="header" style="text-align:center;padding:30px;">
		<a href="upload.php"><img src="logo.png" ></a>
		
	</div>
	<div id="wrapper">
		<script type="text/javascript">
		var RecaptchaOptions = {
		theme : 'white'
		};
		</script>
		<div id="content-fixed">
			<?php 
			
			if(!isset($_POST['active']))
			{
				echo'
				<span style="text-align:center;display:block;">
				<form action="upload.php" method="post" enctype="multipart/form-data">
				<br/>
				<input type="file" name="file1" id="file1" /><br/>
				<input type="hidden" value="1" name="active"/><br/><br/>';
				
				 
				if(isset($_SESSION['captcha_needed']))
				{
					 echo '<center>'.recaptcha_get_html($publickey).'</center>';

				}
				
				echo '<br/>I agree to the <a href="#" onclick="document.getElementById(\'replace\').innerHTML=\'<p>Any image that is legal is allowed. We remove explicit images portraying underage persons. We also remove copyright infringing images in accordance with that pesky DMCA. \';">Terms of Service</a> <input type="submit" class="button1" value="Upload" name="submit"/></form></span>';
				echo '<div id="replace"></div>';
				if(isset($_SESSION['exist']))
				{
					echo '<p style="color:#FF0000;display:block;text-align:center;">No image selected</p>';
				}
				
				
				
				if(isset($_SESSION['type']))
				{
					echo '<p style="color:#FF0000;display:block;text-align:center;">Only images may be uploaded</p>';
				}
				if(isset($_GET['q']) OR isset($_SESSION['size']))
				{
					echo '<p style="color:#FF0000;display:block;text-align:center;">Uploads must be less than 3 Mb</p>';
				}
				if(isset($_SESSION['ban']))
				{
					echo '<p style="color:#FF0000;display:block;text-align:center;">Your IP was temporarily banned</p>';
				}
				unset($_SESSION['exist']);
				unset($_SESSION['type']);
				unset($_SESSION['size']);
				unset($_SESSION['ban']);
				
				
				
			}
			else
			
			{
				
				$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name.';port='.$db_port.';charset=utf8',$db_user,$db_pass);
				$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);	
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
				
				//Flood protect
				$time_check = $db -> prepare('SELECT time FROM res WHERE ip=? ORDER BY id DESC LIMIT 3');
				$time_check -> execute(array($ip_string));
				$last_time = $time_check -> fetchAll(PDO::FETCH_ASSOC);
				
                 if (isset($last_time[2]['time']))          
                 {
					 $resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
					 if (time()-$last_time[1]['time']<=60*15 AND !$resp->is_valid)
					 {
						 $_SESSION['captcha_needed']='1';
						 echo '<script>window.location="upload.php";</script>';
						 die("Fill in CAPTCHA");
					 }
					
				 }
				 
				//check if ip is banned
				$ban_check = $db -> prepare('SELECT time FROM ban WHERE ip=? ORDER BY id DESC LIMIT 1');
				$ban_check -> execute(array($ip_string));
				$ban = $ban_check -> fetchAll(PDO::FETCH_ASSOC);
				if(isset($ban[0]['time']))
				{
					if((time()-$ban[0]['time']) < 60*60*24)
					{
					$_SESSION['ban']='1';
					 echo '<script>window.location="upload.php";</script>';
					 die("Your IP was temporarily banned");
						
						
						
					}
					
					
				}
				  
				 //file exists
				if(strlen($_FILES['file1']['type'])<2)
				{
					$_SESSION['exist']='1';
					 echo '<script>window.location="upload.php";</script>';
					 die("No file selected");
				 }
				 
				 
				//file type
				$file = getimagesize($_FILES['file1']['tmp_name']);
				if($file['mime']!= 'image/jpeg' AND $file['mime']!= 'image/png' AND $file['mime']!= 'image/gif')
				{
					$_SESSION['type']='1';
					echo '<script>window.location="upload.php";</script>';
					die("Only images allowed");
				}
				
				if(filesize($_FILES['file1']['tmp_name'])> $max_upload_size)
				{
					$_SESSION['size']='1';
					echo '<script>window.location="upload.php";</script>';
					die("File too large");
					
				}
				
				//all good?
				
				
				switch ($file['mime'])
				{
					
					case  'image/jpeg':
						$ext = 'jpg';
						break;
					case  'image/png':
						$ext = 'png';
						break;
					case  'image/gif':
						$ext = 'gif';
						break;
					default: die("invalid ext");
			
				}
				
				
				
					
				
				do{
				$new_name = mt_rand(1000000,900000000);
		
				$name_check = $db -> prepare('SELECT id FROM res WHERE name=?');
				$name_check -> execute(array($new_name));
				$name = $name_check -> fetchAll(PDO::FETCH_ASSOC);
				}
				while(isset($name[0]['id']));
				
				
				move_uploaded_file($_FILES['file1']['tmp_name'], 'res/'.$new_name.'.'.$ext);
				//ORDER BY id DESC
				$thumb = new Imagick();
				$thumb->readImage($install_path.'res/'.$new_name.'.'.$ext);    $thumb->scaleImage(200,0);
				$thumb->writeImage($install_path.'res/'.$new_name.'_thumb.'.$ext);
				$thumb->clear();
				$thumb->destroy(); 
				
				$add_image = $db -> prepare('INSERT INTO res (name,ext,ip,time) VALUES (?,?,?,?)');
				$add_image -> execute(array($new_name,$ext,$ip_string,time()));
				
				

				
				echo '<div style="text-align:center;display:block;margin-bottom:10px;">Share link</div><div style="text-align:center;"><textarea style="background-color:#000000;color:#FFFFFF;border-radius:0px;resize:none;height:16px;width:210px;text-align:center;margin-bottom:10px;" readonly >'.$domain.'/?i='.$new_name.'</textarea></div>';
				echo '<div style="font-size:18px;text-align:center;display:block;color:#0036f0;"><a href="http://'.$domain.'/?i='.$new_name.'" class="button1">View</a></div>';
			}
			
			
			?>
			
		</div>
		</div>
	
	
	</body>
	


</html>

