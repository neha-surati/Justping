<?php
include "header.php";

if (isset($_COOKIE["view_id"])) {
    $mode = 'view';
    $viewId = $_COOKIE["view_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `vendor_reg`where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE["edit_id"])) {
    $mode = 'edit';
    echo "edit id = ".$editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `vendor_reg` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $firstname = $_REQUEST["name"];
    $lastname = $_REQUEST["lname"];
    $user_id= $_REQUEST["uid"];
    $password = $_REQUEST["password"];
    $email = $_REQUEST["email"];
    $business_name = $_REQUEST["business_name"];
    $city= $_REQUEST["city"];
    $area = $_REQUEST["area"];
    $address = $_REQUEST["address"];
    $contact_person = $_REQUEST["contact_person"];
    $contact= $_REQUEST["contact"];
    $rating="0";
    $status = isset($_REQUEST["status"])?'Enable':'Disable';
    $id = $_SESSION['id'];
    // $percentage="0";
    $operation = "Added";
    $user_type="admin";
    $isopen="open";

//   echo "INSERT INTO  `vendor_reg`(`name`, `lname`, `username`, `password`, `email`, `business_name`, `city`, `area`, `address`,`contact_person`, `contact`, `rating`,`stats`,`added_by`,`operation`,`user_type`,`isopen`) VALUES ( '".$firstname."', '".$lastname."',  '".$user_id."', '".$password."' , '".$email."',  '".$business_name."', '".$city."',  '".$area."',  '".$address."',  '".$contact_person."' ,  '".$contact."', '".$rating."', '".$status."','".$id."', '".$operation."', '".$user_type."','".$isopen."')";
    
        try {
            $stmt = $obj->con1->prepare(
                "INSERT INTO  `vendor_reg`(`name`, `lname`, `username`, `password`, `email`, `business_name`, `city`, `area`, `address`,`contact_person`, `contact`, `rating`,`stats`,`added_by`,`operation`,`user_type`,`isopen`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param("ssssssiisssdsssss", $firstname, $lastname,  $user_id, $password , $email,  $business_name, $city,  $area,  $address,  $contact_person ,  $contact, $rating, $status,$id, $operation, $user_type,$isopen);
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
             header("location:vendor_reg.php");
        } else {
            setcookie("msg", "fail", time() + 3600, "/");
             header("location:vendor_reg.php");
        }
  
}

if (isset($_REQUEST["update"])) {
    $firstname = $_REQUEST["name"];
    $lastname = $_REQUEST["lname"];
    $user_id= $_REQUEST["uid"];
    $password = $_REQUEST["password"];
    $email = $_REQUEST["email"];
    $business_name = $_REQUEST["business_name"];
    $city= $_REQUEST["city"];
    $area = $_REQUEST["area"];
    $address = $_REQUEST["address"];
    $contact_person = $_REQUEST["contact_person"];
    $contact= $_REQUEST["contact"];
    $rating="0";
    $status = isset($_REQUEST["status"])?'Enable':'Disable';
    $id = $_SESSION['id'];
    // $percentage="0";
    $operation = "Added";
    $user_type="admin";
    $isopen="open";
    $editId = $_COOKIE["edit_id"];

    try {
        $stmt = $obj->con1->prepare(
            "UPDATE  `vendor_reg` SET`name`=?, `lname`=?, `username`=?, `password`=?, `email`=?, `business_name`=?, `city`=?, `area`=?, `address`=?,`contact_person`=?, `contact`=?, `rating`=?,`stats`=?,`added_by`=?,`operation`=?,`user_type`=?,`isopen`=? WHERE `id`=?"
        );
        // echo  "UPDATE  `vendor_reg` SET`name`= '".$firstname."', `lname`='".$lastname."', `username`=  '".$user_id."', `password`= '".$password."', `email`='".$email."', `business_name`= '".$business_name."', `city`='".$city."', `area`='".$area."', `address`='".$address."',`contact_person`= '".$contact_person."', `contact`= '".$contact."', `rating`='".$rating."',`stats`= '".$status."',`added_by`='".$id."',`operation`='".$operation."',`user_type`='".$user_type."',`isopen`='".$isopen."' WHERE `id`='".$editId."'";
        // $stmt->bind_param("ssssssiisssdsssssi", $firstname, $lastname,  $user_id, $password , $email,  $business_name, $city,  $area,  $address,  $contact_person ,  $contact, $rating, $status,$id, $operation, $user_type,$isopen,$editId);

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
        header("location:vendor_reg.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:vendor_reg.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">Registration -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="name">First Name</label>
                        <input id="name" name="name" type="text" class="form-input"
                            placeholder="Enter your first name"
                            value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="lname">Last Name</label>
                        <input id="lname" name="lname" type="text" class="form-input"
                            placeholder="Enter your last name"
                            value="<?php echo (isset($mode)) ? $data['lname'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="gridUID">User Name</label>
                        <input type="text" placeholder="Enter your Userid" name="uid" id="uid" class="form-input"
                            value="<?php echo (isset($mode)) ? $data['username'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="gridpass">Password</label>
                        <input type="password" placeholder="Enter Password" name="password" class="form-input"
                            pattern=".{8,}" title="Password should be at least 8 characters long"
                            value="<?php echo (isset($mode)) ? $data['password'] : '' ?>"
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> required />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="business_name">Business Name</label>
                        <div>
                            <input id="business_name" name="business_name" type="text"
                                placeholder="Enter  Phone Business Name"
                                class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                                value="<?php echo (isset($mode)) ? $data['business_name'] : '' ?>" required
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="text" class="form-input" placeholder="Enter your Email"
                            value="<?php echo (isset($mode)) ? $data['email'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="contact_person">Contact Person</label>
                        <input id="contact_person" name="contact_person" type="text" class="form-input"
                            placeholder="Enter Contact Person"
                            value="<?php echo (isset($mode)) ? $data['contact_person'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="phone_no">Phone Number</label>
                        <div>
                            <div class="flex">
                                <div
                                    class="bg-[#eee] flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                                    +91</div>
                                <input id="contact" name="contact" type="text" placeholder="Enter  Phone Number"
                                    class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="10"
                                    value="<?php echo (isset($mode)) ? $data['contact'] : '' ?>" required
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                            </div>
                        </div>
                    </div>

                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-10">
                    <div>
                        <div>
                            <label for="address">Address </label>
                            <textarea autocomplete="on" name="address" id="address" class="form-textarea" rows="1"
                                value="" required
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>><?php echo isset($mode) ? $data['address'] : '' ?></textarea>
                        </div>
                    </div>
                    <div>
                    <label for="groupFname">City Name</label>
                    <select class="form-select text-gray-500" name="city" id="city"
                    <?php echo isset($mode) && $mode == 'view' ? 'disabled' : ''?> required>
                        <option value="">Choose City</option>
                        <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `city` WHERE city_name!='no city'");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) { 
                        ?>
                            <option value="<?php echo $result["id"]; ?>"
                                <?php echo isset($mode) && $data["city"] == $result["id"] ? "selected" : ""; ?> 
                            >
                                <?php echo $result["city_name"]; ?>
                            </option>
                        <?php 
                            }
                        ?>
                    </select>
                </div>
                    <div>
                        <label for="area">Area</label>
                        <input id="area" name="area" type="tel" class="form-input" placeholder="Enter Pincode"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                            value="<?php echo (isset($mode)) ? $data['area'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            <?php echo isset($mode) && $data['stats'] == 'Enable' ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'Disabled' : '' ?> name="status" required>
                        <span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>


                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>"
                        id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>"
                        <?php echo isset($mode) ? '' : 'onclick="return checkImage()"' ?>>
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
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
    window.location = "vendor_reg.php";
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