<?php
include "header.php";
if (isset($_COOKIE['edit_id'])) {
	$mode = 'edit';
	$editId = $_COOKIE['edit_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `banner` where srno=?");
	$stmt->bind_param('i', $editId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_COOKIE['view_id'])) {
	$mode = 'view';
	$viewId = $_COOKIE['view_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `banner` where srno=?");
	$stmt->bind_param('i', $viewId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_REQUEST["btnsubmit"])) {
	$banner_title = $_REQUEST["banner_title"];
	$status = isset($_REQUEST["status"]) ? "Enable" : "Disable";
	$banner_img = $_FILES['banner_img']['name'];
	$banner_img = str_replace(' ', '_', $banner_img);
	$banner_img_path = $_FILES['banner_img']['tmp_name'];

	if ($banner_img != "") {
		if (file_exists("images/banner_image/" . $banner_img)) {
			$i = 0;
			$PicFileName = $banner_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/banner_image/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $banner_img;
		}
	}
	try {
		$stmt = $obj->con1->prepare("INSERT INTO `banner`(`name`,`filename`, `status`) VALUES (?,?,?)");
		$stmt->bind_param("sss", $banner_title,  $PicFileName, $status);
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
		move_uploaded_file($banner_img_path, "images/banner_image/" . $PicFileName);
		setcookie("msg", "data", time() + 3600, "/");
		header("location:banner.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:banner.php");
	}
}
if (isset($_REQUEST["btn_update"])) {
	$id = $_COOKIE['edit_id'];
	$banner_title = $_REQUEST["banner_title"];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'Enable' : 'Disable';
	$banner_img = $_FILES['banner_img']['name'];
	$banner_img = str_replace(' ', '_', $banner_img);
	$banner_img_path = $_FILES['banner_img']['tmp_name'];
	//$banner_id = $_REQUEST['banner_id'];
	// $banner_id = $_COOKIE['edit_id'];
	$old_img = $_REQUEST['old_img'];
	if ($banner_img != "") {
		if (file_exists("images/banner_image/" . $banner_img)) {
			$i = 0;
			$PicFileName = $banner_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/banner_image/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $banner_img;
		}
		echo ($old_img);
		unlink("images/banner_image/" . $old_img);

		move_uploaded_file($banner_img_path, "images/banner_image/" . $PicFileName);
	} else {
		$PicFileName = $old_img;
	}
	//echo $PicFileName;
	try {
		$stmt = $obj->con1->prepare("UPDATE `banner` SET `name`=?,  `filename`=?,`status`=? WHERE `srno`=?");
		$stmt->bind_param("sssi", $banner_title,  $PicFileName, $status, $id);
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
		header("location:banner.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:banner.php");
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
		<h1 class="dark:text-white-dar text-2xl font-bold">Banner Image - <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?></h1>
	</div>
	<div class="panel mt-6">
		<div class="mb-5">
			<form class="space-y-5" method="post" enctype="multipart/form-data">
				<div>
					<label for="banner_title">Title</label>
					<input id="banner_title" name="banner_title" type="text" class="form-input" required value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" placeholder="Write Title" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
				</div>

				<div class="mb-4">
					<label for="custom_switch_checkbox1">Status</label>
					<label class="w-12 h-6 relative">
						<input type="checkbox" class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status" name="status" <?php echo (isset($mode) && $data['status'] == 'Enable') ? 'checked' : '' ?> <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>><span class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
					</label>
				</div>

				<div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
					<label for="image">Image</label>
					<input id="banner_img" name="banner_img" class="demo1" type="file" data_btn_text="Browse" onchange="readURL(this,'PreviewImage')" accept="image/*, video/*" onchange="readURL(this,'PreviewImage')" placeholder="drag and drop file here" />
				</div>
				<div>
					<h4 class="font-bold text-primary mt-2 mb-3" style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>" id="preview_lable">Preview</h4>
					<div id="mediaPreviewContainer" style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">
						<img src="<?php echo (isset($mode) && is_image($data["filename"])) ? 'images/banner_image/' . $data["filename"] : '' ?>" name="PreviewMedia" id="PreviewMedia" width="400" height="400" style="display:<?php echo (isset($mode) && is_image($data["filename"])) ? 'block' : 'none' ?>" class="object-cover shadow rounded">
						<!-- <video src = "<?php echo (isset($mode) && !is_image($data["filename"])) ? 'images/banner_image/' . $data["filename"] : '' ?>" name="PreviewVideo" id="PreviewVideo" width="400" height="400" style="display:<?php echo (isset($mode) && !is_image($data["filename"])) ? 'block' : 'none' ?>" class="object-cover shadow rounded" controls></video> -->
						<div id="imgdiv" style="color:red"></div>
						<input type="hidden" name="old_img" id="old_img" value="<?php echo (isset($mode) && $mode == 'edit') ? $data["filename"] : '' ?>" />
					</div>
				</div>
				<!-- <div class="relative inline-flex align-middle gap-3 mt-4 <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">

					<button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btnsubmit' ?>" id="save" class="btn btn-success" hidden>Save</button>
				</div>s -->
				<div class="relative inline-flex align-middle gap-3 mt-4 ">
					<button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btnsubmit' ?>" id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">
						<?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
					</button>
					<button type="button" class="btn btn-danger" onclick="location.href='banner.php'">Close</button>
				</div>
			</form>
		</div>
	</div>
	<script type="text/javascript">
		function go_back() {
			eraseCookie("edit_id");
			eraseCookie("view_id");
			window.location = "banner.php";
		}
		/*function readURL(input, preview) {
			if (input.files && input.files[0]) {
				var filename = input.files.item(0).name;

				var reader = new FileReader();
				var extn = filename.split(".");

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
			reader.onload = function(e) {
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
			reader.onload = function(e) {
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