<?php
//Created by Dev Jariwala
include "header.php";
if (isset($_COOKIE['edit_id'])) {
	$mode = 'edit';
	$editId = $_COOKIE['edit_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `blog` where blog_id=?");
	$stmt->bind_param('i', $editId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}

if (isset($_COOKIE['view_id'])) {
	$mode = 'view';
	$viewId = $_COOKIE['view_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `blog` where blog_id=?");
	$stmt->bind_param('i', $viewId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_REQUEST["btnsubmit"])) {
	$blog_title = $_REQUEST["blog_title"];
	$short_desc = $_REQUEST["short_desc"];
	$description = $_REQUEST["description"];
	$status = isset($_REQUEST["status"]) ? "enable" : "disable";
	$blog_img = $_FILES['blog_img']['name'];
	$blog_img = str_replace(' ', '_', $blog_img);
	$blog_img_path = $_FILES['blog_img']['tmp_name'];

	if ($blog_img != "") {
		if (file_exists("images/blog_image/" . $blog_img)) {
			$i = 0;
			$PicFileName = $blog_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/blog_image/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $blog_img;
		}
	}

	try {
		$stmt = $obj->con1->prepare("INSERT INTO `blog`(`title`, `short_description`, `description`, `image`, `b_status`) VALUES (?,?,?,?,?)");
		$stmt->bind_param("sssss", $blog_title, $short_desc, $description, $PicFileName, $status);
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
		move_uploaded_file($blog_img_path, "images/blog_image/" . $PicFileName);
		setcookie("msg", "data", time() + 3600, "/");
		header("location:blog.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:blog.php");
	}
}
if (isset($_REQUEST["btn_update"])) {
	$id = $_COOKIE['edit_id'];
	$blog_title = $_REQUEST["blog_title"];
	$short_desc = $_REQUEST["short_desc"];
	$description = $_REQUEST["description"];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'enable' : 'disable';
	$blog_img = $_FILES['blog_img']['name'];
	$blog_img = str_replace(' ', '_', $blog_img);
	$blog_img_path = $_FILES['blog_img']['tmp_name'];
	// $blog_id = $_REQUEST['blog_id'];
	$old_img = $_REQUEST['old_img'];

	if ($blog_img != "") {
		if (file_exists("images/blog_image/" . $blog_img)) {
			$i = 0;
			$PicFileName = $blog_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/blog_image/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $blog_img;
		}
		unlink("images/blog_image/" . $old_img);
		move_uploaded_file($blog_img_path, "images/blog_image/" . $PicFileName);
	} else {
		$PicFileName = $old_img;
	}

	try {
		$stmt = $obj->con1->prepare("UPDATE `blog` SET `title`=?,`short_description`=?,`description`=?,`image`=?,`b_status`=? WHERE `blog_id`=?");
		$stmt->bind_param("sssssi", $blog_title, $short_desc, $description, $PicFileName, $status, $id);
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
		header("location:blog.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:blog.php");
	}
}

if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
	$blog_subimg = $_REQUEST["blog_subimg"];
	try {
		$stmt_del = $obj->con1->prepare("DELETE FROM `blog_images` WHERE b_sub_id='" . $_REQUEST["sub_img_id"] . "'");
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
		if (file_exists("images/blog_image/" . $blog_subimg)) {
			unlink("images/blog_image/" . $blog_subimg);
		}
		setcookie("msg", "data_del", time() + 3600, "/");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
	}
	header("location:add_blog.php");
}
?>
<div class='p-6'>
	<div class="flex gap-6 items-center pb-8">
		<span class="cursor-pointer">
			<a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
				<i class="ri-arrow-left-line"></i>
			</a>
		</span>
		<h1 class="dark:text-white-dar text-2xl font-bold">Blog -
			<?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
		</h1>
	</div>
	<div class="panel mt-6">
		<div class="mb-5">
			<form class="space-y-5" method="post" enctype="multipart/form-data">
				<div>
					<label for="blog_title"> Blog Title</label>
					<input id="blog_title" name="blog_title" type="text" class="form-input" required
						value="<?php echo (isset($mode)) ? $data['title'] : '' ?>" placeholder="Enter Title" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
				</div>
				<div class="mb-4">
					<label for="quill1">Short Description</label>
					<div id="editor2">
						<?php echo (isset($mode)) ? $data['short_description'] : '' ?>
					</div>
				</div>
				<input type="hidden" id="quill-input2" name="short_desc">

				<div class="mb-4">
					<label for="quill2">Description</label>
					<div id="editor1">
						<?php echo (isset($mode)) ? $data['description'] : '' ?>
					</div>
				</div>
				<input type="hidden" id="quill-input1" name="description">

				<div class="mb-4">
					<label for="custom_switch_checkbox1">Status</label>
					<label class="w-12 h-6 relative">
						<input type="checkbox"
							class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
							name="status" <?php echo (isset($mode) && $data['b_status'] == 'enable') ? 'checked' : '' ?>
							<?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>><span
							class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
					</label>
				</div>

				<div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
					<label for="image">Image</label>
					<input id="blog_img" name="blog_img" class="demo1" type="file" data_btn_text="Browse"
						onchange="readURL(this,'PreviewImage')" onchange="readURL(this,'PreviewImage')"
						placeholder="drag and drop file here" />
				</div>
				<div>
					<h4 class="font-bold text-primary mt-2  mb-3"
						style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
					<img src="<?php echo (isset($mode)) ? 'images/blog_image/' . $data["image"] : '' ?>" name="PreviewImage"
						id="PreviewImage" width="400" height="400"
						style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>" class="object-cover shadow rounded">
					<div id="imgdiv" style="color:red"></div>
					<input type="hidden" name="old_img" id="old_img"
						value="<?php echo (isset($mode) && $mode == 'edit') ? $data["image"] : '' ?>" />
				</div>

				<div class="relative inline-flex align-middle gap-3 mt-4 ">
					<button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btnsubmit' ?>"
						id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>"
						onclick="return setQuillInput()">
						<?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
					</button>
					<button type="button" class="btn btn-danger"
						onclick="<?php echo (isset($mode)) ? 'javascript:go_back()' : 'window.location.reload()' ?>">Close</button>
				</div>
		</div>
		</form>
	</div>
</div>

<?php if (isset($mode)) { ?>
	<div class="animate__animated p-6" :class="[$store.app.animation]">
	<div x-data='pagination'>
		<h1 class="dark:text-white-dar text-2xl font-bold">Blog Images</h1>
		<div class="panel mt-6 flex items-center  justify-between relative">

			<div class="flex gap-6 items-center pb-8 <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>">
				<button type="button" name="btn_add_img" id="btn_add_img" class="p-2 btn btn-primary m-1 add-btn" onclick="location.href='add_blog_subimages.php'">
				<i class="ri-add-line mr-1"></i> Add New Blog Image</button>
			</div>

				<table id="myTable" class="table-hover whitespace-nowrap w-full"></table> 
		</div>
	</div>
</div>
<?php } ?>
<script type="text/javascript">
<?php if (isset($mode)) { ?>
	function getActions(id, blog_img) {
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
			<a href="javascript:showAlert(`+ id + `,\'` + blog_img + `\');" class='text-xl' x-tooltip="Delete">
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
							$stmt = $obj->con1->prepare("SELECT * FROM `blog_images` WHERE blog_id=? order by b_sub_id desc");
							$stmt->bind_param("i",$id);
							$stmt->execute();
							$Resp = $stmt->get_result();
							$i = 1;
							while ($row = mysqli_fetch_array($Resp)) { ?>
								[
								<?php echo $i; ?>,
								'<img src="images/blog_image/<?php echo addslashes($row["b_sub_img"]); ?>" height="200" width="200" class="object-cover shadow rounded">',
								getActions(<?php echo $row["b_sub_id"]; ?>, '<?php echo addslashes($row["b_sub_img"]); ?>')
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
		window.location = "blog.php";
	}

	function editdata(id) {
		createCookie("edit_subimg_id", id, 1);
		window.location = "add_blog_subimages.php";
	}

	function viewdata(id) {
		createCookie("view_subimg_id", id, 1);
		window.location = "add_blog_subimages.php";
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
				var loc = "add_blog.php?flg=del&sub_img_id=" + id + "&blog_subimg=" + img;
				window.location = loc;
			}
		});
	}

	var quill1 = new Quill('#editor1', {
		theme: 'snow',
	});
	var quill2 = new Quill('#editor2', {
		theme: 'snow',
	});
	var toolbar1 = quill1.container.previousSibling;
	toolbar1.querySelector('.ql-picker').setAttribute('title', 'Font Size');
	toolbar1.querySelector('button.ql-bold').setAttribute('title', 'Bold');
	toolbar1.querySelector('button.ql-italic').setAttribute('title', 'Italic');
	toolbar1.querySelector('button.ql-link').setAttribute('title', 'Link');
	toolbar1.querySelector('button.ql-underline').setAttribute('title', 'Underline');
	toolbar1.querySelector('button.ql-clean').setAttribute('title', 'Clear Formatting');
	toolbar1.querySelector('[value=ordered]').setAttribute('title', 'Ordered List');
	toolbar1.querySelector('[value=bullet]').setAttribute('title', 'Bullet List');

	var toolbar2 = quill2.container.previousSibling;
	toolbar2.querySelector('.ql-picker').setAttribute('title', 'Font Size');
	toolbar2.querySelector('button.ql-bold').setAttribute('title', 'Bold');
	toolbar2.querySelector('button.ql-italic').setAttribute('title', 'Italic');
	toolbar2.querySelector('button.ql-link').setAttribute('title', 'Link');
	toolbar2.querySelector('button.ql-underline').setAttribute('title', 'Underline');
	toolbar2.querySelector('button.ql-clean').setAttribute('title', 'Clear Formatting');
	toolbar2.querySelector('[value=ordered]').setAttribute('title', 'Ordered List');
	toolbar2.querySelector('[value=bullet]').setAttribute('title', 'Bullet List');

	function setQuillInput() {
		let quillInput1 = document.getElementById("quill-input1");
		quillInput1.value = quill1.root.innerHTML;

		let quillInput2 = document.getElementById("quill-input2");
		quillInput2.value = quill2.root.innerHTML;

		let val1 = quillInput1.value.replace(/<[^>]*>/g, '');
		let val2 = quillInput2.value.replace(/<[^>]*>/g, '');

		if (val1.trim() == '') {
			coloredToast("danger", 'Please add something in Description.');
			return false;
		}
		else if (val2.trim() == '') {
			coloredToast("danger", 'Please add something in Short Description.');
			return false;
		}
		<?php if(!isset($mode)){ ?>
         else if (<?php echo (!isset($mode))?true:false ?>) {
            return checkImage();
        } 
        <?php } ?> 
		else {
			return true;
		}
	}


	function readURL(input, preview) {
		if (input.files && input.files[0]) {
			var filename = input.files.item(0).name;

			var reader = new FileReader();
			var extn = filename.split(".");

			if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp") {
				reader.onload = function (e) {
					$('#' + preview).attr('src', e.target.result);
					document.getElementById(preview).style.display = "block";
				};

				reader.readAsDataURL(input.files[0]);
				$('#imgdiv').html("");
				document.getElementById('save').disabled = false;
			}
			else {
				$('#imgdiv').html("Please Select Image Only");
				document.getElementById('save').disabled = true;
			}
		}
	}
</script>
<?php
include "footer.php";
?>