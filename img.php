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

if(!is_numeric($_GET['i'])){
	header('Location: ./');
	die();
}

$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name.';port='.$db_port.';charset=utf8',$db_user,$db_pass);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);	
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);	

$get_name = $db -> prepare('SELECT * FROM res WHERE name=?');
$get_name -> execute(array($_GET['i']));
$name = $get_name -> fetchAll(PDO::FETCH_ASSOC);


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
<div id="replace">
	<div id="header" style="text-align:center;padding:30px;">
		<a href="upload.php"><img src="logo.png" ></a>
	</div>
	<div id="wrapper">
		<div id="content" style="text-align:center;">
		<?php
		if(!isset($name[0]['id']))
		{
			echo '<div style="font-size:14px;color:#FF0000;">You are looking for an image that does not exist!</div>';
			
			
		}
		else
		{
			echo '<div><span style="color:#666666;display:block;">Click image for full size<br/><br/></span><a href="#" onClick=\'document.getElementById("replace").innerHTML="<img src=http://'.$domain.'/res/'.$name[0]['name'].'.'.$name[0]['ext'].'>"\'><img src="res/'.$name[0]['name'].'_thumb.'.$name[0]['ext'].'"></a></div>';
			echo '<div style="font-size:16px;padding:5px;margin:20px;"><textarea style="background-color:#000000;color:#FFFFFF;border-radius:0px;resize:none;height:16px;width:210px;text-align:center;" readonly>'.$domain.'/?i='.$name[0]['name'].'</textarea></div>';
		}
		
		?>
		
			
		</div>
	</div>
	</div>


	</body>
	


</html>

