<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
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

        $DEBUG_TAG = "news_form";
        $dbhost = DB_HOST;
        $dbuser = DB_USER;
        $dbpass = DB_PASSWORD;
        $dbname = DB_NAME;
        $dbc;
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

        $stmt = $dbc->prepare("SELECT user_reputation FROM ".DB_TABLE_USER." WHERE user_id =?");
        $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch ( PDO::FETCH_ASSOC );
        $user_reputation = (int)$row['user_reputation'];


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
<script type="text/javascript">
tinymce.init({
        selector: "textarea",
        plugins: [
                "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "table contextmenu directionality emoticons template textcolor paste fullpage textcolor colorpicker textpattern"
        ],

        toolbar1: "newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
        toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
        toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",

        menubar: false,
        toolbar_items_size: 'small',

        style_formats: [
                {title: 'Bold text', inline: 'b'},
                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                {title: 'Example 1', inline: 'span', classes: 'example1'},
                {title: 'Example 2', inline: 'span', classes: 'example2'},
                {title: 'Table styles'},
                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
        ],

        templates: [
                {title: 'Test template 1', content: 'Test 1'},
                {title: 'Test template 2', content: 'Test 2'}
        ]
});
</script>
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
            placeMarker(event.latLng);
        });
        function placeMarker(location) {
            if(marker){ //on vérifie si le marqueur existe
                marker.setPosition(location); //on change sa position
            }else{
                marker = new google.maps.Marker({ //on créé le marqueur
                    position: location, 
                    map: map
                });
            }
            document.getElementById('latlng').value=location;
            getAddress(location);
        }
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



    function codeAddress() {
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
                //alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    }

    google.maps.event.addDomListener(window, 'load', initialize);

    function validateForm() {
    	//alert('Address not ok? '+address_ok);

        //var x = document.forms["add_news_form"]["title"].value;
        if (!address_ok&&(document.getElementById('news_address').value.length>0)) {
            vex.dialog.confirm({            
                message: "你所輸入的地址不正確！請再按檢查地址！",            
                callback: function(value) {}
            });
            return false;
        }

        var val = document.getElementById('news_content').value;
        if (val==null) {
            ///^\s*$/g.test(val)||
            vex.dialog.confirm({            
                message: "請輸入主要內容！",            
                callback: function(value) {}
            });
            return false;
        }
        var val = document.getElementById('news_type').value;
        if (val.length<1) {
            ///^\s*$/g.test(val)||
            vex.dialog.confirm({            
                message: "請輸入主要內容！",            
                callback: function(value) {}
            });
            return false;
        }
        //alert('內容！'+val.textContent);


    }

    var user_reputation = <?php echo $user_reputation;?>;
    if(user_reputation<1){
        vex.dialog.confirm({            
            message: "你的信用度不足！每次發佈新聞必須使用 1 點的信用度！",            
            callback: function(value) {}
        });
        window.location.href = "index.php";
    }
</script>



    <script>

        $(function(){
        	//call a php mysql function to loop all possible tag
            var sampleTags = [
            <?php
                $no_of_row=0;
                $i=0;
                try {
                    $stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_TAG ); 
                    $stmt->execute ();
                    $no_of_row = $stmt->fetch(PDO::FETCH_NUM);
                    $stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_TAG ); 
                    $result = $stmt->execute ();
                } catch ( Exception $e ) {
                    msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
                }
                while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
                    echo "'";
                    echo $row['tag_name'];
                    echo "'";
                    if($i<$no_of_row){
                        $i++;
                        echo ", ";
                    }
                }
            ?>
            ];

            //-------------------------------
            // Minimal
            //-------------------------------
            $('#myTags').tagit();

            //-------------------------------
            // Single field
            //-------------------------------
            $('#singleFieldTags').tagit({
                availableTags: sampleTags,
                // This will make Tag-it submit a single form value, as a comma-delimited field.
                singleField: true,
                tagLimit: 4,
                singleFieldNode: $('#mySingleField')
            });

            // singleFieldTags2 is an INPUT element, rather than a UL as in the other 
            // examples, so it automatically defaults to singleField.
            $('#singleFieldTags2').tagit({
                availableTags: sampleTags
            });

            //-------------------------------
            // Preloading data in markup
            //-------------------------------
            $('#myULTags').tagit({
                availableTags: sampleTags, // this param is of course optional. it's for autocomplete.
                // configure the name of the input field (will be submitted with form), default: item[tags]
                itemName: 'item',
                fieldName: 'tags'
            });

            //-------------------------------
            // Tag events
            //-------------------------------
            var eventTags = $('#eventTags');

            var addEvent = function(text) {
                $('#events_container').append(text + '<br>');
            };

            eventTags.tagit({
                availableTags: sampleTags,
                beforeTagAdded: function(evt, ui) {
                    if (!ui.duringInitialization) {
                        addEvent('beforeTagAdded: ' + eventTags.tagit('tagLabel', ui.tag));
                    }
                },
                afterTagAdded: function(evt, ui) {
                    if (!ui.duringInitialization) {
                        addEvent('afterTagAdded: ' + eventTags.tagit('tagLabel', ui.tag));
                    }
                },
                beforeTagRemoved: function(evt, ui) {
                    addEvent('beforeTagRemoved: ' + eventTags.tagit('tagLabel', ui.tag));
                },
                afterTagRemoved: function(evt, ui) {
                    addEvent('afterTagRemoved: ' + eventTags.tagit('tagLabel', ui.tag));
                },
                onTagClicked: function(evt, ui) {
                    addEvent('onTagClicked: ' + eventTags.tagit('tagLabel', ui.tag));
                },
                onTagExists: function(evt, ui) {
                    addEvent('onTagExists: ' + eventTags.tagit('tagLabel', ui.existingTag));
                }
            });

            //-------------------------------
            // Read-only
            //-------------------------------
            $('#readOnlyTags').tagit({
                readOnly: true
            });

            //-------------------------------
            // Tag-it methods
            //-------------------------------
            $('#methodTags').tagit({
                availableTags: sampleTags
            });

            //-------------------------------
            // Allow spaces without quotes.
            //-------------------------------
            $('#allowSpacesTags').tagit({
                availableTags: sampleTags,
                allowSpaces: true
            });

            //-------------------------------
            // Remove confirmation
            //-------------------------------
            $('#removeConfirmationTags').tagit({
                availableTags: sampleTags,
                removeConfirmation: true
            });
            
        });
        //alert("sampleTags"+sampleTags);

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
			<article class="entry-item-form">
			<form accept-charset="UTF-8" id="add_news_form" name="add_news_form" method="post" action="/backend_php/upload_news_backend.php" enctype="multipart/form-data" onsubmit="return validateForm()">
                <div class="form-group">
                    <h6><i class="fa fa-book"></i>&nbsp;&nbsp;標題(*)：</h6>
                    <input type="text" id='news_title' name="news_title" value= "" class="form-control" required placeholder="Enter ..." onkeypress="return event.keyCode != 13;"/>
                </div>                   
                <div class="form-group">
                    <h6><i class="fa fa-clock-o"></i>&nbsp;&nbsp;時間：</h6>
                    <input type="time" id='news_time' name="news_time" value= "" class="form-control" placeholder="hrs:mins" onkeypress="return event.keyCode != 13;"/>
                </div>            
                
                <div class="form-group">
                    <h6><i class="fa fa-calendar"></i>&nbsp;&nbsp;日期(*):</h6>
                    <input type="date" id="news_date" name= "news_date" value="" class="form-control " required placeholder="Enter ..." onkeypress="return event.keyCode != 13;"></input>
                </div><!-- /.form group -->		

                <div class="form-group">
                    <h6><i class="fa fa-bars"></i>&nbsp;&nbsp;分類選擇(*):<span id="category-span"></span></h6>
                    <input id="news_type" name="news_type" type="hidden" value="">

                    <div id="dd" class="wrapper-dropdown-1" tabindex="1">
                        <span>分類</span>
                        <ul class="dropdown" tabindex="1">
                            <li><a id="news_crime" href="#"><img class="category-icon" src="images/icons/crime_icon_small.png">罪案報導</img></a></li>
                            <li><a id="news_accdient" href="#"><img class="category-icon" src="images/icons/accident_icon_small.png">意外直擊</img></a></li>
                            <li><a id="news_funny" href="#"><img class="category-icon" src="images/icons/funny_icon_small.png">奇聞怪事</img></a></li>
                            <li><a id="news_event" href="#"><img class="category-icon" src="images/icons/event_icon_small.png">活動盛事</img></a></li>
                            <li><a id="news_politics" href="#"><img class="category-icon" src="images/icons/politics_icon_small.png">政治熱話</img></a></li>
                            <li><a id="news_tech" href="#"><img class="category-icon" src="images/icons/tech_icon_small.png">科技資訊</img></a></li>
                            <li><a id="news_business" href="#"><img class="category-icon" src="images/icons/business_icon_small.png">商業財經</img></a></li>
                            <li><a id="news_life" href="#"><img class="category-icon" src="images/icons/life_icon_small.png">生活消閒</img></a></li>
                            <li><a id="news_other" href="#"><img class="category-icon" src="images/icons/other_icon_small.png">其他報導</img></a></li>
                            <?php
                            if($_SESSION['user_is_special'] == 1){
                                echo '<li><a id="news_blog" href="#"><img class="category-icon" src="images/icons/blog_icon_small.png">個人專場</img></a></li>';
                            }  
                        ?>
                        </ul>
                    </div>
                </div><!-- /.form group --> 

                <div class="form-group">
                    <h6><i class="fa fa-picture-o"></i>&nbsp;&nbsp;新聞圖片:</h6>
				    <input type="file"  id="news_img" name="news_img" accept="image/png, image/jpeg, image/jpg" /> 
                </div><!-- /.form group -->		

                <div class="form-group">
                    <h6 id = "address_lable"><i class="fa fa-map-marker"></i>&nbsp;&nbsp;發生地點:</h6>
                    <input id="news_address" name="news_address" type="text" value="" onchange="clearAddressCheck();" onkeypress="return event.keyCode != 13;"></input>
                    <input id="latlng" name="latlng" type="hidden" value="">
                    <input type="button" value="地址檢查" onclick="codeAddress()"><i id="status"></i>
                </div><!-- /.form group --> 



               
                <script type="text/javascript">
                    function DropDown(el) {
                        this.dd = el;
                        this.placeholder = this.dd.children('span');
                        this.opts = this.dd.find('ul.dropdown > li');
                        this.val = '';
                        this.index = -1;
                        this.initEvents();
                    }
                    DropDown.prototype = {
                        initEvents : function() {
                            var obj = this;
                            obj.dd.on('click', function(event){
                                $(this).toggleClass('active');
                                return false;
                            });

                            obj.opts.on('click',function(){
                                var opt = $(this);
                                obj.val = opt.text();
                                obj.index = opt.index();
                                //index start from 0
                                changeNewsType(obj.index);
                                obj.placeholder.text('' + obj.val);
                            });
                        },
                        getValue : function() {
                            return this.val;
                        },
                        getIndex : function() {
                            return this.index;
                        }
                    }

                    function changeNewsType(index){
                        var news_type = document.getElementById("news_type");
                        var category_icon_link = "";
                        if(index==0){
                            news_type_value = "news_crime";
                            category_icon_link = "images/icons/crime_icon_small.png"
                        }else if(index==1){
                            news_type_value = "news_accident";
                            category_icon_link = "images/icons/accident_icon_small.png"
                        }else if(index==2){
                            news_type_value = "news_funny";
                            category_icon_link = "images/icons/funny_icon_small.png"
                        }else if(index==3){
                            news_type_value = "news_event";
                            category_icon_link = "images/icons/event_icon_small.png"
                        }else if(index==4){
                            news_type_value = "news_politics";
                            category_icon_link = "images/icons/politics_icon_small.png"
                        }else if(index==5){
                            news_type_value = "news_tech";
                            category_icon_link = "images/icons/tech_icon_small.png"
                        }else if(index==6){
                            news_type_value = "news_business";
                            category_icon_link = "images/icons/business_icon_small.png"
                        }else if(index==7){
                            news_type_value = "news_life";
                            category_icon_link = "images/icons/life_icon_small.png"
                        }else if(index==8){
                            news_type_value = "news_other";
                            category_icon_link = "images/icons/other_icon_small.png"
                        }else if(index==9){
                            news_type_value = "news_blog";
                            category_icon_link = "images/icons/blog_icon_small.png"
                        }
                        news_type.value=news_type_value;
                        $("#category-span").html("<img class='category-icon' src='"+category_icon_link+"' />");
                    }
                    $(function() {
                        var dd = new DropDown( $('#dd') );
                        $(document).click(function() {
                            // all dropdowns
                            $('.wrapper-dropdown-1').removeClass('active');
                        });

                    });
                    function clearAddressCheck(){
                        address_ok = false;
                        $("#status").html('<img src="" />');
                    }    
                </script>


                <div class="form-group"> 
                    <h6><i class="fa fa-tags"></i>&nbsp;&nbsp;#tag(最多四個):</h6>
                    <input name="news_tags" id="mySingleField" value="" type="hidden" onkeypress="return event.keyCode != 13;"> <!-- only disabled for demonstration purposes -->
                    <ul id="singleFieldTags" onkeypress="return event.keyCode != 13;"></ul>
                </div><!-- /.form group -->		


                <!--時-->
				<!--類-->
				<!--地-->
				<!--圖-->
				<!--事-->
				
				<!--tag-->
				<!--tag-->
				<!--tag-->
				<!--tag-->
				
			</article>
		</div><!--end:primary-->

		<div class="secondary">
			<article class="entry-item">
				<header>
					<h1 class="entry-title">實際位置</h1>					          
				</header>	

				<div class="form-group">
                    <div id="map-canvas"></div>
                </div>
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
		<div class="tertiary">
			<article class="entry-item">

				<!--時-->
				<!--類-->
				<!--地-->
				<!--圖-->
				<!--事-->
				
				<!--tag-->
				<!--tag-->
				<!--tag-->
				<!--tag-->
                    <h6><i class="fa fa-keyboard-o"></i>&nbsp;&nbsp;主要內容(*)：</h6>
                    <textarea accept-charset="UTF-8"  id = "news_content" name="news_content" style="width:100%" rows="22"></textarea>
                    <input style="float:right;" class="green-button" name="submit" id="submit" tabindex="5" value="Submit" type="submit"/>  
                </form>
			<div class="clear"></div>
			</article>
		</div><!--end:tertiary-->
		<?php include("footer.php") ?>

	</div><!--main-content-->
</div><!--end:wrapper--> 

</body>
</html>