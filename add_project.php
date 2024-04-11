<?php
// By Nidhi
include "header.php";

if (isset($_COOKIE['edit_id'])) {
    $mode = 'edit';
    $editId = $_COOKIE['edit_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `project` where p_id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE['view_id'])) {
    $mode = 'view';
    $viewId = $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `project` where p_id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["btn_submit"])) {
    $title = $_REQUEST["title"];
    $status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'enable' : 'disable';
    $recent = (isset($_REQUEST["recent"]) && $_REQUEST["recent"] == 'on') ? '1' : '0';
    $add_as_banner = (isset($_REQUEST["add_as_banner"]) && $_REQUEST["add_as_banner"] == 'on') ? '1' : '0';
    $category = $_REQUEST["category"];
    $category_val = implode(",", $category);
    $description = $_REQUEST["description"];
    $description = $_REQUEST["description"];
    $project_img = $_FILES['project_img']['name'];
    $project_img = str_replace(' ', '_', $project_img);
    $project_img_path = $_FILES['project_img']['tmp_name'];

    //rename file for project image
    if ($project_img != "") {
        if (file_exists("images/project/" . $project_img)) {
            $i = 0;
            $PicFileName = $project_img;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/project/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $project_img;
        }
    }

    try {
        $stmt = $obj->con1->prepare("INSERT INTO `project`(`p_name`, `p_image`, `p_description`, `p_category`, `p_recent`, `add_as_banner`, `p_status`) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssss", $title, $PicFileName, $description, $category_val, $recent, $add_as_banner, $status);
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
        move_uploaded_file($project_img_path, "images/project/" . $PicFileName);
        setcookie("msg", "data", time() + 3600, "/");
        header("location:project.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:project.php");
    }
}

if (isset($_REQUEST["btn_update"])) {
    $title = $_REQUEST["title"];
    $status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'enable' : 'disable';
    $recent = (isset($_REQUEST["recent"]) && $_REQUEST["recent"] == 'on') ? '1' : '0';
    $add_as_banner = (isset($_REQUEST["add_as_banner"]) && $_REQUEST["add_as_banner"] == 'on') ? '1' : '0';
    $category = $_REQUEST["category"];
    $category_val = implode(",", $category);
    $description = $_REQUEST["description"];
    $project_img = $_FILES['project_img']['name'];
    $project_img = str_replace(' ', '_', $project_img);
    $project_img_path = $_FILES['project_img']['tmp_name'];
    $old_img = $_REQUEST['old_img'];
    $id = $_COOKIE["edit_id"];

    //rename file for project image
    if ($project_img != "") {
        if (file_exists("images/project/" . $project_img)) {
            $i = 0;
            $PicFileName = $project_img;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/project/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $project_img;
        }
        unlink("images/project/" . $old_img);
        move_uploaded_file($project_img_path, "images/project/" . $PicFileName);
    } else {
        $PicFileName = $old_img;
    }

    try {
        $stmt = $obj->con1->prepare("UPDATE `project` set `p_name`=?, `p_image`=?, `p_description`=?, `p_category`=?, `p_recent`=?, `add_as_banner`=?, `p_status`=? where p_id=?");
        $stmt->bind_param("sssssssi", $title, $PicFileName, $description, $category_val, $recent, $add_as_banner, $status, $id);
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
        header("location:project.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:project.php");
    }
}

