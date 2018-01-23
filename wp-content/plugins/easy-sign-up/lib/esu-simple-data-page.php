<?php
//esu-simple-data-page.php

if (!class_exists("EsuSimpleSignUpData")) {
  class EsuSimpleSignUpData extends EsuAdmin
  {
    function __construct()
    {
      // CSS
      #add_action( 'admin_init', array( $this,'esu_save_data_admin_init' ) );
      // Menus
      add_action('admin_menu',  array( $this,'esu_simple_data_admin_page' )  );
      // ESU Plugin Options Hook
      add_action('esu_hook_before_process_email', array($this,'esu_save_simple_data'),10,1);
    }

    function esu_simple_data_admin_page()
    {
      $page_label=__('Simple Data','esu_db_lang');
      $esu_added_db_page = add_submenu_page(
        'esu_options_pg',
        ESU_NAME." &rsaquo; ".$page_label,
        $page_label, 'add_users',
        'esu_view_simple_data_page',
        array($this,'esu_view_simple_data_page')
        );
    }


    function esu_view_simple_data_page()
    {
      $set_nonce = wp_create_nonce( 'esu-delete-simple-data-nonce' );
      $esu_pg_title  =   " ".__("Simple Data",'esu_db_lang');
      echo $this->esu_admin_header_html($esu_pg_title);
      echo '<div id="message" class="updated below-h2">';
      echo "<p>".__('If you would like a more advanced system that includes user system information and an export to spreadsheet functionality please check out our ','easy-sign-up')."<a href=\"http://www.beforesite.com/downloads/easy-sign-up-data-extra/\">Easy Data Extra for the Easy Sign Up</a>.</p>";
      echo '</div>';
        if( isset( $_GET['esu_delete'] ) ){
          $nonce = $_REQUEST['_wpnonce'];
          if ( ! wp_verify_nonce( $nonce, 'esu-delete-simple-data-nonce' ) ) {
              // This nonce is not valid.
            wp_die( 'Security check failed' );
          } else {
            delete_option( 'esu_simple_data' );
          }

        }
        $data =  get_option('esu_simple_data', __('Nothing here yet','easy-sign-up'));
        $this->esu_just_name_and_email($data);
        $this->esu_cleaup_data($data);
        echo "<h2>".__('Delete Sign Up Data','easy-sign-up')."</h2>";
        echo "<p>".__('It may be come necessary to delete your sign up data, just remember to copy and paste it into a text file on your computer.','easy-sign-up')."</p>";
        echo '<p class="alignright"><a class="button button-primary error" href="'.get_admin_url().'admin.php?page=esu_view_simple_data_page&amp;esu_delete=true&amp;_wpnonce='.$set_nonce.'">'.__('Delete Data','easy-sign-up').'</a></p>';
      echo $this->esu_admin_footer_html();
    }

    function esu_save_simple_data($esu_post_vars)
    {

      extract($esu_post_vars);

      if(isset($extra_fields) && !empty($extra_fields)) {
        $extra = json_encode($extra_fields);
      }else{
        $extra='';
      }
      $phone = (isset($phone)) ? $phone : '' ;

      $new_data = array(
          'label' => $label,
          'name'  => $name,
          'firstname' => $fname,
          'lastname'  => $lname,
          'email' => $email,
          'phone' => $phone,
          'extra' => $extra
        );
      $new_data = json_encode($new_data);
      $old_data = get_option('esu_simple_data');
      if($old_data)
      {
        $update = $new_data . "\n" . $old_data;
      }else{
        $update = $new_data;
      }
      update_option( 'esu_simple_data', $update );
    }

    function esu_just_name_and_email($data)
    {
      $array = $this->esu_saved_json_to_array($data);
      echo "<h2>". __('Just Names &amp; Emails','easy-sign-up')."</h2>";
      echo "<textarea style='width:100%;height:200px;'>";
        foreach ($array as $key => $value) {
          echo $value['name']   . ' ';
          echo $value['email']  . ", \n";
        }
      echo "</textarea>";
      echo "<p class='description'>";
        echo __( 'This data is coma separated and can be saved as a CSV file then opened by a spreadsheet application.', 'easy-sign-up');
      echo "</p>";
    }

    function esu_cleaup_data($data)
    {
      echo "<h2>". __('Raw data','easy-sign-up')."</h2>";
      echo "<textarea style='width:100%;height:200px;'>";
        $data = str_replace( array('"','{','}'), " ", $data );
        echo  str_replace( "\\", "", $data );
      echo "</textarea>";
      echo "<p class='description'>";
        echo __( 'This data is coma separated and can be saved as a CSV file then opened by a spreadsheet application.', 'easy-sign-up');
      echo "</p>";
    }

    function esu_saved_json_to_array($data)
    {
      $data_to_array = explode("\n", str_replace("\r", "", $data)); /* see https://wordpress.org/support/topic/issue-with-data-page-on-iis?replies=4#post-7126830 */
      foreach ($data_to_array as $value) {
        $json_to_array[] = json_decode( $value,true );
      }
      return $json_to_array;
    }

  } // end class
} // end class check


add_action( 'plugins_loaded', 'load_simple_data' );
function load_simple_data()
{
  if (!class_exists("EsuDB")) {
    $EsuSimpleSignUpData = new EsuSimpleSignUpData();
  }

}