<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_vote_page_process
{
	public function init_page()
	{
		if (qa_request_part(0) !== 'user' || empty(qa_request_part(2)) || qa_request_part(2) !== 'upvotes') {
			return;
		}

		require_once QA_INCLUDE_DIR.'app/cookies.php';
		require_once QA_INCLUDE_DIR.'app/format.php';
		require_once QA_INCLUDE_DIR.'app/users.php';
		require_once QA_INCLUDE_DIR.'app/options.php';
		require_once QA_INCLUDE_DIR.'db/selects.php';

		global $qa_usage;

		qa_report_process_stage('init_page');
		qa_db_connect('qa_page_db_fail_handler');

		qa_page_queue_pending();
		qa_load_state();
		qa_check_login_modules();

		if (QA_DEBUG_PERFORMANCE)
			$qa_usage->mark('setup');

		qa_check_page_clicks();
		//	Determine the identify of the user

		$handle = qa_request_part(1);

		if (!strlen($handle)) {
			$handle = qa_get_logged_in_handle();
			qa_redirect(!empty($handle) ? 'user/'.$handle : 'users');
		}

		//	Get the HTML to display for the handle, and if we're using external users, determine the userid

		if (QA_FINAL_EXTERNAL_USERS) {
			$userid = qa_handle_to_userid($handle);
			if (!isset($userid))
				return include QA_INCLUDE_DIR.'qa-page-not-found.php';

			$usershtml = qa_get_users_html(array($userid), false, qa_path_to_root(), true);
			$userhtml = @$usershtml[$userid];

		} else {
			$userhtml = qa_html($handle);
		}

		qa_set_template('user-upvotes');
		$qa_content = include VOTE_PAGE_DIR.'/user-upvotes.php';

		if (is_array($qa_content)) {
			if (QA_DEBUG_PERFORMANCE)
				$qa_usage->mark('view');

			qa_output_content($qa_content);

			if (QA_DEBUG_PERFORMANCE)
				$qa_usage->mark('theme');

			if (qa_do_content_stats($qa_content) && QA_DEBUG_PERFORMANCE)
				$qa_usage->mark('stats');

			if (QA_DEBUG_PERFORMANCE)
				$qa_usage->output();
		}

		qa_db_disconnect();
	}
}
