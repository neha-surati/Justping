<?php

	session_start();
	session_destroy();
	setcookie("logout", "success",time()+3600,"/");
	header("location:index.php");

?>