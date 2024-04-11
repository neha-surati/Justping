<?php
//CREATED BY HARSH (09/02/2024)
include "header.php";
if (isset($_COOKIE["view_id"])) {
    $mode = 'view';
    $viewId = $_COOKIE["view_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `menu_control` where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE["edit_id"])) {
    $mode = 'edit';
    $editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `menu_control` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $name = $_REQUEST["name"];
    $link = $_REQUEST["link"];
    $status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'enable' : 'disable';

    try {
        $stmt = $obj->con1->prepare(
            "INSERT INTO `menu_control`(`name`,`link`,`status`) VALUES (?,?,?)"
        );
        $stmt->bind_param("sss", $name, $link, $status);
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
        header("location:menu_control.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:menu_control.php");
    }
}

if (isset($_REQUEST["update"])) {
    $name = $_REQUEST["name"];
    $link = $_REQUEST["link"];
    $status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'enable' : 'disable';
    $editId = $_COOKIE["edit_id"];

    try {
        $stmt = $obj->con1->prepare(
            "UPDATE `menu_control` SET name=?, link=?, status=? WHERE id=?"
        );
        $stmt->bind_param("sssi", $name, $link, $status, $editId);
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
        header("location:menu_control.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:menu_control.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">Menu Control-
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div>
                    <label for="title">Name</label>
                    <input id="title" name="name" type="text" class="form-input"
                        value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>
                <div>
                    <label for="link">Link</label>
                    <input id="link" name="link" type="text" min="0" class="form-input"
                        value="<?php echo (isset($mode)) ? $data['link'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>

                <?php //echo "status=".$data['status']     ?>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            <?php echo isset($mode) && $data['status'] == 'enable' ? 'checked' : '' ?> name="status">
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
                        onclick="<?php echo (isset($mode)) ? 'javascript:go_back()' : 'window.location.reload()' ?>">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "menu_control.php";
    }
</script>

<?php
include "footer.php";
?>

<h5 class="text-xl text-primary font-semibold dark:text-white-light">Menu Control -
    <?php echo isset($mode) ? ($mode == "edit" ? 'Edit' : 'View') : 'Add' ?>
</h5>