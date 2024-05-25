<?php
include "header.php";

if (isset($_COOKIE['viewId'])) {
    $mode = 'view';
    $viewId = $_COOKIE['viewId'];
    $stmt = $obj->con1->prepare("SELECT * FROM `state` where id=?");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if(isset($_COOKIE['editId'])){
    $mode = 'edit';
    $editId = $_COOKIE['editId'];
    $stmt = $obj->con1->prepare("SELECT * FROM `state` where id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if(isset($_REQUEST['update'])){
    $editId = $_COOKIE['editId'];
    $state_name = $_REQUEST['state_name'];
    $status = isset($_REQUEST["status"])?'Enable':'Disable';
    $user_id = $_SESSION['id'];
    $operation = "Updated";
 
    // echo "UPDATE `state` SET state_name='".$state_name."', stats='".$status."', added_by='".$user_id."', operation='".."' WHERE id='".."'";

    $stmt = $obj->con1->prepare("UPDATE `state` SET state_name=?, stats=?, added_by=?, operation=? WHERE id=?");
    $stmt->bind_param("ssisi", $state_name, $status, $user_id, $operation, $editId);
    $Res = $stmt->execute();
    $stmt->close();

    if ($Res) {
        setcookie("msg", "update", time() + 3600, "/");
        header("location:state.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:state.php");
    }
}

if (isset($_REQUEST["save"])) {
    $state_name = $_REQUEST["state_name"];
    $status = isset($_REQUEST["status"])?'Enable':'Disable';
    $user_id = $_SESSION['id'];
    $operation = "Added";
    try {
        // echo "INSERT INTO `state`(`state_name`,`stats`,`added_by`,`operation`) VALUES ('".$state_name."', '".$status."', '".$user_id."', '".$operation."')";
        $stmt = $obj->con1->prepare(
            "INSERT INTO `state`(`state_name`,`stats`,`added_by`,`operation`) VALUES (?,?,?,?)"
        );
        $stmt->bind_param("ssis", $state_name, $status, $user_id, $operation);
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
        header("location:state.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:state.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">State-
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div>
                    <label for="state_name">State Name </label>
                    <input id="state_name" name="state_name" type="text" class="form-input"
                        onblur="checkName(this, <?php echo isset($mode) ? $data['id'] : 0 ?>)"
                        value="<?php echo isset($mode) ? $data["state_name"] : ""; ?>" pattern="^\s*\S.*$"
                        <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?> placeholder="Enter state name"
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
                    <button type="button" class="btn btn-danger" onclick="location.href='state.php'">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function go_back() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "state.php";
}
function localValidate(){
        let form = document.getElementById('mainForm');
        let submitButton = document.getElementById('save');
        let nameEle = document.getElementById('groupFname');
        
        if (form.checkValidity() && checkName(nameEle, <?php echo isset($mode) ? $data['id'] : 0 ?>)) {
            setTimeout(() => {
                submitButton.disabled = true;
            }, 0);
            return true;
        }
    }

    function checkName(c1, id) {
        let n = c1.value;
        const obj = new XMLHttpRequest();
        obj.open("GET", "./ajax/check_state.php?state_name=" + n + "&stid=" + id, false);
        obj.send();
        if(obj.status == 200){
            let x = obj.responseText;
            console.log(n, id);
            if (x >= 1) {
                c1.value = "";
                c1.focus();
                document.getElementById("demo").innerHTML = "Sorry the name alredy exist!";
                return false;
            }
            else {
                document.getElementById("demo").innerHTML = "";
                return true;
            }
        }
    }
</script>


<?php
include "footer.php";
?>