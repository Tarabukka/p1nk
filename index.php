<?php
	// Define variables
	define('TRUE_DOCROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
	define('APPLICATION_PATH', TRUE_DOCROOT . 'application' . DIRECTORY_SEPARATOR);
	define('CONTROLLERS_PATH', APPLICATION_PATH . 'controllers' . DIRECTORY_SEPARATOR);
	define('VIEWS_PATH', APPLICATION_PATH . 'views' . DIRECTORY_SEPARATOR);
	define('MODELS_PATH', APPLICATION_PATH . 'models' . DIRECTORY_SEPARATOR);
	define('MODULES_PATH', APPLICATION_PATH . 'modules' . DIRECTORY_SEPARATOR);
	define('ON_PAGE', true);
	
	// Load Pink library
	require_once('application/pink.php');
	
	// Run application
	Pink::Bootstrap();