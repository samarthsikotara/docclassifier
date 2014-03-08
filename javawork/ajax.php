<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Swaroop
 * Date: 12/6/11
 * Time: 8:22 PM
 * To change this template use File | Settings | File Templates.
 */
include_once('Smarty.class.php');
$main_smarty = new Smarty;
include_once('config.php');
include_once(mnminclude.'html1.php');
include_once(mnminclude.'photo.php');
include_once(mnminclude.'smartyvariables.php');
include_once(mnminclude."comment.php");
include_once(mnminclude."link.php");
include_once(mnminclude."group.php");
include_once(mnminclude."groups.php");
include_once(mnminclude."blog.php");


   $work=$_REQUEST['work'];

if($work==1){
    // getting the list of the interest list
    $data=mysql_real_escape_string(strtolower($_POST['data']));
    if(!isset($_POST['limit'])) $_POST['limit']=5;

    if(!is_numeric($_POST['limit'])) $_POST['limit']=5;

    if(trim($data)==""){
     echo "";
    }
    global $db;
    // echo "<table id='g_suggestion' class='suggestions' border='0' cellpadding='1' cellspacing='0'>";
    $query="SELECT `interest_id`, `interest_name`, `interest_short_desc` FROM ".table_interests." WHERE interest_meta LIKE '%".$data."%' AND interest_status=1 AND `interest_privacy`='public' LIMIT ".$_POST['limit'];
    //echo $query;
    $result=$db->get_results($query);
	//print_r($result);
    if(!is_null($result)){
   // print_r($result);
    /*
    foreach($result as $group){
       echo "<tr onClick=selectInterest(".$group->group_id.")><td><img src='images/interest/".$group->group_id.".jpg'></td><td>".substr($group->group_name,0, strpos($group->group_name, $data))."<b>".$data."</b>".substr($group->group_name, (strpos($group->group_name, $data)+strlen($data)))."<br><span class='description'>".$group->group_description."</span></td>";
    }


    echo "</table>";

     */ $i=0;
        foreach($result as $list){
             $array[$i]['id']=$list->interest_id;
             $array[$i]['name']=$list->interest_name;
             $array[$i]['short_desc']=$list->interest_short_desc;
             $array[$i]['image']=getInterestImage($list->interest_id, '35');
            $i++;
        }

        echo json_encode($array);
    }
    else echo "false";
}
if($work==2){
    //getting the list of friends
    include_once(mnminclude.'friend.php');

    $data=mysql_real_escape_string(strtolower($_POST['data']));

    if(trim($data)==""){
     echo "";
   }
    if(isset($_POST['limit']) && is_numeric($_POST['limit']))$limit=$_POST['limit'];
    else $limit=5;
    global $current_user;

    $friend= new Friend();
    $result=$friend->get_foll_frnd_list($current_user->user_id, $limit , $data);
    //print_r($result);

    if(!is_null($result)){
        $i=0;
        foreach($result as $list){
            //if($i==5) break;
            $array[$i]['id']=$list['user_id'];
             $array[$i]['name']=$list['user_names'];
             $array[$i]['short_desc']="";
             $array[$i]['image']=get_avatar('50', "", "", "", $list['user_id']);
             $i++;
        }
        echo json_encode($array);
    }
    else echo "false";
}
if($work==3){

    include_once(mnminclude.'interest_member.php');
    include_once(mnminclude.'common.php');
    $data=mysql_real_escape_string($_POST['interestId']);
    $i=0;
    $userGlobal=interestMember($data);
    foreach($userGlobal as $item){

        $userList['global'][$i]['id']=$item->user_id;
        $i++;
    }
    $userFriend=getFriendListByInterest($data);
    //print_r($user);
    $i=0;
    foreach($userFriend as $item){

        $userList['friend'][$i]['id']=$item->user_id;
        $i++;
    }

    echo json_encode($userList);
}
if($work==4){
    //getting the list of plans by interest
    include_once(mnminclude.'plan_fetch.php');
    include_once('genhtml.php');
    $data=mysql_real_escape_string($_POST['interestId']);
    $i=0;
    $plans=getPlansByInterest($data, "abc");
    //print_r($plans);
    $data="<ul class='plan-list-block'>";
    foreach($plans as $item){
        $data= $data.createPlanShort($item);
    }
   // print_r($array);
    $data=$data."</ul>";
    echo $data;
}
if($work==5){
    //inserting the comment for a plan
    include_once(mnminclude."comment.php");
    global $db, $current_user, $main_smarty;
    $comment = new Comment;
    $cancontinue = false;
    $comment->content=sanitize($_POST['comment_content'], 4);
    //To Do: To check if the user can comment on this post.

    if($current_user->authenticated && sanitize($_POST['user_id'], 3) == $current_user->user_id &&	sanitize($_POST['randkey'], 3) > 0)
	{
        if(sanitize($_POST['comment_content'], 4) != '')
			// this is a normal new comment
			$cancontinue = true;
	}
    if($cancontinue == true)
	{
		$comment->link=sanitize($_POST['link_id'], 3);
		$comment->randkey=rand(10000000, 100000000000);
		$comment->author=$current_user->user_id;
		$comment->status = "published";
        try{
		    if($comment->store()) echo "success";
        }catch (Exception $e){
            echo $e->getMessage();
        }
	}
    else if(!$current_user->authenticated) die('You must be logged in to comment.');
    else die('Something went wrong.');
}
if($work==6){
    // liking the plan
    global $db, $current_user, $main_smarty;
    if(is_numeric($_POST['link_id'])){
    include_once mnminclude."votes.php";
    // need to give ip
    $vote=new Vote();
    $vote->user=$current_user->user_id;
    if($current_user->user_id==0){ die('You must be logged in to like the plan.');}
    $vote->value=1;
    $vote->type='links';
    $vote->link=$_POST['link_id'];
    $status=$vote->insert();
    if($status==true)echo "success";
        else echo $status;
    }
    else echo "failed";

}
if($work==7){
    //
    global $db, $current_user, $main_smarty;
    if(is_numeric($_POST['link_id'])){
    include_once mnminclude."votes.php";
    //echo "sss";
    // need to give ip
    $vote=new Vote();
    $vote->user=$current_user->user_id;
    if($current_user->user_id==0){ die('You must be logged in to like the plan.');}
    $vote->value=1;
    $vote->type='links';
    $vote->link=$_POST['link_id'];
    $status=$vote->remove();
    if($status==true)echo "success";
        else echo $status;
    }
    else echo "failed";

}
if($work==8){
    // adding the interest
    global $current_user;
    include_once mnminclude."interest_member.php";
    //echo $_POST['key'];
    $key=$_POST['key'];
    ///echo $key;
    $result=addinterest($key, "public");
    if($result===true) echo "success";
    else echo $result;
}
if($work==9){

    include_once mnminclude."plan_members.php";


    //echo ".....".joinPlan($_POST['plan_id'], 'public').".......";
	try{
        if(joinPlan($_POST['plan_id'], 'public')) echo "success";
        else echo "Sorry,Something went wrong. Failed to join the Plan.";
    }catch (Exception $e){
        echo $e->getMessage();
    }
}
if($work==10){
	include_once mnminclude."plan_members.php";
    try{
        if(unjoinPlan($_POST['plan_id'], 'public')) echo "success";
        else echo "Sorry,Something went wrong. Failed to join the Plan.";
    }catch (Exception $e){
        echo $e->getMessage();
    }
}
if($work==11){
    //creating a new scribble
    global $db, $current_user;
    //die("....".$the_template);
    if(!is_numeric($_POST['cat_id'])) die();
    include_once mnminclude."scribble.php";
    include_once mnminclude."interest_member.php";
    $scribble =new Scribble;
    $scribble->id=0;
    $scribble->category=$_POST['cat_id'];
    $scribble->content=sanitize($_POST['content'], 3);
    $scribble->status="published";
    $scribble->privacy="public";
    $scribble->groupId=$_POST['group_id'];
    $scribble->author=$current_user->user_id;
    $status=$scribble->store();
    if($status===true){
        $return[0]="success";
        $return[1]=$scribble->print_summary('summary', true, 'scribble_summary.tpl');
        $return[2]=isInterested($scribble->category);
        if($return[2]===false) addinterest($scribble->category, 'public');
    }
    elseif($status===false){$return[0]="failed";$return[1]['error']="";}
    else{$return[0]="failed";$return[1]['error']=$status;}
    echo json_encode($return);
}
if($work==12){
    //adding the scribble comment
    include_once(mnminclude."scribble.php");
    include_once(mnminclude."sc_comment.php");
    global $db, $current_user, $main_smarty;
    $sccomment = new Sc_comment;

    $cancontinue = false;
    $sccomment->content=sanitize($_POST['comment_content'], 4);
    //To Do: To check if the user can comment on this post.

    if($current_user->authenticated && sanitize($_POST['user_id'], 3) == $current_user->user_id)
	{
        if(sanitize($_POST['comment_content'], 4) != '')
			// this is a normal new comment
			$cancontinue = true;
	}
    //echo $cancontinue;
    if($cancontinue == true)
	{

		$sccomment->scribble=sanitize($_POST['scribble_id'], 3);
		$sccomment->randkey=rand(10000000, 100000000000);
		$sccomment->author=$current_user->user_id;
		$sccomment->status = "published";
		if($sccomment->store()) echo "success";
	}
}
if($work==13){
    // bookmarking the plan
    global $db; $current_user;
    include_once mnminclude."bookmark.php";
    try{
        if(addBookmark($db->escape($_POST['id']), $db->escape($_POST['type']))) echo "success";
    }catch (Exception $e){
        echo $e->getMessage();
    }
}
if($work==14){
    // removing the bookmark
    global $db; $current_user;
    include_once mnminclude."bookmark.php";
    try{
        if(removeBookmark($db->escape($_POST['id']), $db->escape($_POST['type']))) echo "success";
    }catch (Exception $e){
        echo $e->getMessage();
    }
}
if($work==15){
    // adding the interest
    global $current_user;
    include_once mnminclude."interest_member.php";
    //echo $_POST['key'];
    $key=$_POST['key'];
    ///echo $key;
    if(removeinterest($key, "public")){
        echo "success";
    }
}
if($work==16){
    $valid_formats = array("jpg", "png", "gif", "bmp","jpeg");
    $path="images/temporary/";
    if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){
        $name = $_FILES['profile-photo']['name'];
        $size = $_FILES['profile-photo']['size'];
        if(strlen($name))
        {

            if($size<(2*1024*1024)) // Image size max 2 MB
            {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $random = rand(1000000, 10000000);
            $actual_image_name = $random.".".strtolower($ext);
            $tmp = $_FILES['profile-photo']['tmp_name'];
                //echo $path.$actual_image_name;
            if(move_uploaded_file($tmp, $path.$actual_image_name))
            {
                echo "<img class='profile-photo-preview' id='reg-ajax-profile-photo' src='images/temporary/".$actual_image_name."' class='preview'><span class='display-none' id='image-uploaded-value'>".$random."</span><span class='display-none' id='image-uploaded-ext'>".strtolower($ext)."</span>";
            }
            else
                echo "Failed";
            }
            else
            echo "Image file size max 5 MB";
            }
        }
        else
        echo "Please select image..!";
        exit;
    }
