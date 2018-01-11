<?php

/**
 * Animate plugin
 * @author alex
 */
class ctCssAnimatePlugin {

	/**
	 * Is someone is using us on this page?
	 * @var bool
	 */

	protected $active = true;

	/**
	 * Animations
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'onInit' ), 9 );
		add_action( 'wp_head', array( $this,'addHeadScripts'));
		add_filter( 'ct_theme_loader.options.load', array( $this, 'addOptions' ) );
		//add_action( 'wp_footer', array( $this, 'injectScripts' ) );
		add_filter( 'body_class', array( $this, 'customBodyClass' ) );
	}

	/**
	 * Register shortcodes
	 */

	public function onInit() {

		//add listeners to our shortcodes
		foreach ( $this->getCompatibleShortcodes() as $shortcode ) {
			ctShortcode::connectInlineAttributeFilter( $shortcode, array( $this, 'addCustomAttributes' ) );
			ctShortcode::connectNormalizedAttributesFilter( $shortcode, array(
				$this,
				'addCustomNormalizedAttributes'
			) );
		}
	}

	/**
	 * Return compatible shortcodes
	 * @return array
	 */
	protected function getCompatibleShortcodes() {
		$shortcodes = array(
			'five_sixth_column',
			'full_column',
			'full_width',
			'half_column',
			'one_sixth_column',
			'quarter_column',
			'row',
			'third_column',
			'three_quarters_column',
			'five_twelfths_column',
			'seven_twelfths_column',
			'title_row',
			'two_thirds_column',
			'vc_column',
			'vc_column_inner',
			'vc_row',
			'img'
		);

		return apply_filters( 'ct.css_animate.compatible_shortcodes', $shortcodes );
	}

	/**
	 * Adds custom options
	 *
	 * @param $sections
	 *
	 * @return mixed
	 */

	public function addOptions( $sections ) {
		//hide options when we do not add animation
		if ( ! $this->getCompatibleShortcodes() ) {
			return $sections;
		}
		foreach ( $sections as $key => $section ) {
			if ( $section['group'] == 'General' ) {
				//add custom fields to general tab
				$sections[ $key ]['fields'][] = array(
					'id'    => "general_show_css_animate",
					'title' => __( "Enable animations", 'ct_theme' ),
					'type'  => 'select_show',
					'std'   => 1
				);
				break;
			}
		}

		return $sections;
	}

	/**
	 * Is it active?
	 * @return bool
	 */

	public function isEnabled() {
		$val = ct_get_option( 'general_show_css_animate' );

		return $val === '' || $val;
	}

	/**
	 * Body class
	 *
	 * @param $classes
	 *
	 * @return mixed
	 */
	public function customBodyClass( $classes ) {
		if ( $this->isEnabled() ) {
			$classes[] = 'cssAnimate';
		}

		return $classes;
	}

	/**
	 * Adds required scripts
	 */

