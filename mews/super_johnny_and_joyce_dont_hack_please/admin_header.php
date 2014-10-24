<?php
    //session_start(); //Start the session
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");
    $pagename=strtolower(basename($_SERVER['REQUEST_URI']));
    $pagename_array = explode("?", $pagename);
    $pagename = $pagename_array[0];
    $pagename_sub = "";
    if(count($pagename_array)>1){
        $pagename_sub = $pagename_array[1];
    }

?>

<header id="header">
    <div id="header-top">
        <div class="wrapper clearfix">
            <div id="welcome">
                Welcome to MEWS! 
            </div><!--end:welcome-->
            <nav id="top-nav">
                <ul id="top-menu" class="clearfix">
                    <li <?php if($pagename=="index.php"){echo 'class="current-menu-item"';}?> ><a href="../index.php">Home</a></li>
                    <li <?php if($pagename=="secondary.php"&&$pagename_sub=="category=news_around"){echo 'class="current-menu-item"';}?> ><a href="../secondary.php?category=news_around">身邊事</a></li>
                    <li <?php if($pagename=="secondary.php"&&$pagename_sub=="category=news_big"
                    ||$pagename_sub=="search_type=type&search_key=news_accident"
                    ||$pagename_sub=="search_type=type&search_key=news_crime"
                    ||$pagename_sub=="search_type=type&search_key=news_event"
                    ||$pagename_sub=="search_type=type&search_key=news_politics"
                    ||$pagename_sub=="search_type=type&search_key=news_other"){echo 'class="current-menu-item"';}?> ><a href="../secondary.php?category=news_big">大件事</a>
                        <ul>
                            <li><a href="../search_result.php?search_type=type&search_key=news_accident">意外</a></li>
                            <li><a href="../search_result.php?search_type=type&search_key=news_crime">罪案</a></li>
                            <li><a href="../search_result.php?search_type=type&search_key=news_event">活動盛事</a></li>
                            <li><a href="../search_result.php?search_type=type&search_key=news_politics">政治</a></li>
                            <li><a href="../search_result.php?search_type=type&search_key=news_other">其他</a></li>
                        </ul>
                    </li>
                    <li <?php if($pagename=="secondary.php"&&$pagename_sub=="category=news_funny"){echo 'class="current-menu-item"';}?> ><a href="../secondary.php?category=news_funny">奇趣</a></li>
                    <li <?php if($pagename=="secondary.php"&&$pagename_sub=="category=news_blog"){echo 'class="current-menu-item"';}?> ><a href="../secondary.php?category=news_blog">個人專場</a></li>     
                    <?php
                        echo "<li";
                        if($pagename=="user_profile.php"||$pagename=="edit_profile.php"){echo ' class="current-menu-item" ';}
                        echo ">";
                        if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
                            echo "<a href='../user_profile.php?user_id=".$_SESSION['user_id']."' >我的帳戶</a>";
                            echo "<ul>";
                            echo "<li><a href='../edit_profile.php'>設定</a></li>";
                            if($_SESSION['user_is_special'] != 1){
                                echo "<li><a href='../edit_profile.php'>申請專區</a></li>";
                            }
                            if($_SESSION['user_is_admin']){
                                echo "<li><a href='../super_johnny_and_joyce_dont_hack_please/admin_panel.php'>管理員版面</a></li>";
                            }
                            echo "<li><a href='".REDIRECT_PAGE."?action=logout'>登出:".$_SESSION['username']."</a></li>";
                            echo "</ul>";
                        }
                        else{
                            echo "<a href='../".LOGIN_PAGE."'>Facebook登入</a>";
                        }
                        echo "</li>";
                    ?>              

                </ul>
            </nav><!--end:top-nav-->            
        </div><!--wrapper-->    
    </div><!--header-top-->
    <div id="header-middle">
        <div class="wrapper clearfix">
            <div id="logo-image"><a href="../index.php"><img src="placeholders/logo.png" alt="logo" /></a></div>
        </div><!--wrapper-->
    </div><!--header-middle-->

    <div id="header-bottom">            
        <div class="wrapper clearfix">
            <div id="headline" class="clearfix">
                <h6>最新報道</h6>
                <div class="horizontal_scroller">
                    <ul class="scrollingtext clearfix">
                    <!--
                    This is the marquee message, only temperature and breaking news will be reported 
                    Breaking news defination:
                    within 5 days, more than 5000 read
                    -->
                    <?php
                    $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_NEWS." ORDER BY `news_post_date_time` DESC LIMIT 5");
                    $stmt->execute();
                    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                        echo "<li>".$row['news_date']." - ".'<a href="../primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a></li> ';
                    }
                    ?>
                    </ul>
                </div>
            </div><!--end:headline-->
            <div id="search-social" class="clearfix">                
                <ul class="social-links clearfix">
                    <li class="facebook-icon">
                        <a target="_blank" title="Facebook" class="facebook" href="https://www.facebook.com/mewsapp">
                            <img src="../images/icons/facebook-icon.png" alt="" />
                        </a>
                    </li>
             
                </ul><!--end:social-links-->
                <div class="search-box clearfix">
                    <form action="search_result.php?search_type=text&" class="search-form" method="get" />
                        <input type="text" onBlur="if(this.value=='')this.value=this.defaultValue;" onFocus="if(this.value==this.defaultValue)this.value='';" value="Search" name="search_key" class="search-text" />
                        <input type="submit" value="" name="submit" class="search-submit" />
                    </form><!-- search-form -->
                </div><!--end:search-box--> 
            </div><!--search-social-->
        </div><!--end:wrapper-->        
    </div><!--header-bottom-->
</header>