if($work==17){
        $page = $_REQUEST['page'];
        $data= urldecode($_REQUEST['data']);
        header('Location: '.my_base_url.my_pligg_base.'/index.php?data=plans&page='.$page."&".$data);
}
if($work==18){
    // liking the scribble
    global $db, $current_user, $main_smarty;
    if(!is_int($_POST['link_id'])){
    include_once mnminclude."votes.php";
    // need to give ip
    $vote=new Vote();
    $vote->user=$current_user->user_id;
    if($current_user->user_id==0){ die('You must be logged in to like the scribble.');}
    $vote->value=1;
    $vote->type='scribble';
    $vote->link=$_POST['link_id'];
    $vote->insert();
        echo "success";
    }
    else echo "failed";
}
if($work==19){
    //
    global $db, $current_user, $main_smarty;
    if(!is_int($_POST['link_id'])){
    include_once mnminclude."votes.php";
    // need to give ip
    $vote=new Vote();
    $vote->user=$current_user->user_id;
    if($current_user->user_id==0){ die('You must be logged in to like the scribble.');}
    $vote->value=1;
    $vote->type='scribble';
    $vote->link=$_POST['link_id'];
    $vote->remove();
        echo "success";
    }
    else echo "failed";
}
if($work==20){
    include_once(mnminclude."search.php");
    $search=new Search();
    $search->offset = (get_current_page()-1)*$page_size;

    // pagesize set in the admin panel
    $search->pagesize = $page_size;

    // since this is index, we only want to view "published" stories
    $search->filterToStatus = "published";
    $interest[0]=sanitize($_REQUEST['interest'], 3);;
    $search->interest=$interest;

    $search->doSearch();
    $linksum_count = $search->countsql;
    $linksum_sql = $search->sql;

    $fetch_link_summary = false;
    $summary_type="short";
    include_once('./libs/link_summary.php'); // this is the code that show the links / stories
}

