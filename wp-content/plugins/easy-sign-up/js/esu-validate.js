//
//  esu-validate.js
//  easy-sign-up
//  License: GPLv2 or later
//  Created by Rew Rixom on February 6, 2013.
//  URL: http://greenvilleweb.us, http://greenvilleweb.com, http://beforesite.com
//  Copyright 2013 Greenville Web. All rights reserved.
//

var esu_feedback = '';
var esu_required_input_fb = '';
var esu_required_email_fb = '';
var esu_required_phone_fb = '';
var esu_required_checkbox_fb = '';
var esu_required_textarea_fb = '';

function esu_validate(esu_form){
  var esu_email_reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
  var esu_phone_reg = /^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext|x|ext)\d{1,5}){0,1}$/;
  var esu_required_input_reg = /\besu-required-input/;
  var esu_required_email_reg = /\besu-required-email/;
  var esu_required_phone_reg = /\besu-required-phone/;
  var esu_required_checkbox_reg = /\besu-required-checkbox/;
  var esu_required_textarea_reg = /\besu-required-textarea/;

  if (typeof $ === 'undefined') { var $ = jQuery; }
  $('input','#'+esu_form).add('textarea','#'+esu_form).each(function() {
    var esu_this_placeholder = $(this).attr('placeholder');
    if (esu_this_placeholder == undefined ) {  esu_this_placeholder = $(this).prev('label').text(); };
    var esu_thiselement = $(this);
    var esu_thiselement_val = $(this).val();
    var esu_li = esu_thiselement.parent();

    $.each(this.attributes, function(i, esu_attrib){
      var esu_name = esu_attrib.name;
      var esu_value = esu_attrib.value;
      if ( esu_name == 'type' && esu_value == 'hidden') { return; };

      // text input
      if (esu_value.match(esu_required_input_reg)) {
        if (esu_thiselement_val.length == 0 || esu_thiselement_val==esu_this_placeholder){
          esu_feedback +=  '<li>' + esu_this_placeholder + esu_required_txt + '</li>';
          esu_li.css(esu_err_colors);
        }else{
          esu_required_input_fb = '';
          esu_li.css(esu_good_colors);
        }
      };

      // email input
      if (esu_value.match(esu_required_email_reg)) {
        if (esu_thiselement_val.length == 0 || esu_thiselement_val==esu_this_placeholder){
          esu_feedback +=  '<li>' + esu_this_placeholder + esu_required_txt + '</li>';
          esu_li.css(esu_err_colors);
        }else if (esu_thiselement_val.length !== 0 &&  esu_email_reg.test(esu_thiselement_val) ){
          esu_li.css(esu_good_colors);
        }else if( esu_thiselement_val.length !== 0 &&  !esu_email_reg.test(esu_thiselement_val) ){
          esu_feedback +=  '<li>' + esu_this_placeholder + esu_not_valid_txt + '</li>';
          esu_li.css(esu_err_colors);
        }
      };

      // phone input
      if (esu_value.match(esu_required_phone_reg)) {
        if ( esu_thiselement_val.length == 0 || esu_thiselement_val==esu_this_placeholder ){
          esu_feedback +=  '<li>' + esu_this_placeholder + esu_required_txt + '</li>';
          esu_li.css(esu_err_colors);
        }else if (esu_thiselement_val.length !== 0 &&  esu_phone_reg.test(esu_thiselement_val) ){
          esu_li.css(esu_good_colors);
        }else if (esu_thiselement_val.length !== 0 &&  !esu_phone_reg.test(esu_thiselement_val) ){
          esu_feedback +=  '<li>' + esu_this_placeholder + esu_not_valid_txt + '</li>';
          esu_li.css(esu_err_colors);
        }
      };

      // textarea
      if (esu_value.match(esu_required_textarea_reg)) {
        if (esu_thiselement_val.length == 0 || esu_thiselement_val==esu_this_placeholder){
          esu_feedback +=  '<li>' + esu_this_placeholder + esu_required_txt + '</li>';
          esu_thiselement.parent('li').css(esu_err_colors);
        }else{
          esu_thiselement.parent('li').css(esu_good_colors);
        }
      };

      // checkbox input
      if (esu_value.match(esu_required_checkbox_reg)) {
        if (!esu_thiselement.attr('checked')){
          esu_feedback += '<li>' + esu_this_placeholder + esu_required_txt + '</li>';
          esu_li.css(esu_err_colors);
        }else{
          esu_li.css(esu_good_colors);
        }
      };

    });
  });

  if(esu_feedback != ''){
    if (esu_show_bar==true) {
      var esu_err_box = jQuery('#esu_err');
      jQuery('#esu_err ul').html(esu_feedback);
      esu_err_box.esuCSS();
      esu_err_box.css('display', 'none');
      esu_err_box.fadeIn('fast');
      esu_err_box.click(function() {
        esu_err_box.css('display', 'none');
      });
    };
    esu_feedback = '';
    return false;
  }
}

(function($) {  $('body').prepend('<div id="esu_err" style="display:none;cursor:pointer;"><ul></ul></div>'); })(jQuery);

jQuery.fn.esuCSS = function () {
    this.css(esu_err_colors);
    this.css(esu_err_css);
    return this;
}