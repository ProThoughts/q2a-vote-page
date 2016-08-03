<?php

/*
	Plugin Name: Vote Page
	Plugin URI:
	Plugin Description: Add page of questions and answers that the user has voted.
	Plugin Version: 1.0
	Plugin Date: 2016-08-03
	Plugin Author: 38qa.net
	Plugin Author URI: http://38qa.net/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.7
	Plugin Update Check URI:
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

//Define global constants
@define( 'VOTE_PAGE_DIR', dirname( __FILE__ ) );
@define( 'VOTE_PAGE_FOLDER', basename( dirname( __FILE__ ) ) );

// process
qa_register_plugin_module('process', 'qa-vote-page-process.php', 'qa_vote_page_process', 'Vote Page Process');
// layer
// qa_register_plugin_layer('qa-vote-page-layer.php','Vote Page Layer');

/*
	Omit PHP closing tag to help avoid accidental output
*/
