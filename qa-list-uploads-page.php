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
		
			/* SETTINGS */
			$lastdays = 3; 				// show new uploads from last x days
			$creditDeveloper = true;	// leave true if you like this plugin, it sets one hidden link to my q2a-forum from the new-uploads-page only
			
			/* start */
			$qa_content=qa_content_prepare();

			// page title
			$qa_content['title'] = qa_lang_html('qa_list_uploads_lang/page_title'); 

			// counter for custom html output
			$c = 2;
			
			// required for qa_get_blob_url()
			require_once QA_INCLUDE_DIR.'qa-app-blobs.php';
			
			// query blobs of last x days
			$queryRecentUploads = qa_db_query_sub("SELECT blobid,format,content,userid,created
											FROM `^blobs`
											WHERE created > NOW() - INTERVAL ".$lastdays." DAY
											ORDER BY created DESC;"); // LIMIT 0,100
											
			// initiate output string
			$listAllUploads = "<table> <thead><tr><th class='column1'>".qa_lang_html('qa_list_uploads_lang/upload_date')."</th>  <th class='column1'>".qa_lang_html('qa_list_uploads_lang/media_item')."</th> <th class='column2'>".qa_lang_html('qa_list_uploads_lang/upload_by_user')."</th> </tr></thead>";
			$d = 0;
			while ( ($blobrow = qa_db_read_one_assoc($queryRecentUploads,true)) !== null ) {
				$currentUser = $blobrow['userid'];
				$userrow = qa_db_select_with_pending( qa_db_user_account_selectspec($currentUser, true) );
				
				// if you do not have a lightbox added to your theme, use version A and comment out B
				// see also lightbox-tutorial: http://question2answer.org/qa/17523/implement-a-lightbox-effect-for-posted-images-q2a-tutorial
				
				// A: without lightbox -> open in new window
				// $listAllUploads .= "<tr><td>".substr($blobrow['created'],0,16)."</td> <td><a target='_blank' href='".qa_get_blob_url($blobrow['blobid'])."'><img class='listSmallImages' src='".qa_get_blob_url($blobrow['blobid'])."' \></a></td> <td></td> </tr>";
				
				// B: with lightbox -> open image in popup
				$listAllUploads .= "<tr><td>".substr($blobrow['created'],0,16)."</td> <td><img class='listSmallImages' src='".qa_get_blob_url($blobrow['blobid'])."' \></td> <td>". qa_get_user_avatar_html($userrow['flags'], $userrow['email'], $userrow['handle'], $userrow['avatarblobid'], $userrow['avatarwidth'], $userrow['avatarheight'], qa_opt('avatar_users_size'), false) ."<br />". qa_get_one_user_html($userrow['handle'], false) ."</td> </tr>";
			}
			$listAllUploads .= "</table>";

			
			/* output into theme */
			$qa_content['custom'.++$c]='<div class="listuploads" style="border-radius:0; padding:0; margin-top:-2px;">';
			
			$qa_content['custom'.++$c]= $listAllUploads;
			
			$qa_content['custom'.++$c]='</div>';
			
			// make list bigger on page and style the dropdown
			$qa_content['custom'.++$c] = '<style type="text/css">table thead tr th,table tfoot tr th{background-color:#cfc;border:1px solid #CCC;padding:4px} table{background-color:#EEE;margin:30px 0 15px;text-align:left;border-collapse:collapse} td{border:1px solid #CCC;padding:1px 10px;line-height:25px}tr:hover{background:#ffc} .column1, .column2 {text-align:center; } td img{border:1px solid #DDD !important; margin-right:5px;} .listSmallImages { max-width:350px; max-height:100px; margin: 5px 0; cursor:pointer; } </style>';
			
			// jquery effect if click on image
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
			});
			</script>';
			
			// as I said, this is one chance to say thank you
			if($creditDeveloper) {
				$qa_content['custom'.++$c] = "<a style='display:none' href='http://www.gute-mathe-fragen.de/'>Gute Mathe-Fragen! * Bestes Mathe-Forum</a>";
			}
			
			// custom css for qa-main
			$qa_content['custom'.++$c] = '<style type="text/css">.qa-main { margin:20px 0 0 60px; width:640px; }</style>';
			
			return $qa_content;
		}
		
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/