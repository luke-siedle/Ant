<?php

	/*
	 *	Generic string functions
	 * 
	 *	@package Ant
	 *	@since 0.1.0
	 */

	namespace Library\String;
	
	function encodePassword( $str ){
		return hash( 'sha256', $str );
	}