	public function injectScripts() {
		if ( $this->active && $this->isEnabled() ) {

			//add appear and call animations. In this js we already call proper animation stuff
			wp_register_script( 'ct-appear', CT_THEME_SETTINGS_MAIN_DIR_URI . '/plugin/css-animate/assets/js/jquery.appear.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'ct-appear' );

			wp_register_style( 'ct-animation', CT_THEME_SETTINGS_MAIN_DIR_URI . '/plugin/css-animate/assets/css/animate.css' );
			wp_enqueue_style( 'ct-animation' );
		}
	}


	/**
	 * Adds head scripts
	 */

	public function addHeadScripts() {
		if ( $this->active && $this->isEnabled() ) {

			//add appear and call animations. In this js we already call proper animation stuff
			wp_register_script( 'ct-appear', CT_THEME_SETTINGS_MAIN_DIR_URI . '/plugin/css-animate/assets/js/jquery.appear.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'ct-appear' );

			wp_register_style( 'ct-animation', CT_THEME_SETTINGS_MAIN_DIR_URI . '/plugin/css-animate/assets/css/animate.css' );
			wp_enqueue_style( 'ct-animation' );
		}
	}

	/**
	 * Add custom attributes
	 *
	 * @param array $content
	 * @param array $attributes
	 *
	 * @internal param $css
	 * @return string
	 */

	public function addCustomAttributes( $content, $attributes = array() ) {
		if ( $this->isEnabled() && isset( $attributes['animation'] ) && $attributes['animation'] ) {
			$this->active         = true;
			$content['data-fx']   = $attributes['animation'];
			$content['data-time'] = isset( $attributes['animation_speed'] ) ? $attributes['animation_speed'] : '';
			$content['class'][]   = 'animated';
		}

		return $content;
	}

	/**
	 * Normalized attributes
	 *
	 * @param $attr
	 */

	public function addCustomNormalizedAttributes( $attr ) {
		$attr['animation_speed'] = array(
			'label'   => __( "animation speed", 'ct_theme' ),
			'type'    => 'input',
			'default' => '',
			'group'   => __( "Animation", 'ct_theme' ),
			'help'    => __( 'In miliseconds ex. 2000 is 2 seconds', 'ct_theme' )
		);
		$attr['animation']       = array(
			'label'   => __( 'animation', 'ct_theme' ),
			'default' => '',
			'group'   => __( "Animation", 'ct_theme' ),
			'type'    => 'select',
			'choices' =>
				array(
					"" => __( "none", "ct_theme" ),
					array(
						__( 'Attention seekers', "ct_theme" )  => array(
							"flash"  => __( "Flash", "ct_theme" ),
							"bounce" => __( "Bounce", "ct_theme" ),
							"shake"  => __( "Shake", "ct_theme" ),
							"tada"   => __( "Tada", "ct_theme" ),
							"swing"  => __( "Swing", "ct_theme" ),
							"wobble" => __( "Wooble", "ct_theme" ),
							"pulse"  => __( "Pulse", "ct_theme" )
						),
						__( 'Flippers', "ct_theme" )           => array(
							"flip"     => __( "Flip", "ct_theme" ),
							"flipInX"  => __( "Flip in X", "ct_theme" ),
							"flipOutX" => __( "Flip out X", "ct_theme" ),
							"flipInY"  => __( "Flip in Y", "ct_theme" ),
							"flipOutY" => __( "Flip out Y", "ct_theme" ),
						),
						__( 'Fading entrances', "ct_theme" )   => array(
							"fadeIn"         => __( "Fade in", "ct_theme" ),
							"fadeInUp"       => __( "Fade in up", "ct_theme" ),
							"fadeInDown"     => __( "Fade in down", "ct_theme" ),
							"fadeInLeft"     => __( "Fade in left", "ct_theme" ),
							"fadeInRight"    => __( "Fade in right", "ct_theme" ),
							"fadeInUpBig"    => __( "Fade in up big", "ct_theme" ),
							"fadeInDownBig"  => __( "Fade in down big", "ct_theme" ),
							"fadeInLeftBig"  => __( "Fade in left big", "ct_theme" ),
							"fadeInRightBig" => __( "Fade in right big", "ct_theme" ),
						),
						__( 'Fading exits', "ct_theme" )       => array(
							"fadeOut"         => __( "Fade out", "ct_theme" ),
							"fadeOutUp"       => __( "Fade out up", "ct_theme" ),
							"fadeOutDown"     => __( "Fade out down", "ct_theme" ),
							"fadeOutLeft"     => __( "Fade out left", "ct_theme" ),
							"fadeOutRight"    => __( "Fade out right", "ct_theme" ),
							"fadeOutUpBig"    => __( "Fade out up big", "ct_theme" ),
							"fadeOutDownBig"  => __( "Fade out down big", "ct_theme" ),
							"fadeOutLeftBig"  => __( "Fade out left big", "ct_theme" ),
							"fadeOutRightBig" => __( "Fade out right big", "ct_theme" ),
						),
						__( 'Sliders', "ct_theme" )            => array(
							"slideInDown"   => __( "Slide in down", "ct_theme" ),
							"slideInLeft"   => __( "Slide in left", "ct_theme" ),
							"slideInRight"  => __( "Slide in right", "ct_theme" ),
							"slideOutUp"    => __( "Slide out up", "ct_theme" ),
							"slideOutLeft"  => __( "Slide out left", "ct_theme" ),
							"slideOutRight" => __( "Slide out right", "ct_theme" ),
						),
						__( 'Bouncing entrances', "ct_theme" ) => array(
							"bounceIn"      => __( "Bounce in", "ct_theme" ),
							"bounceInDown"  => __( "Bounce in down", "ct_theme" ),
							"bounceInUp"    => __( "Bounce in up", "ct_theme" ),
							"bounceInLeft"  => __( "Bounce in left", "ct_theme" ),
							"bounceInRight" => __( "Bounce in right", "ct_theme" ),
						),
						__( 'Bouncing exits', "ct_theme" )     => array(
							"bounceOut"      => __( "Bounce out", "ct_theme" ),
							"bounceOutDown"  => __( "Bounce out down", "ct_theme" ),
							"bounceOutUp"    => __( "Bounce out up", "ct_theme" ),
							"bounceOutLeft"  => __( "Bounce out left", "ct_theme" ),
							"bounceOutRight" => __( "Bounce out right", "ct_theme" ),
						),
						__( 'Rotating entrances', "ct_theme" ) => array(
							"rotateIn"          => __( "Rotate in", "ct_theme" ),
							"rotateInDownLeft"  => __( "Rotate in down left", "ct_theme" ),
							"rotateInDownRight" => __( "Rotate in down right", "ct_theme" ),
							"rotateInUpLeft"    => __( "Rotate in up left", "ct_theme" ),
							"rotateInUpRight"   => __( "Rotate in up right", "ct_theme" ),
						),
						__( 'Rotating exits', "ct_theme" )     => array(
							"rotateOut"          => __( "Rotate out", "ct_theme" ),
							"rotateOutDownLeft"  => __( "Rotate out down left", "ct_theme" ),
							"rotateOutDownRight" => __( "Rotate out down right", "ct_theme" ),
							"rotateOutUpLeft"    => __( "Rotate out up left", "ct_theme" ),
							"rotateOutUpRight"   => __( "Rotate out up right", "ct_theme" ),
						),
						__( 'Lightspeed', "ct_theme" )         => array(
							"lightSpeedIn"  => __( "Light speed in", "ct_theme" ),
							"lightSpeedOut" => __( "Light speed out", "ct_theme" ),
						),
						__( 'Specials', "ct_theme" )           => array(
							"hinge"   => __( 'Hinge', 'ct_theme' ),
							"rollIn"  => __( 'Roll in', 'ct_theme' ),
							"rollOut" => __( 'Roll out', 'ct_theme' ),
						)
					)
				),
			'help'    => sprintf( __( 'Animate this element once it becomes visible. Supported animations: %s', 'ct_theme' ), '<a target="_blank" href="https://daneden.me/animate/">https://daneden.me/animate/</a>' )
		);

		return $attr;
	}

}

new ctCssAnimatePlugin();