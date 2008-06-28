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

require_once(PATH_tslib.'class.tslib_pibase.php');

 /**
  * 'audioplayer' extension class
  *
  * The 'audioplayer' extension provides an api for other extensions to
  * show an mp3 flash audio player (1pixelout.net WordPress player)
  *
  * $Id:
  *
  * @author		Peter Schuster <typo3@peschuster.de>
  * @package		TYPO3
  * @subpackage 	tx_audioplayer
  */
class tx_audioplayer {
	var $prefixId		= 'tx_audioplayer';		// Same as class name
	var $scriptRelPath	= 'class.tx_audioplayer.php';	// Path to this script relative to the extension dir.
	var $extKey			= 'audioplayer';	// The extension key.
	var $flashFile;
	var $configJS;
	var $defaultVars;
	var $Vars;


	function init() {

		$this->flashFile = t3lib_extMgm::siteRelPath($this->extKey).'res/player.swf';
		$this->configJS= t3lib_extMgm::siteRelpath($this->extKey).'res/audio-player.js';

		$this->defaultVars = array(
			'autostart' => 'no', 'loop' => 'no', 'animation' => 'yes', 'remaining' => 'no', 'noinfo' => 'no',
			'initialvolume' => 60, 'buffer' => 5, 'encode' => 'no', 'checkpolicy' => 'no', 'width' => 290,
			'transparentbg' => 'no', 'pagebg' => '',
			'bg' => 'E5E5E5', 'leftbg' => 'CCCCCC', 'lefticon' => '333333', 'voltrack' => 'F2F2F2',
			'volslider' => '666666', 'rightbg' => 'B4B4B4', 'rightbghover' => '999999', 'righticon' => '333333',
			'righticonhover' => 'FFFFFF', 'loader' => '009900', 'track' => 'FFFFFF',
			'tracker' => 'DDDDDD', 'border' => 'CCCCCC', 'skip' => '666666', 'text' => '333333'
		);
		$this->Vars = array();
	}

	/**
	 * Set the options for flashfile
	 * see manual for complete option reference
	 *
	 * @param	array		$options: array with options
	 * @return	boolean
	 */
	function setOptions($options=array()) {
		if (function_exists('array_intersect_key')) { //function 'array_intersect_key' is only availabe for php >= 5.1.0
			$options = array_intersect_key($options,$this->defaultVars);
		}
		if (is_array($options)) {
			$this->Vars = array_merge(is_array($this->Vars) ? $this->Vars : array(), $options);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adjust colors of the flashfile
	 * see manual for complete option reference
	 *
	 * @param	array		$options: array with options
	 * @return	boolean
	 */
	function setColors($options=array()) {
		return $this->setOptions($options);
	}

	/**
	 * Returns code for AudioFlashPlayer
	 *
	 * @param	string		$file: path to the mp3 file, multiple files can be passedthrough in an array  
	 * @param	integer		$playerId: ID of the player (only needed, when more then one player is used on one page)
	 * @param	string		$titles: Title to be shown in player, mutiple titles for multiple files as array
	 * @param	string		$artists: Artist to be shown in player, mutiple artists for multiple files as array
	 * @return	string		code for Flashplayer, which can be placed on website
	 */
	function getFlashPlayer($file, $playerId=1, $titles='', $artists='') {
		$this->checkVars();
		$renderedVars = $this->renderVars();
		$renderdTracksOptions = $this->renderTracksOptions($file, $titles, $artists);
		
		if ($renderdTracksOptions === false) {
			return 'no file to play';
		}
		
		$GLOBALS['TSFE']->additionalHeaderData['tx_audioplayer1'] = '<script type="text/javascript" src="'.$this->configJS.'"></script>';		
		$GLOBALS['TSFE']->additionalHeaderData['tx_audioplayer2'] = '<script type="text/javascript">AudioPlayer.setup("'.$this->flashFile.'", {'.$renderedVars.'});</script>';
		$content .= 'AudioPlayer.embed("audioplayer_'.$playerId.'", {';
		$content .= $renderdTracksOptions;
		$content .= '});';

		return '<div id="audioplayer_'.$playerId.'">Alternative content</div>
		'.t3lib_div::wrapJS($content);
	}

	/**
	 * Checks all option/color input values and converts them to valid values
	 * keys not present in $this->defaultVars are sorted out
	 *
	 * @return	boolean		sucess of function
	 */
	function checkVars() {
		$result = array();
		foreach ($this->Vars as $key => $value) {
			if (in_array($key, array_keys($this->defaultVars))) {
				if (in_array($this->defaultVars[$key], array('yes','no'))) {
					$result[$key] = $this->convBoolean($this->Vars[$key]);
					if ($result[$key] === false) unset($result[$key]);
				} elseif (is_int($this->defaultVars[$key])) {
					$result[$key] = intval($this->Vars[$key]);
					if ($result[$key] === null) unset($result[$key]);
				} else {
					$result[$key] = substr(strtoupper(strval($this->Vars[$key])),0,6);
					if (empty($result[$key])) unset($result[$key]);
				}
			}
		}
		if (!isset($result['width'])) $result['width'] = $this->defaultVars['width'];
		$this->Vars = $result;
		unset($result);
		return true;
	}

	/**
	 * Converts values like '1', 'True', true to expected boolean input of player.swf (='yes'/'no')
	 *
	 * @param	mixed		$input: some unknow input, expected to be boolean
	 * @return	string		'yes' or 'no'
	 */
	function convBoolean($input) {
		if (gettype($input)==='boolean') {
			return $input ? 'yes' : 'no';
		} elseif (gettype($input)==='integer') {
			if ($input === 1) {
				return 'yes';
			} elseif ($input === 0) {
				return 'no';
			}
		}
		$testAsString = strtolower(strval($input));
		switch ($testAsString) {
			CASE 'true':
			CASE 'yes':
			CASE '1':
				$output = 'yes';
				break;
			CASE 'no':
			CASE 'false':
			CASE '0':
				$output = 'no';
				break;
			default:
				$output = false;
				break;
		}
		return $output;
	}

	/**
	 * Renders 'soundFile', 'titles' and 'artists' variables for output
	 *
	 * @param	string/array		$file: filename(s) for output
	 * @param	string/array		$titles: title(s) for output
	 * @param	string/array		$artists: artist(s) for output
	 * @return	string
	 */
	function renderTracksOptions($file, $titles, $artists) {
		$content = '';
		if(!empty($file)) {
			if(is_array($file)) {
				$content .= 'soundFile: "'.implode(',',$file).'"';
			} else {
				$content .= 'soundFile: "'.$file.'"';
			}
		} else {
			return false;
		}
		if(!empty($titles)) {
			if(is_array($titles)) {
				$content .= ', titles: "'.implode(',',$titles).'"';
			} else {
				$content .= ', titles: "'.$titles.'"';
			}
		}
		if(!empty($artists)) {
			if(is_array($artists)) {
				$content .= ', artists: "'.implode(',',$artists).'"';
			} else {
				$content .= ', artists: "'.$artists.'"';
			}
		}
		return $content;
	}
	
	/**
	 * Renders $this->Vars for output
	 *
	 * @return	string		rendered array
	 */
	function renderVars() {
		$result = array();
		foreach ($this->Vars as $key => $value) {
			 $result[] = $key.': '.(is_int($value) ? $value : '"'.$value.'"');
		}
		return implode(', ',$result);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/audioplayer/class.tx_audioplayer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/audioplayer/class.tx_audioplayer.php']);
}

?>