<?php
	
	/**
	 *	
	 *	All requests are 
	 *	interpreted here
	 *	
	 *	@package Ant
	 *	@since 0.1.0
	 */

	// Errors //
	error_reporting( 
		E_ERROR | 
			E_WARNING | 
				E_RECOVERABLE_ERROR 
	);
	
	// Check the PHP version //
	if( (float)substr(phpversion(), 0, 3) < 5.3 ){
		throw new Exception('You need PHP 5.3.0+ to run this framework.');
	}
	
	// Version //
	define( 'VERSION', '0.2.0' );
	
	// Application root //
	define( 'APPLICATION_ROOT', __DIR__ );
	
	// Document root //
	define( 'DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] );
	
	// Some string hacking to detect public root, 
	// Local or remote (http, https) //
	$publicRoot = str_replace( 
		$_GET['request'], 
		'', 
		$_SERVER['REQUEST_URI']
	);

	if( $pos = strpos( $publicRoot, '?' ) ){
		$publicRoot = substr( $publicRoot, 0, $pos );
	}
	
	define( 'PUBLIC_ROOT', $publicRoot );
	
	// Library path //
	define( 'LIB_PATH', dirname( __DIR__ ) . '/lib' );
	
	// Initial requires //
	require( 'app/classes/application.php' );
	require( 'app/classes/configuration.php');
	
	use Core\Application as App;
	
	// Perform basic initialization/configuration //
	App :: initialize();
	
	// Determine if environment is local/remote //
	App :: setEnvironment();
	
	// Determine the client based on variables, like request, headers //
	App :: setClient();
	
	// Set client specific settings //
	App :: setClientSettings();
	
	// Set the current language, based on user's session, cookies //
	App :: setLanguage();
	
	// Set the current theme (for templates) //
	App :: setTheme( 'default' );
	
	// Detect region from Ip Address, set the timezone and date //
	App :: setLocale();
	
	// Allocate the resources as required by the client and current context //
	App :: allocateResources();
	
	// Perform router tasks (set current channel, shared and contextual view)  //
	App :: route();
	
	// Set the headers, set within the current route //
	App :: setHeaders();
	
	// Generate output, post-output, and release any resources //
	App :: flush();