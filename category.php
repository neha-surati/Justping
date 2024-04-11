<?php
// By Nidhi
include "header.php";
if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
    $category_img = $_REQUEST["category_img"];
    try {
        $stmt_del = $obj->con1->prepare("DELETE FROM `category` WHERE c_id='" .$_REQUEST["category_id"] ."'");
        $Resp = $stmt_del->execute();
        if (!$Resp) {
            if (strtok($obj->con1->error, ":") == "Cannot delete or update a parent row"
            ) {
                throw new Exception("Category is already in use!");
            }
        }
        $stmt_del->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        if(file_exists("images/category/".$category_img)){
            unlink("images/category/".$category_img);  
        }
        setcookie("msg", "data_del", time() + 3600, "/");
    }
    else{
        setcookie("msg", "fail", time() + 3600, "/");
    }
    header("location:category.php");
}
?>

<div class="animate__animated p-6" :class="[$store.app.animation]">
    <div x-data="basic">
        <div class="flex gap-6 items-center pb-8">
            <h1 class="dark:text-white-dar  pb-8 text-3xl font-bold">Categories</h1>
        </div>
        <div class="panel space-y-8"> 
            <div class="flex gap-6 items-center pb-8 <?php echo (isset($mode) && $mode=='view')? 'hidden':''?>">
                <button type="button" class="p-2 btn btn-primary m-1 add-btn" onclick="javascript:insertdata()"><i class="ri-add-line mr-1"></i> Add New Category</button>
            </div>
            <div>
                <div id="cat_id_values" x-text="id_array" style="display:none;"></div>
                <ul id="example1">   
                    <template x-for="item in items">
                        <li class="mb-2.5 cursor-grab">
                            <div
                                class="items-md-center flex flex-col rounded-md border border-white-light bg-white px-6 py-3.5 text-center dark:border-dark dark:bg-[#1b2e4b] md:flex-row ltr:md:text-left rtl:md:text-right"
                            >
                            
                                <div class="flex flex-1 flex-col items-center justify-between md:flex-row">
                                    <div class="my-3 font-semibold md:my-0">
                                        <div class="text-base text-dark dark:text-[#bfc9d4]" x-text="item.srno"></div>
                                    </div>
                                    <div class="my-3 font-semibold md:my-0">
                                        <div class="text-base text-dark dark:text-[#bfc9d4]" x-text="item.title"></div>
                                    </div>
                                    <div class="my-3 font-semibold md:my-0">
                                        <img x-bind:src="'images/category/'+item.image" height="200" width="200" class="object-cover shadow rounded">
                                    </div>
                                    <div class="my-3 font-semibold md:my-0">
                                        <span class="badge whitespace-nowrap" :class="{'badge-outline-success': item.status === 'enable', 'badge-outline-danger': item.status === 'disable'}" x-text="item.status"></span>
                                    </div>
                                    <div>
                                        <ul class="flex items-center gap-4">
                                        <li>
                                            <a x-bind:onclick="'javascript:viewdata('+item.id+');'" class='text-xl' x-tooltip="View">
                                                <i class="ri-eye-line text-primary"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a x-bind:onclick="'javascript:editdata('+item.id+');'" class='text-xl' x-tooltip="Edit">
                                                <i class="ri-pencil-line text text-success"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a x-bind:onclick="'javascript:showAlert('+item.id+',\''+item.image+'\');'" class='text-xl' x-tooltip="Delete">
                                                <i class="ri-delete-bin-line text-danger"></i>
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


<script>
checkCookies();

    // for sortable drag and drop
    var example1 = document.getElementById('example1');
    var sortable = Sortable.create(example1, {
        animation: 200,
        ghostClass: 'gu-transit',
        group: 'shared',
        onEnd: function(/**Event*/evt) {
            old_index = evt.oldIndex;
            new_index = evt.newIndex;
            
            cat_id_values = document.getElementById("cat_id_values").innerHTML;
            cat_id_array = cat_id_values.split(',');
            sortedList = moveArrayElement(cat_id_array, old_index, new_index);
            
            $.ajax({
                async: false,
                type: "POST",
                url: "ajaxdata.php?action=updateCategoryList",
                data: "sortedList="+sortedList,
                cache: false,
                success: function(result){
                    if(result){
                        document.getElementById("cat_id_values").innerHTML = sortedList;
                        coloredToast("success", 'Record Updated Successfully.');
                    }
                    else{
                        coloredToast("danger", 'Some Error Occured.');
                    }
                }
            });
        }
    });

    // for sortable drag and drop
    document.addEventListener('alpine:init', () => {
        Alpine.data('basic', () => ({
            items: [
            <?php
                    $cat_id = array();

                    $stmt = $obj->con1->prepare("SELECT * FROM `category` order by c_priority");
                    $stmt->execute();
                    $Resp = $stmt->get_result();
                    $stmt->close();
                    $i = 1;
                    while ($row = mysqli_fetch_array($Resp)) {
                        $cat_id[] = $row['c_id'];
            ?>
                {
                    srno: <?php echo $i; ?>,
                    id: '<?php echo addslashes($row["c_id"]); ?>',
                    title: '<?php echo addslashes($row["c_name"]); ?>',
                    status: '<?php echo addslashes($row["c_status"]); ?>',
                    image: '<?php echo addslashes($row["c_image"]); ?>',
                },
            <?php 
                    $i++; 
                } 
                $cat_id_values = implode(",",$cat_id);
            ?>
            ],
            id_array : '<?php echo $cat_id_values; ?>',
        }));
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

function insertdata(id){
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "add_project_category.php";
}

function editdata(id){
    createCookie("edit_id",id,1);
    window.location = "add_project_category.php";
}

function viewdata(id){
    createCookie("view_id",id,1);
    window.location = "add_project_category.php";
}

async function showAlert(id,img) {
    new window.Swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        showCancelButton: true,
        confirmButtonText: 'Delete',
        padding: '2em',
    }).then((result) => {
        if (result.isConfirmed) {
            var loc = "category.php?flg=del&category_id="+id+"&category_img="+img;
            window.location = loc;
        }
    });
}
</script>

<?php include "footer.php";
?>