if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
    $project_subimg = $_REQUEST["project_subimg"];
    try {
        $stmt_del = $obj->con1->prepare("DELETE FROM `project_images` WHERE p_img_id='" . $_REQUEST["sub_img_id"] . "'");
        $Resp = $stmt_del->execute();
        if (!$Resp) {
            if (
                strtok($obj->con1->error, ":") == "Cannot delete or update a parent row"
            ) {
                throw new Exception("Image is already in use!");
            }
        }
        $stmt_del->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        if (file_exists("images/project/" . $project_subimg)) {
            unlink("images/project/" . $project_subimg);
        }
        setcookie("msg", "data_del", time() + 3600, "/");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
    }
    header("location:add_project.php");
}
?>
<div class='p-6'>
    <div class="flex gap-6 items-center pb-8">
        <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span>
        <h1 class="dark:text-white-dar text-2xl font-bold">Project -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">
                <div>
                    <label for="title">Title</label>
                    <input id="title" name="title" type="text" class="form-input" required
                        value="<?php echo (isset($mode)) ? $data['p_name'] : '' ?>" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                </div>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
                            name="status" <?php echo (isset($mode) && $data['p_status'] == 'enable') ? 'checked' : '' ?>
                            <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>>
                        <span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Recent</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="recent"
                            name="recent" <?php echo (isset($mode) && $data['p_recent'] == '1') ? 'checked' : '' ?> <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>>
                        <span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Add As Banner</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox"
                            class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer"
                            id="add_as_banner" name="add_as_banner" <?php echo (isset($mode) && $data['add_as_banner'] == '1') ? 'checked' : '' ?> <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>>
                        <span
                            class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>
                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Category</label>
                    <select name="category[]" id="category" class="selectize" multiple='multiple' required>
                        <?php
                        if (isset($mode)) {
                            $category_id = $data['p_category'];
                            $cat_ids = explode(",", $category_id);
                        }

                        $stmt = $obj->con1->prepare("SELECT c_id, c_name FROM `category` WHERE c_status='enable'");
                        $stmt->execute();
                        $Resp = $stmt->get_result();
                        $i = 1;
                        while ($row = mysqli_fetch_array($Resp)) {
                            ?>
                            <option value="<?php echo $row['c_id'] ?>" <?php echo (isset($mode) && in_array($row['c_id'], $cat_ids)) ? 'selected' : '' ?>     <?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>>
                                <?php echo $row['c_name'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="quill">Description</label>
                    <div id="editor1">
                        <?php echo (isset($mode)) ? $data['p_description'] : ''; ?>
                    </div>
                </div>
                <input type="hidden" id="quill-input" name="description">

                <div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
                    <label for="image">Image</label>
                    <input id="project_img" class="demo1" type="file" name="project_img" data_btn_text="Browse"
                        onchange="readURL(this,'PreviewImage')" placeholder="drag and drop file here" />
                </div>
                <div>
                    <h4 class="font-bold text-primary mt-2  mb-3"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
                    <img src="<?php echo (isset($mode)) ? 'images/project/' . $data["p_image"] : '' ?>" name="PreviewImage"
                        id="PreviewImage" width="400" height="400"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>" class="object-cover shadow rounded">
                    <div id="imgdiv" style="color:red"></div>
                    <input type="hidden" name="old_img" id="old_img"
                        value="<?php echo (isset($mode) && $mode == 'edit') ? $data["p_image"] : '' ?>" />
                </div>

                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit"
                        name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btn_submit' ?>" id="save"
                        class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>"
                        onclick="return setQuillInput()">
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <button type="button" class="btn btn-danger"
                        onclick="<?php echo (isset($mode)) ? 'javascript:go_back()' : 'window.location.reload()' ?>">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($mode)) { ?>
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div x-data="basic">
            <div class="flex gap-6 items-center pb-8">
                <h1 class="dark:text-white-dar text-2xl font-bold">Project Images</h1>
            </div>
            <div class="panel space-y-8">
                <div class="flex gap-6 items-center pb-8 <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>">
                    <button type="button" name="btn_add_img" id="btn_add_img" class="p-2 btn btn-primary m-1 add-btn"
                        onclick="javascript:insertdata()">
                        <i class="ri-add-line mr-1"></i> Add New Project</button>
                </div>
                <div>
                    <div id="sub_img_values" x-text="id_array" style="display:none;"></div>
                    <ul id="example1">
                        <template x-for="item in items">
                            <li class="mb-2.5 cursor-grab">
                                <div
                                    class="items-md-center flex flex-col rounded-md border border-white-light bg-white px-6 py-3.5 text-center dark:border-dark dark:bg-[#1b2e4b] md:flex-row ltr:md:text-left rtl:md:text-right">

                                    <div class="flex flex-1 flex-col items-center justify-between md:flex-row">
                                        <div class="my-3 font-semibold md:my-0">
                                            <div class="text-base text-dark dark:text-[#bfc9d4]" x-text="item.srno"></div>
                                        </div>
                                        <div class="my-3 font-semibold md:my-0">
                                            <img x-bind:src="'images/project/'+item.image" height="200" width="200"
                                                class="object-cover shadow rounded">
                                        </div>
                                        <div class="my-3 font-semibold md:my-0">
                                            <span class="badge whitespace-nowrap"
                                                :class="{'badge-outline-success': item.status === 'enable', 'badge-outline-danger': item.status === 'disable'}"
                                                x-text="item.status"></span>
                                        </div>
                                        <div>
                                            <ul class="flex items-center gap-4">
                                                <li>
                                                    <a x-bind:onclick="'javascript:viewdata('+item.id+');'" class='text-xl'
                                                        x-tooltip="View">
                                                        <i class="ri-eye-line text-primary"></i>
                                                    </a>
                                                </li>
                                                <li <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
                                                    <a x-bind:onclick="'javascript:editdata('+item.id+');'" class='text-xl'
                                                        x-tooltip="Edit">
                                                        <i class="ri-pencil-line text text-success"></i>
                                                    </a>
                                                </li>
                                                <li <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
                                                    <a x-bind:onclick="'javascript:showAlert('+item.id+',\''+item.image+'\');'"
                                                        class='text-xl' x-tooltip="Delete">
                                                        <i class="ri-delete-bin-line text-danger"></i>
                                                    </a>
                                                </li>
                                                <li <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
                                                    <a x-bind:onclick="'javascript:changeDefaultImage('+item.id+');'"
                                                        class='text-xl'>
                                                        <span class="badge whitespace-nowrap badge-outline-primary">Set As
                                                            Default</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<script>
    checkCookies();

    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "project.php";
    }

    function insertdata(id){
        eraseCookie("edit_subimg_id");
        eraseCookie("view_subimg_id");
        window.location = "add_project_subimages.php";
    }

    function editdata(id) {
        createCookie("edit_subimg_id", id, 1);
        window.location = "add_project_subimages.php";
    }

    function viewdata(id) {
        createCookie("view_subimg_id", id, 1);
        window.location = "add_project_subimages.php";
    }

    async function showAlert(id, img) {
        new window.Swal({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            showCancelButton: true,
            confirmButtonText: 'Delete',
            padding: '2em',
        }).then((result) => {
            if (result.isConfirmed) {
                var loc = "add_project.php?flg=del&sub_img_id=" + id + "&project_subimg=" + img;
                window.location = loc;
            }
        });
    }

    function changeDefaultImage(id) {
        $.ajax({
            async: false,
            type: "POST",
            url: "ajaxdata.php?action=updateDefaultImage",
            data: "subimg_id=" + id,
            cache: false,
            success: function (result) {
                if (result == 1) {
                    createCookie("msg", "data", 1);
                    window.location = "add_project.php";
                    coloredToast("success", 'Record Updated Successfully.');
                }
                else {
                    createCookie("msg", "fail", 1);
                    coloredToast("danger", 'Some Error Occured.');
                }
            }
        });
    }

    // to get value of quill 
    function setQuillInput() {
        let quillInput = document.getElementById("quill-input");
        quillInput.value = quill.root.innerHTML;

        let val = quillInput.value.replace(/<[^>]*>/g, '');

        if (val.trim() == '') {
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

    // for preview image
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

    // for multiple select dropdown
    document.addEventListener('DOMContentLoaded', function (e) {
        // default
        // var els = document.querySelectorAll('.selectize');
        // els.forEach(function (select) {
        //     NiceSelect.bind(select);
        // });

        // seachable
        var options = {
            searchable: true,
        };
        NiceSelect.bind(document.getElementById('category'), options);
    });

    // for quill
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

    <?php echo (isset($mode) && $mode == 'view') ? 'quill.enable(false);' : '' ?>

    <?php if (isset($mode)) { ?>
        // for sortable drag and drop
        var example1 = document.getElementById('example1');
        var sortable = Sortable.create(example1, {
            animation: 200,
            ghostClass: 'gu-transit',
            group: 'shared',
            onEnd: function (/**Event*/evt) {
                old_index = evt.oldIndex;
                new_index = evt.newIndex;

                sub_img_values = document.getElementById("sub_img_values").innerHTML;
                sub_img_array = sub_img_values.split(',');

                sortedList = moveArrayElement(sub_img_array, old_index, new_index);

                $.ajax({
                    async: false,
                    type: "POST",
                    url: "ajaxdata.php?action=updateSortedList",
                    data: "sortedList=" + sortedList,
                    cache: false,
                    success: function (result) {
                        if (result) {
                            document.getElementById("sub_img_values").innerHTML = sortedList;
                            coloredToast("success", 'Record Updated Successfully.');
                        }
                        else {
                            coloredToast("danger", 'Some Error Occured.');
                        }
                    }
                });
            }
        });

        function moveArrayElement(arr, oldIndex, newIndex) {
            // Adjust negative indices to positive indices
            while (oldIndex < 0) {
                oldIndex += arr.length;
            }
            while (newIndex < 0) {
                newIndex += arr.length;
            }

            // If newIndex is beyond the array length, extend the array with undefined elements
            if (newIndex >= arr.length) {
                const numToAdd = newIndex - arr.length + 1;
                while (numToAdd--) {
                    arr.push(undefined);
                }
            }

            // Remove the element at oldIndex and insert it at newIndex
            const element = arr.splice(oldIndex, 1)[0];
            arr.splice(newIndex, 0, element);
            return arr;
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('basic', () => ({
                items: [
                    <?php

                    $id = ($mode == 'edit') ? $editId : $viewId;
                    $subimg_id = array();

                    $stmt_img = $obj->con1->prepare("SELECT * FROM `project_images` WHERE p_id=? order by priority");
                    $stmt_img->bind_param('i', $id);
                    $stmt_img->execute();
                    $img_data = $stmt_img->get_result();
                    $stmt_img->close();
                    $i = 1;
                    $sub_img_values = "";
                    while ($res_img = mysqli_fetch_array($img_data)) {
                        $subimg_id[] = $res_img['p_img_id'];
                        ?>
                        {
                            srno: <?php echo $i; ?>,
                            id: '<?php echo $res_img["p_img_id"]; ?>',
                            status: '<?php echo ($res_img["add_as_banner"] == '1') ? 'enable' : 'disable'; ?>',
                            image: '<?php echo addslashes($res_img["p_sub_img"]); ?>',
                        },
                        <?php $i++;
                    }
                    $sub_img_values = implode(",", $subimg_id);
                    ?>
                ],
                id_array: '<?php echo $sub_img_values; ?>',
            }));
        });
    <?php } ?>
</script>

<?php
include "footer.php";
?>