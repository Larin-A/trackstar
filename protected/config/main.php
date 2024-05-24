<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'TrackStar',
	'id' => 'TrackStar',
	'homeUrl' => '/project',
	'theme' => 'newtheme',

	// set target language to be Russian
	'language' => 'ru_ru',

	// set source language to be English
	'sourceLanguage' => 'en_us',

	// preloading 'log' component
	'preload' => array('log'),

	// autoloading model and component classes
	'import' => array(
		'application.models.*',
		'application.components.*',
	),

	'modules' => array(

		'gii' => array(
			'class' => 'system.gii.GiiModule',
			'password' => 'PassPaGii11!',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters' => array('127.0.0.1', '::1'),
		),
		'admin',
	),

	// application components
	'components' => array(

		'user' => array(
			// enable cookie-based authentication
			'allowAutoLogin' => true,
		),

		// uncomment the following to enable URLs in path-format
		/*
'urlManager'=>array(
'urlFormat'=>'path',
'rules'=>array(
'<controller:\w+>/<id:\d+>'=>'<controller>/view',
'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
),
),
*/

		// database settings are configured in database.php
		'db' => require (dirname(__FILE__) . '/database.php'),

		'errorHandler' => array(
			// use 'site/error' action to display errors
			'errorAction' => YII_DEBUG ? null : 'site/error',
		),

		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'error, warning',
				),
				// uncomment the following to show log messages on web pages
/*
array(
	'class'=>'CWebLogRoute',
),
*/
			),
		),

		'authManager' => array(
			'class' => 'CDbAuthManager',
			'connectionID' => 'db',
			'itemTable' => 'tbl_auth_item',
			'assignmentTable' => 'tbl_auth_assignment',
			'itemChildTable' => 'tbl_auth_item_child',
		),

		'urlManager' => array(
			'urlFormat' => 'path',
			'rules' => array(
				'<pid:\d+>/commentfeed' => array(
					'comment/feed',
					'urlSuffix' => '.xml',
					'caseSensitive' => false
				),
				'commentfeed' => array(
					'comment/feed',
					'urlSuffix' => '.xml',
					'caseSensitive' => false
				),
				'issue/<id:\d+>/*' => 'issue/view',
				'register' => 'user/create',
			),
			'showScriptName' => false,
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params' => array(
		// this is used in contact page
		'adminEmail' => 'webmaster@example.com',
	),
);
