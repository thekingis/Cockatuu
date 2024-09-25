<?php
date_default_timezone_set('UTC');
$lang = 'english';
$date = date('Y-m-d G:i:s');
$httpStr = "(?:https?:\/\/|[a-z0-9-_]\d{0,3}[.])";
$wwwStr = "(www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)";
$pathStr = '(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’])';

function getUserInfo($user, $field) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM accounts WHERE id = '".$user."'");
	if(mysqli_num_rows($query) > 0){
		$run = mysqli_fetch_array($query);
		if(!empty($run[$field]))
			return $run[$field];
	}
	return null;
}

function getMessageInfo($myId, $query) {
	$fileCount = 0;
	$time = time();
	$datas = array();
	While($sql = mysqli_fetch_array($query)){
		$data = array();
		$id = $sql['id'];
		$userFrom = $sql['userFrom'];
		$text = $sql['text'];
		$seen = $sql['seen'];
		$dlvd = $sql['dlvd'];
		$maxDate = $sql['date'];
		$msgRef = $sql['msgRef'];
		$files = $sql['files'];
		$oriFiles = $files;
		$audioType = filter_var($sql['audioType'], FILTER_VALIDATE_BOOLEAN);
		$date = convertDate($maxDate, true);
		$maxTime = strtotime($maxDate);
		if(!empty($files)){
			$files = explode(',', $files);
			$fileCount += count($files);
		}
		$msgRefData = getMessageRefData($msgRef);
		$hasLink = hasLink($text);
		$deletable = false;
		$timeDiff = $time - $maxTime;
		$timeDiff /= 60;
		if($timeDiff < 10 && $userFrom == $myId)
			$deletable = true;
		
		$data['id'] = $id;
		$data['userFrom'] = $userFrom;
		$data['msgRef'] = $msgRef;
		$data['msgBody'] = $text;
		$data['seen'] = $seen;
		$data['dlvd'] = $dlvd;
		$data['date'] = $date;
		$data['files'] = $files;
		$data['oriFiles'] = $oriFiles;
		$data['time'] = $maxTime;
		$data['audioType'] = $audioType;
		$data['msgRefData'] = $msgRefData;
		$data['hasLink'] = $hasLink;
		$data['deletable'] = $deletable;
		
		$datas[$id] = $data;
	}
	if(count($datas) > 0)
		return array($datas, $fileCount);
	return array(null, 0);
}

function getPageInfo($pageId, $field) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM pages WHERE id = '".$pageId."'");
	if(mysqli_num_rows($query) > 0){
		$run = mysqli_fetch_array($query);
		if(!empty($run[$field]))
			return $run[$field];
	}
	return null;
}

function getComRepInfo($id, $tab, $field) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM ".$tab." WHERE id = '".$id."'");
	if(mysqli_num_rows($query) > 0){
		$run = mysqli_fetch_array($query);
		if(!empty($run[$field]))
			return $run[$field];
	}
	return null;
}

function getTableInfo($table, $column, $value, $field) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM ".$table." WHERE ".$column." = '".$value."'");
	if(mysqli_num_rows($query) > 0){
		$run = mysqli_fetch_array($query);
		if(!empty($run[$field]))
			return $run[$field];
	}
	return null;
}

function getPageFollowNumber($pageId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM pagefollow WHERE pageId = '".$pageId."'");
	$num = mysqli_num_rows($query);
	return $num;
}

function getPostInfo($postId, $field) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM posts WHERE id = '".$postId."'");
	if(mysqli_num_rows($query) > 0){
		$run = mysqli_fetch_array($query);
		if(!empty($run[$field]))
			return $run[$field];
	}
	return null;
}

function getCommentInfo($comId, $field) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM comments WHERE id = '".$comId."'");
	if(mysqli_num_rows($query) > 0){
		$run = mysqli_fetch_array($query);
		if(!empty($run[$field]))
			return $run[$field];
	}
	return null;
}

function checkPostLikeNumber($postId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM postlike WHERE postId = '".$postId."'");
	return mysqli_num_rows($query);
}

function checkPostCommentNumber($postId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM comments WHERE postId = '".$postId."'");
	return mysqli_num_rows($query);
}

