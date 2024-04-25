<?php
include "header.php";

if (isset($_COOKIE['viewId'])) {
    $mode = 'view';
    $viewId = $_COOKIE['viewId'];
    $stmt = $obj->con1->prepare("SELECT * FROM `privacy_policy` where id=?");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if(isset($_COOKIE['editId'])){
    $mode = 'edit';
    $editId = $_COOKIE['editId'];
    $stmt = $obj->con1->prepare("SELECT * FROM `privacy_policy` where id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if(isset($_REQUEST['update'])){
    $editId = $_COOKIE['editId'];
    $detail = $_REQUEST["quill_input"];
    $type = $_REQUEST["type"];
    $date_time = date("d-m-Y h:i A");
    $user_id = $_SESSION['id'];
    $operation = "Updated";

    // echo "UPDATE `city` SET city_name= $city_name, state_id=$state_id, status=$status,added_by= $user_id,operation= $operation";
    $stmt = $obj->con1->prepare("UPDATE `privacy_policy` SET detail=?, type=?, date_time=?,added_by=?,operation=? WHERE id=?");
    $stmt->bind_param("sssisi", $detail, $type, $date_time, $user_id, $operation ,$editId);
    $Res = $stmt->execute();
    $stmt->close();

    if ($Res) {
        setcookie("msg", "update", time() + 3600, "/");
        header("location:privacy_policy.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:privacy_policy.php");
    }
}

if (isset($_REQUEST["save"])) {
    $detail = $_REQUEST["quill_input"];
    $type = $_REQUEST["type"];
    $date_time = date("d-m-Y h:i A");
    $user_id = $_SESSION['id'];
    $operation = "Added";
    try {
        // echo "INSERT INTO `privacy_policy`(`detail`,`type`,`date_time`,`added_by`,`operation`) VALUES ('".$detail."', '".$type."', '".$date_time."', '".$user_id."', '".$operation."')";
        $stmt = $obj->con1->prepare(
            "INSERT INTO `privacy_policy`(`detail`,`type`,`date_time`,`added_by`,`operation`) VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param("sssis", $detail, $type, $date_time, $user_id, $operation);
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
        header("location:privacy_policy.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:privacy_policy.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">Privacy Policy-
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div>
                    <label for="type">Type</label>
                    <select class="form-select text-white-dark" required id="type" name="type"
                        <?php echo isset($mode) && $mode == 'view' ? 'disabled' : ''?>>
                        <option value="">Choose Type</option>
                        <option value="vendor" <?php echo isset($mode) && $data['type'] == 'vendor' ? 'selected' : '' ?>>
                            Vendor</option>
                        <option value="user" <?php echo isset($mode) && $data['type'] == 'user' ? 'selected' : '' ?>>
                            User</option>
                    </select>
                </div>

                <div>
                    <label for="editor" class="mb-3 block">Detail</label>
                    <div id="editor" name="detail" class="!mt-1">
                        <?php echo isset($mode) ? $data['detail'] : '' ?>
                    </div>
                </div>

                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>"
                        id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>" onclick="return formSubmit()">
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <button type="button" class="btn btn-danger"
                        onclick="location.href='privacy_policy.php'">Close</button>
                </div>
                <input type="hidden" name="quill_input" id="quill_input">
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function go_back() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "privacy_policy.php";
}
</script>

<script src="assets/js/quill.js"></script>
<script>
document.addEventListener("alpine:init", () => {
    Alpine.data("form", () => ({
        tableData: {
            id: 1,
            name: 'John Doe',
            email: 'johndoe@yahoo.com',
            date: '10/08/2020',
            sale: 120,
            status: 'Complete',
            register: '5 min ago',
            progress: '40%',
            position: 'Developer',
            office: 'London'
        },
    }));
});

var quill = new Quill('#editor', {
    theme: 'snow'
});
var toolbar = quill.container.previousSibling;
toolbar.querySelector('.ql-picker').setAttribute('title', 'Font Size');
toolbar.querySelector('button.ql-bold').setAttribute('title', 'Bold');
toolbar.querySelector('button.ql-italic').setAttribute('title', 'Italic');
toolbar.querySelector('button.ql-link').setAttribute('title', 'Link');
toolbar.querySelector('button.ql-underline').setAttribute('title', 'Underline');
toolbar.querySelector('button.ql-clean').setAttribute('title', 'Clear Formatting');
toolbar.querySelector('[value=ordered]').setAttribute('title', 'Ordered List');
toolbar.querySelector('[value=bullet]').setAttribute('title', 'Bullet List');

function formSubmit() {
    let xyz = document.getElementById("quill_input");
    let editorContent = quill.root.innerHTML;
    xyz.value = editorContent;
    let val = xyz.value.replace(/<[^>]*>/g, '');

    if(val.trim() == ''){
        coloredToast("danger", 'Please add something in Detail.');
        return false;
    } else {
        return true;
    }
}
</script>


<?php
include "footer.php";
?>