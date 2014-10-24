<?php
    //session_start(); //Start the session
    error_reporting(E_ALL);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");
    $pagename=strtolower(basename($_SERVER['REQUEST_URI']));
    $pagename_array = explode("?", $pagename);
    $pagename = $pagename_array[0];
    $pagename_sub = "";
    if(count($pagename_array)>1){
    	$pagename_sub = $pagename_array[1];
    }
    $DEBUG_TAG = "header";

?>

<header id="header">
    

	<div id="header-top">
		<div class="wrapper clearfix">
			<div id="welcome">
      	        <span ><a href="index.php"><img id="header-logo" src="placeholders/logo.png" alt="logo" /> </a></span>
            </div><!--end:welcome-->
            <nav id="top-nav">
				<ul id="top-menu" class="clearfix">
					<li <?php if($pagename=="index.php"){echo 'class="current-menu-item"';}?> ><a href="index.php">首頁</a></li>
					<li <?php if($pagename=="news_page.php"
					||($pagename=="secondary.php"&&$pagename_sub!="category=news_blog")){echo 'class="current-menu-item"';}?> ><a href="news_page.php">分類報導</a>
						<ul>
                            <li><a href="secondary.php?category=news_crime"><img src="images/icons/crime_icon_small.png"></img>罪案報導</a></li>
                            <li><a href="secondary.php?category=news_accident"><img src="images/icons/accident_icon_small.png"></img>意外直擊</a></li>
                            <li><a href="secondary.php?category=news_event"><img src="images/icons/event_icon_small.png"></img>活動盛事</a></li>
                            <li><a href="secondary.php?category=news_politics"><img src="images/icons/politics_icon_small.png"></img>政治熱話</a></li>
                            <li><a href="secondary.php?category=news_funny"><img src="images/icons/funny_icon_small.png"></img>奇聞怪事</a></li>
                            <li><a href="secondary.php?category=news_crime"><img src="images/icons/tech_icon_small.png"></img>科技資訊</a></li>
                            <li><a href="secondary.php?category=news_event"><img src="images/icons/business_icon_small.png"></img>商業經濟</a></li>
                            <li><a href="secondary.php?category=news_life"><img src="images/icons/life_icon_small.png"></img>生活消閒</a></li>
                            <li><a href="secondary.php?category=news_other"><img src="images/icons/other_icon_small.png"></img>其他新聞</a></li>
                        </ul>
					</li>
                    <li <?php if($pagename=="secondary.php"&&$pagename_sub=="category=news_blog"){echo 'class="current-menu-item"';}?> ><a href="secondary.php?category=news_blog">個人專場</a></li>     
                    <?php
                        if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
                        	echo "<li ";
                        	if($pagename=="news_form.php"){
                        		echo 'class="current-menu-item"';
                        	}
                        	echo "><a href='news_form.php'>發表報道</a></li>";
                        }

                    ?>
                    <!--
                    The part for notifiction
                    
                    -->
                    <?php
                        if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
                        	echo "<li";
	                        if($pagename=="following.php"||$pagename=="my_following.php"){echo ' class="current-menu-item" ';}
	                        echo ">";
                        	$notifiation_html = "";
                        	$notificatin_counter = 0;
                        	$stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_FOLLOW." WHERE `follow_follower` =? AND `follow_type` =?");
                        	$stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_STR);
                        	$stmt->bindValue(2, "user", PDO::PARAM_STR);
                        	try{
                        		$stmt->execute();
                        	}
                        	catch(Exception $e){
                        		msg_log($DEBUG_TAG." : ".$e);
                        	}
                        	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                    			$mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_author_id` =? AND `news_post_date_time` >?";
                    			$stmt2 = $dbc->prepare($mysql_stmt);
                    			$stmt2->bindValue(1, $row['follow_following'],PDO::PARAM_STR);
                    			$stmt2->bindValue(2, $row['follow_last_seen'],PDO::PARAM_STR);
                        		$stmt2->execute();
	                    		
	                    		$loop_counter = 0;
	                    		while($row2=$stmt2->fetch(PDO::FETCH_ASSOC)){
	                    			if($loop_counter<1){
	                    				$stmt3 = $dbc->prepare("SELECT `user_name` FROM ".DB_TABLE_USER." WHERE `user_id` =?");
		                    			$stmt3->bindValue(1, $row2['news_author_id'],PDO::PARAM_STR);
		                    			$stmt3->execute();
		                    			while($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)){
		                    				$notifiation_html .= "<li><a href='follow.php?follow_type=user&follow_following=".$row2['news_author_id']."'>".$row3['user_name']." (".$stmt2->rowCount().")</a></li>";
		                    			}
	                    			}
	                    			$notificatin_counter++;
	                    			$loop_counter++;
	                    		}
                        	}

                        	$stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_FOLLOW." WHERE `follow_follower` =? AND `follow_type` =?");
                        	$stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_STR);
                        	$stmt->bindValue(2, "tag", PDO::PARAM_STR);
                        	try{
                        		$stmt->execute();
                        	}
                        	catch(Exception $e){
                        		msg_log($DEBUG_TAG." : ".$e);
                        	}
                        	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                    			$mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE (`news_tag_1` =? OR `news_tag_2` =? OR `news_tag_3` =? OR `news_tag_4` =?) AND `news_post_date_time` >?";
                    			$stmt2 = $dbc->prepare($mysql_stmt);
                    			$stmt2->bindValue(1, $row['follow_following'],PDO::PARAM_STR);
                    			$stmt2->bindValue(2, $row['follow_following'],PDO::PARAM_STR);
                    			$stmt2->bindValue(3, $row['follow_following'],PDO::PARAM_STR);
                    			$stmt2->bindValue(4, $row['follow_following'],PDO::PARAM_STR);
                    			$stmt2->bindValue(5, $row['follow_last_seen'],PDO::PARAM_STR);
                        		$stmt2->execute();
	                    		
	                    		$loop_counter = 0;
	                    		while($row2=$stmt2->fetch(PDO::FETCH_ASSOC)){
	                    			if($loop_counter<1){
		                    			$notifiation_html .= "<li><a href='follow.php?follow_type=tag&follow_following=".$row['follow_following']."'>".$row['follow_following']." (".$stmt2->rowCount().")</a></li>";
	                    			}
	                    			$notificatin_counter++;
	                    			$loop_counter++;
	                    		}
                        	
                        	}
                        	echo "<a href='my_following.php' >我的關注 (".$notificatin_counter.")</a>";
                        	echo "<ul>";   	
                        	echo $notifiation_html;
                        	echo "</ul>";
                        }
                        else{
                        	//echo "<a href='".LOGIN_PAGE."'>Facebook登入</a>";
                        }
                        echo "</li>";
                    ?>              

                    <?php
                        echo "<li";
                        if($pagename=="user_profile.php"||$pagename=="edit_profile.php"){echo ' class="current-menu-item" ';}
                        echo ">";
                        if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
                        	echo "<a href='user_profile.php?user_id=".$_SESSION['user_id']."' >我的帳戶</a>";
                        	echo "<ul>";
                        	echo "<li><a href='edit_profile.php'>設定</a></li>";
                        	if($_SESSION['user_is_special'] != 1){
                        		echo "<li><a href='edit_profile.php'>申請專區</a></li>";
                        	}
                        	if(isset($_SESSION['user_is_admin'])){
                        		if($_SESSION['user_is_admin']==1){
                        		echo "<li><a href='super_johnny_and_joyce_dont_hack_please/admin_panel.php'>管理員版面</a></li>";
                        	}
                        	}
                        	
                            echo "<li><a href='".REDIRECT_PAGE."?action=logout'>登出:".$_SESSION['username']."</a></li>";
                        	echo "</ul>";
                        }
                        else{
                        	echo "<a href='".LOGIN_PAGE."'>Facebook登入</a>";
                        }
                        echo "</li>";
                    ?>  



				</ul>
			</nav><!--end:top-nav-->			
		</div><!--wrapper-->	
	</div><!--header-top-->

    <div id="header-bottom">            
        <div class="wrapper clearfix">
            <div id="headline" class="clearfix">
                <h6>最新報道： </h6>
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
                        echo "".$row['news_date']." - ".'<a href="primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a>';
                    }
                    ?>
                    </ul>
                </div>
            </div><!--end:headline-->
            <div id="search-social" class="clearfix">                
                <ul class="social-links clearfix">
                    <li class="facebook-icon">
                        <a target="_blank" title="Facebook" class="facebook" href="https://www.facebook.com/mewsapp">
                            <img src="images/icons/facebook-icon-hover.png" alt="" />
                        </a>
                    </li>
             
                </ul><!--end:social-links-->
                <div class="search-box clearfix">
                    <form action="search_result.php?" class="search-form" method="get" />
                        <input type="hidden" value="text" name="search_type" class="search-text" />
                        <input type="text" onBlur="if(this.value=='')this.value=this.defaultValue;" onFocus="if(this.value==this.defaultValue)this.value='';" value="Search" name="search_key" class="search-text" />
                        <input type="submit" value="" name="" class="search-submit" />
                    </form><!-- search-form -->
                </div><!--end:search-box--> 
            </div><!--search-social-->
        </div><!--end:wrapper-->        
    </div><!--header-bottom-->
    <div id="hot-tag-div">            
        <div class="wrapper clearfix">
            <div id="" class="clearfix">
                <span class="hot-tag-title">熱門#TAG: &nbsp;&nbsp;</span>
                <?php $mysql_hot_tag = "SELECT * ,(`tag_no_follower`*5+`tag_total_no`*3)/TIMEDIFF(NOW(),`tag_last_add`) AS `tag_hotness` FROM ".DB_TABLE_TAG." ORDER BY `tag_hotness` DESC LIMIT 5"; 
                $stmt = $dbc->prepare($mysql_hot_tag);
                $stmt->execute();
                while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                    echo '<span class="hot-tag"><a href="search_result.php?search_type=tag&search_key='.$row['tag_name'].'">#'.$row['tag_name'].'</a></span>';
                }

                ?>
            </div><!--end:headline-->
        </div><!--end:wrapper-->        
    </div><!--header-bottom-->
</header>