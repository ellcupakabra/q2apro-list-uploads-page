<?php

	class qa_list_uploads_page {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		// for display in admin interface under admin/pages
		function suggest_requests() 
		{	
			return array(
				array(
					'title' => 'Uploads', // title of page
					'request' => 'listuploads', // request name
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		// for url query
		function match_request($request)
		{
			if ($request=='listuploads') {
				return true;
			}

			return false;
		}

		function process_request($request)
		{
			/* process URL parameters */ 
			
			// you can set number of days to be shown in the URL
			// e.g. yoursite.com/listuploads?days=5
			$lastdays = qa_get("days");
			if(is_null($lastdays) || $lastdays<=0) {
				$lastdays = 3; // show new uploads from last x days
			}
			
			// you can set a flag in the URL to show only those images that do not exist in posts/avatars
			// e.g. yoursite.com/listuploads?days=5&remove=1
			$onlyImgToRemove = qa_get("remove");
			$removeMode = false;
			$deleteAll = false;
			if(!is_null($onlyImgToRemove)) {
				$removeMode=true;
				// show unused images of last 30 days, if days not specified in URL
				if(is_null( qa_get("days") )) {
					$lastdays = 3;
				}
				// if remove=all in URL, then set flag to remove all images
				if($onlyImgToRemove=="all") {
					$deleteAll = true;
				}
			}
			
			// you can specifiy a username in the URL if you wish to only see images of this user
			// e.g. yoursite.com/listuploads?days=30&user=William35
			$gotUserName = qa_get("user");
			$usernameNotExists = false;
			if(is_null($gotUserName)) {
				$userid_toQuery = "";
			}
			else {
				// get userid
				$useridRowQuery = qa_db_query_sub("SELECT userid FROM ^users WHERE handle = '".$gotUserName."' LIMIT 1");
				$theUserData = mysql_fetch_array($useridRowQuery);
				if(!is_null($theUserData[0])) {				
					$userid_toQuery = "AND userid = ".$theUserData[0];
				}
				else {
					$userid_toQuery = "";
					$usernameNotExists = true; // to inform admin
				}
			}
			
			
			/* start content */
			$qa_content = qa_content_prepare();

			// page title
			$qa_content['title'] = $deleteAll ? "Images have been deleted" : qa_lang_html('qa_list_uploads_lang/page_title') . " ".$lastdays." ".qa_lang_html('qa_list_uploads_lang/page_days'); 

			// return if not admin!
			$level=qa_get_logged_in_level();
			if ($level<QA_USER_LEVEL_ADMIN) {
				$qa_content['custom0']='<div>'.qa_lang_html('qa_list_uploads_lang/not_allowed').'</div>';
				return $qa_content;
			}
			
			// delete button was hit by admin
			$deleteBlobId = qa_get("delete");
			if(!is_null($deleteBlobId)) {
				// delete image from database, i.e. blobid from table qa_blobs
				$queryDeleteBlob = qa_db_query_sub("DELETE FROM `^blobs` WHERE blobid = ".$deleteBlobId." LIMIT 1;");
				$qa_content['custom0']='<p style="margin:40px 0 20px 0;font-size:15px;">Image with BlobID '.$deleteBlobId.' has been deleted!<br /><br />Thanks for cleaning up :)</p>';
				$qa_content['custom1']='<p style="font-size:15px;"><a href="./listuploads">&raquo; '.qa_lang_html('qa_list_uploads_lang/nav_back_list').'</a> | <a href="./listuploads?remove=1">&raquo; '.qa_lang_html('qa_list_uploads_lang/nav_back_removelist').'</a></p>';
				return $qa_content;
			}
			
			// inform admin if username passed by URL does not exist
			if($usernameNotExists) {
				$qa_content['custom0']='<div>'.qa_lang_html('qa_list_uploads_lang/user_not_existing').'</div>';
				return $qa_content;
			}

			// required for qa_get_blob_url()
			require_once QA_INCLUDE_DIR.'qa-app-blobs.php';
			
			// query blobs of last x days
			$queryRecentUploads = qa_db_query_sub("SELECT blobid,format,userid,created,filename
											FROM `^blobs`
											WHERE created > NOW() - INTERVAL ".$lastdays." DAY " 
											. $userid_toQuery .
											" ORDER BY created DESC;"); // LIMIT 0,100
											
			// counter for custom html output
			$c = 2;
			$imgCount = 1;
			$imgDelCount = 1;
			
			// initiate output string
			$listAllUploads = "<table> <thead><tr><th>&nbsp;</th><th class='column1'>".qa_lang_html('qa_list_uploads_lang/upload_date')."</th>  <th class='column1'>".qa_lang_html('qa_list_uploads_lang/media_item')."</th> <th>Size</th> <th class='column2'>".qa_lang_html('qa_list_uploads_lang/upload_by_user')."</th> </tr></thead>";
			$d = 0;
			while ( ($blobrow = qa_db_read_one_assoc($queryRecentUploads,true)) !== null ) {
				$currentUser = $blobrow['userid'];
				$userrow = qa_db_select_with_pending( qa_db_user_account_selectspec($currentUser, true) );
				
				// get size of image
				$imageSizeQuery = qa_db_query_sub("SELECT OCTET_LENGTH(content) FROM `^blobs` WHERE blobid='".$blobrow['blobid']."' LIMIT 1");
				// $imgRow = qa_db_read_one_assoc($queryRecentUploads,true)
				$theSize = mysql_fetch_array($imageSizeQuery);
				$imgSize = round($theSize[0]/1000, 1).' kB';
				
				// check if image is used in post content
				$notFoundString = '<span style="color:#F00">&rarr; not found in posts &rarr; <a class="delImageLink" href="?delete='.$blobrow['blobid'].'">delete image?</a></span>';
				$imageExistsQuery = qa_db_query_sub("SELECT postid,type,parentid FROM `^posts` WHERE `content` LIKE '%".$blobrow['blobid']."%' LIMIT 1");
				$imageInPost = mysql_fetch_array($imageExistsQuery);
				$existsInPost = $imageInPost[0];
				// $existsInPost = ($existsInPost=="") ? $notFoundString : "";
				
				// set link to question, answer, comment that contains the image
				if($existsInPost=="") {
					$existsInPost = $notFoundString;
				}
				else if($imageInPost[1]=="A") {
					$existsInPost = "<a href='".$imageInPost[2]."?show=".$imageInPost[0]."#a".$imageInPost[0]."' style='margin-left:10px;font-size:11px;'>&rarr; in answer: ".$existsInPost."</a>";
				}
				else if($imageInPost[1]=="C") {
					// get question link from answer
					$getQlink = mysql_fetch_array( qa_db_query_sub("SELECT parentid,type FROM `^posts` WHERE `postid` = ".$imageInPost[2]." LIMIT 1") );
					$linkToQuestion = $getQlink[0];
					if($getQlink[1]=="A") {
						$existsInPost = "<a href='".$linkToQuestion."?show=".$imageInPost[0]."#c".$imageInPost[0]."' style='margin-left:10px;font-size:11px;'>&rarr; in comment: ".$existsInPost."</a>";
					}
					else {
						// default: comment on question
						$existsInPost = "<a href='".$imageInPost[2]."?show=".$imageInPost[0]."#c".$imageInPost[0]."' style='margin-left:10px;font-size:11px;'>&rarr; in comment: ".$existsInPost."</a>";
					}
				}
				else {
					// default: question
					$existsInPost = "<a href='".$existsInPost."' style='margin-left:10px;font-size:11px;'>&rarr; in question: ".$imageInPost[0]."</a>";
				}

				// check if image is used as user avatar
				$avImageExistsQuery = qa_db_query_sub("SELECT userid FROM `^users` WHERE `avatarblobid` LIKE '".$blobrow['blobid']."' LIMIT 1");
				$imageAsAvatar = mysql_fetch_array($avImageExistsQuery);
				$existsAsAvatar = $imageAsAvatar[0];
				if($existsInPost==$notFoundString && $existsAsAvatar!="") {
					$existsInPost = "<span style='color:#00F'>&rarr; used as avatar image</span>";
				}
				else {
					// check if image is used as default avatar (within table qa_options, field avatar_default_blobid)
					$avImageExistsQuery2 = qa_db_query_sub("SELECT title FROM `^options` WHERE `content` LIKE '".$blobrow['blobid']."' LIMIT 1");
					$imageAsAvatar2 = mysql_fetch_array($avImageExistsQuery2);
					$existsAsAvatar = $imageAsAvatar2[0];
					if($existsInPost==$notFoundString && $existsAsAvatar!="") {
						$existsInPost = "<span style='color:#07F'>&rarr; used as default avatar image</span>";
					}
				}
				
				// check if image is used in custom pages
				$pageImgExistsQuery = qa_db_query_sub(" SELECT tags FROM `^pages` WHERE `content` LIKE '%".$blobrow['blobid']."%' LIMIT 1");
				$imageInPageResult = mysql_fetch_array($pageImgExistsQuery);
				$existsInPage = $imageInPageResult[0];
				if($existsInPost==$notFoundString && $existsInPage!="") {
					$existsInPost = "<span style='color:#09C;'>&rarr; used in custom page: '".$existsInPage."'</span>";
				}
				
				// delete all unused images (not in post, not avatar) if flag is set in URL
				if($deleteAll && $existsInPost==$notFoundString && $existsAsAvatar=="") {
					// delete image from database, i.e. blobid from table qa_blobs, do not touch images that were uploaded within last 10 min
					$queryDeleteAll = qa_db_query_sub("DELETE FROM `^blobs` WHERE blobid = ".$blobrow['blobid']." AND created < (NOW( ) - INTERVAL 10 MINUTE) LIMIT 1;");
					// time difference in seconds
					$timeDiff = strtotime(date('Y-m-d H:i:s')) - strtotime($blobrow['created']);
					if($timeDiff>600) {
						$qa_content['custom'.++$c] = '<p>'.$imgDelCount.'. Image deleted: ' . $blobrow['blobid'] . '</p>';
						$imgDelCount++;
					}
					else {
						$qa_content['custom'.++$c] = "Image too young (survivor): " . $blobrow['blobid'] . "<br />";
					}
					continue;
				}

				
				$rowString = "<tr><td>".($removeMode ? $imgDelCount : $imgCount).".<td>".substr($blobrow['created'],0,16)."</td> <td><img class='listSmallImages' src='".qa_get_blob_url($blobrow['blobid'])."' \> <br /><span style='color:#777;font-size:11px;'>".$blobrow['blobid']."</span> ".$existsInPost."<br /><span style='color:#777;font-size:11px;'>".$blobrow['filename']."</span></td> <td>".$imgSize."</td> <td>". qa_get_user_avatar_html($userrow['flags'], $userrow['email'], $userrow['handle'], $userrow['avatarblobid'], $userrow['avatarwidth'], $userrow['avatarheight'], qa_opt('avatar_users_size'), false) ."<br />". qa_get_one_user_html($userrow['handle'], false) ."</td> </tr>";
			
				// list only images to be deleted or all images
				if($removeMode) {
					if($existsInPost==$notFoundString) {
						$listAllUploads .= $rowString;
						$imgDelCount++;
					}
				}
				else {
					// uncomment for hack: show only big images over 100K
					//if($theSize[0] > 100000) {
					$listAllUploads .= $rowString;
					//}
				}
				// image count
				$imgCount++;
			}
			$listAllUploads .= "</table>";

			
			/* output into theme */
			if($deleteAll) {
				$qa_content['custom'.++$c]='<br /><a href="./listuploads">&raquo; '.qa_lang_html('qa_list_uploads_lang/nav_back_list').'</a>';
				return $qa_content;
			}
			$qa_content['custom'.++$c]='<p><a onclick="javascript:return confirm(\''.qa_lang_html('qa_list_uploads_lang/remove_all_warning_popup').'?\');" href="?remove=all">'.qa_lang_html('qa_list_uploads_lang/remove_all').'</a> &rarr; '.qa_lang_html('qa_list_uploads_lang/remove_all_warning').'</p>';
			$qa_content['custom'.++$c]= $removeMode ? '<p style="margin-top:10px;"><a href="./listuploads">'.qa_lang_html('qa_list_uploads_lang/show_all').'</a></p>': '<p style="margin-top:10px;"><a href="?remove=1">'.qa_lang_html('qa_list_uploads_lang/show_unused').'</a></p>';
			
			if($removeMode) { $qa_content['custom'.++$c]='<p>'.qa_lang_html('qa_list_uploads_lang/number_images_remove').': '.($imgDelCount-1).'</p>'; }
	
			$qa_content['custom'.++$c]='<div class="listuploads" style="border-radius:0; padding:0; margin-top:-2px;">';
			
			$qa_content['custom'.++$c]= $listAllUploads;
			
			$qa_content['custom'.++$c]='</div>';
			
			// show admin tip how to use parameters in URL
			$qa_content['custom'.++$c]='<div style="padding:20px;border:1px solid #CCC;border-radius:10px;background:#FFC;"><p><b>Instructions for Admin:</b><br />Use URL parameters to filter images: /listuploads?<span style="color:#F00">days=30</span>&amp;<span style="color:#090">remove=1</span>&amp;<span style="color:#00F">user=William35</span></p>';
			$qa_content['custom'.++$c]='<p><span style="color:#F00">days=30</span> &rarr; sets number of days to be shown</p>';
			$qa_content['custom'.++$c]='<p><span style="color:#090">remove=1</span> &rarr; show only images that do not exist in posts/avatars</p>';
			$qa_content['custom'.++$c]='<p><span style="color:#00F">user=William35</span> &rarr; show only images of certain user</p>';
			$qa_content['custom'.++$c]='</div>';
			
			// CSS: make list bigger on page and style the dropdown
			$qa_content['custom'.++$c] = '<style type="text/css">table thead tr th,table tfoot tr th{background-color:#cfc;border:1px solid #CCC;padding:4px} table{background-color:#EEE;margin:30px 0 15px;text-align:left;border-collapse:collapse} td{border:1px solid #CCC;padding:1px 10px;line-height:25px}tr:hover{background:#ffc} .column1, .column2 {text-align:center; } td img{border:1px solid #DDD !important; margin-right:5px;} .listSmallImages { max-width:350px; max-height:100px; margin: 5px 0; cursor:pointer; } .delImageLink {color:#F00;} .delImageLink:visited {color:#FAA;} </style>';
			
			// jquery lightbox effect: if you click an image, it opens in a popup 
			// if you do not have a lightbox added to your theme, jquery will link the image to itself
			// see also lightbox-tutorial: http://question2answer.org/qa/17523/implement-a-lightbox-effect-for-posted-images-q2a-tutorial
			$qa_content['custom'.++$c] = '<script type="text/javascript">
			$(document).ready(function(){ 
				// check if lightbox-popup exists
				if ($("#lightbox-popup").length>0) { 
					// lightbox effect for images
					$(".listSmallImages").click(function(){
						$("#lightbox-popup").fadeIn("fast");
						$("#lightbox-img").attr("src", $(this).attr("src"));
						// center vertical
						$("#lightbox-center").css("margin-top", ($(window).height() - $("#lightbox-center").height())/2  + "px");
					});
					$("#lightbox-popup").click(function(){
						$("#lightbox-popup").fadeOut("fast");
					});
				}
				else {
					// wrap image in anchor and link to itself
					$(".listSmallImages").each(function(){
						var anchor = $("<a/>").attr({"href": this.src});
						$(this).wrap(anchor);
					});
				}
			});
			</script>';
			
			return $qa_content;
		}
		
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/