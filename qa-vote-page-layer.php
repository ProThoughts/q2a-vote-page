<?php

if ( !defined( 'QA_VERSION' ) ) { // don't allow this page to be requested directly from browser
	header( 'Location: ../' );
	exit;
}

class qa_html_theme_layer extends qa_html_theme_base
{
	public function head()
	{
		$this->add_user_upvotes_sub_nav();
		qa_html_theme_base::head();
	}

	private function add_user_upvotes_sub_nav()
	{
		$content = &$this->content;

		if ( isset( $content['navigation']['sub']['profile'] ) ) {
			//means the control is on the profile page , so display here the user blogs menu
			$sub_nav = &$content['navigation']['sub'];
			$handle = qa_request_part(1);
			$this->user_sub_navigation( $sub_nav, $handle ? $handle : qa_get_logged_in_handle() );
		}

	}

	private function user_sub_navigation(&$sub_nav, $handle)
	{
		$nav = array(
			'upvotes' => array(
				'label'	=> qa_lang_html('qa_vote_page/upvotes_posts'),
				'url'	  => qa_path_html('user/'.$handle.'/upvotes'),
				'selected' => qa_request_part(2) === 'upvotes' ? true : false,
			)
		);

		if (isset($sub_nav['user_blogs'])) {
			qa_array_insert($sub_nav, 'user_blogs', $nav);
		} else {
			$sub_nav['upvotes'] = $nav['upvotes'];
		}

	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
