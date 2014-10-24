<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."msg_constant.php");



    $DEBUG_TAG = "primary";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;
    $news_type = "";    
    $news_type_name = "";    
    /*
     * PDO Settings                 
     */
    if(isset($_GET['news_id'])||$_GET['news_id']=""){

    }else{
    	//TODO redirect to 404
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

    $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_NEWS." WHERE news_id =?");
    $stmt->bindValue(1, $_GET['news_id'], PDO::PARAM_STR);
    $stmt->execute();
    if($stmt->rowCount()>0){
    	while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
            $news_id = $row['news_id'];
            $news_title = $row['news_title'];
            $news_author_id = $row['news_author_id'];
            $news_post_date_time = $row['news_post_date_time'];
            $news_date = $row['news_date'];
            $news_time = $row['news_time'];
            $news_content = $row['news_content'];
            $news_type = $row['news_type'];
            $news_address = $row['news_address'];
            $news_lat = $row['news_lat'];
            $news_lng = $row['news_lng'];
            $news_no_read = $row['news_no_read'];
            $news_no_useful = $row['news_no_useful'];
            $news_no_rubbish = $row['news_no_rubbish'];
            $news_no_spam = $row['news_no_spam'];
            $news_no_comment = $row['news_no_comment'];
            $news_pic1 = $row['news_pic1'];
            $news_locked = $row['news_locked'];
            $news_tag_1 = $row['news_tag_1'];
            $news_tag_2 = $row['news_tag_2'];
            $news_tag_3 = $row['news_tag_3'];
            $news_tag_4 = $row['news_tag_4'];
            $news_type = $row['news_type'];
            $news_type_name = "其他新聞";
            if($row['news_type']=="news_crime"){
                $news_type_name = "罪案報導";
            }else if($row['news_type']=="news_politics"){
                $news_type_name = "政治熱話";
            }else if($row['news_type']=="news_accident"){
                $news_type_name = "意外直擊";
            }else if($row['news_type']=="news_event"){
                $news_type_name = "活動盛事";
            }else if($row['news_type']=="news_other"){
                $news_type_name = "其他新聞";
            }else if($row['news_type']=="news_funny"){
                $news_type_name = "奇聞趣事";
            }else if($row['news_type']=="news_tech"){
                $news_type_name = "科技資訊";
            }else if($row['news_type']=="news_life"){
                $news_type_name = "生活消閒";
            }else if($row['news_type']=="news_business"){
                $news_type_name = "商業財經";
            }else if($row['news_type']=="news_blog"){
                $news_type_name = "專場報道";
            }
        }
    }
    else{
        header('Location: '.PAGE_NOT_FOUND);
    }
    $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER." WHERE user_id =?");
    $stmt->bindValue(1, $news_author_id, PDO::PARAM_STR);
    try{
        $stmt->execute();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    	    $author_name = $row['user_name'];
    	    $author_info = $row['user_info'];
    	    $author_pic = $row['user_pic'];
    	    if(strlen($author_info)<1){
    		    $author_info = "用戶未有提供個人簡介!";
    	    }
        }
    }
    catch ( PDOException $e ) {
	    msg_log ( $DEBUG_TAG.'SELECT failed: ' . $e->getMessage () );
    }
    $rows = 0;
    try {
        $stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_COMMENT." WHERE comment_news_comment_id =? AND comment_type =?" ); 
        $stmt->bindValue(1, $news_id, PDO::PARAM_STR);
        $stmt->bindValue(2, "news", PDO::PARAM_STR);
	    $stmt->execute ();
        $rows = $stmt->rowCount ();

    } catch ( Exception $e ) {
    	msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
    }
    //break total records into pages
    //$rows  = 21;
    $pages = ceil($rows/$item_per_page);	    
    $pagination	= '';
    //create pagination

    if($pages > 0)
    {
        $pagination	.= '<div class="comment-pagination clearfix">&nbsp;&nbsp;';
        for($i = 1; $i<$pages+1; $i++)
        {
        	$pagination .= '<a href="#" class="paging" id="'.$i.'-page">'.$i.'</a>';
        }
        $pagination .= '</div>';
    }
    if(!isset($_SESSION['hasVisited'.$news_id])){
        $_SESSION['hasVisited'.$news_id]="yes";
        $news_no_read_int = (int)$news_no_read;
        $news_no_read_int++;
        $stmt = $dbc->prepare("UPDATE ".DB_TABLE_NEWS." SET `news_no_read` =? WHERE news_id =?");
        $stmt->bindValue(1, $news_no_read_int, PDO::PARAM_STR);
        $stmt->bindValue(2, $news_id, PDO::PARAM_STR);
        try{
        	$stmt->execute();
        }catch ( Exception $e ) {
    	    msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
        }
        
    }
    include("login_check.php");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />	
    <?php 
    if (strlen($news_pic1)>1){ echo '<meta property="og:image" content="'.BASE_URL.GW_IMAGEPATH.$news_pic1.'"/>'; }
    else{
        echo '<meta property="og:image" content="'.BASE_URL.GW_IMAGEPATH.'default.jpg"/>';     
    }
    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    echo '<meta property="og:url" content="'.$actual_link.'" />';
    echo '<meta property="og:title" content="'.$news_title.'" />';
    ?>
    <title><?php echo $news_title;?>－MEWS 新聞地圖</title>
    <meta name=“description” content=“mews is a hong kong opensource news platform with location search function, we help user to find news by using their geolocation”>
    <?php 
        $keywords = "Hong Kong,Mews,MEWS,新聞,地圖,新聞地圖,公民新聞,公民,news,香港,民主,佔中,反佔中,中國,意外,身邊事";
        $keywords .= ",".$news_title;
        if(strlen($news_tag_1)>1){
            $keywords .= ",".$news_tag_1;
        }
        if(strlen($news_tag_2)>1){
            $keywords .= ",".$news_tag_2;
        }
        if(strlen($news_tag_3)>1){
            $keywords .= ",".$news_tag_3;
        }
        if(strlen($news_tag_4)>1){
            $keywords .= ",".$news_tag_4;
        }
        echo "<meta name='keywords' content='".$keywords."'/>";
        ?>
    <meta name="robots" content="index, follow" />               

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




    <!-- Add fancyBox main JS and CSS files -->
    <script type="text/javascript" src="js/fancy_box/jquery.fancybox.js?v=2.1.5"></script>
    <link rel="stylesheet" type="text/css" href="css/fancy_box/jquery.fancybox.css?v=2.1.5" media="screen" />



    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&language=zh-TW"></script>


    <style>
    #map-canvas {
        height: 230px;
        margin-right:20px;
	    margin-bottom:20px;
	    margin-top:10px;
    }
    </style>
    <script>
    var submit_comment = false;
    if (window.location.hash && window.location.hash == '#_=_') {
        if (window.history && window.history.pushState) {
            window.history.pushState("", document.title, window.location.pathname+<?php echo "'?news_id=".$news_id."'"; ?>);
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
    var news_type = <?php echo "'".$news_type."'";?>;
    var geocoder;
    var map;
    var marker;
    var address_ok = false;
    var address_need = true;
    var latlng ;
    function initialize() {
        geocoder = new google.maps.Geocoder();
        latlng = new google.maps.LatLng(<?php echo $news_lat.",".$news_lng; ?>);
        var mapOptions = {
            zoom: 13,
            center: latlng
        }
        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
        // This event listener will call addMarker() when the map is clicked.
        var contentString = <?php echo "'".$news_title."'"; ?>;

        var infowindow = new google.maps.InfoWindow({
            content: contentString
        });
        var imageFile = "images/icons/map_other.png";

        if(news_type=="news_crime"){
            imageFile = "images/icons/map_crime.png";
        }
        else if(news_type=="news_accident"){
            imageFile = "images/icons/map_accident.png";
        }
        else if(news_type=="news_funny"){
            imageFile = "images/icons/map_funny.png";
        }
        else if(news_type=="news_event"){
            imageFile = "images/icons/map_event.png";
        }
        else if(news_type=="news_other"){
            imageFile = "images/icons/map_other.png";
        }
        else if(news_type=="news_politics"){
            imageFile = "images/icons/map_politics.png";
        }
        else if(news_type=="news_tech"){
            imageFile = "images/icons/map_tech.png";
        }
        else if(news_type=="news_business"){
            imageFile = "images/icons/map_business.png";
        }
        else if(news_type=="news_life"){
            imageFile = "images/icons/map_life.png";
        }
        else if(news_type=="news_blog"){
            imageFile = "images/icons/map_blog.png";
        }
        markerImage = new google.maps.MarkerImage(imageFile,
            new google.maps.Size(71, 71),
            new google.maps.Point(0, 0),
            new google.maps.Point(17, 34),
            new google.maps.Size(40, 40));
        var marker = new google.maps.Marker({
            position: latlng,
            info_content: <?php echo "'".$news_title."'";?>,
            icon: markerImage,
            animation: google.maps.Animation.DROP,
            map: map
        });

        google.maps.event.addListener(map, 'click', function(event) {
        	//TODO get address and lat lng after clicking
        });
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.open(map,marker);
        });
    }
    
    google.maps.event.addDomListener(window, 'load', initialize);

    var voted = false;
    var number_element;
    var logged_in = <?php if(isset($_SESSION ['username'])) { echo json_encode("true");}
						  else{ echo json_encode("false");} ?>;
	
	var vote_giver_id = <?php if(isset($_SESSION ['user_id'])){ echo json_encode($_SESSION ['user_id']);}
						     else{ echo json_encode("none");} ?>;


    function voteForNews(clickID){
    	var news_comment_id = <?php echo json_encode($news_id);?>;
    	var vote_receiver_id = <?php echo json_encode($news_author_id);?>;
	    var action = "";
	    var type = 'news';

		if(logged_in == "true"){
			if(clickID == "news_upvote"){
				action = "news_upvote";
				number_element = document.getElementById('news_upvote_number');
				
			}
			else if(clickID == "news_downvote"){
				action = "news_downvote";
				number_element = document.getElementById('news_downvote_number');
			}
			else if(clickID == "news_spam"){
				action = "news_spam";
				number_element = document.getElementById('news_spam_number');
			}
			number_string = number_element.innerHTML;
            //number_element = "<img src='images/loader.gif'>";
			var number = parseInt(number_string);
			number = number+1;
			voted = true;
 			$.ajax({
 				type: "post",
                url: "backend_php/ajax/submit_vote.php",
                data: {'action': action, 
                       'type': type, 
                       'news_comment_id': news_comment_id, 
                       'no_of_vote': number, 
                       'vote_receiver_id': vote_receiver_id, 
                       'vote_giver_id': vote_giver_id },
                success:function(data){
                    var msg = "系統出錯! 你的評分未能送出!";
                	if(data == 5){
                        msg = "系統出錯! 你的評分未能送出!";
                    }else if(data == 1){
                        msg = "你不能給自己評分!";        
                    }else if(data == 2){
                        msg = "你之前已經評分!";        
                    }else if(data == 3){
                        msg = "回報已經送出!";
                    }else if(data == 4){
                        msg = "你沒有足夠的分數!";
                    }else{
                        number_element.innerHTML = number;
                        msg = "";
                    }
                    if(msg.length>1){
                        vex.dialog.confirm({            
                            message: msg,            
                            callback: function(value) {}
                        });
                    }
                    
                }
            });
			
			return false;
		}
		else if(logged_in != "true"){
            vex.dialog.confirm({            
                message: "<li><a href='facebook_login.php'>請先登入!</a></li>",            
                callback: function(value) {}
            });
		}

	}

	function voteForComment(action_code, comment_id, vote_receiver_id){
    	var news_comment_id = comment_id;
    	var vote_receiver_id = vote_receiver_id;
    	var action = "";
	    var type = 'comment';

		if(logged_in == "true"){
			if(action_code == 1){
				action = "comment_upvote";
				number_element = document.getElementById('comment_upvote_number_'+comment_id);
				
			}
			else if(action_code == 2){
				action = "comment_downvote";
				number_element = document.getElementById('comment_downvote_number_'+comment_id);
			}
			else if(action_code == 3){
				action = "comment_spam";
				number_element = document.getElementById('comment_spam_number_'+comment_id);
			}
			number_string = number_element.innerHTML;
			var number = parseInt(number_string);
			number = number+1;
			


            

 			$.ajax({
 				type: "post",
                url: "backend_php/ajax/submit_vote.php",
                data: {'action': action, 
                       'type': type, 
                       'news_comment_id': news_comment_id, 
                       'no_of_vote': number, 
                       'vote_receiver_id': vote_receiver_id, 
                       'vote_giver_id': vote_giver_id },
                success:function(data){
                	var msg = "系統出錯! 你的評分未能送出!";
                    if(data == 5){
                        msg = "系統出錯! 你的評分未能送出!";
                    }else if(data == 1){
                        msg = "你不能給自己評分!";        
                    }else if(data == 2){
                        msg = "你之前已經評分!";        
                    }else if(data == 3){
                        msg = "回報已經送出!";
                    }else if(data == 4){
                        msg = "你沒有足夠的分數!";
                    }else{
                        number_element.innerHTML = number;
                        msg = "";
                    }
                    if(msg.length>1){
                        vex.dialog.confirm({            
                            message: msg,            
                            callback: function(value) {}
                        });
                    }
                }
            });
			
			return false;
		}
		else if(logged_in != "true"){
			vex.dialog.confirm({            
                message: "<li><a href='facebook_login.php'>請先登入!</a></li>",            
                callback: function(value) {}
            });
		}

	}

	$(document).ready(function() {
		var type = "news";
		var news_comment_id = <?php echo "'".$news_id."'"?>;
	    $("#comments-list-section").load("backend_php/ajax/fetch_comment.php", {'page':0, 
				 'type':type, 
				 'news_comment_id':news_comment_id}, function(){$("#1-page").addClass('current');});  //initial page number to load
		$(".paging").click(function (e) {
		
			$("#comments-list-section").prepend('<div class="loading-indication"><img src="ajax-loader.gif" /> Loading...</div>');
			var clicked_id = $(this).attr("id").split("-"); //ID of clicked element, split() to get page number.
			var page_num = parseInt(clicked_id[0]); //clicked_id[0] holds the page number we need 
			var type = "news";
			var news_comment_id = <?php echo "'".$news_id."'"?>;
			$('.paging').removeClass('current'); //remove any active class
      		//post page number and load returned data into result element
      		//notice (page_num-1), subtract 1 to get actual starting point
			$("#comments-list-section").load("backend_php/ajax/fetch_comment.php", 
				{'page':(page_num-1), 
				 'type':type, 
				 'news_comment_id':news_comment_id}, function(){});
			$(this).addClass('current'); //add active class to currently clicked element (style purpose)
			return false; //prevent going to herf 
		});	

        $('#news_pic').fancybox({helpers: {
            overlay: {
                locked: false
            }
        }});

	});

    
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
<body class="sub-1sidebar">
<?php include('header.php');?>
<div class="wrapper">
	<div id="main-content">
		<div class="primary">
			<article class="entry-item main-article">
				<header>
					<h2 class="entry-title"><?php echo $news_title; ?></h2>					          
					<span class="entry-news-info"><i class="fa fa-fw fa-user"></i>:&nbsp;</span><a class="entry-author" href=
					<?php
					    //TODO wait for the user public profile page 
					    echo '"user_profile.php?user_id='.$news_author_id.'"';?>>
					<?php echo $author_name; ?></a>
					
					<?php
					    if(strlen($news_address)>1){
					    	$link="";
					    	echo "<span class='entry-news-info'><i class='fa fa-fw fa-map-marker'></i>:&nbsp;</span>";
					    	echo "<a class='entry-category' href='".$link."'>".$news_address."</a><span class='entry-date'>&nbsp;&nbsp;|";
					    }
					    else{
					    	echo "<span class='entry-date'>";
					    }
					    echo "&nbsp;&nbsp;";
					    if(strlen($news_date)>1){
					    	echo $news_date;
					    }
					    else{
					    	echo date("d/m/Y", strtotime($news_post_date_time));
					    }
					    echo "</span>";
					    
					?>


					<br>
					
					<span class="entry-news-info"><i class="fa fa-fw fa-eye"></i>:&nbsp;</span><span id="read_number"><?php echo $news_no_read; ?>&nbsp;</span>
					<span class="entry-news-info clickable"><i id="news_upvote" onClick="voteForNews(this.id)" class="glyphicon glyphicon-fire"></i>:&nbsp;</span><span id="news_upvote_number"><?php echo $news_no_useful; ?>&nbsp;</span>
					<span class="entry-news-info clickable"><i id="news_downvote" onClick="voteForNews(this.id)" class="fa fa-fw fa-fire-extinguisher"></i>:&nbsp;</span><span id="news_downvote_number"><?php echo $news_no_rubbish; ?>&nbsp;</span>
					<span class="entry-news-info clickable"><i id="news_spam" onClick="voteForNews(this.id)" class="fa fa-fw fa-trash-o"></i>:&nbsp;</span><span id="news_spam_number"><?php echo $news_no_spam; ?>&nbsp;&nbsp;</span>
					
					<?php 
					if(strlen($news_tag_1)>0||strlen($news_tag_2)>0||strlen($news_tag_3)>0||strlen($news_tag_4)>0){
						echo"<span class='entry-news-info'><i id='' class='fa fa-tags'></i>:&nbsp;</span>";
					}
					if(strlen($news_tag_1)>0){
					    echo "<a class='entry-category' href='search_result.php?search_type=tag&search_key=".$news_tag_1."'>#".$news_tag_1."</a><span class='entry-date'>&nbsp;&nbsp;";
					} 
					if(strlen($news_tag_2)>0){
					    echo "|&nbsp;<a class='entry-category' href='search_result.php?search_type=tag&search_key=".$news_tag_2."'>#".$news_tag_2."</a><span class='entry-date'>&nbsp;&nbsp;";
					} 
					if(strlen($news_tag_3)>0){
					    echo "|&nbsp;<a class='entry-category' href='search_result.php?search_type=tag&search_key=".$news_tag_3."'>#".$news_tag_3."</a><span class='entry-date'>&nbsp;&nbsp;";
					} 
					if(strlen($news_tag_4)>0){
					    echo "|&nbsp;<a class='entry-category' href='search_result.php?search_type=tag&search_key=".$news_tag_4."'>#".$news_tag_4."</a><span class='entry-date'>&nbsp;&nbsp;";
					} 
                    echo "</span>";
                    echo "&nbsp;分類: <span class='entry-date'><a class='entry-category' href='secondary.php?category=".$news_type."'>".$news_type_name."</a></span>";

					?>
				</header>	

					<?php
					 if(strlen($news_address)>1){
					    echo '<div id="map-canvas"></div>';
					}else{
						echo "<br><br>";
					}
					
					    
				?>
				

				<?php
				    if($news_pic1!="" && strlen($news_pic1)>1){
                        echo '<div id="post-container"><div class="image-container">';
                        echo "<a id='news_pic' href='../".GW_IMAGEPATH.$news_pic1."'>";
				    	echo "<img class='responsive-img feature-img' src='../".GW_IMAGEPATH.$news_pic1."' alt='' ></img></a>";
                        echo '</div></div>';
				    }
				?>
				
				<?php echo $news_content; ?>
			<div class="clear"></div>
			</article>
			<div class="social-share clearfix">
				<h6>轉發到: </h6>
				<div class="social-share-button clearfix">
                    <span class="social-share-facebook">
                        <div id="fb-root"></div>
                        <script>(function(d, s, id) {
                            var js, fjs = d.getElementsByTagName(s)[0];
                            if (d.getElementById(id)) return;
                            js = d.createElement(s); js.id = id;
                            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=350593555103996&version=v2.0";
                            fjs.parentNode.insertBefore(js, fjs);
                        }(document, 'script', 'facebook-jssdk'));</script>
                        <?php 
                          $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                          echo '<span class="share_button btn_fb">';
                          echo '<span class="fb-like" data-title="'.$news_title.'" data-img="'.BASE_URL.GW_IMAGEPATH.$news_pic1.'" data-href="'.$actual_link.'" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></span>';
                          echo '</span>';            
                        ?>
                    </span>

					<!--Begin  twitter-->
					<span class="social-share-twitter">
						<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
						<a href="http://www.cssmoban.com/" class="twitter-share-button">Tweet</a>
					</span><!--twitter-share-->
					<!--Begin  facebook-->

                    <span class="social-share-google-plus">
                        <!-- Place this tag where you want the +1 button to render. -->
                        <div class="g-plusone" data-size="medium"></div>

                        <!-- Place this tag after the last +1 button tag. -->
                        <script type="text/javascript">
                            (function() {
                                var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                                po.src = 'https://apis.google.com/js/plusone.js';
                                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                            })();
                        </script>
                    </span>

					<!--end  facebook-->

					
					
				</div><!--social-share-button-->
			</div><!--social-share-->        
			<div class="about-author">
				<h3>發佈者:<?php echo $author_name;?></h3>
				<div class="about-author-content"> 
					<?php
                    echo '<a href="user_profile.php?user_id='.$news_author_id.'"><img class="responsive-img news-profile-img" alt="" src=';
					if(strlen($author_pic)>0){
						echo "'".GW_USER_IMAGEPATH.$author_pic."'";
					}else{
						echo "'placeholders/avatar/author.png'" ;
					}
					echo " /></a><p>".$author_info."</p>";
					?>

					
					<div class="clear"></div>
				</div><!--about-author-content--> 
			</div><!--about-author-->
			<section class="related-posts">
				<h3>相關新聞</h3>
				<ul class="clearfix">
                    <?php
                    $mysql_tag_query = "";
                    if(strlen($news_tag_1)>0){
                        $mysql_tag_query .= ' OR `news_tag_1` ="'.$news_tag_1.'" OR  `news_tag_2` ="'.$news_tag_1.'" OR  `news_tag_3` ="'.$news_tag_1.'" OR  `news_tag_4` ="'.$news_tag_1.'" ';
                    } 
                    if(strlen($news_tag_2)>0){
                        $mysql_tag_query .= ' OR `news_tag_1` ="'.$news_tag_2.'" OR  `news_tag_2` ="'.$news_tag_2.'" OR  `news_tag_3` ="'.$news_tag_2.'" OR  `news_tag_4` ="'.$news_tag_2.'" ';
                    } 
                    if(strlen($news_tag_3)>0){
                        $mysql_tag_query .= ' OR `news_tag_1` ="'.$news_tag_3.'" OR  `news_tag_2` ="'.$news_tag_3.'" OR  `news_tag_3` ="'.$news_tag_3.'" OR  `news_tag_4` ="'.$news_tag_3.'" ';
                    } 
                    if(strlen($news_tag_4)>0){
                        $mysql_tag_query .= ' OR `news_tag_1` ="'.$news_tag_4.'" OR  `news_tag_2` ="'.$news_tag_4.'" OR  `news_tag_3` ="'.$news_tag_4.'" OR  `news_tag_4` ="'.$news_tag_4.'" ';
                    } 
                    $mysql = "SELECT * FROM ".DB_TABLE_NEWS." WHERE (`news_type` ='".$news_type."'".$mysql_tag_query.") AND `news_id` <>".$news_id." ORDER BY RAND() LIMIT 4";
                    $stmt = $dbc->prepare($mysql);
                    try{
                        $stmt->execute();
                    }catch(Exception $e){
                        msg_log($DEBUG_TAG." : ".$e);
                    }
                    while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                        echo"<li><article class='clearfix'>";
                        if(strlen($row['news_pic1'])>1){
                            echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="responsive-img relative-random-column-pic" src="../news_images/'.$row['news_pic1'].'" alt="" /></a>';
                        }
                        else{
                            echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="responsive-img" src="placeholders/139x116/1.jpg" alt="" /></a>';
                        }
                        echo '<h6><a href="primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a></h6>';
                        echo"</article></li>";
                    }
                    ?>
					
				</ul><!--end:related-posts-->
			</section><!--end:related-posts--> 
			<section id="comments" class="clearfix">
				<h2 id= "number_of_comment" class="comment-box-title"><?php echo $rows." 則評論"?></h2>
				
				<ol class="comments-list">
					<section id="comments-list-section">

					</section>
				</ol>
			    
				<?php echo $pagination;?>
				<div id="respond">
					<h3>發表評論</h3>       
					<?php
					    if(isset($_SESSION ['username'])){
					    	echo '
					    	<form id="comments_form" class="clearfix" method="post" data-role="validator" novalidate="novalidate" action=""/>                
        						<div class="comment-left">
							        <p class="textarea-block">                        
								        <label class="required" for="comment-message">評論 (請注意，評論的好壞亦會影響你的分數):</label><br />
								        <input id="comment_type" name="comment_type" type="hidden" value="news">
								        <input id="comment_news_comment_id" name="comment_news_comment_id" type="hidden" value="'.$news_id.'">
								        <textarea validationmessage="Please enter your message" required="" rows="6" cols="88" id="comment-message" name="comment_content"></textarea>
							        </p>
						        </div>
						        <div class="clear"></div>
						            <p class="comment-button" />                    
							            <input type="submit" id="submit-comment" value="Submit" />
							        <p id="status-message">
						        </p>                        
					        </form>';
					    }
					    else{
					    	echo "<a href='".LOGIN_PAGE."?from=news_primary&news_id=".$news_id."'>請先登入!</a>";
					    	msg_log($DEBUG_TAG.": ". "<a href='".LOGIN_PAGE."?from=news_primary&news_id=".$news_id."'>請先登入!</a>");
					    }
					?>        
					<script type="text/javascript">
					//callback handler for form submit
					
					$("#comments_form").submit(function(e)
					{
						var val = document.getElementById('comment-message').value;
                    	if (val==null||val=="") {
                            ///^\s*$/g.test(val)||
                            alert('請輸入評論內容！');
                            return false;
                        }        
                        // validate and process form here
                        if(submit_comment != true && submit_comment != "true"){
                        	submit_comment = true;
                        }else{
                        	return false;
                        }
					    var postData = $(this).serializeArray();
					    var formURL = 'backend_php/ajax/commnet_form_submit.php';
					    $.ajax(
					    {
					    	url : formURL,
                            type: "POST",
                            data : postData,
                            success:function(data, textStatus, jqXHR) 
                            {
                                //data: return data from server
                                var type = "news";
                                var news_comment_id = <?php echo "'".$news_id."'";?>; 
                                $("#comments-list-section").load("backend_php/ajax/fetch_comment.php", {'page':0, 
                                	'type':type, 
                                	'news_comment_id':news_comment_id
                                }, 
                                function(){$("#1-page").addClass('current');}); 
                                document.getElementById("comment-message").value = "";
                                document.getElementById("number_of_comment").innerHTML = <?php $rows = $rows+1; echo "'".$rows." 則評論'"; ?>;
                                document.getElementById('comments').scrollIntoView();


                            },
                            error: function(jqXHR, textStatus, errorThrown) 
                            {
                                //if fails      
                            }
                        });
                        e.preventDefault(); //STOP default action
                        e.unbind(); //unbind. to stop multiple form submit.
                    });
                    $("#submit-comment").click(function() {
                    	//alert(submit_comment);
                     });
					</script>
				</div><!--end:respond--> 
			</section>
		</div><!--end:primary-->
		<div class="secondary">
			<div id="sidebar-b" class="sidebar">
				<aside class="widget box-hover">
					<header class="entry-header">
						<h4 class="widget-title clearfix"><span>最熱<?php echo $news_type_name?></span></h4>					
					</header>
					<?php

                    $stmt = $dbc->prepare("SELECT * ,(`news_no_comment`*0.8+`news_no_useful`*0.6+`news_no_read`*0.05-`news_no_rubbish`*0.4) AS `news_hotness`
                         FROM ".DB_TABLE_NEWS." WHERE `news_post_date_time` > DATE_SUB(curdate(), INTERVAL 1 DAY) AND `news_type` ='".$news_type."' ORDER BY `news_hotness` DESC LIMIT 1");
                    try{
                        $stmt->execute();
                    }catch(Exception $e){
                        msg_log($DEBUG_TAG." : ".$e);
                    }
                    while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                        echo'<article class="entry-box clearfix">';
                        if(strlen($row['news_pic1'])>1){
                            echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="hover-effect responsive-img" src="../news_images/'.$row['news_pic1'].'" alt="" /></a>';
                        }
                        echo '<div class="entry-content">';
                        if(strlen($row['news_address'])>1){
                            echo '<span class="entry-author"><i class="fa fa-fw fa-map-marker"></i>:&nbsp;</span>';
                            echo '<a class="entry-category" >'.$row['news_address'].'</a>';
                        }
                        echo '<span class="entry-date">&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-calendar-o"></i>:&nbsp;</span>&nbsp;&nbsp;'.$row['news_date'].'</span>';
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
                         FROM ".DB_TABLE_NEWS." WHERE `news_post_date_time` > DATE_SUB(curdate(), INTERVAL 1 DAY) AND `news_type` ='".$news_type."' ORDER BY `news_hotness` DESC LIMIT 1, 10");
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
		<?php include("footer.php") ?>

	</div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>