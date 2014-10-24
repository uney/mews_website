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
    
    /*
     * PDO Settings                 
     */
    if(isset($_GET['user_id'])){

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

    $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER." WHERE user_id =?");
    $stmt->bindValue(1, $_GET['user_id'], PDO::PARAM_STR);
    $stmt->execute();
    if($stmt->rowCount()>0){
    	while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
            $user_name = $row['user_name'];
            $user_pic = $row['user_pic'];
            $user_reputation = $row['user_reputation'];
            $user_info = $row['user_info'];
            $user_since = $row['user_since'];
            $user_last_add_point = $row['user_last_add_point'];
            $user_block = $row['user_blocked'];
            $user_is_special = $row['user_is_special'];
            $user_special_intro = $row['user_special_intro'];
            $user_gender = $row['user_gender'];
            $user_post = $row['user_post'];
            $user_up = $row['user_up'];
            $user_down = $row['user_down'];
            $user_read = $row['user_read'];
            $user_no_follower = $row['user_no_follower'];
        }
    }
    else{
        header('Location: '.PAGE_NOT_FOUND);
    }

    //For user activity later
    try {
        $stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_NEWS." WHERE news_author_id =?  ORDER BY `news_post_date_time` DESC LIMIT 10" ); 
        $stmt->bindValue(1, $_GET['user_id'], PDO::PARAM_STR);
	    $stmt->execute ();
        if($stmt->rowCount()>0){
        while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {

        }
    }

    } catch ( Exception $e ) {
    	msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
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
        echo "<meta name='keywords' content='".$keywords.",".$user_name."'/>";
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
        var latlng = new google.maps.LatLng(22.290025, 114.173784);
        var mapOptions = {
            zoom: 10,
            center: latlng
        }
        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

        <?php
        try{
            $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_author_id` =? AND `news_address` <>'' ORDER BY `news_post_date_time` DESC LIMIT 10");
            $stmt->bindValue(1, $_GET['user_id'], PDO::PARAM_STR);
            $stmt->execute();
            $i=0;
            while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
                if(strlen($row['news_address'])>1){
                    echo 'var marker_latlng = new google.maps.LatLng('.$row['news_lat'].', '.$row['news_lng'].');';
                    echo 'var marker_'.$i.' = new google.maps.Marker({
                          position: marker_latlng,
                          map: map,
                          title: "'.$row['news_title'].'"
                          });';
                    echo 'google.maps.event.addListener(marker_'.$i.', "click", function() {            
                          infowindow.setContent(this.title);
                          infowindow.open(map,marker_'.$i.');
                          });';
                    $i++;
                }
                
            }              
        }
        catch(PDOException $e){
            msg_log($DEBUG_TAG.": ".$e);
        }  
        ?>
        var infowindow = new google.maps.InfoWindow({
            content: "loading..."
        });


        google.maps.event.addListener(map, 'click', function(event) {
            //TODO get address and lat lng after clicking
        });
    }


    google.maps.event.addDomListener(window, 'load', initialize);

    
    var user_no_follower = <?php echo $user_no_follower; ?>;
    number_of_follower = parseInt(user_no_follower);
    function followUser(action){
        var logged_in = <?php if(isset($_SESSION ['username'])) { echo json_encode("true");}
                          else{ echo json_encode("false");} ?>;
        var follow_type = "user";
        var follow_following = <?php echo json_encode($_GET['user_id']);?>;
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
                        if(action=="follow"){
                            document.getElementById("follow_div").innerHTML  = '<a class="grey-button follow" id="unfollow" onClick="followUser(this.id)">取消關注&nbsp;&nbsp;<i class="fa fa-bookmark"></i></a>';
                            number_of_follower = number_of_follower+1;
                        }else if(action=="unfollow"){
                            document.getElementById("follow_div").innerHTML  = '<a class="orange-button follow" id="follow" onClick="followUser(this.id)">關注作者&nbsp;&nbsp;<i class="fa fa-bookmark-o"></i></a>';
                            number_of_follower = number_of_follower-1;
                        }
                        document.getElementById("no_of_follower").innerHTML = "已關注人數: "+number_of_follower;
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
        <div id="map-canvas"></div>
		<section class="user-profile clearfix">
			<div class="left-col">
                <div class="profile-pic" >


                    <img class="responsive-img " alt="" src=
                    <?php
                    if(strlen($user_pic)>0){
                        echo "'".GW_USER_IMAGEPATH.$user_pic."'";
                    }else{
                        echo "'placeholders/avatar/author.png'" ;
                    }
                    ?>
                    />
                    <?php
                    echo '<div id="follow_div">';
                    if(isset($_SESSION['user_id'])){
                        $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_FOLLOW.
                            " WHERE `follow_type` ='user' AND `follow_following` =? AND `follow_follower` =?");
                        $stmt->bindValue(1, $_GET['user_id'], PDO::PARAM_STR);
                        $stmt->bindValue(2, $_SESSION['user_id'], PDO::PARAM_STR);
                        try{
                            $stmt->execute();
                        }
                        catch(Exception $e){
                            msg_log($DEBUG_TAG.": ".$e);
                        }
                        if($stmt->rowCount()>0){
                            echo '<a class="grey-button follow" id="unfollow" onClick="followUser(this.id)">'.'取消關注&nbsp;&nbsp;<i class="fa fa-bookmark"></i></a>';
                        }
                        else{
                            echo '<a class="orange-button follow" id="follow" onClick="followUser(this.id)">'.'關注作者&nbsp;&nbsp;<i class="fa fa-bookmark-o"></i></a>';
                        }
                    }
                    else{
                        echo '<a class="grey-button" href="facebook_login.php" >'.'請先登入<i class="fa fa-bookmark-o"></i></a>';
                    }
                    echo '</div>';

                    ?>
                    <h4 id="no_of_follower">已關注人數: <?php echo $user_no_follower;?></h4>
                </div><!--profile-->
			</div><!--left-col-->
            <div class="right-col">
                <h1>用戶資料</h1>
                <li><h6>用戶名稱：<?php echo $user_name;?></h6></li>
                <li><h6>用戶類別：<?php if($user_is_special==1){ echo "專場會員";} else{echo '一般會員';}?></h6></li>
                <!--<li>用戶稱號：</li>-->
                <li><h6>用戶簡介：
                    <?php 
                    if($user_is_special==1){
                        echo $user_special_intro;
                    } 
                    else{
                        if(strlen($user_info)>1){
                            echo $user_info;
                        }
                        else{
                            echo "未有資料";
                        }
                    }?></h6></li>
                <li><h6>注冊時間：<?php echo $user_since;?></h6></li>
                <br><li><a href=<?php echo'"search_result.php?search_type=user&search_key='.$_GET['user_id'].'"';?> ><h6>查看所有文章</h6></a></li><br>

            </div><!--right-col-->
            <div class="user-stat">
                <span><a class="blue-button" ><?php echo $user_post;?> 則新聞&nbsp;&nbsp;<i class="fa fa-keyboard-o"></i></a></span>
                <span><a class="green-button" ><?php echo $user_up;?> 熱度&nbsp;&nbsp;<i class="glyphicon glyphicon-fire"></i></a></span>
                <span><a class="red-button" ><?php echo $user_down;?> 滅火筒&nbsp;&nbsp;<i class="fa fa-fw fa-fire-extinguisher"></i></a></span>
                <span><a class="pink-button" ><?php echo $user_read;?> 閱讀數&nbsp;&nbsp;<i class="fa fa-eye"></i></a></span>
                <h4>信用評價: <?php
                $user_reputation_text = "信譽一般";
                if($user_reputation<0){
                    $user_reputation_text = "CCC";
                }
                if($user_reputation>10){
                    $user_reputation_text = "BBB";
                }
                if($user_reputation>50){
                    $user_reputation_text = "A";
                }
                if($user_reputation>100){
                    $user_reputation_text = "AA";
                }
                if($user_reputation>200){
                    $user_reputation_text = "AAA";
                }
                if($user_reputation>500){
                    $user_reputation_text = "牙齒當金駛";
                }
                echo $user_reputation_text;
                echo "  (".$user_reputation.")";
                ?></h4>

            </div><!--end of user-stat-->
            <div class="latest-post-list">
                <h4><?php echo $user_name;?> 最近發表</h4>
                <?php
                try{
                    $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_author_id` =? ORDER BY `news_post_date_time` DESC LIMIT 10");
                    $stmt->bindValue(1, $_GET['user_id'], PDO::PARAM_STR);
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
                        echo '<span>&nbsp;&nbsp;<i class="glyphicon glyphicon-fire"></i>:'.$row['news_no_useful'].'</span>';
                        echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-fire-extinguisher"></i>:'.$row['news_no_rubbish'].'</span>';
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