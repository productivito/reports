<?php
	if($_POST)
	{
		$host = $_POST['dbhost'];
		$dbname = $_POST['dbname'];
		$username = $_POST['dbusername'];
		$password = $_POST['dbpassword'];
		//echo $username."  ".$dbname."</br>";
	
		$contents = file_get_contents("http://localhost/productivito/public/install/application.ini.template", FILE_USE_INCLUDE_PATH);
		$contents = str_replace("_DBHOST_", $host, $contents);
		$contents = str_replace("_DBNAME_", $dbname, $contents);
		$contents = str_replace("_DBUSERNAME_", $username, $contents);
		$contents = str_replace("_DBPASSWORD_", $password, $contents);
		
		$ourFileName = "application.ini";
		$ourFileHandle = fopen($ourFileName, 'w');
		
		fwrite($ourFileHandle,$contents);
		
		//file_put_contents("http://localhost/Productivito/public/install/application.ini", $contents);
	}
	

?>

<html>
	<head>
		<title> Installer </title>
		<style>
			#installer
			{
				margin-top:100px;
			}
		</style>
	</head>
	
	<body>
		<h1 style="text-align:center">Productivito Installer</h1>
		<form id="installer" action="/Productivito/public/install/install.php" method="post">
			<table>
				<tr>
					<td>Host :</td>
					<td><input type="text" name="dbhost"/></td>
				</tr>
				<tr>
					<td>Database Name: </td>
					<td><input type="text" name="dbname"/></td>
				</tr>
				<tr>
					<td>Username :</td>
					<td><input type="text" name="dbusername"/></td>
				</tr>
				<tr>
					<td>Password :</td>
					<td><input type="password" name="dbpassword"/></td>
				</tr>		
				<tr>
					<td><input type="submit" ></td>
				</tr>											
			</table>
			
		</form>
	</body>
	
</html>
