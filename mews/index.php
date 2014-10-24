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





    $DEBUG_TAG = "map_view";
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
<script>
    if (window.location.hash && window.location.hash == '#_=_') {
        if (window.history && window.history.pushState) {
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
  
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-54036500-1', 'auto');
  ga('require', 'displayfeatures');
  ga('send', 'pageview');

</script>
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
    <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css' />  
    <link rel="stylesheet" href="css/animation/animate.css">  
    <noscript><link rel="stylesheet" type="text/css" href="css/noJS.css" /></noscript>

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
    <script src="js/vex_dialog/vex.combined.min.js"></script>
    <script>vex.defaultOptions.className = 'vex-theme-flat-attack';</script>

    <!-- The real deal -->
    <script src="js/tag-it.js" type="text/javascript" charset="utf-8"></script>

    <script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&language=zh-TW"></script>
    <script type="text/javascript" src="js/modernizr.custom.79639.js"></script> 

<style>

    .wrapper-map {
        width: 100%;
        position: relative;
    }
    #map-canvas {
        position: absolute;
        height: 450px;
        width: 100%;
        margin: 0px;
        padding: 0px;
        z-index:10;
    }
    #map-control {
        position: absolute;
        height: 100px;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        padding: 0px;
        z-index:20;
        text-align: center; 
    }

    .map-button{
        width:3%;
        padding:5px;
        display:inline-block;
    }
    #news-selected{
        position: relative;
        
    }
    #news-selected-div{
    }


