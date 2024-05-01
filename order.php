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
        <h1 class="dark:text-white-dar text-2xl font-bold">Customer Registration -
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
                            value="<?php echo (isset($mode)) ? $data['firstname'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="lname">Last Name</label>
                        <input id="lname" name="lname" type="text" class="form-input"
                            placeholder="Enter your last name"
                            value="<?php echo (isset($mode)) ? $data['lastname'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
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
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-input" placeholder="Enter your Email"
                            value="<?php echo (isset($mode)) ? $data['email'] : '' ?>" required
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
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            <?php echo isset($mode) && $data['status'] == 'Enable' ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'Disabled' : '' ?> name="status">
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
    window.location = "customer_reg.php";
}

</script>
<?php
include "footer.php";
?>