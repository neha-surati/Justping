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
    $details = $_REQUEST["details"];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'Enable' : 'Disable';
    $operation = "Added";
    $sort_order="1";


    try {
        $stmt = $obj->con1->prepare(
            "INSERT INTO `product_category`(`name`, `details`, `stats`, `operation`,`sort_order`) VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param("ssssi",$name,$details ,$status,$operation,$sort_order);
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
    $details = $_REQUEST["details"];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'Enable' : 'Disable';
    $added_by = $_SESSION["id"];
    $editId = $_COOKIE["edit_id"];
    $operation = "Updated";


    try {
        $stmt = $obj->con1->prepare(
            "UPDATE `product_category` SET `name`=?,`details`=?,`stats`=?,`operation`=? WHERE `id`=?"
        );
        $stmt->bind_param("ssssi", $name, $details , $status, $operation, $editId);

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
        <h1 class="dark:text-white-dar text-2xl font-bold">Product Category -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">

                <div>
                    <label for="name">Name</label>
                    <input type="hidden" id="pid" value="<?php echo isset($mode) ? $data['id'] : '' ?>">
                    <input id="name" name="name" type="text" class="form-input" placeholder="Enter name"
                    onblur="checkName(this, <?php echo isset($mode) ? $data['id'] : -1 ?>)"
                        value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required
                        <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        <p class="mt-3 text-danger text-base font-bold" id="demo"></p>
                </div>
                <div>
                    <label for="details">Details</label>
                    <textarea autocomplete="on" name="details" id="details" class="form-textarea" rows="2"
                                value="" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>><?php echo isset($mode) ? $data['details'] : '' ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            name="status" <?php echo (isset($mode) && $data['stats'] == 'Enable') ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>><span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>
                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>"
                        id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">
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



document.addEventListener('DOMContentLoaded', function() {
    const submitButton = document.getElementById('save');
    const form = document.getElementById('mainForm');

    submitButton.addEventListener('click', function() {
        const c1 = document.getElementById("name");
        const id = document.getElementById("pid");
        if (!checkName(c1, id)) {
            return false;
        }
    });
});

function checkName(c1, id) {
    const n = c1.value;
    const pid = id.value;

    const obj = new XMLHttpRequest();
    obj.open("GET", "./ajax/check_product.php?name=" + n + "&pid=" + pid, false); // synchronous request
    obj.send();

    if (obj.status == 200) {
        const x = obj.responseText;
        if (x >= 1) {
            c1.value = "";
            c1.focus();
            document.getElementById("demo").innerHTML = "Sorry the product already exists!";
            document.getElementById("demo").classList.remove("hidden");
            return false;
        } else {
            document.getElementById("demo").innerHTML = "";
            document.getElementById("demo").classList.add("hidden");
            return true;
        }
    } else {
        return false;
    }
}
</script>

<?php
include "footer.php";
?>