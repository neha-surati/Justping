<?php
	include "../db_connect.php";
	$obj = new DB_Connect();
    $ctid = $_REQUEST["ctid"];
    $city_name = $_REQUEST["city_name"];
    $state_id = $_REQUEST["state_id"];
    $stmt = $obj->con1->prepare("SELECT count(*) as tot FROM `city` WHERE soundex(ctnm)=soundex(?) AND state_id=? AND srno!=?");
    $stmt->bind_param("ssi", $city_name, $state_id, $ctid);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    echo $data["tot"];
    $stmt->close();
?>