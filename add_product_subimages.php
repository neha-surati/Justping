<?php
include "header.php";

$product_id = isset($_COOKIE['edit_id']) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];


$stmt_id = $obj->con1->prepare("SELECT name FROM product WHERE id = ?");
$stmt_id->bind_param('i', $product_id);
$stmt_id->execute();
$data_id = $stmt_id->get_result()->fetch_assoc();
$stmt_id->close();
// echo "name = ".$data_id["name"];


if (isset($_COOKIE['edit_subimg_id'])) {
    $mode = 'edit';
    $editId = $_COOKIE['edit_subimg_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `product_image` WHERE image_id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE['view_subimg_id'])) {
    $mode = 'view';
    $viewId = $_COOKIE['view_subimg_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `product_image` WHERE image_id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["btn_submit"])) {
    try {
       
        foreach ($_FILES["product_img"]['name'] as $key => $value) {
               
            if ($_FILES["product_img"]['name'][$key] != "") {
                $PicSubImage = $_FILES["product_img"]["name"][$key];
                if (file_exists("images/product_images/" . $PicSubImage)) {
                    $i = 0;
                    $SubImageName = $PicSubImage;
                    $Arr = explode('.', $SubImageName);
                    $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    while (file_exists("images/product_images/" . $SubImageName)) {
                        $i++;
                        $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    }
                } else {
                    $SubImageName = $PicSubImage;
                }
                $SubImageTemp = $_FILES["product_img"]["tmp_name"][$key];
                $SubImageName = str_replace(' ', '_', $SubImageName);

                // sub images qry
                move_uploaded_file($SubImageTemp, "images/product_images/" . $SubImageName);

                // echo "INSERT INTO `product_image`(`product_id`, `image`) VALUES ('".$product_id."','".$SubImageName."')";
                $stmt_image = $obj->con1->prepare("INSERT INTO `product_image`(`product_id`, `image`) VALUES (?,?)");
                $stmt_image->bind_param("is", $product_id, $SubImageName);
                $Resp = $stmt_image->execute();
                $stmt_image->close();
            }
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
        header("location:add_product.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:add_product.php");
    }
}

if (isset($_REQUEST["btn_update"])) {
    $product_img_one = $_FILES['product_img_one']['name'];
    $product_img_one = str_replace(' ', '_', $product_img_one);
    $product_img_one_path = $_FILES['product_img_one']['tmp_name'];
    $old_img = $_REQUEST['old_img'];
    $id = $_COOKIE['edit_subimg_id'];

    //rename file for event image
    if ($product_img_one != "") {
        if (file_exists("images/product_images/" . $product_img_one)) {
            $i = 0;
            $PicFileName = $product_img_one;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/product_images/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $product_img_one;
        }
        unlink("images/product_images/" . $old_img);
        move_uploaded_file($product_img_one_path, "images/product_images/" . $PicFileName);
    } else {
        $PicFileName = $old_img;
    }

    try {
        $stmt = $obj->con1->prepare("UPDATE `product_image` SET `image`=? WHERE `image_id`=?");
        $stmt->bind_param("si", $PicFileName, $id);
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
        header("location:add_product.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:add_product.php");
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
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?> Product Images
        </h1>
    </div>

    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">

                <div>
                    <label for="product_name">Product Name</label>
                    <input id="product_name" name="product_name" type="text" class="form-input" required value="<?= $data_id["name"] ?>" placeholder="Enter Product" readonly />
                </div>

                <div <?php echo (isset($mode)) ? 'hidden' : '' ?>>
                    <label for="image">Image</label>
                    <input id="product_img" class="demo1" type="file" name="product_img[]" multiple data_btn_text="Browse" onchange="readURL_multiple(this)" placeholder="drag and drop file here" multiple />
                    <div id="preview_image_div"></div>
                    <div id="imgdiv_multiple" style="color:red"></div>
                </div>

                <div <?php echo (isset($mode) && $mode == 'edit') ? '' : 'hidden' ?>>
                    <label for="image">Image</label>
                    <input id="product_img_one" class="demo1" type="file" name="product_img_one" data_btn_text="Browse" onchange="readURL(this,'PreviewImage')" placeholder="drag and drop file here" />
                </div>
                <div <?php echo (isset($mode)) ? '' : 'hidden' ?>>
                    <h4 class="font-bold text-primary mt-2  mb-3" style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
                    <img src="<?php echo (isset($mode)) ? 'images/product_images/' . $data["image"] : '' ?>" name="PreviewImage" id="PreviewImage" width="400" height="400" style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>" class="object-cover shadow rounded">
                    <div id="imgdiv" style="color:red"></div>
                    <input type="hidden" name="old_img" id="old_img" value="<?php echo (isset($mode) && $mode == 'edit') ? $data["image"] : '' ?>" />
                </div>

                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btn_submit' ?>" id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>" <?php echo isset($mode) ? '' : 'onclick="return checkImage()"' ?>>
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
        eraseCookie("edit_subimg_id");
        eraseCookie("view_subimg_id");
        window.location = "add_product.php";
    }

    function readURL(input, preview) {
        if (input.files && input.files[0]) {
            var filename = input.files.item(0).name;

            var reader = new FileReader();
            var extn = filename.split(".");

            if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp") {
                reader.onload = function(e) {
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

    function readURL_multiple(input) {
        $('#preview_image_div').html("");
        var filesAmount = input.files.length;
        for (i = 0; i <= filesAmount; i++) {
            if (input.files && input.files[i]) {

                var filename = input.files.item(i).name;
                var reader = new FileReader();
                var extn = filename.split(".");
                if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp" || extn[1].toLowerCase() == "mp4" || extn[1].toLowerCase() == "webm" || extn[1].toLowerCase() == "ogg") {
                    if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp") {
                        reader.onload = function(e) {
                            $('#preview_image_div').append('<img src="' + e.target.result + '" name="PreviewImage' + i + '" id="PreviewImage' + i + '" width="400" height="400" class="object-cover shadow rounded" style="display:inline-block; margin:2%;">');
                        };
                    } else if (extn[1].toLowerCase() == "mp4" || extn[1].toLowerCase() == "webm" || extn[1].toLowerCase() == "ogg") {
                        reader.onload = function(e) {
                            $('#preview_image_div').append('<video src="' + e.target.result + '" name="PreviewVideo' + i + '" id="PreviewVideo' + i + '" width="400" height="400" style="display:inline-block" class="object-cover shadow rounded" controls></video>');
                        }
                    }

                    reader.readAsDataURL(input.files[i]);
                    $('#imgdiv_multiple').html("");
                    document.getElementById('save').disabled = false;
                } else {
                    $('#imgdiv_multiple').html("Please Select Image Or Video Only");
                    document.getElementById('save').disabled = true;
                }
            }
        }
    }
</script>

<?php
include "footer.php";
?>