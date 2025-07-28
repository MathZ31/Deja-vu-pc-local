<?php 
try {
	//ouverture connection
	$user = "root";		// ici $user="root"  
	$pass  = "";		// $password=""
	$dbh = new PDO('mysql:host=localhost;dbname=mathieu-list', $user, $pass);
}
catch (PDOException $e) {
	print "Error!: " . $e->getMessage() . "<br/>";
	die();
}