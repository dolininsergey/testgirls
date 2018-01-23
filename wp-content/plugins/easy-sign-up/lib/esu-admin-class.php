<?php
//
//  esu-admin-class.php
//  easy-sign-up
//
//  Created by Rew Rixom on 2011-03-29.
//

if (!class_exists("EsuAdmin")) {
	class EsuAdmin
	{
		function __construct()
		{
			add_action( 'admin_init', array( $this,'esu_admin_init' ) );
			add_action( 'admin_menu', array( $this,'esu_add_pages'  ) );
    	add_action( 'contextual_help', array(&$this,'esu_custom_help_tabs'), 10,3 );
		}

	  /**
	   * Help tabs
	   */

	  function esu_custom_help_tabs($contextual_help, $screen_id, $screen)
	  {
	    // easy-sign-up_page_esu_view_simple_data_page
	    if ( 'toplevel_page_esu_options_pg' !== $screen->id ) // Return early if we're not on the esu options page.
	      return;
	    // Setup help tab args.
			$making_use_of_txt =	__('<h3>Use the following short code in your pages and posts:</h3>
			<p><code>[easy_sign_up title="My Title" <abbr title="Show First and Last Names">fnln="1"</abbr> phone="1" esu_label="My Label" esu_class="my-class-name" <abbr title="Override the redirect">esu_r_url="www.redirecting.com"</abbr>]</code></p>
			<p>All the attributes are optional if you don\'t want them just add the short code without it, e.g.: <code>[easy_sign_up]</code></p>
			<p>Please note that it will not work if your shortcode is on multiple lines. In other words put the shortcode on <b>one</b> line</p>
			<p>If you would like to include the name person who signed up in the Thank You Email just paste:
			<code>#fullname#</code> into the Thank You Email text field where you\'d like to see it.</p>','easy-sign-up');
			$esu_forum_text  = "<h3>". __("Do you need more help?",'easy-sign-up')."</h3><p>". __("If you need support or want to suggest improvements to the plugin please visit the ",'easy-sign-up');
			$esu_forum_text .= '<a href="http://wordpress.org/support/plugin/easy-sign-up/">'.__("plugin's support forum",'easy-sign-up').'</a></p>';

	    $making_use_of_args = array(
	      'id'      => 'esu_making_use_of_tab', //unique id for the tab
	      'title'   => __('Easy Sign Up Help','easy-sign-up'), //unique visible title for the tab
	      'content' => $making_use_of_txt.$esu_forum_text  //actual help text
	    );
	    // Setup extra feed args
	    $esu_extras_args = array(
	      'id'      => 'esu_extras_tab', //unique id for the tab
	      'title'   => __('Easy Extras','easy-sign-up'), //unique visible title for the tab
	      'content' => $this->esu_pro(),  //actual help text
	    );
	    // Setup News args
	    $esu_extras_news_args = array(
	      'id'      => 'esu_news_tab', //unique id for the tab
	      'title'   => __('News','easy-sign-up'), //unique visible title for the tab
	      'content' => $this->esu_news(),  //actual help text
	    );
	    // Add the help tabs.
	    $making_use_of_tabs 	= $screen->add_help_tab( $making_use_of_args );
	    // @ todo rethink this area
	    #$esu_extras_tabs 			= $screen->add_help_tab( $esu_extras_args );
	    $esu_extras_news_tabs = $screen->add_help_tab( $esu_extras_news_args );
	  }

		//add TinyMCE button and register esu stylesheet
		function esu_admin_init()
		{
			// add TinyMCE button
			$this->esu_add_form_buttons();
			if(!isset($_GET['page'])) return;
			$page  = $_GET['page'];
			$esu_page = explode("_", $page);
			if( $esu_page[0] == "esu" )
			{
				wp_register_style( 'esuStylesheet', ESU_URL . 'css/stylesheet.css', false, ESU_VERSION,'all' );
				wp_enqueue_style( 'esuStylesheet' );
			}
		}

		//add menu to admin page
		function esu_add_pages()
		{
			add_menu_page(
				ESU_NAME." &rsaquo; ".__('Options Page','easy-sign-up'),
				ESU_NAME, 'add_users',
				'esu_options_pg',
				array( $this,'esu_options_pg'  ),
				( ESU_URL.'images/icon.png' )
			);
		} //End easy_sign_up_add_pages()

		//create the options page
		function esu_options_pg()
		{
			//options saved message
			$this->esu_options_saved_message();
			$this->esu_options_pg_html();
		}

		function esu_options_saved_message($value='easy_sign_up_saved')
		{
			//options saved message
			if (isset($_REQUEST['action']) && $value == $_REQUEST['action'] ) echo '<div id="message" class="updated fade"><p><strong>'.__('Settings saved.','easy-sign-up').'</strong></p></div>';
		}
		//this is the html for the esu options page
		function esu_options_pg_html()
		{
			$e_options = esu_options_array();
			// save plugin's options

			if ( isset($_REQUEST['action']) &&  'easy_sign_up_saved' == $_REQUEST['action'] ) {
					foreach ($e_options as $value) {
						$temp_val = ( isset($_REQUEST[ $value['id'] ]) ) ? $_REQUEST[ $value['id'] ] : '' ;
						update_option( $value['id'], $temp_val );
					}

					foreach ($e_options as $value) {
						if( isset( $_REQUEST[ $value['id'] ] ) ) {

							switch ( $value['type'] ) {
								case 'text':
									$text_update_value = sanitize_text_field( $_REQUEST[ $value['id'] ] );
									update_option( $value['id'], $text_update_value );
									break;
								case 'hidden':
									$hidden_update_value = sanitize_text_field( $_REQUEST[ $value['id'] ] );
									update_option( $value['id'], $hidden_update_value );
									break;
								case 'wp_editor':
									update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
									break;
								case 'textarea':
									$textarea_update_value = sanitize_text_field( $_REQUEST[ $value['id'] ] );
									update_option( $value['id'], $textarea_update_value );
									break;

								default:
									update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
									break;
							}

						} else {
							delete_option( $value['id'] );
						}
					}
			}
			?>
				<div class="wrap">
					<h2><?php  echo(ESU_NAME);_e(" Options",'easy-sign-up'); ?></h2>
					<?php $this->esu_plug(); ?>
					<form method="post" action="">
						<table class="form-table" id="easy_sign_up_form_table">
								<?php
								foreach ($e_options as $value) {

									switch ( $value['type'] ) {

										case 'hidden':
										?>
										<input 	name="<?php echo $value['id']; ?>"
														id="<?php echo $value['id']; ?>"
														type="<?php echo $value['type']; ?>"
														value="<?php
															if ( get_option( $value['id'] ) != "") {
																echo get_option( $value['id'] );
															} else {
																echo $value['std'];
															} ?>">
										<?php
										break;

										case 'text':
										?>
										<tr valign="top">
											<th scope="row"><label for="<?php echo $value['id']; ?>"><?php echo($value['name']); ?></label></th>
											<td>
								        <input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>"
								        	class="<?php $class = ( isset($value['class']) ) ? $value['class'] :  'regular-text' ; echo($class); ?>"
													type="<?php echo $value['type']; ?>"
													value="<?php
														if ( get_option( $value['id'] ) != "") {
															echo stripslashes( htmlentities( get_option( $value['id'] ) ) );
														} else {
															echo $value['std'];
														} ?>" >
												<?php echo($value['desc']); ?>
											</td>
										</tr>
										<?php
										break;

										case 'wp_editor':
										?>
										<tr valign="top">
											<th scope="row"><label for="<?php echo $value['id']; ?>"><?php echo($value['name']); ?></label></th>
											<td>
												<?php
													$esu_wysiwyg_options = array(
															'media_buttons' => 0,
															'teeny' 				=> 1
														);
													$esu_wysiwyg_options = apply_filters('esu_wysiwyg_options_filter', $esu_wysiwyg_options );
													$esu_wysiwyg_options['textarea_name'] = $value['id'];
													$esu_wysiwyg_contents = (get_option($value['id'])!= "") ? stripslashes( get_option($value['id']) ) : $value['std'] ;
													wp_editor(
														$esu_wysiwyg_contents,
														$value['id'],
														$esu_wysiwyg_options
														);
												?>
												<br>
												<?php echo($value['desc']); ?>
											</td>
										</tr>
										<?php
										break;

										case 'textarea':
										if(isset($_REQUEST['options']))	$ta_options = $value['options'];
										?>
										<tr valign="top">
											<th scope="row"><label for="<?php echo $value['id']; ?>"><?php echo($value['name']); ?></label></th>
											<td>
												<textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>"
													class="widefat"
													cols="<?php echo $value['cols']; ?>"
													rows="<?php echo $value['rows']; ?>"><?php
												if( get_option($value['id']) != "") {
														echo(stripslashes(get_option($value['id'])));
													}else{
														echo($value['std']);
												}?></textarea>
												<br>
												<?php echo($value['desc']); ?>
											</td>
										</tr>
										<?php
										break;
										case 'checkbox':
										if(isset($_REQUEST['options']))	$ta_options = $value['options'];
										?>
										<tr valign="top">
											<th scope="row"><label for="<?php echo $value['id']; ?>"><?php echo($value['name']); ?></label></th>
											<td>
												<?php
													if( get_option($value['id']) == true) {
															$ischecked = 'checked="checked"';
														}else{
															$ischecked = '';
													}
												?>
												<input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="checkbox" value="true" <?php echo($ischecked); ?>>
												<?php echo($value['desc']); ?>
											</td>
										</tr>
										<?php
										break;
										case 'nothing':
										$ta_options = $value['options'];
										?>
										</table>
											<?php echo($value['desc']); ?>
										<table class="form-table">
										<?php
										break;

										default:

										break;
									}
								}
								?>
					      <tr valign="top">
							  <th scope="row">&nbsp;</th>
							  <td>
					            <p class="submit">
					                <input type="hidden" name="action" value="easy_sign_up_saved" >
					                <input class="button-primary" name="save" type="submit" value="<?php _e('Save Your Changes','easy-sign-up'); ?>" >
					            </p>
					          </td>
						  </tr>
						</table>
					</form>
			<!-- END WRAP -->
    <?php
		}

    //admin header html
    function esu_admin_header_html($esu_admin_pg_title='')
    {
      $header_html='
        <div class="wrap">
      		<h2>'.ESU_NAME.$esu_admin_pg_title.'</h2>
      		<!-- START THE CONTENT WRAPPING DIV -->
      		<div class="metabox-holder">
      ';
      return $header_html;
    }

    //admin footer html
		function esu_admin_footer_html()
    {
      $footer_html='
        		</div>
        		<!-- END THE CONTENT WRAPPING DIV -->
        </div>
        <!-- END WRAP -->
      ';
      return $footer_html;
	  }

		//Admin UI widgets/panels
		function esu_plug()
		{ ?>
			<!-- UpGrade -->
			<div id="esu_like_plug">
			  <h3><?php _e('Hello, if you like this plugin please spread the word!','easy-sign-up'); ?></h3>
			  <div class="inside">
			  	<p><?php _e('This plugin has cost me many hours of work, if you use it, please:','easy-sign-up'); ?>
			    <br><a href="http://wordpress.org/support/view/plugin-reviews/easy-sign-up">
			      	<?php echo __('Rate the plugin <span title="Five Stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span> on WordPress.org','easy-sign-up'); ?>
			      </a>,
			      <a href="http://www.beforesite.com/downloads/"><?php _e('Upgrade your plugin','easy-sign-up'); ?></a>,
			      <strong>&#9733; <a href="http://www.greenvilleweb.us/services/?ref=plugin_services"
  						title="<?php _e("Need WordPress Design? Themes and Plugins",'easy-sign-up'); ?>"><?php _e("You can hire me.",'easy-sign-up'); ?></a> &#9733;</strong>
			    </p>
			  </div>
			</div>
			<!-- END Upgrade -->
		<?php
		}

		function esu_news() // has news feed
		{
      $args = array(
        'id'=>'esu_news_feed',
        'ele_class'=>'easy-rss',
        'feed_items'=>5,
        'show_sub_link'=>true,
        'show_content'=>true
      );
			return esu_feeds("http://feeds.feedburner.com/EasySignUpPluginNews",$args);
		}

		function esu_pro()
		{
      $args = array(
        'id'=>'esu_extras_feed',
        'ele_class'=>'easy-rss',
        'feed_items'=>5,
        'show_sub_link'=>true,
        'show_content'=>true
      );
      $url = "http://feeds.feedburner.com/EasySignUpExtras";
      return esu_feeds($url,$args);
		}
		// End right col widgets

		//TinyMCE

		/**
		* Create Our Initialization Function
		* for the WP editor
		*/

		function esu_add_form_buttons()
		{
			if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
			     return;
			}
			if ( get_user_option('rich_editing') == 'true' && !isset($_REQUEST['page']) ) {
			     	add_filter( 'mce_external_plugins', array($this,'esu_add_TinyMCE_plugin') ); // calls the fun to add a button to the wp-editor
			     	add_filter( 'mce_buttons', array($this,'esu_register_button') );
			}
		}

		/**
		* Register Button for the wysiwyg editor
		* in the admin add or edit auction area
		* used by esu_add_form_buttons()
		*/

		function esu_register_button( $buttons )
		{
		 	array_push( $buttons, "esubutton" );
		 	return $buttons;
		}

		/**
		* Register TinyMCE Plugin
		* used by esu_add_form_buttons()
		* Don't move to the load js class
		*/
		function esu_add_TinyMCE_plugin( $plugin_array )
		{
		  $plugin_array['esubutton'] = ESU_URL . 'js/esu-button.js';
		  return $plugin_array;
		}

	} //End Class EasyAdmin

} //End if Class EasyAdmin

// eof