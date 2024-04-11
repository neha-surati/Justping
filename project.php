<?php
// By Nidhi
include "header.php";
if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
    $project_id = $_REQUEST["project_id"];
    $project_img = $_REQUEST["project_img"];
    try {
        $stmt_subimg = $obj->con1->prepare("SELECT * FROM `project_images` WHERE p_id=?");
        $stmt_subimg->bind_param("i",$project_id);
        $stmt_subimg->execute();
        $Resp_subimg = $stmt_subimg->get_result();
        $stmt_subimg->close();

        while($row_subimg = mysqli_fetch_array($Resp_subimg)){
            if(file_exists("images/project/".$row_subimg["p_sub_img"])){
                unlink("images/project/".$row_subimg["p_sub_img"]);  
            }
        }

        $stmt_subimg_del = $obj->con1->prepare("DELETE FROM `project_images` WHERE p_id=?");
        $stmt_subimg_del->bind_param("i",$project_id);
        $Resp_subimg_del = $stmt_subimg_del->execute();
        $stmt_subimg_del->close();

        $stmt_del = $obj->con1->prepare("DELETE FROM `project` WHERE p_id=?");
        $stmt_del->bind_param("i",$project_id);
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
        if(file_exists("images/project/".$project_img)){
            unlink("images/project/".$project_img);  
        }
        setcookie("msg", "data_del", time() + 3600, "/");
    }
    else{
        setcookie("msg", "fail", time() + 3600, "/");
    }
    header("location:project.php");
}
?>

<div class='p-6 animate__animated' x-data='pagination'>
<h1 class="dark:text-white-dar  pb-8 text-3xl font-bold">Project</h1>
    <div class="panel mt-6 flex items-center  justify-between relative">
        
        <button type="button" class="p-2 btn btn-primary m-1 add-btn" onclick="javascript:insertdata()">
        <i class="ri-add-line mr-1"></i> Add New Project</button>
               
        <table id="myTable" class="table-hover whitespace-nowrap w-full"></table>
    </div>
   
</div>

</div>

<script>
checkCookies();

function getActions(id,img) {
    return `<ul class="flex items-center gap-4">
        <li>
            <a href="javascript:add_subimages(`+id+`);" class='text-xl' x-tooltip="Add">
            <i class="ri-add-line text text-success"></i>
            </a>
        </li>
        <li>
            <a href="javascript:viewdata(`+id+`);" class='text-xl' x-tooltip="View">
                <i class="ri-eye-line text-primary"></i>
            </a>
        </li>
        <li>
            <a href="javascript:editdata(`+id+`);" class='text-xl' x-tooltip="Edit">
                <i class="ri-pencil-line text text-success"></i>
            </a>
        </li>
        <li>
            <a href="javascript:showAlert(`+id+`,\'`+img+`\');" class='text-xl' x-tooltip="Delete">
                <i class="ri-delete-bin-line text-danger"></i>
            </a>
        </li>
    </ul>`
}

document.addEventListener('alpine:init', () => {
    Alpine.data('pagination', () => ({
        datatable: null,
        init() {
            this.datatable = new simpleDatatables.DataTable('#myTable', {
                data: {
                    headings: ['Sr.No.', 'Image', 'Title', 'Category', 'Status', 'Project Status', 'Add As Banner', 'Action'],
                    data: [
                        <?php
                        $stmt = $obj->con1->prepare("SELECT p.*, GROUP_CONCAT(c.c_name) AS category_names FROM project AS p JOIN category AS c ON FIND_IN_SET(c.c_id, p.p_category) > 0 GROUP BY p.p_id ORDER BY p.p_id desc");
                        $stmt->execute();
                        $Resp = $stmt->get_result();
                        $i = 1;
                        while ($row = mysqli_fetch_array($Resp)) { ?>
                        [
                            <?php echo $i; ?>, 
                            '<img src="images/project/<?php echo addslashes($row["p_image"]); ?>" height="200" width="200" class="object-cover shadow rounded">',
                            '<?php echo addslashes($row["p_name"]); ?>',
                            '<?php echo addslashes($row["category_names"]); ?>',
                            '<span class="badge whitespace-nowrap" :class="{\'badge-outline-success\': \'<?php echo $row["p_status"]; ?>\' === \'enable\', \'badge-outline-danger\': \'<?php echo $row["p_status"]; ?>\' === \'disable\'}"><?php echo $row["p_status"]; ?></span>',
                            '<span class="badge whitespace-nowrap" :class="{\'badge-outline-success\': \'<?php echo $row["p_recent"]; ?>\' === \'0\', \'badge-outline-danger\': \'<?php echo $row["p_recent"]; ?>\' === \'1\'}"><?php if($row["p_recent"]=='1'){ echo 'Completed'; }else if($row["p_recent"]=='0'){ echo 'Ongoing'; } ?></span>',
                            '<span class="badge whitespace-nowrap" :class="{\'badge-outline-success\': \'<?php echo $row["add_as_banner"]; ?>\' === \'1\', \'badge-outline-danger\': \'<?php echo $row["add_as_banner"]; ?>\' === \'0\'}"><?php if($row["add_as_banner"]=='1'){ echo 'Yes'; }else if($row["add_as_banner"]=='0'){ echo 'No'; } ?></span>',
                            
                            getActions(<?php echo $row["p_id"]; ?>,'<?php echo addslashes($row["p_image"]); ?>')
                        ],
                        <?php $i++;}
                        ?>
                    ],
                },
                perPage: 10,
                perPageSelect: [10, 20, 30, 50, 100],
                columns: [{
                    select: 0,
                    sort: 'asc',
                }, ],
                firstLast: true,
                firstText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M13 19L7 12L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M16.9998 19L10.9998 12L16.9998 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
                lastText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M11 19L17 12L11 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M6.99976 19L12.9998 12L6.99976 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
                prevText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M15 5L9 12L15 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
                nextText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
                labels: {
                    perPage: '{select}',
                },
                layout: {
                    top: '{search}',
                    bottom: "<div class='flex items-center gap-4'>{info}{select}</div>{pager}",
                },
            });
        },

        // exportTable(eType) {
        //     var data = {
        //         type: eType,
        //         filename: 'table',
        //         download: true,
        //     };

        //     if (data.type === 'csv') {
        //         data.lineDelimiter = '\n';
        //         data.columnDelimiter = ';';
        //     }
        //     this.datatable.export(data);
        // },

        printTable() {
            this.datatable.print();
        },

        formatDate(date) {
            if (date) {
                const dt = new Date(date);
                const month = dt.getMonth() + 1 < 10 ? '0' + (dt.getMonth() + 1) : dt.getMonth() +
                    1;
                const day = dt.getDate() < 10 ? '0' + dt.getDate() : dt.getDate();
                return day + '/' + month + '/' + dt.getFullYear();
            }
            return '';
        },
    }));
})

function insertdata(id){
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "add_project.php";
}

function editdata(id){
    createCookie("edit_id",id,1);
    window.location = "add_project.php";
}

function viewdata(id){
    createCookie("view_id",id,1);
    window.location = "add_project.php";
}

function add_subimages(id){
    createCookie("edit_id",id,1);
    window.location = "add_project_subimages.php";
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
            var loc = "project.php?flg=del&project_id="+id+"&project_img="+img;
            window.location = loc;
        }
    });
}
</script>

<?php include "footer.php";
?>