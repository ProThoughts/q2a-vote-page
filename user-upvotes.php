<?php

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}

	require_once QA_INCLUDE_DIR.'db/selects.php';
	require_once QA_INCLUDE_DIR.'app/format.php';


//	$handle, $userhtml are already set by qa-page-user.php - also $userid if using external user integration


//	Find the recent activity for this user

	$loginuserid = qa_get_logged_in_userid();
	$identifier = QA_FINAL_EXTERNAL_USERS ? $userid : $handle;

	list($useraccount, $questions, $answerqs) = qa_db_select_with_pending(
		QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_account_selectspec($handle, false),
		qa_user_upvotes_qs_selectspec($loginuserid, $identifier, qa_opt_if_loaded('page_size_activity')),
		qa_user_upvotes_a_qs_selectspec($loginuserid, $identifier)
	);

	if (!QA_FINAL_EXTERNAL_USERS && !is_array($useraccount)) // check the user exists
		return include QA_INCLUDE_DIR.'qa-page-not-found.php';


//	Get information on user references

	$questions = qa_any_sort_and_dedupe(array_merge($questions, $answerqs));
	$questions = array_slice($questions, 0, qa_opt('page_size_activity'));
	$usershtml = qa_userids_handles_html(qa_any_get_userids_handles($questions), false);


//	Prepare content for theme

	$qa_content = qa_content_prepare(true);

	if (count($questions))
		$qa_content['title'] = qa_lang_html_sub('qa_vote_page/upvotes_by_x', $userhtml);
	else
		$qa_content['title'] = qa_lang_html_sub('qa_vote_page/no_upvotes_by_x', $userhtml);


//	Recent activity by this user

	$qa_content['q_list']['form'] = array(
		'tags' => 'method="post" action="'.qa_self_html().'"',

		'hidden' => array(
			'code' => qa_get_form_security_code('vote'),
		),
	);

	$qa_content['q_list']['qs'] = array();

	$htmldefaults = qa_post_html_defaults('Q');
	$htmldefaults['whoview'] = false;
	$htmldefaults['voteview'] = false;
	$htmldefaults['avatarsize'] = 0;

	foreach ($questions as $question) {
		$qa_content['q_list']['qs'][] = qa_any_to_q_html_fields($question, $loginuserid, qa_cookie_get(),
			$usershtml, null, array('voteview' => false) + qa_post_html_options($question, $htmldefaults));
	}


//	Sub menu for navigation in user pages

	$ismyuser = isset($loginuserid) && $loginuserid == (QA_FINAL_EXTERNAL_USERS ? $userid : $useraccount['userid']);
	$qa_content['navigation']['sub'] = qa_user_sub_navigation($handle, 'upvotes', $ismyuser);


	return $qa_content;

	function qa_user_upvotes_qs_selectspec($voteuserid, $identifier, $count=null, $start=0)
	{
		$count=isset($count) ? min($count, QA_DB_RETRIEVE_QS_AS) : QA_DB_RETRIEVE_QS_AS;

		$selectspec=qa_db_posts_basic_selectspec($voteuserid);

		$selectspec['source'].=" WHERE ^posts.postid IN (SELECT postid FROM ^uservotes WHERE userid=".(QA_FINAL_EXTERNAL_USERS ? "$" : "(SELECT userid FROM ^users WHERE handle=$ LIMIT 1)")." AND vote = 1) AND type='Q' ORDER BY ^posts.created DESC LIMIT #,#";
		array_push($selectspec['arguments'], $identifier, $start, $count);
		$selectspec['sortdesc']='created';

		return $selectspec;
	}

	function qa_user_upvotes_a_qs_selectspec($voteuserid, $identifier, $count=null, $start=0)
	{
		$count=isset($count) ? min($count, QA_DB_RETRIEVE_QS_AS) : QA_DB_RETRIEVE_QS_AS;

		$selectspec=qa_db_posts_basic_selectspec($voteuserid);

		qa_db_add_selectspec_opost($selectspec, 'aposts');

		$selectspec['columns']['oupvotes']='aposts.upvotes';
		$selectspec['columns']['odownvotes']='aposts.downvotes';
		$selectspec['columns']['onetvotes']='aposts.netvotes';

		$selectspec['source'].=" JOIN ^posts AS aposts ON ^posts.postid=aposts.parentid".
			" JOIN (SELECT postid FROM ^posts WHERE ".
			" postid IN (SELECT postid FROM ^uservotes WHERE userid=".(QA_FINAL_EXTERNAL_USERS ? "$" : "(SELECT userid FROM ^users WHERE handle=$ LIMIT 1)"). " AND vote = 1)".
			" AND type='A' ORDER BY created DESC LIMIT #,#) y ON aposts.postid=y.postid WHERE ^posts.type='Q'";

		array_push($selectspec['arguments'], $identifier, $start, $count);
		$selectspec['sortdesc']='otime';

		return $selectspec;
	}

/*
	Omit PHP closing tag to help avoid accidental output
*/
