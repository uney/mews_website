<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."msg_constant.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookSession.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookRedirectLoginHelper.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookRequest.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookResponse.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookSDKException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookRequestException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookAuthorizationException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."GraphObject.php");

 
    use Facebook\FacebookSession;
    use Facebook\FacebookRedirectLoginHelper;
    use Facebook\FacebookRequest;
    use Facebook\FacebookResponse;
    use Facebook\FacebookSDKException;
    use Facebook\FacebookRequestException;
    use Facebook\FacebookAuthorizationException;
    use Facebook\GraphObject;





    $DEBUG_TAG = "index";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;
    $rows = 0;
    $page_offset = 3;
    $item_per_page = 5;
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
    try {
    	$stmt = $dbc->prepare ( "SELECT `news_id` FROM ".DB_TABLE_NEWS." WHERE `news_type` <>'news_blog' AND `news_type` <>'news_funny' AND `news_type` <>'news_life' AND `news_type` <>'news_event' AND `news_post_date_time` >DATE_SUB(DATE(NOW()), INTERVAL 30 DAY) LIMIT 45" ); 
    	$stmt->execute ();
        $rows = $stmt->rowCount();

    } catch ( Exception $e ) {
    	msg_log ( $DEBUG_TAG. "pagination: " . $e->getMessage () );
    }
    include("login_check.php");

    //break total records into pages
    $pages = ceil(($rows-$page_offset)/$item_per_page);	
    $pagination	= '';
    //create pagination
    if($pages > 1)
    {
    	$pagination	.= '<ul class="pagination clearfix">';
    	for($i = 1; $i<$pages+1; $i++)
    	{
    		$pagination .= '<li><a href="#" class="paging" id="'.$i.'-page">'.$i.'</a></li>';
    	}
    	$pagination .= '</ul>';
    }
    include("login_check.php");


?>
<!DOCTYPE html>
<!--
      __  __ ________          _______ 
     |  \/  |  ____\ \        / / ____|
     | \  / | |__   \ \  /\  / / (___  
     | |\/| |  __|   \ \/  \/ / \___ \ 
     | |  | | |____   \  /\  /  ____) |
     |_|  |_|______|   \/  \/  |_____/ 
                                       
     handcrafted with love by MEWS Team
     join us: http://www.mews.mobi/primary.php?news_id=25
     www.mews.com
    -->
<html>
<head>
    <meta charset="UTF-8">
    <?php 
    echo '<meta property="og:image" content="'.BASE_URL.'images/mews_icon.png"/>';     
    echo '<meta property="og:url" content="'.HOME_PAGE.'" />';
    echo '<meta property="og:title" content="MEWS" />';
    echo '<meta property="og:description" content="MEWS(新聞地圖) 是一個完全由用戶主導的新聞社區，我們確信「去中心化」是控制的克星，在這裏你不再只是讀者，只要你願意，你也可以登高一呼，讓所有人聽到你的聲音。" />';
    ?>
	<title>MEWS 新聞地圖</title>
    <meta name=“description” content=“mews is a hong kong opensource news platform with location search function, we help user to find news by using their geolocation”>
    <meta name='keywords' content='Hong Kong,Mews,MEWS,新聞,地圖,新聞地圖,公民新聞,公民,news,香港,民主,佔中,反佔中,中國,意外,身邊事'/>
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
    
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

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
	    $("#hong_kong_news").load("backend_php/ajax/fetch_pages.php", {'page':0, 'offset':<?php echo $page_offset;?>}, function() {$("#1-page").addClass('current');});  //initial page number to load
		$(".paging").click(function (e) {
		
			$("#hong_kong_news").prepend('<div class="loading-indication"><img src="ajax-loader.gif" /> Loading...</div>');
			var clicked_id = $(this).attr("id").split("-"); //ID of clicked element, split() to get page number.
			var page_num = parseInt(clicked_id[0]); //clicked_id[0] holds the page number we need 
		
			$('.paging').removeClass('current'); //remove any active class
      		//post page number and load returned data into result element
      		//notice (page_num-1), subtract 1 to get actual starting point
			$("#hong_kong_news").load("backend_php/ajax/fetch_pages.php", {'page':(page_num-1), 'offset':<?php echo $page_offset;?>}, function(){});
			$(this).addClass('current'); //add active class to currently clicked element (style purpose)
			return false; //prevent going to herf 
		});	
	});

    </script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-54036500-1', 'auto');
  ga('require', 'displayfeatures');
  ga('send', 'pageview');

