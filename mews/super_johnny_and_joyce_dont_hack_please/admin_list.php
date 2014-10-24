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
    
    $mysql_stmt = "";
    $search_key = "";
    $search_type = "";
    $search_lat = "";
    $search_lng = "";
    /*
     * 4 search types:              
     * search #tag
     * search text           
     * search user           
     * search location (TEXT input or lat lng)          
     *            
     *            
     */
    if(isset($_GET['admin_action'])){

    }else{
    	//TODO redirect to 404
    }
    if($_GET['admin_action']=="pending_user"){
        $mysql_stmt = "SELECT * FROM ".DB_TABLE_SPECIAL_PENDING." WHERE `pending_result` =0 ORDER BY `pending_date_time` DESC ";
    }else if($_GET['admin_action']=="spam_flag"){
        $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_no_spam` >6 AND `news_never_spam` =0 AND `news_deleted` =0 ORDER BY `news_no_spam` DESC ";
    }else if($_GET['admin_action']=="reputation_alert"){
        $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE (`news_no_useful` >30 OR `news_no_rubbish` >20) AND `news_deleted` =0 AND `news_is_hot` =0 AND `news_never_spam` =0 AND `news_post_date_time` > DATE_SUB(curdate(), INTERVAL 5 DAY) ORDER BY `news_post_date_time` DESC ";
    }
    else if($_GET['admin_action']=="abuse_user"){
        $mysql_stmt = "SELECT *, COUNT(`rep_giver_ip`) AS count_ip FROM ".DB_TABLE_REPUTATION." WHERE `rep_date_time` > DATE_SUB(curdate(), INTERVAL 20 DAY) AND `rep_never_spam` =0 GROUP BY `rep_giver_ip` HAVING count_ip > 10 ORDER BY `count_ip` DESC ";
    }else{
        //404
    }

    try {
	    $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
	    $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch ( PDOException $e ) {
	    msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
    }
    include("admin_check.php");
    $rows = 0;
    try {
        $stmt = $dbc->prepare ($mysql_stmt); 
        $stmt->execute ();
        $rows = $stmt->rowCount ();
    } catch ( Exception $e ) {
        msg_log ( $DEBUG_TAG. "MYSQL ERROR: " . $e->getMessage () );
    }
    //break total records into pages
    $pages = ceil($rows/15);    
    
    $pagination = '';
    //create pagination
    if($pages > 1)
    {
        $pagination .= '<ul class="pagination box-hover clearfix">';
        for($i = 1; $i<$pages+1; $i++)
        {
            $pagination .= '<li><a href="#" class="paging" id="'.$i.'-page">'.$i.'</a></li>';
        }
            $pagination .= '</ul>';
    }


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


    <script>

    $(document).ready(function() {
        $("#search_result_list").load("backend_php/ajax/fetch_pages.php",
         {'page':0,
          'item_per_page':15,
          'search_type':'admin_action',
          'search_key':<?php echo "'".$_GET['admin_action']."'"; ?>,
          'mysql_stmt':<?php echo '"'.$mysql_stmt.'"'; ?>
         }, function() {$("#1-page").addClass('current');});  //initial page number to load
        
        $(".paging").click(function (e) {
        
        $("#search_result_list").prepend('<div class="loading-indication"><img src="ajax-loader.gif" /> Loading...</div>');
        var clicked_id = $(this).attr("id").split("-"); //ID of clicked element, split() to get page number.
        var page_num = parseInt(clicked_id[0]); //clicked_id[0] holds the page number we need 
        
        $('.paging').removeClass('current'); //remove any active class
        //post page number and load returned data into result element
        //notice (page_num-1), subtract 1 to get actual starting point
        $("#search_result_list").load("backend_php/ajax/fetch_pages.php", {'page':(page_num-1),
          'item_per_page':15,
          'search_type':'admin_action',
          'search_key':<?php echo "'".$_GET['admin_action']."'"; ?>,
          'mysql_stmt':<?php echo '"'.$mysql_stmt.'"'; ?>
         }, function(){});
        $(this).addClass('current'); //add active class to currently clicked element (style purpose)
        return false; //prevent going to herf 
        }); 
    });

 

    </script>
	<!--[if lt IE 9]>
	<script type="text/javascript" src="js/ie7-fixed.js"></script>
	<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body class="sub-nosidebar">
<?php include("admin_header.php"); ?>
<div class="wrapper">
	<div id="main-content">
		<section class="user-profile clearfix">

            <div class="latest-post-list">
                <br>
                <h2>搜尋列表：一共有<?php echo $rows;?>則結果</h2>
                <br>

                <div id="search_result_list"></div>

            </div><!--lower-part-->
            <div><?php echo $pagination;?></div>
		</section>

        <?php include(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'footer.php'); ?>
	</div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>