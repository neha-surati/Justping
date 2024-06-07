<?php 
    include "header.php";

	if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
	$id = $_REQUEST['id'];
   

    try {
        $stmt_del = $obj->con1->prepare("DELETE FROM `rating` WHERE id=?");
        $stmt_del->bind_param("i",$id);
        $Resp = $stmt_del->execute();
        if (!$Resp) {
           throw new Exception("Problem in deleting! ". strtok($obj->con1-> error,  '('));
       }
       $stmt_del->close();
   } catch (\Exception $e) {
      setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
  }

  if ($Resp) {
  setcookie("msg", "data_del", time() + 3600, "/");
} 
header("location:rating.php");
}
?>
<div class='p-6 animate__animated' x-data='pagination'>
    <h1 class="dark:text-white-dar  pb-8 text-3xl font-bold">Rating</h1>
    <div class="panel mt-6 flex items-center  justify-between relative">

        <!-- <button type="button" class="p-2 btn btn-primary m-1 add-btn" onclick="javascript:insertdata()">
            <i class="ri-add-line mr-1"></i> Add Customer</button> -->

        <table id="myTable" class="table-hover whitespace-nowrap w-full"></table>
    </div>

</div>

<script>
checkCookies();
function getActions(id) {
    return `<ul class="flex items-center gap-4">
        <li>
            <a href="javascript:viewdata(` + id + `);" class='text-xl' x-tooltip="View">
                <i class="ri-eye-line text-primary"></i>
            </a>
        </li>
        <li>
            <a href="javascript:editdata(` + id + `);" class='text-xl' x-tooltip="Edit">
                <i class="ri-pencil-line text text-success"></i>
            </a>
        </li>
        <li>
            <a href="javascript:showAlert(` + id + `);" class='text-xl' x-tooltip="Delete">
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
                    headings: ['Sr.No.', 'Vendor','Rate',
                        'Product', 'Review','Customer','Status','Action'
                    ],
                    data: [
                        <?php
                                $stmt = $obj->con1->prepare("SELECT r1.*, v1.name as vname, CONCAT(c1.firstname, ' ', c1.lastname) as cname, p1.name as pname
                                FROM `rating` r1
                                JOIN `vendor_reg` v1 ON r1.v_id = v1.id
                                JOIN `customer_reg` c1 ON r1.cust_id = c1.id
                                JOIN `product` p1 ON r1.p_id = p1.id;
                                ");
                                $stmt->execute();
                                $Resp = $stmt->get_result();
                                $i = 1;
                                while ($row = mysqli_fetch_array($Resp)) { ?>
                                [
                            <?php echo $i; ?>,
                            '<?php echo addslashes ($row["vname"]); ?>',
                            '<?php echo addslashes ($row["rate"]); ?>',
                            '<?php echo addslashes ($row["pname"]); ?>',
                            '<?php echo addslashes ($row["review"]); ?>',
                            '<?php echo addslashes ($row["cname"]); ?>',
                            '<span class="badge whitespace-nowrap" :class="{\'badge-outline-success\': \'<?php echo $row["stats"]; ?>\' === \'Enable\', \'badge-outline-danger\': \'<?php echo $row["stats"]; ?>\' === \'Disable\'}"><?php echo $row["stats"]; ?></span>',
                            getActions(<?php echo $row["id"];?>,
                                )
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

        exportTable(eType) {
            var data = {
                type: eType,
                filename: 'table',
                download: true,
            };

            if (data.type === 'csv') {
                data.lineDelimiter = '\n';
                data.columnDelimiter = ';';
            }
            this.datatable.export(data);
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

// function insertdata(id) {
//     eraseCookie("edit_id");
//     eraseCookie("view_id");
//     window.location = "add_customer_reg.php";
// }

function editdata(id) {
    eraseCookie("view_id");
    createCookie("edit_id", id, 1);
    window.location = "rating_detail.php";
}

function viewdata(id) {
    eraseCookie("edit_id");
    createCookie("view_id", id, 1);
    window.location = "rating_detail.php";
}

async function showAlert(id) {
    new window.Swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        showCancelButton: true,
        confirmButtonText: 'Delete',
        padding: '2em',
    }).then((result) => {
        if (result.isConfirmed) {
            var loc = "rating.php?flg=del&id=" + id ;
            window.location = loc;
        }
    });
}
</script>
<?php
	include "footer.php";
?>