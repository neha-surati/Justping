<?php
include "header.php";
if (isset($_COOKIE['edit_id'])) {
	$mode = 'edit';
	$editId = $_COOKIE['edit_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `product` where id=?");
	$stmt->bind_param('i', $editId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_COOKIE['view_id'])) {
	$mode = 'view';
	$viewId = $_COOKIE['view_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `product` where id=?");
	$stmt->bind_param('i', $viewId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_REQUEST["btnsubmit"])) {
	$name = $_REQUEST["name"];
	$v_id = $_REQUEST["v_id"];
	$details = $_REQUEST["details"];
	$status = isset($_REQUEST["status"]) ? "Enable" : "Disable";
	$price = $_REQUEST["price"];
	$discount = $_REQUEST["discount"];
	$finalPrice = $_REQUEST["finalPrice"];
	$operation = "Added";
	$product_img = $_FILES['product_img']['name'];
	$product_img = str_replace(' ', '_', $product_img);
	$product_img_path = $_FILES['product_img']['tmp_name'];

	if ($product_img != "") {
		if (file_exists("images/product_images/" . $product_img)) {
			$i = 0;
			$PicFileName = $product_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/product_images/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $product_img;
		}
	}
	try {
		$stmt = $obj->con1->prepare("INSERT INTO `product`(`name`, `detail`, `v_id`,`image`, `stats`, `main_price`, `discount_per`, `discount_price`, `operation`) VALUES (?,?,?,?,?,?,?,?,?)");
		$stmt->bind_param("ssissiiis", $name, $details, $v_id, $PicFileName, $status, $price, $discount, $finalPrice, $operation);
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
		move_uploaded_file($product_img_path, "images/product_images/" . $PicFileName);
		setcookie("msg", "data", time() + 3600, "/");
		header("location:product_details.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:product_details.php");
	}
}
if (isset($_REQUEST["btn_update"])) {
	$id = $_COOKIE['edit_id'];
	$v_id = $_REQUEST["v_id"];
	$name = $_REQUEST["name"];
	$details = $_REQUEST["details"];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'Enable' : 'Disable';
	$price = $_REQUEST["price"];
	$discount = $_REQUEST["discount"];
	$finalPrice = $_REQUEST["finalPrice"];
	;
	try {
		$stmt = $obj->con1->prepare("UPDATE `product` SET `name`=?, `detail`=?, `v_id`=?, `image`=?,`stats`=?, `main_price`=?, `discount_per`=?, `discount_price`=?,`operation`=? WHERE `id`=?");
		$stmt->bind_param("ssissiiisi", $name, $details, $v_id, $PicFileName, $status, $price, $discount, $finalPrice, $operation, $id);
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
		header("location:product_details.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:product_details.php");
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
                        <label for="Promocode">Promocode</label>
                        <input id="Promocode" name="Promocode" type="text" class="form-input" placeholder="Enter promocode"
                            value="<?php echo (isset($mode)) ? $data['promocode'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-10">
                    <div>
                        <label for="price">Price</label>
                        <input id="price" name="price" type="text" class="form-input" required
                            value="<?php echo (isset($mode)) ? $data['main_price'] : '' ?>" placeholder="Enter price"
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="discount">Discount (%)</label>
                        <input id="discount" name="discount" type="text" class="form-input"
                            placeholder="Enter discount percentage" onchange="calculateFinalPrice();"
                            value="<?php echo (isset($mode)) ? $data['discount_per'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="finalPrice">Final Price</label>
                        <input id="finalPrice" name="finalPrice" type="text" class="form-input" required
                            value="<?php echo (isset($mode)) ? $data['discount_price'] : '' ?>"
                            placeholder="Final price" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>

                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            name="status" <?php echo (isset($mode) && $data['stats'] == 'Enable') ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>><span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>

                <div>

                </div>

                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit"
                        name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btnsubmit' ?>" id="save"
                        class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <button type="button" class="btn btn-danger"
                        onclick="location.href='product_details.php'">Close</button>
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
    /*function readURL(input, preview) {
    	if (input.files && input.files[0]) {
    		var image = input.files.item(0).name;

    		var reader = new FileReader();
    		var extn = image.split(".");

    		if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp" ) {
    			reader.onload = function (e) {
    				$('#' + preview).attr('src', e.target.result);
    				document.getElementById(preview).style.display = "block";
    			}

    			reader.readAsDataURL(input.files[0]);
    			$('#imgdiv').html("");
    			document.getElementById('save').disabled = false;
    		}
    		else if(extn[1].toLowerCase() == "mp4" || extn[1].toLowerCase() == "mkv" || extn[1].toLowerCase() == "mov"|| extn[1].toLowerCase() == "webm")
    		{

    			reader.onload = function (e) {
    				//console.log(e.target.result);
    				$('#PreviewVideo').attr('src', e.target.result);
    				document.getElementById('PreviewVideo').style.display = "block";
    			}

    			reader.readAsDataURL(input.files[0]);
    			$('#imgdiv').html("");
    			document.getElementById('save').disabled = false;

    		}
    		else {
    			$('#imgdiv').html("Please Select Image Only");
    			document.getElementById('save').disabled = true;
    		}
    	}
    }*/
  

   
    </script>
    <?php
	include "footer.php";
	?>