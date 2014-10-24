<?php
header('Content-Type: text/html; charset=utf-8');

require_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.php");

//sanitize post value


$DEBUG_TAG = "get_news_map";
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
if(isset($_POST["lat"])&&isset($_POST["lng"])&&$_POST["fetch_type"]=="map"){
    
    $search_lat = $_POST['lat'];
    $search_lng = $_POST['lng'];
    $table = DB_TABLE_NEWS;
    $reqDist=1.8;

    $newsTypeMysql = "";
    if($_POST["news_type"]!=""){
        $newsTypeMysql = " `news_type` = '".$_POST["news_type"]."' AND";
    }
    
    $lng1 = $search_lng-$reqDist/abs(cos(deg2rad($search_lat))*69);        
    $lng2 = $search_lng+$reqDist/abs(cos(deg2rad($search_lat))*69);                       
    $lat1 = $search_lat-($reqDist/69);        
    $lat2 = $search_lat+($reqDist/69);

    $mysql_stmt = "SELECT *,  
    ( 3959 * acos( cos( radians($search_lat) ) 
    * cos( radians( $table.news_lat ) ) 
    * cos( radians( $table.news_lng ) - radians($search_lng) ) 
    + sin( radians($search_lat) ) 
    * sin( radians( $table.news_lat ) ) ) ) AS distance 
    FROM $table
    WHERE".$newsTypeMysql.
    " `news_post_date_time` > DATE_SUB(NOW(), INTERVAL 20 DAY) and
    $table.news_lat between $lat1 and $lat2
    and $table.news_lng between $lng1 and $lng2
    having `distance` < $reqDist ORDER BY `distance` ASC limit 15";



    msg_log($DEBUG_TAG.": "."Get lat = ".$_POST["lat"]);
    msg_log($DEBUG_TAG.": "."Get lng = ".$_POST["lng"]);
    msg_log($DEBUG_TAG.": "."Get mysql = ".$mysql_stmt);
    try{
        $stmt = $dbc->prepare($mysql_stmt);
        $stmt->execute();
    }catch(Exception $e){
        msg_log($DEBUG_TAG." : ".$e);
    }
    $result = array();
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $result[] = $row;
        msg_log($DEBUG_TAG." : ".$row['news_title']);
        //store the result in a 2d array
        //ecode it into json and pass it bact to javascript
    }
    echo json_encode($result);
}
if(isset($_POST["lat"])&&isset($_POST["lng"])&&$_POST["fetch_type"]=="list"){
    
    $search_lat = $_POST['lat'];
    $search_lng = $_POST['lng'];
    $table = DB_TABLE_NEWS;
    $reqDist=1.8;
    $newsTypeMysql = "";
    if($_POST["news_type"]!=""){
        $newsTypeMysql = " `news_type` = '".$_POST["news_type"]."' AND";
    }
    $lng1 = $search_lng-$reqDist/abs(cos(deg2rad($search_lat))*69);        
    $lng2 = $search_lng+$reqDist/abs(cos(deg2rad($search_lat))*69);                       
    $lat1 = $search_lat-($reqDist/69);        
    $lat2 = $search_lat+($reqDist/69);

    

    $mysql_stmt = "SELECT *,  
    ( 3959 * acos( cos( radians($search_lat) ) 
    * cos( radians( $table.news_lat ) ) 
    * cos( radians( $table.news_lng ) - radians($search_lng) ) 
    + sin( radians($search_lat) ) 
    * sin( radians( $table.news_lat ) ) ) ) AS distance 
    FROM $table
    WHERE".$newsTypeMysql.
    " `news_post_date_time` > DATE_SUB(NOW(), INTERVAL 20 DAY) and
    $table.news_lat between $lat1 and $lat2
    and $table.news_lng between $lng1 and $lng2
    having `distance` < $reqDist ORDER BY `distance` ASC limit 15";

    try{
        $stmt = $dbc->prepare($mysql_stmt);
        $stmt->execute();
    }catch(Exception $e){
        msg_log($DEBUG_TAG." : ".$e);
    }

    
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
            $news_type = "其他";
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

                        echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="row-news-pic hover-effect" src="../'.GW_IMAGEPATH.$row['news_pic1'].'" alt="" ></img></a>';

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
if(isset($_POST["news_id"])){
    
    $mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_id` =?";
    try{
        $stmt = $dbc->prepare($mysql_stmt);
        $stmt->bindValue(1, $_POST["news_id"],PDO::PARAM_STR);
        $stmt->execute();
    }catch(Exception $e){
        msg_log($DEBUG_TAG." : ".$e);
    }

    
    echo '<div class="fadeout-background">  </div><ul class="page_result">';
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
            $news_type = "其他";
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

        echo '<div class="article-wrap selected bounceIn animated" id="news-selected-div">
                    <article class="entry-item box-hover clearfix large-list">';
                    if(strlen($row['news_pic1'])>1){
                        echo '<a href="primary.php?news_id='.$row['news_id'].'"><img class="row-news-pic hover-effect" src="../'.GW_IMAGEPATH.$row['news_pic1'].'" alt="" /></a>';
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