if($work==21){
    //removing the location
    global $current_user;
    include_once mnminclude."location.php";
    //echo $_POST['key'];
    $key=$_POST['key'];
    ///echo $key;
    if(deleteLocation($key)){
        echo "success";
    }
}

if($work==23){
    include_once mnminclude."user.php";
    $user= new User();
    $user->id=$current_user->user_id;
    $user->uploadAvatar($_FILES['image_file']);
        echo "<img src='".get_avatar('original', "", "", "", $user->id)."' alt='avatar'>";
}
if($work==24){

    include_once mnminclude."notify.php";
    if(!$current_user->authenticated){
        $return[0]='falied';
        $return[1]['error']='Please Login in';
    }else{
        $notify= new Notify();

        $return[0]="success";
        $return[1]=$notify->generateNotificationHtml(15);
        $notify->viewed_notification();
    }
    echo json_encode($return);
}

if($work==25)
{
    include_once mnminclude."notify.php";
    $notify = new Notify();
    extract($_REQUEST);
    $ret=$notify->encodejson($timecount);
    echo json_encode($ret);


}
if($work==27){
		
        $page = $_REQUEST['page'];
		$data= urldecode($_REQUEST['data']);
		header('Location: '.my_base_url.my_pligg_base.'/index.php?data=scribble'."&".$data);
		    


}
if($work==28){
		
     include_once mnminclude."friends.php";
	 $frnd=new Friend();
	 //print_r($_REQUEST);
     $_REQUEST['action'];
		if(($_REQUEST['id']!="")&& is_numeric($_REQUEST['id']))
		 {
            if(isset($_REQUEST['action'])){
                if($frnd->send_req($_REQUEST['id'],$_REQUEST['action'])){
                    echo 'success';
                }
                else
				{ echo "failed";}
            }
		 }
		 else
		 {
			echo "Can not process the request this time";
		 }
		    


}

