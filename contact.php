<?php
// CREATED BY HARSH (09/02/2024)
include "header.php";
if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
    try {
        $stmt_del = $obj->con1->prepare(
            "delete from contact where id='" .
                $_REQUEST["contactid"] .
                "'"
        );
        $Resp = $stmt_del->execute();
        if (!$Resp) {
            if (
                strtok($obj->con1->error, ":") == "Cannot delete or update a parent row"
            ) {
                throw new Exception("City is already in use!");
            }
        }
        $stmt_del->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data_del", time() + 3600, "/");
    }
    header("location:contact.php");
}
?>

<div class='p-6 animate__animated' x-data='pagination'>
    <h1 class="dark:text-white-dar  pb-8 text-3xl font-bold">Contact</h1>
    <div class="panel mt-6 flex items-center  justify-between relative">
        <table id="myTable" class="table-hover whitespace-nowrap w-full"></table>
    </div>

</div>

<!-- script -->
<script>
    checkCookies();

    function getActions(id) {
        return `<ul class="flex items-center gap-4">
        <li>
            <a href="javascript:;" class='text-xl' x-tooltip="Delete" @click="showAlert(` + id + `)">
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
                        headings: ['Sr.No.', 'Name', 'E-Mail', 'Phone', 'Message','Actions'],
                        data: [
                            <?php
                            $stmt = $obj->con1->prepare(
                                "SELECT * FROM `contact` order by id desc"
                            );
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) {
                            ?>[
                                    <?php echo $i; ?>,
                                    '<?php echo addslashes($row["name"]); ?>',
                                    '<?php echo addslashes($row["email"]); ?>',
                                    '<?php echo addslashes($row["phone"]); ?>',
                                    '<?php echo addslashes($row["msg"]); ?>',
                                    getActions(<?php echo $row["id"]; ?>)
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


    async function showAlert(id) {
        new window.Swal({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            showCancelButton: true,
            confirmButtonText: 'Delete',
            padding: '2em',
        }).then((result) => {
            if (result.isConfirmed) {
                var loc = "contact.php?flg=del&contactid=" + id;
                window.location = loc;
            }
        });
    }
</script>

<?php include "footer.php";
?>