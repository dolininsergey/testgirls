<?php
/**
 * /lib/esu-form-process-class.php
 * easy-sign-up
 * Created by Rew Rixom on November 7, 2012
 * @since 3.0
 **/
if (!class_exists("EsuProcess")) {
	class EsuProcess
	{

		function __construct()
		{
			add_action('init', array($this,'esu_spam_check'));
			// use the hook defined in function esu_spam_check()
			add_action('esu_hook_process_email', array('EsuProcess','esu_process_email'),10,3);
			add_action('esu_hook_send_responder_email', array('EsuProcess','esu_send_responder_email'),10,4);
		}

		function esu_spam_check()
		{
			$esu_check_nonce = self::esu_check_nonce();
			$esu_post_vars = $esu_check_nonce;

			$use_akismet = get_option('easy_sign_up_use_askismet');

			if(!esuAkismet::esu_has_akismet() OR $use_akismet == false) {
				$is_spam = false;
				$has_akismet = false;
			}else{
				$is_spam = esuAkismet::is_akismet_spam( $esu_post_vars );
				$has_akismet = true;
			}

			$honeypot 			= self::esu_use_honeypot();
		  if($honeypot != false){
		  	$honeypot_value = trim($esu_post_vars['extra_fields'][$honeypot]);
		  }else{
		  	$honeypot_value = false;
		  }

			if( $honeypot_value != false ) {
				$bot_message = __('Hello there, <br>Rex our automated bot detector thinks you may be a pesky robot! Super sorry if this is an error. Perhaps you are using an autofill add-on or something similar. If so please fill out the form manually and we should all be able to get on with our day. Cheerio!  ;-)', 'easy-sign-up');
				 wp_die( $bot_message, 'Domo Arigato, Mr. Roboto', array( 'back_link' => true ) );
				}

			// This is done to clean up if the honeypot field before sending the responders
			if($honeypot != false){
				if( isset($esu_post_vars['extra_fields']) && array_key_exists( $honeypot,  $esu_post_vars['extra_fields'] ) ) {
					unset($esu_post_vars['extra_fields'][$honeypot]);
				}
			}
			// lets move this to a filter field
			if( isset($esu_post_vars['extra_fields']) && array_key_exists( 'g-recaptcha-response',  $esu_post_vars['extra_fields'] ) ){
				unset($esu_post_vars['extra_fields']['g-recaptcha-response']);
			}

			// create hooks for use by extras or 3rd party plugins
			do_action( 'esu_hook_before_process_email', $esu_post_vars);
			do_action( 'esu_hook_process_email', $esu_post_vars,$is_spam,$has_akismet);
		}

		/**
		* Process the form
	 	* **/
		public static function esu_process_email($esu_post_vars,$is_spam,$esu_has_akismet)
		{
			if(!is_array($esu_post_vars)) wp_die(__('sorry nothing was sent - please use the back button and try again','easy-sign-up'), __('sorry nothing was sent, try again','easy-sign-up'));
			if ($is_spam!==false) return false; // this is Spam
			// set variables
			// Set err message.
			$esu_error_message = __('Error: please fill out all the required fields','easy-sign-up');
			extract($esu_post_vars);
			if(isset($extra_fields) && !empty($extra_fields)) {
				$extra=self::esu_process_extra_fields($extra_fields);
			}else{
				$extra='';
			}
			// err checking
			if( !isset($name) || $name == "")
				wp_die($esu_error_message, __('Please fill out your name','easy-sign-up'));
			if( !isset($email) || $email == "")
				wp_die($esu_error_message, __('Please fill out your email address','easy-sign-up'));
			$phone = (isset($phone)) ? __('Phone:','easy-sign-up') ."\n{$phone}\n" : null ;
			$label = (isset($label)) ? $label : "Easy Sign Up Form";

			// Get options
			$easy_sign_up_co_email = get_option('easy_sign_up_co_email'); // Admin's Email address
			$easy_sign_up_co_from_email = get_option('easy_sign_up_co_from_email'); // Automated Reply Email
			$easy_sign_up_thank_you_email = stripslashes_deep(get_option('easy_sign_up_thank_you_email')); // The thank you email content
			$easy_sign_up_thank_you_email = str_replace( "#fullname#",$name, $easy_sign_up_thank_you_email );
			/**
			 * Added #firstname# quick tag.
			 * @since 3.4
			 */
			if( isset($fname) AND $fname !== ""){
				$easy_sign_up_thank_you_email = str_replace( "#firstname#",$fname, $easy_sign_up_thank_you_email );
			}
			/**
			 * Added #firstname# quick tag.
			 * @since 3.4
			 */
			if( isset($lname) AND $lname !== ""){
				$easy_sign_up_thank_you_email = str_replace( "#lastname#",$lname, $easy_sign_up_thank_you_email );
			}

			$easy_sign_up_url = ( isset($extra_fields['r_url']) ) ? $extra_fields['r_url'] : get_option('easy_sign_up_url'); // where we need to send them
			// This should not be necessary as we load the default options on activation
			// however if the option is deleted by the user we need a fall back
			if( !$easy_sign_up_url || trim($easy_sign_up_url) == "" ):
				$easy_sign_up_url = WP_URL;
			endif;
			// @since 3.3.5
			// ensureing that blogs with the Apostrophes in Quotation marks in the title are respected by the from email field
			$esu_blog_name = get_bloginfo('name');
			$esu_replace_what = array('&#039;', '&#34;', '&quot;');
			$esu_replace_with = array("'",'"','"');
			$esu_blog_name = str_replace($esu_replace_what,$esu_replace_with, $esu_blog_name );
			$from = 'From: '.$esu_blog_name.' <'.$easy_sign_up_co_from_email.'>' . "\r\n";

			if(self::esu_check_email_address($email))
			{
				$admin_message = "$name ( $email ) ".__('signed up and been redirected to','easy-sign-up')." $easy_sign_up_url $phone $extra";
				$admin_subject = $esu_blog_name.": ".$label;
				$subject = get_option( 'easy_sign_up_co_from_email_subject', false );
				if( $subject == false OR $subject == '' ) {
					$subject = __( "Email confirmation from",'easy-sign-up' )." ".$esu_blog_name;
				}
				// send admin email
				wp_mail ($easy_sign_up_co_email, $admin_subject, $admin_message, $from);
				$esu_responder_email = do_action( 'esu_hook_send_responder_email', $email, $subject, $easy_sign_up_thank_you_email, $from );
				// redirect
				wp_redirect( esc_url($easy_sign_up_url) ); exit;
			}else{
				wp_die($esu_error_message);
			}
		} // end esu_process_email

		public function esu_post_vars()
		{
			$esu_post_vars = $_REQUEST;
			if (!isset($esu_post_vars['esu_formID'])) return false;
			$esu_f_id = $esu_post_vars['esu_formID'].'_';
			$esu_form_defaults = array('lama','_wp_http_referer','formID','label','permalink','fname','lname','name','email','phone');
			$ret_arr = array(); // return array
			$extra_fields = array();
			foreach ($esu_post_vars as $key => $value) {
				if($key!='esu_send_bnt'):
					if($key!='esu_qv'):
						$k = trim( str_replace( array($esu_f_id,'esu_'), array(null,null), $key ) );
						if(in_array($k, $esu_form_defaults)) {
							$ret_arr[$k] = $value;
						}else{
							$extra_fields[$k] = $value;
						}
					endif;
				endif;
			}

			if( !isset($ret_arr['fname']) ){
				$a = explode(' ', $ret_arr['name'] );
				$count = count($a);
				if($count == 2){
					$ret_arr['fname'] = $a['0'];
					$ret_arr['lname'] = $a['1'];
				}elseif ($count == 1 || $count > 2 ) {
					$ret_arr['fname'] = false;
					$ret_arr['lname'] = false;
				}
			}

			$ret_arr['name'] =  (isset($ret_arr['name'])) ? $ret_arr['name'] : $ret_arr['fname'].' '.$ret_arr['lname'];
			$ret_arr['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
      $ret_arr['referer'] = $_SERVER['HTTP_REFERER'];
      $ret_arr['user_ip'] = $_SERVER['REMOTE_ADDR'];
      $ret_arr['comment_author_IP'] = preg_replace( '/[^0-9., ]/', '', $ret_arr['user_ip'] );
      if(!empty($extra_fields)) $ret_arr['extra_fields'] = $extra_fields;
			return $ret_arr;
		}

		// Validate for folks with no JS
		public static function esu_check_email_address($email)
		{
		  if(function_exists('is_email')){
		    return is_email($email); // a WordPress function. is_email() is located in wp-includes/formatting.php
		  }else{
		    return true;
		  }
		}

		// first line of defense in the Spam war
		function esu_check_nonce()
		{
			$esu_post_vars = self::esu_post_vars();
			$esu_failed_message_vars  = __('Failed Security Check: nothing was sent','easy-sign-up');
			$esu_failed_message_nonce = __('Failed Nonce Security Check','easy-sign-up');
			if (!$esu_post_vars) wp_die($esu_failed_message_vars);
			extract($esu_post_vars);
			if ( !wp_verify_nonce($lama, "{$formID}_esu_nonce") ) wp_die($esu_failed_message_nonce);
			// This is done to clean up if folks use an image submit button;
			if( isset( $esu_post_vars['extra_fields']['send_bnt_x'] ) ) {
				unset($esu_post_vars['extra_fields']['send_bnt_x']);
			}
			if(isset($esu_post_vars['extra_fields']['send_bnt_y'])) {
				unset($esu_post_vars['extra_fields']['send_bnt_y']);
			}
			return $esu_post_vars;
		}

		/* Moved the email to the user out of the process function so it can be affected by a hook */
		public static function esu_send_responder_email($email, $subject, $easy_sign_up_thank_you_email, $from)
		{
			// send auto responder
			add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
			$esu_body = apply_filters('the_content', $easy_sign_up_thank_you_email);
			$subject  = stripslashes(  $subject );
			$esu_return = wp_mail ($email, $subject, $esu_body, $from);
			add_filter('wp_mail_content_type',create_function('', 'return "text/plain"; '));
			return $esu_return;
		}

		/* Pocessing  */
		public static function esu_process_extra_fields($extra_fields)
		{
			$extra = null;
			foreach ($extra_fields as $key => $value) {
				if (is_array($key)) {
					$extra = self::esu_process_extra_fields($key);
				}else{
					$extra .= "\n{$key}: {$value}\n";
				}
			}
			return $extra;
		}

		/**
		 * Checks the site options to see if we use the honeypot here
		 * If so then it'll return the name of the honeypot field
		 * If not then it returns false
		 *
		 * @return string | false boolean
		 * @author Rew Rixom - web: http://www.greenvilleweb.us email: rew@greenvilleweb.us
		 **/
		public static function esu_use_honeypot()
		{

			$use_honeypot 	= get_option( ESU_S_NAME."_use_honey_pot", false );
			$honey_pot_name = get_option( ESU_S_NAME."_honey_pot_name", false );

			if($honey_pot_name == false OR trim($honey_pot_name) == '' && $honey_pot_name == null ){
				return false;
			}

			if($use_honeypot != false && trim($use_honeypot) != '' && $use_honeypot != null ){
				return esu_prep_id($honey_pot_name);
			}else{
				return false;
			}

		}

	} /* End Class */
}
// EOF
