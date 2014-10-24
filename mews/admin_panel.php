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

    $hacking_detected = false;
    if($_SESSION['user_id_admin']==1){
        $stmt = $dbc->prepare("SELECT `user_id` FROM ".DB_TABLE_USER." WHERE user_id =? AND user_is_admin =1");
        $stmt->bindValue(1, $_GET['user_id'], PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount()<1){
            $hacking_detected = true;
        }
    }
    else{
        $hacking_detected = true;
    }
    if($hacking_detected){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $stmt = $dbc->prepare("INSERT INTO db_table_hack (`hack_date_time`, `hack_ip`, `hack_facebook_id`) VALUES (NOW(), ?, ?)");
        try {
            $result = $stmt->execute ( array ($ip, $_SESSION["facebook_id"]));
        }catch (Exception $e){
            msg_log($DEBUG_TAG." : ".$e);
        }
        $dbc=null;
        die("檢查到惡意攻擊動作！你的ip地址已被記錄");
    }
    
   
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
	<title>MEWS</title>
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

	<!--[if lt IE 9]>
	<script type="text/javascript" src="js/ie7-fixed.js"></script>
	<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body class="sub-nosidebar">
<?php include('header.php');?>
<div class="wrapper">
	<div id="main-content">
        <div id="map-canvas"></div>
		<section class="user-profile clearfix">

            <div class="user-stat">
                <span><a class="blue-button">UPDTAE USER STAT&nbsp;&nbsp;<i class="fa fa-keyboard-o"></i></a></span>
                <span><a class="green-button">APPROVAL PENDING USER&nbsp;&nbsp;<i class="glyphicon glyphicon-circle-arrow-up"></i></a></span>
                <span><a class="red-button">ALERT REPORT&nbsp;&nbsp;<i class="glyphicon glyphicon-circle-arrow-down"></i></a></span>
                <span><a class="pink-button">FLAG REPORT&nbsp;&nbsp;<i class="fa fa-eye"></i></a></span>
                <h4>信用評價: </h4>

            </div><!--end of user-stat-->
            <div class="latest-post-list">
                <h4>Latest Hack Report</h4>
                <?php
                try{
                    $stmt = $dbc->prepare("SELECT * FROM db_table_hack ORDER BY `hack_date_time` DESC LIMIT 10");
                    $stmt->bindValue(1, $_GET['user_id'], PDO::PARAM_STR);
                    $stmt->execute();
                    if($stmt->rowCount()<1){
                        echo "找不到任何記錄";
                    }
                    while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
                        echo '<div class="latest-post">';
                        echo "<span href='http://whatismyipaddress.com/ip/".$row['hack_ip']."'>・".$row['hack_ip']."</a> &nbsp&nbsp;&nbsp&nbsp;".$row['hack_date_time'];

                        echo '<br><span href="http://facebook.com/profile.php?id='.$row['hack_facebook_id'].'">・Facebook:'.$row['hack_facebook_id'].'</a> </span>';

                        echo '</div>';
                    }              
                }
                catch(PDOException $e){
                    msg_log($DEBUG_TAG.": ".$e);
                }  

                ?>



            </div><!--lower-part-->
		</section>
        <?php include("footer.php"); ?>
	</div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>