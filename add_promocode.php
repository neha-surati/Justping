<?php
include "header.php";
if (isset($_COOKIE['edit_id'])) {
	$mode = 'edit';
	$editId = $_COOKIE['edit_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `promocode` where promo_id=?");
	$stmt->bind_param('i', $editId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_COOKIE['view_id'])) {
	$mode = 'view';
	$viewId = $_COOKIE['view_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM  `promocode` where promo_id=?");
	$stmt->bind_param('i', $viewId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_REQUEST["btnsubmit"])) {
	$name = $_REQUEST["name"];
	$promocode= $_REQUEST["promocode"];
	$discount = $_REQUEST["discount"];
	$maxdiscount=$_REQUEST["max_discount"];
	$minamount = $_REQUEST["min_amount"];
	$startdate=$_REQUEST["start_date"];
	$enddate=$_REQUEST["end_date"];
	$info=$_REQUEST["info"];
	$status = isset($_REQUEST["status"]) ? "Enable" : "Disable";
		try {
		$stmt = $obj->con1->prepare("INSERT INTO `promocode`(`name`, `promocode`, `discount`,`max_discount`, `min_amount`, `start_date`,`end_date`,`info`,`status`) VALUES (?,?,?,?,?,?,?,?,?)");
		$stmt->bind_param("ssdddssss", $name, $promocode, $discount,$maxdiscount, $minamount,$startdate,$enddate, $info, $status);
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
        header("location:promocode.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:promocode.php");
    }
	
}
if (isset($_REQUEST["btn_update"])) {
	$name = $_REQUEST["name"];
	$promocode= $_REQUEST["promocode"];
	$discount = $_REQUEST["discount"];
	$maxdiscount=$_REQUEST["max_discount"];
	$minamount = $_REQUEST["min_amount"];
	$startdate=$_REQUEST["start_date"];
	$enddate=$_REQUEST["end_date"];
	$info=$_REQUEST["info"];
	$status = isset($_REQUEST["status"]) ? "Enable" : "Disable";
	$editId = $_COOKIE["edit_id"];
	
	try {
		$stmt = $obj->con1->prepare("UPDATE `promocode` SET `name`=?, `promocode`=?, `discount`=?, `max_discount`=?,`min_amount`=?, `start_date`=?, `end_date`=?, `info`=?,`status`=? WHERE `promo_id`=?");
		$stmt->bind_param("ssdddssssi", $name, $promocode, $discount,$maxdiscount, $minamount,$startdate,$enddate, $info, $status,$editId);
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
		header("location:promocode.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:promocode.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">Promocode -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel ">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="name">Name</label>
                        <input id="name" name="name" type="text" class="form-input" required
                            value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" placeholder="Enter name"
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="promocode">Promocode</label>
                        <input id="promocode" name="promocode" type="text" class="form-input"
                            placeholder="Enter promocode" value="<?php echo (isset($mode)) ? $data['promocode'] : '' ?>"
                            required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-10">
                    <div>
                        <label for="discount">Discount (%)</label>
                        <input id="discount" name="discount" type="text" class="form-input"
                            placeholder="Enter discount percentage"
                            value="<?php echo (isset($mode)) ? $data['discount'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="max_discount"> Maximum Discount (%)</label>
                        <input id="max_discount" name="max_discount" type="text" class="form-input" required
                            value="<?php echo (isset($mode)) ? $data['max_discount'] : '' ?>"
                            placeholder="Enter Maximun Discount"
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="min_amount"> Minimum Amount</label>
                        <input id="min_amount" name="min_amount" type="text" class="form-input"
                            placeholder="Enter Minimum Amount"
                            value="<?php echo (isset($mode)) ? $data['min_amount'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div x-data="form">
                        <label> Start Date </label>
                        <div class="relative">
                            <input id="basic" x-model="date1" class="form-input" name="start_date" required 
                            value="<?php echo (isset($mode)) ? $data['start_date'] : '' ?>"
                            <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />

                        </div>
                    </div>
                    <div x-data="form">
                        <label> End Date </label>
                        <div class="relative">
                            <input id="basic2" x-model="date2" class="form-input" name="end_date" required 
                            value="<?php echo (isset($mode)) ? $data['end_date'] : '' ?>"
                            <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                           
                        </div>
                    </div>
                </div>
                <div>
                    <label for="info">Information</label>
                    <input id="info" name="info" type="text" class="form-input" required
                        value="<?php echo (isset($mode)) ? $data['info'] : '' ?>" placeholder="Enter information"
                        <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>

                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            name="status" <?php echo (isset($mode) && $data['status'] == 'Enable') ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>><span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>

                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit"
                        name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btnsubmit' ?>" id="save"
                        class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <button type="button" class="btn btn-danger"
                        onclick="return go_back()">Close</button>
                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript">
    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "promocode.php";
    }

    </script>
    <script>
    document.addEventListener("alpine:init", () => {
        let todayDate = new Date();
        let formattedToday = todayDate.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        }).split('/').join('-')

        Alpine.data("form", () => ({
            date1: '<?php echo isset($mode) ? date("d-m-Y", strtotime($data['start_date'])) : date("d-m-Y") ?>',
            date2: '<?php echo isset($mode) ? date("d-m-Y", strtotime($data['end_date'])) : date("d-m-Y") ?>',
            init() {
                flatpickr(document.getElementById('basic'), {
                    dateFormat: 'd-m-Y',
                    minDate: formattedToday,
                    defaultDate: this.date1,
                    onChange: (selectedDates, dateStr, instance) => {
                        const endDatePicker = document.getElementById('basic2')._flatpickr;
                        endDatePicker.set('minDate', dateStr);
                    },
                });
                flatpickr(document.getElementById('basic2'), {
                    dateFormat: 'd-m-Y',
                    minDate: formattedToday,
                    defaultDate: this.date2,
                    onChange: (selectedDates, dateStr, instance) => {
                        const startDatePicker = document.getElementById('basic')._flatpickr;
                        startDatePicker.set('maxDate', dateStr);
                    },
                });
            }
        }));
    });
    </script>

    <?php
	include "footer.php";
	?>
</div>
