<?php
	include "../db_connect.php";
	$obj = new DB_Connect();
    $name = $_REQUEST["name"];
    $id=$_REQUEST["pid"];
    $stmt = $obj->con1->prepare("SELECT count(*) as tot FROM `product_category` WHERE name=? AND id!=?");
    $stmt->bind_param("si", $name,$id);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    echo $data["tot"];
    $stmt->close();
?>