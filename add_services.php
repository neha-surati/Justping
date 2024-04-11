<?php
//By Arya
include "header.php";
if (isset($_COOKIE["viewId"])) {
    // $img = $_REQUEST['img'];
    $mode = 'view';
    $viewId = $_COOKIE["viewId"];
    $stmt = $obj->con1->prepare("SELECT * FROM `services` where s_id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}
if (isset($_COOKIE["updateId"])) {
    // $img = $_REQUEST['img'];
    $mode = 'edit';
    $viewId = $_COOKIE["updateId"];
    $stmt = $obj->con1->prepare("SELECT * FROM `services` where s_id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}
if (isset($_REQUEST['update'])) {
    $editId = $_COOKIE['updateId'];
    $name = $_REQUEST["name"];
    $desc = $_REQUEST["description"];
    $s_status = (isset($_REQUEST["s_status"])) ? "enable" : "disable";
    $img = $_FILES["demo2"]["name"];
    $img = str_replace(' ', '_', $img);
    $img_path = $_FILES["demo2"]["tmp_name"];
    $oldImg = $_REQUEST['h_img'];
    if ($img != "") {
        if (file_exists("images/services/" . $img)) {
            $i = 0;
            $PicFileName = $img;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/services/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $img;
        }
        unlink("images/services/" . $oldImg);
        move_uploaded_file($img_path, "images/services/" . $PicFileName);
    } else {
        $PicFileName = $oldImg;
    }
    // echo $PicFileName;
    try {
        // echo "UPDATE `services` SET `name`='" . $name . "',`s_description`='" . $desc . "',`s_image`='" . $img . "',`s_status`='" . $s_status . "' WHERE `s_id`='" . $editId . "'";
        $stmt = $obj->con1->prepare("UPDATE `services` SET `name`=?,`s_description`=?,`s_image`=?,`s_status`=? WHERE `s_id`=?");
        $stmt->bind_param("ssssi", $name, $desc, $PicFileName, $s_status, $editId);
        $Res = $stmt->execute();
        $stmt->close();
        if (!$Resp) {
            throw new Exception(
                "Problem in updating! " . strtok($obj->con1->error, "(")
            );
        }
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }
    if ($Res) {
        setcookie("msg", "update", time() + 3600, "/");
        setcookie("updateId", "", time() - 100, "/");
        header("location:services.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:services.php");
    }
    exit();
}
if (isset($_REQUEST["save"])) {
    $name = $_REQUEST["name"];
    $desc = $_REQUEST["description"];
    $s_status = (isset($_REQUEST["s_status"])) ? "enable" : "disable";
    $img = $_FILES["demo2"]["name"];
    $img = str_replace(' ', '_', $img);
    $img_path = $_FILES['demo2']['tmp_name'];
    if ($img != "") {
        if (file_exists("images/services/" . $img)) {
            $i = 0;
            $PicFileName = $img;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/services/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $img;
        }
    }
    try {
        $stmt = $obj->con1->prepare("INSERT INTO `services`(`name`, `s_description`, `s_image`, `s_status`) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name, $desc, $PicFileName, $s_status);
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
        move_uploaded_file($img_path, "images/services/" . $PicFileName);
        setcookie("msg", "data", time() + 3600, "/");
        header("location:services.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:services.php");
    }
}



function uploadImage($inputName, $uploadDirectory)
{
    if (isset($_FILES[$inputName]) && isset($_FILES[$inputName]["name"])) {
        $fileName = $_FILES[$inputName]["name"];
        $tmpFilePath = $_FILES[$inputName]["tmp_name"];

        if ($fileName != "") {
            $targetDirectory = $uploadDirectory . "/";

            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }

            $i = 0;
            $newFileName = $fileName;

            while (file_exists($targetDirectory . $newFileName)) {
                $i++;
                $newFileName = $i . "_" . $fileName;
            }

            $targetFilePath = $targetDirectory . $newFileName;
            return $newFileName;
        }
    }

    return null;
}





?>

<div class='p-6'>
    <div class="flex gap-6 items-center pb-8">
        <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span>
        <h1 class="dark:text-white-dar text-3xl font-bold">Services-
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">


        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">
                <div>
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" class="form-input"
                        value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>



                <div class="mb-4">
                    <label for="quill">Description</label>
                    <div id="editor1">
                        <?php echo (isset($mode)) ? $data['s_description'] : '' ?>
                    </div>
                </div>
                <input type="hidden" id="description" name="description">


                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer"
                            id="s_status" name="s_status" <?php echo isset($mode) && $data['s_status'] == 'enable' ? 'checked' : '' ?>><span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>
                <div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
                    <label for="image">Image</label>
                    <input id="demo2" class="demo1" type="file" data_btn_text="Browse"
                        placeholder="drag and drop file here" name="demo2" onchange="readURL(this,'PreviewImage')" />
                </div>
                <div>
                    <h4 class="font-bold text-primary mt-2  mb-3"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
                    <img src="<?php echo (isset($data)) ? 'images/services/' . $data["s_image"] : '' ?>"
                        name="PreviewImage" id="PreviewImage" width="400" height="400"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>"
                        class="object-cover shadow rounded">
                    <div id="imgdiv" style="color:red"></div>
                    <input type="hidden" name="h_img" id="h_img"
                        value="<?php echo (isset($data)) ? $data["s_image"] : '' ?>" />
                </div>

        </div>

        <div class="relative inline-flex align-middle gap-3 mt-4 ">
            <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
                class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>"
                onclick="return setQuillInput() ">
                <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
            </button>
            <button type="button" class="btn btn-danger"
                onclick="<?php echo (isset($mode)) ? 'javascript:go_back()' : 'window.location.reload()' ?>">Close</button>
        </div>
        </form>
    </div>
</div>
<script>
    function readURL(input, preview) {
        if (input.files && input.files[0]) {
            var filename = input.files.item(0).name;

            var reader = new FileReader();
            var extn = filename.split(".");

            if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp") {
                reader.onload = function (e) {
                    $('#' + preview).attr('src', e.target.result);
                    document.getElementById(preview).style.display = "block";
                };

                reader.readAsDataURL(input.files[0]);
                $('#imgdiv').html("");
                document.getElementById('save').disabled = false;
            }
            else {
                $('#imgdiv').html("Please Select Image Only");
                document.getElementById('save').disabled = true;
            }
        }
    }

    function go_back() {
        eraseCookie("viewId");
        eraseCookie("updateId");
        var loc = "services.php";
        window.location = loc;
    }

    var quill = new Quill('#editor1', {
        theme: 'snow',
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

    function setQuillInput() {
        let quillInput = document.getElementById("description");
        quillInput.value = quill.root.innerHTML;

        // let quillInput2 = document.getElementById("quill-input2");
        // quillInput2.value = quill2.root.innerHTML;

        let val1 = quillInput.value.replace(/<[^>]*>/g, '');
        // let val2 = quillInput2.value.replace(/<[^>]*>/g, '');

        if (val1.trim() == '') {
            coloredToast("danger", 'Please add something in Description.');
            return false;
        }
        <?php if(!isset($mode)){ ?>
         else if (<?php echo (!isset($mode))?true:false ?>) {
            return checkImage();
        } 
        <?php } ?> 
        else {
            return true;
        }
    }
</script>
<?php
include "footer.php";
?>