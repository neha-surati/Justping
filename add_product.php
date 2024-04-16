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
		$stmt = $obj->con1->prepare("INSERT INTO `product`(`name`, `detail`, `v_id`,`image`, `stats`) VALUES (?,?,?,?,?)");
		$stmt->bind_param("ssiss", $name, $details, $v_id, $PicFileName, $status);
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
		$stmt = $obj->con1->prepare("UPDATE `product` SET `name`=?, `detail`=?, `v_id`=?, `image`=?,`status`=? WHERE `id`=?");
		$stmt->bind_param("ssissi", $name, $details, $v_id, $PicFileName, $status, $id);
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
						<input id="details" name="details" type="text" class="form-input"
							placeholder="Enter detail"
							value="<?php echo (isset($mode)) ? $data['details'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
					</div>
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

				<div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
					<label for="image">Image</label>
					<input id="product_img" name="product_img" class="demo1" type="file" data_btn_text="Browse"
						onchange="readURL(this,'PreviewImage')" accept="image/*, video/*"
						onchange="readURL(this,'PreviewImage')" placeholder="drag and drop file here" />
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
						<!-- <video src = "<?php echo (isset($mode) && !is_image($data["image"])) ? 'images/product_images/' . $data["image"] : '' ?>" name="PreviewVideo" id="PreviewVideo" width="400" height="400" style="display:<?php echo (isset($mode) && !is_image($data["image"])) ? 'block' : 'none' ?>" class="object-cover shadow rounded" controls></video> -->
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
		function readURL(input, preview) {
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
	</script>
	<?php
	include "footer.php";
	?>