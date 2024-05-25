<?php
include "header.php";

if (isset($_COOKIE['viewId'])) {
    $mode = 'view';
    $viewId = $_COOKIE['viewId'];
    $stmt = $obj->con1->prepare("SELECT a1.*, c1.city_name FROM `area` a1, `state` s1, `city` c1 WHERE a1.srno=? AND a1.city=c1.id;");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if(isset($_COOKIE['editId'])){
    $mode = 'edit';
    $editId = $_COOKIE['editId'];
    $stmt = $obj->con1->prepare("SELECT a1.*, c1.city_name FROM `area` a1, `state` s1, `city` c1 WHERE a1.srno=? AND a1.city=c1.id;");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if(isset($_REQUEST['update'])){
    $editId = $_COOKIE['editId'];
    $city_id = $_REQUEST["city_id"];
    $area_name = $_REQUEST["area_name"];
    $pincode = $_REQUEST["pincode"];
    $status = isset($_REQUEST["status"])?'Enable':'Disable';
    $user_id = $_SESSION['id'];
    $operation = "Updated";

    
    // echo "UPDATE `area` SET city='".$city_id."', area_name='". $area_name."', pincode='". $pincode."',  stats='".$status."',added_by='".$user_id."', operation='".$operation."' WHERE id='".$editId."'";
    $stmt = $obj->con1->prepare("UPDATE `area` SET city=?, area_name=?, pincode=?, stats=?, added_by=?, operation=? WHERE srno=?");
    $stmt->bind_param("isssisi", $city_id, $area_name, $pincode, $status, $user_id, $operation, $editId );
    $Res = $stmt->execute();
    $stmt->close();

    if ($Res) {
        setcookie("msg", "update", time() + 3600, "/");
        header("location:area.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:area.php");
    }
}

if (isset($_REQUEST["save"])) {
    $city_id = $_REQUEST["city_id"];
    $area_name = $_REQUEST["area_name"];
    $pincode = $_REQUEST["pincode"];
    $status = isset($_REQUEST["status"])?'Enable':'Disable';
    $user_id = $_SESSION['id'];
    $operation = "Added";
    try {
        // echo "INSERT INTO `area`(`city`,`area_name`,`pincode`,`stats`,`added_by`,`operation`) VALUES ('".$city_id."', '".$area_name."','".$pincode."', '".$status."', '".$user_id."', '".$operation."')";
        $stmt = $obj->con1->prepare(
            "INSERT INTO `area`(`city`,`area_name`,`pincode`,`stats`,`added_by`,`operation`) VALUES (?,?,?,?,?,?)"
        );
        $stmt->bind_param("isssis", $city_id, $area_name, $pincode, $status, $user_id, $operation);
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
        header("location:area.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:area.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">Area-
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
            <div>
                    <label for="groupFname">City Name</label>
                    <select class="form-select text-gray-500" name="city_id" id="city_id"
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
                    <label for="area_name">Area Name </label>
                    <input id="area_name" name="area_name" type="text" class="form-input" 
                    value="<?php echo isset($mode) ? $data["area_name"] : ""; ?>" pattern="^\s*\S.*$" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?>
                    required />
                    <p class="mt-3 text-danger text-base font-bold" id="demo"></p>
                </div> 

                <div>
                    <label for="pincode"> Pincode </label>
                    <input id="pincode" name="pincode" type="text" class="form-input"
                     pattern="^[1-9][0-9]{5}$"title="enter valid pincode" maxlength="6"
                        onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 13" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> value="<?php echo isset($mode) ? $data['pincode'] : '' ?>"
                        required />
                    <p class="mt-3 text-danger text-base font-bold" id="demo"></p>
                </div>

                <?php //echo "status=".$data['status']     ?>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            <?php echo isset($mode) && $data['stats'] == 'Enable' ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'Disabled' : '' ?> name="status" >
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
                    <button type="button" class="btn btn-danger"
                    onclick="location.href='area.php'">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script type="text/javascript">
    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "area.php";
    }

    function localValidate(){
        let form = document.getElementById('mainForm');
        let submitButton = document.getElementById('save');
        let city = document.getElementById('city_name');

        if(form.checkValidity() && checkCity(city, <?php echo isset($mode) ? $data['id'] : -1 ?>)){
            setTimeout(() => {
                submitButton.disabled = true;
            }, 0);
            return true;
        }
    }

    function checkCity(c1, id){
        let n = c1.value;
        var state_id = document.getElementById('state_id').value;
        let cityId = id;

        const obj = new XMLHttpRequest();
        obj.open("GET",`./ajax/check_city.php?city_name=${n}&state_id=${state_id}&ctid=${cityId}`, false);
        obj.send();

        if(obj.status == 200){
            let x = obj.responseText;
            if(x>=1){
                c1.value="";
                c1.focus();
                document.getElementById("demo").innerHTML = "Sorry the name alredy exist!";
                return false;
            } else{  
                document.getElementById("demo").innerHTML = "";
                return true;
            }
        }
    }

</script>
<script>
    function loadCities(stid, ctid = 0) {
        const xhttp = new XMLHttpRequest();
        xhttp.open("GET", `getcities.php?sid=${stid}&ctid=${ctid}`);
        xhttp.send();
        xhttp.onload = function () {
            document.getElementById("area_id").innerHTML = xhttp.responseText;
        }
    }
</script>
<?php
if (isset($mode) && $mode == 'edit') {
    echo "
            <script>
                const stid = document.getElementById('stateId').value;
                const ctid =" . json_encode($data['city']) . ";
                loadCities(stid, ctid);
            </script>
        ";
}
?>


<?php
include "footer.php";
?>