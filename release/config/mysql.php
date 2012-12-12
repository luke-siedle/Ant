<?php
	
	/**
	 *	MySQL configuration
	 *	local, staging, and remote.
	 *	
	 *	@package Core
	 *	@subpackage Configuration
	 *	@since 0.1.0	 
	 */
	namespace Core;
	
	// Local mysql
	$config['mysql_local']['host']		= 'localhost';
	$config['mysql_local']['db']		= 'ant';
	$config['mysql_local']['username']	= 'root';
	$config['mysql_local']['password']	= '';
	
	// Staging mysql //
	$config['mysql_stage']['host']		= '';
	$config['mysql_stage']['db']		= 'ant';
	$config['mysql_stage']['username']	= 'root';
	$config['mysql_stage']['password']	= '';
	
	// Production mysql //
	$config['mysql_remote']['host']		= '';
	$config['mysql_remote']['db']		= 'ant';
	$config['mysql_remote']['username']	= 'root';
	$config['mysql_remote']['password']	= '';
	
	// Set the table prefix (can be blank) //
	$config['mysql_table_prefix']		= 'ant_';
	
	// Apply //
	Configuration :: set( $config );