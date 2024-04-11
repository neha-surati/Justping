<?php
// CREATED BY HARSH (10/02/2024)
include "header.php";
if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
    $testimonial_img = $_REQUEST['testimonial_img'];
    try {
        $stmt_del = $obj->con1->prepare("delete from testimonial where id='" .$_REQUEST["testimonialid"] ."'");
        $Resp = $stmt_del->execute();
        if (!$Resp) {
            if (
                strtok($obj->con1->error, ":") == "Cannot delete or update a parent row"
            ) {
                throw new Exception("Testimonial is already in use!");
            }
        }
        $stmt_del->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        if(file_exists("images/testimonial_img/".$testimonial_img)){
            unlink("images/testimonial_img/".$testimonial_img);  
        }
        setcookie("msg", "data_del", time() + 3600, "/");
    }
    header("location:testimonial.php");
}
?>

<div class='p-6 animate__animated' x-data='pagination'>
    <h1 class="dark:text-white-dar  pb-8 text-3xl font-bold">Testimonial</h1>
    <div class="panel mt-6 flex items-center  justify-between relative">
        <button type="button" class="p-2 btn btn-primary m-1 add-btn" onclick="javascript:insertdata()">
            <i class="ri-add-line mr-1"></i> Add New Testimonial</button>
        <table id="myTable" class="table-hover whitespace-nowrap w-full"></table>
    </div>

</div>

<script>
    checkCookies();

    function getActions(id,img) {
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
                        headings: ['Sr.No.', 'Image', 'Name', 'Designation', 'Actions'],
                        data: [
                            <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `testimonial` order by id desc");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) {
                            ?>[
                                    <?php echo $i; ?>,
                                    '<img src="images/testimonial_img/<?php echo addslashes($row["image"]); ?>" height="200" width="200" class="object-cover shadow rounded"/>',
                                    '<?php echo addslashes($row["name"]); ?>',
                                    '<?php echo addslashes($row["designation"]); ?>',
                                    getActions(<?php echo $row["id"]; ?>,'<?php echo addslashes($row["image"]); ?>')
                                ],
                            <?php
                                $i++;
                            }
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
        window.location = "add_testimonial.php";
    }

    function editdata(id) {
        createCookie("edit_id", id, 1);
        window.location = "add_testimonial.php";
    }

    function viewdata(id) {
        createCookie("view_id", id, 1);
        window.location = "add_testimonial.php";
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
                var loc = "testimonial.php?flg=del&testimonialid="+id+"&testimonial_img="+img;
                window.location = loc;
            }
        });
    }
</script>

<?php include "footer.php";
?>