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
	$c_id = $_REQUEST["c_id"];
	$details = $_REQUEST["details"];
	$status = isset($_REQUEST["status"]) ? "Enable" : "Disable";
	$pstatus = isset($_REQUEST["pstatus"]) ? "Publish" : "Pending";
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
		// echo "INSERT INTO `product`(`name`,`detail`,`v_id`,`image`,`stats`,`main_price`,`discount_per`,`discount_price`,`operation`,) VALUES ('".$name."', '". $details."', '".$v_id."', '".$PicFileName."', '".$status."', '".$price."', '".$discount."', '".$finalPrice."', '".$operation."',)";
		$stmt = $obj->con1->prepare("INSERT INTO `product`(`name`, `detail`, `v_id`,`c_id`,`image`, `stats`,`publish_status`, `main_price`, `discount_per`, `discount_price`, `operation`) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
		$stmt->bind_param("ssiisssiiis", $name, $details, $v_id,$c_id, $PicFileName, $status,$pstatus, $price, $discount, $finalPrice, $operation);
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
	$c_id = $_REQUEST["c_id"];
	$name = $_REQUEST["name"];
	$details = $_REQUEST["details"];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'Enable' : 'Disable';
	$pstatus = (isset($_REQUEST["pstatus"]) && $_REQUEST["pstatus"] == 'on') ? 'Publish' : 'Pending';
	$price = $_REQUEST["price"];
	$discount = $_REQUEST["discount"];
	$finalPrice = $_REQUEST["finalPrice"];
	$operation = "Updated";
	$product_img = $_FILES['product_img']['name'];
	$product_img = str_replace(' ', '_', $product_img);
	$product_img_path = $_FILES['product_img']['tmp_name'];
	$old_img = $_REQUEST['old_img'];
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
		echo ($old_img);
		unlink("images/product_images/" . $old_img);

		move_uploaded_file($product_img_path, "images/product_images/" . $PicFileName);
	} else {
		$PicFileName = $old_img;
	}
	//echo $PicFileName;
	try {
	// echo "UPDATE `product` SET `name`= '".$name."', `detail`='".$details."', `v_id`= '".$v_id."', `c_id`= '".$c_id."', `image`='". $PicFileName."', `stats`= '". $status."', publish_status`='".$pstatus."', `main_price`='".$price."', `discount_per`='".$discount."',`discount_price`= '".$finalPrice."',`operation`='".$operation."' WHERE `id`='".$id."'";
		
		$stmt = $obj->con1->prepare("UPDATE `product` SET `name`=?, `detail`=?, `v_id`=?,`c_id`=?, `image`=?,`stats`=?,`publish_status`=?, `main_price`=?, `discount_per`=?, `discount_price`=?,`operation`=? WHERE `id`=?");
		$stmt->bind_param("ssiisssiiisi", $name, $details, $v_id, $c_id, $PicFileName, $status,$pstatus, $price, $discount, $finalPrice, $operation, $id);
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
function is_image($filename)
{
	$allowed_extensions = array('jpg', 'jpeg', 'png', 'bmp');
	$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	return in_array($extension, $allowed_extensions);
}
if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
	$product_subimg = $_REQUEST["product_subimg"];
	try {
		$stmt_del = $obj->con1->prepare("DELETE FROM `product_image` WHERE image_id='" . $_REQUEST["sub_img_id"] . "'");
		$Resp = $stmt_del->execute();
		if (!$Resp) {
			if (
				strtok($obj->con1->error, ":") == "Cannot delete or update a parent row"
			) {
				throw new Exception("Image is already in use!");
			}
		}
		$stmt_del->close();
	} catch (\Exception $e) {
		setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
	}

	if ($Resp) {
		if (file_exists("images/product_images/" . $product_subimg)) {
			unlink("images/product_images/" . $product_subimg);
		}
		setcookie("msg", "data_del", time() + 3600, "/");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
	}
	header("location:add_product.php");
}
?>
<div class='p-6'>
	<div class="flex gap-6 items-center pb-8">
		<span class="cursor-pointer">
			<a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
				<i class="ri-arrow-left-line"></i>
			</a>
		</span>
		<h1 class="dark:text-white-dar text-2xl font-bold">Product -
			<?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
		</h1>
	</div>
	<div class="panel mt-6">
		<div class="mb-5">
			<form class="space-y-5" method="post" enctype="multipart/form-data">
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
					<div>
						<label for="name">Name</label>
						<input id="name" name="name" type="text" class="form-input" required
							value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" placeholder="Enter name" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
					</div>
					<div>
                    <label for="details">Details</label>
                    <textarea autocomplete="on" name="details" id="details" class="form-textarea" rows="2"
                                value="" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>><?php echo isset($mode) ? $data['detail'] : '' ?></textarea>
                	</div>
				</div>
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
				<div>
					<label for="groupFname">Vendor Name</label>
					<select class="form-select text-black" name="v_id" id="v_id" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required>
						<option value="">Select Vendor</option>
						<?php
						$stmt = $obj->con1->prepare("SELECT * FROM `vendor_reg`");
						$stmt->execute();
						$Resp = $stmt->get_result();
						$stmt->close();

						while ($result = mysqli_fetch_array($Resp)) {
							?>
							<option value="<?php echo $result["id"]; ?>" <?php echo (isset($mode) && $data["v_id"] == $result["id"]) ? "selected" : ""; ?>>
								<?php echo $result["name"]; ?>
							</option>
							<?php
						}
						?>
					</select>
				</div>
				<div>
					<label for="groupFname">Category</label>
					<select class="form-select text-black" name="c_id" id="c_id" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required>
						<option value="">Select Category</option>
						<?php
						$stmt = $obj->con1->prepare("SELECT * FROM `product_category`");
						$stmt->execute();
						$Resp = $stmt->get_result();
						$stmt->close();

						while ($result = mysqli_fetch_array($Resp)) {
							?>
							<option value="<?php echo $result["id"]; ?>" <?php echo (isset($mode) && $data["c_id"] == $result["id"]) ? "selected" : ""; ?>>
								<?php echo $result["name"]; ?>
							</option>
							<?php
						}
						?>
					</select>
				</div>
				</div>
				<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-10">
					<div>
						<label for="price">Price</label>
						<input id="price" name="price" type="text" class="form-input" required   onkeyup="calculateFinalPrice();"
							value="<?php echo (isset($mode)) ? $data['main_price'] : '' ?>" placeholder="Enter price" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
					</div>
					<div>
						<label for="discount">Discount (%)</label>
						<input id="discount" name="discount" type="text" class="form-input"  
							placeholder="Enter discount percentage" onkeyup="calculateFinalPrice();"
							value="<?php echo (isset($mode)) ? $data['discount_per'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
					</div>
					<div>
						<label for="finalPrice">Final Price</label>
						<input id="finalPrice" name="finalPrice" type="text" class="form-input" required
							value="<?php echo (isset($mode)) ? $data['discount_price'] : '' ?>" placeholder="Final price" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
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
				<div class="mb-4">
                    <label for="custom_switch_checkbox1">Publish Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="pstatus"
                            name="pstatus" <?php echo (isset($mode) && $data['publish_status'] == 'Publish') ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'Disabled' : '' ?>><span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>
				<div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
					<label for="image">Image</label>
					<input id="product_img" name="product_img" class="demo1" type="file" data_btn_text="Browse"
						onchange="readURL(this,'PreviewImage')" accept="image/*, video/*" placeholder="drag and drop file here" />
				</div>
				<div>
					<h4 class="font-bold text-primary mt-2 mb-3"
						style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>" id="preview_lable">Preview
					</h4>
					<div id="mediaPreviewContainer" style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">
						<img src="<?php echo (isset($mode) && is_image($data["image"])) ? 'images/product_images/' . $data["image"] : '' ?>"
							name="PreviewMedia" id="PreviewMedia" width="400" height="400"
							style="display:<?php echo (isset($mode) && is_image($data["image"])) ? 'block' : 'none' ?>"
							class="object-cover shadow rounded">
						<video src = "<?php echo (isset($mode) && !is_image($data["image"])) ? 'images/product_images/' . $data["image"] : '' ?>" name="PreviewVideo" id="PreviewVideo" width="400" height="400" style="display:<?php echo (isset($mode) && !is_image($data["image"])) ? 'block' : 'none' ?>" class="object-cover shadow rounded" controls></video>
						<div id="imgdiv" style="color:red"></div>
						<input type="hidden" name="old_img" id="old_img"
							value="<?php echo (isset($mode) && $mode == 'edit') ? $data["image"] : '' ?>" />
					</div>
				</div>
				
				<!-- <div class="relative inline-flex align-middle gap-3 mt-4 <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">

					<button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btnsubmit' ?>" id="save" class="btn btn-success" hidden>Save</button>
				</div>s -->
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
			window.location = "product_details.php";
		}
	
		function readURL(input, preview) {
			console.log("yes");
			if (input.files && input.files[0]) {
				var filename = input.files.item(0).name;
				var extn = filename.split(".").pop().toLowerCase();

				if (["jpg", "jpeg", "png", "bmp"].includes(extn)) {
					// Handle image preview
					console.log("image");
					displayImagePreview(input, preview);
				} else if (["mp4", "webm", "ogg"].includes(extn)) {
					// Handle video preview
					console.log("video");
					displayVideoPreview(input, preview);
				} else {
					// Display error message for unsupported file types
					$('#imgdiv').html("Unsupported file type. Please select an image or video.");
					document.getElementById('mediaPreviewContainer').style.display = "none";
				}
			}
		}
		function displayImagePreview(input, preview) {
			console.log("image");
			var reader = new FileReader();
			reader.onload = function (e) {
				document.getElementById('mediaPreviewContainer').style.display = "block";
				$('#PreviewMedia').attr('src', e.target.result);
				document.getElementById('PreviewMedia').style.display = "block";
				document.getElementById('preview_lable').style.display = "block";
				document.getElementById('PreviewVideo').style.display = "none";
			};
			reader.readAsDataURL(input.files[0]);
			$('#imgdiv').html("");
			document.getElementById('save').disabled = false;
		}
		function displayVideoPreview(input, preview) {
			var reader = new FileReader();
			reader.onload = function (e) {
				let file = input.files.item(0);
				let blobURL = URL.createObjectURL(file);
				document.getElementById('mediaPreviewContainer').style.display = "block";
				$('#PreviewVideo').attr('src', blobURL);
				document.getElementById('PreviewVideo').style.display = "block";

				document.getElementById('preview_lable').style.display = "block";
				document.getElementById('PreviewMedia').style.display = "none";
			};
			reader.readAsDataURL(input.files[0]);
			$('#imgdiv').html("");
			document.getElementById('save').disabled = false;
		}

		function calculateFinalPrice() {
			if(document.getElementById("price").value!="" && document.getElementById("discount").value!=""){
				var price = parseFloat(document.getElementById("price").value);
				var discountPercentage = parseFloat(document.getElementById("discount").value);
				
				// Calculate the final amount after applying the discount
				var discountAmount = price * (discountPercentage / 100);
				var finalPrice = price - discountAmount;

				// Display the final amount in the third text field
				document.getElementById("finalPrice").value = finalPrice.toFixed(2);
			}
			else{ 
				document.getElementById("finalPrice").value =0;
			}
        }
	</script>

