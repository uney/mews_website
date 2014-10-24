<?php 
    session_set_cookie_params(0, '/', 'mews.mobi');
    session_start(); //Start the session
    error_reporting(E_ALL);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."msg_constant.php");

    $category = "";
    $category_name ="news";
    $mysql_stmt = "";
    $icon_img = "images/icons/other_icon_small_white.png";
    if(isset($_GET['category'])){
        $category = $_GET['category'];

        $category_name = "其他新聞";
        $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` <>'news_crime' AND `news_type` <>'news_politics' AND `news_type` <>'news_accident' AND `news_type` <>'news_event' AND `news_type` <>'news_other' AND `news_type` <>'news_funny' AND `news_type` <>'news_tech' AND `news_type` <>'news_life' AND `news_type` <>'news_business' AND `news_type` <>'news_blog' ORDER BY `news_post_date_time` DESC ";
        if($category=="news_crime"){
            $category_name = "罪案報導";
            $icon_img = "images/icons/crime_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_crime' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_politics"){
            $category_name = "政治熱話";
            $icon_img = "images/icons/politics_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_politics' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_accident"){
            $category_name = "意外直擊";
            $icon_img = "images/icons/accident_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_accident' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_event"){
            $category_name = "活動盛事";
            $icon_img = "images/icons/event_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_event' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_other"){
            $category_name = "其他新聞";
            $icon_img = "images/icons/other_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_other' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_funny"){
            $category_name = "奇聞趣事";
            $icon_img = "images/icons/funny_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_funny' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_tech"){
            $category_name = "科技資訊";
            $icon_img = "images/icons/tech_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_tech' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_life"){
            $category_name = "生活消閒";
            $icon_img = "images/icons/life_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_life' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_business"){
            $category_name = "商業財經";
            $icon_img = "images/icons/business_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_business' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_blog"){
            $category_name = "專場報道";
            $icon_img = "images/icons/blog_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_blog' ORDER BY `news_post_date_time` DESC ";
        }else if($category=="news_funny_and_life"){
            $category_name = "輕鬆消閒";
            $icon_img = "images/icons/funny_icon_small_white.png";
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE (`news_type` ='news_funny' OR `news_type` ='news_life') ORDER BY `news_post_date_time` DESC ";
        }
    }
    else{
        header('Location: '.PAGE_NOT_FOUND);
    }

    $DEBUG_TAG = "secondary";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;
    $rows = 0;
    $page_offset = 0;
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
        include("login_check.php");

        try {
        	$stmt = $dbc->prepare( $mysql_stmt."LIMIT 100" ); 
	    	$stmt->execute();
            $rows = $stmt->rowCount();

	    } catch ( Exception $e ) {
	    	msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
	    }
        //break total records into pages
        $pages = ceil(($rows-$page_offset)/$item_per_page);	
    
        $pagination	= '';
        //create pagination
        if($pages > 1)
        {
        	$pagination	.= '<ul class="pagination box-hover clearfix">';
        	for($i = 1; $i<$pages+1; $i++)
        	{
        		$pagination .= '<li><a href="#" class="paging" id="'.$i.'-page">'.$i.'</a></li>';
        	}
        	$pagination .= '</ul>';
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
        if(isset($_GET['category'])){
            if($_GET['category']=="news_around"){
                $keywords .= ","."身邊事";
            }
            if($_GET['category']=="news_big"){
                $keywords .= ","."大事";
            }
            if($_GET['category']=="news_blog"){
                $keywords .= ","."個人專場";
            }
            if($_GET['category']=="news_funny"){
                $keywords .= ","."奇怪,奇聞,有趣,趣聞";
            }
        }
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
	<!-- jQuery 2.0.2 -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>

    <!-- jQuery UI 1.10.3 -->
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
    
    <script type="text/javascript">
    if (window.location.hash && window.location.hash == '#_=_') {
        if (window.history && history.pushState) {
            window.history.pushState("", document.title, window.location.pathname);
        } else {
            // Prevent scrolling by storing the page's current scroll offset
            var scroll = {
                top: document.body.scrollTop,
                left: document.body.scrollLeft
            };
            window.location.hash = '';
            // Restore the scroll offset, should be flicker free
            document.body.scrollTop = scroll.top;
            document.body.scrollLeft = scroll.left;
        }
    }

    $(document).ready(function() {
	    $("#news-list").load("backend_php/ajax/fetch_pages.php", {'page':0,
          'item_per_page':5,
          'search_type':<?php echo "'secondary'"; ?>,
          'search_key':<?php echo "'".$_GET['category']."'"; ?>,
          'mysql_stmt':<?php echo '"'.$mysql_stmt.'"'; ?>

         }, function() {$("#1-page").addClass('current');});  //initial page number to load
		
        $(".paging").click(function (e) {
			$("#news-list").prepend('<div class="loading-indication"><img src="ajax-loader.gif" /> Loading...</div>');
			var clicked_id = $(this).attr("id").split("-"); //ID of clicked element, split() to get page number.
			var page_num = parseInt(clicked_id[0]); //clicked_id[0] holds the page number we need 
		
			$('.paging').removeClass('current'); //remove any active class
      		//post page number and load returned data into result element
      		//notice (page_num-1), subtract 1 to get actual starting point
			$("#news-list").load("backend_php/ajax/fetch_pages.php", {'page':(page_num-1), 'item_per_page':5,
              'search_type':<?php echo "'secondary'"; ?>,
              'search_key':<?php echo "'".$_GET['category']."'"; ?>,
              'mysql_stmt':<?php echo '"'.$mysql_stmt.'"'; ?> }, function(){});
			$(this).addClass('current'); //add active class to currently clicked element (style purpose)
			return false; //prevent going to herf 
		});	
	});

</script>
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
<body class="sub-1sidebar">

<?php
    include("header.php");
?>


<div class="wrapper">
	<div id="main-content">
        
		<div class="primary">			
			<section>
                <header class="secondary-page-header">
                    <h1 class="secondary-page-header-title"><?php 
                    echo "<span>".$category_name;
                    echo "<img class='secondary-icon' src='".$icon_img."'/>";
                    if($category=="news_blog"){
                        echo "<a class='secondary-page-header-link' href='http://www.mews.mobi/edit_profile.php'>申請你的專場</a>";
                    }
                        ?></span></h1>                      
                </header>
                <div id="news-list"></div>
				
				<?php echo $pagination;?>
			</section>
		</div><!--end:primary-->
		<div class="secondary">
			<div id="sidebar-b" class="sidebar">
				<aside class="widget box-hover">
					<header class="entry-header">
						<h4 class="widget-title clearfix"><span>相關熱門話題</span></h4>					
					</header>
                    <?php

                    if($category == "news_crime"){
                        $mysql_part = "AND `news_type` ='news_crime'";
                    }else if($category=="news_politics"){
                        $mysql_part = "AND `news_type` ='news_politics'";
                    }else if($category=="news_accident"){
                        $mysql_part = "AND `news_type` ='news_accident'";
                    }else if($category=="news_event"){
                        $mysql_part = "AND `news_type` ='news_event'";
                    }else if($category=="news_other"){
                        $mysql_part = "AND `news_type` ='news_other'";
                    }else if($category=="news_funny"){
                        $mysql_part = "AND `news_type` ='news_funny'";
                    }else if($category=="news_tech"){
                        $mysql_part = "AND `news_type` ='news_tech'";
                    }else if($category=="news_life"){
                        $mysql_part = "AND `news_type` ='news_life'";
                    }else if($category=="news_business"){
                        $mysql_part = "AND `news_type` ='news_business'";
                    }else if($category=="news_blog"){
                        $mysql_part = "AND `news_type` ='news_blog'";
                    }
                    $stmt = $dbc->prepare("SELECT * ,(`news_no_comment`*0.8+`news_no_useful`*0.6+`news_no_read`*0.05-`news_no_rubbish`*0.4) AS `news_hotness`
                         FROM ".DB_TABLE_NEWS." WHERE `news_post_date_time` > DATE_SUB(curdate(), INTERVAL 10 DAY) ".$mysql_part." ORDER BY `news_hotness` DESC LIMIT 1");
                    try{
                        $stmt->execute();
                    }catch(Exception $e){
                        msg_log($DEBUG_TAG." : ".$e);
                    }
                    while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                        echo'<article class="entry-box clearfix">';
                        if(strlen($row['news_pic1'])>1){
                            echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="hover-effect relative-hot-news-column-pic" src="../news_images/'.$row['news_pic1'].'" alt="" /></a>';
                        }
                        echo '<div class="entry-content">';
                        if(strlen($row['news_address'])>1){
                            echo '<span class="entry-author"><i class="fa fa-fw fa-map-marker"></i>:&nbsp;</span>';
                            echo '<a class="entry-category" >'.$row['news_address'].'</a>';
                        }
                        echo '<h3 class="entry-title"><a href="primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a></h3>';
                        $stmt2 = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER." WHERE user_id =?");
                        $stmt2->bindValue(1, $row['news_author_id'], PDO::PARAM_STR);
                        $author_name = "";
                        try{
                            $stmt2->execute();
                            while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
                                $author_name = $row2['user_name'];
                            }
                        }catch(Exception $e){
                            msg_log($DEBUG_TAG." : ".$e);
                        }
                        echo '<span class="entry-author"><i class="fa fa-fw fa-user"></i>:&nbsp;</span><a href="user_profile.php?user_id='.$row['news_author_id'].'">'.$author_name.'</a>';
                        echo '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-eye"></i>:&nbsp;</span>'.$row['news_no_read'].
                                '&nbsp;&nbsp;<span class="entry-author"><i class="glyphicon glyphicon-fire"></i>:&nbsp;</span>'.$row['news_no_useful'].
                                '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-fire-extinguisher"></i>:&nbsp;</span>'.$row['news_no_rubbish'].
                                '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-comment-o"></i>:&nbsp;</span>'.$row['news_no_comment'].
                            '</div>';
                        echo'</article>';
                    }

                    $stmt = $dbc->prepare("SELECT * ,(`news_no_comment`*0.8+`news_no_useful`*0.6+`news_no_read`*0.05-`news_no_rubbish`*0.4) AS `news_hotness`
                         FROM ".DB_TABLE_NEWS." WHERE `news_post_date_time` > DATE_SUB(curdate(), INTERVAL 15 DAY) ".$mysql_part." ORDER BY `news_hotness` DESC LIMIT 1, 10");
                    try{
                        $stmt->execute();
                    }catch(Exception $e){
                        msg_log($DEBUG_TAG." : ".$e);
                    }

                    echo '<ul class="older-posts">';
                    while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                        echo '<li><h3 class="entry-title"><a href="primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a></h3></li>';
                    }
                    echo '</ul>';


                    ?>

				</aside><!--end:widget-->

				<div class="clear"></div>
			</div><!--end:sidebar-->
			<div class="clear"></div>
		</div><!--end:secondary-->
		<div class="clear"></div>
		<?php include("footer.php") ?>
	</div><!--main-content-->
</div><!--end:wrapper-->

</body>
</html>