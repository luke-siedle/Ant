<?php
	
	/**
	 *	The Router exists to organise
	 *	requests and give them a purpose.
	 * 
	 *	@package Core
	 *	@subpackage Router
	 *	@since 0.1.0
	 */
	namespace Core {
		
		use \Core\Application as App;
		
		Class Router {
			
			public static	$routeXml, 
							$client,
							$viewDir,
							$controlDir,
							$namespace,
							$context		= 'home',
							$routes			= array(),
							$path			= '',							
							$routeVars		= array(),
							$channel		= false,
							$stopRouting	= false;
			
			/**
			 *	Set some paths critical for 
			 *	other areas of application
			 *	which may be initiated prior
			 *	to routing.
			 * 
			 *	@since 0.2.1
			 *	
			 */
			
			public static function setPaths( $client ){
				// Set some useful vars and paths //
				self :: $client			= $client;
				self :: $viewDir		= 'app/views/shared/' . $client . '/';
				self :: $controlDir		= 'app/views/shared/controllers/' . $client . '/';
				self :: $namespace		= "\View\\" . $client;
				
				// Create the "true" query string //
				self :: setQueryString();
				
				// Initializes request vars //
				Request :: initialize( $_GET );
			}
			
			/**
			 *	The principal function to
			 *	start routing the application.
			 *	Usually used from a shortcut
			 *	within @subpackage Application
			 *	
			 *	@param string $client The client 'web' 'mobile'
			 *	
			 *	@since 0.1.0
			 */
			public static function route(){
				
				// Load the main route handlers //
				self :: $routes = require_once('app/routes/' . self :: $client . '/index.php');
				
				self :: $context = Application :: get()->context;
				
				$mapLoaded = false;
				
				// Load the main map //
				$routeMapMain = 'app/routes/' . self :: $client . '/main.php';
				if( file_exists($routeMapMain) ){
					require_once( $routeMapMain );
				} else {
					new Error( 404, 'route_not_found' );
				}
				
				// Load the contextual map if it exists //
				$routeMap = 'app/routes/' . self :: $client . '/' . self :: $context . '.php';
				
				if( file_exists( $routeMap )){
					require_once( $routeMap );
				}
				
				self :: planRoute();
				
				// Check if a channel is in use ( .../?channel=ajax ) //
				if( self :: $channel = self :: loadChannel() ){
					// Just load the channel and stop (e.g. ajax) //
					return;
				}
				
				// Load the route index, the principal file for handling the view //
				self :: loadRouteIndex();
			}
			
			/**
			 *	Set the query string which
			 *	would have been misinterpreted due	
			 *	to .htaccess creating its own.
			 * 
			 *	@since 0.1.0
			 */
			public static function setQueryString(){
				$array = parse_url( $_SERVER['REQUEST_URI'] );
				parse_str( $array['query'], $parts );
				$_REQUEST = array_merge( $_REQUEST, $parts );
				$_GET = $parts;
				self :: $path = $array['path'];
			}
			
			/**
			 *	Attempt to load a channel
			 *	if it exists. Channeling is used
			 *	when the same route is needed
			 *	but different output is required.
			 *	
			 *	@param string $channel The channel
			 *	'ajax'
			 *	
			 *	@since 0.1.0
			 */
			public static function loadChannel( $channel = null ){
				
				if( is_null($channel) ){
					$channel = $_GET['channel'];
				}
				if( strlen($channel) > 0 ){
					
					if( file_exists(
						$channelFile = self :: $viewDir . 'channel/' . $channel . '.php')
					){
						require( $channelFile );
						
						$fn = '\\' . implode('\\', array(
							'View',
							self :: $client, 
							'Channel',
							$channel,
							'Index'
						));
						
						$fn( self :: getRouteVars() );
						
						return $channel;
					} else {
						throw new \Exception(
							ucwords( $channel) . ' channel does not exist in ' . $channelFile 
						);
					}
				}
				return false;
			}
			
			/**
			 *	Attempt to reset and load the specified
			 *	channel, for example, during an error 404.
			 *	
			 *	@param string $channel The channel, 'ajax'
			 * 
			 *	@since 0.1.0
			 */
			public static function resetChannel( $channel ){
				self :: $channel = self :: loadChannel( $channel );
			}
			
			/**
			 *	Get the current channel
			 *	in use
			 * 
			 *	@since 0.1.0
			 *	@return string The current channel
			 */
			public static function getChannel(){
				return self :: $channel;
			}
			
			/**
			 *	Get the current public path 
			 *	to the application, i.e. not 
			 *	necessarily the root
			 *	
			 *	@since 0.1.0
			 *	@return string Current public path to application
			 */
			public static function getAppPath(){
				$http = $_SERVER['HTTPS'];
				if( !$http ){
					$http = 'http://';
				}
				return  $http . $_SERVER['SERVER_NAME'] . self :: $path;
			}
			
			/**
			 *	Get the public path to the root
			 *	of the application
			 * 
			 *	@since 0.1.0
			 *	@return string Public path to application root
			 */
			public static function getPublicRoot( $https = false ){
				$http = 'http://';
				if( $https ){
					$http = 'https://';
				}
				return  $http . $_SERVER['SERVER_NAME'] . PUBLIC_ROOT;
			}
			
			/**
			 *	Plan the route if a route map exists,
			 *	using the variables created from the map.
			 *	Required variables include:
			 *	module, template, frame, doctitle
			 *	
			 *	Context is determined here.
			 *	
			 *	@since 0.1.0
			 */
			public static function planRoute(){
				
				// Load main vars //
				self :: $routeVars = array_merge(array(
					'context'	=> self :: $context
				), \Route\Main\Index() );
				
				$tokens = array(
					':string' => '([a-zA-Z]+)',
					':number' => '([0-9]+)',
					':alpha'  => '([a-zA-Z0-9-_]+)'
				);
				
				$path_info = '/' . self :: getRequestURI();
				
				// Borrowed from ToroPHP //
				foreach (self :: $routes as $pattern => $handler) {
					$pattern = strtr($pattern, $tokens);
					if (preg_match('#^/?' . $pattern . '/?$#', $path_info, $matches)) {
						$handlerName = $handler;
						$regex_matches = $matches;
						$routeFound = true;
						break;
					}
				}
				
				// 404 not found //
				if( !function_exists($handlerName) ){
					new Error( 404, 'route_not_found' );
				}
				
				if( ! $routeFound ){
					new Error( 404, 'route_not_found' );
				}
				
				if( ! $handlerVars = $handlerName( $regex_matches )){
					new Error( 404, 'route_not_found' );
				}
				
				// Create the vars //
				self :: $routeVars		= (object) array_merge(
					self :: $routeVars, 	
					$handlerVars
				);
				
			}
			
			
			/**
			 *	Set the route context, 'article' 'user'
			 *	
			 *	@param string $context
			 * 
			 *	@since 0.1.0
			 */
			public static function setRouteContext( $context ){
				self :: $context = $context;
			}
			
			/**
			 *	Load the route index based on the client.
			 *	This is contained in the shared/views
			 *	folder and is required. The function
			 *	must always be namespaced accordingly.
			 *	This allows for multiple indexes
			 *	to be declared if required.
			 *	
			 *	@since 0.1.0
			 *	@return mixed The return variable of
			 *	the index function
			 */
			public static function loadRouteIndex(){
				
				require('app/views/shared/' . self :: $client . '/index.php');
				// Execute //
				$fn = self :: $namespace . "\\index";
				return $fn( self :: getRouteVars() );
			}
			
			/**
			 *	Load the route view based on the client,
			 *	and the current context. The module/view
			 *	to be called is set within the route map.
			 *	The function must always be namespaced 
			 *	accordingly. This allows for multiple indexes
			 *	to be declared if required.
			 *	
			 *	Examples of context:
			 *	'home' 'news' 'about' 'user' 'article'
			 *	
			 *	This function needs to be manually 
			 *	called from within the index.
			 *	
			 *	@since 0.1.0
			 *	@return mixed | CollectionSet The collections
			 *	of the view to be passed to templating.
			 */
			public static function loadRouteView(){
				
				$mod = self :: getModule();				
				
				$view = ('app/views/context/' 
							. self :: $client . '/'
							. self :: getContext() . '/'
							. self :: getModule() . '.php');
				
				// If no view is found this is an internal error
				// since the route has specified one should exist //
				if( ! file_exists($view) ){
					new Error( '500', 'view_not_found' );
				}
				
				// Include and execute //
				require( $view );
				$fn = self :: $namespace . "\\" . self :: getContext() . '\\' . self :: getModule();
				return $fn( self :: getRouteVars() );
				
			}
			
			/**
			 *	Load the shared view based on the client, 
			 *	regardless of context. The shared view
			 *	would contain functionality that 
			 *	is global, and would usually be named 
			 *	something like 'frame.php'
			 *	
			 *	This function needs to be manually 
			 *	called from within the index.
			 *	
			 *	@param string $view The view function
			 *	@param mixed $contextView The values return
			 *	by the view, allowing them to be accessed	
			 *	in the shared view, e.g. 'frame.php'
			 *	@param string $client Force a specific client	
			 *	view.	
			 *	
			 *	@since 0.1.0
			 *	@return mixed | CollectionSet The collections
			 *	of the view to be passed to templating.
			 */
			public static function loadSharedView( $view, $contextView = null, $client = null ){
				
				if( !$client ){
					$client = self :: $client; 
				}
				
				// If the file doesn't exist, throw internal 500. //
				if( !file_exists($sharedView = 'app/views/shared/' 
							. $client . '/'
							. $view . '.php')){
					
					new Error('500', 'shared_view_not_found');
					
				}
				
				require( $sharedView );
				
				$fn = '\View\\' . $client . "\\" . $view;
				return $fn( self :: getRouteVars(), $contextView );
				
			}
			
			/**
			 *	Get the route variables, as defined
			 *	in route.xml. Example:
			 *	'view' 'client'
			 *	
			 *	@since 0.1.0
			 *	@return object The route variables
			 */
			public static function getRouteVars(){
				return self :: $routeVars;
			}
			
			/**
			 *	Get the original request URI
			 * 			 
			 *	@since 0.2.0
			 *	@return string The request URI
			 */
			public static function getRequestURI(){
				return Application :: get()->requestURI;
			}
			
			/**
			 *	Get the current module, as
			 *	defined in route.xml.
			 * 			 
			 *	@since 0.1.0
			 *	@return string The module
			 */
			public static function getModule(){
				return self :: $routeVars->module;
			}
			
			/**
			 *	Get the current template file, as
			 *	defined in route.xml.
			 * 			 
			 *	@since 0.1.0
			 *	@return string The template
			 */
			public static function getTemplate(){
				return self :: $routeVars->template;
			}
			
			/**
			 *	Get the current request context,
			 *	as defined in route.xml.
			 *	Example : 'home' 'news' 'about'
			 * 			 
			 *	@since 0.1.0
			 *	@return string The request context
			 */
			public static function getContext(){
				return self :: $routeVars->context;
			}
			
			/**
			 *	Get the document title
			 *	set inside the route.
			 * 			 
			 *	@since 0.1.0
			 *	@return string The title
			 */
			public static function getDocTitle(){
				return self :: $routeVars->doctitle;
			}
			
			/**
			 *	Set the document title
			 *	in special cases, example '404 error'
			 *	
			 *	@param string $title The document title
			 *  			 
			 *	@since 0.1.0
			 *	@return string The title
			 */
			public static function setDocTitle( $title ){
				self :: $routeVars->doctitle = $title;
			}
			
			/**
			 *	Get the controllers set
			 *	inside the route.
			 * 	
			 *	@since 0.1.0
			 *	@return array The controller names
			 */
			public static function getControllers(){
				return self :: $routeVars->controllers;
			}
			
		}
		
	}