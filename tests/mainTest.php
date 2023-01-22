<?php
/**
 * test for px2-error-reporter
 */
class mainTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setUp() : void{
		mb_internal_encoding('UTF-8');
	}

	/**
	 * TEST
	 */
	public function testStandard(){
		$this->assertTrue( true );
	}

}
