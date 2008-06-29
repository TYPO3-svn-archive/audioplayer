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
		$test = array('Hello1'=>'World1','Hello2'=>'World2',
				'autostart'=>true,'animation'=>'mussnichsein',
				'bgmain'=>'000','bg'=>'ff0ec9','rightbghover'=>'fef','leftbg'=>'0','traker'=>'F', 'buffer' => '5');
		$expected = array('width'=>290,'autostart'=>'yes','bg'=>'FF0EC9','rightbghover'=>'FFEEFF','leftbg'=>'000000');
		$this->audioplayer->init();
		$temp = $this->audioplayer->Vars;
		$this->audioplayer->Vars = $test;
		$this->audioplayer->checkVars();
		$result = $this->audioplayer->Vars;
		sort($result); sort($expected);
		self::assertEquals($result,$expected,$result);
		$this->audioplayer->Vars = $temp;
	}
	
	function test_renderVars() {
		$test = array('Hello1'=>'World1','Hello2'=>'World2');
		$temp = $this->audioplayer->Vars; 
		$this->audioplayer->Vars = $test;
		$result = $this->audioplayer->renderVars();
		self::assertEquals($result, 'Hello1: "World1", Hello2: "World2"',$result);
		$this->audioplayer->Vars = $temp;
	}

	function test_renderTracksOptions() {
		$testcases = array(
			array('hallo.mp3','',''),
			array('','hello','world'),
			array('hal lo.mp3','<bold>',''),
			array(array('http://www.this.tld/index.php?id=1','hallo.mp4'),'','')
		);
		$expectedresults = array(
			'soundFile: "hallo.mp3"',
			false,
			'soundFile: "hal%20lo.mp3", titles: "<bold>"',
			'soundFile: "http%3A%2F%2Fwww.this.tld%2Findex.php%3Fid%3D1,hallo.mp4"'
		);
		foreach ($testcases as $key => $testcase) {
			list($test_file,$test_titles,$test_artists) = $testcase;
			$result = $this->audioplayer->renderTracksOptions($test_file,$test_titles,$test_artists);
			self::assertEquals($result, $expectedresults[$key], $result);
		}		
	}
}

?>