<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");

    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
        //do nothing if the user is logged in
    }else{
        $url = REDIRECT_PAGE."?action=login_before_post";
        header('Location: '.$url);
    }

    // echo phpinfo();
    // ini_set('display_errors',1);
    // echo var_dump($_FILES);
    /**
     * msg_log function for error log
     */
    //echo dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR."config.php";
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");

        $DEBUG_TAG = "edit_profile";
        $dbhost = DB_HOST;
        $dbuser = DB_USER;
        $dbpass = DB_PASSWORD;
        $dbname = DB_NAME;
        $dbc;
        $user_id = $_SESSION['user_id'];
        $user_info = "";
        $user_pic = "";
        $user_pic_link = "'placeholders/avatar/author.png'";
        $user_is_special = "";
        $user_special_intro = "";
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
            $stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_USER ." WHERE user_id =?"); 
            $stmt->bindValue ( 1, $_SESSION['user_id'], PDO::PARAM_INT );
            $stmt->execute ();
            while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
                $user_info = $row['user_info'];
                $user_pic = $row['user_pic'];
                $_SESSION['user_pic'] = $user_pic;
                $_SESSION['username'] = $row['user_name'];
                if(strlen($user_pic) >1){
                    $user_pic_link = "'".GW_USER_IMAGEPATH.$user_pic."'";
                }
                $user_is_special = $row['user_is_special'];
                $_SESSION['user_is_special'] = $row['user_is_special'];
                $user_special_intro = $row['user_special_intro'];
            }
        } catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        $firsttime = false;
        if(!isset($_COOKIE['firsttime_edit_user'])&& $_SESSION['logged_in'] != true){
            $firsttime = true;
            $expire=time()+60*60*24*360*100;
            setcookie("firsttime_edit_user", "not_first_time", $expire);
        }

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>MEWS</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />		
	<link rel="stylesheet" href="css/reset.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/nivoslider.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/responsive.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/icon.css" type="text/css" media="all" />
    <link href="css/jquery.tagit.css" rel="stylesheet" type="text/css">
    <link href="css/tagit.ui-zendesk.css" rel="stylesheet" type="text/css">
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
    <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>  

    <script type="text/javascript" src="js/jquery.nivo.slider.pack.js"></script>
    <script type="text/javascript" src="js/jquery.carouFredSel-5.6.2.js"></script>  
    <script type="text/javascript" src="js/jquery.prettyPhoto.js"></script>
    <script type="text/javascript" src="js/jquery.sticky.js"></script>
    <script type="text/javascript" src="js/jquery-scroller-v1.min.js"></script>
    <script type="text/javascript" src="js/kendo.web.min.js"></script>
    


    <!-- The real deal -->
    <script src="js/tag-it.js" type="text/javascript" charset="utf-8"></script>

    <script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&language=zh-TW"></script>
    <script src="js/vex_dialog/vex.combined.min.js"></script>
    <script>vex.defaultOptions.className = 'vex-theme-flat-attack';</script>

