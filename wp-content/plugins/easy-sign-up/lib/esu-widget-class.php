<?php
/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'esu_load_widgets' );

/**
 * Register our widget.
 * 'Example_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function esu_load_widgets() {
	register_widget( 'EsuWidget_Widget' );
}

/**
 * Easy sign up Widget  class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class EsuWidget_Widget extends WP_Widget {

  /**
   * Widget setup
   */
  function __construct() {
    /* Widget settings. */
		$widget_ops = array(
			'classname' => 'easysignup',
			'description' => sprintf(__('A widget that displays the %s form.','easy-sign-up'),ESU_NAME) );

		/* Widget control settings. */
		$control_ops = array('id_base' => 'easysignup-widget' );

		/* Create the widget. */
		parent::__construct( 'easysignup-widget',ESU_NAME, $widget_ops, $control_ops );
  }

  /**
   *
   * How to display the widget on the screen.
   */
	function widget( $args, $instance ) {
		// default value for title for sidebar customizer compatibility
		$title = __('Please change the title', 'easy-sign-up');
		extract( $args );
		extract( $instance );
		/* Our variables from the widget settings. */
		$title = apply_filters( 'widget_title', $title );
		/* Before widget (defined by themes). */
		echo $before_widget;
		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )	echo $before_title . $title . $after_title;
		$form_id      ='esu';
		/* The Display the form */
		// esu_form_html($form_id='esu',$title=false, $fnln=true, $phone=true, $esu_label=null, $esu_class='', $esu_r_url=null)
		$fnln  = (isset($fnln) ) ? $fnln  : false ;
		$phone = (isset($phone)) ? $phone : false ;
		$label = (isset($label)) ? $label : null  ;
		echo EsuForms::esu_form_html($form_id,false,$fnln,$phone,$label,'esu-widget',null);
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 *
	 */

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		if (!isset($new_instance['fnln'])) $new_instance['fnln'] = 0;
		$instance['fnln']  = strip_tags( $new_instance['fnln'] );

		if (!isset($new_instance['phone'])) $new_instance['phone'] = 0;
		$instance['phone']  = strip_tags( $new_instance['phone'] );

		$instance['label'] = strip_tags( $new_instance['label'] );
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {
		/* Set up some default widget settings. */
		$defaults = array(
			'title' => ESU_NAME,
			'label' => null,
			'phone' => '1',
			'fnln' => '1'
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:','easy-sign-up'); ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		<!-- Form Label: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'label' ); ?>"><?php _e('Hidden Label:','easy-sign-up'); ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'label' ); ?>" name="<?php echo $this->get_field_name( 'label' ); ?>" value="<?php echo $instance['label']; ?>" />
		</p>
		<p class="description"><?php _e("The label will be included in a hidden form field, allowing you to keep track of different campaigns.",'easy-sign-up') ?></p>
	<?php	/* New options: $fnln,$phone,$old_form,$esu_class	*/ ?>
		<p>
			<label><input type="checkbox" class="checkbox" name="<?php echo $this->get_field_name( 'fnln' );  ?>" value="1" <?php if($instance['fnln'] == '1'){  echo 'checked="checked"';} ?>> <?php _e('Separate the first and last name fields','easy-sign-up'); ?></label><br>
			<label><input type="checkbox" class="checkbox" name="<?php echo $this->get_field_name( 'phone' ); ?>" value="1" <?php if($instance['phone']== '1'){ echo 'checked="checked"';} ?>> <?php _e('Show phone the field','easy-sign-up'); ?></label>
		</p>
	<?php
	}

}