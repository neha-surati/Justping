<?php
	include "db_connect.php";
	$obj = new DB_Connect();
	$city_id = $_REQUEST["city_id"];
    $area_id = $_REQUEST["area_id"];
	$stmt = $obj->con1->prepare("select * from area WHERE stats='Enable' and city=?");
	$stmt->bind_param("i", $city_id);
	$stmt->execute();
	$result = $stmt->get_result();
?>

<option value="">Choose Area</option>

<?php 
while ($row = mysqli_fetch_assoc($result)) { 
?>
	<option value="<?php echo $row["srno"]; ?>" <?php echo $area_id!=0 && $area_id == $row['srno'] ? 'selected' : '' ?> >
		<?php echo $row["area_name"]; ?>
	</option>
<?php
}
?>