<?php
	include "db_connect.php";
	$obj = new DB_Connect();
	$s = $_REQUEST["sid"];
	$ctid = $_REQUEST['ctid'];
	$stmt = $obj->con1->prepare("select * from city WHERE status='enable' and state_id=?");
	$stmt->bind_param("i", $s);
	$stmt->execute();
	$result = $stmt->get_result();
?>

<option value="">Choose City</option>

<?php 
while ($row = mysqli_fetch_assoc($result)) { 
?>
	<option value="<?php echo $row["id"]; ?>" <?php echo isset($ctid) && $ctid == $row['id'] ? 'selected' : '' ?> >
		<?php echo $row["city_name"]; ?>
	</option>
<?php
}
?>