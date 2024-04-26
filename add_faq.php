<?php
include "header.php";
error_reporting(E_ALL);

if (isset($_COOKIE['view_id'])) {
	$mode = 'view';
	$viewId = $_COOKIE['view_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `faq`where id=?");
	$stmt->bind_param('i', $viewId);
	$stmt->execute();
	$Resp = $stmt->get_result();
	$data = $Resp->fetch_assoc();
	$stmt->close();
}

if (isset($_COOKIE['edit_id'])) {
	$mode = 'edit';
	$editId = $_COOKIE['edit_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `faq` where id=?");
	$stmt->bind_param("i", $editId);
	$stmt->execute();
	$Res = $stmt->get_result();
	$data = $Res->fetch_assoc();
	$stmt->close();
}

if (isset($_REQUEST['update'])) {
	$detail = $_REQUEST["detail"];
	// $type = $_REQUEST["type"];
	$date_time = date("d-m-Y h:i A");
	$editId = $_COOKIE['edit_id'];

	$stmt = $obj->con1->prepare("UPDATE `faq` SET detail=?, date_time=? WHERE id=?");
	$stmt->bind_param("ssi", $detail, $date_time, $editId);
	$Res = $stmt->execute();
	$stmt->close();

	if ($Res) {
		setcookie("msg", "update", time() + 3600, "/");
		header("location:faq.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:faq.php");
	}
}

if (isset($_REQUEST["save"])) {
	$detail = $_REQUEST["detail"];
	$date_time = date("d-m-Y h:i A");
	try {
		$stmt = $obj->con1->prepare(
			"INSERT INTO `faq`(`detail`,`date_time`) VALUES (?,?)"
		);
		$stmt->bind_param("ss", $detail, $date_time);
		$Resp = $stmt->execute();
		$stmt->close();

		if (!$Resp) {
			throw new Exception(
				"Problem in adding! " . strtok($obj->con1->error, "(")
			);
		}
	} catch (\Exception $e) {
		setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
	}

	if ($Resp) {
		setcookie("msg", "data", time() + 3600, "/");
		header("location:faq.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:faq.php");
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
		<h1 class="dark:text-white-dar text-2xl font-bold">FAQ - <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
		</h1>
	</div>
	<div class="panel mt-6">
		<div class="mb-5">
			<form method="post" class="space-y-5" id="mainForm">
				<div>
					<label for="editor" class="mb-3 block">Detail</label>
					<div id="editor" name="detail" class="!mt-1">
						<?php echo isset($mode) ? $data['detail'] : '' ?>
					</div>
				</div>
				<div class="relative inline-flex align-middle gap-5 mt-4">
					<button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>"
						id="save" class="btn btn-success  <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>"
						onclick="return formSubmit('detail')"><?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?></button>
					<button type="button" class="btn btn-danger" onclick="return go_back()">Close</button>
				</div>
				<input type="hidden" name="detail" id="detail">
			</form>
		</div>
	</div>
</div>
</div>

<!-- script -->
<script src="assets/js/quill.js"></script>
<script>
	function go_back() {
        eraseCookie("view_id");
        eraseCookie("edit_id");
        var loc = "faq.php";
        window.location = loc;
    }


	document.addEventListener("alpine:init", () => {
		Alpine.data("form", () => ({
			tableData: {
				id: 1,
				name: 'John Doe',
				email: 'johndoe@yahoo.com',
				date: '10/08/2020',
				sale: 120,
				status: 'Complete',
				register: '5 min ago',
				progress: '40%',
				position: 'Developer',
				office: 'London'
			},
		}));
	});

	var quill = new Quill('#editor', {
		theme: 'snow'
	});
	var toolbar = quill.container.previousSibling;
	toolbar.querySelector('.ql-picker').setAttribute('title', 'Font Size');
	toolbar.querySelector('button.ql-bold').setAttribute('title', 'Bold');
	toolbar.querySelector('button.ql-italic').setAttribute('title', 'Italic');
	toolbar.querySelector('button.ql-link').setAttribute('title', 'Link');
	toolbar.querySelector('button.ql-underline').setAttribute('title', 'Underline');
	toolbar.querySelector('button.ql-clean').setAttribute('title', 'Clear Formatting');
	toolbar.querySelector('[value=ordered]').setAttribute('title', 'Ordered List');
	toolbar.querySelector('[value=bullet]').setAttribute('title', 'Bullet List');

	function formSubmit(ele) {
		let xyz = document.getElementById("detail");
		let editorContent = quill.root.innerHTML;
		 xyz.value = editorContent;
		let val = editorContent.replace(/<[^>]*>/g, '');

		if (val.trim() == '') {
			coloredToast("danger", 'Please add something in Detail.');
			return validateAndDisable();
		} else {
			return validateAndDisable();
		}
	}
</script>

<?php
include "footer.php";
?>