</script>
	<!--[if lt IE 9]>
	<script type="text/javascript" src="js/ie7-fixed.js"></script>
	<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>
<body class="home-page">
	<script>
    // This is called with the results from from FB.getLoginStatus().
    function statusChangeCallback(response) {
        console.log('statusChangeCallback');
        console.log(response);
        // The response object is returned with a status field that lets the
        // app know the current login status of the person.
        // Full docs on the response object can be found in the documentation
        // for FB.getLoginStatus().
        if (response.status === 'connected') {
            // Logged into your app and Facebook.
            //testAPI();
            //document.getElementById('login').style.visibility="hidden";
            document.getElementById('login').remove();
            document.getElementById('logout').style.visibility="visible";
        } else if (response.status === 'not_authorized') {
        	document.getElementById('login').style.visibility="visible";
            document.getElementById('logout').style.visibility="hidden";
            // The person is logged into Facebook, but not your app.
            //document.getElementById('status').innerHTML = 'Please log '+'into this app.';
        } else {
        	document.getElementById('login').style.visibility="visible";
            document.getElementById('logout').style.visibility="hidden";
            // The person is not logged into Facebook, so we're not sure if
            // they are logged into this app or not.
            //document.getElementById('status').innerHTML = 'Please log ' + 'into Facebook.';
        }
    }

    // This function is called when someone finishes with the Login
    // Button.    See the onlogin handler attached to it in the sample
    // code below.
    function checkLoginState() {
        FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
        });
    }

    window.fbAsyncInit = function() {
    FB.init({
        appId : '135716029958952',
        cookie : true, // enable cookies to allow the server to access 
                       // the session
        xfbml : true,  // parse social plugins on this page
        version : 'v2.0' // use version 2.0
    });

    // Now that we've initialized the JavaScript SDK, we call 
    // FB.getLoginStatus().This function gets the state of the
    // person visiting this page and can return one of three states to
    // the callback you provide.    They can be:
    //
    // 1. Logged into your app ('connected')
    // 2. Logged into Facebook, but not your app ('not_authorized')
    // 3. Not logged into Facebook and can't tell if they are logged into
    //    your app or not.
    //
    // These three cases are handled in the callback function.

    FB.getLoginStatus(function(response) {
        //statusChangeCallback(response);
    });

    };

    // Load the SDK asynchronously
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    // Here we run a very simple test of the Graph API after login is
    // successful.    See statusChangeCallback() for when this call is made.
    function testAPI() {
        console.log('Welcome!    Fetching your information.... ');
        FB.api('/me', function(response) {
            console.log('Successful login for: ' + response.name);
            document.getElementById('status').innerHTML =
                'Thanks for logging in, ' + response.name + '!';
        });
    }


</script>
<?php
    include("header.php");
?>

