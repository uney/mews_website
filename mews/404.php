<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");



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


    <style>
    #map-canvas {
        height: 290px;
        margin-right:30px;
        margin-left:30px;
	    margin-bottom:20px;
	    margin-top:10px;
    }
    </style>
    <script>

    var geocoder;
    var map;
    var marker;
    var address_ok = false;
    var address_need = true;
    function initialize() {
        geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(0, 0);
        var mapOptions = {
            zoom: 1,
            center: latlng
        }
        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);


        var infowindow = new google.maps.InfoWindow({
            content: "loading..."
        });

        google.maps.event.addListener(marker, 'click', function() {
            infowindow.setContent(this.title);
            infowindow.open(map,marker);
        });
        google.maps.event.addListener(map, 'click', function(event) {
            //TODO get address and lat lng after clicking
        });
    }


    google.maps.event.addDomListener(window, 'load', initialize);


    </script>
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
                
                <h1>地球很危險，404已回到火星</h1>

            </div><!--end of user-stat-->
            <div class="latest-post-list">
                <h4>其他有關地球報道</h4>
                <?php
                try{
                    $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_NEWS." ORDER BY RAND() LIMIT 10");
                    $stmt->execute();
                    if($stmt->rowCount()<1){
                        echo "找不到任何記錄";
                    }
                    while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
                        echo '<div class="latest-post">';
                        echo "<a href='primary.php?news_id=".$row['news_id']."'>・".$row['news_title']."</a> <br>&nbsp&nbsp;&nbsp&nbsp;".$row['news_date'];
                        if(strlen($row['news_address'])>1){
                            echo'&nbsp;<span><i class="fa fa-fw fa-map-marker"></i>&nbsp;'.$row['news_address'].'</span>';
                        }
                        echo '<span>&nbsp;&nbsp;<i class="fa fa-eye"></i>:'.$row['news_no_read'].'</span>';
                        echo '<span>&nbsp;&nbsp;<i class="glyphicon glyphicon-circle-arrow-up"></i>:'.$row['news_no_useful'].'</span>';
                        echo '<span>&nbsp;&nbsp;<i class="glyphicon glyphicon-circle-arrow-down"></i>:'.$row['news_no_rubbish'].'</span>';
                        echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-trash-o"></i>:'.$row['news_no_spam'].'</span>';
                        echo '</div>';
                    }              
                }
                catch(PDOException $e){
                    msg_log($DEBUG_TAG.": ".$e);
                }  

                ?>



            </div><!--lower-part-->
		</section>
		<?php include("footer.php");?>
	</div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>