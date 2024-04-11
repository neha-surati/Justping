<?php
// by Nidhi
include "header.php";

if (isset($_COOKIE['edit_id'])) {
    $mode = 'edit';
    $editId = $_COOKIE['edit_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `category` where c_id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE['view_id'])) {
    $mode = 'view';
    $viewId = $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `category` where c_id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["btn_submit"])) {
    $title = $_REQUEST["title"];
    $status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'enable' : 'disable';
    $category_img = $_FILES['category_img']['name'];
    $category_img = str_replace(' ', '_', $category_img);
    $category_img_path = $_FILES['category_img']['tmp_name'];

    $priority = 1;
    $stmt = $obj->con1->prepare("SELECT MAX(c_priority) as c_priority FROM `category`");
    $stmt->execute();
    $data = $stmt->get_result();
    $stmt->close();
    if (mysqli_num_rows($data) > 0) {
        $res = mysqli_fetch_array($data);
        $priority = ($res['c_priority'] + 1);
    }

    //rename file for category image
    if ($category_img != "") {
        if (file_exists("images/category/" . $category_img)) {
            $i = 0;
            $PicFileName = $category_img;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/category/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $category_img;
        }
    }

    try {
        $stmt = $obj->con1->prepare("INSERT INTO `category`(`c_name`, `c_image`, `c_status`, `c_priority`) VALUES (?,?,?,?)");
        $stmt->bind_param("sssi", $title, $PicFileName, $status, $priority);
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
        move_uploaded_file($category_img_path, "images/category/" . $PicFileName);
        setcookie("msg", "data", time() + 3600, "/");
        header("location:category.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:category.php");
    }
}

if (isset($_REQUEST["btn_update"])) {
    $title = $_REQUEST["title"];
    $status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'enable' : 'disable';
    $category_img = $_FILES['category_img']['name'];
    $category_img = str_replace(' ', '_', $category_img);
    $category_img_path = $_FILES['category_img']['tmp_name'];
    $old_img = $_REQUEST['old_img'];
    $id = $_COOKIE['edit_id'];

    //rename file for category image
    if ($category_img != "") {
        if (file_exists("images/category/" . $category_img)) {
            $i = 0;
            $PicFileName = $category_img;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/category/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $category_img;
        }
        unlink("images/category/" . $old_img);
        move_uploaded_file($category_img_path, "images/category/" . $PicFileName);
    } else {
        $PicFileName = $old_img;
    }

    try {
        $stmt = $obj->con1->prepare("UPDATE `category` set `c_name`=?, `c_image`=?, `c_status`=? where c_id=?");
        $stmt->bind_param("sssi", $title, $PicFileName, $status, $id);
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
        setcookie("edit_id", "", time() - 3600, "/");
        setcookie("msg", "update", time() + 3600, "/");
        header("location:category.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:category.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">Category-
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>

    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">
                <div>
                    <label for="title">Title</label>
                    <input id="title" name="title" type="text" class="form-input" required
                        value="<?php echo (isset($mode)) ? $data['c_name'] : '' ?>" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            name="status" <?php echo (isset($mode) && $data['c_status'] == 'enable') ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>>
                        <span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>
                <div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
                    <label for="image">Image</label>
                    <input id="category_img" class="demo1" type="file" name="category_img" data_btn_text="Browse"
                        onchange="readURL(this,'PreviewImage')" placeholder="drag and drop file here" />
                </div>
                <div>
                    <h4 class="font-bold text-primary mt-2  mb-3"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
                    <img src="<?php echo (isset($mode)) ? 'images/category/' . $data["c_image"] : '' ?>"
                        name="PreviewImage" id="PreviewImage" width="400" height="400"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>"
                        class="object-cover shadow rounded">
                    <div id="imgdiv" style="color:red"></div>
                    <input type="hidden" name="old_img" id="old_img"
                        value="<?php echo (isset($mode) && $mode == 'edit') ? $data["c_image"] : '' ?>" />
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
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "category.php";
    }


</script>

<?php
include "footer.php";
?>