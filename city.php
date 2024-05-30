<?php
include "header.php";

setcookie("editId", "", time() - 3600);
setcookie("viewId", "", time() - 3600);

if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
    try {
        $stmt_del = $obj->con1->prepare("DELETE FROM city WHERE id = ?");
        $stmt_del->bind_param("i", $_REQUEST["id"]);
        $Resp = $stmt_del->execute();
        if (!$Resp) {
            if (strtok($obj->con1->error, ":") == "Cannot delete or update a parent row") {
                throw new Exception("City is already in use!");
            }
        }
        $stmt_del->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
        setcookie("msg", "cant_delete", time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data_del", time() + 3600, "/");
    }
    header("location:city.php");
}
?>
<div class='p-6 animate__animated' x-data='pagination'>
	<h1 class="dark:text-white-dar  pb-8 text-3xl font-bold">City</h1>
	<div class="panel mt-6 flex items-center  justify-between relative">

		<button type="button" class="p-2 btn btn-primary m-1 add-btn" onclick="javascript:add_data()">
			<i class="ri-add-line mr-1"></i> Add City</button>

			<table id="myTable" class="table-hover whitespace-nowrap w-full"></table> </div>

        </div>
        <script type="text/javascript">
          checkCookies();
          eraseCookie("editId");
          eraseCookie("viewId");
          function getActions(id) {
            return `<ul class="flex items-center gap-4">
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
            <a href="javascript:showAlert(`+id+`);" class='text-xl' x-tooltip="Delete">
            <i class="ri-delete-bin-line text-danger"></i>
            </a>
            </li>
            </ul>`
        }
        document.addEventListener('alpine:init', () => {
            Alpine.data('pagination', () => ({
                datatable: null,
                init() {
                    this.datatable = new simpleDatatables.DataTable('#myTable',{
                        data: {
                            headings: ['Sr.No.','City Name','State Name','Status', 'Action'],
                            data: [
                                <?php
                                $stmt = $obj->con1->prepare("SELECT c1.*, s1.state_name FROM `city` c1, `state` s1 WHERE c1.state_id=s1.id;");
                                $stmt->execute();
                                $Resp = $stmt->get_result();
                                $i = 1;
                                while ($row = mysqli_fetch_array($Resp)) { ?>
                                    [
                                        <?php echo $i; ?>, 
                                        '<?php echo $row["city_name"]; ?>',
                                        '<?php echo $row["state_name"]; ?>',
                                        '<span class="badge whitespace-nowrap" :class="{\'badge-outline-success\': \'<?php echo $row["status"]; ?>\' === \'Enable\', \'badge-outline-danger\': \'<?php echo $row["status"]; ?>\' === \'Disable\'}"><?php echo $row["status"]; ?></span>',
                                        getActions(<?php echo $row["id"];?>)
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
function add_data(){
    eraseCookie("editId");
    eraseCookie("viewId");
    window.location = "add_city.php";
}
function editdata(id){
    createCookie("editId",id,1);
    window.location = "add_city.php";
}

function viewdata(id){
    createCookie("viewId",id,1);
    window.location = "add_city.php";
}


async function showAlert(id,blog_img) {
   new window.Swal({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    showCancelButton: true,
    confirmButtonText: 'Delete',
    padding: '2em',
}).then((result) => {
    if (result.isConfirmed) {
     var loc = "city.php?flg=del&id=" +id;
     window.location = loc;
 }
});
}
</script>

<?php
include "footer.php"
?>