<style>
    #map-canvas {
        height: 286px;
        margin: 0px;
        padding: 0px;
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
            zoom: 13,
            center: latlng
        }
        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
        // This event listener will call addMarker() when the map is clicked.
        google.maps.event.addListener(map, 'click', function(event) {
        	//TODO get address and lat lng after clicking
        });
    }

    function codeAddress() {
    	address_need = true;
    	var latlng = document.getElementById("latlng");
    	latlng.value = "";
        
        var address = document.getElementById('news_address').value;
        geocoder.geocode( { 'address': address, 'region': 'hk'}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
            	map.setCenter(results[0].geometry.location);
            	if (!marker) {        
        	        // Create the marker if it doesn't exist
                    marker = new google.maps.Marker({
                        position: results[0].geometry.location,
                        map: map
                    });
                }
                // Otherwise, simply update its location on the map.
                else { 
                	marker.setPosition(results[0].geometry.location); 
                    //alert('location result:'  + results[0].formatted_address);
                }
                latlng.value = results[0].geometry.location;    	
                 $("#status").html('<img src="images/icons/accepted.png" />');
                document.getElementById('news_address').value = results[0].formatted_address;
                address_ok = true;

            } 
            else {
            	address_ok = false;
            	$("#status").html('<img src="images/icons/no.png" />');
                alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    }

    google.maps.event.addDomListener(window, 'load', initialize);
    var apply_clicked = false;

    function validateForm() {
    	//alert('Address not ok? '+address_ok);

        var val = document.getElementById('user_name').value;
        if (val==null) {
            ///^\s*$/g.test(val)||
            alert('請輸入你的名稱！');
            return false;
        }
        if(val.length>16){
            alert('名稱不可長過16字！');
            return false;   
        }
        if(apply_clicked){
            var val = document.getElementById('user_special_intro').value;
            if (val==null) {
                ///^\s*$/g.test(val)||
                alert('請輸入主要內容！');
                return false;
            }
            var val = document.getElementById('user_special_ability').value;
            if (val==null) {
                ///^\s*$/g.test(val)||
                alert('請輸入你的專長！');
                return false;
            }            
        }
        //alert('內容！'+val.textContent);
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

<body class="news-form-page" >
<?php
    include("header.php");
?>
<div class="wrapper">
	<div id="main-content">
		<div class="primary">
            <script>
            <?php
            $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER." WHERE `user_id` =? AND `user_first_login` =1");
            $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_STR);
            try {
                $stmt->execute();
            } catch (Exception $e) {
                msg_log($DEBUG_TAG." : ".$e);
            }

            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                echo "vex.dialog.confirm({";
                echo 'message: "'.FIRST_EDIT_USER.'",';
                echo "callback: function(value) {";
                echo "}});";
            }
            ?>
            </script>
			<article class="entry-item">
			<form accept-charset="UTF-8" id="add_news_form" name="add_news_form" method="post" action="/backend_php/update_user_profile_backend.php" enctype="multipart/form-data" onsubmit="return validateForm()">
                
                <?php
                if($_SESSION['user_is_special']==0){
                    echo'<div class="form-group">
                             <a class="red-button" id="apply_special" href="#top">申請個人專場</a>
                         </div> ';
                }else if($_SESSION['user_is_special']==1){
                    echo'<div class="form-group">
                             <span class="red-button" id="apply_special_1"  >你已是專長會員，在發佈新聞是可選擇專長報道</span>
                         </div> ';
                }else if($_SESSION['user_is_special']==2){
                    echo'<div class="form-group">
                             <span class="red-button" id="apply_special_2"  none" >管理員正審核你的申請</span>
                         </div> ';
                }
                ?>
                
                <div class="form-group">
                    <h6><i class="fa fa-user"></i>&nbsp;&nbsp;顯示名稱(最多16字)(*)：</h6>
                    <input type="text" id='user_name' name="user_name" maxlength="16" value= <?php echo "'".$_SESSION['username']."'";?> class="form-control" required  onkeypress="return event.keyCode != 13;"/>
                </div>                   
                         
                <div class="form-group">
                    <h6><i class="fa fa-pencil"></i>&nbsp;&nbsp;個人簡介：</h6>
                    <textarea id='user_info' name="user_info" style="width:90%" rows="6" onkeypress="return event.keyCode != 13;"><?php echo $user_info;?></textarea>
                </div>   

                <div class="form-group">
                    <style>
                        #user_pic{
                            opacity: 0;
                        }
                    </style>
                    <h6><i class="fa fa-picture-o"></i>&nbsp;&nbsp;我的頭像: &nbsp;&nbsp;&nbsp; <a class="small-grey-button" id="no_profile_pic_button" href="#">取消使用頭像</a></h6>
                    <input type ="hidden" name="no_profile_pic" id="no_profile_pic" value ="0"/>
                    <input type="file"  id="user_pic" name="user_pic" accept="image/png, image/jpeg, image/jpg" onchange="readURL(this);"/> 
                    <img class="hover-effect user-pic-display-img" id="user_pic_display" alt="" src=
                        <?php
                            echo $user_pic_link;       
                        ?>
                    />
                    <script>
                        $('#no_profile_pic_button').click(function(e){
                             var elem = document.getElementById("no_profile_pic");
                             elem.value = "1";
                             $('#user_pic_display').attr('src', 'placeholders/avatar/author.png');
                             e.preventDefault();
                        });
                        $('#user_pic_display').click(function(e){
                             $('#user_pic').click();
                             var elem = document.getElementById("no_profile_pic");
                             elem.value = "0";
                             e.preventDefault();
                        });
                        function readURL(input) {
                            if (input.files && input.files[0]) {
                                var reader = new FileReader();
                                reader.onload = function (e) {
                                    $('#user_pic_display')
                                    .attr('src', e.target.result)
                                    //.width(150)
                                    //.height(200);
                                };
                                reader.readAsDataURL(input.files[0]);
                            }
                        }
                        $('#apply_special').click(function(e){
                            if(!apply_clicked){
                                document.getElementById("apply_special_input_1").style.display = "block";
                                document.getElementById("apply_special_input_2").style.display = "block";
                                document.getElementById("user_special_ability").required = true;
                                document.getElementById("user_special_intro").required = true;
                                var elem = document.getElementById("apply_for_special");
                                elem.value = "1";
                                document.getElementById("apply_special").innerHTML = "取消申請";
                                apply_clicked = true
                                
                            }else{
                                document.getElementById("apply_special_input_1").style.display = "none";
                                document.getElementById("apply_special_input_2").style.display = "none";
                                document.getElementById("user_special_ability").required = false;
                                document.getElementById("user_special_intro").required = false;
                                var elem = document.getElementById("apply_for_special");
                                elem.value = "0";
                                document.getElementById("apply_special").innerHTML = "申請個人專場";
                                apply_clicked = false;
                            }
                            e.preventDefault();
                             
                        });

                    </script>
                    <div class="clear"></div>
                </div><!-- /.form group --> 
                <div id ="jump_to"> </div>
                <div class="form-group" id="apply_special_input_1" style="display: none;">
                    <h6><i class="fa fa-bolt"></i>&nbsp;&nbsp;你的專長（必須填寫）：</h6>
                    <input type ="hidden" name="apply_for_special" id="apply_for_special" value ="0"/>
                    <input type="text" id='user_special_ability' name="user_special_ability" class="form-control" onkeypress="return event.keyCode != 13;"/>
                </div>
                                       
                <div class="form-group" id="apply_special_input_2" style="display: none;">
                    <h6><i class="fa fa-dashboard"></i>&nbsp;&nbsp;申請人簡介（必須填寫）：</h6>
                    <textarea  id='user_special_intro' name="user_special_intro" style="width:90%" rows="13" onkeypress="return event.keyCode != 13;"></textarea>
                </div>   
                <input style="float:left;" class="green-button" name="submit" id="submit" tabindex="5" value="提交" type="submit"/>  
                </form>
			</article>
		</div><!--end:primary-->


		<div class="secondary">
			<article class="entry-item">
                <!--phase 2
				<header>
					<h1 class="entry-title">關注位置()</h1>					          
				</header>	

				<div class="form-group">
                    <div id="map-canvas"></div>
                </div>
                -->
				<!--題-->
				<!--時-->
				<!--類-->
				<!--地-->
				<!--圖-->
				<!--事-->
				
				<!--tag-->
				<!--tag-->
				<!--tag-->
				<!--tag-->

                    
			<div class="clear"></div>
			</article>
		</div><!--end:secondary-->

		<?php include("footer.php") ?>

	</div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>