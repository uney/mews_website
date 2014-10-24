<?php
    session_start(); //Start the session
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");

    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
        //do nothing if the user is logged in
    }else{
        $url = REDIRECT_PAGE;
        header('Location: '.$url);
    }

    $DEBUG_TAG = "my_following";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;

    $query_mysql_stmt = "SELECT * FROM ".DB_TABLE_FOLLOW." WHERE `follow_follower` =? AND `follow_type` =? ORDER BY `follow_last_seen` ASC";
    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
        try {
            $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
            $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }    
    }
    else{
    	header('Location: '.LOGIN_PAGE);
    }
?>              

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>MEWS 新聞地圖</title>
    <meta name=“description” content=“mews searching page”>
    <?php 
        $keywords = "Hong Kong,Mews,MEWS,新聞,地圖,新聞地圖,公民新聞,公民,news,香港,民主,佔中,反佔中,中國,意外,身邊事";
        echo "<meta name='keywords' content='".$keywords."'/>";
    ?>
    <meta name="robots" content="index, follow" />      
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />        
    <link rel="stylesheet" href="css/reset.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/nivoslider.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/responsive.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/icon.css" type="text/css" media="all" />
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
    <!--[if IE 7]><link rel="stylesheet" href="css/ie7.css" type="text/css" media="all" />
    <![endif]-->
    <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
        <link rel="stylesheet" href="css/ie.css" type="text/css" media="all" />
    <![endif]-->
    <!-- Favicons

    個人專長 inside 個人專場
    身邊事
    
    ================================================== -->
    <link rel="shortcut icon" href="images/favicon.ico" />   
    <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>  

    <script type="text/javascript" src="js/jquery.nivo.slider.pack.js"></script>
    <script type="text/javascript" src="js/jquery.carouFredSel-5.6.2.js"></script>  
    <script type="text/javascript" src="js/jquery.prettyPhoto.js"></script>
    <script type="text/javascript" src="js/jquery.sticky.js"></script>
    <script type="text/javascript" src="js/jquery-scroller-v1.min.js"></script>
    <script type="text/javascript" src="js/kendo.web.min.js"></script>

    <script type="text/javascript" src="js/custom.js"></script>


    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&language=zh-TW"></script>
    <script>
    $(document).ready(function() {
        $("#user_result_list").load("backend_php/ajax/follow.php",
        { 
            'follow_follower':<?php echo "'".$_SESSION['user_id']."'"; ?>,
            'follow_type':'user',
            'mysql_stmt':<?php echo '"'.$query_mysql_stmt.'"'; ?>
        }, function() {});  //initial page number to load

        $("#tag_result_list").load("backend_php/ajax/follow.php",
        { 
            'follow_follower':<?php echo "'".$_SESSION['user_id']."'"; ?>,
            'follow_type':'tag',
            'mysql_stmt':<?php echo '"'.$query_mysql_stmt.'"'; ?>
        }, function() {});  //initial page number to load
        
    });


    function follow(id, action, type, follow_following){
        var minus = document.getElementById(id);
        var img = document.createElement("IMG");
        img.src = "images/loader.gif";        
        minus.parentNode.replaceChild(img, minus);
        //document.getElementById(id).innerHTML = "<img src='images/loader.gif' alt='The Image' />";
        var logged_in = <?php if(isset($_SESSION ['username'])) { echo json_encode("true");}
                          else{ echo json_encode("false");} ?>;
        var follow_type = type;
        var follow_follower =  <?php echo json_encode($_SESSION['user_id']);?>;
        var follow_action = action;
        if(logged_in == "true"){
            $.ajax({
                type: "post",
                url: "backend_php/ajax/follow.php",
                data: {'follow_action': follow_action, 
                       'follow_follower': follow_follower, 
                       'follow_following': follow_following, 
                       'follow_type': follow_type},
                success:function(data){
                    if(data == 1){
                        $("#user_result_list").load("backend_php/ajax/follow.php",
                        { 
                            'follow_follower':<?php echo "'".$_SESSION['user_id']."'"; ?>,
                            'follow_type':'user',
                            'mysql_stmt':<?php echo '"'.$query_mysql_stmt.'"'; ?>
                        }, function() {});  //initial page number to load

                        $("#tag_result_list").load("backend_php/ajax/follow.php",
                        { 
                            'follow_follower':<?php echo "'".$_SESSION['user_id']."'"; ?>,
                            'follow_type':'tag',
                            'mysql_stmt':<?php echo '"'.$query_mysql_stmt.'"'; ?>
                        }, function() {});  //initial page number to load
                    }
                    else{
                        vex.dialog.confirm({            
                            message: "系統出錯，請稍後再試",            
                            callback: function(value) {}
                        });                
                    }
                }
            });
            
            return false;
        }
        else if(logged_in != "true"){
            alert( "請先登入! ");
        }

    }
    </script>



    <!--[if lt IE 9]>
    <script type="text/javascript" src="js/ie7-fixed.js"></script>
    <![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-54036500-1', 'auto');
  ga('require', 'displayfeatures');
  ga('send', 'pageview');

</script>
    </head>
<body class="sub-nosidebar">
<?php include('header.php');?>
<div class="wrapper">
    <div id="main-content">
        <section class="user-profile clearfix">

            <div class="latest-post-list">
                
                
                <h2>我已關注的用戶：</h2>
                <div id="user_result_list"></div>

                <h2>我已關注的#TAG：</h2>
                <div id="tag_result_list"></div>

            </div><!--lower-part-->
        </section>

        <?php include('footer.php'); ?>
    </div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>