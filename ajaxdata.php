<?php
session_start();
date_default_timezone_set("Asia/Kolkata");
//error_reporting(E_ALL);
include("db_connect.php");
$obj=new DB_Connect();
if(isset($_REQUEST['action']))
{
	// add_project
	if($_REQUEST['action']=="updateSortedList")
	{	
		$html="";
		$sortedList = $_REQUEST['sortedList'];
		$sortedList_array=explode(",",$sortedList);

		for($j=0;$j<count($sortedList_array);$j++)
		{
			$priority = ($j+1);
			$stmt = $obj->con1->prepare("UPDATE `project_images` SET `priority`=? WHERE p_img_id=?");
			$stmt->bind_param("is", $priority, $sortedList_array[$j]);
			$Resp = $stmt->execute();
			$stmt->close();
		}	
		echo $Resp;
	}

	// add_project
	if($_REQUEST['action']=="updateDefaultImage")
	{	
		$html="";
		$subimg_id = $_REQUEST['subimg_id'];
		
		// get sub image name and project id
		$stmt_subimg = $obj->con1->prepare("SELECT * FROM `project_images` WHERE p_img_id=?");
        $stmt_subimg->bind_param("i",$subimg_id);
        $stmt_subimg->execute();
        $Resp_subimg = $stmt_subimg->get_result()->fetch_assoc();
        $stmt_subimg->close();

		$sub_img = $Resp_subimg['p_sub_img'];
		$project_id = $Resp_subimg['p_id'];

		// get main image name
		$stmt_mainimg = $obj->con1->prepare("SELECT * FROM `project` WHERE p_id=?");
        $stmt_mainimg->bind_param("i",$project_id);
        $stmt_mainimg->execute();
        $Resp_mainimg = $stmt_mainimg->get_result()->fetch_assoc();
        $stmt_mainimg->close();

		$main_img = $Resp_mainimg['p_image'];

		$stmt_updatesub = $obj->con1->prepare("UPDATE `project_images` SET `p_sub_img`=? WHERE p_img_id=?");
		$stmt_updatesub->bind_param("si", $main_img, $subimg_id);
		$Resp_updatesub = $stmt_updatesub->execute();
		$stmt_updatesub->close();

		$stmt_updatemain = $obj->con1->prepare("UPDATE `project` SET `p_image`=? WHERE p_id=?");
		$stmt_updatemain->bind_param("si", $sub_img, $project_id);
		$Resp_updatemain = $stmt_updatemain->execute();
		$stmt_updatemain->close();

		echo $Resp_updatemain;
	}

	// category
	if($_REQUEST['action']=="updateCategoryList")
	{	
		$html="";
		$sortedList = $_REQUEST['sortedList'];
		$sortedList_array=explode(",",$sortedList);

		for($j=0;$j<count($sortedList_array);$j++)
		{
			$priority = ($j+1);
			$stmt = $obj->con1->prepare("UPDATE `category` SET `c_priority`=? WHERE c_id=?");
			$stmt->bind_param("is", $priority, $sortedList_array[$j]);
			$Resp = $stmt->execute();
			$stmt->close();
		}	
		echo $Resp;
	}
}

?>