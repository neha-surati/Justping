<?php
include "header.php";

if (isset($_COOKIE["view_id"])) {
    $mode = 'view';
    $viewId = $_COOKIE["view_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `rating`where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
    // print_r($data);
}

if (isset($_COOKIE["edit_id"])) {
    $mode = 'edit';
    $editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `rating` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
    // print_r($data);
}


if (isset($_REQUEST["update"])) {
    $status = isset($_REQUEST["status"])?'Enable':'Disable';
    $operation = "update";
    $editId = $_COOKIE["edit_id"];

    try {
        $stmt = $obj->con1->prepare("UPDATE   `rating` SET `stats`=?,`operation`=? WHERE `id`=?" );
        $stmt->bind_param("ssi", $status, $operation, $editId);
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
        header("location:rating.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:rating.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">Rating -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div>
                    <label for="groupFname">Vendor</label>
                    <select class="form-select text-gray-500" name="vendor" id="vendor"
                    <?php echo isset($mode) && ($mode == 'view' || $mode == 'edit') ? 'readonly' : '' ?>required>
                        <option value="">Choose Vendor</option>
                        <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `vendor_reg` WHERE name!='no name'");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) { 
                        ?>
                        <option value="<?php echo $result["id"] ?>"
                            <?php echo isset($mode) && $data["v_id"] == $result["id"] ? "selected" : "" ?>>
                            <?php echo $result["name"] ?>
                        </option>
                        <?php 
                            }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="rate">Rate</label>
                    <input id="rate" name="rate" type="text" class="form-input" placeholder="5"
                        value="<?php echo (isset($mode)) ? $data['rate'] : '' ?>" required
                        <?php echo isset($mode) && ($mode == 'view' || $mode == 'edit') ? 'readonly' : '' ?> />
                </div>
                <div>
                    <label for="groupFname">Product</label>
                    <select class="form-select text-gray-500" name="product" id="product"
                    <?php echo isset($mode) && ($mode == 'view' || $mode == 'edit') ? 'readonly' : '' ?> required>
                        <option value="">Choose Product</option>
                        <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `product` WHERE name!='no name'");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) { 
                        ?>
                        <option value="<?php echo $result["id"]; ?>"
                            <?php echo isset($mode) && $data["p_id"] == $result["id"] ? "selected" : ""; ?>>
                            <?php echo $result["name"]; ?>
                        </option>
                        <?php 
                            }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="review">Review</label>
                    <input id="review" name="review" type="text" class="form-input" placeholder="Enter your Review"
                        value="<?php echo (isset($mode)) ? $data['review'] : '' ?>" required
                        <?php echo isset($mode) && ($mode == 'view' || $mode == 'edit') ? 'readonly' : '' ?> />
                </div>
                <div>
                    <label for="groupFname">Customer</label>
                    <select class="form-select text-gray-500" name="customer" id="customer"
                    <?php echo isset($mode) && ($mode == 'view' || $mode == 'edit') ? 'disabled' : '' ?> required>
                        <option value="">Choose Customer</option>
                        <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `customer_reg`");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) { 
                        ?>
                        <option value="<?php echo $result["id"]; ?>"
                            <?php echo isset($mode) && $data["cust_id"] == $result["id"] ? "selected" : ""; ?>>
                            <?php echo $result["firstname"] . " " . $result["lastname"]; ?>
                        </option>
                        <?php 
                            }
                        ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            <?php echo isset($mode) && $data['stats'] == 'Enable' ? 'checked' : '' ?>
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
function go_back() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "rating.php";
}
</script>
<?php
include "footer.php";
?>