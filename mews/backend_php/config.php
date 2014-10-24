<?php
	date_default_timezone_set("America/New_York");
    set_time_limit(0);
    /** USER NAME AND PASSWORD */
    /** MYSQL */
    define('DB_NAME', 'mewsmobi_mews');
    define('DB_USER', 'mewsmobi_admin07');
    define('DB_PASSWORD', 'comp104104');
    define('DB_HOST', 'localhost');  
    /** FACEBOOK */
    define('FACEBOOK_APP_ID', '135716029958952');  
    define('FACEBOOK_APP_SECRET', 'e49af6ac7dd404027b9c98bbfca63fd7');  



    define('DB_TABLE_NEWS', 'db_table_news');  
    define('DB_TABLE_USER', 'db_table_user');  
    define('DB_TABLE_COMMENT', 'db_table_comment');  
    define('DB_TABLE_REPUTATION', 'db_table_reputation');  
    define('DB_TABLE_SPAM', 'db_table_spam'); 
    define('DB_TABLE_BLOCK', 'db_table_block'); 
    define('DB_TABLE_TAG', 'db_table_tag'); 
    define('DB_TABLE_SPECIAL_PENDING', 'db_table_special_pending'); 
    define('DB_TABLE_HACK', 'db_table_hack'); 
    define('DB_TABLE_MSG', 'db_table_msg'); 
    define('DB_TABLE_FOLLOW', 'db_table_follow'); 
	// Define application constants
    define('GW_MAXIMAGESIZE', 2000000);
    define('GW_MAXPROFILESIZE', 500000);
    define('GW_IMAGEPATH', 'news_images/');
    define('GW_USER_IMAGEPATH', 'user_images/');
    define('GW_THUMBNAIL_IMAGEPATH', 'news_thumbnail_images/');

    /**
     * website parameters     
     */
	$item_per_page = 5; // for ajax pagination at HOME PAGE
    define('NEWS_AROUND', "news_around");
    define('NEWS_HK', "news_hk");
    define('NEWS_FUN', "news_fun");

    define('NEWS_HK_ACCIDENT', "news_accident");
    define('NEWS_HK_CRIME', "news_crime");
    define('NEWS_HK_EVENT', "news_event");
    define('NEWS_HK_OTHER', "news_other");
    /**
     * page redirection url
     */
    define('BASE_URL', "http://www.mews.mobi/");
    define('HOME_PAGE', "http://www.mews.mobi/index.php");
    define('LOGIN_PAGE', "http://www.mews.mobi/facebook_login.php");
    define('REDIRECT_PAGE', "http://www.mews.mobi/redirect.php");
    define('EDIT_PROFILE', "http://www.mews.mobi/edit_profile.php");
    define('NEWS_FORM', "http://www.mews.mobi/news_form.php");
    define('NEWS_TERTIARY', "http://www.mews.mobi/tertiary.php");
    define('NEWS_SECONDARY_PAGE', "http://www.mews.mobi/secondary.php");
    define('NEWS_PRIMARY', "http://www.mews.mobi/primary.php");
    define('NEWS_ARTICLE', "http://www.mews.mobi/news_article.php");
    define('PAGE_NOT_FOUND', "http://www.mews.mobi/404.php");


    /**
     * Default img source
     */
    define('DEFAULT_NEWS_IMG', "default.jpg");
    define('DEFAULT_NEWS_CRIME_IMG', "http://www.mews.mobi/news_images/default.jpg");
    define('DEFAULT_NEWS_EVENT_IMG', "http://www.mews.mobi/news_images/default.jpg");
    define('DEFAULT_NEWS_FUNNY_IMG', "http://www.mews.mobi/news_images/default.jpg");
    define('DEFAULT_NEWS_ACCIDENT_IMG', "http://www.mews.mobi/news_images/default.jpg");
    define('DEFAULT_NEWS_OTHER_IMG', "http://www.mews.mobi/news_images/default.jpg");
    define('DEFAULT_NEWS_BLOG_IMG', "http://www.mews.mobi/news_images/default.jpg");


    /**
     * Defiened Message 
     */
    define('WELCOME', "You are logging in as: ");
    define('LOGIN_ERROR', "Wrong Username or Password. Please retry! ");

    
    
	/*
	 * Error logging function
	 */
    $CURR_PATH=dirname(__FILE__);
	$CURR_DATE=date("Ymd");
	$LOG_FILE=$CURR_PATH . DIRECTORY_SEPARATOR . "log/{$CURR_DATE}.log";
	function msg_log($msg) {
    global $LOG_FILE;
    if (!is_dir(dirname($LOG_FILE))) {
        // dir doesn't exist, make it
      	mkdir(dirname($LOG_FILE));
        }
        file_put_contents($LOG_FILE, (date("Y-m-d H:i:s")."\t\t".$msg."\r\n"), FILE_APPEND);       
    }
    // $url should be an absolute url

    function redirect($url){
        if (headers_sent()){
            die('<script type="text/javascript">window.location.href="' . $url . '";</script>');
        }else{
            header( "refresh:3;url=".$url );
            die();
        }    
    }
    function redirectToError($url, $message, $next_url){
        if (headers_sent()){
            echo "<script> alert('".$message."') </script>";
            echo "<script type='text/javascript'>window.location.href='" . $url . "';</script>";
        }else{
            header('Location: ' . $url."?error_msg=".$message."&url=".$next_url);
        }    
    }

        //function for getting thumbnail image
    function compress_image($source_url, $destination_url, $quality) 
    { 
        $info = getimagesize($source_url); 
        if ($info['mime'] == 'image/jpeg') 
            {$image = imagecreatefromjpeg($source_url); }
        else if ($info['mime'] == 'image/gif') 
            {$image = imagecreatefromgif($source_url); }
        else if ($info['mime'] == 'image/png') 
            {$image = imagecreatefrompng($source_url); }
        imagejpeg($image, $destination_url, $quality); 
        return $destination_url; 
    }
     
?>