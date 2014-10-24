<?php
    session_start(); //Start the session
    error_reporting(E_ALL);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."msg_constant.php");
    $DEBUG_TAG = "tutorial";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <title>MEWS 新聞地圖</title>
        <meta name="description" content="Vex is a Javascript and CSS library for creating beautiful and functional modal dialogs. It is free and open source and was developed by HubSpot developers Adam Schwartz (@adamfschwartz) and Zack Bloom (@zackbloom).">
        <link rel="icon" href="images/favicon.ico">
        <script type="text/javascript" src="//use.typekit.net/jbn8qxr.js"></script>
        <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        <script src="js/vex_dialog/vex.js"></script>
        <script src="js/vex_dialog/vex.dialog.js"></script>
        <link href="css/vex_dialog/vex.css" rel="stylesheet" />
        <link href="css/vex_dialog/vex-theme-default.css" rel="stylesheet" />
        <link href="css/vex_dialog/vex-theme-os.css" rel="stylesheet" />
        <link href="css/vex_dialog/vex-theme-plain.css" rel="stylesheet" />
        <link href="css/vex_dialog/vex-theme-flat-attack.css" rel="stylesheet" />
        <link href="css/vex_dialog/vex-theme-wireframe.css" rel="stylesheet" />
        <link rel="stylesheet" href="css/icon.css" type="text/css" media="all" />
        <link rel="stylesheet" href="css/responsive.css" type="text/css" media="all" />
        <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript">
        
        $(document).ready(function(){
            demo.loadInitialDialogs();          
        });
        </script>
    </head>
    <body>
        <style>
            html, body {
                overflow: hidden;
                margin: 0;
            }
            body.demo-start .page, body.page-intro .page {
                opacity: 0;
                z-index: 0;
            }

            .page {
                color: #000;
                font-family: "proxima-nova", "Helvetica Neue", sans-serif;
                -webkit-transition: opacity 1s;
                -moz-transition: opacity 1s;
                -o-transition: opacity 1s;
                transition: opacity 1s;
                text-align: center;
                margin: auto;
                top: 0;
                left: 0;
                bottom: 0;
                right: 0;
                position: fixed;
                width: 40em;
                max-width: 100%;
                height: 36em;
                z-index: 50;
            }
            .left_li{                
                float: left;
                display: inline-block;
            }
            .page h1 {
                font-size: 110px;
                font-weight: 100;
                margin: 40px 0;
            }
            .page h2 {
                font-size: 2.5em;
                font-weight: 100;
                margin: 1em 0;
            }
            .page p {
                margin: 1em 0;
            }
            .page a {
                display: inline-block;
                border: 2px solid #000;
                color: #000;
                padding: 1em 1.25em;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 3px;
                text-decoration: none;
                cursor: pointer;
                width: 140px;
                line-height: 1.3em;
            }
            .page a.demo-link {
                margin-left: 20px;
                border-color: transparent;
                background: #000;
                color: #fff;
                font-weight: 700;
            }


            .stack {
                color: #000;
                font-family: "proxima-nova", "Helvetica Neue", sans-serif;
                -webkit-transition: opacity 1s;
                -moz-transition: opacity 1s;
                -o-transition: opacity 1s;
                transition: opacity 1s;
                text-align: center;
                -webkit-perspective: 700px;
                -webkit-perspective-origin: 320px 700px;
                margin: 0 auto;
                height: 850px;
                width: 70%;
                max-width: 100%;
                margin: auto;
                top: 0;
                left: 0;
                bottom: 0;
                right: 0;
                position: absolute;
            }

            @-moz-document url-prefix() {
                .stack {
                    height: 640px;
                }
            }
            .tutorial_img{
                width:100%;
            }
            .vex.vex-theme-flat-attack.vex-theme-flat-attack-demo {
                overflow: hidden;
            }
            .vex.vex-theme-flat-attack.vex-theme-flat-attack-demo .vex-content {
                font-family: "proxima-nova", "Helvetica Neue", sans-serif;
                font-weight: 400;
            }
            .vex.vex-theme-flat-attack.vex-theme-flat-attack-demo .vex-dialog-form .vex-dialog-buttons {
                padding-top: 1em;
                margin-bottom: -2em;
                margin-left: -2em;
                margin-right: -1em;
            }
            .vex.vex-theme-flat-attack.vex-theme-flat-attack-demo .vex-content h1 {
                font-weight: 100;
                font-size: 60px;
                line-height: 1.2em;
                margin: 0;
            }
            .vex.vex-theme-flat-attack.vex-theme-flat-attack-demo .vex-content h2 {
                font-weight: 100;
                font-size: 40px;
                line-height: 1.2em;
                margin: 0;
            }
            .vex.vex-theme-flat-attack.vex-theme-flat-attack-demo .vex-content h2 u {
                text-decoration: none;
                font-weight: 300;
            }
            .vex.vex-theme-flat-attack.vex-theme-flat-attack-demo .vex-dialog-form .vex-dialog-buttons .vex-dialog-button {
                font-family: "proxima-nova", "Helvetica Neue", sans-serif;
                font-size: 2em;
                font-weight: 300;
            }

            .vex.vex-theme-stack-demo {
                overflow: hidden;
            }
            .vex.vex-theme-stack-demo.vex-theme-os .vex-dialog-message {
                padding: 1em;
            }
            .vex.vex-theme-stack-demo.vex-theme-wireframe .vex-overlay, .vex.vex-theme-stack-demo.vex-theme-os .vex-overlay {
                display: none;
            }
            .vex.vex-theme-stack-demo.vex-theme-flat-attack.vex-theme-flat-attack-green .vex-overlay {
                display: block;
                background: #34b989;
                top: 80px;
                bottom: 270px;
            }
            .vex.vex-theme-stack-demo .vex-content {
                font-family: "proxima-nova", "Helvetica Neue", sans-serif;
                font-weight: 400;
                width: 100%;
            }
            .vex.vex-theme-stack-demo .vex-dialog-message {
                padding-top: 1em;
                text-align: center;
                height: 320px;
            }
            .vex.vex-theme-stack-demo .vex-dialog-message h1 {
                line-height: 1.3em;
            }
            .vex.vex-theme-stack-demo.vex-closing {
                -webkit-transform: translate3d(0px, 200px, 200px);
            }

            .stack > .vex {
                -webkit-transition: -webkit-transform 0.7s;
            }

            .stack > .v0 { -webkit-transform: translate3d(0px, 0px, 0px); }
            .stack > .v1 { -webkit-transform: translate3d(0px, -200px, -200px); }
            .stack > .v2 { -webkit-transform: translate3d(0px, -400px, -400px); }
            .stack > .v3 { -webkit-transform: translate3d(0px, -600px, -600px); }
            .stack > .v4 { -webkit-transform: translate3d(0px, -800px, -800px); }

            @media (max-width: 568px) {
                .stack {
                    display: none;
                }

                body.demo-start .page, body.page-intro .page {
                    opacity: 1;
                }

                .page h2 {
                    font-size: 20px;
                }

                .page a {
                    width: 100px;
                    font-size: 10px;
                }

                .page h1 {
                    padding-top: .5em;
                }

                .vex.vex-theme-default .vex-dialog-form .vex-dialog-buttons .vex-dialog-button:focus {
                    animation: none;
                    -webkit-animation: none;
                    -moz-animation: none;
                    -ms-animation: none;
                    -o-animation: none;
                }
            }

            .examples a {
                background: #3288e6;
                color: #fff;
                padding: 8px 18px 6px;
                cursor: pointer;
                margin: 0 20px 20px 0;
                border-radius: 3px;
                display: inline-block;
            }
            .themes a {
                background: #34b989;
                color: #fff;
                padding: 8px 18px 6px;
                cursor: pointer;
                margin: 0 20px 20px 0;
                display: inline-block;
            }
            .themes a.selected {
                background: #2B755B;
            }

            .button {
                text-decoration: none;
                background: #000;
                color: #fff;
                padding: 8px 18px 6px;
                cursor: pointer;
                margin: 0 20px 20px 0;
                display: inline-block;
            }
        </style>
        <script>

            //javascript set cookie
            function createCookie(name,value,days) {
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime()+(days*24*60*60*1000));
                    var expires = "; expires="+date.toGMTString();
                }
                else var expires = "";
                document.cookie = name+"="+value+expires+"; path=/ ;domain=.mews.mobi";
            }
            var demo = {};

            demo.className = 'vex-theme-flat-attack vex-theme-flat-attack-demo';
            vex.defaultOptions.className = 'vex-theme-flat-attack';
            vex.dialog.defaultOptions.showCloseButton = true;

            demo.loadInitialDialogs = function(){
                $('body').addClass('page-intro');

                demo.initialDialogsClassName = 'vex-theme-wireframe vex-theme-stack-demo'

                for (var i = 4; i > -1; i--) {
                    var next_button = '►';
                    if(i==4){
                        next_button= "馬上開始";
                    }
                    vex.dialog.alert({
                        appendLocation: '.stack',
                        message: $('.stack-tempates > div:nth-child(' + (i + 1) + ')').html(),
                        className: demo.initialDialogsClassName,
                        buttons: [
                            $.extend({}, vex.dialog.buttons.YES, { text: next_button })
                        ],
                        callback: function(value) {

                            setTimeout(function(){
                                demo.advanceDemoDialogs();
                            }, 0);
                            
                        }
                    });
                }

                demo.advanceDemoDialogs();
            };

            demo.advanceDemoDialogs = function() {
                var $remaining = $('.stack > .vex:not(".vex-closing")');
                $('.stack').show();
                if ($remaining.length === 0) {
                    createCookie("tutorial_done", "done", 36500);
                    window.location.replace("index.php");
                }

                if ($remaining.length === 1) {
                    $.each($remaining.removeClass('').toArray().reverse(), function(i, item){
                        $(item).addClass('v' + i);
                    });
                }
                else{
                    $.each($remaining.removeClass('v0 v1 v2 v3 v4').toArray().reverse(), function(i, item){
                        $(item).addClass('v' + i);
                    });
                    
                }
                $('.stack > .v0:not(".vex-closing") input[type="submit"]').focus();


            }
        </script>
        <div class="page">

        </div>
        <div class="stack-tempates" style="display: none">
            <div>
                <?php
                echo FIRST_MESSAGE;
                ?>
            </div>
            <div>
                <?php
                echo SECOND_MESSAGE;
                ?>
            </div>
            <div>
                <?php
                echo THIRD_MESSAGE;
                ?>
            </div>
            <div>
                <?php
                echo FORTH_MESSAGE;
                ?>
            </div>
            <div>
                <?php
                echo PLEASE_LOGIN;
                ?>
                
            </div>
        </div>
        <style>
            .reveal {
                -webkit-transition: opacity 1s 0.3s;
                -moz-transition: opacity 1s 0.3s;
                -o-transition: opacity 1s 0.3s;
                transition: opacity 1s 0.3s;
                opacity: 0;
            }
            .vex.v0 .reveal {
                opacity: 1;
            }
        </style>
        <div class="stack"></div>
        <script>
            $('.demo-link').click(function(){
                demo.loadInitialDialogs();
                
            });
        </script>

        <style>
            #retweet_button {
                position: fixed;
                bottom: 30px;
                left: 50%;
                width: 100px;
                margin-left: -90px;
                -webkit-filter: grayscale(1) contrast(1.3);
                -webkit-transform: translateZ(0);
            }
            #retweet_button:hover {
                -webkit-filter: none;
            }

            credits {
                font-family: "proxima-nova", "Helvetica Neue", sans-serif;
                position: fixed;
                bottom: 35px;
                left: 50%;
                width: 130px;
                margin-left: 20px;
                font-size: 13px;
                color: #aaa;
            }

            credits a, credits a:link, credits a:visited {
                color: #aaa;
                text-decoration: none;
            }

            credits a:hover {
                text-decoration: underline;
                color: #444;
            }

            .documentation-link {
                font-family: "proxima-nova", "Helvetica Neue", sans-serif;
                position: fixed;
                bottom: 75px;
                left: 50%;
                width: 200px;
                margin-left: -100px;
                text-align: center;
                font-size: 16px;
            }

            .documentation-link, .documentation-link:link, .documentation-link:visited {
                color: #000;
                text-decoration: none;
            }

            .documentation-link:hover {
                text-decoration: underline;
                color: #000;
            }

            #retweet_button, credits, .documentation-link {
                transition: opacity 200ms;
                opacity: 1;
                z-index: 99;
            }

            body.demo-start #retweet_button, body.demo-start credits, body.demo-start .documentation-link,
            body.page-intro #retweet_button, body.page-intro credits, body.page-intro .documentation-link {
                opacity: 0;
                z-index: -1;
            }
        </style>

        <!-- Share -->
        <div id="retweet_button">
            <a href="http://twitter.com/share" class="twitter-share-button" data-url="http://github.hubspot.com/vex/docs/welcome" data-text="Check out Vex, a modern UI library for creating beautiful and functional dialogs." data-count="horizontal" data-via="HubSpotDev">Tweet</a>
            <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
        </div>

   


        <!-- Start of Async HubSpot Analytics Code -->
        <script type="text/javascript">
            (function(d,s,i,r) {
                if (d.getElementById(i)){return;}
                var n=d.createElement(s),e=d.getElementsByTagName(s)[0];
                n.id=i;n.src='//js.hubspot.com/analytics/'+(Math.ceil(new Date()/r)*r)+'/51294.js';
                e.parentNode.insertBefore(n, e);
            })(document,"script","hs-analytics",300000);
        </script>
        <!-- End of Async HubSpot Analytics Code -->

        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-45159009-1', 'hubspot.com');
            ga('send', 'pageview');
        </script>

        <!-- Force 3d acceleration always and forever :) -->
        <div style="-webkit-transform: translateZ(0)"></div>
    </body>
</html>
