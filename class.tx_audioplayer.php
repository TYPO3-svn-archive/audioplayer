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

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   55: class tx_audioplayer
 *   71:     function init()
 *   94:     function setOptions($options = array())
 *  115:     function setColors($options = array())
 *  129:     function getFlashPlayer($file, $playerId = 1, $titles = '', $artists = '')
 *  155:     function checkVars()
 *  202:     function convBoolean($input)
 *  242:     function renderTracksOptions($file, $titles, $artists)
 *  282:     function renderVars()
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * 'audioplayer' extension class
 *
 * The 'audioplayer' extension provides an api for other extensions to
 * show an mp3 flash audio player (1pixelout.net WordPress player)
 *
 * @author  Peter Schuster <typo3@peschuster.de>
 * @package  TYPO3
 * @subpackage  tx_audioplayer
 */
class tx_audioplayer {
	var $prefixId = 'tx_audioplayer';					// Same as class name
	var $scriptRelPath = 'class.tx_audioplayer.php';	// Path to this script relative to the extension dir.
	var $extKey = 'audioplayer';						// The extension key.
	var $flashFile;										// path to the flash player file
	var $configJS;										// path to the javascript file which configures the player
	var $defaultVars;									// default variable values for the flash player
	var $Vars;											// variables that are passed to the player
	var $noJSMessage = 'Please activate javascript to show the mp3 audio player.';

	/**
	 * Initiates variables for class 'tx_audioplayer'
	 *
	 * @return	void
	 * @access public
	 */
	function init() {
		$this->flashFile = t3lib_extMgm::siteRelPath($this->extKey).'res/player.swf';
		$this->configJS = t3lib_extMgm::siteRelpath($this->extKey).'res/audio-player.js';

		$this->defaultVars = array(
			'autostart' => 'no', 'loop' => 'no', 'animation' => 'yes', 'remaining' => 'no', 'noinfo' => 'no',
			'initialvolume' => 60, 'buffer' => 5, 'encode' => 'no', 'checkpolicy' => 'no', 'width' => 290,
			'transparentbg' => 'no', 'pagebg' => '',
			'bg' => 'E5E5E5', 'leftbg' => 'CCCCCC', 'lefticon' => '333333', 'voltrack' => 'F2F2F2',
			'volslider' => '666666', 'rightbg' => 'B4B4B4', 'rightbghover' => '999999', 'righticon' => '333333',
			'righticonhover' => 'FFFFFF', 'loader' => '009900', 'track' => 'FFFFFF',
			'tracker' => 'DDDDDD', 'border' => 'CCCCCC', 'skip' => '666666', 'text' => '333333' );
		$this->Vars = array();
	}