function checkPostLike($user, $postId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM postlike WHERE user = '".$user."' AND postId = '".$postId."'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function checkPostHide($user, $postId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM hiddenposts WHERE user = '".$user."' AND postId = '".$postId."'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function checkReplyLike($user, $repId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM replike WHERE user = '".$user."' AND repId = '".$repId."'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function getReplyLikeNum($repId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM replike WHERE repId = '".$repId."'");
	return mysqli_num_rows($query);
}

function checkCommentLike($user, $comId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM comlike WHERE user = '".$user."' AND comId = '".$comId."'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function getCommentLikeNum($comId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM comlike WHERE comId = '".$comId."'");
	return mysqli_num_rows($query);
}

function getCommentReplyNum($comId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM replies WHERE comId = '".$comId."'");
	return mysqli_num_rows($query);
}

function friendsOrFollowing($userTo, $userFrom) {
	global $con;
	$queryFrnd = mysqli_query($con, "SELECT * FROM friends WHERE (userTo = '".$userTo."' AND userFrom = '".$userFrom."') OR (userTo = '".$userFrom."' AND userFrom = '".$userTo."') AND accepted = 'yes'");
	$queryFllwing = mysqli_query($con, "SELECT * FROM follow WHERE userTo = '".$userTo."' AND userFrom = '".$userFrom."'");
	if(mysqli_num_rows($queryFrnd) == 1 || mysqli_num_rows($queryFllwing) == 1)
		return true;
	return false;
}

function getfriendsArray($user) {
	global $con;
	$array = array(0);
	$queryStr = "SELECT userTo AS user FROM friends WHERE userFrom = '".$user."' AND accepted = 'yes' UNION SELECT userFrom AS user FROM friends WHERE userTo = '".$user."' AND accepted = 'yes'";
	$queryFrnd = mysqli_query($con, $queryStr);
	While($sql = mysqli_fetch_array($queryFrnd))
		$array[] = $sql['user'];
	return $array;
}

function checkBlocked($userFrom, $userTo) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM blocked WHERE (userTo = '".$userTo."' AND userFrom = '".$userFrom."') OR (userTo = '".$userFrom."' AND userFrom = '".$userTo."')");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function checkMessageAccess($userFrom, $userTo) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM msgblock WHERE (userTo = '".$userTo."' AND userFrom = '".$userFrom."') OR (userTo = '".$userFrom."' AND userFrom = '".$userTo."')");
	if(mysqli_num_rows($query) == 1)
		return false;
	$query = mysqli_query($con, "SELECT * FROM blocked WHERE (userTo = '".$userTo."' AND userFrom = '".$userFrom."') OR (userTo = '".$userFrom."' AND userFrom = '".$userTo."')");
	if(mysqli_num_rows($query) == 1)
		return false;
	return true;
}

function getBlocks($user) {
	global $con;
	$array = array(0);
	$query = mysqli_query($con, "SELECT userFrom AS user FROM blocked WHERE userTo = '".$user."' UNION SELECT userTo AS user FROM blocked WHERE userFrom = '".$user."'");
	While($sql = mysqli_fetch_array($query)){
		$array[] = $sql['user'];
	}
	return $array;
}

function getMessageBlocks($user) {
	global $con;
	$array = array(0);
	$query = mysqli_query($con, "SELECT userFrom AS user FROM msgblock WHERE userTo = '".$user."' UNION SELECT userTo AS user FROM msgblock WHERE userFrom = '".$user."'");
	While($sql = mysqli_fetch_array($query)){
		$array[] = $sql['user'];
	}
	return $array;
}

function checkUserBlocked($userFrom, $userTo) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM msgblock WHERE userTo = '".$userTo."' AND userFrom = '".$userFrom."'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function checkSoundEnable($userFrom, $userTo) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM offchatsound WHERE userTo = '".$userTo."' AND userFrom = '".$userFrom."'");
	if(mysqli_num_rows($query) == 1)
		return false;
	return true;
}

function checkOnlineDisable($userFrom, $userTo) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM offlinechat WHERE userTo = '".$userTo."' AND userFrom = '".$userFrom."'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function getMessageId($myId, $user) {
	global $con, $date;
	$query = mysqli_query($con, "SELECT * FROM startmsg WHERE (userFrom = '".$myId."' AND userTo = '".$user."') OR (userFrom = '".$user."' AND userTo = '".$myId."')");
	if(mysqli_num_rows($query) == 1){
		$sql = mysqli_fetch_array($query);
		$id = $sql['id'];
	} else {
		mysqli_query($con, "INSERT INTO startmsg VALUES('0', '".$myId."', '".$user."', '".$date."', '".$date."', 'abstract', '0')");
		$id = mysqli_insert_id($con);
	}
	return $id;
}

function getFavourites($user) {
	global $con;
	$array = array('f' => '0');
	$query = mysqli_query($con, "SELECT * FROM favourites WHERE user = '".$user."'");
	While($sql = mysqli_fetch_array($query)){
		$msgId = $sql['msgId'];
		$array[$msgId] = $msgId;
	}
	return $array;
}

function getArchives($user) {
	global $con;
	$array = array('a' => '0');
	$query = mysqli_query($con, "SELECT * FROM archives WHERE user = '".$user."'");
	While($sql = mysqli_fetch_array($query)){
		$msgId = $sql['msgId'];
		$array[$msgId] = $msgId;
	}
	return $array;
}

function getLastMessageInfo($msgId, $user) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM allmsgs WHERE msgId = '".$msgId."' AND deleted != '".$user."' ORDER BY id DESC LIMIT 1");
	$sql = mysqli_fetch_array($query);
	return $sql;
}

