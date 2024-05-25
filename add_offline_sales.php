<?php
include "header.php";
if (isset($_COOKIE['edit_id'])) {
	$mode = 'edit';
	$editId = $_COOKIE['edit_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `offline_sales` where sr_no=?");
	$stmt->bind_param('i', $editId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_COOKIE['view_id'])) {
	$mode = 'view';
	$viewId = $_COOKIE['view_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `offline_sales` where sr_no=?");
	$stmt->bind_param('i', $viewId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_REQUEST["btnsubmit"])) {
	$customer_name = $_REQUEST["customer_name"];
    $agent_name=$_REQUEST["agent_name"];
	$commission_amount = $_REQUEST["commission_amount"];
    $product = $_REQUEST["product"];
	$date =  date("Y-m-d", strtotime($_REQUEST['date']));
    $detail = $_REQUEST["detail"];
	$product_service_provided= $_REQUEST["pservice"];
	$money_charged = $_REQUEST["money_charged"];
    $commission_paid=$_REQUEST["default_radio"];
    $cost_to_company=$_REQUEST["cost_to_company"];

    // echo "INSERT INTO `offline_sales`(`customer_name`,`agent_name`, `commission_amount`, `product`,`date`, `detail`, `product_service_provided`, `money_charged`, `commission_paid`,`cost_to_company`) VALUES('".$customer_name."',  '".$agent_name."' , '".$commission_amount."',  '".$product."', '".$date."',  '". $detail."',  '".$product_service_provided."',  '".$money_charged."' ,  '". $commission_paid."', '".$cost_to_company."')";
	try {
		$stmt = $obj->con1->prepare("INSERT INTO `offline_sales`(`customer_name`,`agent_name`, `commission_amount`, `product`,`date`, `detail`, `product_service_provided`, `money_charged`, `commission_paid`,`cost_to_company`) VALUES (?,?,?,?,?,?,?,?,?,?)");
		$stmt->bind_param("ssdssssdsd",$customer_name,$agent_name,$commission_amount,  $product,$date, $detail , $product_service_provided,$money_charged,  $commission_paid,$cost_to_company);
		$Resp = $stmt->execute();
		if (!$Resp) {
			throw new Exception(
				"Problem in adding! " . strtok($obj->con1->error, "(")
			);
		}
		$stmt->close();
	} catch (\Exception $e) {
		setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
	}
	if ($Resp) {
		setcookie("msg", "data", time() + 3600, "/");
		header("location:offline_sales.php");
	 } else {
		setcookie("msg", "fail", time() + 3600, "/");
		 header("location:offline_sales.php");
	}
}
if (isset($_REQUEST["btn_update"])) {
	$customer_name = $_REQUEST["customer_name"];
    $agent_name=$_REQUEST["agent_name"];
	$commission_amount = $_REQUEST["commission_amount"];
    $product = $_REQUEST["product"];
	$date =  date("Y-m-d", strtotime($_REQUEST['date']));
    $detail = $_REQUEST["detail"];
	$product_service_provided= $_REQUEST["pservice"];
	$money_charged = $_REQUEST["money_charged"];
    $commission_paid=$_REQUEST["default_radio"];
    $cost_to_company=$_REQUEST["cost_to_company"];
    $editId = $_COOKIE["edit_id"];

	try {
		$stmt = $obj->con1->prepare("UPDATE  `offline_sales` SET`customer_name`=?,`agent_name`=?, `commission_amount`=?, `product`=?,`date`=?, `detail`=?, `product_service_provided`=?, `money_charged`=?, `commission_paid`=?,`cost_to_company`=? WHERE `sr_no`=?");
        $stmt->bind_param("ssdssssdsdi",$customer_name,$agent_name,$commission_amount,  $product,$date,  $detail , $product_service_provided,$money_charged,  $commission_paid,$cost_to_company,$editId);
		$Resp = $stmt->execute();
		if (!$Resp) {
			throw new Exception(
				"Problem in updating! " . strtok($obj->con1->error, "(")
			);
		}
		$stmt->close();
	} catch (\Exception $e) {
		setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
	}

	if ($Resp) {
		setcookie("edit_id", "", time() - 3600, "/");
		setcookie("msg", "update", time() + 3600, "/");
		header("location:offline_sales.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:offline_sales.php");
	}
}
?>
<div class='p-6'>
    <div class="flex gap-6 items-center pb-8">
        <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span>
        <h1 class="dark:text-white-dar text-2xl font-bold">Offline Sales
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel ">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="customer_name">Customer Name</label>
                        <input id="customer_name" name="customer_name" type="text" class="form-input" required
                            value="<?php echo (isset($mode)) ? $data['customer_name'] : '' ?>" placeholder="Enter name"
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="agent_name">Agent Name</label>
                        <input id="agent_name" name="agent_name" type="text" class="form-input"
                            placeholder="Enter Agent name"
                            value="<?php echo (isset($mode)) ? $data['agent_name'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="commission_amount">Commission Amount</label>
                        <input id="commission_amount" name="commission_amount" type="text" class="form-input"
                            placeholder="Enter commission amount" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 13"
                            value="<?php echo (isset($mode)) ? $data['commission_amount'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="product">Product</label>
                        <input id="product" name="product" type="text" class="form-input" placeholder="Enter product"
                            value="<?php echo (isset($mode)) ? $data['product'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />

                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div x-data="form">
                        <label> Date </label>
                        <input id="basic" x-model="date1" class="form-input" name="date" required
                        value="" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                    </div>
                    <div>
                        <label for="detail">Detail</label>
                        <input id="detail" name="detail" type="text" class="form-input" placeholder="Enter detail"
                            value="<?php echo (isset($mode)) ? $data['detail'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />

                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label forpservice="">Product Service Provided</label>
                        <input id="pservicee" name="pservice" type="text" class="form-input" required
                            value="<?php echo (isset($mode)) ? $data['product_service_provided'] : '' ?>"
                            placeholder="Enter product service provided"
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>

                    <div>
                        <label for="money_charged">Money Charged</label>
                        <input id="money_charged" name="money_charged" type="text" class="form-input" required
                            value="<?php echo (isset($mode)) ? $data['money_charged'] : '' ?>"
                            placeholder="money charged" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 13"
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="cost_to_company">Cost To Company</label>
                        <input id="cost_to_company" name="cost_to_company" type="text" class="form-input" required
                            value="<?php echo (isset($mode)) ? $data['cost_to_company'] : '' ?>"
                            placeholder="cost to company" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 13"
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="commission_paid">Commission Paid</label>
                        <label class="inline-flex mr-3">
                            <input type="radio" name="default_radio" value="yes" class="form-radio" checked required
                                <?php echo isset($mode) && $data["commission_paid"] == "enable" ? "checked" : ""; ?>
                                <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                            <span>Yes</span>
                        </label>
                        <label class="inline-flex mr-3">
                            <input type="radio" name="default_radio" value="no" class="form-radio text-danger"
                                required
                                <?php echo isset($mode) && $data["commission_paid"] == "disable" ? "checked" : ""; ?>
                                <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                            <span>No</span>
                        </label>
                    </div>


                </div>
                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit"
                        name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btnsubmit' ?>" id="save"
                        class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <button type="button" class="btn btn-danger"
                        onclick="location.href='offline_sales.php'">Close</button>
                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript">
    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "offline_sales.php";
    }
    </script>
    <script type="text/javascript">
document.addEventListener("alpine:init", () => {
    Alpine.data("form", () => ({
        date1: '<?php echo isset($mode) && isset($data['date']) ? date("d-m-Y", strtotime($data['date'])) : date("d-m-Y") ?>',
        init() {
            flatpickr(document.getElementById('basic'), {
                dateFormat: 'd-m-Y',
                defaultDate: this.date1,
                minDate: 'today',
            });
        }
    }));
});


    </script>
    <?php
	include "footer.php";
	?>