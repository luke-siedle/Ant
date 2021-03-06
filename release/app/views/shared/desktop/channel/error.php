<?php
	
	/*
	 *	The error channel hosts
	 *	a different view of the 
	 *	same route for handling errors
	 * 
	 *	@package Ant
	 *	@since 0.1.0
	 */

	namespace View\Desktop\Channel\Error {
		
		use \Core\Template as Template;
		use \Core\Application as App;
		use \Core\Document as Document;
		
		function index( $request ){
			
			// Load a template from the shared space //
			$frame		= Template :: loadSharedTemplate('frame');
			
			$error		= Template :: loadSharedTemplate( 'error' );
			
			if( App :: developerMode() ){
				$errorDev = Template :: loadSharedTemplate( 'error_dev' );
				$errorDev->loadInto( $error, '__DEV__' );
			}
			
			$error->loadInto( $frame, '__CONTENT__' );
			
			// Buffer the template for output //
			Template :: setBuffer( $frame );
			
			Document :: prepare();
			
			App :: setHeaders();
			
			// Channel is always responsible for output //
			echo Template :: output();
		}
	
	}