</style>
<script>
    var clickedButton = "";
    var news_type = "";
    var markerArray = [];
    var markerLocation = null;
    var geocoder;
    var map;
    var marker;
    var infowindow = new google.maps.InfoWindow({});// create only one instant of infowindows, infowindow will then be show one at a time

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
            // get address and lat lng after clicking
            $("#status").html('<img src="images/icons/loading.gif"/>');
            placeUserMarker(event.latLng);
        });
        function getAddress(latLng) {
            geocoder.geocode( {'latLng': latLng, 'region': 'hk'},
            function(results, status) {
                if(status == google.maps.GeocoderStatus.OK) {
                    if(results[0]) {
                        document.getElementById("news_address").value = results[0].formatted_address;
                        $("#status").html('<img src="images/icons/accepted.png" />');
                        address_ok = true;
                    }
                    else {
                        document.getElementById("news_address").value = "地址解譯失敗";
                        $("#status").html('<img src="images/icons/no.png" />');
                        address_ok = false;
                     }
                }
                else {
                    $("#status").html('<img src="images/icons/no.png" />');
                    document.getElementById("news_address").value = "地址解譯失敗";
                    address_ok = false;
                }
            });
        }

    }
    function placeUserMarker(location) {
        markerLocation = location;
        map.setCenter(location);
        $("#news-selected").html("");
        if(marker){ //check if marker exist
            marker.setPosition(location); //if yes, reuse the maker
        }else{
            markerImage = new google.maps.MarkerImage("images/icons/person.png",
                new google.maps.Size(71, 71),
                new google.maps.Point(0, 0),
                new google.maps.Point(17, 34),
                new google.maps.Size(50, 50));
            marker = new google.maps.Marker({ //else instaniate a new one
                position: location, 
                icon: markerImage, 
                map: map
            });
        }
        //document.getElementById('latlng').value=location;
        getNews(location);
    }
    function getNews(location){
        clearMarkers();
        var userLat = location.lat();
        var userLng = location.lng();
        $("#news-list").load("backend_php/ajax/get_news_map.php", 
            {'lat': userLat, 
             'lng': userLng,
             'fetch_type': "list",
             'news_type': news_type
            }, function() {}); 
        
        $.ajax({
            type: "post",
            url: "backend_php/ajax/get_news_map.php",
            data: {'lat': userLat, 
                   'lng': userLng,
                   'fetch_type': "map",
                   'news_type': news_type
                  },
            success:function(data){
                if(isJson(data)&&data.length>3){
                    obj = JSON.parse(data);
                    placeNewsMarker(obj);
                    scrollToMap();
                }
                else{
                    vex.dialog.confirm({            
                        message: "找不到附近的新聞",            
                        callback: function(value) {}
                    });                
                }
            }
        });
    }

    function placeNewsMarker(jsonArray){
        markerArray = [];
        for(var i=0; i<Object.keys(jsonArray).length; i++){
            //markerImage[i] = new google.maps.MarkerImage('marker.png');
            //pointMarker[i] = new google.maps.Marker({
            //        position: collection[i],
            //        map: map,
            //        icon: pointMarkerImage[i],
            //        animation: google.maps.Animation.BOUNCE,
            //        title: "collection"+ i 
            //});
            //alert(Object.keys(jsonArray).length);
            addMarker(jsonArray[i]);
        }
        
    }


    google.maps.event.addDomListener(window, 'load', initialize);

    /*
     * Check if the data is json
     */
    function isJson(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    //add marker to marker array
    function addMarker(jsonObject) {      
        //alert(jsonObject.news_lat);
        var imageFile = "images/icons/map_accident.png";

        if(jsonObject.news_type=="news_crime"){
            imageFile = "images/icons/map_crime.png";
        }
        else if(jsonObject.news_type=="news_accident"){
            imageFile = "images/icons/map_accident.png";
        }
        else if(jsonObject.news_type=="news_funny"){
            imageFile = "images/icons/map_funny.png";
        }
        else if(jsonObject.news_type=="news_event"){
            imageFile = "images/icons/map_event.png";
        }
        else if(jsonObject.news_type=="news_other"){
            imageFile = "images/icons/map_other.png";
        }
        else if(jsonObject.news_type=="news_politics"){
            imageFile = "images/icons/map_politics.png";
        }
        else if(jsonObject.news_type=="news_tech"){
            imageFile = "images/icons/map_tech.png";
        }
        else if(jsonObject.news_type=="news_business"){
            imageFile = "images/icons/map_business.png";
        }
        else if(jsonObject.news_type=="news_life"){
            imageFile = "images/icons/map_life.png";
        }
        else if(jsonObject.news_type=="news_blog"){
            imageFile = "images/icons/map_blog.png";
        }
        markerImage = new google.maps.MarkerImage(imageFile,
            new google.maps.Size(71, 71),
            new google.maps.Point(0, 0),
            new google.maps.Point(17, 34),
            new google.maps.Size(40, 40));
        var myLatlng = new google.maps.LatLng(jsonObject.news_lat, jsonObject.news_lng);
        var marker = new google.maps.Marker({
            position: myLatlng,
            info_content: jsonObject.news_title,
            news_id: jsonObject.news_id,
            icon: markerImage,
            animation: google.maps.Animation.DROP,
            map: map
        });
        
        

        google.maps.event.addListener(marker, 'click', function () {
            // where I have added .html to the marker object.
            infowindow.setContent(this.info_content);
            infowindow.open(map, this);
            scrollToMap();
            $("#news-selected").load("backend_php/ajax/get_news_map.php", 
            {'news_id': this.news_id
            }, function() {}); 
            
        });
        markerArray.push(marker);

    }
    // Sets the map on all markers in the array.
    function setAllMap(map) {
        for (var i = 0; i < markerArray.length; i++) {
            markerArray[i].setMap(map);
        }
    }

    // Removes the markers from the map, but keeps them in the array.
    function clearMarkers() {
        setAllMap(null);
    }

    //scroll to destinated position
    //var navHeight = $('nav').outerHeight();
    function scrollToMap(){
        var newPos = $("#map-canvas").offset().top;
        $('html, body').stop().animate({scrollTop: newPos}, 1300);
    }

    //get user current location
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {  
                pinUserPosition(position); 
            }, function() {
                if(document.cookie.indexOf("firsttime_index") >= 0&&document.cookie.indexOf("tutorial_done") >= 0){
                    vex.dialog.confirm({            
                        message: "請允許MEWS讀取你的位置，否則請手動點擊地圖",            
                        callback: function(value) {}
                    });   
                }
                
            });
        } else { 
            if(document.cookie.indexOf("firsttime_index") >= 0&&document.cookie.indexOf("tutorial_done") >= 0){
                vex.dialog.confirm({            
                    message: "你的裝置不支持定位功能！請手動點擊地圖",            
                    callback: function(value) {}
                });   
            }            
        }
    }


    function pinUserPosition(position) {
        var myLatlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
        placeUserMarker(myLatlng);
        //x.innerHTML="Latitude: " + position.coords.latitude + 
        //"<br>Longitude: " + position.coords.longitude;  
    }

    function clickMapButton(button_id){
        //reset icon status
        if(clickedButton==button_id){
            clickedButton = "";
            news_type = "" ;
            if(markerLocation!=null){
                placeUserMarker(markerLocation);
            }
            document.getElementById(button_id).src="images/icons/"+button_id+".png";
        }else{
            news_type = button_id;
            if(markerLocation!=null){
                placeUserMarker(markerLocation);
            }
            document.getElementById("news_crime").src="images/icons/news_crime.png";
            document.getElementById("news_accident").src="images/icons/news_accident.png";
            document.getElementById("news_funny").src="images/icons/news_funny.png";
            document.getElementById("news_event").src="images/icons/news_event.png";
            document.getElementById("news_politics").src="images/icons/news_politics.png";
            document.getElementById("news_tech").src="images/icons/news_tech.png";
            document.getElementById("news_business").src="images/icons/news_business.png";
            document.getElementById("news_life").src="images/icons/news_life.png";
            document.getElementById("news_other").src="images/icons/news_other.png";
            document.getElementById("news_blog").src="images/icons/news_blog.png";
            document.getElementById(button_id).src="images/icons/"+button_id+"_clicked.png";
            //document.getElementById(button_id).onclick = unClickMapButton(button_id);
            clickedButton = button_id;
        }
        

    }
    
    window.onload=getLocation() ;
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
<div class="wrapper-map">
    <div id="main-content">
        <script>
        

        <?php
        if(isset($_SESSION['user_id'])){
            echo "ga('set', '&uid', '".$_SESSION['user_id']."');";
            $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_MSG." WHERE `msg_to` =? AND `msg_from` ='admin' AND `msg_read` =0");
            $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_STR);
            try {
                $stmt->execute();
            } catch (Exception $e) {
                msg_log($DEBUG_TAG." : ".$e);
            }
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                echo "vex.dialog.confirm({";
                echo 'message: "'.$row['msg_content'].'",';
                echo "callback: function(value) {";
                echo "$.ajax({";
                echo "type: 'post',";
                echo "url: 'backend_php/ajax/msg_ajax.php',";
                echo "data: {'action': 'read', ";
                echo "'user_id': '".$_SESSION['user_id']."',";
                echo "'msg_id': '".$row['msg_id']."'},";
                echo "success:function(data){}});}";
                echo "});";
            }

            $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER." WHERE `user_id` =? AND `user_first_login` =1");
            $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_STR);
            try {
                $stmt->execute();
            } catch (Exception $e) {
                msg_log($DEBUG_TAG." : ".$e);
            }

            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                echo "vex.dialog.confirm({";
                echo 'message: "'.FIRST_LOGIN.'",';
                echo "callback: function(value) {";
                echo "$.ajax({";
                echo "type: 'post',";
                echo "url: 'backend_php/ajax/msg_ajax.php',";
                echo "data: {'action': 'first_login', ";
                echo "'user_id': '".$_SESSION['user_id']."'},";
                echo "success:function(data){}});}";
                echo "});";
            }
        }

        ?>

        </script>

        <div class="tertiary_2">
            <article class="entry-item-map">
                <!--<h1 class="entry-title" id="page-title"></h1>   -->                          

                <div class="form-group">
                    <div id="map-canvas"></div>
                    <div id="map-control">
                        <div id="map-control-button">
                            <span><img class="map-button" id="news_crime" onCLick="clickMapButton(this.id);" src="images/icons/news_crime.png"></img></span>
                            <span><img class="map-button" id="news_accident" onCLick="clickMapButton(this.id);" src="images/icons/news_accident.png"></img></span>
                            <span><img class="map-button" id="news_business" onCLick="clickMapButton(this.id);" src="images/icons/news_business.png"></img></span>
                            <span><img class="map-button" id="news_politics" onCLick="clickMapButton(this.id);" src="images/icons/news_politics.png"></img></span>
                            <span><img class="map-button" id="news_event" onCLick="clickMapButton(this.id);" src="images/icons/news_event.png"></img></span>
                            <span><img class="map-button" id="news_tech" onCLick="clickMapButton(this.id);" src="images/icons/news_tech.png"></img></span>
                            <span><img class="map-button" id="news_funny" onCLick="clickMapButton(this.id);" src="images/icons/news_funny.png"></img></span>
                            <span><img class="map-button" id="news_life" onCLick="clickMapButton(this.id);" src="images/icons/news_life.png"></img></span>
                            <span><img class="map-button" id="news_other" onCLick="clickMapButton(this.id);" src="images/icons/news_other.png"></img></span>
                            <span><img class="map-button" id="news_blog" onCLick="clickMapButton(this.id);" src="images/icons/news_blog.png"></img></span>
                        </div>
                    </div>
                </div>

            <div class="clear"></div>
            </article>
        </div><!--end:tertiary-->


    </div><!--main-content-->
</div><!--end:wrapper--> 

<div class="wrapper">
    <div id="main-content">

        <div class="tertiary_map">
            <div id ="flipbox">
                <div class="empty_flipbox">

                </div>
            </div>



            <div id="news-selected">
                
            </div>

            <div id="news-list"></div>
        </div><!--end:tertiary-->
        <?php include("footer.php") ?>

    </div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>