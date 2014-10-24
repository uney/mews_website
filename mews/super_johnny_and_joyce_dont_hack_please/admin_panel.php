<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");

    $DEBUG_TAG = "primary";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;
    
    /*
     * PDO Settings                 
     */
    try {
	    $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
	    $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch ( PDOException $e ) {
	    msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
    }
    include("admin_check.php");
    
   
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
	<title>MEWS</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />		
	<link rel="stylesheet" href="../css/reset.css" type="text/css" media="all" />
    <link rel="stylesheet" href="../css/nivoslider.css" type="text/css" media="all" />
    <link rel="stylesheet" href="../css/prettyPhoto.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="../css/style.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="../css/responsive.css" type="text/css" media="all" />
    <link rel="stylesheet" href="../css/icon.css" type="text/css" media="all" />
    <link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
    <!--[if IE 7]><link rel="stylesheet" href="css/ie7.css" type="text/css" media="all" />
	<![endif]-->
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
		<link rel="stylesheet" href="css/ie.css" type="text/css" media="all" />
	<![endif]-->
    <!-- Favicons
	================================================== -->
	<link rel="shortcut icon" href="../images/favicon.ico" />   
	<script type="text/javascript" src="../js/jquery-1.7.1.min.js"></script>
	<script src="../js/jquery-ui.min.js" type="text/javascript"></script>	

	<script type="text/javascript" src="../js/jquery.nivo.slider.pack.js"></script>
    <script type="text/javascript" src="../js/jquery.carouFredSel-5.6.2.js"></script>	
    <script type="text/javascript" src="../js/jquery.prettyPhoto.js"></script>
	<script type="text/javascript" src="../js/jquery.sticky.js"></script>
	<script type="text/javascript" src="../js/jquery-scroller-v1.min.js"></script>
	<script type="text/javascript" src="../js/kendo.web.min.js"></script>

    <script type="text/javascript" src="../js/custom.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&language=zh-TW"></script>

	<!--[if lt IE 9]>
	<script type="text/javascript" src="js/ie7-fixed.js"></script>
	<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body class="sub-nosidebar">
<?php include("admin_header.php"); ?>
<div class="wrapper">
	<div id="main-content">
        <div id="map-canvas"></div>
		<section class="user-profile clearfix">

            <div class="user-stat">
                <span><a class="blue-button" href="backend_php/update_user_stat.php">UPDTAE USER STATISTIC</i></a></span>
                <span><a class="green-button" href="admin_list.php?admin_action=pending_user">APPROVAL PENDING USER</a></span>
                <span><a class="red-button" href="admin_list.php?admin_action=reputation_alert">ALERT REPORT CHECKING</a></span>
                <span><a class="pink-button" href="admin_list.php?admin_action=spam_flag">FLAG REPORT HANDLING</a></span>
                <span><a class="pink-button" href="admin_list.php?admin_action=abuse_user">HANDLE ABUSE USER</a></span>
                <h4>用戶人數: <?php $stmt=$dbc->prepare("SELECT `user_id` FROM ".DB_TABLE_USER);
                $stmt->execute();
                echo $stmt->rowCount();
                ?></h4>
                <h4>七天內活躍用戶: <?php $stmt=$dbc->prepare("SELECT `user_id` FROM ".DB_TABLE_USER." WHERE `user_last_login` >DATE_SUB(curdate(), INTERVAL 7 DAY)");
                $stmt->execute();
                echo $stmt->rowCount();
                ?></h4>

            </div><!--end of user-stat-->
            <div class="latest-post-list">
                <h4>Latest Hack Report</h4>
                <?php
                try{
                    $stmt = $dbc->prepare("SELECT * FROM db_table_hack ORDER BY `hack_date_time` DESC LIMIT 10");
                    $stmt->execute();
                    if($stmt->rowCount()<1){
                        echo "找不到任何記錄";
                    }
                    while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
                        echo '<div class="latest-post">';
                        echo "<span><a href='http://whatismyipaddress.com/ip/".$row['hack_ip']."'>・".$row['hack_ip']."</a> &nbsp&nbsp;&nbsp&nbsp;".$row['hack_date_time']."</span>";

                        echo '<br><span><a href="http://facebook.com/profile.php?id='.$row['hack_facebook_id'].'">・Facebook:'.$row['hack_facebook_id'].'</a> </span>';

                        echo '</div>';
                    }              
                }
                catch(PDOException $e){
                    msg_log($DEBUG_TAG.": ".$e);
                }  

                ?>



            </div><!--lower-part-->
		</section>
        <?php include(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."footer.php"); ?>
	</div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>