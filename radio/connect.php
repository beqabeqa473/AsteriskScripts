<?php
	$con=mysqli_connect('host', 'dbuser', 'dbpass', 'dbname') or die('error : '.mysql_error());
	mysqli_set_charset($con, "utf8");
?>