function getMessageRefData($msgId) {
	global $con;
	$array = array();
	$query = mysqli_query($con, "SELECT * FROM allmsgs WHERE id = '".$msgId."'");
	$sql = mysqli_fetch_array($query);
	$array['userFrom'] = $sql['userFrom'];
	$array['msgBody'] = $sql['text'];
	$array['files'] = $sql['files'];
	$array['type'] = filter_var($sql['audioType'], FILTER_VALIDATE_BOOLEAN);
	return $array;
}

function checkLastMessageFrom($msgId, $user) {
	global $con;
	$fromMe = false;
	$query = mysqli_query($con, "SELECT * FROM allmsgs WHERE msgId = '".$msgId."' AND deleted != '".$user."' ORDER BY id DESC LIMIT 1");
	$sql = mysqli_fetch_array($query);
	$userFrom = $sql['userFrom'];
	if($userFrom == $user)
		$fromMe = true;
	return $fromMe;
}

function getMsgNum($msgId, $user) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM allmsgs WHERE msgId = '".$msgId."' AND seen = '0' AND userTo = '".$user."'");
	$num = mysqli_num_rows($query);
	if($num > 0)
		$num = '('.$num.')';
	else $num = '';
	return $num;
}

function hiddenPosts($user) {
	global $con;
	$array = array(0);
	$query = mysqli_query($con, "SELECT * FROM hiddenposts WHERE user = '".$user."'");
	While($sql = mysqli_fetch_array($query)){
		$postId = $sql['postId'];
		$array[] = $postId;
	}
	return implode(',', $array);
}

function getPrefSetting($user, $type) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM settings WHERE user = '".$user."' AND type = '".$type."'");
	if(mysqli_num_rows($query) == 1){
		$sql = mysqli_fetch_array($query);
		$ret = (int)$sql['opt'];
		return $ret;
	}
	return 0;
}

function getSettingID($user, $type) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM settings WHERE user = '".$user."' AND type = '".$type."'");
	if(mysqli_num_rows($query) == 1){
		$sql = mysqli_fetch_array($query);
		$ret = (int)$sql['id'];
		return $ret;
	}
	return 0;
}

function checkPostSave($user, $postId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM savedposts WHERE user = '".$user."' AND postId = '".$postId."'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function checkFollowUser($myId, $user) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM follow WHERE userFrom = '".$myId."' AND userTo = '".$user."' AND accepted = 'yes'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function checkFollowUserInt($myId, $user) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM follow WHERE userFrom = '".$myId."' AND userTo = '".$user."'");
	if(mysqli_num_rows($query) == 1){
		$sql = mysqli_fetch_array($query);
		$accepted = $sql['accepted'];
		if($accepted == 'yes')
			return 2;		
		return 1;
	}
	return 0;
}

function checkFriendToUser($myId, $user) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM friends WHERE userFrom = '".$myId."' AND userTo = '".$user."'");
	if(mysqli_num_rows($query) == 1){
		$sql = mysqli_fetch_array($query);
		$accepted = $sql['accepted'];
		if($accepted == 'yes')
			return 3;
		return 1;
	}
	$query = mysqli_query($con, "SELECT * FROM friends WHERE userFrom = '".$user."' AND userTo = '".$myId."'");
	if(mysqli_num_rows($query) == 1){
		$sql = mysqli_fetch_array($query);
		$accepted = $sql['accepted'];
		if($accepted == 'yes')
			return 3;
		return 2;
	}
	return 0;
}

