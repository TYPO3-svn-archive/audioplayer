<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Peter Schuster <typo3@peschuster.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
define('T3UNIT_TESTING','1');

require_once (t3lib_extMgm::extPath('audioplayer').'class.tx_audioplayer.php');

 /**
  * Testcase for checking the audioplayer extension
  *
  * @author		Peter Schuster <typo3@peschuster.de>
  * @package	TYPO3
  * @subpackage tx_audioplayer
  */
class tx_audioplayer_testcase extends tx_t3unit_testcase {
	var $audioplayer;
	
	function __construct() {
		$this->audioplayer = new tx_audioplayer;
	}
	
	function test_convBoolean() {
		$testPatterns = array(
			'yes'=>array(true,'yes',1,'1','true','TruE','YeS','YES'),
			'no'=>array(false,'no',0,'0','false','FaLse','NO','nO')
		);
		foreach ($testPatterns as $k => $value) {
			foreach ($value as $v) {
				$result = $this->audioplayer->convBoolean($v);
				self::assertEquals($result, $k, $v.' ('.gettype($v).'): '.$result);
			}
		}
		$result = $this->audioplayer->convBoolean('something');
		self::assertEquals($result, false, 'something: '.$result);
		$result = $this->audioplayer->convBoolean('0815');
		self::assertEquals($result, false, '0815 (string): '.$result);
		$result = $this->audioplayer->convBoolean(815);
		self::assertEquals($result, false, '815 ('.gettype(815).'): '.$result);
	}

	function test_checkVars() {
		
	}
	
	function test_renderVars() {
		$test = array('Hello1'=>'World1','Hello2'=>'World2');
		$cache = $this->audioplayer->Vars; 
		$this->audioplayer->Vars = $test;
		$result = $this->audioplayer->renderVars();
		self::assertEquals($result, 'Hello1: "World1", Hello2: "World2"',$result);
		$this->audioplayer->Vars = $cache;
	}
}

?>