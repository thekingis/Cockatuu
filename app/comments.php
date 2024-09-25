<?php
if(isset($_POST)){
	$home = $_SERVER['DOCUMENT_ROOT'];
	include $home.'/app/connect.php';
	include $home.'/app/functions.php';
	$lang = 'english';
	$lang = $_POST['lang'];
	$myId = $_POST['user'];
	$selectedComms = json_decode($_POST['selectedComms']);
	$firstLoad = $_POST['firstLoad'];
	$firstLoad = filter_var($firstLoad, FILTER_VALIDATE_BOOLEAN);
	$postId = $_POST['postID'];
	$allLoaded = false;
	$sort = getPrefSetting($myId, 'sort');
	$comDatas = array();
	$data = array();
	$blockArray = getBlocks($myId);
	if(empty($selectedComms))
		$selectedComms[] = 0;
	if(isset($_POST['sort']))
		$sort = $_POST['sort'];
	$selectedComms = implode(',', $selectedComms);
	$blockedUsers = implode(',', $blockArray);

	if($firstLoad){
		$photoUrl = getUserInfo($myId, 'photo');
		$data['photoUrl'] = $photoUrl;
	}
	
	$queryArray = array(
		"SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND id NOT IN (".$selectedComms.") AND NOT FIND_IN_SET('".$myId."', hide) ORDER BY id ASC LIMIT 15",
		"SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND id NOT IN (".$selectedComms.") AND NOT FIND_IN_SET('".$myId."', hide) ORDER BY id DESC LIMIT 15",
		"SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND id NOT IN (".$selectedComms.") AND NOT FIND_IN_SET('".$myId."', hide) ORDER BY relevance DESC LIMIT 15"
	);
	
	if(isset($_POST['commentID'])){
		$array = array();
		$commentID = $_POST['commentID'];
		$nq = mysqli_query($con, "SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND NOT FIND_IN_SET('".$myId."', hide)");
		While($obj = mysqli_fetch_array($nq)){
			$array[] = $obj['id'];
		}
		if(in_array($commentID, $array)){			
			$key = array_search($commentID, $array);
			$lastKey = count($array) - 1;
			$minKey = $key - 8;
			if($key < 8)
				$minKey = 0;
			$maxKey = $minKey + 15;
			if($maxKey > $lastKey)
				$maxKey = $lastKey;
			$miniId = $array[$minKey];
			$maxId = $array[$maxKey];
			
			$queryArray = array(
				"SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND id >= ".$miniId." AND id <= ".$maxId." AND NOT FIND_IN_SET('".$myId."', hide) ORDER BY id ASC LIMIT 15",
				"SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND id >= ".$miniId." AND id <= ".$maxId." AND NOT FIND_IN_SET('".$myId."', hide) ORDER BY id DESC LIMIT 15",
				"SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND id >= ".$miniId." AND id <= ".$maxId." AND NOT FIND_IN_SET('".$myId."', hide) ORDER BY relevance DESC LIMIT 15"
			);
		}
	}
	
	$queryStr = $queryArray[$sort];
	$query = mysqli_query($con, $queryStr);
	if(mysqli_num_rows($query) < 15)
		$allLoaded = true;
	While($sql = mysqli_fetch_array($query)){
		$comData = array();
		$comId = $sql['id'];
		$user = $sql['user'];
		$comment = $sql['comment'];
		$type = $sql['type'];
		$date = convertDate($sql['date'], true);
		$name = getUserInfo($user, 'fName').' '.getUserInfo($user, 'lName');
		$userName = getUserInfo($user, 'userName');
		$photo = getUserInfo($user, 'photo');
		$commenter = $user;
		$verified = filter_var(getUserInfo($user, 'verified'), FILTER_VALIDATE_BOOLEAN);
		$access = false;
		$likeNum = getCommentLikeNum($comId);
		$repNum = getCommentReplyNum($comId);
		$liked = checkCommentLike($myId, $comId);
		if($type == 'page'){
			$userName = '';
			$commenter = getPageInfo($user, 'user');
			$name = getPageInfo($user, 'name');
			$photo = getPageInfo($user, 'photo');
			$verified = false;
		}
		if($commenter == $myId)
			$access = true;
		if(!empty($comment)){
			$html = stripslashes($comment);
			$doc = new DOMDocument();
			$doc->loadHTML($html);
			$anchorNodes = $doc->getElementsByTagName('a');
			if($anchorNodes->length > 0){
				$spanOpen = '<span style="background-color:#F9F9F9;">';
				$bOpen = '<b>';
				$aClose = '<\/a>';
				$bClose = '<\/b>';
				$spanClose = '<\/span>';
				for ($i = 0; $i < $anchorNodes->length; $i++){
					$anchor = $anchorNodes->item($i);
					$href = $anchor->getAttribute('href');
					$hrefArr = explode('-', $href);
					if(count($hrefArr) == 3){
						$tagType = $hrefArr[0];
						$tagId = $hrefArr[1];
						if($tagType == 'Friend' && in_array($tagId, $blockArray)) {
							$aOpen = '<a href="Friend-'.$tagId.'-(.*?)">';
							$comment = preg_replace(array('/'.$spanOpen.$bOpen.$aOpen.'(.*?)'.$aClose.$bClose.$spanClose.'/i', '/'.$spanOpen.$aOpen.$bOpen.'(.*?)'.$bClose.$aClose.$spanClose.'/i',
														'/'.$bOpen.$spanOpen.$aOpen.'(.*?)'.$aClose.$spanClose.$bClose.'/i', '/'.$bOpen.$aOpen.$spanOpen.'(.*?)'.$spanClose.$aClose.$bClose.'/i',
														'/'.$aOpen.$spanOpen.$bOpen.'(.*?)'.$bClose.$spanClose.$aClose.'/i', '/'.$aOpen.$bOpen.$spanOpen.'(.*?)'.$spanClose.$bClose.$aClose.'/i'),
												array('$2', '$2', '$2', '$2', '$2', '$2'), $comment);
						}
					}
				}
			}
		}
		$comData['id'] = $comId;
		$comData['user'] = $commenter;
		$comData['name'] = $name;
		$comData['userName'] = $userName;
		$comData['photo'] = $photo;
		$comData['comment'] = $comment;
		$comData['date'] = $date;
		$comData['type'] = $type;
		$comData['verified'] = $verified;
		$comData['access'] = $access;
		$comData['likeNum'] = $likeNum;
		$comData['repNum'] = $repNum;
		$comData['liked'] = $liked;
		$comDatas[] = $comData;
	}
	
	$poster = getPostInfo($postId, 'user');
	$postType = getPostInfo($postId, 'postType');
	$perm = true;
	$vary = false;
	$pagerId = '0';
	if($postType == 'profile'){
		if($poster != $myId){
			$comPerm = getPrefSetting($poster, 'com');
			if($comPerm == 1 && !friendsOrFollowing($poster, $myId))
				$perm = false;
		}
	} else {
		$pagerId = $poster;
		$poster = getPageInfo($poster, 'user');
		if($poster == $myId)
			$vary = true;
	}
	$data['sort'] = $sort;	
	$data['comDatas'] = $comDatas;	
	$data['perm'] = $perm;
	$data['vary'] = $vary;
	$data['pagerId'] = $pagerId;
	$data['allLoaded'] = $allLoaded;
	echo json_encode($data);
}
?>