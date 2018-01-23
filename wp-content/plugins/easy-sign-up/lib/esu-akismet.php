<?php
/**
 * esu akismet
 */
class esuAkismet {
  /* // the __construct
  function __construct()
  {
  }
  */

  public static function esu_has_akismet() {
    return function_exists( 'akismet_http_post' );
  }

  public static function is_akismet_spam( $esu_post_vars ) {
    global $akismet_api_host, $akismet_api_port;
    $fields = self::esu_get_akismet_fields( $esu_post_vars );
    // Submitting to Akismet
    // akismet_http_post($request, $host, $path, $port = 80, $ip=null)
    $response = akismet_http_post( $fields, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );
    if ($response == '') {
      // if there is an error akismet_http_post will return an empty string
      // see: http://plugins.trac.wordpress.org/browser/akismet/trunk/akismet.php#L172 (Line 172 & line 185)
      return false;
    }
    $return = ($response['1']=='true') ? true : false;
    return $return;
  }

  public static function mark_akismet_spam( $esu_post_vars, $is_spam ) {
    global $akismet_api_host, $akismet_api_port;
    $fields = self::esu_get_akismet_fields( $esu_post_vars );
    $as = $is_spam ? "spam" : "ham";
    //Submitting info do Akismet
    akismet_http_post( $fields, $akismet_api_host,  '/1.1/submit-'.$as, $akismet_api_port );
  }

  private static function esu_get_akismet_fields( $esu_post_vars ) {
    //Gathering Akismet information
    $akismet_info = array();
    $akismet_info['comment_type']         = 'easy_sign_up_form';
    $akismet_info['comment_author']       = $esu_post_vars['name'];
    $akismet_info['comment_author_email'] = $esu_post_vars['email'];
    $akismet_info['contact_form_subject'] = $esu_post_vars['label'];
    $akismet_info['comment_author_IP']    = $esu_post_vars["comment_author_IP"];
    $akismet_info['permalink']            = $esu_post_vars["permalink"];
    $akismet_info['user_ip']              = preg_replace( '/[^0-9., ]/', '', $esu_post_vars["user_ip"] );
    $akismet_info['user_agent']           = $esu_post_vars["user_agent"];
    $akismet_info['referrer']             = $esu_post_vars['referer'];
    $akismet_info['blog']                 = get_option( 'home' );
    return http_build_query( $akismet_info );
  }
} // end class
