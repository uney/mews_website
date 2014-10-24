<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");



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
    $news_type_name = "";
    $user_name = "";
    /*
     * 4 search types:              
     * search #tag
     * search text           
     * search user           
     * search location (TEXT input or lat lng)          
     *            
     *            
     */
    if(isset($_GET['search_type'])&&isset($_GET['search_key'])){

    }else{
        header('Location: '.PAGE_NOT_FOUND);
    }
    if($_GET['search_type']=="tag"){
        $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_tag_1` LIKE ? OR `news_tag_2` LIKE ? OR `news_tag_3` LIKE ? OR `news_tag_4` LIKE ? ORDER BY `news_post_date_time` DESC ";
    }else if($_GET['search_type']=="text"){
        // mysql multiple column
        $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_content` LIKE ? OR `news_title` LIKE ? OR `news_tag_1` LIKE ? OR `news_tag_2` LIKE ? OR `news_tag_3` LIKE ? OR `news_tag_4` LIKE ? ORDER BY `news_post_date_time` DESC ";
    }else if($_GET['search_type']=="type"){
        // mysql multiple column
        if($_GET['search_key']=="news_around"){
            $news_type_name = "身邊事";
        }else if($_GET['search_key']=="news_crime"){
            $news_type_name = "罪案";
        }else if($_GET['search_key']=="news_politics"){
            $news_type_name = "政治";
        }else if($_GET['search_key']=="news_accident"){
            $news_type_name = "意外";
        }else if($_GET['search_key']=="news_event"){
            $news_type_name = "活動";
        }else if($_GET['search_key']=="news_other"){
            $news_type_name = "其他";
        }else if($_GET['search_key']=="news_funny"){
            $news_type_name = "奇趣";
        }else if($_GET['search_key']=="news_blog"){
            $news_type_name = "專場報道";
        }
        $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` =? ORDER BY `news_post_date_time` DESC ";
    }else if($_GET['search_type']=="user"){
        $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_author_id` =? ORDER BY `news_post_date_time` DESC ";
    }else if($_GET['search_type']=="loaction"){
        if(isset($_GET['search_lat'])&&isset($_GET['search_lng'])){
            $search_lat = $_GET['search_lat'];
            $search_lng = $_GET['search_lng'];
        }else{
            if(isset($_GET['search_address'])){
                $geocode=file_get_contents("http://maps.google.com/maps/api/geocode/json?address=".$_GET['search_address']."&sensor=false");
                $output= json_decode($geocode);
                $search_lat = $output->results[0]->geometry->location->lat;
                $search_lng = $output->results[0]->geometry->location->lng;
            }
        }
        $reqDist=0.8;
        $lng1 = $search_lng-$reqDist/abs(cos(deg2rad($userLat))*69);
        $lng2 = $search_lng+$reqDist/abs(cos(deg2rad($userLat))*69);
        $lat1 = $search_lat-($reqDist/69);
        $lat2 = $search_lat+($reqDist/69);
        // query
        $mysql_stmt = "SELECT *, ( 3959 * acos( cos( radians($userLat) ) * cos( radians( ".DB_TABLE_NEWS.".news_lat ) ) * cos( radians( ".DB_TABLE_NEWS.".news_lng ) - radians(".$search_lng.") ) + sin( radians(".$search_lat.") ) * sin( radians( ".DB_TABLE_NEWS.".lat ) ) ) ) AS distance FROM ".DB_TABLE_NEWS." WHERE `news_post_date_time` > DATE_SUB(NOW(), INTERVAL 10 DAY) and ".DB_TABLE_NEWS.".lat between ".$lat1." AND ".$lat1."and ".DB_TABLE_NEWS.".lng between ".$lng1." AND ".$lng1." having distance < ".$reqDist." ORDER BY distance ASC limit 15";
    }else{
        header('Location: '.PAGE_NOT_FOUND);
    }

    try {
	    $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
	    $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch ( PDOException $e ) {
	    msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
    }
    include("login_check.php");

    try {
        $stmt = $dbc->prepare ($mysql_stmt); 
        if($_GET['search_type']=="tag"){
            //search key == tag name
            $stmt->bindValue(1, $_GET['search_key'], PDO::PARAM_STR);
            $stmt->bindValue(2, $_GET['search_key'], PDO::PARAM_STR);
            $stmt->bindValue(3, $_GET['search_key'], PDO::PARAM_STR);
            $stmt->bindValue(4, $_GET['search_key'], PDO::PARAM_STR);
        }else if($_GET['search_type']=="text"){
            //search key == text
            $fullTextSearch = "%".$_GET['search_key']."%";
            $stmt->bindValue(1, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(2, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(3, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(4, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(5, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(6, $fullTextSearch, PDO::PARAM_STR);
        }else if($_GET['search_type']=="type"){
            //search key == text
            $stmt->bindValue(1, $_GET['search_key'], PDO::PARAM_STR);
        }else if($_GET['search_type']=="user"){
            //search_key == user_id
            $stmt->bindValue(1, $_GET['search_key'], PDO::PARAM_STR);
        }else if($_GET['search_type']=="location"){
            //search_key == addres, will not be use in this case
            // location search need not to paginate
        }
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

    $tag_no_follower = 0;
    $user_no_follower = 0;
    if($_GET['search_type']=="tag"){
        $stmt = $dbc->prepare("SELECT `tag_no_follower` FROM ".DB_TABLE_TAG." WHERE `tag_name` =?");
        $stmt->bindValue(1, $_GET['search_key'], PDO::PARAM_STR);
        $stmt->execute();
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $tag_no_follower = $row['tag_no_follower'];
        }
    }
    if($_GET['search_type']=="user"){
        $stmt = $dbc->prepare("SELECT `user_no_follower` FROM ".DB_TABLE_USER." WHERE `user_id` =?");
        $stmt->bindValue(1, $_GET['search_key'], PDO::PARAM_STR);
        $stmt->execute();
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $user_no_follower = $row['user_no_follower'];
        }
        $stmt = $dbc->prepare("SELECT `user_name` FROM ".DB_TABLE_USER." WHERE `user_id` ='".$_GET['search_key']."'");
        try{
            $stmt->execute();
        }catch(Exception $e){
            msg_log($DEBUG_TAG." : ".$e);
        }
        
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_name = $row['user_name'];
        }
    }
    include("login_check.php");


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
    <link rel="stylesheet" href="css/vex_dialog/vex.css" />
    <link rel="stylesheet" href="css/vex_dialog/vex-theme-os.css" />
    <link rel="stylesheet" href="css/vex_dialog/vex-theme-flat-attack.css" />
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
    <script src="js/vex_dialog/vex.combined.min.js"></script>
    <script>vex.defaultOptions.className = 'vex-theme-flat-attack';</script>

    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&language=zh-TW"></script>


    <script>

    $(document).ready(function() {
        $("#search_result_list").load("backend_php/ajax/fetch_pages.php",
         {'page':0,
          'item_per_page':15,
          'search_type':<?php echo "'".$_GET['search_type']."'"; ?>,
          'search_key':<?php echo "'".$_GET['search_key']."'"; ?>,
          <?php
          if($_GET['search_type']=="location"){     
              echo "'search_lat':".$search_lat.",";       
              echo "'search_lng':".$search_lng.",";       
          }
          ?>'mysql_stmt':<?php echo '"'.$mysql_stmt.'"'; ?>

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
            'search_type':<?php echo "'".$_GET['search_type']."'"; ?>,
            'search_key':<?php echo "'".$_GET['search_key']."'"; ?>,
            <?php
            if($_GET['search_type']=="location"){     
                echo "'search_lat':".$search_lat.",";       
                echo "'search_lng':".$search_lng.",";       
            }
            ?>
            'mysql_stmt':<?php echo '"'.$mysql_stmt.'"'; ?>
         }, function(){});
        $(this).addClass('current'); //add active class to currently clicked element (style purpose)
        return false; //prevent going to herf 
        }); 
    });


    var no_follower = <?php if($_GET['search_type']=='tag'){echo $tag_no_follower;} else if($_GET['search_type']=='user'){echo $user_no_follower;} else{echo 0;} ?>;
    number_of_follower = parseInt(no_follower);
    function follow(action, type){
        var logged_in = <?php if(isset($_SESSION ['username'])) { echo json_encode("true");}
                          else{ echo json_encode("false");} ?>;
        var follow_type = type;
        var follow_following = <?php echo json_encode($_GET['search_key']);?>;
        var follow_follower =  <?php echo json_encode($_SESSION['user_id']);?>;
        var follow_action = action;
        var display_name = <?php if($_GET['search_type']=='user'){echo json_encode($user_name);}else{echo json_encode("#".$_GET['search_key']);}?>;
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
                        if(action=="follow"){
                            document.getElementById("follow_"+follow_type+"_span").innerHTML  = '<a id="unfollow" onClick="follow(this.id, '+"'"+follow_type+"'"+')"><i class="fa fa-fw fa-bookmark"></i> 取消關注</a>';
                            number_of_follower = number_of_follower+1;
                        }else if(action=="unfollow"){
                            document.getElementById("follow_"+follow_type+"_span").innerHTML  = '<a id="follow" onClick="follow(this.id, '+"'"+follow_type+"'"+')"><i class="fa fa-fw fa-bookmark-o"></i>'+display_name+'</a>';
                            number_of_follower = number_of_follower-1;
                        }
                        document.getElementById("no_of_follower").innerHTML = "關注人數: "+number_of_follower;
                    }
                    else if(data == 2){
                        vex.dialog.confirm({            
                            message: "請別太自戀，關注一下其他人",            
                            callback: function(value) {}
                        });    
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
  ga('send', 'pageview');

</script>
    </head>
<body class="sub-nosidebar">
<?php include('header.php');?>
<div class="wrapper">
	<div id="main-content">
		<section class="user-profile clearfix">

            <div class="latest-post-list">
                <br>
                <?php
                if($_GET['search_type']=="tag"){
                    echo  '<h2>#'.$_GET['search_key'].' 一共有'.$rows.'則相關新聞 <span id="follow_tag_span">';
                    if(isset($_SESSION['user_id'])){
                        $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_FOLLOW.
                            " WHERE `follow_type` ='tag' AND `follow_following` =? AND `follow_follower` =?");
                        $stmt->bindValue(1, $_GET['search_key'], PDO::PARAM_STR);
                        $stmt->bindValue(2, $_SESSION['user_id'], PDO::PARAM_STR);
                        try{
                            $stmt->execute();
                        }
                        catch(Exception $e){
                            msg_log($DEBUG_TAG.": ".$e);
                        }
                        if($stmt->rowCount()>0){
                            echo '<a id="unfollow" onClick="follow(this.id, '."'".'tag'."'".')"><i class="fa fa-fw fa-bookmark"></i>取消關注</a></span></h2>';
                        }
                        else{
                            echo '<a id="follow" onClick="follow(this.id, '."'".'tag'."'".')"><i class="fa fa-fw fa-bookmark-o"></i>#'.$_GET['search_key'].'</a></span></h2>';
                        }
                    }
                    else{
                        echo '<a href="facebook_login.php"><i class="fa fa-fw fa-bookmark-o"></i>請先登入</a></span></h2>';
                    }
                    echo "<div id='no_of_follower'>關注人數".$tag_no_follower."</div>";

                }
                else if($_GET['search_type']=="type"){
                    echo '<h2>'.$news_type_name.' 分類一共有'.$rows.'則相關新聞</h2>';
                }
                else if($_GET['search_type']=="user"){
                    echo '<h2>'.$user_name.' 一共發佈了'.$rows.'則新聞<span id="follow_user_span">';
                    if(isset($_SESSION['user_id'])){
                        $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_FOLLOW.
                            " WHERE `follow_type` ='user' AND `follow_following` =? AND `follow_follower` =?");
                        $stmt->bindValue(1, $_GET['search_key'], PDO::PARAM_STR);
                        $stmt->bindValue(2, $_SESSION['user_id'], PDO::PARAM_STR);
                        try{
                            $stmt->execute();
                        }
                        catch(Exception $e){
                            msg_log($DEBUG_TAG.": ".$e);
                        }
                        if($stmt->rowCount()>0){
                            echo '<a id="unfollow" onClick="follow(this.id, '."'".'user'."'".')"><i class="fa fa-fw fa-bookmark"></i>取消關注</a></span></h2>';
                        }
                        else{
                            echo '<a id="follow" onClick="follow(this.id, '."'".'user'."'".')"><i class="fa fa-fw fa-bookmark-o"></i>#'.$user_name.'</a></span></h2>';
                        }
                    }
                    else{
                        echo '<a href="facebook_login.php"><i class="fa fa-fw fa-bookmark-o"></i>請先登入</a></span></h2>';
                    }
                    echo "<div id='no_of_follower'>關注人數".$user_no_follower."</div>";
                }
                else{
                    echo '<h2>搜尋列表：一共有'.$rows.'則結果</h2>';
                }
                ?>
                
                <br>

                <div id="search_result_list"></div>

            </div><!--lower-part-->
            <div><?php echo $pagination;?></div>
		</section>

        <?php include('footer.php'); ?>
	</div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>