if($work==30)
{
		// delete friend 
		include_once mnminclude."friend.php";
		$frnd=new Friend();
		
		if(($_REQUEST['id']!="")&&(is_numeric($_REQUEST['id'])))
		 {
		 echo $frnd->delete_friend($_REQUEST['id']);
		 }
		 else
		 {
			echo "false";
		 }
		    
}
if($work==31)
{
		// delete followerss
		include_once mnminclude."friend.php";
		$frnd=new Friend();
		
		if(($_REQUEST['id']!="")&&(is_numeric($_REQUEST['id'])))
		 {
		 echo $frnd->delete_follower($_REQUEST['id']);
		 }
		 else
		 {
			echo "false";
		 }
		    
}

if($work==32)
{
		// delete following
		include_once mnminclude."friend.php";
		$frnd=new Friend();
		
		if(($_REQUEST['id']!="")&&(is_numeric($_REQUEST['id'])))
		 {
		 echo $frnd->delete_following($_REQUEST['id']);
		 }
		 else
		 {
			echo "flase";
		 }
		    
}
if($work==33){
    include_once mnminclude."interest.php";
    if(!is_numeric($_POST['interst_id'])) $return [0]= "failed";
    $result= getPlanType($_POST['interest_id']);
    //print_r($result);
    if($result){
        $return[0]= "success";
        $return[1]= $result;
    }
    else $return[0]= "failed";
    echo json_encode($return);
}


if($work==34){
    include_once mnminclude."interest.php";
    if(!is_numeric($_POST['interst_id'])) $return [0]= "failed";
    $result= getPlanTitle($_POST['interest_id'], sanitize($_POST['plan_type'], 3));
    //echo $result;
    if($result){
        $return[0]= "success";
        $return[1]= $result;
    }
    else $return[0]= "failed";
    echo json_encode($return);
}
if($work==35){
		
        
		
     include_once mnminclude."friend.php";
	 $frnd=new Friend();
     $_REQUEST['action'];
		if(($_REQUEST['id']!="")&& is_numeric($_REQUEST['id']))
		 {
            if(isset($_REQUEST['action'])){
                if($frnd->get_friend_status_result($_REQUEST['id'])){
                    echo 'success';
                }
                else echo "failed";
            }
		 }
		 else
		 {
			echo "Can not process the request this time";
		 }
		    


}
if($work==36){	
     include_once mnminclude."plan_members.php";
	if(($_REQUEST['plan_id']!="")&& is_numeric($_REQUEST['plan_id']))
		 {
                 if(remove_members($_REQUEST['plan_id'],$_REQUEST['user_id'])){
                   echo 'success';
                }
                
          }
		 else
		 {
			echo  "failure";
		 }
		    


}

if($work==37){
include_once mnminclude."comment.php";
    $comment_id =   $_REQUEST['comment_id'];
    if(is_numeric($comment_id)){
        $commentobj=new Comment();
        $commentobj->id= $comment_id;
        if($commentobj->delete_comment()){
                   echo 'success';
        }
                
    }
	else{
	    echo  "failure";
	}
}
if($work==38){
    $text= strip_tags(addslashes(urldecode($_POST['text'])));
    global $db, $current_user;
    if($current_user->user_id==0) echo "failed";
    $sql="UPDATE ".table_users." SET `user_desc`='".$text."' WHERE `user_id`=".$current_user->user_id." AND `user_enabled`=1";
    if($db->query($sql)){
        echo "success";
    }else echo "Failed to update your status";
}

