<?php
//
//  esu-admin-class.php
//  easy-sign-up
//
//  Created by Rew Rixom on 2011-03-29.
//

if (!class_exists("EsuForms")) {
	class EsuForms
	{
		function __construct()
		{
			// register the short-code
			add_shortcode('easy_sign_up', array($this,'esu_form_func'));
			if(!is_admin()){	//load the css
				add_action('init', array($this,'esu_front_end_css'));
				add_action('wp_head', array($this,'esu_validation_lang_js')); // language variables & and css json
				add_action('init', array($this,'esu_validation_js')); // load the validation js
			}
		}

		function esu_form_func($atts)
		{
			// [easy_sign_up title="value" fnln="true" phone="true" esu_label="A unique identifier for your form" esu_class="my-class-here" esu_r_url="http://xxx.com"]
			extract(
				shortcode_atts(
					array(
						 'title' 			=> false
						,'fnln'				=> true
						,'phone' 			=> true
						,'esu_label'	=> null
						,'esu_class'	=> ''
						,'esu_r_url' 	=> null
					), $atts
				)
			);
			$esu_class  = $esu_class." esu-from-shortcode";
			$form_id 		= 'esu'.self::esu_rand_str('20');
			$esu_return = $this->esu_form_html($form_id,$title,$fnln,$phone,$esu_label,$esu_class,$esu_r_url);
			return $esu_return;
	  }

		public static function esu_form_html($form_id,$title=false, $fnln=true, $phone=true, $esu_label=null, $esu_class='',$esu_r_url=null)
		{
			// this is for a an issue while working on localhost
			$esu_form_action_url = ESU_WEB_URL . '/';
			if (defined('ESU_QUIRK_MODE') ){
				$esu_form_action_url = ESU_WEB_URL."/index.php";
			}

			$esu_return = "<div class='esu-form-div $esu_class'>";
			$esu_return .= '<form id="'.$form_id.'" name="'.$form_id.'" method="post" action="'.$esu_form_action_url.'?esu_qv=true" onsubmit="javascript:return esu_validate(\''.$form_id.'\');">';

			if($esu_label == null || trim($esu_label) == '') :
				$esu_label = ESU_NAME;
			endif;
			// set nonce
			$esu_return .= wp_nonce_field( $form_id.'_esu_nonce',$form_id.'_lama', true, false); // nonce

			if(!is_null($esu_r_url))
			{

				$esu_return .= '<input type="hidden" name="esu_r_url" value="'.esc_url($esu_r_url).'">';
			}


			$esu_return .= '<input type="hidden" name="esu_formID" value="'.$form_id.'">';

			$esu_return .= '<input type="hidden" name="esu_label" value="'.$esu_label.'">';

			$esu_return .= '<input type="hidden" name="esu_permalink" value="'.esc_url(get_permalink()).'">';

			$esu_return .= "\n<ul>\n"; // open list

			if(trim($title) != false ) :
				$esu_return .= '<li class="esu-form-title">';
				$esu_return .=  $title;
				$esu_return .= "</li>\n";
			endif;

			$esu_return .= self::esu_build_form($form_id,$fnln,$phone,$esu_class);
			$esu_return .= "</ul>\n"; // close list
			$esu_return .= '</form>';
			$esu_return .= "</div>";
			return $esu_return;
		}

		public static function esu_build_form( $form_id, $fnln, $phone,$esu_class )
		{
			// an array full of filters
			$options = esu_default_form_setup($esu_class);
			if(!is_array($options)) {
				$esu_oops =  "<h1>".__('Error: Needs an array','easy-sign-up')."</h1>";
				return $esu_oops;
			}
			if($fnln == true ) :
				unset($options['n']);
			else:
				unset($options['fn']);
				unset($options['ln']);
			endif;
			if($phone != '1' ) :
				unset($options['p']);
			endif;
			$output = null;
			foreach ( $options as $value ) {
				if( is_array($value) ){
	        $select_value = '';
	        $checked = '';
	        $val = (isset($value['default'])) ? $value['default'] : null ;

	        // Wrap all options
	        if ( ( $value['type'] != "desc" ) && ( $value['type'] != "info" ) ) {
	          // Keep all ids lowercase with no spaces
	          $value['id'] = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($value['id']) );

	          $id = $form_id .'_'. $value['id'];

	          $class = null;

	          if ( isset( $value['type'] ) ) {
	            $class .= ' esu-' . $value['type'];
	          }
	          if ( isset( $value['validate'] ) ) {
	            $class .= ' ' . $value['validate'];
	          }
	          if ( isset( $value['class'] ) ) {
	            $class .= ' ' . $value['class'];
	          }
	        }
	        //label classes
	        if (isset( $value['label_class'] )) {
	        	$esu_label_class = $value['label_class'];
	        	$class .= ' ' . $value['label_class'];
	        }else{
	        	$esu_label_class = 'esu-hide';
	        }

	        switch ( $value['type'] ) {
						// Basic text input
					  case 'text':
					  	$output .= '<li>';
					  		$output .= '<label for="'. esc_attr( $id ) . '" class="'.$esu_label_class.'">'.$value['name'].'</label>';
					  		$output .= '<input id="' . esc_attr( $id ) . '" class="'.esc_attr( $class ).'" name="' . esc_attr( $id ) . '" type="text" ' . self::esu_if_ie_input_js(esc_attr( $value['name'] )) . '>';
					  		$output .= "<br class=\"$esu_label_class\">";
					  	$output .= '</li>'."\n";
					  break;
					  // Honey Pot
					  case 'honey':
					  	$output .= '<li class="esu-hide">';
					  		$output .= '<label for="'. esc_attr( $id ) . '" class="esu-hide">'.$value['name'].'</label>';
					  		$output .= '<input id="' . esc_attr( $id ) . '" class="'.esc_attr( $class ).'" name="' . esc_attr( $id ) . '" type="text" value="">';
					  		$output .= "<br class=\"$esu_label_class\">";
					  	$output .= '</li>'."\n";
					  break;
					  // Submit button
					  case 'submit':
					  	$output .= '<li>';
						  	$output .= '<input id="' . esc_attr( $id ) . '" class="'.esc_attr( $class ).'" name="esu_send_bnt" type="submit" value="' . esc_attr( $value['name'] ) . ' "/>';
					  	$output .= '</li>'."\n";
					  break;
					  // Image Submit button
					  case 'img':
					  	$output .= '<li>';
						  $output .= '<input id="' . esc_attr( $id ) . '" class="'.esc_attr( $class ).'" name="esu_send_bnt" type="image" src="' . esc_attr( $value['src'] ) . '"/>';
					  	$output .= '</li>'."\n";
					  break;
					  // Textarea
					  case 'textarea':
					    $rows = '8';
					    if ( isset( $value['settings']['rows'] ) ) {
					      $custom_rows = $value['settings']['rows'];
					      if ( is_numeric( $custom_rows ) ) {
					        $rows = $custom_rows;
					      }
					    }
					    $output .= '<li>';
					    	$output .= '<label for="'. esc_attr( $id ) . '" class="'.$esu_label_class.'">'.$value['name'].'</label>';
					    	$output .= '<textarea '. self::esu_if_ie_input_js(esc_attr( $value['name'] )) .' id="' . esc_attr( $id ) . '" class="'.esc_attr( $class ).'" name="' . esc_attr( $id ) . '" rows="' . $rows . '"></textarea>';
					    	$output .= "<br class=\"$esu_label_class\">";
					  	$output .= '</li>'."\n";
					  break;
					  // Select Box
					  case 'select':
					  	$output .= '<li>';
					  		$output .= '<div class="'.$class.'">';
					  			if ( isset( $value['name']) ) $output .= '<label for="'. esc_attr( $id ) . '">'.$value['name'].'</label>';
							    $output .= '<select id="' . esc_attr( $id ) . '" class="'.esc_attr( $class ).'" name="' . esc_attr( $id ) . '">';
					          foreach ($value['options'] as $key => $option ) {
					            $selected = '';
					            if ( $val != null ) {
					              if ( $val == $key) { $selected = ' selected="selected"';}
					            }
					            $output .= '<option'. $selected .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
					          }
				          $output .= '</select>';
				          if ( isset( $value['desc']) ) $output .= '<div class="esu-desc">'.$value['desc'].'</div>';
			          $output .= '</div>';
					  	$output .= '</li>'."\n";
					  break;
					  // Radio Box
					  case "radio":
					  	$output .= '<li>';
					  		$output .= '<div class="'.$class.'">';
						  		if ( isset( $value['desc']) ) $output .= '<div class="esu-desc">'.$value['desc'].'</div>';
							    foreach ($value['options'] as $key => $option) {
							      $id .= '-' . $key;
							      $output .= '<input class="esu-input esu-radio" type="radio" name="'
							      						. esc_attr( $form_id .'_'. $value['id'] ) . '" id="'
							      						. esc_attr( $id ) . '" value="'
							      						. esc_attr( $key )
							      						. '" '
							      						. checked( $val, $key, false) .' />';
							      $output .= '<label class="esu-radio" for="'. esc_attr( $id ) . '">' . esc_html( $option ) . '</label>';
						    	}
					    	$output .= '</div>';
					  	$output .= '</li>';
					  break;
					  // Info
					  case "info":
					    $id = '';
					    $class = null;
					    if ( isset( $value['id'] ) ) {
					      $id = 'id="' . esc_attr( $value['id'] ) . '" ';
					    }
					    if ( isset( $value['type'] ) ) {
					      $class .= ' esu-' . $value['type'];
					    }
					    if ( isset( $value['class'] ) ) {
					      $class .= ' ' . $value['class'];
					    }
					    $output .= '<li><div ' . $id . 'class="' . esc_attr( $class ) . '">' . "\n";
					    if ( isset($value['name']) ) {
					      $output .= '<h4 class="esu-heading">' . esc_html( $value['name'] ) . '</h4>' . "\n";
					    }
					    if ( isset( $value['desc'] ) ) {
					      $output .=  $value['desc'] . "\n";
					    }
					    $output .= '</div></li>' . "\n";
					  break;
					  // Checkbox
					  case "checkbox":
					  	$output .= '<li>';
						  	$output .= '<input id="' . esc_attr( $id ) . '" class="'.esc_attr( $class ).'" name="' . esc_attr( $id ) . '" type="checkbox"  value="' . esc_attr( $value['name'] ) . ' "/>';
						  	$output .= '<label class="explain" for="' . esc_attr( $id ) . '">' .  esc_attr( $value['name'] ) . '</label>';
					  	$output .= '</li>'."\n";
					  break;
					} // end switch
				} // end if
			} // end loop
			return $output;
		} // end function

		function esu_front_end_css()
		{
			$esu_style_url 	= ESU_URL. 'css/esu-styles.css';
		  // check that the ESU Style Extra is NOT present
			if (!class_exists("EsuStyle")){
			  wp_register_style('esu_style_url', $esu_style_url,false, ESU_VERSION,'all');
		    wp_enqueue_style( 'esu_style_url');
			}
		} //end esu_validation_stylesheet
		function esu_validation_js()
		{
			$esu_js_url 		= ESU_URL. 'js/esu-validate.js';
      // check that the ESU Style Extra is NOT present
			wp_enqueue_script('esu_js_url',$esu_js_url,array('jquery'), ESU_VERSION,true );
		} //end esu_validation_stylesheet

		function esu_validation_lang_js()
		{
			$esu_required_txt 	= __('Required','easy-sign-up');
			$esu_not_valid_txt 	= __('Not Valid','easy-sign-up');
			$esu_script = "
			<script type='text/javascript'>
				/* Easy Sign Up Plugin */
				var esu_err_colors = {'background-color': 'red','color':'white'};
				var esu_good_colors = {'background-color': '#F5F5DC','color':'#FFD797'};
				var esu_err_css = {'top':0,'left':0,'right':0,'width':'100%','position':'fixed'};
				var esu_required_txt = ' $esu_required_txt';
				var esu_not_valid_txt = ' $esu_not_valid_txt';
				var esu_show_bar = true;
			</script>\n";
			echo $esu_script;
		}

		public static function esu_if_ie_input_js($arg)
		{
			global $esu_no_placeholder;
			if (true == $esu_no_placeholder ) return;
			global $is_IE;
			if( true == $is_IE):
				return "placeholder=\"$arg\" value=\"$arg\" onfocus=\"if(this.value == '$arg'){this.value = '';}\" onblur=\"if(this.value == ''){this.value = '$arg';}\"";
			else:
				return 'placeholder="'.$arg.'"';
			endif;
		}

		public function esu_rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
		{
	    // Length of character list
	    $chars_length = (strlen($chars) - 1);
	    // Start our string
	    $string = $chars{rand(0, $chars_length)};
	    // Generate random string
	    for ($i = 1; $i < $length; $i = strlen($string))
	    {
	        // Grab a random character from our list
	        $r = $chars{rand(0, $chars_length)};
	        // Make sure the same two characters don't appear next to each other
	        if ($r != $string{$i - 1}) $string .=  $r;
	    }
	    // Return the string
	    return $string;
		}

	}
}