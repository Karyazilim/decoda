<?php

class EmoticonHook extends DecodaHook {

	/**
	 * Mapping of emoticons and smilies.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_emoticons = array();
	
	/**
	 * Map of smilies to emoticons.
	 * 
	 * @access protected
	 * @var array
	 */
	protected $_map = array();
	
	/**
	 * Relative path to the emoticons folder.
	 * 
	 * @access protected
	 * @var array
	 */
	protected $_path = array();

	/**
	 * Load the emoticons from the JSON file.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$path = DECODA_CONFIG .'emoticons.json';

		if (file_exists($path)) {
			$this->_emoticons = json_decode(file_get_contents($path), true);

			foreach ($this->_emoticons as $emoticon => $smilies) {
				foreach ($smilies as $smile) {
					$this->_map[$smile] = $emoticon;
				}
			}

			$this->_path = str_replace(array(realpath($_SERVER['DOCUMENT_ROOT']), '\\', '/'), array('', '/', '/'), DECODA_EMOTICONS);
		}
	}

	/**
	 * Parse out the emoticons and replace with images.
	 * 
	 * @access public
	 * @param string $content
	 * @return string
	 */
	public function beforeParse($content) {
		if (!$this->getParser()->getFilter('Image')) {
			return $content;
		}

		foreach ($this->_emoticons as $emoticon => $smilies) {
			foreach ($smilies as $smile) {
				$content = preg_replace_callback('/(\s)?'. preg_quote($smile, '/') .'(\s)?/is', array($this, '_emoticonCallback'), $content);
			}
		}

		return $content;
	}

	/**
	 * Callback for smiley processing.
	 * 
	 * @access protected
	 * @param array $matches
	 * @return string 
	 */
	protected function _emoticonCallback($matches) {
		$smiley = trim($matches[0]);

		if (count($matches) == 1 || !isset($this->_map[$smiley])) {
			return $matches[0];
		}

		$l = isset($matches[1]) ? $matches[1] : '';
		$r = isset($matches[2]) ? $matches[2] : '';

		$image = $this->getParser()->getFilter('Image')->parse(array(
			'tag' => 'img',
			'attributes' => array()
		), $this->_path . $this->_map[$smiley] .'.png');

		return $l . $image . $r;
	}
	
}