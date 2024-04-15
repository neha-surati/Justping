<?php
include "header.php";

if (isset($_COOKIE["view_id"])) {
    $mode = 'view';
    $viewId = $_COOKIE["view_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `registration` where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE["edit_id"])) {
    $mode = 'edit';
    $editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `registration` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $firstname = $_REQUEST["firstname"];
    $lastname = $_REQUEST["lastname"];
    $dob = $_REQUEST["dob"];
    $gender = $_REQUEST["gender"];
    $phone_no = $_REQUEST["phone_no"];
    $email = $_REQUEST["email"];
    $marital_s = $_REQUEST["marital_s"];
    $state = $_REQUEST["state"];
    $city = $_REQUEST["city"];
    $pincode = $_REQUEST["pincode"];
    $occupation = $_REQUEST["occupation"];
    $blood_g = $_REQUEST["blood_g"];
    $password = $_REQUEST["password"];
    $confirm_password = $_REQUEST["confirm_password"];

    if ($password == $confirm_password) {
        try {
            $stmt = $obj->con1->prepare(
                "INSERT INTO `registration`(`firstname`, `lastname`, `dob`, `gender`, `phone_no`, `email`, `marital_status`, `state`, `city`, `pincode`, `occupation`, `blood_group`,`password`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param("ssssisssissss", $firstname, $lastname, $dob, $gender, $phone_no, $email, $marital_s, $state, $city, $pincode, $occupation, $blood_g, $password);
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
            header("location:user_details.php");
        } else {
            setcookie("msg", "fail", time() + 3600, "/");
            header("location:user_details.php");
        }
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:tribal_registration.php");
    }
}

if (isset($_REQUEST["update"])) {
    $firstname = $_REQUEST["firstname"];
    $lastname = $_REQUEST["lastname"];
    $dob = $_REQUEST["dob"];
    $gender = $_REQUEST["gender"];
    $phone_no = $_REQUEST["phone_no"];
    $email = $_REQUEST["email"];
    $marital_s = $_REQUEST["marital_s"];
    $state = $_REQUEST["state"];
    $city = $_REQUEST["city"];
    $pincode = $_REQUEST["pincode"];
    $occupation = $_REQUEST["occupation"];
    $blood_g = $_REQUEST["blood_g"];
    $password = $_REQUEST["password"];
    $editId = $_COOKIE["edit_id"];

    try {
        $stmt = $obj->con1->prepare(
            "UPDATE `registration` SET `firstname`=?,`lastname`=?,`dob`=?,`gender`=?,`phone_no`=?,`email`=?,`marital_status`=?,`state`=?,`city`=?,`pincode`=?,`occupation`=?,`blood_group`=?,`password`=? WHERE `id`=?"
        );
        //echo "UPDATE `registration` SET `firstname`=$firstname,`lastname`=$lastname,`dob`=$dob,`gender`=$gender,`phone_no`=$phone_no,`email`=$email,`marital_status`=$marital_s,`state`=$state,`city`=$city,`pincode`=$pincode,`occupation`=$occupation,`blood_group`=$blood_g,`password`=$password WHERE `id`=$editId";
        $stmt->bind_param("ssssisssissssi", $firstname, $lastname, $dob, $gender, $phone_no, $email, $marital_s, $state, $city, $pincode, $occupation, $blood_g, $password, $editId);

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
        header("location:user_details.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:user_details.php");
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
                        <label for="firstname">First Name</label>
                        <input id="firstname" name="firstname" type="text" class="form-input"
                            placeholder="Enter your first name"
                            value="<?php echo (isset($mode)) ? $data['firstname'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="lastname">Last Name</label>
                        <input id="lastname" name="lastname" type="text" class="form-input"
                            placeholder="Enter your last name"
                            value="<?php echo (isset($mode)) ? $data['lastname'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="gridUID">Userid</label>
                        <input type="text" placeholder="" name="userid" class="form-input"
                            value="<?php echo (isset($mode)) ? $data['userid'] : '' ?>" required
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
                        <label for="phone_no">Phone Number</label>
                        <div>
                            <div class="flex">
                                <div
                                    class="bg-[#eee] flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                                    +91</div>
                                <input id="phone_no" name="phone_no" type="text" placeholder="Enter  Phone Number"
                                    class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="10"
                                    value="<?php echo (isset($mode)) ? $data['phone_no'] : '' ?>" required
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="text" class="form-input" placeholder="Enter your Email"
                            value="<?php echo (isset($mode)) ? $data['email'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-10">
                    <div>
                        <div>
                            <label for="groupFname">State Name</label>
                            <select class="form-select text-black" name="state" id="state"
                                <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required
                                onchange="fillCity(this.value)">
                                <option value="">Select State</option>
                                <?php
                                $stmt = $obj->con1->prepare("SELECT * FROM state");
                                $stmt->execute();
                                $Resp = $stmt->get_result();
                                $stmt->close();

                                while ($result = mysqli_fetch_array($Resp)) {
                                ?>
                                <option value="<?php echo $result["id"]; ?>"
                                    <?php echo (isset($mode) && $data["state"] == $result["id"]) ? "selected" : ""; ?>>
                                    <?php echo $result["name"]; ?>
                                </option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="city">City</label>
                        <select class="form-select text-black" name="city" id="city"
                            <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required>

                            <?php
                            if (isset($mode)) {
                                $s = $data["state"];
                                $stmt = $obj->con1->prepare("select * from city WHERE state_id=?");
                                $stmt->bind_param("i", $s);
                                $stmt->execute();
                                $res = $stmt->get_result();
                                $stmt->close();
                                while ($result = mysqli_fetch_array($res)) {
                            ?>
                            <option value="<?php echo $result["id"]; ?>"
                                <?php echo (isset($mode) && $data["city"] == $result["id"]) ? "selected" : ""; ?>>
                                <?php echo $result["city_nm"]; ?>
                            </option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="pincode">Pincode</label>
                        <input id="pincode" name="pincode" type="tel" class="form-input" placeholder="Enter Pincode"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                            value="<?php echo (isset($mode)) ? $data['pincode'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-10">
                    <div>
                        <label for="occupation">Occupation</label>
                        <input id="occupation" name="occupation" type="text" min="0" class="form-input"
                            placeholder="Enter Occupation"
                            value="<?php echo (isset($mode)) ? $data['occupation'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>

                    <div>
                        <label for="blood_g">Blood Group</label>
                        <select class="form-select" id="blood_g" name="blood_g" required>
                            <option value="" selected>Select Blood Group</option>
                            <option value="A+"
                                <?php echo isset($data) && $data['blood_group'] == "A+" ? "selected" : "" ?>>A+</option>
                            <option value="A-"
                                <?php echo isset($data) && $data['blood_group'] == "A-" ? "selected" : "" ?>>A-</option>
                            <option value="B+"
                                <?php echo isset($data) && $data['blood_group'] == "B+" ? "selected" : "" ?>>B+</option>
                            <option value="B-"
                                <?php echo isset($data) && $data['blood_group'] == "B-" ? "selected" : "" ?>>B-</option>
                            <option value="O+"
                                <?php echo isset($data) && $data['blood_group'] == "O+" ? "selected" : "" ?>>O+</option>
                            <option value="O-"
                                <?php echo isset($data) && $data['blood_group'] == "O-" ? "selected" : "" ?>>O-</option>
                            <option value="AB+"
                                <?php echo isset($data) && $data['blood_group'] == "AB+" ? "selected" : "" ?>>AB+
                            </option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="password">Create Password</label>
                        <input id="password" name="password" type="password" min="0" class="form-input"
                            placeholder="Create your new Password"
                            value="<?php echo (isset($mode)) ? $data['password'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="password">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" min="0" class="form-input"
                            placeholder="Confirm your new Password"
                            value="<?php echo (isset($mode)) ? $data['password'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
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
$(document).ready(function() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
});
checkCookies();

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