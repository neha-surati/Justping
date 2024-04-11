<?php
// CREATED BY HARSH (10/02/2024)
include "header.php";
if (isset($_COOKIE["view_id"])) {
    $mode = 'view';
    $viewId = $_COOKIE["view_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `testimonial` where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE["edit_id"])) {
    $mode = 'edit';
    $editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `testimonial` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $img = $_FILES['image']['name'];
    $img = str_replace(' ', '_', $img);
    $img_path = $_FILES['image']['tmp_name'];
    $name = $_REQUEST["name"];
    $desig = $_REQUEST["desig"];
    $review = $_REQUEST["review"];

    if ($_FILES['image']['name'] != "") {
        if (file_exists("images/testimonial_img/" . $img)) {
            $i = 0;
            $imgFileName = $_FILES['image']['name'];
            $Arr1 = explode('.', $imgFileName);

            $imgFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/testimonial_img/" . $imgFileName)) {
                $i++;
                $imgFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $imgFileName = $_FILES['image']['name'];
        }
    }

    try {
        $stmt = $obj->con1->prepare(
            "INSERT INTO `testimonial`(`image`,`name`,`designation`,`review`) VALUES (?,?,?,?)"
        );
        $stmt->bind_param("ssss", $imgFileName, $name, $desig, $review);
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
        move_uploaded_file($img_path, "images/testimonial_img/" . $imgFileName);
        setcookie("msg", "data", time() + 3600, "/");
        header("location:testimonial.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:testimonial.php");
    }
}

if (isset($_REQUEST["update"])) {
    $img = $_FILES['image']['name'];
    $img = str_replace(' ', '_', $img);
    $img_path = $_FILES['image']['tmp_name'];
    $name = $_REQUEST["name"];
    $desig = $_REQUEST["desig"];
    $review = $_REQUEST["review"];
    $editId = $_COOKIE['edit_id'];
    $old_imgP = $_REQUEST["h_img"];

    if ($_FILES['image']['name'] != "") {
        if (file_exists("images/testimonial_img/" . $img)) {
            $i = 0;
            $imgFileName = $_FILES['image']['name'];
            $Arr1 = explode('.', $imgFileName);

            $imgFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/testimonial_img/" . $imgFileName)) {
                $i++;
                $imgFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $imgFileName = $_FILES['image']['name'];
        }
        unlink("images/testimonial_img/" . $old_imgP);
    } else {
        $imgFileName = $old_imgP;
    }
    move_uploaded_file($img_path, "images/testimonial_img/" . $imgFileName);

    try {
        echo "UPDATE `testimonial` SET image=$imgFileName, name=$name, designation=$desig, review=$review WHERE id=$editId";
        $stmt = $obj->con1->prepare(
            "UPDATE `testimonial` SET image=?, name=?, designation=?, review=? WHERE id=?"
        );
        $stmt->bind_param("ssssi", $imgFileName, $name, $desig, $review, $editId);
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
        setcookie("edit_id", "", time() - 3600, "/");
        header("location:testimonial.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        setcookie("edit_id", "", time() - 3600, "/");
        header("location:testimonial.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">Testimonial-
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">
                <div>
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" placeholder="Write Name" class="form-input"
                        value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>

                <div>
                    <label for="desig">Designation</label>
                    <input id="desig" name="desig" type="text" min="0" placeholder="Write Designation"
                        class="form-input" value="<?php echo (isset($mode)) ? $data['designation'] : '' ?>" required
                        <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>

                <div>
                    <label for="review">Review</label>
                    <input id="review" name="review" type="text" min="0" placeholder="Write Review" class="form-input"
                        value="<?php echo (isset($mode)) ? $data['review'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>

                <div>
                    <label for="image">Image</label>
                    <input id="image" class="demo1" type="file" data_btn_text="Browse"
                        placeholder="drag and drop file here" name="image" onchange="readURL(this,'PreviewImage')" />
                </div>

                <div>
                    <h4 class="font-bold text-primary mt-2  mb-3"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
                    <img src="<?php echo (isset($mode)) ? 'images/testimonial_img/' . $data["image"] : '' ?>"
                        name="PreviewImage" id="PreviewImage" width="400" height="400"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>"
                        class="object-cover shadow rounded">
                    <div id="imgdiv" style="color:red"></div>
                    <input type="hidden" name="h_img" id="h_img"
                        value="<?php echo (isset($data)) ? $data["image"] : '' ?>" />
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
        window.location = "testimonial.php";

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
            } else {
                $('#imgdiv').html("Please Select Image Only");
                document.getElementById('save').disabled = true;
            }
        }
    }
</script>


<?php
include "footer.php";
?>