if($work==39){
 include_once(mnminclude.'report_abuse.php');
$abuseobj= new abuse();
	echo $abuseobj->abuse_call($_REQUEST['plan_id'],"plan","abuse");
}

if($work==40){
    include_once(mnminclude."bookmark.php");
    include_once(mnminclude."location.php");
    include_once(mnminclude.'plan_members.php');
    $user_id=explode(",",$_REQUEST['user_id']);
    $plan_id=$_REQUEST['plan_id'];
    if(!is_numeric($plan_id)) die();
    for($i=0;$i<count($user_id);$i++){
        if(is_numeric($user_id[$i])){
            $res=invite($user_id[$i],$plan_id, false);

        }
        else if(check_email($user_id[$i])){
            $res=invite_guest($user_id[$i],$plan_id);
        }
    }
return;

}
if($work==41){
  // getting the list of the interest list
    $data=mysql_real_escape_string(strtolower($_POST['data']));
    if(!isset($_POST['limit'])) $_POST['limit']=5;

    if(!is_numeric($_POST['limit'])) $_POST['limit']=5;

    if(trim($data)==""){
         echo "";
    }
    global $db;
    // echo "<table id='g_suggestion' class='suggestions' border='0' cellpadding='1' cellspacing='0'>";
    $query="SELECT `user_id`, `user_names`, `user_desc` FROM ".table_users." WHERE user_names LIKE '%".$data."%'  LIMIT ".$_POST['limit'];
    //echo $query;
    $result=$db->get_results($query);
	//print_r($result);
    if(!is_null($result)){

    $i=0;
        foreach($result as $list){
             $array[$i]['id']=$list->user_id;
             $array[$i]['name']=$list->user_names;
             $array[$i]['short_desc']="";
             $array[$i]['image']=get_avatar(50, "","","", $list->user_id);
            $i++;
        }

        echo json_encode($array);
    }
    else echo "false";

}
if($work==42){
    $plan_id=$_REQUEST['planId'];
    if(!is_numeric($plan_id)) return false;

    $files=$_FILES['plan-photo'];
    $count=count($_FILES['plan-photo']['name']);
    $status=false;
    //die();
    //print_r($_FILES);
    //echo $count;
    for($i=0; $i<$count; $i++){
        $photo=new Photo();
        $array['tmp_name']=$files['tmp_name'][$i];
        $array['name']=$files['name'][$i];
        $array['error']=$files['error'][$i];
        $array['type']=$files['type'][$i];
        $array['size']=$files['size'][$i];
        $photo->photo=$array;
        $photo->typeName='plan';
        $photo->typeId=$plan_id;
        if($photo->uploadPhoto()){
            $status=true;
            $return[$i][0]="success";
            $return[$i][1]=my_pligg_base."/image.php?photoId=".$photo->photoid;
        }else{
            $return[$i][0]="failed";
            $return[$i][0]['error']=$photo->error;
        }
        $photo="";
    }
    if($status){
        include_once mnminclude."notification.php";
        addPlanPhotoNotification($plan_id);
    }
    echo json_encode($return);

}
if($work==43){
    echo "success";
}

if($work==44){
    if(!$current_user->authenticated) die("failed");
    if(isset($_REQUEST['mail']) && is_array($_REQUEST['mail'])){
        $mail=$_REQUEST['mail'];
        $sql="INSERT INTO ".table_google_mail." (`contact_id`, `user_id`, `status`) VALUES ";
        $count=0;
        foreach($mail as $item){
            if(is_numeric($item)){
                $count++;
                $sql.="(".$item.", ".$current_user->user_id.", 0) ,";
            }
        }
        if($count>0){
            $sql=substr($sql, 0, -2);
            echo $sql;
            if($db->query($sql)) echo "success";
        }else echo "success";
    }
}
if($work==45){
    //inserting the review for a plan
    include_once(mnminclude."review.php");
    global $db, $current_user, $main_smarty;
    $review = new Review();

    $cancontinue = false;

    $review->review_descp=sanitize($_POST['review_content'], 4);
    if(is_numeric($_POST['review_rating'])){
        $review->review_rating=$_POST['review_rating'];
    }else return 'rating is ambguished';
    //To Do: To check if the user can comment on this post.

    if($current_user->authenticated && sanitize($_POST['user_id'], 3) == $current_user->user_id)
    {
        if(sanitize($_POST['review_content'], 4) != '')
            // this is a normal new comment
            $cancontinue = true;
    }
    if($cancontinue == true)
    {
        $review->review_plan_id=sanitize($_POST['link_id'], 3);
        $review->review_user_id=$current_user->user_id;
        $review->review_status = 1;
        $review->submit_review();

    }
    else if(!$current_user->authenticated) die('You must be logged in to review.');
    else die('Something went wrong.');
}

