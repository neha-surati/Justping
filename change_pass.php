<?php
// CREATED BY HARSH (12/02/2024)
include "header.php";
if (isset($_REQUEST["update"])) {
    $oldPass = $_REQUEST["oldPass"];
    $newPass = $_REQUEST["newPass"];
    $confNewPass = $_REQUEST["confNewPass"];
    $username = $_SESSION["username"];
    
    $stmt1 = $obj->con1->prepare("SELECT `password` FROM `admin` WHERE `username`=?");
    $stmt1->bind_param("s", $username);
    $stmt1->execute();
    $result = $stmt1->get_result();
    $row=$result->fetch_assoc();
    $pass = $row["password"];
    $stmt1->close();

    if ($row['password']==$oldPass) {
        if ($newPass == $confNewPass && $newPass != $oldPass && $oldPass == $pass) {
            try {
                $stmt = $obj->con1->prepare("UPDATE `admin` SET `password`=? WHERE username=?");
                $stmt->bind_param("ss", $confNewPass, $username);
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
                header("location:change_pass.php");
            } else {
                setcookie("msg", "fail", time() + 3600, "/");
                header("location:change_pass.php");
            }
        }
        else{
            setcookie("change_pass", "not_match", time() + 3600, "/");
            header("location:change_pass.php");
        }        
    }
    else{
        setcookie("change_pass", "incorrect_pass", time() + 3600, "/");
        header("location:change_pass.php");
    }
}
?>

<div class='p-6'>
    <div class="flex gap-6 items-center pb-8">
        <h1 class="dark:text-white-dar text-2xl font-bold">Change Password</h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div>
                    <label for="oldPass">Old Password</label>
                    <input id="oldPass" name="oldPass" placeholder="Enter Old Password" type="text" class="form-input" value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>
                <div>
                    <label for="newPass">New Password</label>
                    <input id="newPass" name="newPass" placeholder="Enter New Password" type="text" min="0" class="form-input" value="<?php echo (isset($mode)) ? $data['link'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>
                <div>
                    <label for="confNewPass">Confirm New Password</label>
                    <input id="confNewPass" name="confNewPass" placeholder="Enter New Password Again" type="text" min="0" class="form-input" value="<?php echo (isset($mode)) ? $data['link'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>

                <div class="relative inline-flex align-middle gap-3 mt-4">
                    <button type="submit" name='update' id="update" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    checkCookies();
    // if(readCookie("change_pass") == "not_match"){
    //     coloredToast("danger", 'Password Does Not Match');
    //     //eraseCookie("change_pass");
    // }
    function go_back() {
        history.back();
    }

</script>

<?php
include "footer.php";
?>