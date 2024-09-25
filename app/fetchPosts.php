<?php
if(isset($_POST)){
	$home = $_SERVER['DOCUMENT_ROOT'];
	include $home.'/app/connect.php';
	include $home.'/app/functions.php';
	$lang = 'english';
	$lang = $_POST['lang'];
	$myId = $_POST['user'];
	$firstLoad = $_POST['firstLoad'];
	$selectedPosts = json_decode($_POST['selectedPosts']);
	$allLoaded = false;
	$firstLoad = filter_var($firstLoad, FILTER_VALIDATE_BOOLEAN);
	if(empty($selectedPosts))
		$selectedPosts[] = 0;
	$selectedPosts = implode(',', $selectedPosts);
	$frndArr = array($myId);
	$fllwingArr = array(0);
	$pgLkArr = array(0);
	$pgFlArr = array(0);
	$hasPage = hasPage($myId);
	$pageArray = array();
	$userPages = array();
	$postDatas = array();
	$myPages = array();
	$searchData = array();
	$data = array();
	$seePstFrmFllwrs = getPrefSetting($myId, 'pref');
	$blockArray = getBlocks($myId);
	$hiddenPosts = hiddenPosts($myId);
	$blockedUsers = implode(',', $blockArray);
	
	if($firstLoad){
		$photoUrl = getUserInfo($myId, 'photo');
		$verified = getUserInfo($myId, 'verified');
		$verified = filter_var($verified, FILTER_VALIDATE_BOOLEAN);
		$username = getUserInfo($myId, 'userName');
		$name = getUserInfo($myId, 'fName').' '.getUserInfo($myId, 'lName');
		$msgQuery = mysqli_query($con, "SELECT * FROM allmsgs WHERE userTo = '".$myId."' AND popped = '0' GROUP BY userFrom");
		$msgCount = mysqli_num_rows($msgQuery);
		$noteQuery = mysqli_query($con, "SELECT * FROM notifications WHERE userTo = '".$myId."' AND popped = 'false'");
		$noteCount = mysqli_num_rows($noteQuery);
		$noteQuery = mysqli_query($con, "SELECT * FROM friends WHERE userTo = '".$myId."' AND popped = '0'");
		$noteCount += mysqli_num_rows($noteQuery);
		$noteQuery = mysqli_query($con, "SELECT * FROM follow WHERE userTo = '".$myId."' AND popped = '0'");
		$noteCount += mysqli_num_rows($noteQuery);
		$data['name'] = $name;
		$data['photoUrl'] = $photoUrl;
		$data['username'] = $username;
		$data['verified'] = $verified;
		$data['msgCount'] = $msgCount;
		$data['noteCount'] = $noteCount;
		
		$frndsArrX = getfriendsArray($myId);
		$hQuery = mysqli_query($con, "SELECT * FROM search WHERE user = '".$myId."' AND category = 'search' ORDER BY id DESC");
		While($hSql = mysqli_fetch_array($hQuery)){
			$dataId = $hSql['dataId'];
			$type = $hSql['type'];
			$txt = $hSql['text'];
			if($type == 'page'){
				$name = getPageInfo($dataId, 'name');
				$userName = '';
				$photo = getPageInfo($dataId, 'photo');
				$verified = filter_var(getPageInfo($dataId, 'verified'), FILTER_VALIDATE_BOOLEAN);
				$isFrnd = false;				
			} else if($type == 'profile'){
				$name = getUserInfo($dataId, 'fName').' '.getUserInfo($dataId, 'lName');
				$userName = getUserInfo($dataId, 'userName');
				$photo = getUserInfo($dataId, 'photo');
				$verified = filter_var(getUserInfo($dataId, 'verified'), FILTER_VALIDATE_BOOLEAN);
				if(in_array($dataId, $frndsArrX))
					$isFrnd = true;
				else
					$isFrnd = false;
			} else {
				$name = $txt;
				$userName = '';
				$photo = '';
				$verified = false;
				$isFrnd = false;				
			}
			$searchArr = array(
				'dataId' => $dataId,
				'name' => $name,
				'type' => $type,
				'userName' => $userName,
				'photo' => $photo,
				'verified' => $verified,
				'txt' => $txt,
				'isFrnd' => $isFrnd
			);
			$searchData[] = $searchArr;
		}
		$data['searchData'] = $searchData;		
	}
	
	$pagesQuery = mysqli_query($con, "SELECT * FROM pages WHERE user = ".$myId);
	While($pagesSql = mysqli_fetch_array($pagesQuery)){
		$pageArr = array();
		$pageArr['id'] = $pagesSql['id'];
		$pageArr['pageName'] = $pagesSql['name'];
		$pageArr['photo'] = $pagesSql['photo'];
		$pageArr['verified'] = filter_var($pagesSql['verified'], FILTER_VALIDATE_BOOLEAN);
		$userPages[] = $pageArr;
		$myPages[] = $pagesSql['id'];
	}
	$pageArray['hasPage'] = $hasPage;
	$pageArray['userPages'] = $userPages;
	$data['pageArray'] = $pageArray;

	$frndQuery = mysqli_query($con, "SELECT * FROM friends WHERE accepted = 'yes' AND (userFrom = '".$myId."' OR userTo = '".$myId."')");
	While($frndSql = mysqli_fetch_array($frndQuery)){
		$userTo = $frndSql['userTo'];
		$userFrom = $frndSql['userFrom'];
		$frnd = $userFrom;
		if($userFrom == $myId)
			$frnd = $userTo;
		if(!in_array($frnd, $blockArray))
			$frndArr[] = $frnd;
	}

	$fllwingQuery = mysqli_query($con, "SELECT * FROM follow WHERE userFrom = '".$myId."' AND accepted = 'yes'");
	While($fllwingSql = mysqli_fetch_array($fllwingQuery)){
		if(!in_array($fllwingSql['userTo'], $blockArray))
			$fllwingArr[] = $fllwingSql['userTo'];
	}

	if($seePstFrmFllwrs){
		$fllwerQuery = mysqli_query($con, "SELECT * FROM follow WHERE userTo = '".$myId."' AND accepted = 'yes'");
		While($fllwerSql = mysqli_fetch_array($fllwerQuery)){
			if(!in_array($fllwerSql['userFrom'], $blockArray))
				$fllwingArr[] = $fllwerSql['userFrom'];
		}
		$fllwingArr = array_unique($fllwingArr);
	}

	$grpFQuery = mysqli_query($con, "SELECT * FROM pageFollow WHERE user = '".$myId."'");
	While($fllwingSql = mysqli_fetch_array($grpFQuery)){
		$pgFlArr[] = $fllwingSql['pageId'];
	}

	$grpFQuery = mysqli_query($con, "SELECT * FROM pageLike WHERE user = '".$myId."'");
	While($fllwingSql = mysqli_fetch_array($grpFQuery)){
		$pgLkArr[] = $fllwingSql['pageId'];
	}
	$frndsArr = array_merge($frndArr, $fllwingArr);
	$frndsArr = array_unique($frndsArr);
	$friends = implode(',', $frndsArr);
	$pagesArr = array_merge($pgFlArr, $pgLkArr);
	$pagesArr = array_merge($pagesArr, $myPages);
	$pagesArr = array_unique($pagesArr);
	$pages = implode(',', $pagesArr);
	
	$postIds = array();

	$query = mysqli_query($con, "SELECT * FROM posts WHERE (postType = 'profile' AND user IN (".$friends.") AND user NOT IN (".$blockedUsers.") AND id NOT IN(".$selectedPosts.") ) OR (postType = 'page' AND user IN (".$pages.") AND id NOT IN(".$selectedPosts.") ) OR FIND_IN_SET('".$myId."', mentionedUsers) AND id NOT IN(".$selectedPosts.") AND id NOT IN(".$hiddenPosts.") AND deleted = 'false' ORDER BY id DESC LIMIT 9");
	if(mysqli_num_rows($query) < 9)
		$allLoaded = true;
	While($sql = mysqli_fetch_array($query)){
		$postId = $sql['id'];
		$postIds[] = $postId;
	}
	
	shuffle($postIds);
	foreach($postIds as $postId){
		$hasFiles = false;
		$pagerId = '0';
		$postData = array();
		$postOptions = array();
		$postOptionsVal = array();
		$user = getPostInfo($postId, 'user');
		$postText = getPostInfo($postId, 'postText');
		$files = getPostInfo($postId, 'files');
		$date = convertDate(getPostInfo($postId, 'date'), true);
		$postType = getPostInfo($postId, 'postType');
		$fName = getUserInfo($user, 'fName');
		$lName = getUserInfo($user, 'lName');
		$name = $fName." ".$lName;
		$userName = getUserInfo($user, 'userName');
		$photo = getUserInfo($user, 'photo');
		if(!empty($files)){
			$files = explode(',', $files);
			$hasFiles = true;
		}
		$poster = $user;
		$perm = true;
		$vary = false;
		$verified = false;
		$liked = checkPostLike($myId, $postId);
		$likeNum = checkPostLikeNumber($postId);
		$comNum = checkPostCommentNumber($postId);
		$savedPost = checkPostSave($myId, $postId);
		$notifyStatus = checkNotifyStatus($myId, $postId);
		
		$postOptions['savePost'] = $savedPost ? 'Unsave Post' : 'Save Post';
		$postOptionsVal['savePost'] = $savedPost;
		if($postType == 'page'){
			$pagerId = $user;
			$name = getPageInfo($user, 'name');
			$poster = getPageInfo($user, 'user');
			$photo = getPageInfo($user, 'photo');
			$verified = filter_var(getPageInfo($user, 'verified'), FILTER_VALIDATE_BOOLEAN);
			$userName = '';
			$fName = $name;
			$checkFollowPage = checkFollowPage($myId, $user);
			if($myId != $poster){
				$postOptions['unfollowPage'] = $checkFollowPage ? 'Unfollow '.$fName : 'Follow '.$fName;
				$postOptionsVal['unfollowPage'] = $checkFollowPage;
			} else 
				$vary = true;
		} else if($postType == 'profile'){
			$verified = filter_var(getUserInfo($user, 'verified'), FILTER_VALIDATE_BOOLEAN);
			$checkFollowUser = checkFollowUserInt($myId, $user);
			if($user != $myId){
				list($preName) = explode(' ', $fName);
				$postOptions['unfollowPers'] = $checkFollowUser ? 'Unfollow '.$preName : 'Follow '.$preName;
				$postOptionsVal['unfollowPers'] = $checkFollowUser;
			}
			if($poster != $myId){
				$comPerm = getPrefSetting($poster, 'com');
				if($comPerm == 1 && !friendsOrFollowing($poster, $myId))
					$perm = false;
			}
		}
		if(!empty($postText)){
			$html = stripslashes($postText);
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
							$postText = preg_replace(array('/'.$spanOpen.$bOpen.$aOpen.'(.*?)'.$aClose.$bClose.$spanClose.'/i', '/'.$spanOpen.$aOpen.$bOpen.'(.*?)'.$bClose.$aClose.$spanClose.'/i',
														'/'.$bOpen.$spanOpen.$aOpen.'(.*?)'.$aClose.$spanClose.$bClose.'/i', '/'.$bOpen.$aOpen.$spanOpen.'(.*?)'.$spanClose.$aClose.$bClose.'/i',
														'/'.$aOpen.$spanOpen.$bOpen.'(.*?)'.$bClose.$spanClose.$aClose.'/i', '/'.$aOpen.$bOpen.$spanOpen.'(.*?)'.$spanClose.$bClose.$aClose.'/i'),
												array('$2', '$2', '$2', '$2', '$2', '$2'), $postText);
						}
					}
				}
			}
		}
		
		$postData['id'] = $postId;
		$postData['user'] = $user;
		$postData['name'] = $name;
		$postData['userName'] = $userName;
		$postData['photo'] = $photo;
		$postData['type'] = $postType;
		$postData['files'] = $files;
		$postData['hasFiles'] = $hasFiles;
		$postData['date'] = $date;
		$postData['post'] = $postText;
		$postData['pagerId'] = $pagerId;
		$postData['verified'] = $verified;
		$postData['liked'] = $liked;
		$postData['perm'] = $perm;
		$postData['vary'] = $vary;
		$postData['likeNum'] = $likeNum;
		$postData['comNum'] = $comNum;
		
		$postOptions['notifyStatus'] = $notifyStatus ? 'Turn Off Notification' : 'Turn On Notification';
		$postOptionsVal['notifyStatus'] = $notifyStatus;
		
		$postOptions['copyLink'] = 'Copy Link';
		$postOptions['hidePost'] = 'Hide Post';
		if($myId == $poster){
			$postOptions['editPost'] = 'Edit Post';
			$postOptions['deletePost'] = 'Delete Post';
		} else {
			$postOptions['reportPost'] = 'Report Post';	
		}
		
		$postData['options'] = $postOptions;
		$postData['optionsVal'] = $postOptionsVal;
		$comData = array();
		$sort = getPrefSetting($myId, 'sort');
		$queryArray = array(
			"SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND NOT FIND_IN_SET('".$myId."', hide) ORDER BY id ASC LIMIT 3",
			"SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND NOT FIND_IN_SET('".$myId."', hide) ORDER BY id DESC LIMIT 3",
			"SELECT * FROM comments WHERE postId = '".$postId."' AND (type = 'page' OR (type = 'profile' AND user NOT IN (".$blockedUsers."))) AND NOT FIND_IN_SET('".$myId."', hide) ORDER BY relevance DESC LIMIT 3"
		);
		$queryStr = $queryArray[$sort];
		$comQuery = mysqli_query($con, $queryStr);
		While($comSql = mysqli_fetch_array($comQuery)){
			$comArray = array();
			$comId = $comSql['id'];
			$comUser = $comSql['user'];
			$comment = $comSql['comment'];
			$comType = $comSql['type'];
			$comUserName = getUserInfo($comUser, 'userName');
			$comPhoto = getUserInfo($comUser, 'photo');
			if($comType == 'page'){
				$comUserName = getPageInfo($comUser, 'name');
				$comPhoto = getPageInfo($comUser, 'photo');
			}
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
			$comArray['id'] = $comId;
			$comArray['userName'] = $comUserName;
			$comArray['photo'] = $comPhoto;
			$comArray['comment'] = $comment;
			$comData[] = $comArray;
		}
		$postData['comments'] = $comData;
		$postDatas[] = $postData;
	}
	$data['postDatas'] = $postDatas;
	$data['allLoaded'] = $allLoaded;
	echo json_encode($data);
}
?>