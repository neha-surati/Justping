<?php
	include "../db_connect.php";
	$obj = new DB_Connect();
    $pincode=$_REQUEST["pincode"];
    $id=$_REQUEST["pid"];
    $stmt = $obj->con1->prepare("SELECT count(*) as tot FROM `area` WHERE pincode=? AND id!=?");
    $stmt->bind_param("si", $pincode,$id);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    echo $data["tot"];
    $stmt->close();
?>