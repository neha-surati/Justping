<?php
include "header.php";

if (isset($_COOKIE["view_id"])) {
    $mode = 'view';
    $viewId = $_COOKIE["view_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `customer_addresses`where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE["edit_id"])) {
    $mode = 'edit';
    $editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `customer_addresses` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $customername = $_REQUEST["name"];
    $customeradd= $_REQUEST["addresslabel"];
    $housenumber= $_REQUEST["housenumber"];
    $address = $_REQUEST["address"];
    $cityname= $_REQUEST["city"];
    $area = $_REQUEST["area"];
    $notes = $_REQUEST["notes"];
    
  

//  echo  "INSERT INTO `customer_addresses`(`c_id`, `add_label`, `house_no`,`street`,`city_id`, `area_id`,`notes`)VALUES ( '" .$customername."', '".$customeradd."',  '".$housenumber."', '".$address."' , '".$cityname."', '". $area."',  '".$notes."')";
    
        try {
            $stmt = $obj->con1->prepare(
                "INSERT INTO `customer_addresses`(`c_id`, `add_label`, `house_no`,`street`,`city_id`, `area_id`,`notes`) VALUES (?,?,?,?,?,?,?)"
            );
            $stmt->bind_param("isssiis", $customername, $customeradd,$housenumber, $address , $cityname,  $area,  $notes );
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
              header("location:customer_addresses.php");
        } else {
            setcookie("msg", "fail", time() + 3600, "/");
             header("location:customer_addresses.php");
        }
  
}

if (isset($_REQUEST["update"])) {
    $customername = $_REQUEST["name"];
    $customeradd= $_REQUEST["addresslabel"];
    $housenumber= $_REQUEST["housenumber"];
    $address = $_REQUEST["address"];
    $cityname= $_REQUEST["city"];
    $area = $_REQUEST["area"];
    $notes = $_REQUEST["notes"];
    $editId = $_COOKIE["edit_id"];

    try {
        $stmt = $obj->con1->prepare(
            "UPDATE `customer_addresses` SET `c_id`=?, `add_label`=?, `house_no`=?,`street`=?,`city_id`=?, `area_id`=?,`notes`=? WHERE `id`=?"
        );
       
        $stmt->bind_param("isssiisi", $customername, $customeradd,$housenumber, $address , $cityname,  $area,  $notes,$editId );
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
        header("location:customer_addresses.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:customer_addresses.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">Customer Addresses -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div>
                    <label for="name">Customer Name</label>
                    <select class="form-select text-gray-500" name="name" id="name"
                            <?php echo isset($mode) && $mode == 'view' ? 'disabled' : ''?> required>
                            <option value="">Choose Customer Name</option>
                            <?php
                            $stmt = $obj->con1->prepare("SELECT CONCAT(firstname, ' ', lastname) AS full_name, customer_reg.*
                            FROM customer_reg;");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) { 
                        ?>
                            <option value="<?php echo $result["id"]; ?>"
                                <?php echo isset($mode) && $data["c_id"] == $result["id"] ? "selected" : ""; ?>>
                                <?php echo $result["full_name"]; ?>
                            </option>
                            <?php 
                            }
                        ?>
                        </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="addresslabel">Address Label</label>
                        <input id="addresslabel" name="addresslabel" type="text" class="form-input"
                            placeholder="Enter your address label"
                            value="<?php echo (isset($mode)) ? $data['add_label'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>

                    <div>
                        <label for="housenumber">House Number</label>
                        <input id="housenumber" name="housenumber" type="number" class="form-input"
                            placeholder="Enter your House Number"
                            value="<?php echo (isset($mode)) ? $data['house_no'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-10">
                    <div>
                        <div>
                            <label for="address">Address </label>
                            <textarea autocomplete="on" name="address" id="address" class="form-textarea" rows="1"
                                value="" required
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>><?php echo isset($mode) ? $data['street'] : '' ?></textarea>
                        </div>
                    </div>
                    <div>
                        <label for="groupFname">City Name</label>
                        <select class="form-select text-gray-500" name="city" id="city" onchange="getAreaList(this.value)" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : ''?> required>
                            <option value="">Choose City</option>
                            <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `city` WHERE city_name!='no city'");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) { 
                        ?>
                            <option value="<?php echo $result["id"]; ?>"
                                <?php echo isset($mode) && $data["city_id"] == $result["id"] ? "selected" : ""; ?>>
                                <?php echo $result["city_name"]; ?>
                            </option>
                            <?php 
                            }
                        ?>
                        </select>
                    </div>
                    <div>
                        <label for="area">Area</label>
                        <!-- <input id="area" name="area" type="tel" class="form-input" placeholder="Enter Pincode"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" value="<?php echo (isset($mode)) ? $data['area_id'] : '' ?>" required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> /> -->
                        <select class="form-select text-gray-500" name="area" id="area" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : ''?> required>
                            <option value="">Choose Area</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="notes">Notes</label>
                    <input id="notes" name="notes" type="text" class="form-input" placeholder="Enter Notes"
                        value="<?php echo (isset($mode)) ? $data['notes'] : '' ?>" required
                        <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
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
    window.location = "customer_addresses.php";
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
        xhttp.onload = function () {
            document.getElementById("area").innerHTML = xhttp.responseText;
        }
    }

</script>
<?php
if (isset($mode)) {
    echo "
            <script>
                const city_id = ". json_encode($data['city_id']) .";
                const area_id =" . json_encode($data['area_id']) . ";
                getAreaList(city_id, area_id);
            </script>
        ";
}
?>

<?php
include "footer.php";
?>