<?php if (isset($mode)) { ?>
	<div class="animate__animated p-6" :class="[$store.app.animation]">
	<div x-data='pagination'>
		<h1 class="dark:text-white-dar text-2xl font-bold">Product Images</h1>
		<div class="panel mt-6 flex items-center  justify-between relative">

			<div class="flex gap-6 items-center pb-8 <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>">
				<button type="button" name="btn_add_img" id="btn_add_img" class="p-2 btn btn-primary m-1 add-btn" onclick="location.href='add_product_subimages.php'">
				<i class="ri-add-line mr-1"></i> Add product Images</button>
			</div>

				<table id="myTable" class="table-hover whitespace-nowrap w-full"></table> 
		</div>
	</div>
</div>
<?php } ?>
<script type="text/javascript">
<?php if (isset($mode)) { ?>
	function getActions(id, product_img) {
		checkCookies();
		return `<ul class="flex items-center gap-4">
		<li>
			<a href="javascript:viewdata(`+ id + `);" class='text-xl' x-tooltip="View">
			<i class="ri-eye-line text-primary"></i>
			</a>
		</li>
		<?php if(isset($mode) && $mode == 'edit') { ?>
		<li>
			<a href="javascript:editdata(`+ id + `);" class='text-xl' x-tooltip="Edit">
			<i class="ri-pencil-line text text-success"></i>
			</a>
		</li>
		<li>
			<a href="javascript:showAlert(`+ id + `,\'` + product_img + `\');" class='text-xl' x-tooltip="Delete">
			<i class="ri-delete-bin-line text-danger"></i>
			</a>
		</li>
		<?php } ?>
		</ul>`
	}
	document.addEventListener('alpine:init', () => {
		Alpine.data('pagination', () => ({
			datatable: null,
			init() {
				this.datatable = new simpleDatatables.DataTable('#myTable', {
					data: {
						headings: ['Sr.No.', 'Image', 'Action'],
						data: [
						<?php
							$id = ($mode=='edit')?$editId:$viewId;
							$stmt = $obj->con1->prepare("SELECT * FROM `product_image` WHERE product_id=? order by image_id desc");
							$stmt->bind_param("i",$id);
							$stmt->execute();
							$Resp = $stmt->get_result();
							$i = 1;
							while ($row = mysqli_fetch_array($Resp)) { ?>
								[
								<?php echo $i; ?>,
								`<?php 
                                        $img_array= array("jpg", "jpeg", "png", "bmp");
                                        $vd_array=array("mp4", "webm", "ogg","mkv");
                                        $extn = strtolower(pathinfo($row["image"], PATHINFO_EXTENSION));
                                        if (in_array($extn,$img_array)) {
                                        ?>
                                            <img src="images/product_images/<?php echo addslashes($row["image"]);?>" width="200" height="200" style="display:<?php (in_array($extn, $img_array))?'block':'none' ?>" class="object-cover shadow rounded">
                                        <?php
                                             } if (in_array($extn,$vd_array )) {
                                        ?>
                                            <video src="images/product_images/<?php echo addslashes($row["image"]);?>" height="200" width="200" style="display:<?php (in_array($extn, $vd_array))?'block':'none' ?>" class="object-cover shadow rounded" controls></video>
                                        <?php } ?>`,
								getActions(<?php echo $row["image_id"]; ?>, '<?php echo addslashes($row["image"]); ?>')
								],
								<?php $i++;
							}
						?>
						],
					},
					perPage: 10,
					perPageSelect: [10, 20, 30, 50, 100],
					columns: [{
						select: 0,
						sort: 'asc',
					},],
					firstLast: true,
					firstText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M13 19L7 12L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M16.9998 19L10.9998 12L16.9998 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
					lastText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M11 19L17 12L11 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M6.99976 19L12.9998 12L6.99976 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
					prevText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M15 5L9 12L15 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
					nextText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
					labels: {
						perPage: '{select}',
					},
					layout: {
						top: '{search}',
						bottom: "<div class='flex items-center gap-4'>{info}{select}</div>{pager}",
					},
				});
			},

			printTable() {
				this.datatable.print();
			},

			formatDate(date) {
				if (date) {
					const dt = new Date(date);
					const month = dt.getMonth() + 1 < 10 ? '0' + (dt.getMonth() + 1) : dt.getMonth() +
						1;
					const day = dt.getDate() < 10 ? '0' + dt.getDate() : dt.getDate();
					return day + '/' + month + '/' + dt.getFullYear();
				}
				return '';
			},
		}));
	})
<?php } ?>
	function go_back() {
		eraseCookie("edit_id");
		eraseCookie("view_id");
		window.location = "product_details.php";
	}

	function editdata(id) {
		createCookie("edit_subimg_id", id, 1);
		window.location = "add_product_subimages.php";
	}

	function viewdata(id) {
		createCookie("view_subimg_id", id, 1);
		window.location = "add_product_subimages.php";
	}

	async function showAlert(id, img) {
		new window.Swal({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			showCancelButton: true,
			confirmButtonText: 'Delete',
			padding: '2em',
		}).then((result) => {
			if (result.isConfirmed) {
				var loc = "add_product.php?flg=del&sub_img_id=" + id + "&product_subimg=" + img;
				window.location = loc;
			}
		});
	}
</script>
<?php
include "footer.php";
?>