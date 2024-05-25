<?php
include "db_connect.php";
$obj = new DB_Connect();
date_default_timezone_set('Asia/Kolkata');
session_start();

if (isset($_REQUEST['save'])) {
    $userName = $_REQUEST['username'];
    $password = $_REQUEST['password'];

    $stmt = $obj->con1->prepare("SELECT * FROM `admin` WHERE username=? AND BINARY password=?");
    $stmt->bind_param("ss", $userName, $password);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $admin_data = $Resp->fetch_assoc();
    $stmt->close();

    if ($admin_data) {
        $_SESSION['type_admin'] = true;
        $_SESSION['admin_username'] = $admin_data['username'];
        $_SESSION['admin_name'] = $admin_data['name'];
        $_SESSION['id'] = $admin_data['id'];
        setcookie("msg", "login", time() + 3600, "/");
        header("location:banner.php");
        exit;
    } else {
        echo "in vendor";
        echo "SELECT * FROM `vendor_reg` WHERE email='".$userName."' AND BINARY password='".$password."'";
        $stmt = $obj->con1->prepare("SELECT * FROM `vendor_reg` WHERE email=? AND BINARY password=?");
        $stmt->bind_param("ss", $userName, $password);
        $stmt->execute();
        $Resp = $stmt->get_result();
        $vendor_reg_data = $Resp->fetch_assoc();
        $stmt->close();

        if($vendor_reg_data){
            $_SESSION['type_vendor'] = true;
            $_SESSION['username'] = $vendor_reg_data['email'];
            $_SESSION['name'] = $vendor_reg_data['name'];
            $_SESSION['id'] = $vendor_reg_data['id'];
            
            setcookie("msg", "sc_login", time() + 3600, "/");
            header("location:banner.php");
        } else {
            setcookie("msg", "wrong_cred", time() + 3600, "/");
            header("location:index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Justping - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="favicon.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" type="text/css" media="screen" href="assets/css/perfect-scrollbar.min.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="assets/css/style.css" />
    <link defer rel="stylesheet" type="text/css" media="screen" href="assets/css/animate.css" />
    <script src="assets/js/mainScript.js"></script>
    <script src="assets/js/perfect-scrollbar.min.js"></script>
    <script defer src="assets/js/popper.min.js"></script>
    <script defer src="assets/js/tippy-bundle.umd.min.js"></script>
    <script src="assets/js/sweetalert.min.js"></script>
</head>

<body x-data="main" class="relative overflow-x-hidden font-nunito text-sm font-normal antialiased"
    :class="[$store.app.sidebar ? 'toggle-sidebar' : '', $store.app.theme === 'dark' || $store.app.isDarkMode ?  'dark' : '', $store.app.menu, $store.app.layout,$store.app.rtlClass]">

    <div class="main-container min-h-screen text-black dark:text-white-dark">
        <!-- start main content section -->
        <div
            class="flex min-h-screen items-center justify-center bg-[url('../images/map.svg')] bg-cover bg-center dark:bg-[url('../images/map-dark.svg')]">
            <div class="panel m-6 w-full max-w-lg sm:w-[480px] shadow-3xl">
                <div class="border-2 border-primary opacity-70 rounded-full"></div>
                <h2 class="mb-3 mt-5 text-3xl font-bold">Sign In</h2>
                <p class="mb-7">Enter your Username and password to login</p>
                <form class="space-y-5" method="post">
                    <div>
                        <label for="username" class="font-bold">Username</label>
                        <input id="username" name="username" type="text" class="form-input" placeholder="Enter Username"
                            required />
                    </div>
                    <div>
                        <label for="password" class="font-bold">Password</label>
                        <input id="password" name="password" type="password" class="form-input"
                            placeholder="Enter Password" required />
                    </div>
                    <button type="submit" name="save" class="btn btn-primary w-full">SIGN IN</button>
                </form>
            </div>
        </div>
        <!-- end main content section -->
    </div>

    <script src="assets/js/alpine-collaspe.min.js"></script>
    <script src="assets/js/alpine-persist.min.js"></script>
    <script defer src="assets/js/alpine-ui.min.js"></script>
    <script defer src="assets/js/alpine-focus.min.js"></script>
    <script defer src="assets/js/alpine.min.js"></script>
    <script src="assets/js/custom.js"></script>

    <script>
        checkCookies();
        // main section
        document.addEventListener('alpine:init', () => {
            Alpine.data('scrollToTop', () => ({
                showTopButton: false,
                init() {
                    window.onscroll = () => {
                        this.scrollFunction();
                    };
                },

                scrollFunction() {
                    if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
                        this.showTopButton = true;
                    } else {
                        this.showTopButton = false;
                    }
                },
            }));
        });
    </script>
</body>

</html>