<div class="wrapper">
	<div id="main-content">
		<div class="primary">
			<div class="slider-box-hover">	
				<div class="slider-wrapper">
                    <?php                                            
                    $table = DB_TABLE_NEWS;
                    $mysql_headline_stmt = "SELECT * ,(`news_no_comment`*0.8+`news_no_useful`*0.6+`news_no_read`*0.05-`news_no_rubbish`*0.4)/TIMEDIFF(NOW(),`news_post_date_time`) AS `news_hotness`
                            FROM $table WHERE `news_post_date_time` > DATE_SUB(DATE(NOW()), INTERVAL 30 DAY) AND `news_type` <>'news_blog' AND `news_is_hot` =0 ORDER BY `news_hotness` DESC LIMIT $page_offset";
                    $stmt2=$dbc->prepare("SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_is_hot` =1");
                    $stmt2->execute();
                    $no_of_hot = $stmt2->rowCount();
                    
                    ?>
					<div id="slider" class="nivoSlider">
                    <?php
                        $doc = new DOMDocument();
                        $i = 1;
                        if($no_of_hot>0){
                            while($row2=$stmt2->fetch(PDO::FETCH_ASSOC)){
                                $news_pic = DEFAULT_NEWS_IMG;
                                if(strlen($row2['news_pic1'])>1){
                                    $news_pic = $row2['news_pic1'];
                                }
                                echo '<img src="../'.GW_IMAGEPATH.$news_pic.'" alt="" title="#slide-caption-'.$i.'"/>';
                                $i++;
                            }
                        }
                        try {
                            $stmt = $dbc->prepare ($mysql_headline_stmt); 
                            $stmt->execute ();
                        }
                        catch ( Exception $e ) {
                           echo $DEBUG_TAG. ": " . $e->getMessage ();
                           msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
                       }
                        while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                            $news_pic = DEFAULT_NEWS_IMG;
                            if(strlen($row['news_pic1'])>1){
                                $news_pic = $row['news_pic1'];
                            }
                            echo '<img src="../'.GW_IMAGEPATH.$news_pic.'" alt="" title="#slide-caption-'.$i.'"/>';
                            $i++;
                        }
                        ?>

					</div>
                    <?php
                    
                    $i=0;
                    if($no_of_hot>0){
                        $table = DB_TABLE_NEWS;
                        $stmt2 = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_is_hot` =1" ); 
                        $stmt2->execute ();
                        while($row = $stmt2->fetch ( PDO::FETCH_ASSOC )){
                            $i++;
                            $author_name = "";
                            $news_type = "身邊事";
                            if($row['news_type']=="news_crime"){
                                $news_type = "罪案報導";
                            }else if($row['news_type']=="news_politics"){
                                $news_type = "政治熱話";
                            }else if($row['news_type']=="news_accident"){
                                $news_type = "意外直擊";
                            }else if($row['news_type']=="news_event"){
                                $news_type = "活動盛事";
                            }else if($row['news_type']=="news_other"){
                                $news_type = "其他新聞";
                            }else if($row['news_type']=="news_funny"){
                                $news_type = "奇聞趣事";
                            }else if($row['news_type']=="news_tech"){
                                $news_type = "科技資訊";
                            }else if($row['news_type']=="news_life"){
                                $news_type = "生活消閒";
                            }else if($row['news_type']=="news_business"){
                                $news_type = "商業財經";
                            }else if($row['news_type']=="news_blog"){
                                $news_type = "專場報道";
                            }
                            /*
                    <div id="slide-caption-1" class="nivo-html-caption">
                        <header>                    
                            <span class="entry-category">in:&nbsp;</span>
                            <a class="entry-category" href="#">Fashion</a><span class="entry-date">&nbsp;&nbsp;|&nbsp;&nbsp;07.08.2012</span>
                            <h1><a href="single.html">Welcome to the Dummy Text Generator!</a></h1>
                        </header>
                        <p>This handy tool helps you create dummy text for all your layout needs.We are gradually adding new functionality and we welcome your suggestions and feedback. Please feel free to send us any additional dummy texts.</p>
                        <span class="entry-author"><i class="fa fa-fw fa-user"></i>:&nbsp;</span><a class="entry-author" href="#">Admin</a>
                        <span class="entry-author"><i class="fa fa-fw fa-eye"></i>:&nbsp;</span>123
                        <span class="entry-author"><i class="fa fa-fw fa-arrow-circle-up"></i>:&nbsp;</span>123
                        <span class="entry-author"><i class="fa fa-fw fa-arrow-circle-down"></i>:&nbsp;</span>123
                    </div>

                            */
                            echo '<div id="slide-caption-'.$i.'" class="nivo-html-caption">';
                            echo '<header><span class="entry-category">分類:&nbsp;</span><a class="entry-category" href="secondary.php?category='.$row['news_type'].'">'.$news_type.'</a><span class="entry-date">&nbsp;&nbsp;|&nbsp;&nbsp;'.$row['news_date'].'</span>';
                            echo '<h1><a href="primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a></h1>';
                            $doc->loadHTML(mb_convert_encoding($row['news_content'], 'HTML-ENTITIES', 'UTF-8'));
                            $news_preview_element = $doc->getElementsByTagName('span')->item(0);
                            $news_preview = $news_preview_element->nodeValue;
                            $news_preview = mb_strcut($news_preview, 0, 300, 'utf-8');
                            $news_preview .="...";
                            echo '<p>'.$news_preview.'</p>';
                            $stmt2 = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER." WHERE user_id =?");
                            $stmt2->bindValue(1, $row['news_author_id'], PDO::PARAM_STR);
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
                        }
                    }
                    try {
                        $table = DB_TABLE_NEWS;
                        $stmt = $dbc->prepare ( $mysql_headline_stmt ); 
                        $stmt->execute ();
                    }
                    catch ( Exception $e ) {
                        echo $DEBUG_TAG. ": " . $e->getMessage ();
                        msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
                    }
                    while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                            $i++;
                            $author_name = "";
                            $news_type = "身邊事";
                            if($row['news_type']=="news_crime"){
                                $news_type = "罪案報導";
                            }else if($row['news_type']=="news_politics"){
                                $news_type = "政治熱話";
                            }else if($row['news_type']=="news_accident"){
                                $news_type = "意外直擊";
                            }else if($row['news_type']=="news_event"){
                                $news_type = "活動盛事";
                            }else if($row['news_type']=="news_other"){
                                $news_type = "其他新聞";
                            }else if($row['news_type']=="news_funny"){
                                $news_type = "奇聞趣事";
                            }else if($row['news_type']=="news_tech"){
                                $news_type = "科技資訊";
                            }else if($row['news_type']=="news_life"){
                                $news_type = "生活消閒";
                            }else if($row['news_type']=="news_business"){
                                $news_type = "商業財經";
                            }else if($row['news_type']=="news_blog"){
                                $news_type = "專場報道";
                            }
                            /*
                    <div id="slide-caption-1" class="nivo-html-caption">
                        <header>                    
                            <span class="entry-category">in:&nbsp;</span>
                            <a class="entry-category" href="#">Fashion</a><span class="entry-date">&nbsp;&nbsp;|&nbsp;&nbsp;07.08.2012</span>
                            <h1><a href="single.html">Welcome to the Dummy Text Generator!</a></h1>
                        </header>
                        <p>This handy tool helps you create dummy text for all your layout needs.We are gradually adding new functionality and we welcome your suggestions and feedback. Please feel free to send us any additional dummy texts.</p>
                        <span class="entry-author"><i class="fa fa-fw fa-user"></i>:&nbsp;</span><a class="entry-author" href="#">Admin</a>
                        <span class="entry-author"><i class="fa fa-fw fa-eye"></i>:&nbsp;</span>123
                        <span class="entry-author"><i class="fa fa-fw fa-arrow-circle-up"></i>:&nbsp;</span>123
                        <span class="entry-author"><i class="fa fa-fw fa-arrow-circle-down"></i>:&nbsp;</span>123
                    </div>

                            */
                            echo '<div id="slide-caption-'.$i.'" class="nivo-html-caption">';
                            echo '<header><span class="entry-category">分類:&nbsp;</span><a class="entry-category" href="secondary.php?category='.$row['news_type'].'">'.$news_type.'</a><span class="entry-date">&nbsp;&nbsp;|&nbsp;&nbsp;'.$row['news_date'].'</span>';
                            echo '<h1><a href="primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a></h1>';
                            $doc->loadHTML(mb_convert_encoding($row['news_content'], 'HTML-ENTITIES', 'UTF-8'));
                            $news_preview_element = $doc->getElementsByTagName('span')->item(0);
                            $news_preview = $news_preview_element->nodeValue;
                            $news_preview = mb_strcut($news_preview, 0, 300, 'utf-8');
                            $news_preview .="...";
                            echo '<p>'.$news_preview.'</p>';
                            $stmt2 = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER." WHERE user_id =?");
                            $stmt2->bindValue(1, $row['news_author_id'], PDO::PARAM_STR);
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
                        }
                    ?>

				</div><!--end:slider-wrapper-->
			</div><!--end:slider-box-hover-->	
			<section id="main-section">
				<header class="entry-header">
					<div class="header-inner">
						<a href="secondary.php?category=news_big"><span>最熱新聞</span></a>
						<!--<select>
							<option selected="selected" value="" />熱門
							<option value="" />最新
						</select>
					</div>-->
                    <!--header-inner-->
				</header>
                <div id="hong_kong_news"></div>
				
				<div class="pagination-wrap">
					<?php echo $pagination;?>

				</div><!--pagination-wrap-->
			</section><!--end:main-section-->
		</div><!-- end:/primary -->
		<div class="secondary">
			<div id="top-sidebar">
				<section class="feature-widget-non-hove">
						<a href="secondary.php?category=news_blog"><h1 class="widget-title clearfix"><span>個人專場(Blog)</span></h1></a>					
                    <div class="list-carousel responsive">
                        <ul class="feature-news clearfix">
                        <?php
                        $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_type` ='news_blog' ORDER BY `news_post_date_time` DESC LIMIT 6");
                        try{
                            $stmt->execute();
                        }catch(Exception $e){
                            msg_log($DEBUG_TAG." : ".$e);
                        }
                        while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                            echo'<li><article><div class="feature-item box-hover clearfix" small-list>';
                            $news_pic = DEFAULT_NEWS_IMG;
                            if(strlen($row['news_pic1'])>1){
                                $news_pic = $row['news_pic1'];
                            }
                            echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="hover-effect responsive-img" src="../news_images/'.$news_pic.'" alt="" /></a>';
                            echo '<div class="entry-content">';
                            echo '<span class="entry-author">&nbsp;&nbsp;<i class="fa fa-fw fa-calendar-o"></i>:&nbsp;&nbsp;&nbsp;'.$row['news_date'].'</span>';
                            echo '<h4 class="entry-title"><a href="primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a></h4>';
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
                            echo '</div></div></article></li>';
                            }
                            ?>
         
                        </ul><!--end:feature-news-->
                        <div class="clear"></div>
                        <div class="feature-nav">
                            <a id="prev-1" class="prev" href="#">&nbsp;</a>
                            <a id="next-1" class="next" href="#">&nbsp;</a>
                        </div><!--feature-nav-->
                    </div><!--end:list-carousel--> 
				</section><!--end:feature-widget-->
			</div><!--top-sidebar-->

			<div class="sidebar" id="sidebar-a">
				<header class="entry-header">
					<div class="header-inner">
						<a href="secondary.php?category=news_event"><span>活動盛事</span></a>
						
					</div><!--header-inner-->
				</header>
                <?php
                $stmt = $dbc->prepare("SELECT * ,(`news_no_comment`*0.8+`news_no_useful`*0.6+`news_no_read`*0.05-`news_no_rubbish`*0.4)/TIMEDIFF(NOW(),`news_post_date_time`) AS `news_hotness`
                   FROM $table WHERE `news_post_date_time` > DATE_SUB(curdate(), INTERVAL 3 DAY) AND `news_type` ='news_event' ORDER BY `news_hotness` DESC LIMIT 8");
                try{
                    $stmt->execute();
                }catch(Exception $e){
                    msg_log($DEBUG_TAG." : ".$e);
                }
                while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                    echo'<div class="article-wrap">';
                    echo'<article class="entry-item box-hover clearfix small-list">';
                    //if(strlen($row['news_pic1'])>1){
                    //    echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="hover-effect responsive-img" src="../news_images/'.$row['news_pic1'].'" alt="" /></a>';
                    //}
                    echo '<div class="entry-content">';
                    echo '<span class="entry-author"><i class="fa fa-fw fa-map-marker"></i>:&nbsp;</span>';
                    echo '<a class="entry-category" >'.$row['news_address'].'</a>';
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
                    echo '<span class="entry-date"><span class="entry-author"><i class="fa fa-fw fa-calendar-o"></i>:&nbsp;</span>&nbsp;&nbsp;'.$row['news_date'].'</span>&nbsp;';
                    echo '<span class="entry-author"><i class="fa fa-fw fa-user"></i>:&nbsp;</span><a href="user_profile.php?user_id='.$row['news_author_id'].'">'.$author_name.'</a><br>';
                    echo '<span class="entry-author"><i class="fa fa-fw fa-eye"></i>:&nbsp;</span>'.$row['news_no_read'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="glyphicon glyphicon-fire"></i>:&nbsp;</span>'.$row['news_no_useful'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-fire-extinguisher"></i>:&nbsp;</span>'.$row['news_no_rubbish'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-comment-o"></i>:&nbsp;</span>'.$row['news_no_comment'].
                            '</div>';
                    echo '</article>';
                    echo '</div>';
                }
                ?>

			</div><!--end:sidebar-->

			<div id="sidebar-b" class="sidebar">
				<header class="entry-header">
					<div class="header-inner">
						<a href="secondary.php?category=news_funny_and_life"><span>輕鬆消閒</span></a>
						
					</div><!--header-inner-->
				</header>
           
                <?php
                $stmt = $dbc->prepare("SELECT * ,(`news_no_comment`*0.8+`news_no_useful`*0.6+`news_no_read`*0.05-`news_no_rubbish`*0.4)/TIMEDIFF(NOW(),`news_post_date_time`) AS `news_hotness`
                   FROM $table WHERE `news_post_date_time` > DATE_SUB(curdate(), INTERVAL 3 DAY) AND (`news_type` ='news_funny' OR `news_type` ='news_life') ORDER BY `news_hotness` DESC LIMIT 8");
                try{
                    $stmt->execute();
                }catch(Exception $e){
                    msg_log($DEBUG_TAG." : ".$e);
                }
                while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                    echo'<div class="article-wrap">';
                    echo'<article class="entry-item box-hover clearfix small-list">';
                    //if(strlen($row['news_pic1'])>1){
                    //    echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="hover-effect responsive-img" src="../news_images/'.$row['news_pic1'].'" alt="" /></a>';
                    //}
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
                    echo '<span class="entry-date"><span class="entry-author"><i class="fa fa-fw fa-calendar-o"></i>:&nbsp;</span>&nbsp;&nbsp;'.$row['news_date'].'</span>&nbsp;';
                    echo '<span class="entry-author"><i class="fa fa-fw fa-user"></i>:&nbsp;</span><a href="user_profile.php?user_id='.$row['news_author_id'].'">'.$author_name.'</a><br>';
                    echo '<span class="entry-author"><i class="fa fa-fw fa-eye"></i>:&nbsp;</span>'.$row['news_no_read'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="glyphicon glyphicon-fire"></i>:&nbsp;</span>'.$row['news_no_useful'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-fire-extinguisher"></i>:&nbsp;</span>'.$row['news_no_rubbish'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-comment-o"></i>:&nbsp;</span>'.$row['news_no_comment'].
                            '</div>';
                    echo '</article>';
                    echo '</div>';
                }
                ?>
				<div class="clear"></div>
			</div><!--end:sidebar-->


	    </div><!--end:secondary-->
        <?php include("footer.php") ?>
    </div><!-- end:/main-content -->
</div><!-- end:/wrapper -->

</body>
</html>