if($work==46){
    //inserting the review for a plan
    include_once(mnminclude."friendsuggestion.php");
    global $db, $current_user, $main_smarty;
    $friendsugg = new friendsuggestion();

    $user_id = $_POST['user_id'];
    $interest_id = $_POST['interest_id'];
    //To Do: To check if the user can comment on this post.

    if(!is_numeric($user_id) || !is_numeric($interest_id)) $result[0]="failed";
    else{
        $array=$friendsugg->allweigtagewithname($user_id,$interest_id,'');
        if(count($array)<1) $result[0]="failed";
        else{
            $result[0]="success";
            $result[1]=$array;
        }
    }
    echo json_encode($result);
}
if($work==47){
    include_once mnminclude."group.php";
    //echo ".....".joinPlan($_POST['plan_id'], 'public').".......";
    try{
        $status=joinGroup($_POST['group_id'],$_POST['user_id']);
        if($status) {$result[0]="success";$result[1]=$status;}
        else{$result[0]="failed";$result[1]="Sorry,Something went wrong. Failed to join the Group.";
        }


    }
    catch(Exception $e){
        $result[0]="failed";
        $result[1]="Something went wrong";
        error_log($e);
    }

    echo json_encode($result);

}
if($work==48){
    include_once mnminclude."group.php";
    //echo ".....".joinPlan($_POST['plan_id'], 'public').".......";
    try{
        $status=unjoinGroup($_POST['group_id'],$_POST['user_id']);
        if($status) {$result[0]="success";$result[1]=true;}
        else{$result[0]="failed";$result[1]="Sorry,Something went wrong. Failed to join the Group.";
        }


    }
    catch(Exception $e){
        $result[0]="failed";
        $result[1]="Something went wrong";
        error_log($e);
    }

    echo json_encode($result);
}
if($work==49){
    $user_id=explode(",",$_REQUEST['user_id']);
    $group_id=$_REQUEST['group_id'];
    //print_r($user_id);
    if(!is_numeric($group_id)) die('failed');
    foreach($user_id as $user){
        //echo $user;
        if(is_numeric($user) || check_email($user)){
            try{
                //echo "nane:".$user." done";
                joinGroup($group_id,$user);
                //echo "checked";
            }
            catch(Exception $e){
                //echo $e;
                error_log($e);
            }
        }
    }
    echo "success";
}
if($work==50){
    include_once mnminclude."scribble.php";
    $scribble_id=$_REQUEST['scribble_id'];
    if(!is_numeric($scribble_id)) echo "failed";
    $scribble=new Scribble();
    $scribble->id=$scribble_id;
    if($scribble->delete()) echo "success";
    else echo "failed";
}
if($work==51){
    $group_id = $_REQUEST['group_id'];
    if(!is_numeric($group_id)) return false;
    //$interest_id = $_REQUEST['interest_id'];
    $interest_id = explode(",",$_REQUEST['interest_id']);

    if(addgroupInterest($group_id,$interest_id)) echo "success";
    else echo "there something wrong";

}
if($work==52){
    $link_id = $_REQUEST['plan_id'];
    $link_update = $_REQUEST['plan_update'];
    $link_notify = $_REQUEST['plan_notify'];
    if(!is_numeric($link_id)) return false;
    //$interest_id = $_REQUEST['interest_id'];
    $plan=new Link();
    $plan->id=$link_id;
    if($link_notify==1) $link_notify=true; else $link_notify=false;
    if($plan->updateInfo($link_update,$link_notify)) echo  "success";
    else echo "failed";
}
if($work==53){
    $files=$_FILES['bawaras-images'];
    //print_r($_FILES);

    if(!$current_user->authenticated) return false;
    $count=count($_FILES['bawaras-images']['name']);
    //echo $count;
    //print_r($_FILES);

    for($i=0; $i<$count; $i++){
        $photo=new Photo();
        $array['tmp_name']=$files['tmp_name'][$i];
        $array['name']=$files['name'][$i];
        $array['error']=$files['error'][$i];
        $array['type']=$files['type'][$i];
        $array['size']=$files['size'][$i];
        $photo->photo=$array;
        //print_r($array);

        $photo->typeName='bawaras';
        $photo->typeId=$current_user->user_id;
        $photo->userId=$current_user->user_id;
        //print_r($photo->uploadPhoto());

        if($photo->uploadPhoto()){
            $return[$i][0]="success";
            $return[$i][1]=my_pligg_base."/image.php?photoId=".$photo->photoid;
            $return[$i][2]=$photo->photoid;
            $return[$i][3]=my_base_url.my_pligg_base."/images/uploads/".$photo->photoName;
        }else{

            $return[$i][0]="failed";
            $return[$i][1]=$photo->error;
        }
        $photo="";
    }
    //print_r($return);
    echo json_encode($return);
}
if($work==54){
    // getting the list of the interest list
    $data=mysql_real_escape_string(strtolower($_POST['data']));
    if(!isset($_POST['limit'])) $_POST['limit']=5;

    if(!is_numeric($_POST['limit'])) $_POST['limit']=5;

    if(trim($data)==""){
        echo "";
    }
    global $db;
    // echo "<table id='g_suggestion' class='suggestions' border='0' cellpadding='1' cellspacing='0'>";
    $query="SELECT `location_name`,`location_url_title` ,`location_id`, location_lat1 AS lat1, location_lat2 AS lat2, location_lon1 AS lon1, location_lon2 AS lon2  FROM ".table_locations_mumbai." WHERE location_name LIKE '%".$data."%' LIMIT ".$_POST['limit'];
    //echo $query;
    $result=$db->get_results($query);
    //print_r($result);
    if(!is_null($result)){
        // print_r($result);
        /*
        foreach($result as $group){
           echo "<tr onClick=selectInterest(".$group->group_id.")><td><img src='images/interest/".$group->group_id.".jpg'></td><td>".substr($group->group_name,0, strpos($group->group_name, $data))."<b>".$data."</b>".substr($group->group_name, (strpos($group->group_name, $data)+strlen($data)))."<br><span class='description'>".$group->group_description."</span></td>";
        }


        echo "</table>";

         */ $i=0;
        foreach($result as $list){
            $array[$i]['id']=$list->location_id;
            $array[$i]['name']=$list->location_name;
            $array[$i]['short_desc']="";
            $array[$i]['image']=$base_url.$my_pligg_base."/images/location_stamp.png";
            $i++;
        }

        echo json_encode($array);
    }
    else echo "false";
}

