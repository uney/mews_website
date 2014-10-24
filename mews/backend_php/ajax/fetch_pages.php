<?php
header('Content-Type: text/html; charset=utf-8');

require_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.php");

//sanitize post value
if(isset($_POST["page"])){
    $page_number = filter_var($_POST["page"], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);

    //validate page number is really numaric
    if(!is_numeric($page_number)){die('Invalid page number!');}

    //get current starting point of records
    if(isset($_POST['item_per_page'])){
        $item_per_page = $_POST['item_per_page'];
    }
    $position = ($page_number * $item_per_page);
    if(isset($_POST['offset'])){
        $position = $position + (int)$_POST['offset'];
    }

}

$DEBUG_TAG = "fetch_pages";
$dbhost = DB_HOST;
$dbuser = DB_USER;
$dbpass = DB_PASSWORD;
$dbname = DB_NAME;

//Limit our results within a specified range. 
try {
    $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
	    		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
	$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
catch ( PDOException $e ) {
	echo $DEBUG_TAG. ": " . $e->getMessage ();

	msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
}
if(isset($_POST["search_type"])&&isset($_POST["mysql_stmt"])&&!isset($_POST["is_following"])){
    //
    // Ajax for search page
    // Ajax for search page
    // Ajax for search page
    //
    //
    if($_POST["search_type"]!="secondary"){
        $mysql_stmt = $_POST["mysql_stmt"]."LIMIT ".$position.", ".$item_per_page;
	    $stmt = $dbc->prepare ($mysql_stmt); 
	    if($_POST['search_type']=="tag"){
           //search key == tag name
           $stmt->bindValue(1, $_POST['search_key'], PDO::PARAM_STR);
           $stmt->bindValue(2, $_POST['search_key'], PDO::PARAM_STR);
           $stmt->bindValue(3, $_POST['search_key'], PDO::PARAM_STR);
           $stmt->bindValue(4, $_POST['search_key'], PDO::PARAM_STR);
        }else if($_POST['search_type']=="text"){
         //search key == text
            $fullTextSearch = "%".$_POST['search_key']."%";
            $stmt->bindValue(1, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(2, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(3, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(4, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(5, $fullTextSearch, PDO::PARAM_STR);
            $stmt->bindValue(6, $_fullTextSearch, PDO::PARAM_STR);
        }else if($_POST['search_type']=="type"){
         //search key == text
           $stmt->bindValue(1, $_POST['search_key'], PDO::PARAM_STR);
        }else if($_POST['search_type']=="user"){
           //search_key == user_id
           $stmt->bindValue(1, $_POST['search_key'], PDO::PARAM_STR);
        }else if($_POST['search_type']=="location"){
           //search_key == addres, will not be use in this case
           // location search need not to paginate
        }

        try{
    	    $stmt->execute ();
    	    echo '<ul class="page_result">';
	        while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                echo '<div class="latest-post">';
                echo "<a href='primary.php?news_id=".$row['news_id']."'>・".$row['news_title']."</a> 
                <br>&nbsp&nbsp;&nbsp&nbsp;";
                $stmt2 = $dbc->prepare("SELECT `user_name` FROM ".DB_TABLE_USER." WHERE user_id =?");
                $stmt2->bindValue(1, $row['news_author_id'], PDO::PARAM_STR);
                try{
                    $stmt2->execute();
                    while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
    	                $author_name = $row2['user_name'];
                    }
                }catch(Exception $e){
                	msg_log($DEBUG_TAG." search page: ".$e);
                }
                echo "<a href='user_profile.php?user_id=".$row['news_author_id']."'>".$author_name."</a>&nbsp&nbsp;".$row['news_date'];
                if(strlen($row['news_address'])>1){
                    echo'<span>&nbsp;<i class="fa fa-fw fa-map-marker"></i>'.$row['news_address'].'</span>';
                }
                echo '<span>&nbsp;&nbsp;<i class="fa fa-eye"></i>:'.$row['news_no_read'].'</span>';
                echo '<span>&nbsp;&nbsp;<i class="glyphicon glyphicon-fire"></i>:'.$row['news_no_useful'].'</span>';
                echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-fire-extinguisher"></i>:'.$row['news_no_rubbish'].'</span>';
                echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-trash-o"></i>:'.$row['news_no_spam'].'</span>';
                echo '</div>';	    
            }
	        echo '</ul>';
        }
        catch(Exception $e){
        	msg_log ( $DEBUG_TAG. "search page: " . $e->getMessage () );
        }
    }
    else{
        //
        // Ajax for secondary page
        // Ajax for secondary page
        // Ajax for secondary page
        //
        //
        $mysql_stmt = $_POST["mysql_stmt"]."LIMIT ".$position.", ".$item_per_page;
        $stmt = $dbc->prepare ($mysql_stmt); 
        try{
            $stmt->execute ();
            $news_type = "身邊事";
            $meta = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        
            
            $doc = new DOMDocument();

            while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){

                $doc->loadHTML(mb_convert_encoding($row['news_content'], 'HTML-ENTITIES', 'UTF-8'));
                $news_preview_element = $doc->getElementsByTagName('span')->item(0);
                //$news_preview = iconv ( 'utf-8' , 'utf-8' , $news_preview_element->nodeValue );
                $news_preview = $news_preview_element->nodeValue;

                $news_preview = mb_strcut($news_preview, 0, 300, 'utf-8');
                $news_preview .="...";
                $author_name = "";
                $stmt2 = $dbc->prepare("SELECT `user_name` FROM ".DB_TABLE_USER." WHERE user_id =?");
                $stmt2->bindValue(1, $row['news_author_id'], PDO::PARAM_STR);
                try{
                    $stmt2->execute();
                    while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
                        $author_name = $row2['user_name'];
                    }
                }catch(Exception $e){
                    msg_log($DEBUG_TAG."secondary page : ".$e);
                }
                echo '<article class="entry-item box-hover clearfix large-list">';
                if(strlen($row['news_pic1'])>1){
                    echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="hover-effect row-news-pic" src="../'.GW_IMAGEPATH.$row['news_pic1'].'" alt="" /></a>';
                }
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
                echo '<div class="entry-content">';
                echo '  <span class="entry-category">分類:&nbsp;</span>
                        <a class="entry-category" href="secondary.php?category='.$row['news_type'].'">'.$news_type.'</a><span class="entry-date">&nbsp;&nbsp;|&nbsp;&nbsp;'.$row['news_date'].'</span><br>';
                if(strlen($row['news_address'])>1){
                    echo '<i class="fa fa-fw fa-map-marker"></i>'.$row['news_address'];
                }
                echo '<h3 class="entry-title"><a href="primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a></h3>
                            <p>'.$news_preview.'</p>
                            <span class="entry-author"><i class="fa fa-fw fa-user"></i>:&nbsp;</span><a href="user_profile.php?user_id='.$row['news_author_id'].'">'.$author_name.'</a>'.
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-eye"></i>:&nbsp;</span>'.$row['news_no_read'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="glyphicon glyphicon-fire"></i>:&nbsp;</span>'.$row['news_no_useful'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-fire-extinguisher"></i>:&nbsp;</span>'.$row['news_no_rubbish'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-comment-o"></i>:&nbsp;</span>'.$row['news_no_comment'].
                        '</div><!--end:entry-content-->
                    </article><!--end:entry-item-->';
            }

        }
        catch(Exception $e){
            msg_log ( $DEBUG_TAG. "secondary : " . $e->getMessage () );
        }    
    }
}else if(isset($_POST["search_type"])&&isset($_POST["mysql_stmt"])&&isset($_POST["is_following"])&&isset($_POST["follow_last_seen"])){
    //
    // Ajax for following page
    // Ajax for following page
    // Ajax for following page
    // Ajax for following page
    //
    $mysql_stmt = $_POST["mysql_stmt"]."LIMIT ".$position.", ".$item_per_page;
    $stmt = $dbc->prepare ($mysql_stmt); 
    if($_POST['search_type']=="tag"){
       //search key == tag name
       $stmt->bindValue(1, $_POST['search_key'], PDO::PARAM_STR);
       $stmt->bindValue(2, $_POST['search_key'], PDO::PARAM_STR);
       $stmt->bindValue(3, $_POST['search_key'], PDO::PARAM_STR);
       $stmt->bindValue(4, $_POST['search_key'], PDO::PARAM_STR);
       $stmt->bindValue(5, $_POST['follow_last_seen'], PDO::PARAM_STR);
    }else if($_POST['search_type']=="user"){
       //search_key == user_id
        $stmt->bindValue(1, $_POST['search_key'],PDO::PARAM_STR);
        $stmt->bindValue(2, $_POST['follow_last_seen'],PDO::PARAM_STR);
    }else if($_POST['search_type']=="location"){
       //search_key == addres, will not be use in this case
       // location search need not to paginate
    }

    try{
        $stmt->execute ();
        echo '<ul class="page_result">';
        while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
            echo '<div class="latest-post">';
            echo "<a href='primary.php?news_id=".$row['news_id']."'>・".$row['news_title']."</a> 
            <br>&nbsp&nbsp;&nbsp&nbsp;";
            $stmt2 = $dbc->prepare("SELECT `user_name` FROM ".DB_TABLE_USER." WHERE user_id =?");
            $stmt2->bindValue(1, $row['news_author_id'], PDO::PARAM_STR);
            try{
                $stmt2->execute();
                while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
                    $author_name = $row2['user_name'];
                }
            }catch(Exception $e){
                msg_log($DEBUG_TAG." follow1 : ".$e->getMessage ().", ".$mysql_stmt);
            }
            echo "<a href='user_profile.php?user_id=".$row['news_author_id']."'>".$author_name."</a>&nbsp&nbsp;".$row['news_date'];
            if(strlen($row['news_address'])>1){
                echo'<span>&nbsp;<i class="fa fa-fw fa-map-marker"></i>'.$row['news_address'].'</span>';
            }
            echo '<span>&nbsp;&nbsp;<i class="fa fa-eye"></i>:'.$row['news_no_read'].'</span>';
            echo '<span>&nbsp;&nbsp;<i class="glyphicon glyphicon-fire"></i>:'.$row['news_no_useful'].'</span>';
            echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-fire-extinguisher"></i>:'.$row['news_no_rubbish'].'</span>';
            echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-trash-o"></i>:'.$row['news_no_spam'].'</span>';
            echo '</div>';      
        }
        echo '</ul>';
    }
    catch(Exception $e){
        msg_log ( $DEBUG_TAG. "follow2 : " . $e->getMessage ().", ".$mysql_stmt );
    }
}
else{
    //
    // Ajax for hone page
    // Ajax for hone page
    // Ajax for hone page
    // Ajax for hone page
    //
    //
	try {
		$table = DB_TABLE_NEWS;
    	//$stmt = $dbc->prepare ( "SELECT *, (`news_comment`*0.8+`news_no_useful`*0.6+`news_no_read`*0.05-`news_no_rubbish`*0.4)/DATEDIFF(`news_date_time`, NOW()) AS `news_hotness`
    	//    FROM $table WHERE `news_date_time` > DATEDIFF(NOW(), INTERVAL 5 DAY) 
    	// 	  ORDER BY `news_hotness` DESC LIMIT $position, $item_per_page" );     
    	$stmt = $dbc->prepare ( "SELECT * ,(`news_no_comment`*0.8+`news_no_useful`*0.6+`news_no_read`*0.05-`news_no_rubbish`*0.4)/TIMEDIFF(NOW(),`news_post_date_time`) AS `news_hotness`
    		FROM $table WHERE `news_type` <>'news_blog' AND `news_type` <>'news_funny' AND `news_type` <>'news_life' AND `news_type` <>'news_event' AND `news_post_date_time` > DATE_SUB(curdate(), INTERVAL 30 DAY) ORDER BY `news_hotness` DESC LIMIT $position, $item_per_page" ); 
	   	$stmt->execute ();
	}
	catch ( Exception $e ) {
		echo $DEBUG_TAG. "hone page: " . $e->getMessage ();

    	msg_log ( $DEBUG_TAG. "hone page: " . $e->getMessage () );
	}


	//output results from database
	echo '<ul class="page_result">';
    $doc = new DOMDocument();
	while($row = $stmt->fetch ( PDO::FETCH_ASSOC ))
	{
        
        $news_type = "身邊事";
        $meta = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        //$doc->loadHTML($meta, $row['news_content']);
        
        $doc->loadHTML(mb_convert_encoding($row['news_content'], 'HTML-ENTITIES', 'UTF-8'));
        $news_preview_element = $doc->getElementsByTagName('span')->item(0);
        //$news_preview = iconv ( 'utf-8' , 'utf-8' , $news_preview_element->nodeValue );
        $news_preview = $news_preview_element->nodeValue;

        $news_preview = mb_strcut($news_preview, 0, 300, 'utf-8');
        $news_preview .="...";
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
        $stmt2 = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER." WHERE user_id =?");
        $stmt2->bindValue(1, $row['news_author_id'], PDO::PARAM_STR);
        try{
            $stmt2->execute();
            while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
                $author_name = $row2['user_name'];
            }
        }catch(Exception $e){
            msg_log($DEBUG_TAG." hone page: ".$e);
        }

		echo '<div class="article-wrap">
                    <article class="entry-item box-hover clearfix large-list">';
                    if(strlen($row['news_pic1'])>1){
                        echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="hover-effect row-news-pic" src="../'.GW_IMAGEPATH.$row['news_pic1'].'" alt="" /></a>';
                    }
                    echo '<div class="entry-content">';
                    echo '  <span class="entry-category">分類:&nbsp;</span>
                            <a class="entry-category" href="secondary.php?category='.$row['news_type'].'">'.$news_type.'</a><span class="entry-date">&nbsp;&nbsp;|&nbsp;&nbsp;'.$row['news_date'].'</span><br>';
                            if(strlen($row['news_address'])>1){
                                echo '<i class="fa fa-fw fa-map-marker"></i>'.$row['news_address'];
                            }
                    echo '<h3 class="entry-title"><a href="primary.php?news_id='.$row['news_id'].'">'.$row['news_title'].'</a></h3>
                            <p>'.$news_preview.'</p>
                            <span class="entry-author"><i class="fa fa-fw fa-user"></i>:&nbsp;</span><a href="user_profile.php?user_id='.$row['news_author_id'].'">'.$author_name.'</a>'.
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-eye"></i>:&nbsp;</span>'.$row['news_no_read'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="glyphicon glyphicon-fire"></i>:&nbsp;</span>'.$row['news_no_useful'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-fire-extinguisher"></i>:&nbsp;</span>'.$row['news_no_rubbish'].
                            '&nbsp;&nbsp;<span class="entry-author"><i class="fa fa-fw fa-comment-o"></i>:&nbsp;</span>'.$row['news_no_comment'].
                        '</div><!--end:entry-content-->
                    </article><!--end:entry-item-->
                </div><!--article-wrap-->';
	}

	echo '</ul>';
}
?>

