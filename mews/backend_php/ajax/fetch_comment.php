<?php

require_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.php");

//sanitize post value
$page_number = filter_var($_POST["page"], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);

//validate page number is really numaric
if(!is_numeric($page_number)){die('Invalid page number!');}

//get current starting point of records
$position = ($page_number * $item_per_page);

$DEBUG_TAG = "fetch_comment";
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
try {
	$table = DB_TABLE_COMMENT;

    $stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_COMMENT.
    	" WHERE comment_news_comment_id =? AND comment_type =? ORDER BY `comment_date_time` DESC LIMIT $position, $item_per_page" ); 
    $stmt->bindValue(1, $_POST['news_comment_id'], PDO::PARAM_STR);
    $stmt->bindValue(2, $_POST['type'], PDO::PARAM_STR);
    $stmt->execute ();
}
catch ( Exception $e ) {
	echo $DEBUG_TAG. ": " . $e->getMessage ();

    msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
}

/*
					<li class="comment depth-1">
						<article class="comment-wrap clearfix">
							<div class="comment-date clearfix">
								<span class="enrty-meta">On:</span><span class="entry-date">&nbsp;07.08.2012</span>
							</div>
							<img class="avatar" alt="" src="placeholders/avatar/avatar.jpg" />
							<div class="comment-body">
								<h6 class="author">Admin says&nbsp;&nbsp;
									<span class="entry-news-info"><i id="news_upvote" onClick="voteForNews(this.id)" class="fa fa-fw fa-arrow-circle-up"></i>:&nbsp;</span><span id="news_upvote_number"><?php echo $news_no_useful; ?>&nbsp;</span>
					                <span class="entry-news-info"><i id="news_downvote" onClick="voteForNews(this.id)" class="fa fa-fw fa-arrow-circle-down"></i>:&nbsp;</span><span id="news_downvote_number"><?php echo $news_no_rubbish; ?>&nbsp;</span>
					                <span class="entry-news-info"><i id="news_spam" onClick="voteForNews(this.id)" class="fa fa-fw fa-trash-o"></i>:&nbsp;</span><span id="news_spam_number"><?php echo $news_no_spam; ?>&nbsp;&nbsp;</span>
								</h6>							
								<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium.</p>
								<div class="comment-reply">
									<a class="comment-reply-link" href="#">Reply</a> 
									<a class="comment-edit-link" href="#" title="Edit comment">(Edit)</a>
									<div class="clear"></div>
								</div><!--comment-reply-->

							</div><!--comment-body-->
						</article>
					</li>

*/
//output results from database
echo '<ul class="page_result">';
while($row = $stmt->fetch ( PDO::FETCH_ASSOC ))
{
	$comment_date = date("d/m/Y", strtotime($row['comment_date_time']));

	echo '<li class="comment depth-1">'.
	         '<article class="comment-wrap clearfix">'.
	             '<div class="comment-date clearfix">'.
	                 '<span class="enrty-meta">On:</span><span class="entry-date">&nbsp;'.$comment_date.'</span>'.
	             '</div>';
	             $stmt2 = $dbc->prepare("SELECT user_pic, user_name FROM ".DB_TABLE_USER." WHERE user_id =?");
	             $stmt2->bindValue(1, $row['comment_author_id'], PDO::PARAM_STR);
	             $stmt2->execute();
	             $user_name = "";
	             while($row2 = $stmt2->fetch (PDO::FETCH_ASSOC)){
	             	$user_name = $row2['user_name'];
	             	if(strlen($row2['user_pic'])>1){
	             		echo'<a href="user_profile.php?user_id='.$row['comment_author_id'].'"><img class="avatar" alt="" src="../../'.GW_USER_IMAGEPATH.$row2['user_pic'].'" /></a>';
	             	}
	             	else{
	             		echo '<a href="user_profile.php?user_id='.$row['comment_author_id'].'"><img class="avatar" alt="" src="placeholders/avatar/avatar.jpg" /></a>';
	             	}
	             }
	                     echo '<div class="comment-body">';
	                     echo '<a href="user_profile.php?user_id='.$row['comment_author_id'].'"><h6 class="author">'.$user_name.'&nbsp;&nbsp;';
	                    
	                 echo '</h6></a>';
	                 echo '<p>'.$row['comment_content'].'</p>';
	                 echo '<h6>';
	                 echo '<span class="entry-news-info clickable"><i id="'.$row['comment_id'].'" onClick="voteForComment(1, this.id, '."'".$row['comment_author_id']."'".')" class="glyphicon glyphicon-fire"></i>:&nbsp;</span><span id="comment_upvote_number_'.$row['comment_id'].'">'.$row['comment_no_useful'].'&nbsp;</span>';
	                 echo '<span class="entry-news-info clickable"><i id="'.$row['comment_id'].'" onClick="voteForComment(2, this.id, '."'".$row['comment_author_id']."'".')" class="fa fa-fw fa-fire-extinguisher"></i>:&nbsp;</span><span id="comment_downvote_number_'.$row['comment_id'].'">'.$row['comment_no_rubbish'].'&nbsp;</span>';
	                 echo '<span class="entry-news-info clickable"><i id="'.$row['comment_id'].'" onClick="voteForComment(3, this.id, '."'".$row['comment_author_id']."'".')" class="fa fa-fw fa-trash-o"></i>:&nbsp;</span><span id="comment_spam_number_'.$row['comment_id'].'">'.$row['comment_no_spam'].'&nbsp;&nbsp;</span>';
	                 echo '</h6>';
	                 echo '</div><!--comment-body--></article></li>';
}

$dbc=null;
?>

