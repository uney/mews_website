<?php
    ob_start();
    session_start();
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MEWS Redirect</title>

        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
                      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
                    <![endif]-->

        <style>
            body {
                padding-top: 50px;
            }
            .starter-template {
                padding: 40px 15px;
                text-align: center;
            }
        </style>

    </head>

    <body>

        <div class="container">

            <div class="starter-template">
                <?php
                    $msg = '';
                    $redirectTo = "index.php";
                    if (isset($_GET['action'])) {
                        if ($_GET['action'] == 'logout') {
                            session_unset();
                            setcookie("user_id", "", 1, '/', '.mews.mobi');
                            session_destroy();
                            $msg = '你已成功登出. 返回首頁';
                        }
                        else if($_GET['action'] == 'login_before_post') {
                            $msg = '請先登入';
                            $redirectTo = LOGIN_PAGE."?from=news_form";
                        }  
                    } else {
                        header('Location: index.php');
                    }
                    echo '<p class="lead">' . $msg . '</p>';
                    header('Refresh: 2; URL='.$redirectTo);
                ?>
                <h2 class="text-center"><a href="<?php
                    echo 'http://' . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['PHP_SELF']),
                            '', $_SERVER['PHP_SELF']);
                    ?>" >Home <span class="glyphicon glyphicon-home"></span></a></h2>
            </div>
        </div><!-- /.container -->
    </body>
</html>

<!--
  By Lộc Nguyễn
  URL: http://www.umsl.edu/~lhn7c5/
  May 18, 2014
-->