	/**
	 * Set options for the flash file
	 * see manual for complete option reference
	 *
	 * @param	array		$options: array with options
	 * @return	boolean
	 * @access public
	 */
	function setOptions($options = array()) {
		if (function_exists('array_intersect_key')) {
			//function 'array_intersect_key' is only availabe for php >= 5.1.0
			$options = array_intersect_key($options, $this->defaultVars);
		}
		if (is_array($options)) {
			$this->Vars = array_merge(is_array($this->Vars) ? $this->Vars : array(), $options);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adjust colors of the flash file
	 * see manual for complete option reference
	 *
	 * @param	array		$options: array with options
	 * @return	boolean
	 * @access public
	 */
	function setColors($options = array()) {
		return $this->setOptions($options);
	}

	/**
	 * Returns code for AudioFlashPlayer and sets html header data
	 *
	 * @param	string		$file: path to the mp3 file, multiple files can be passed through in an array
	 * @param	integer		$playerId: ID of the player (only needed, when more then one player is used on one page)
	 * @param	string		$titles: Title to be shown in player, mutiple titles for multiple files as array
	 * @param	string		$artists: Artist to be shown in player, mutiple artists for multiple files as array
	 * @return	string		code for Flashplayer, which can be placed on website
	 * @access public
	 */
	function getFlashPlayer($file, $playerId = 1, $titles = '', $artists = '') {
		$this->checkVars();
		$renderedVars = $this->renderVars();
		$renderdTracksOptions = $this->renderTracksOptions($file, $titles, $artists);

		if ($renderdTracksOptions === false) {
			return 'no file to play';
		}

		$content = 'AudioPlayer.embed("audioplayer_'.$playerId.'", {';
		$content .= $renderdTracksOptions;
		$content .= '});';

		$this->setHeaders($renderedVars);
		
		return '<div id="audioplayer_'.$playerId.'">'.$this->noJSMessage.'</div>
			'.t3lib_div::wrapJS($content);
	}
	
	function setHeaders($renderedVars) {
		$GLOBALS['TSFE']->additionalHeaderData['tx_audioplayer1'] = '<script type="text/javascript" src="'.$this->configJS.'"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['tx_audioplayer2'] = '<script type="text/javascript">AudioPlayer.setup("'.$this->flashFile.'", {'.$renderedVars.'});</script>';
	}

	/**
	 * Checks all option/color input values and converts them to valid values
	 * keys not present in $this->defaultVars are dropped
	 *
	 * @return	boolean
	 * @access private
	 */
	function checkVars() {
		$result = array();
		foreach ($this->Vars as $key => $value) {
			if ($this->defaultVars[$key] === $this->Vars[$key]) continue;
			if (in_array($key, array_keys($this->defaultVars))) {
				if (in_array($this->defaultVars[$key], array('yes', 'no'))) {
					$result[$key] = $this->convBoolean($this->Vars[$key]);
					if ($result[$key] === false) unset($result[$key]);
				} elseif (is_int($this->defaultVars[$key])) {
					$result[$key] = intval($this->Vars[$key]);
					if ($result[$key] === null) unset($result[$key]);
				} else {
					switch (strlen(strval($this->Vars[$key]))) {
						CASE 6:
							$result[$key] = strtoupper(strval($this->Vars[$key]));
							break;
						CASE 3:
							$temp1 = substr(strtoupper(strval($this->Vars[$key])), 0, 1);
							$temp2 = substr(strtoupper(strval($this->Vars[$key])), 1, 1);
							$temp3 = substr(strtoupper(strval($this->Vars[$key])), 2, 1);
							$result[$key] = $temp1.$temp1.$temp2.$temp2.$temp3.$temp3;
							break;
						CASE 0:
							$result[$key] = null;
							break;
						default:
							$result[$key] = substr(strtoupper(strval($this->Vars[$key])).'000000', 0, 6);
							break;
					}
					if (empty($result[$key])) unset($result[$key]);
				}
			}
			if ($this->defaultVars[$key] === $result[$key]) unset($result[$key]);
		}
		if (!isset($result['width'])) $result['width'] = $this->defaultVars['width'];
		$this->Vars = $result;
		unset($result);
		return true;
	}

	/**
	 * Converts values like '1', 'True', true to required boolean input of player.swf (= ['yes','no'])
	 *
	 * @param	mixed		$input: some unknow input, expected to be boolean
	 * @return	string		'yes' or 'no'
	 * @access private
	 */
	function convBoolean($input) {
		if (gettype($input) === 'boolean') {
			return $input ? 'yes' : 'no';
		} elseif (gettype($input) === 'integer') {
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
	 * more than one value has to be passed to the function in an array
	 * parameters need to be of type string (for single values) or array (for multiple values)
	 *
	 * @param	mixed		$file: filename(s) for output
	 * @param	mixed		$titles: title(s) for output
	 * @param	mixed		$artists: artist(s) for output
	 * @return	string
	 * @access private
	 */
	function renderTracksOptions($file, $titles, $artists) {
		$content = '';

		if (!empty($file)) {
			if (is_array($file)) {
				foreach ($file as $k => $v) {
					$file[$k] = rawurlencode($v);
				}
				$content .= 'soundFile: "'.implode(',', $file).'"';
			} else {
				$content .= 'soundFile: "'.rawurlencode($file).'"';
			}
		} else {
			return false;
		}

		if (!empty($titles)) {
			if (is_array($titles)) {
				$content .= ', titles: "'.implode(',', $titles).'"';
			} else {
				$content .= ', titles: "'.$titles.'"';
			}
		}

		if (!empty($artists)) {
			if (is_array($artists)) {
				$content .= ', artists: "'.implode(',', $artists).'"';
			} else {
				$content .= ', artists: "'.$artists.'"';
			}
		}
		return $content;
	}

	/**
	 * Returns rendered option/color variables for output
	 *
	 * @return	string		rendered array
	 * @access private
	 */
	function renderVars() {
		$result = array();
		foreach ($this->Vars as $key => $value) {
			$result[] = $key.': '.(is_int($value) ? $value : '"'.$value.'"');
		}
		return implode(', ', $result);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/audioplayer/class.tx_audioplayer.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/audioplayer/class.tx_audioplayer.php']);
}

?>
