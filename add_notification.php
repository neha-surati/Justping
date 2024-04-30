<?php
include "header.php";

if (isset($_REQUEST["save"])) {
    $to = $_REQUEST["to"];
    
    $product= $_REQUEST["product"];
    $message = $_REQUEST["message"];
	$product_img = $_FILES['product_img']['name'];
	$product_img = str_replace(' ', '_', $product_img);
	$product_img_path = $_FILES['product_img']['tmp_name'];

    if($to=="specific_user"){
        $username = $_REQUEST["name"];
        $user_ids= implode(",",$username);
    }

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
    // echo "INSERT INTO `notification_center`(`notification_type`, `user_ids`, `item_id`,`image`, `msg`) VALUES ('".$to."', '". $username."', '".$product."', '". $PicFileName."', '".$message."')";
	try {
        if($to=="specific_user"){
            $stmt = $obj->con1->prepare("INSERT INTO `notification_center`(`notification_type`, `user_ids`, `item_id`,`image`, `msg`) VALUES (?,?,?,?,?)");
		    $stmt->bind_param("ssiss", $to, $user_ids, $product, $PicFileName, $message);
        }
        else if($to=="all"){
            $stmt = $obj->con1->prepare("INSERT INTO `notification_center`(`notification_type`, `item_id`,`image`, `msg`) VALUES (?,?,?,?)");
		    $stmt->bind_param("siss", $to, $product, $PicFileName, $message);
        }
		
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
		header("location:notification.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:notification.php");
	}
}

?>

<div class='p-6'>
    <div class='flex items-center mb-3 gap-6'>
        <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span>
        <h1 class="dark:text-white-dar text-2xl font-bold"> Notification -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div class="col-md-6">
                        <label for="marital_s">To</label>
                        <div class="flex gap-10 items-center mt-3">
                            <div>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="to" id="all" class="form-radio" value="all"
                                        onclick="getform()"
                                        <?php echo (isset($mode) && $data['notification_type'] == "all") ? "checked" : "" ?>checked
                                        required />
                                    <span class="text-black">All</span>
                                </label>
                            </div>


                            <div>
                                <label class=" flex items-center cursor-pointer">
                                    <input type="radio" name="to" id="specific_user" class="form-radio"
                                        value="specific_user" onclick="getform()"
                                        <?php echo (isset($mode) && $data['notification_type'] == "specific_user") ? "checked" : "" ?>
                                        required />
                                    <span class="text-black">Specific User</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="username_div" hidden>
                    <label for="name">User Name</label>
                    <select class="selectize" multiple='multiple' id="name" name="name[]">
                        <option value="">Choose</option>
                        <?php
                        if(isset($mode)){
                            $user_id=$data['user_ids'];
                            $user_ids_array = explode(",",$user_id);
                        }
                            $stmt = $obj->con1->prepare("SELECT CONCAT(firstname, ' ', lastname) AS full_name, customer_reg.*
                            FROM customer_reg;");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) { 
                        ?>
                        <option value="<?php echo $result["id"]; ?>"
                            <?php echo isset($mode) && in_array( $result["id"],$user_ids_array) ? "selected" : ""; ?>>
                            <?php echo $result["full_name"]; ?>
                        </option>
                        <?php 
                            }
                        ?>
                    </select>
                </div>

                <div id=allform>
                    <div class="mb-4">
                        <div>
                            <label for="product">Product</label>
                            <select class="form-select text-gray-500" name="product" id="product" required>
                                <option value="">Choose</option>
                                <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `product_category` ");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) { 
                        ?>
                                <option value="<?php echo $result["id"]; ?>"
                                    <?php echo isset($mode) && $data["item_id"] == $result["id"] ? "selected" : ""; ?>>
                                    <?php echo $result["name"]; ?>
                                </option>
                                <?php 
                            }
                        ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div>
                            <label for="image">Image</label>
                            <input id="product_img" name="product_img" class="demo1" type="file" data_btn_text="Browse"
                                onchange="readURL(this,'PreviewImage')" accept="image/*, video/*"
                                placeholder="drag and drop file here" />
                        </div>
                        <div>
                            <h4 class="font-bold text-primary mt-2 mb-3"
                                style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>" id="preview_lable">
                                Preview
                            </h4>
                            <div id="mediaPreviewContainer"
                                style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">
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
                    </div>

                    <div>
                        <label for="message">Message</label>
                        <textarea autocomplete="off" name="message" id="message" class="form-textarea" rows="2" value=""
                            required><?php echo isset($mode) ? $data['msg'] : '' ?></textarea>
                    </div>

                    <div class="relative inline-flex align-middle gap-3 mt-4 ">
                        <button type="submit" name="save" id="save"
                            class="btn btn-success"
                            <?php echo isset($mode) ? '' : 'onclick="return checkImage()"' ?>>
                            <?php echo isset($mode) && $mode == 'edit' ? 'Send Message' : 'Send Message' ?>
                        </button>
                        <button type="button" class="btn btn-danger" onclick="javascript:go_back()">Close</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function readURL(input, preview) {
    if (input.files && input.files[0]) {
        var filename = input.files.item(0).name;
        var extn = filename.split(".").pop().toLowerCase();

        if (["jpg", "jpeg", "png", "bmp"].includes(extn)) {
            // Handle image preview
            console.log("image");
            displayImagePreview(input, preview);
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

function getform() {

    if (document.getElementById('all').checked) {

        document.getElementById('username_div').setAttribute("hidden", true);
        document.getElementById('name').removeAttribute("required");
    } else if (document.getElementById('specific_user').checked) {

        document.getElementById('username_div').removeAttribute("hidden");
        document.getElementById('name').setAttribute("required", true);
    }
}

document.addEventListener("DOMContentLoaded", function(e) {
    // default
    var els = document.querySelectorAll(".selectize");
    els.forEach(function(select) {
        NiceSelect.bind(select);
    });
});


// $(document).ready(function() {
//     eraseCookie("edit_id");
//     eraseCookie("view_id");
// });
// checkCookies();

function go_back() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "notification.php";
}

function fillCity(stid) {
    const xhttp = new XMLHttpRequest();
    xhttp.open("GET", "getcities.php?sid=" + stid);
    xhttp.send();
    xhttp.onload = function() {
        document.getElementById("city").innerHTML = xhttp.responseText;
    }
}
</script>
<!-- <?php
        if (isset($mode) && $mode == 'edit') {
            echo "
            <script>
                const stid = document.getElementById('stateID').value;
                const ctid =" . json_encode($data['city_id']) . ";
                loadCities(stid, ctid);
            </script>
        ";
        }
        ?> -->

<?php
include "footer.php";
?>