function checkFollowPage($user, $pageId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM pagefollow WHERE user = '".$user."' AND pageId = '".$pageId."'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function checkNotifyStatus($user, $postId) {
	global $con;
	$query = mysqli_query($con, "SELECT * FROM notifystatus WHERE user = '".$user."' AND postId = '".$postId."'");
	if(mysqli_num_rows($query) == 1)
		return true;
	return false;
}

function hasPage($user){
	global $con;
	$query = mysqli_query($con, "SELECT * FROM pages WHERE user = '".$user."'");
	if(mysqli_num_rows($query) > 0)
		return true;
	return false;
}

function hasLink($text){
	$regex1 = '((http|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])?)';
	$regex2 = '((www.)([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])?)';
	preg_match_all("/".$regex1."/", $text, $matches1);
	preg_match_all("/".$regex2."/", $text, $matches2);
	if(count($matches1[0]) > 0 || count($matches2[0]) > 0)
		return true;
	return false;
}

function getLinkData($text){
	if(hasLink($text)){
		$regex = '((http|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])?)';
		preg_match_all("/".$regex."/", $text, $matches);
		$link = $matches[0][0];
		$html = fileGetContentsCurl($link);
		$data = parseTheUrl($html, $link);
		return $data;
	} else
		return '';
}

function parseTheUrl($html, $urlStr){
	$doc = new DOMDocument();
	@$doc->loadHTML($html);
	$nodes = $doc->getElementsByTagName('title');
	$node = $doc->getElementsByTagName('img');
	@$title = $nodes->item(0)->nodeValue;
	$metas = $doc->getElementsByTagName('meta');
	$links = $doc->getElementsByTagName('link');
	for ($i = 0; $i < $node->length; $i++){
		$code = $node->item($i);
		$imgs[] = $code->getAttribute('src');
	}
	for ($i = 0; $i < $metas->length; $i++){
		$meta = $metas->item($i);
		if($meta->getAttribute('name') == 'description')
			$description = $meta->getAttribute('content');
		if($meta->getAttribute('name') == 'keywords')
			$keywords = $meta->getAttribute('content');
		if($meta->getAttribute('property') == 'og:image')
			$ogImg = $meta->getAttribute('content');
	}
	@shuffle($imgs);
	$img = $imgs[0];
	$urlArray = parse_url($urlStr);
	$scheme = $urlArray['scheme'];
	$host = $urlArray['host'];
	$hostPath = $scheme."://".$host;
	$hostLen = strlen($hostPath);
	$urlHost = substr($img, 0, $hostLen);
	if($urlHost == $hostPath)
		$imaged = $img;
	else 
		$imaged = $hostPath.$img;
	if(!empty($ogImg) && ($ogImg == true)){
		$image = $ogImg;
	} else {
		$image = $imaged;
		for ($i = 0; $i < $links->length; $i++){
			$link = $links->item($i);
			if($link->getAttribute('rel') == 'icon' && $link->getAttribute('type') == 'image/x-icon')
				$image = $link->getAttribute('href');
		}
	}
	if(!preg_match('/^(https?):\/\//', $image)){
		if(preg_match('/^\//', $image))
			$image = $urlStr.$image;
		if(preg_match('/^www\./', $image) && preg_match('/^'.$host.'/', $image))
			$image = $hostPath.$image;
	}
	@$metaArray = array(
		'title' => $title,
		'description' => $description,
		'imageUrl' => $image,
		'linkUrl' => $urlStr,
		'host' => $host
	);
	return $metaArray;	
}

function fileGetContentsCurl($url){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
	curl_setopt($curl, CURLOPT_AUTOREFERER, true); 
	curl_setopt($curl, CURLOPT_VERBOSE, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_ENCODING, 'gzip');

	$data = curl_exec($curl);
	curl_close($curl);

    return $data;
}

function getPage($user){
	global $con;
	$array = array(0);
	$query = mysqli_query($con, "SELECT * FROM pages WHERE user = '".$user."'");
	While($sql = mysqli_fetch_array($query)){
		$id = $sql['id'];
		$array[] = $id;
	}
	return implode(',', $array);
}

function getFrndsNum($user){
	global $con;
	$query = mysqli_query($con, "SELECT * FROM friends WHERE (userTo = '".$user."' AND accepted = 'yes') OR (userFrom = '".$user."' AND accepted = 'yes')");
	return mysqli_num_rows($query);
}

function getFllwersNum($user){
	global $con;
	$query = mysqli_query($con, "SELECT * FROM follow WHERE userTo = '".$user."' AND accepted = 'yes'");
	return mysqli_num_rows($query);
}

function getFllwingNum($user){
	global $con;
	$query = mysqli_query($con, "SELECT * FROM follow WHERE userFrom = '".$user."' AND accepted = 'yes'");
	return mysqli_num_rows($query);
}

function convertDate($date, $addStr){
	$currTime = time();
	$dateTime = strtotime($date);
	$seconds = $currTime - $dateTime;
	$minutes = (int)($seconds / 60 );
	$hours = (int)($seconds / 3600 );
	$days = (int)($seconds / 86400);
	$daysArr = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
	$monthArr = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	$yrOfTime = date('Y', $currTime);
	$yrOfDate = date('Y', $dateTime);
	$monOfDate = date('m', $dateTime);
	$monOfDate--;
	$dayOfDate = (int)date('w', $dateTime);
	$dayOfTime = (int)date('w', $currTime);
	$dayDiff = $dayOfTime - $dayOfDate;
	$mon = $monthArr[$monOfDate];
	$w = $daysArr[$dayOfDate];
	$d = date('d', $dateTime);
	$h = date('G', $dateTime);
	$m = date('i', $dateTime);
	$a = 'am';
	if($h > 12){
		$a = 'pm';
		$h -= 12;
	}
	if($h == 12)
		$a = 'noon';
	$str = ' at '.$h.':'.$m.$a;
	if(!$addStr)
		$str = '';
	if($days > 6)
		$dateStr = $mon.' '.$d.$str;
	if($yrOfDate < $yrOfTime)
		$dateStr = $yrOfDate.' '.$mon.' '.$d.$str;	
	if($days < 7)
		$dateStr = $w.$str;
	if($hours < 24)
		$dateStr = $hours.'hrs ago';
	if($hours == 1)
		$dateStr = $hours.'hr ago';
	if($days < 2 && $dayDiff == 1)
		$dateStr = 'Yesterday'.$str;
	if($minutes < 60)
		$dateStr = $minutes.'mins ago';
	if($minutes == 1)
		$dateStr = $minutes.'min ago';
	if($seconds < 60)
		$dateStr = 'Just Now';
	return $dateStr;
}

function convertImage($originalImage, $outputImage, $quality){
    // jpg, png, gif or bmp?
    $exp = explode('.', $originalImage);
    $ext = end($exp); 

    if (preg_match('/jpg|jpeg/i',$ext))
        $imageTmp=imagecreatefromjpeg($originalImage);
    else if (preg_match('/png/i',$ext))
        $imageTmp=imagecreatefrompng($originalImage);
    else if (preg_match('/gif/i',$ext))
        $imageTmp=imagecreatefromgif($originalImage);
    else if (preg_match('/bmp/i',$ext))
        $imageTmp=imagecreatefrombmp($originalImage);
    else
        return false;

    // quality is a value from 0 (worst) to 100 (best)
    imagejpeg($imageTmp, $outputImage, $quality);
    imagedestroy($imageTmp);

    return true;
}

function removeFromArray($arr1, $arr2){
	foreach($arr1 as $key => $a){
		if(!in_array($a, $arr2))
			unset($arr1[$key]);
	}
	return $arr1;
}

function captureUrls($text){
	global $httpStr, $wwwStr, $pathStr;
	$text = preg_replace(array('/(?i)\b(^|\s)('.$httpStr.$pathStr.')/', '/(?i)\b(^|\s)('.$wwwStr.$pathStr.')/'), 
		array('$1<a href="$2">$2</a>', '$1<a href="http://$2">$2</a>'), $text);
	return $text;
}

function stripHTMLTags($text){
	$text = str_replace('<br>', ' ', $text);
	$text = preg_replace('/<(.*?)>/', '', $text);
	return $text;
}

?>