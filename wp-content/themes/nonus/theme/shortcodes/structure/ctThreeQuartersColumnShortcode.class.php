<?php
/**
 * 3/4 column shortcode
 */
class ctThreeQuartersColumnShortcode extends ctShortcode {

	/**
	 * Returns name
	 * @return string|void
	 */
	public function getName() {
		return '3/4 column';
	}

	/**
	 * Shortcode name
	 * @return string
	 */
	public function getShortcodeName() {
		return 'three_quarters_column';
	}

	/**
	 * Action
	 * @return string
	 */

	public function getGeneratorAction() {
		return self::GENERATOR_ACTION_INSERT;
	}

	/**
	 * Handles shortcode
	 * @param $atts
	 * @param null $content
	 * @return string
	 */

	public function handle($atts, $content = null) {
		extract(shortcode_atts($this->extractShortcodeAttributes($atts), $atts));

		$innerPre = '';
		$innerPost = '';
		$inner = false;
		if($inner == 'true' || $inner == 'yes' || $inner == '1' || $space == 'true' || $space == 'yes' || $space == '1'){
			$innerPre = '<div class="inner">';
			$innerPost = '</div>';
		}

		return '<div'.$this->buildContainerAttributes(array('class'=>array('span9',$class)),$atts).'>' . $innerPre . do_shortcode($content) . $innerPost . '</div>';
	}

	/**
	 * Returns config
	 * @return null
	 */
	public function getAttributes() {
		return array(
			'space' => array('label' => __('add inner space?', 'ct_theme'), 'type' => "checkbox", 'default' => false),
			'class' => array('type' => false)
		);
	}
}

new ctThreeQuartersColumnShortcode();