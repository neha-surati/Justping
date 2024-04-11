<?php
// BY ARYA
include "header.php";

$stmt = $obj->con1->prepare("SELECT * FROM `career`");
$stmt->execute();
$Resp = $stmt->get_result();
$stmt->close();
if ($Resp->num_rows > 0) {
    $data = $Resp->fetch_assoc();
    $mode = 'edit';
}



if (isset($_REQUEST['update'])) {
    // $desc = $_REQUEST["description"];
    $img = $_FILES["demo2"]["name"];
    $img = str_replace(' ', '_', $img);
    $img_path = $_FILES["demo2"]["tmp_name"];
    $oldImg = $_REQUEST['h_img'];
    if ($img != "") {
        if (file_exists("images/career/" . $img)) {
            $i = 0;
            $PicFileName = $img;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/career/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $img;
        }
        unlink("images/career/" . $oldImg);
        move_uploaded_file($img_path, "images/career/" . $PicFileName);
    } else {
        $PicFileName = $oldImg;
    }
    try {
        $stmt = $obj->con1->prepare("UPDATE `career` SET `career_image`=?");
        $stmt->bind_param("s", $PicFileName);
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
        header("location:career.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:career.php");
    }
    exit();
}
if (isset($_REQUEST["save"])) {
    // $desc = $_REQUEST["description"];
    $img = $_FILES["demo2"]["name"];
    $img = str_replace(' ', '_', $img);
    $img_path = $_FILES['demo2']['tmp_name'];
    if ($img != "") {
        if (file_exists("images/career/" . $img)) {
            $i = 0;
            $PicFileName = $img;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/career/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $img;
        }
    }
    try {
        $stmt = $obj->con1->prepare("INSERT INTO `career`(`career_image`) VALUES (?)");
        $stmt->bind_param("s", $PicFileName);
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
        move_uploaded_file($img_path, "images/career/" . $PicFileName);
        setcookie("msg", "data", time() + 3600, "/");
        header("location:career.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:career.php");
    }
}

?>

<div class='p-6'>
    <div class="flex gap-6 items-center pb-8">
        <!-- <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span> -->
        <h1 class="dark:text-white-dar text-3xl font-bold">Career Image</h1>
    </div>
    <div class="panel mt-6">


        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">

                <div>
                    <label for="image">Image</label>
                    <input id="demo2" class="demo1" type="file" data_btn_text="Browse"
                        placeholder="drag and drop file here" name="demo2" onchange="readURL(this,'PreviewImage')" />
                </div>

                <div>
                    <h4 class="font-bold text-primary mt-2  mb-3"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
                    <img src="<?php echo (isset($mode)) ? 'images/career/' . $data["career_image"] : '' ?>"
                        name="PreviewImage" id="PreviewImage" width="400" height="400"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>" class="object-cover shadow rounded">

                    <div id="imgdiv" style="color:red"></div>
                    <input type="hidden" name="h_img" id="h_img"
                        value="<?php echo (isset($data)) ? $data["career_image"] : '' ?>" />
                </div>

        </div>


        <div class="relative inline-flex align-middle gap-3 mt-4 ">
            <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
                class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>" <?php echo isset($mode) ? '' : 'onclick="return checkImage()"' ?>>
                Save
            </button>
            <button type="button" class="btn btn-danger"
                onclick="<?php echo (isset($mode)) ? 'javascript:go_back()' : 'window.location.reload()' ?>">Close</button>
        </div>
        </form>
    </div>
</div>
<script>
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
        eraseCookie("viewId");
        eraseCookie("updateId");
        history.back();
    }
</script>
<?php
include "footer.php";
?>