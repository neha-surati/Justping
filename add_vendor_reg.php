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
    $editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `vendor_reg` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $firstname = $_REQUEST["name"];
    $password = $_REQUEST["password"];
    $email = $_REQUEST["email"];
    $business_name = $_REQUEST["business_name"];
    $city = $_REQUEST["city"];
    $area = $_REQUEST["area"];
    $address = $_REQUEST["address"];
    $contact_person = $_REQUEST["contact_person"];
    $contact = $_REQUEST["contact"];
    $status = isset($_REQUEST["status"]) ? 'Enable' : 'Disable';
    $percentage = $_REQUEST["percentage"];
    $operation = "Added";

    //  echo "INSERT INTO  `vendor_reg`(`name`,  `password`, `email`, `business_name`, `city`, `area`, `address`,`contact_person`, `contact`, `stats`,`operation`,`percentage`) VALUES ( '".$firstname."',  '".$password."' , '".$email."',  '".$business_name."', '".$city."',  '".$area."',  '".$address."',  '".$contact_person."' ,  '".$contact."', '".$status."', '".$operation."', '".$percentage."')";

    try {
        $stmt = $obj->con1->prepare(
            "INSERT INTO  `vendor_reg`(`name`,  `password`, `email`, `business_name`, `city`, `area`, `address`,`contact_person`, `contact`,`stats`,`percentage`,`operation`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param("ssssiissssds", $firstname, $password, $email,  $business_name, $city,  $area,  $address,  $contact_person,  $contact, $status, $percentage, $operation);
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
    $password = $_REQUEST["password"];
    $email = $_REQUEST["email"];
    $business_name = $_REQUEST["business_name"];
    $city = $_REQUEST["city"];
    $area = $_REQUEST["area"];
    $address = $_REQUEST["address"];
    $contact_person = $_REQUEST["contact_person"];
    $contact = $_REQUEST["contact"];
    $status = isset($_REQUEST["status"]) ? 'Enable' : 'Disable';
    $percentage = $_REQUEST["percentage"];
    $operation = "Added";
    $editId = $_COOKIE["edit_id"];

    try {
        $stmt = $obj->con1->prepare(
            "UPDATE  `vendor_reg` SET`name`=?,`password`=?, `email`=?, `business_name`=?, `city`=?, `area`=?, `address`=?,`contact_person`=?, `contact`=?,`stats`=?,`percentage`=?,`operation`=? WHERE `id`=?"
        );
        //  echo  "UPDATE  `vendor_reg` SET`name`= '".$firstname."',  `password`= '".$password."', `email`='".$email."', `business_name`= '".$business_name."', `city`='".$city."', `area`='".$area."', `address`='".$address."',`contact_person`= '".$contact_person."', `contact`= '".$contact."',`stats`= '".$status."',`operation`='".$operation."',`percentage`='".$percentage."' WHERE `id`='".$editId."'";
        $stmt->bind_param("ssssiissssdsi", $firstname, $password, $email,  $business_name, $city,  $area,  $address,  $contact_person,  $contact, $status, $percentage, $operation,  $editId);

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
        <h1 class="dark:text-white-dar text-2xl font-bold">Vendor Registration -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">

                <div>
                    <label for="name"> Name</label>
                    <input id="name" name="name" type="text" class="form-input" placeholder="Enter your name" value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-input" placeholder="Enter your Email" value="<?php echo (isset($mode)) ? $data['email'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="gridpass">Password</label>
                        <input type="password" placeholder="Enter Password" name="password" class="form-input" pattern=".{8,}" title="Password should be at least 8 characters long" value="<?php echo (isset($mode)) ? $data['password'] : '' ?>" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> required />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="business_name">Business Name</label>
                        <div>
                            <input id="business_name" name="business_name" type="text" placeholder="Enter  Phone Business Name" class="form-input ltr:rounded-l-none rtl:rounded-r-none" value="<?php echo (isset($mode)) ? $data['business_name'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                    </div>
                    <div>
                        <label for="percentage">Discount Percentage</label>
                        <div>
                            <input id="percentage" name="percentage" type="text" placeholder="Enter percentage" class="form-input" value="<?php echo (isset($mode)) ? $data['percentage'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="contact_person">Contact Person</label>
                        <input id="contact_person" name="contact_person" type="text" class="form-input" placeholder="Enter Contact Person" value="<?php echo (isset($mode)) ? $data['contact_person'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="phone_no">Phone Number</label>
                        <div>
                            <div class="flex">
                                <div class="bg-[#eee] flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                                    +91</div>
                                <input id="contact" name="contact" type="text" placeholder="Enter Phone Number" class="form-input ltr:rounded-l-none rtl:rounded-r-none" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="10" value="<?php echo (isset($mode)) ? $data['contact'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                            </div>
                        </div>
                    </div>

                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-10">
                    <div>
                        <div>
                            <label for="address">Address </label>
                            <textarea autocomplete="on" name="address" id="address" class="form-textarea" rows="2" value="" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>><?php echo isset($mode) ? $data['address'] : '' ?></textarea>
                        </div>
                    </div>
                    <div>
                        <label for="groupFname">City Name</label>
                        <select class="form-select text-gray-500" name="city" id="city" onchange="getAreaList(this.value)" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required>
                            <option value="">Choose City</option>
                            <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `city` WHERE city_name!='no city'");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) {
                            ?>
                                <option value="<?php echo $result["id"]; ?>" <?php echo isset($mode) && $data["city"] == $result["id"] ? "selected" : ""; ?>>
                                    <?php echo $result["city_name"]; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="area">Area</label>
                        <select class="form-select text-gray-500" name="area" id="area" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required>
                            <option value="">Choose Area</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox" class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status" <?php echo isset($mode) && $data['stats'] == 'Enable' ? 'checked' : '' ?> <?php echo (isset($mode) && $mode == 'view') ? 'Disabled' : '' ?> name="status">
                        <span class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>


                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>" <?php echo isset($mode) ? '' : 'onclick="return checkImage()"' ?>>
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

    function getAreaList(city_id, area_id = 0) {
        const xhttp = new XMLHttpRequest();
        xhttp.open("GET", `getarea.php?city_id=${city_id}&area_id=${area_id}`);
        xhttp.send();
        xhttp.onload = function() {
            document.getElementById("area").innerHTML = xhttp.responseText;
        }
    }
</script>
<?php
if (isset($mode)) {
    echo "
            <script>
                const city_id = " . json_encode($data['city']) . ";
                const area_id =" . json_encode($data['area']) . ";
                getAreaList(city_id, area_id);
            </script>
        ";
}
?>
<?php
include "footer.php";
?>