if($work==55){

    $photoId = $_REQUEST['photoId'];
    if(!$current_user->authenticated){  return false; }
    if(!is_numeric($photoId)){ return false; }

    $photo=new Photo();
    $photo->photoid=sanitize($photoId,3);
    $photo->readPhotoDetails();

    if($photo->deletePhoto()==1){
        $return[0]="success";
    }else{
        $return[0]="failed";
        $return[1]=$photo->error;
    }

    echo json_encode($return);
}
if($work==56){
    if ($current_user->user_id > 0 && $current_user->authenticated){
        $user_id=$current_user->user_id;
    }else{
        return false;
    }
    $sql = "SELECT * FROM `shbawaras` WHERE user_id = ".$user_id."";
    $results = $db->get_row($sql);


    $occupation=sanitize(strip_tags($_POST['reg_occupation'],3));
    $place=sanitize(strip_tags($_POST['reg_place']),3);
    $field1=sanitize(strip_tags($_POST['reg_desc1']),3);
    $field2=sanitize(strip_tags($_POST['reg_desc2']),3);
    $field3=sanitize(strip_tags($_POST['reg_desc3']),3);
    $field4=sanitize(strip_tags($_POST['reg_desc4']),3);

    if(!is_null($results->user_id)){
       $sql="UPDATE `shbawaras` SET occupation='".$occupation."', workplace='".$place."' , bawara_field1='".$field1."' , bawara_field2='".$field2."', bawara_field3='".$field3."', bawara_field4='".$field4."' WHERE user_id = ".$user_id."";
    }else{
        $sql="INSERT INTO `shbawaras` (user_id,occupation,workplace,bawara_field1,bawara_field2,bawara_field3,bawara_field4) VALUES (".$user_id.",'".$occupation."','".$place."','".$field1."','".$field2."','".$field3."','".$field4."')";
    }
    //echo $sql;
    if($db->query($sql)){
        //header('Location: '.$my_base_url.$my_pligg_base.'/bawaras_sample.php');
        $return = "true";
    }else{
        if(!is_null($results->user_id)){
            $return = "true";
        }else{
            $return = "false";
        }
    }

    echo $return;
}
if($work==60){
    global $db, $current_user;
    $unique_id = sanitize($_GET['id'], 3);
    $status = $_GET['status'];
    if( $unique_id != '' && $status == '200' && $current_user->authenticated ){
        $sql="INSERT IGNORE INTO ".table_videos." (`user_id`, `video_id`, `url`, `date`, `active`) VALUES(".$current_user->user_id.", '".$unique_id."', 'http://youtube.com/?v=".$unique_id."', NOW(), 1)";
        $db->query($sql);
        $return[0]="success";
        $return[1]=my_base_url.my_pligg_base."/images/video_thumn.png";
        $return[2]="http://youtube.com/watch?v=".$unique_id;
    }else{
        $return[0]="failed";
        $return[1]="Failed to upload. Please try again later.";
    }
echo json_encode($return);


}

