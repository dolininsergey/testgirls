<?php
/*
Plugin Name: WP Youtube Gallery
Plugin URI: http://www.mrwebsolution.in/
Description: "wp-youtube-gallery" is the very simple plugin for add to youtube gallery (In a easy way) on your site!.
Author: Raghunath Gurjar
Author URI: http://raghunathgurjar.wordpress.com
Version: 1.4
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!class_exists('WP_Youtube_Gallery'))
{
    class WP_Youtube_Gallery
    {
	 /**
      * Construct the plugin object
      */
	public function __construct()
	   {
		    // register actions
			add_action('admin_init', array(&$this, 'wpyg_sidebar_init'));
			add_action('admin_menu', array(&$this, 'wpyg_sidebar_menu'));
			add_filter( "plugin_action_links_".plugin_basename( __FILE__ ), array(&$this,'wpyg_add_settings_link' ));
			/** register_activation_hook */
			register_activation_hook( __FILE__, array(&$this, 'init_activation_wpyg_plugins' ) );
			/** register_deactivation_hook */
			register_deactivation_hook( __FILE__, array(&$this, 'init_deactivation_wpyg_plugins' ) );
		}
	/**
      * Admin Menu
      */
	public function wpyg_sidebar_menu()
	{
	  add_submenu_page('edit.php?post_type=wp_youtube_gallery','WP Youtube Gallery Settings Page','Settings','manage_options','wpyg-settings',array(&$this,'wpyg_sidebar_admin_option_page'));
     }
    /**
      *  Register_Setting
      */
    function wpyg_sidebar_init()
    {
		register_setting('wpyg_sidebar_options','wpyg_active');
		register_setting('wpyg_sidebar_options','wpyg_min_h');
		register_setting('wpyg_sidebar_options','wpyg_lightbox');
		register_setting('wpyg_sidebar_options','wpyg_iframe_w');
		register_setting('wpyg_sidebar_options','wpyg_desc');
		register_setting('wpyg_sidebar_options','wpyg_title');
		register_setting('wpyg_sidebar_options','wpyg_content_limit');
		register_setting('wpyg_sidebar_options','wpyg_per_row_posts');
    }
	/**
	*  Plugin Settings Links
	*/
    public function wpyg_add_settings_link( $links ) 
    {
            $settings_link = '<a href="edit.php?post_type=wp_youtube_gallery&page=wpyg-settings">' . __( 'Settings', 'wpyg' ) . '</a>';
            array_unshift( $links, $settings_link );
            return $links;
     }
     /** 
	  * Display the Options form for WP Youtube Gallery
	  *
	*/
   public function wpyg_sidebar_admin_option_page(){ ?>
	<div style="width: 80%; padding: 10px; margin: 10px;"> 
	<h1>WP Youtube Gallery Settings</h1>
   <!-- Start Options Form -->
	<form action="options.php" method="post" id="wpyg-sidebar-admin-form">
	<div id="wpyg-tab-menu"><a id="wpyg-general" class="wpyg-tab-links active" >General</a>  <a  id="wpyg-shortcodes" class="wpyg-tab-links">Shortcodes</a> <a  id="wpyg-support" class="wpyg-tab-links">Support</a> </div>
	<div class="wpyg-setting">
	<!-- General Setting -->	
	<div class="first wpyg-tab" id="div-wpyg-general">
	<h2>General Settings</h2>
	<p> <input type="checkbox" id="wpyg_active" name="wpyg_active" value='1' <?php if(get_option('wpyg_active')!=''){ echo ' checked="checked"'; }?>/><label><?php _e('Enable:');?></label></p>
	<p><input type="checkbox" id="wpyg_title" name="wpyg_title" value='1' <?php if(get_option('wpyg_title')!=''){ echo ' checked="checked"'; }?>/><label><?php _e('Show Title:');?><label> </p>
	<p><input type="checkbox" id="wpyg_desc" name="wpyg_desc" value='1' <?php if(get_option('wpyg_desc')!=''){ echo ' checked="checked"'; }?>/><label><?php _e('Show Description:');?><label></p>  
	 <p><label><?php _e('Video Box Width:');?><label><input type="text" id="wpyg_iframe_w" name="wpyg_iframe_w" size="10" value="<?php echo get_option('wpyg_iframe_w'); ?>" placeholder="100%"> </p>  
	  <p><label><?php _e('Video Minimum Height:');?><label><input type="text" id="wpyg_min_h" name="wpyg_min_h" size="10" value="<?php echo get_option('wpyg_min_h'); ?>" placeholder="auto"> </p> 
	 <p><label><?php _e('Content Limit:');?><label><input type="text" id="wpyg_content_limit" name="wpyg_content_limit" size="10" value="<?php echo get_option('wpyg_content_limit'); ?>" placeholder="200"> </p>  
	 	  
	</div>
	<!-- Shortcodes -->
	<div class="author wpyg-tab" id="div-wpyg-shortcodes">
	<h2>Shortcodes</h2>
	<p><b>[wp_youtube_gallery category_slug="ENTER YOUTUBE CATEGORY SLUG"]</b>:<br> You can add gallery using this shortcode on any page.<br><br>You can add shortcode into your templates files , just need to add given below code into your template files and update category slug <pre>if(function_exists('get_wp_youtube_gallery')){<br> echo do_shortcode('[wp_youtube_gallery category_slug="ENTER YOUTUBE CATEGORY SLUG"]');<br>} <br> </pre></p>
	</div>

	<!-- Support -->
	<div class="last author wpyg-tab" id="div-wpyg-support">
	<h2>Plugin Support</h2>
	<table>
	<tr>
	<td width="50%"><p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZEMSYQUZRUK6A" target="_blank" style="font-size: 17px; font-weight: bold;"><img src="<?php echo  plugins_url( 'images/btn_donate_LG.gif' , __FILE__ );?>" title="Donate for this plugin"></a></p>
	<p><strong>Plugin Author:</strong><br><a href="http://raghunathgurjar.wordpress.com" target="_blank">Raghunath Gurjar</a></p>
	<p><a href="mailto:raghunath.0087@gmail.com" target="_blank" class="contact-author">Contact Author</a></p></td>
	<td><p><strong>My Other Plugins:</strong><br>
	  <ol>
		<li><a href="https://wordpress.org/plugins/custom-share-buttons-with-floating-sidebar" target="_blank">Custom Share Buttons With Floating Sidebar</a></li>
				<li><a href="https://wordpress.org/plugins/protect-wp-admin/" target="_blank">Protect WP-Admin</a></li>
				<li><a href="https://wordpress.org/plugins/wp-sales-notifier/" target="_blank">WP Sales Notifier</a></li>
				<li><a href="https://wordpress.org/plugins/wp-categories-widget/" target="_blank">WP Categories Widget</a></li>
				<li><a href="https://wordpress.org/plugins/wp-protect-content/" target="_blank">WP Protect Content</a></li>
				<li><a href="https://wordpress.org/plugins/wp-version-remover/" target="_blank">WP Version Remover</a></li>
				<li><a href="https://wordpress.org/plugins/wp-posts-widget/" target="_blank">WP Post Widget</a></li>
				<li><a href="https://wordpress.org/plugins/wp-importer" target="_blank">WP Importer</a></li>
				<li><a href="https://wordpress.org/plugins/wp-csv-importer/" target="_blank">WP CSV Importer</a></li>
				<li><a href="https://wordpress.org/plugins/wp-testimonial/" target="_blank">WP Testimonial</a></li>
				<li><a href="https://wordpress.org/plugins/wc-sales-count-manager/" target="_blank">WooCommerce Sales Count Manager</a></li>
				<li><a href="https://wordpress.org/plugins/wp-social-buttons/" target="_blank">WP Social Buttons</a></li>
				<li><a href="https://wordpress.org/plugins/wp-youtube-gallery/" target="_blank">WP Youtube Gallery</a></li>
				<li><a href="https://wordpress.org/plugins/tweets-slider/" target="_blank">Tweets Slider</a></li>
				<li><a href="https://wordpress.org/plugins/rg-responsive-gallery/" target="_blank">RG Responsive Slider</a></li>
				<li><a href="https://wordpress.org/plugins/cf7-advance-security" target="_blank">Contact Form 7 Advance Security WP-Admin</a></li>
				<li><a href="https://wordpress.org/plugins/wp-easy-recipe/" target="_blank">WP Easy Recipe</a></li>
		</ol>
	 </td>
	</tr>
	</table>
	</div>
	</div>
    <span class="submit-btn"><?php echo get_submit_button('Save Settings','button-primary','submit','','');?></span>
    <?php settings_fields('wpyg_sidebar_options'); ?>
	</form>
	<!-- End Options Form -->
		</div>
	<?php
	}
	 /** 
      * register_activation_hook 
      **/
    static function init_activation_wpyg_plugins(){
		 /* Flush rewrite rules for custom post types. */
		 flush_rewrite_rules();

		}
	/** 
	 * register_deactivation_hook 
	 **/
    static function init_deactivation_wpyg_plugins(){
         delete_option('wpyg_active');
		 flush_rewrite_rules();

		} 
	
    } // END class WP_Youtube_Gallery
} // END if(!class_exists('WP_Youtube_Gallery'))

if(class_exists('WP_Youtube_Gallery'))
{
    // instantiate the plugin class
    $wpyg_plugin_template = new WP_Youtube_Gallery();
}

require dirname(__FILE__).'/wpyg-class.php';
/** add js into admin footer */
add_action('admin_footer','init_wpyg_admin_scripts');
if(!function_exists('init_wpyg_admin_scripts')):
function init_wpyg_admin_scripts()
{
wp_register_style( 'wpyg_admin_style', plugins_url( 'css/wpyg-admin.css',__FILE__ ) );
wp_enqueue_style( 'wpyg_admin_style' );
echo $script='<script type="text/javascript">
	/* WP Youtube Gallery js for admin */
	jQuery(document).ready(function(){
		jQuery(".wpyg-tab").hide();
		jQuery("#div-wpyg-general").show();
	    jQuery(".wpyg-tab-links").click(function(){
		var divid=jQuery(this).attr("id");
		jQuery(".wpyg-tab-links").removeClass("active");
		jQuery(".wpyg-tab").hide();
		jQuery("#"+divid).addClass("active");
		jQuery("#div-"+divid).fadeIn();
		})
		})
	</script>';

	}	
endif;	
?>
