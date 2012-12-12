<?php
	
	/*
	 *	Test the functionality 
	 *	of class Collection
	 * 
	 *	@package Ant
	 *	@type Test Suite
	 *	@since 0.1.0
	 */
	
	// Require Collection and its dependency, Underscore //
	require_once( PROJECT_ROOT . '/app/classes/collection.php' );
	require_once( dirname(PROJECT_ROOT) . '/lib/php/underscore.php' );
	
	class TestCollection extends PHPUnit_Framework_TestCase
	{
		public static $testArrayMulti		= array(
			0 => array(
				'year'		=> 1989,
				'title'		=> 'Batman'
			),
			1 => array(
				'year'		=> 1992,
				'title'		=> 'Batman Returns'
			)
		);
		
		public static $testArraySingle	= array(
			0 => array(
				'year'		=> 1989,
				'title'		=> 'Batman'
			)
		);
		
		public static $testJoin	= array(
			0 => array(
				'actor' => 'Michael Keaton'
			)
		);
		
		public function testToArrayEmpty()
		{
			$collection = new \Core\Collection;
			$this->assertEmpty( $collection->toArray() );
		}
		
		public function testToArraySingle()
		{
			$collection = new \Core\Collection( self :: $testArraySingle );
			$this->assertEquals( self :: $testArraySingle, $collection->toArray() );
		}
		
		public function testToArrayMulti()
		{
			$collection = new \Core\Collection( self :: $testArrayMulti );
			$this->assertEquals( self :: $testArrayMulti , $collection->toArray() );
		}
		
		public function testToArrayShallow()
		{
			$collection = new \Core\Collection( self :: $testArrayMulti );
			$join		= new \Core\Collection( self :: $testArraySingle );
			
			$collection->join( $join );
			
			$this->assertEquals( self :: $testArrayMulti , $collection->toArrayShallow() );
		}
		
		public function testToObjectEmpty()
		{
			$collection = new \Core\Collection;
			$this->assertEmpty( $collection->toObject() );
		}
		
		public function testToObjectSingle()
		{
			$collection = new \Core\Collection( self :: $testArraySingle );
			
			$obj = array((object) self :: $testArraySingle[0]);
			
			$this->assertEquals( $obj, $collection->toObject() );
		}
		
		public function testToObjectMulti()
		{
			$collection = new \Core\Collection( self :: $testArrayMulti );
			
			$obj = array(
				0 => (object) self :: $testArrayMulti[0],
				1 => (object) self :: $testArrayMulti[1]
			);
			
			
			$this->assertEquals( $obj , $collection->toObject() );
		}
		
		public function testEmptyInstance()
		{
			$collection = new \Core\Collection;
			$this->assertEmpty( $collection->toArray() );
		}
		
		public function testSingleRecordInstance()
		{
			$collection = new \Core\Collection( self :: $testArraySingle);
			$this->assertEquals( self :: $testArraySingle, $collection->toArray() );
		}
		
		public function testMultiRecordInstance()
		{
			$collection = new \Core\Collection( self :: $testArrayMulti );
			$this->assertEquals( self :: $testArrayMulti, $collection->toArray() );
		}
		
		public function testLength()
		{
			$collection = new \Core\Collection( self :: $testArrayMulti );
			$this->assertEquals( 2, $collection->length() );
		}
		
		public function testJoinAll(){
			
			global $testArrayMulti;
			
			$collection = new \Core\Collection( self :: $testArrayMulti, 'movies' );
			$join		= new \Core\Collection( self :: $testJoin, 'actors' );
			
			$collection->join( $join );
			
			$equalArray = self :: $testArrayMulti;
			
			foreach( $equalArray as $i => $each ){
				$equalArray[ $i ]['\actors'] = self :: $testJoin; 
			}
			
			$this->assertEquals( $equalArray , $collection->toArray() );
			
		}
		
		public function testJoinSingle(){
			
			$collection = new \Core\Collection( self :: $testArrayMulti, 'movies' );
			$join		= new \Core\Collection( self :: $testJoin, 'actors' );
			
			$collection->join( $join );
			
			$equalArray = self :: $testArrayMulti;
			
			foreach( $equalArray as $i => $each ){
				$equalArray[ $i ]['\actors'] = self :: $testJoin; 
			}
			
			$this->assertEquals( $equalArray , $collection->toArray() );
			
		}
		
		public function testUnjoin(){
			
			$collection = new \Core\Collection( self :: $testArrayMulti, 'movies' );
			
			$join		= new \Core\Collection( self :: $testJoin, 'actors' );
			
			$collection->join( $join );
			
			$collection->unjoin( 'actors' );
			
			$this->assertEquals( $collection->toArray(), self :: $testArrayMulti );	
			
		}
		
		public function testFirst(){
			$collection = new \Core\Collection( self :: $testArrayMulti );
			$this->assertEquals( self :: $testArrayMulti[0], $collection->first()->toArray() );	
		}
		
		public function testLast(){
			$collection = new \Core\Collection( self :: $testArrayMulti );
			$this->assertEquals( self :: $testArrayMulti[1], $collection->last()->toArray() );	
		}
		
		public function testAt(){
			$collection = new \Core\Collection( self :: $testArrayMulti );
			$this->assertEquals( self :: $testArrayMulti[1], $collection->at(1)->toArray() );	
		}
		
		public function testEach(){
			$collection = new \Core\Collection( self :: $testArrayMulti );
			
			$arr = array();
			
			$collection->each( function( $each ) use( & $arr ) {
				$arr[] = $each->toArray();
			});
			
			$arr2 = array();
			
			foreach( self :: $testArrayMulti as $each ){
				$arr2[] = $each;
			}
			
			$this->assertEquals( $arr, $arr2 );	
		}
		
		public function testFind(){
			
			$collection = new \Core\Collection( self :: $testArrayMulti );
			
			$searchResults = $collection->find(array(
				'title' => 'Batman Returns'
			));
			
			$this->assertEquals( $searchResults->first()->toArray(), self :: $testArrayMulti[1] );
		}
		
		public function testGetNamespace(){
			
			$collection = new \Core\Collection( self :: $testArrayMulti, 'movies' );
			
			$this->assertEquals( 'movies', $collection->getNamespace() );
		}
		
		public function testAdd(){
			
			$collection = new \Core\Collection( self :: $testArrayMulti, 'movies' );
			
			$arr = array(
				'year' => '1995',
				'title' => 'Batman Forever'
			);
			
			$collection->add( $arr );
			
			$compare = self :: $testArrayMulti;
			$compare[] = $arr;			
			
			$this->assertEquals( $compare, $collection->toArray() );
		}
		
		public function testClear(){
			
			$collection = new \Core\Collection( self :: $testArrayMulti );
			
			$collection->clear();
			
			$this->assertEmpty( $collection->toArray() );
		}
		
	}
	
	// Run the test suite //
	new TestCollection;
	
?>