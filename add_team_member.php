<?php 
// Created by Dev Jariwala
include "header.php";
if(isset($_COOKIE['edit_id'])) {
	$mode = 'edit';
	$editId = $_COOKIE['edit_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `team_members` where m_id=?");
	$stmt->bind_param('i', $editId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}

if(isset($_COOKIE['view_id'])) {
	$mode = 'view';
	$viewId = $_COOKIE['view_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `team_members` where m_id=?");
	$stmt->bind_param('i', $viewId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_REQUEST["btnsubmit"])) {
	$member_name = $_REQUEST["member_name"];
	$member_designation = $_REQUEST["member_designation"];
	$member_img = $_FILES['member_img']['name'];
	$member_img = str_replace(' ', '_', $member_img);
	$member_img_path = $_FILES['member_img']['tmp_name'];

	if ($member_img != "")
	{
		if(file_exists("images/member_image/" . $member_img)) {
			$i = 0;
			$PicFileName = $member_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/member_image/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} 
		else {
			$PicFileName = $member_img;
		}
	}

	try {
		$stmt = $obj->con1->prepare("INSERT INTO `team_members`(`m_name`, `m_designation`, `m_image`) VALUES (?,?,?)");
		$stmt->bind_param("sss", $member_name,$member_designation,$PicFileName);
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
		move_uploaded_file($member_img_path,"images/member_image/".$PicFileName);
		setcookie("msg", "data", time() + 3600, "/");
		header("location:team_member.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:team_member.php");
	}
}
if(isset($_REQUEST["btn_update"])){
	$id = $_COOKIE['edit_id'];
	$member_name = $_REQUEST["member_name"];
	$member_designation = $_REQUEST["member_designation"];
	$member_img = $_FILES['member_img']['name'];
	$member_img = str_replace(' ', '_', $member_img);
	$member_img_path = $_FILES['member_img']['tmp_name'];
	$old_img=$_REQUEST['old_img'];
	
	if ($member_img != "")
    {
      if(file_exists("images/member_image/" . $member_img)) {
        $i = 0;
        $PicFileName = $member_img;
        $Arr1 = explode('.', $PicFileName);

        $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
        while (file_exists("images/member_image/" . $PicFileName)) {
          $i++;
          $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
        }
      } 
      else {
        $PicFileName = $member_img;
      }
      unlink("images/member_image/".$old_img);  
      move_uploaded_file($member_img_path,"images/member_image/".$PicFileName);
    }
    else
    {
      $PicFileName=$old_img;
    }
		
	try {
		$stmt = $obj->con1->prepare("UPDATE `team_members` SET `m_name`=?,`m_designation`=?,`m_image`=? WHERE `m_id`=?");
		$stmt->bind_param("sssi", $member_name,$member_designation,$PicFileName,$id);
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
		header("location:team_member.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:team_member.php");
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
			<h1 class="dark:text-white-dar text-2xl font-bold">Team  Member - <?php echo (isset($mode)) ? (($mode=='view')?'View':'Edit') : 'Add' ?></h1>
		</div>
		<div class="panel mt-6">
			<div class="mb-5">
				<form class="space-y-5" method="post" enctype="multipart/form-data">
					<div>
						<label for="member_name">Name</label>
						<input id="member_name" name="member_name" type="text" class="form-input" required value="<?php echo (isset($mode))?$data['m_name']:'' ?>" placeholder="Enter Member Name"<?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?> />
					</div>
					<div>
						<label for="member_designation">Designation</label>
						<input id="member_designation" name="member_designation" type="text" class="form-input" required value="<?php echo (isset($mode))?$data['m_designation']:'' ?>" placeholder="Enter Member Designation"<?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?> />
					</div>  

					<div <?php echo (isset($mode) && $mode=='view')?'hidden':''?>>
						<label for="image">Image</label>
						<input id="member_img" name="member_img"class="demo1" type="file" data_btn_text="Browse" onchange="readURL(this,'PreviewImage')"onchange="readURL(this,'PreviewImage')" placeholder="drag and drop file here"/> 
					</div>
					<div>
						<h4 class="font-bold text-primary mt-2  mb-3" style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
						<img src="<?php echo (isset($mode))?'images/member_image/'.$data["m_image"]:'' ?>" name="PreviewImage" id="PreviewImage" width="400" height="400"
						style="display:<?php echo (isset($mode))?'block':'none' ?>" class="object-cover shadow rounded">
						<div id="imgdiv" style="color:red"></div>
						<input type="hidden" name="old_img" id="old_img" value="<?php echo (isset($mode) && $mode=='edit')?$data["m_image"]:'' ?>" />
					</div>

					<div class="relative inline-flex align-middle gap-3 mt-4 ">
						<button type="submit" name="<?php echo isset($mode) && $mode=='edit'? 'btn_update' : 'btnsubmit' ?>" id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : ''?>" <?php echo isset($mode) ? '' : 'onclick="return checkImage()"' ?>>
							<?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
						</button>
						<button type="button" class="btn btn-danger"
							onclick="<?php echo (isset($mode)) ? 'javascript:go_back()' : 'window.location.reload()' ?>">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	function go_back()
	{
		eraseCookie("edit_id");
		eraseCookie("view_id");
		window.location ="team_member.php";
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