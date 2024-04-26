<?php
include "header.php";

if (isset($_COOKIE["view_id"])) {
    $mode = 'view';
    $viewId = $_COOKIE["view_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `customer_reg`where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE["edit_id"])) {
    $mode = 'edit';
    $editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `customer_reg` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $firstname = $_REQUEST["name"];
    $lastname = $_REQUEST["lname"];
    $contact= $_REQUEST["contact"];
    $email = $_REQUEST["email"];
    $user_id= $_REQUEST["uid"];
    $password = $_REQUEST["password"];
    $status = isset($_REQUEST["status"])?'Enable':'Disable';
    $operation = "Added";
    $user_type="admin";
  

//  echo "INSERT INTO  `customer_reg`(`name`, `lastname`, `username`, `password`, `email`, `contact`,`status`,`operation`,`type`) VALUES ( '".$firstname."', '".$lastname."',  '".$user_id."', '".$password."' , '".$email."', '".$contact."',  '".$status."', '".$operation."', '".$user_type."')";
    
        try {
            $stmt = $obj->con1->prepare(
                "INSERT INTO `customer_reg`(`firstname`, `lastname`, `username`,`password`, `email`, `contact`,`status`,`operation`,`type`) VALUES (?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param("sssssssss", $firstname, $lastname,$user_id, $password , $email,  $contact,  $status, $operation, $user_type);
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
              header("location:customer_reg.php");
        } else {
            setcookie("msg", "fail", time() + 3600, "/");
             header("location:customer_reg.php");
        }
  
}

if (isset($_REQUEST["update"])) {
    $firstname = $_REQUEST["name"];
    $lastname = $_REQUEST["lname"];
    $contact= $_REQUEST["contact"];
    $email = $_REQUEST["email"];
    $user_id= $_REQUEST["uid"];
    $password = $_REQUEST["password"];
    $status = isset($_REQUEST["status"])?'Enable':'Disable';
    $operation = "update";
    $user_type="admin";
    $editId = $_COOKIE["edit_id"];

    try {
        $stmt = $obj->con1->prepare(
            "UPDATE   `customer_reg` SET `firstname`=?, `lastname`=?, `username`=?, `password`=?, `email`=?, `contact`=?, `status`=?,`operation`=?,`type`=? WHERE `id`=?"
        );
        // echo  "UPDATE  `vendor_reg` SET`name`= '".$firstname."', `lname`='".$lastname."', `username`=  '".$user_id."', `password`= '".$password."', `email`='".$email."', `business_name`= '".$business_name."', `city`='".$city."', `area`='".$area."', `address`='".$address."',`contact_person`= '".$contact_person."', `contact`= '".$contact."', `rating`='".$rating."',`stats`= '".$status."',`added_by`='".$id."',`operation`='".$operation."',`user_type`='".$user_type."',`isopen`='".$isopen."' WHERE `id`='".$editId."'";
        $stmt->bind_param("sssssssssi", $firstname, $lastname,$user_id, $password , $email,  $contact,  $status, $operation, $user_type,$editId);
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
        setcookie("msg", "update", time() + 3600, "/");
        header("location:customer_reg.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:customer_reg.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">New Notification -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                        <div class="col-md-6">
                            <label for="marital_s">To</label>
                            <div class="flex gap-10 items-center mt-3">
                                <div>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="to" id="all" class="form-radio" value="all"
                                            <?php echo (isset($mode) && $data['notification_type'] == "all") ? "checked" : "" ?> />
                                        <span class="text-black">All</span>
                                    </label>
                                </div>
                                <div>
                                    <label class=" flex items-center cursor-pointer">
                                        <input type="radio" name="to" id="specific_user" class="form-radio"
                                            value="specific_user"
                                            <?php echo (isset($mode) && $data['notification_type'] == "specific_user") ? "checked" : "" ?> />
                                        <span class="text-black">Specific User</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                        <div>
                            <label for="product">Product</label>
                            <select class="form-select" id="blood_g" name="blood_g" required>
                                <option value="" selected>Select Blood Group</option>
                                <option value="A+"
                                    <?php echo isset($data) && $data['blood_group'] == "A+" ? "selected" : "" ?>>A+
                                </option>
                                <option value="A-"
                                    <?php echo isset($data) && $data['blood_group'] == "A-" ? "selected" : "" ?>>A-
                                </option>
                                <option value="B+"
                                    <?php echo isset($data) && $data['blood_group'] == "B+" ? "selected" : "" ?>>B+
                                </option>
                                <option value="B-"
                                    <?php echo isset($data) && $data['blood_group'] == "B-" ? "selected" : "" ?>>B-
                                </option>
                                <option value="O+"
                                    <?php echo isset($data) && $data['blood_group'] == "O+" ? "selected" : "" ?>>O+
                                </option>
                                <option value="O-"
                                    <?php echo isset($data) && $data['blood_group'] == "O-" ? "selected" : "" ?>>O-
                                </option>
                                <option value="AB+"
                                    <?php echo isset($data) && $data['blood_group'] == "AB+" ? "selected" : "" ?>>AB+
                                </option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                            <div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
                                <label for="image">Image</label>
                                <input id="product_img" name="product_img" class="demo1" type="file"
                                    data_btn_text="Browse" onchange="readURL(this,'PreviewImage')"
                                    accept="image/*, video/*" onchange="readURL(this,'PreviewImage')"
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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                        <div>
                            <label for="address">Message</label>
                            <textarea autocomplete="on" name="address" id="address" class="form-textarea" rows="2"
                                value="" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>><?php echo isset($mode) ? $data['address'] : '' ?></textarea>
                        </div>
                        </div>
                        <div class="relative inline-flex align-middle gap-3 mt-4 ">
                            <button type="submit"
                                name="<?php echo isset($mode) && $mode == 'edit' ? 'Send Message' : 'Send Message' ?>" id="save"
                                class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>"
                                <?php echo isset($mode) ? '' : 'onclick="return checkImage()"' ?>>
                                <?php echo isset($mode) && $mode == 'edit' ? 'Send Message' : 'Send Message' ?>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="javascript:go_back()">Close</button>
                        </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
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