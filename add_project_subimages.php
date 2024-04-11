<?php
// by nidhi
include "header.php";

$project_id = isset($_COOKIE['edit_id']) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
;

if (isset($_COOKIE['edit_subimg_id'])) {
    $mode = 'edit';
    $editId = $_COOKIE['edit_subimg_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `project_images` WHERE p_img_id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE['view_subimg_id'])) {
    $mode = 'view';
    $viewId = $_COOKIE['view_subimg_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `project_images` WHERE p_img_id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["btn_submit"])) {
    $status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? '1' : '0';
    $priority = 0;

    $stmt = $obj->con1->prepare("SELECT max(priority) as priority FROM `project_images` WHERE p_id=?");
    $stmt->bind_param('i', $project_id);
    $stmt->execute();
    $data = $stmt->get_result();
    $stmt->close();
    if (mysqli_num_rows($data) > 0) {
        $res = mysqli_fetch_array($data);
        $priority = $res['priority'];
    }

    try {
        // multiple estate images 
        foreach ($_FILES["project_img"]['name'] as $key => $value) {
            // rename for estate images       
            if ($_FILES["project_img"]['name'][$key] != "") {
                $PicSubImage = $_FILES["project_img"]["name"][$key];
                if (file_exists("images/project/" . $PicSubImage)) {
                    $i = 0;
                    $SubImageName = $PicSubImage;
                    $Arr = explode('.', $SubImageName);
                    $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    while (file_exists("images/project/" . $SubImageName)) {
                        $i++;
                        $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    }
                } else {
                    $SubImageName = $PicSubImage;
                }
                $SubImageTemp = $_FILES["project_img"]["tmp_name"][$key];
                $SubImageName = str_replace(' ', '_', $SubImageName);

                // sub images qry
                move_uploaded_file($SubImageTemp, "images/project/" . $SubImageName);
            }

            $stmt_image = $obj->con1->prepare("INSERT INTO `project_images`(`p_id`, `p_sub_img`, `priority`, `add_as_banner`) VALUES (?,?,?,?)");
            $stmt_image->bind_param("isis", $project_id, $SubImageName, $priority, $status);
            $Resp = $stmt_image->execute();
            $stmt_image->close();

            $priority++;
        }

        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data", time() + 3600, "/");
        header("location:add_project.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:add_project.php");
    }
}

if (isset($_REQUEST["btn_update"])) {
    $status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? '1' : '0';
    $project_img_one = $_FILES['project_img_one']['name'];
    $project_img_one = str_replace(' ', '_', $project_img_one);
    $project_img_one_path = $_FILES['project_img_one']['tmp_name'];
    $old_img = $_REQUEST['old_img'];
    $id = $_COOKIE['edit_subimg_id'];

    //rename file for category image
    if ($project_img_one != "") {
        if (file_exists("images/project/" . $project_img_one)) {
            $i = 0;
            $PicFileName = $project_img_one;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/project/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $project_img_one;
        }
        unlink("images/project/" . $old_img);
        move_uploaded_file($project_img_one_path, "images/project/" . $PicFileName);
    } else {
        $PicFileName = $old_img;
    }

    try {
        $stmt = $obj->con1->prepare("UPDATE `project_images` set `p_id`=?, `p_sub_img`=?, `add_as_banner`=? where p_img_id=?");
        $stmt->bind_param("issi", $project_id, $PicFileName, $status, $id);
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
        setcookie("edit_subimg_id", "", time() - 3600, "/");
        setcookie("msg", "update", time() + 3600, "/");
        header("location:add_project.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:add_project.php");
    }
}
?>

<div class='p-6'>
    <div class="flex gap-6 items-center pb-8">
        <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span>
        <h1 class="dark:text-white-dar text-2xl font-bold">
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?> Project Images
        </h1>
    </div>

    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            name="status" <?php echo (isset($mode) && $data['add_as_banner'] == '1') ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>>
                        <span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>

                <div <?php echo (isset($mode)) ? 'hidden' : '' ?>>
                    <label for="image">Image</label>
                    <input id="project_img" class="demo1" type="file" name="project_img[]" multiple
                        data_btn_text="Browse" onchange="readURL_multiple(this)" placeholder="drag and drop file here"
                        multiple />
                    <div id="preview_image_div"></div>
                    <div id="imgdiv_multiple" style="color:red"></div>
                </div>

                <div <?php echo (isset($mode) && $mode == 'edit') ? '' : 'hidden' ?>>
                    <label for="image">Image</label>
                    <input id="project_img_one" class="demo1" type="file" name="project_img_one" data_btn_text="Browse"
                        onchange="readURL(this,'PreviewImage')" placeholder="drag and drop file here" />
                </div>
                <div <?php echo (isset($mode)) ? '' : 'hidden' ?>>
                    <h4 class="font-bold text-primary mt-2  mb-3"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
                    <img src="<?php echo (isset($mode)) ? 'images/project/' . $data["p_sub_img"] : '' ?>"
                        name="PreviewImage" id="PreviewImage" width="400" height="400"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>"
                        class="object-cover shadow rounded">
                    <div id="imgdiv" style="color:red"></div>
                    <input type="hidden" name="old_img" id="old_img"
                        value="<?php echo (isset($mode) && $mode == 'edit') ? $data["p_sub_img"] : '' ?>" />
                </div>

                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit"
                        name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btn_submit' ?>" id="save"
                        class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>" <?php echo isset($mode) ? '' : 'onclick="return checkImage()"' ?>>
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
    checkCookies();

    function go_back() {
        eraseCookie("edit_subimg_id");
        eraseCookie("view_subimg_id");
        window.location = "add_project.php";
    }

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

    function readURL_multiple(input) {
        $('#preview_image_div').html("");
        var filesAmount = input.files.length;
        for (i = 0; i <= filesAmount; i++) {
            if (input.files && input.files[i]) {

                var filename = input.files.item(i).name;
                var reader = new FileReader();
                var extn = filename.split(".");

                if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp") {
                    reader.onload = function (e) {
                        $('#preview_image_div').append('<img src="' + e.target.result + '" name="PreviewImage' + i + '" id="PreviewImage' + i + '" width="400" height="400" class="object-cover shadow rounded" style="display:inline-block; margin:2%;">');
                    };

                    reader.readAsDataURL(input.files[i]);
                    $('#imgdiv_multiple').html("");
                    document.getElementById('save').disabled = false;
                }
                else {
                    $('#imgdiv_multiple').html("Please Select Image Only");
                    document.getElementById('save').disabled = true;
                }
            }
        }
    }
</script>

<?php
include "footer.php";
?>