if($work==61){
    global $db, $current_user;
	$blog_content=$_REQUEST['blog_update'];
	$blogs = new blog();
	//print_r($blogs->insertBlog());
	if($blogs->insertBlog($blog_content)) echo "success";
	else 
	echo "failed";

}
if($work==62){

	$id=$_REQUEST['id'];
	//echo $id;
	global $db, $current_user;
	$sql = "SELECT * FROM `shgroup_approve` WHERE id = ".$id."";
    $results = $db->get_row($sql);
	
	$name=sanitize(strip_tags($_POST['name']));
	$group_url=makeGroupUrlFriendly($name);
	$sql3="SELECT user_id FROM `shgroup_approve` WHERE id = ".$id." ";
	$val = $db->get_var($sql3);
	$desc=sanitize(strip_tags($_POST['desc']));
	$remove_notif=sanitize(strip_tags($_POST['remove_notif']));
	$location=sanitize(strip_tags($_POST['details']));
	$location_id=sanitize(strip_tags($_POST['location_id']));
	$location_name=sanitize(strip_tags($_POST['user_loc']));
	$loc_lat=sanitize(strip_tags($_POST['user_loc1']));
	$loc_lng=sanitize(strip_tags($_POST['user_loc2']));
	$status = sanitize(strip_tags($_POST['status']));
	
	
	if(!is_null($results->id)){
		
		$sql1=" REPLACE INTO `shgroups`(group_creator,group_status,group_date,group_name,group_description,group_privacy,group_field5,group_field6,group_notify_email)VALUES(".$val.",'disable',NOW(),'".$name."','".$desc."','public',".$id.",'".$group_url."',0) ";
		$sql4="UPDATE `shgroup_approve` SET status = ".$status." WHERE id = ".$id." ";
		echo $sql4;
		$db->query($sql4);
		echo $sql1;
	}
	
	/*
	$url="http://maps.googleapis.com/maps/api/geocode/json?address=."$location_name".&sensor=true";
	$result = file_get_contents($url);
	$final_result=json_decode($result, true);
	/*
	$map_loc=array();
	$i=0;
	foreach($final_result['results']['location'] as $name){
		
		print_r($name);
		
	}
	*/
	if($db->query($sql1)){
        
        echo $return = "true";
	}else{
	echo $return = "false";}
	
}

if($work==63){

	$id=$_REQUEST['id'];
	echo $id;
	global $db, $current_user;
	
		$sql="DELETE FROM `shgroup_approve` WHERE id=".$id." ";
		echo $sql;
	if($db->query($sql)){
        
        echo $return = "true";
	}else{
	echo $return = "false";}
	
}
if($work==64){
	
	if ($current_user->user_id > 0 && $current_user->authenticated){
        $user_id=$current_user->user_id;
    }else{
        return false;
    }
	
	$name = sanitize(strip_tags($_POST['name']));
	$short_desc = sanitize(strip_tags($_POST['short_desc']));
	$desc = sanitize(strip_tags($_POST['desc']));
	$meta = sanitize(strip_tags($_POST['meta']));
	
	if(!is_null($user_id)){
	
		global $db, $current_user;
		$sql="INSERT INTO `shinterests`(interest_name,interest_short_desc,interest_desc,interest_meta,interest_creator_id,interest_status,interest_privacy,interest_updated) VALUES ('".$name."','".$short_desc."','".$desc."','".$meta."',1,0,'public',NOW())";
		echo $sql;
		

	}
	if($db->query($sql)){
		return true;
	}else{
		return false;
	}
	
}

if($work==65){
	
	if ($current_user->user_id > 0 && $current_user->authenticated){
        $user_id=$current_user->user_id;
    }else{
        return false;
    }
	
	$name = sanitize(strip_tags($_POST['name']));
	$lat1 = sanitize(strip_tags($_POST['lat1']));
	$lng1 = sanitize(strip_tags($_POST['lng1']));
	$lat2 = sanitize(strip_tags($_POST['lat2']));
	$lng2 = sanitize(strip_tags($_POST['lng2']));
	
	if(!is_null($user_id)){
	
		global $db, $current_user;
		$sql="INSERT INTO `shlocations`(location_name,location_lat1,location_lat2,location_lon1,location_lon2) VALUES ('".$name."',".$lat1.",".$lat2.",".$lng1.",".$lng2.")";
		echo $sql;
		

	}
	if($db->query($sql)){
		return true;
	}else{
		return false;
	}
	
}

?>
 
