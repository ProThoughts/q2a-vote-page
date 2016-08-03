<?php

if ( !defined( 'QA_VERSION' ) ) { // don't allow this page to be requested directly from browser
	header( 'Location: ../' );
	exit;
}

class qa_html_theme_layer extends qa_html_theme_base
{
	function body_content()
	{
		qa_html_theme_base::body_content();
	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
