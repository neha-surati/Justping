<?php
include "header.php";

if (isset($_COOKIE["view_id"])) {
    $mode = 'view';
    $viewId = $_COOKIE["view_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `product_category` where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE["edit_id"])) {
    $mode = 'edit';
    $editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `product_category` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $name = $_REQUEST["name"];
    $v_id = $_REQUEST["v_id"];
    $details = $_REQUEST["details"];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'Enable' : 'Disable';
    $added_by = $_SESSION["id"];
    $operation = "Added";


    try {
        $stmt = $obj->con1->prepare(
            "INSERT INTO `product_category`(`v_id`,`name`, `details`, `stats`, `added_by`, `operation`) VALUES (?,?,?,?,?,?)"
        );
        $stmt->bind_param("isssis",$v_id,$name,$details ,$status,$added_by,$operation);
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
        header("location:product_category_details.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:product_category_details.php");
    }
}

if (isset($_REQUEST["update"])) {
    $name = $_REQUEST["name"];
    $v_id = $_REQUEST["v_id"];
    $details = $_REQUEST["details"];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'Enable' : 'Disable';
    $added_by = $_SESSION["id"];
    $editId = $_COOKIE["edit_id"];
    $operation = "Updated";


    try {
        $stmt = $obj->con1->prepare(
            "UPDATE `product_category` SET `v_id`=?,`name`=?,`details`=?,`stats`=?,`added_by`=?,`operation`=? WHERE `id`=?"
        );
        $stmt->bind_param("isssisi", $v_id, $name, $details , $status, $added_by, $operation, $editId);

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
        header("location:product_category_details.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:product_category_details.php");
    }
}

?>

<div class='p-6'>
    <div class='flex items-center mb-3'>
        <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span>
        <h1 class="dark:text-white-dar text-2xl font-bold">Product Category - <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="name">Name</label>
                        <input id="name" name="name" type="text" class="form-input" placeholder="Enter name" value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="details">Details</label>
                        <input id="details" name="details" type="text" class="form-input" placeholder="Enter detail" value="<?php echo (isset($mode)) ? $data['details'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div>
                    <label for="groupFname">Vendor Name</label>
                    <select class="form-select text-black" name="v_id" id="v_id" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required>
                        <option value="">Select Vendor</option>
                        <?php
                        $stmt = $obj->con1->prepare("SELECT * FROM `vendor_reg`");
                        $stmt->execute();
                        $Resp = $stmt->get_result();
                        $stmt->close();

                        while ($result = mysqli_fetch_array($Resp)) {
                        ?>
                            <option value="<?php echo $result["id"]; ?>" <?php echo (isset($mode) && $data["v_id"] == $result["id"]) ? "selected" : ""; ?>>
                                <?php echo $result["name"]; ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox" class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status" name="status" <?php echo (isset($mode) && $data['stats'] == 'Enable') ? 'checked' : '' ?> <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>><span class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>
                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="javascript:go_back()">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    checkCookies();

    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "product_category_details.php";
    }
</script>

<?php
include "footer.php";
?>