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





/*  Create these tables in the database of your choice
    CREATE TABLE res(id INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(id),name VARCHAR(16),ext VARCHAR(4), ip VARCHAR(45), time INT);
	
	CREATE TABLE user(id INT NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(id),user VARCHAR(32), pass CHAR(65), auth_time INT, 
	auth_key VARCHAR(64), auth_ip VARCHAR(45));
	
	CREATE TABLE ban(id INT NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(id),ip VARCHAR(45), time INT);
*/





	$db_host = 'localhost';
	$db_name = 'image';
	$db_user = 'root';
	$db_pass = 'password';
	$db_port = '3306';
	$domain = 'example.com';
	$ip_string = $_SERVER['REMOTE_ADDR'];
	$max_upload_size = 3000000;
	$install_path = '/directory/exampledir/';
	$publickey = "YOUR PUBLIC KEY";     //For ReCAPTCHA
	$privatekey = "YOUR PRIVATE KEY"; 
	
	
	
	
	


?>
