<?php
header('Content-Type: text/html; charset=utf-8');

require_once (dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");

//sanitize post value
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
if(isset($_POST["search_type"])&&isset($_POST["mysql_stmt"])){
    if($_POST['search_type']=="admin_action"){
        $mysql_stmt = $_POST["mysql_stmt"]."LIMIT ".$position.", ".$item_per_page;
	    $stmt = $dbc->prepare ($mysql_stmt); 


        try{
    	    $stmt->execute ();
    	    echo '<ul class="page_result">';
	        while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
                echo '<div class="latest-post">';
                if($_POST['search_key']=="pending_user"){
                    $stmt2 = $dbc->prepare("SELECT `user_name` FROM ".DB_TABLE_USER." WHERE user_id =?");
                    $stmt2->bindValue(1, $row['pending_user_id'], PDO::PARAM_STR);
                    try{
                        $stmt2->execute();
                        while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
                            $name = $row2['user_name'];
                        }
                    }catch(Exception $e){
                        msg_log($DEBUG_TAG." : ".$e);
                    }
                    echo "<a href='../user_profile.php?user_id=".$row['pending_user_id']."'><h2>&nbsp;".$name."</h2></a><br>";
                    
                    echo "<h6>。".$row['pending_special_ability']."</h6> 
                    <br>";
                    echo "<p>。".$row['pending_intro']."</p> <br>";
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw  fa-times-circle"></i>:<a href="backend_php/admin_action_update.php?pending_id='.$row['pending_id'].'&from=pending_user&action=deny_application&user_id='.$row['pending_user_id'].'">駁回申請</a></span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw   fa-check-circle"></i>:<a href="backend_php/admin_action_update.php?pending_id='.$row['pending_id'].'&from=pending_user&action=approve_application&user_id='.$row['pending_user_id'].'">批准申請</a></span>';
                }else if($_POST['search_key']=="abuse_user"){
                    echo "<span><a href='http://whatismyipaddress.com/ip/".$row['rep_giver_ip']."'><h2>・".$row['rep_giver_ip']."</h2></a></span>";
                    echo "此IP 使用名字及Facebook ID：<br>";
                    $stmt2 = $dbc->prepare("SELECT DISTINCT `rep_giver` FROM ".DB_TABLE_REPUTATION." WHERE rep_giver_ip =?");
                    $stmt2->bindValue(1, $row['rep_giver_ip'], PDO::PARAM_STR);
                    try{
                        $stmt2->execute();
                        while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
                            $stmt3 = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER." WHERE user_id =?");
                            $stmt3->bindValue(1, $row2['rep_giver'], PDO::PARAM_STR);
                            $stmt3->execute();
                            while($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)){                    
                                echo "User Name:&nbsp;<a href='../user_profile.php?user_id=".$row3['user_id']."'>".$row3['user_name']."</a>";
                                echo '<span>,&nbsp;&nbsp;Facebook:&nbsp;<a href="http://facebook.com/profile.php?id='.$row3['user_facebook_id'].'">'.$row3['user_facebook_id'].'</a> </span>';
                            }
                        }
                    }catch(Exception $e){
                        msg_log($DEBUG_TAG." : ".$e);
                    }                    
                    echo "<h6>。一天內投票次數".$row['count_ip']."</h6> 
                    <br>";
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw  fa-times-circle"></i>:<a href="backend_php/admin_action_update.php?user_ip='.$row['rep_giver_ip'].'&from=abuse_user&action=block_user">封鎖ip</a></span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw   fa-check-circle"></i>:<a href="backend_php/admin_action_update.php?user_ip='.$row['rep_giver_ip'].'&from=abuse_user&action=cancel_alert">取消警報</a></span>';                    
                }else if($_POST['search_key']=="reputation_alert"){
                    echo "。<a href='../primary.php?news_id=".$row['news_id']."'>".$row['news_title']."</a> 
                    <br>&nbsp&nbsp;&nbsp&nbsp;";
                    $stmt2 = $dbc->prepare("SELECT `user_name` FROM ".DB_TABLE_USER." WHERE user_id =?");
                    $stmt2->bindValue(1, $row['news_author_id'], PDO::PARAM_STR);
                    try{
                        $stmt2->execute();
                        while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
                            $name = $row2['user_name'];
                        }
                    }catch(Exception $e){
                        msg_log($DEBUG_TAG." : ".$e);
                    }
                    echo "<a href='../user_profile.php?user_id=".$row['news_author_id']."'>".$name."</a>&nbsp&nbsp;".$row['news_date'];
                    if(strlen($row['news_address'])>1){
                        echo'<span>&nbsp;<i class="fa fa-fw fa-map-marker"></i>'.$row['news_address'].'</span>';
                    }
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-eye"></i>:'.$row['news_no_read'].'</span>';
                    echo '<span>&nbsp;&nbsp;<i class="glyphicon glyphicon-fire"></i>:'.$row['news_no_useful'].'</span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-fire-extinguisher"></i>:'.$row['news_no_rubbish'].'</span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-trash-o"></i>:'.$row['news_no_spam'].'</span><br>';
                    echo '&nbsp&nbsp;<span>&nbsp;&nbsp;<i class="glyphicon glyphicon-fire"></i>:<a href="backend_php/admin_action_update.php?user_id='.$row['news_author_id'].'&from=reputation_alert&action=add_hot&news_id='.$row['news_id'].'">加為最熱文章</a></span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw  fa-times-circle"></i>:<a href="backend_php/admin_action_update.php?user_id='.$row['news_author_id'].'&from=reputation_alert&action=block&news_id='.$row['news_id'].'">封鎖此文並扣減作者分數</a></span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw   fa-check-circle"></i>:<a href="backend_php/admin_action_update.php?user_id='.$row['news_author_id'].'&from=reputation_alert&action=cancel&news_id='.$row['news_id'].'">取消警報</a></span>';
                    echo '</div>';      
                }else if($_POST['search_key']=="spam_flag"){
                    echo "<a href='../primary.php?news_id=".$row['news_id']."'>・".$row['news_title']."</a> 
                    <br>&nbsp&nbsp;&nbsp&nbsp;";
                    $stmt2 = $dbc->prepare("SELECT `user_name` FROM ".DB_TABLE_USER." WHERE user_id =?");
                    $stmt2->bindValue(1, $row['news_author_id'], PDO::PARAM_STR);
                    try{
                        $stmt2->execute();
                        while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
                            $name = $row2['user_name'];
                        }
                    }catch(Exception $e){
                        msg_log($DEBUG_TAG." : ".$e);
                    }
                    echo "<a href='../user_profile.php?user_id=".$row['news_author_id']."'>".$name."</a>&nbsp&nbsp;".$row['news_date'];
                    if(strlen($row['news_address'])>1){
                        echo'<span>&nbsp;<i class="fa fa-fw fa-map-marker"></i>'.$row['news_address'].'</span>';
                    }
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-eye"></i>:'.$row['news_no_read'].'</span>';
                    echo '<span>&nbsp;&nbsp;<i class="glyphicon glyphicon-fire"></i>:'.$row['news_no_useful'].'</span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-fire-extinguisher"></i>:'.$row['news_no_rubbish'].'</span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw fa-trash-o"></i>:'.$row['news_no_spam'].'</span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw  fa-times-circle"></i>:<a href="backend_php/admin_action_update.php?user_id='.$row['news_author_id'].'&from=spam_flag&action=block&news_id='.$row['news_id'].'">封鎖此文並扣減作者分數</a></span>';
                    echo '<span>&nbsp;&nbsp;<i class="fa fa-fw   fa-check-circle"></i>:<a href="backend_php/admin_action_update.php?user_id='.$row['news_author_id'].'&from=spam_flag&action=cancel&news_id='.$row['news_id'].'">取消警報</a></span>';
                    echo '</div>';      
                }
                
                
            }
	        echo '</ul>';
        }
        catch(Exception $e){
        	msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
        }
    }
}

?>

