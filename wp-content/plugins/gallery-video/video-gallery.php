<?php

/*
Plugin Name: Huge IT Video Gallery
Plugin URI: http://huge-it.com/wordpress-video-gallery/
Description: Video Gallery plugin was created and specifically designed to show your video files in unusual splendid ways.
Version: 1.5.8
Author: http://huge-it.com/
License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/




add_action('media_buttons_context', 'add_videogallery_my_custom_button');


add_action('admin_footer', 'add_videogallery_inline_popup_content');
add_action( 'wp_ajax_huge_it_video_gallery_ajax', 'huge_it_video_gallery_ajax_callback' );
add_action( 'wp_ajax_nopriv_huge_it_video_gallery_ajax', 'huge_it_video_gallery_ajax_callback' );




function huge_it_video_gallery_ajax_callback(){
    if(!function_exists('get_video_gallery_id_from_url')) {
    function get_video_gallery_id_from_url($url){
    if(strpos($url,'youtube') !== false || strpos($url,'youtu') !== false){ 
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            return array ($match[1],'youtube');
        }
    }else {
        $vimeoid =  explode( "/", $url );
        $vimeoid =  end($vimeoid);
        return array($vimeoid,'vimeo');
    }
}
}
if(!function_exists('youtube_or_vimeo')) {
        function youtube_or_vimeo($videourl){
    if(strpos($videourl,'youtube') !== false || strpos($videourl,'youtu') !== false){   
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videourl, $match)) {
            return 'youtube';
        }
    }
    elseif(strpos($videourl,'vimeo') !== false && strpos($videourl,'video') !== false) {
        $explode = explode("/",$videourl);
        $end = end($explode);
        if(strlen($end) == 8)
            return 'vimeo';
    }
    return 'image';
}
}
////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['task']) && $_POST['task']=="load_videos_content"){
        global $wpdb;
    $page = 1;
    if(!empty($_POST["page"]) && is_numeric($_POST['page']) && $_POST['page']>0){
        $page = $_POST["page"];
        $num=$_POST['perpage'];
        $start = $page * $num - $num; 
        $idofgallery=$_POST['galleryid'];
         $query=$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_videos where videogallery_id = '%d' order by ordering ASC LIMIT %d,%d",$idofgallery,$start,$num);
       $page_images=$wpdb->get_results($query);
        $output = '';
        foreach($page_images as $key=>$row)
    {
        $link = str_replace('__5_5_5__','%',$row->sl_url);
        $video_name=str_replace('__5_5_5__','%',$row->name);
        $id=$row->id;
        $descnohtml=strip_tags(str_replace('__5_5_5__','%',$row->description));
        $result = substr($descnohtml, 0, 50);
        ?>
        
            
                <?php 
                    $imagerowstype=$row->sl_type;
                    if($row->sl_type == ''){$imagerowstype='image';}
                    switch($imagerowstype){
                        case 'image':
                ?>                                  
                            <?php $imgurl=explode(";",$row->image_url); ?>
                           <?php    if($row->image_url != ';'){ 
                            $video='<img id="wd-cl-img'.$key.'" src="'.$imgurl[0].'" alt="" />';
                             } else {
                            $video='<img id="wd-cl-img'.$key.'" src="images/noimage.jpg" alt="" />';
                            
                            } ?>

                <?php
                        break;
                        case 'video':
                ?>
                        <?php
                            $videourl=get_video_gallery_id_from_url($row->image_url);
                            if($videourl[1]=='youtube'){
                                    if(empty($row->thumb_url)){
                                            $thumb_pic='http://img.youtube.com/vi/'.$videourl[0].'/mqdefault.jpg';
                                        }else{
                                            $thumb_pic=$row->thumb_url;
                                        }
                                
                                $video='<img src="'.$thumb_pic.'" alt="" />';                             
                            
                                }else {
                                $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$videourl[0].".php"));
                                if(empty($row->thumb_url)){
                                        $imgsrc=$hash[0]['thumbnail_large'];
                                    }else{
                                        $imgsrc=$row->thumb_url;
                                    }
                            
                                $video='<img src="'.$imgsrc.'" alt="" />';
                            
                            }
                        ?>
                <?php
                        break;
                    }
                ?>
           
                
                <?php if($_POST['showbutton']=='on'){
                    if ($row->link_target=="on"){
                        $target='target="_blank"';
                    }else{
                        $target='';
                    }
                        
                    
                    $button='<div class="button-block"><a href="'.str_replace('__5_5_5__','%',$row->sl_url).'"'.$target.' >'.$_POST['linkbutton'].'</a></div>';
                }else{
                   $button=''; 
                } ?>
            
          
       
      
    
    <?php
            

            $output.='<div class="videoelement_'.$idofgallery.' " tabindex="0" data-symbol="'.$video_name.'"  data-category="alkaline-earth">';
            $output.='<input type="hidden" class="pagenum" value="'.$page.'" />';
            $output.='<div class="image-block_'.$idofgallery.'">';
            $output.=$video;
            $output.='<div class="videogallery-image-overlay"><a href="#'.$id.'"></a></div>';
            //$output.='<div style="clear:both;"></div>';
            $output.='</div>';
            $output.='<div class="title-block_'.$idofgallery.'">';
            $output.='<h3>'.$video_name.'</h3>';
            $output.=$button;
            $output.='</div>';
            $output.='</div>';
                
            
        
     }
        echo json_encode(array("success"=>$output));
        die();
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['task']) && $_POST['task']=="load_videos_lightbox"){
        global $wpdb;
    $page = 1;
    if(!empty($_POST["page"]) && is_numeric($_POST['page']) && $_POST['page']>0){
        $page = $_POST["page"];
        $num=$_POST['perpage'];
        $start = $page * $num - $num; 
        $idofgallery=$_POST['galleryid'];
         $query=$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_videos where videogallery_id = '%d' order by ordering ASC LIMIT %d,%d",$idofgallery,$start,$num);
       $page_images=$wpdb->get_results($query);
        $output = '';
        foreach($page_images as $key=>$row)
    {
        $link = str_replace('__5_5_5__','%',$row->sl_url);
        $video_name=str_replace('__5_5_5__','%',$row->name);
        $descnohtml=strip_tags(str_replace('__5_5_5__','%',$row->description));
        $result = substr($descnohtml, 0, 50);
        ?>
        
            
                <?php 
                    $imagerowstype=$row->sl_type;
                    if($row->sl_type == ''){$imagerowstype='image';}
                    switch($imagerowstype){
                        case 'image':
                ?>                                  
                            <?php $imgurl=explode(";",$row->image_url); ?>
                            <?php  
                             if($row->image_url != ';'){ 
                            $video='<a href="'.$imgurl[0].'" title="'.$video_name.'"><img id="wd-cl-img'.$key.'" src="'.$imgurl[0].'" alt="'.$video_name.'" /></a>';
                            } 
                            else { 
                            $video='<img id="wd-cl-img'.$key.'" src="images/noimage.jpg" alt="" />';
                           
                            } ?>

                <?php
                        break;
                        case 'video':
                ?>
                        <?php
                            $videourl=get_video_gallery_id_from_url($row->image_url);
                            if($videourl[1]=='youtube'){
                                    if(empty($row->thumb_url)){
                                            $thumb_pic='http://img.youtube.com/vi/'.$videourl[0].'/mqdefault.jpg';
                                        }else{
                                            $thumb_pic=$row->thumb_url;
                                        }
                                
                                $video='<a class="youtube huge_it_videogallery_item group1"  href="https://www.youtube.com/embed/'.$videourl[0].'" title="'.$video_name.'">
                                            <img src="'.$thumb_pic.'" alt="'.$video_name.'" />
                                            <div class="play-icon '.$videourl[1].'-icon"></div>
                                        </a>';                             
                            
                                }else {
                                $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$videourl[0].".php"));
                                if(empty($row->thumb_url)){
                                        $imgsrc=$hash[0]['thumbnail_large'];
                                    }else{
                                        $imgsrc=$row->thumb_url;
                                    }
                            
                                $video='<a class="vimeo huge_it_videogallery_item group1" href="http://player.vimeo.com/video/'.$videourl[0].'" title="'.$video_name.'">
                                    <img src="'.$imgsrc.'" alt="" />
                                    <div class="play-icon '.$videourl[1].'-icon"></div>
                                </a>';
                            
                            }
                        ?>
                <?php
                        break;
                    }
                ?>
            
          
         <?php if(str_replace('__5_5_5__','%',$row->name)!=""){
                if ($row->link_target=="on"){
                   $target= 'target="_blank"';
                }else{
                    $target= '';
                }
               $linkimg='<div class="title-block_'.$idofgallery.'"><a href="'.$link.'"'.$target.'>'.$video_name.'</a></div>';
            
            } else{
                $linkimg='';
            }?>
      
    
    <?php
            
            
            $output.='<div class="videoelement_'.$idofgallery.'" tabindex="0" data-symbol="'.$video_name.'"  data-category="alkaline-earth">';
            $output.='<input type="hidden" class="pagenum" value="'.$page.'" />';
            $output.='<div class="image-block_'.$idofgallery.'">';
            $output.=$video;
            //$output.='';
            $output.=$linkimg;
           // $output.='';
            //$output.='<div style="clear:both;"></div>';
            $output.='</div>';
            $output.='</div>';
           
    
            
        
     }
        echo json_encode(array("success"=>$output));
        die();
    }
}

////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['task']) && $_POST['task']=="load_videos_justified"){
        global $wpdb;
    $page = 1;
    if(!empty($_POST["page"]) && is_numeric($_POST['page']) && $_POST['page']>0){
        $page = $_POST["page"];
        $num=$_POST['perpage'];
        $start = $page * $num - $num; 
        $idofgallery=$_POST['galleryid'];
         $query=$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_videos where videogallery_id = '%d' order by ordering ASC LIMIT %d,%d",$idofgallery,$start,$num);
       
        $output = '';
        $page_images=$wpdb->get_results($query);
        foreach($page_images as $key=>$row){
            //var_dump($icon);
            $video_name=str_replace('__5_5_5__','%',$row->name);
            $video_thumb=$row->thumb_url;
         $videourl=get_video_gallery_id_from_url($row->image_url);


         $imagerowstype=$row->sl_type; 
                    if($row->sl_type == ''){$imagerowstype='image';}
                    
                    switch($imagerowstype){
                        case 'image': 
                        
                      $video='<a class="group1" href="'.$videourl.'" title="'.$video_name.'">
                                    <img id="wd-cl-img'.$key.'" alt="'.$video_name.'" src="<?php echo get_huge_image('.$videourl.','.$image_prefix.'); ?>"/>
                                </a>
                                <?php } else { ?>
                                <img alt="'.$video_name.'" id="wd-cl-img'.$key.'" src="images/noimage.jpg"  />'  
                    ?>                                  
                         
                    <?php 
                        break;
                        case 'video':

            if($videourl[1]=='youtube'){
                if(empty($row->thumb_url)){
                                            $thumb_pic='http://img.youtube.com/vi/'.$videourl[0].'/mqdefault.jpg';
                                        }else{
                                            $thumb_pic=$row->thumb_url;
                                        }
                $video = '<a class="youtube huge_it_videogallery_item group1"  href="https://www.youtube.com/embed/'.$videourl[0].'" title="'.$video_name.'">
                                                <img  src="'.$thumb_pic.'" alt="'.$video_name.'" />
                                                <div class="play-icon '.$videourl[1].'-icon"></div>
                                        </a>';
            }else {

                $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$videourl[0].".php"));
                                    
                                    if(empty($row->thumb_url)){
                                        $imgsrc=$hash[0]['thumbnail_large'];
                                    }else{
                                        $imgsrc=$row->thumb_url;
                                    }
                $video = '<a class="vimeo huge_it_videogallery_item group1" href="http://player.vimeo.com/video/'.$videourl[0].'" title="'.$video_name.'">
                                                <img alt="'.$video_name.'" src="'.$imgsrc.'"/>
                                                <div class="play-icon '.$videourl[1].'-icon"></div>
                                        </a>';
            }
                break;
            }
            ?>

            <?php
            $icon=youtube_or_vimeo($row->image_url);
            if($video_thumb != ''){
                 $thumb = '<div class="playbutton '.$icon.'-icon"></div>';
           
            }else{
                 $thumb ="";
            }



            $output .=$video.'<input type="hidden" class="pagenum" value="'.$page.'" />';
                
            
        
        }
        ?>

        <?php
        echo json_encode(array("success"=>$output));
        die();
    }
}
////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['task']) && $_POST['task']=="load_videos_thumbnail"){
        global $wpdb;
    $page = 1;
    if(!empty($_POST["page"]) && is_numeric($_POST['page']) && $_POST['page']>0){
        $page = $_POST["page"];
        $num=$_POST['perpage'];
        $start = $page * $num - $num; 
        $idofgallery=$_POST['galleryid'];
         $query=$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_videos where videogallery_id = '%d' order by ordering ASC LIMIT %d,%d",$idofgallery,$start,$num);
       
        $output = '';
        $page_images=$wpdb->get_results($query);
        foreach($page_images as $key=>$row){
            //var_dump($icon);
            $video_name=str_replace('__5_5_5__','%',$row->name);
            $video_thumb=$row->thumb_url;
         $videourl=get_video_gallery_id_from_url($row->image_url);


         $imagerowstype=$row->sl_type; 
                    if($row->sl_type == ''){$imagerowstype='image';}
                    
                    switch($imagerowstype){
                        case 'image': 
                        $video='<a class="group1" href="'.$videourl[0].'"></a>
                        <img src="'.$row->image_url.'" alt="'.$video_name.'" />';
                    ?>                                  
                         
                    <?php 
                        break;
                        case 'video':

            if($videourl[1]=='youtube'){
                if(empty($row->thumb_url)){
                                            $thumb_pic='http://img.youtube.com/vi/'.$videourl[0].'/mqdefault.jpg';
                                        }else{
                                            $thumb_pic=$row->thumb_url;
                                        }
                $video = '<a  class="youtube huge_it_videogallery_item group1"  href="https://www.youtube.com/embed/'.$videourl[0].'" title="'.$video_name.'"></a>
                                    <img src="'.$thumb_pic.'" alt="'.$video_name.'" />';
            }else {

                $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$videourl[0].".php"));
                                    
                                    if(empty($row->thumb_url)){
                                        $imgsrc=$hash[0]['thumbnail_large'];
                                    }else{
                                        $imgsrc=$row->thumb_url;
                                    }
                $video = '<a  class="vimeo huge_it_videogallery_item group1" href="http://player.vimeo.com/video/'.$videourl[0].'" title="'.$video_name.'"></a>
                                    <img src="'.$imgsrc.'" alt="'.$video_name.'" />';
            }
                break;
            }
            $icon=youtube_or_vimeo($row->image_url);
            if($video_thumb != ''){
                 $thumb = '<div class="playbutton '.$icon.'-icon"></div>';
           
            }else{
                 $thumb ="";
            }
            


            $output .='
                <li class="huge_it_big_li">
                    <input type="hidden" class="pagenum" value="'.$page.'" />
                        '.$video.'

                    <div class="overLayer"></div>
                    <div class="infoLayer">
                        <ul>
                            <li>
                                <h2>
                                    '.$video_name.'
                                </h2>
                            </li>
                            <li>
                                <p>
                                    '.$_POST['thumbtext'].'
                                </p>
                            </li>
                        </ul>
                    </div>
                    
                </li>
            ';
        
        }
        echo json_encode(array("success"=>$output));
        die();
    }
}
///////////////////////////////////////////////////////////////////////////////////////////
    if(isset($_POST['task']) && $_POST['task']=="load_videos"){
        global $wpdb;
    $page = 1;
    if(!empty($_POST["page"]) && is_numeric($_POST['page']) && $_POST['page']>0){
        $page = $_POST["page"];
        $num=$_POST['perpage'];
        $start = $page * $num - $num; 
        $idofgallery=$_POST['galleryid'];
         $query=$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_videos where videogallery_id = '%d' order by ordering ASC LIMIT %d,%d",$idofgallery,$start,$num);
       
        $output = '';
        $page_images=$wpdb->get_results($query);
        foreach($page_images as $key=>$row){
            //var_dump($icon);
            $video_name=str_replace('__5_5_5__','%',$row->name);
             $video_desc=str_replace('__5_5_5__','%',$row->description);
            $video_thumb=$row->thumb_url;
            if($video_thumb==''){
                $thumbimglink='';
            }else{
                $thumbimglink='<img class="thumb_image" style="cursor: pointer;" src="'.$video_thumb.'" alt="" />';
            }
         $videourl=get_video_gallery_id_from_url($row->image_url);
            if($videourl[1]=='youtube'){
                $iframe = '<iframe class="video_view9_img" width="'.$_POST['width'].'" height="'.$_POST['height'].'" src="//www.youtube.com/embed/'.$videourl[0].'" style="border: 0;" allowfullscreen></iframe>';
            }else {
                $iframe = '<iframe class="video_view9_img" width="'.$_POST['width'].'" height="'.$_POST['height'].'" src="//player.vimeo.com/video/'.$videourl[0].'"  style="border: 0;" allowfullscreen></iframe>';
            }
            $icon=youtube_or_vimeo($row->image_url);
            if($video_thumb != ''){
                 $thumb = '<div class="playbutton '.$icon.'-icon"></div>';
           
            }else{
                 $thumb ="";
            }
            if($_POST['position']==1){


            $output .='
                <div class="video_view9_container">
                    <input type="hidden" class="pagenum" value="'.$page.'" />
                    <div class="video_view9_vid_wrapper">

                        <div class="thumb_wrapper" onclick="thevid=document.getElementById("thevideo"); thevid.style.display="block"; this.style.display="none">
                            
                            '.$thumbimglink.$thumb.'
                        </div>
                        <div id="thevideo" style="display: block;">
                            '.$iframe.'
                        </div>
                    </div>
                    <h1 class="video_new_view_title">'.$video_name.'</h1>
                    <div class="video_new_view_desc">'.$video_desc.'</div>
                </div>
                <div class="clear"></div>
            ';
        }elseif($_POST['position']==2){


            $output .='
                <div class="video_view9_container">
                    <input type="hidden" class="pagenum" value="'.$page.'" />
                    <h1 class="video_new_view_title">'.$video_name.'</h1>
                    <div class="video_view9_vid_wrapper">

                        <div class="thumb_wrapper" onclick="thevid=document.getElementById("thevideo"); thevid.style.display="block"; this.style.display="none">
                            
                            '.$thumbimglink.$thumb.'
                        </div>
                        <div id="thevideo" style="display: block;">
                            '.$iframe.'
                        </div>
                    </div>
                    
                    <div class="video_new_view_desc">'.$video_desc.'</div>
                </div>
                <div class="clear"></div>
            ';
            }elseif($_POST['position']==3){


            $output .='
                <div class="video_view9_container">
                    <input type="hidden" class="pagenum" value="'.$page.'" />
                    <h1 class="video_new_view_title">'.$video_name.'</h1>
                    <div class="video_new_view_desc">'.$video_desc.'</div>
                    <div class="video_view9_vid_wrapper">

                        <div class="thumb_wrapper" onclick="thevid=document.getElementById("thevideo"); thevid.style.display="block"; this.style.display="none">
                            
                            '.$thumbimglink.$thumb.'
                        </div>
                        <div id="thevideo" style="display: block;">
                            '.$iframe.'
                        </div>
                    </div>
                    
                    
                </div>
                <div class="clear"></div>
            ';
            }
        }
        echo json_encode(array("success"=>$output));
        die();
    }
}
} 

function add_videogallery_my_custom_button($context) {
  

  $img = plugins_url( '/images/post.button.png' , __FILE__ );
  

  $container_id = 'huge_it_videogallery';
  

  $title = 'Select Huge IT Video Gallery to insert into post';

  $context .= '<a class="button thickbox" title="Select Video Gallery to insert into post"    href="#TB_inline?width=400&inlineId='.$container_id.'">
		<span class="wp-media-buttons-icon" style="background: url('.$img.'); background-repeat: no-repeat; background-position: left bottom;"></span>
	Add Video Gallery
	</a>';
  
  return $context;
}
/********************************Add Post Popup ajax function**/
    add_action('wp_ajax_my_action-video-gal', 'huge_it_video_gal_my_action_callback_frontend');
    add_action('wp_ajax_nopriv_my_action-video-gal', 'huge_it_video_gal_my_action_callback_frontend' );
    function huge_it_video_gal_my_action_callback_frontend(){
        //var_dump($_POST);
        global $wpdb;
        if($_POST['post'] == 'video_gal_change_options'){
            
            if(isset($_POST['id'])){
                $id = $_POST['id'];
                $query=$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_galleries WHERE id = %d", $id);
                $row=$wpdb->get_row($query);
                $response = array(  'huge_it_sl_effects'            => $row->huge_it_sl_effects,
                                    'sl_height'                     => $row->sl_height,
                                    'sl_width'                      => $row->sl_width,
                                    'pause_on_hover'                => $row->pause_on_hover,
                                    'videogallery_list_effects_s'   => $row->videogallery_list_effects_s,
                                    'sl_pausetime'                  => $row->description,
                                    'sl_changespeed'                => $row->param,
                                    'sl_position'                   => $row->sl_position,
                                    'display_type'                  => $row->display_type,
                                    'content_per_page'              => $row->content_per_page);
                
                echo json_encode($response);
                
            }
        }
        /**************************Options DB update****************************************/
        if($_POST['post'] == 'videoGalSaveOptions'){
            if(isset($_POST["video_id"])){
                $id = $_POST["video_id"];
              
                $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  huge_it_sl_effects = '%s'  WHERE id = %d ", sanitize_text_field($_POST["huge_it_sl_effects"]), $id));
                if(isset($_POST["display_type"]) || isset($_POST["content_per_page"])){
                    $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  display_type = '%s'  WHERE id = %d ", sanitize_text_field($_POST["display_type"]), $id));
                    $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  content_per_page = '%s'  WHERE id = %d ", sanitize_text_field($_POST["content_per_page"]), $id));
                }    
                $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  sl_width = '%s'  WHERE id = %d ", sanitize_text_field($_POST["sl_width"]), $id));
                $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  sl_height = '%s'  WHERE id = %d ", sanitize_text_field($_POST["sl_height"]), $id));
                $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  videogallery_list_effects_s = '%s'  WHERE id = %d ", sanitize_text_field($_POST["videogallery_list_effects_s"]), $id));
                $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  description = '%s'  WHERE id = %d ", sanitize_text_field($_POST["sl_pausetime"]), $id));
                $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  param = '%s' WHERE id = %d ", $_POST["sl_changespeed"], $id));
                $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  sl_position = '%s' WHERE id = %d ", $_POST["sl_position"], $id));
                $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."huge_it_videogallery_galleries SET  pause_on_hover = '%s' WHERE id = %d ", $_POST["pause_on_hover"], $id));
            }
        }
        die();
    }
    /**************************************************************************************/


function add_videogallery_inline_popup_content() {
?>
<script type="text/javascript">
                    var pause_on_hover;
                    var gal_sel;
                    var response;
                    var video_div = "#videogallery-post-insert-unique-options";

                jQuery(document).ready(function() {

                     jQuery(video_div + ' input[name="pause_on_hover"]').change(function(){ 
                        if(jQuery(video_div + ' input[name="pause_on_hover"]').prop('checked')  == false){
                            jQuery(video_div + ' input[name="pause_on_hover"]').val('off');
                        }
                        else if(jQuery(video_div + ' input[name="pause_on_hover"]').prop('checked')  == true){
                            jQuery(video_div + ' input[name="pause_on_hover"]').val('on');
                        }
                    }); 
                    
                    
                    
                    jQuery('#hugeitvideogalleryinsert').on('click', function() {
                        
                        var id = jQuery('#huge_it_videogallery-select option:selected').val();
                        var huge_it_sl_effects = jQuery(video_div + ' #huge_it_sl_effects').val();
                        var sl=huge_it_sl_effects;
                        var display_type = jQuery(video_div + ' #videogallery-current-options-'+sl+' select[id="display_type"]').val();
                        var content_per_page = jQuery(video_div + ' #videogallery-current-options-'+sl+' input[id="content_per_page"]').val();
                        var sl_width = jQuery(video_div + ' input[name="sl_width"]').val();
                        var sl_height = jQuery(video_div + ' input[name="sl_height"]').val();
                        var videogallery_list_effects_s = jQuery(video_div + ' select[name="videogallery_list_effects_s"]').val();
                        var sl_pausetime = jQuery(video_div + ' input[name="sl_pausetime"]').val();
                        var sl_changespeed = jQuery(video_div + ' input[name="sl_changespeed"]').val();
                        var sl_position = jQuery(video_div + ' select[name="sl_position"]').val();
                        pause_on_hover = jQuery(video_div + ' input[name="pause_on_hover"]').val();
                        var data = {
                            video_id:                       id,
                            action:                         'my_action-video-gal',
                            post:                           'videoGalSaveOptions',
                            huge_it_sl_effects:             huge_it_sl_effects,
                            display_type:                   display_type,
                            content_per_page:               content_per_page,
                            sl_width:                       sl_width,
                            sl_height:                      sl_height,
                            videogallery_list_effects_s:    videogallery_list_effects_s,
                            sl_pausetime:                   sl_pausetime,
                            sl_changespeed:                 sl_changespeed,
                            sl_position:                    sl_position,
                            pause_on_hover:                 pause_on_hover
                        };
                        
                        jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data, function(response) {
                            //console.log(data);                
                        });
                        window.send_to_editor('[huge_it_videogallery id="' + id + '"]');
                        tb_remove();
                    })
                    if(jQuery(video_div + ' select[name="display_type"]').val()== 2){
                        jQuery(video_div + ' li[id="content_per_page"]').hide();
                    }else{
                          jQuery(video_div + ' li[id="content_per_page"]').show();
                    }
                    jQuery(video_div + ' select[name="display_type"]').on('change' ,function(){
                        if(jQuery(this).val()== 2){
                            jQuery(video_div + ' li[id="content_per_page"]').hide();
                        }else{
                            jQuery(video_div + ' li[id="content_per_page"]').show();
                        }
                    })
                    jQuery(video_div).on('change',function(){
                        jQuery(this).find( 'div[id^="videogallery-current-options"]').each(function(){
                            if(!jQuery(this).hasClass( "active" )){
                                jQuery(this).find('ul li input[name="content_per_page"]').attr('name', '');
                                jQuery(this).find('ul li select[name="display_type"]').attr('name', '');
                            }else{
                                jQuery(this).find('ul li input[id="content_per_page"]').attr('name', 'content_per_page');
                                jQuery(this).find('ul li select[id="display_type"]').attr('name', 'display_type');
                            }
                        });
                        if(jQuery(video_div + ' select[name="display_type"]').val()== 2){
                            jQuery(video_div + ' li[id="content_per_page"]').hide();
                        }else{
                            jQuery(video_div + ' li[id="content_per_page"]').show();
                    }
                    });
                    jQuery(video_div + ' #huge_it_sl_effects').change(function(){
                        var sel = jQuery(this).val();
                        jQuery( video_div + ' div[id^="videogallery-current-options"]').each(function(){
                            if(jQuery(this).hasClass( "active" )){
                                jQuery(this).removeClass("active");
                            }
                        });
                        if(sel == 0){
                            jQuery(video_div + ' #videogallery-current-options-0').addClass('active');
                        }
                        if(sel == 3){
                            jQuery(video_div + ' #videogallery-current-options-3').addClass('active');
                        }
                        if(sel == 4){
                            jQuery(video_div + ' #videogallery-current-options-4').addClass('active');
                        }
                        if(sel == 5){
                            jQuery(video_div + ' #videogallery-current-options-5').addClass('active');
                        }
                        if(sel == 6){
                            jQuery(video_div + ' #videogallery-current-options-6').addClass('active');
                        }
                        if(sel == 7){
                            jQuery(video_div + ' #videogallery-current-options-7').addClass('active');
                        }
                    });
                    jQuery(video_div + ' #huge_it_sl_effects').change();
                    jQuery('#huge_it_videogallery-select').change(function (){
                        gal_sel = jQuery(this).val(); 
                        var data = {
                          action: 'my_action-video-gal',
                          post: 'video_gal_change_options',
                          id: gal_sel
                        };
                        jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data, function(response){
                            response = JSON.parse(response);
                            jQuery(video_div + ' #huge_it_sl_effects').val(response.huge_it_sl_effects);
                            jQuery(video_div + ' #huge_it_sl_effects').change();
                            jQuery(video_div + ' select[name="display_type"]').val(response.display_type);
                            jQuery(video_div + ' input[name="content_per_page"]').val(response.content_per_page);
                            jQuery(video_div + ' input[name="sl_width"]').val(response.sl_width);
                            jQuery(video_div + ' input[name="sl_height"').val(response.sl_height);
                            jQuery(video_div + ' select[name="videogallery_list_effects_s"]').val(response.videogallery_list_effects_s);
                            jQuery(video_div + ' input[name="sl_pausetime"').val(response.sl_pausetime);
                            jQuery(video_div + ' input[name="sl_changespeed"').val(response.sl_changespeed);
                            jQuery(video_div + ' select[name="sl_position"').val(response.sl_position);
                            
                            if(jQuery(video_div + ' select[name="display_type"]').val()== 2){
                                jQuery(video_div + ' li[id="content_per_page"]').hide();
                            }else{
                                  jQuery(video_div + ' li[id="content_per_page"]').show();
                            }
                            
                            jQuery(video_div + ' input[name="pause_on_hover"]').val(response.pause_on_hover);
                            if(jQuery(video_div + ' input[name="pause_on_hover"]').val()  == 'on'){
                                jQuery(video_div + ' input[name="pause_on_hover"]').attr('checked','checked');
                            }
                            else jQuery(video_div + ' input[name="pause_on_hover"]').removeAttr('checked');
                            
                        });
                        
                    });
                });
</script>
<style>
    .videogallery-current-options {display:none;}
    .videogallery-current-options.active {display:block;}
    #videogallery-post-insert-unique-options label{
        width: 145px;
        display:inline-block;
    }
    #hugeitvideogalleryinsert{
        margin-left:10px;
    }
    #videogallery-post-insert-unique-options input[type='number']{
        width:79px;
    }
    #videogallery-post-insert-unique-options input[type='text'],#videogallery-post-insert-unique-options select{
        width:141px;
    }
    #huge_it_sl_effects{
        width:226px;
    }
    #videogallery-post-insert-unique-options input[type='checkbox']{
        margin-left: 1px;
    }
    #TB_ajaxContent{
        overflow:visible;
        width:700px;
    }
    #TB_window{
            background-color: #f1f1f1;
    }
    #videogallery-post-insert-unique-options{
        border:none;
        background-color: #fff;
        box-shadow: none;
    }
	#major-publishing-actions {
		background-color: #fff;
		border-top: none;
	}
</style>

<div id="huge_it_videogallery" style="display:none;">
  <h3>Select Huge IT Video Gallery to insert into post</h3>
  <?php 
      global $wpdb;
      $query="SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_galleries order by id ASC";
               $shortcodevideogallerys=$wpdb->get_results($query);
               ?>

 <?php  if (count($shortcodevideogallerys)) {
                            echo "<select id='huge_it_videogallery-select'>";
                            foreach ($shortcodevideogallerys as $shortcodevideogallery) {
                                echo "<option value='".$shortcodevideogallery->id."'>".$shortcodevideogallery->name."</option>";
                            }
                            echo "</select>";
                            echo "<button class='button button-primary' id='hugeitvideogalleryinsert'>Insert Video Gallery</button>";
                        } else {
                            echo "No slideshows found", "huge_it_videogallery";
                        }
        $query="SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_galleries";
	  $firstrow=$wpdb->get_row($query);
	  if(isset($_POST["huge_it_videogallery_galleries"])){
	  $id=$_POST["huge_it_videogallery_galleries"];
	  }
	  else{
	  $id=$firstrow->id;
	  }
        $query=$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_galleries WHERE id= %d",$id);
        $row=$wpdb->get_row($query);  
        //var_dump($row);
                        ?>
  <div id="videogallery-post-insert-unique-options" class="postbox">
    <h3>Current Video Gallery Options</h3> 
    <ul id="videogallery-unique-options-list">
                                                  <li>
                                                          <label for="huge_it_sl_effects">Views</label>
                                                          <select name="huge_it_sl_effects" id="huge_it_sl_effects">
                                                                          <option <?php if($row->huge_it_sl_effects == '0'){ echo 'selected'; } ?>  value="0">Video Gallery/Content-Popup</option>
                                                                          <option <?php if($row->huge_it_sl_effects == '1'){ echo 'selected'; } ?>  value="1">Content Video Slider</option>
                                                                          <option <?php if($row->huge_it_sl_effects == '5'){ echo 'selected'; } ?>  value="5">Lightbox-Video Gallery</option>
                                                                          <option <?php if($row->huge_it_sl_effects == '3'){ echo 'selected'; } ?>  value="3">Video Slider</option>
                                                                          <option <?php if($row->huge_it_sl_effects == '4'){ echo 'selected'; } ?>  value="4">Thumbnails View</option>
                                                                          <option <?php if($row->huge_it_sl_effects == '6'){ echo 'selected'; } ?>  value="6">Justified</option>
                                                                          <option <?php if($row->huge_it_sl_effects == '7'){ echo 'selected'; } ?>  value="7">Blog Style Gallery</option>
                                                          </select>
                                                  </li>
                                          <script>
                                                  jQuery(document).ready(function ($){
                                                          //alert('hi');
                                                          //$('div[id^="list_"]')
                                                                  

                                                  })
                                          </script>
                                          <div id="videogallery-current-options-0" class="videogallery-current-options <?php if($row->huge_it_sl_effects == 0){ echo ' active'; }  ?>">
                                                  <ul id="view4">
                                                          <?php //print_r($row);?>
                                                          <?php //var_dump($row->display_type);?>
                                                            <li>
                                                                  <label for="display_type">Displaying Content</label>
                                                                  <select id="display_type" name="display_type">

                                                                            <option <?php if($row->display_type == 0){ echo 'selected'; } ?>  value="0">Pagination</option>
                                                                                  <option <?php if($row->display_type == 1){ echo 'selected'; } ?>   value="1">Load More</option>
                                                                                  <option <?php if($row->display_type == 2){ echo 'selected'; } ?>   value="2">Show All</option>

                                                                  </select>
                                                                  </li>
                                                          <li id="content_per_page">
                                                                  <label for="content_per_page">Videos Per Page</label>
                                                                  <input type="number" name="content_per_page" id="content_per_page" value="<?php echo $row->content_per_page; ?>" class="text_area" />
                                                          </li>



                                                  </ul>
                                          </div>	
                                          <div id="videogallery-current-options-3" class="videogallery-current-options <?php if($row->huge_it_sl_effects == 3){ echo ' active'; }  ?>">
                                          <ul id="slider-unique-options-list">
                                                  <li>
                                                          <label for="sl_width">Width</label>
                                                          <input type="number" name="sl_width" id="sl_width" value="<?php echo $row->sl_width; ?>" class="text_area" />
                                                  </li>
                                                  <li>
                                                          <label for="sl_height">Height</label>
                                                          <input type="number" name="sl_height" id="sl_height" value="<?php echo $row->sl_height; ?>" class="text_area" />
                                                  </li>
                                                  <li>
                                                          <label for="pause_on_hover">Pause on hover</label>
                                                          <input type="checkbox" name="pause_on_hover"  value="<?php echo $row->pause_on_hover;?>" id="pause_on_hover"  <?php if($row->pause_on_hover  == 'on'){ echo 'checked="checked"'; } ?> />
                                                  </li>
                                                  <li>
                                                          <label for="videogallery_list_effects_s">Effects</label>
                                                          <select name="videogallery_list_effects_s" id="videogallery_list_effects_s">
                                                                          <option <?php if($row->videogallery_list_effects_s == 'none'){ echo 'selected'; } ?>  value="none">None</option>
                                                                          <option <?php if($row->videogallery_list_effects_s == 'cubeH'){ echo 'selected'; } ?>   value="cubeH">Cube Horizontal</option>
                                                                          <option <?php if($row->videogallery_list_effects_s == 'cubeV'){ echo 'selected'; } ?>  value="cubeV">Cube Vertical</option>
                                                                          <option <?php if($row->videogallery_list_effects_s == 'fade'){ echo 'selected'; } ?>  value="fade">Fade</option>
                                                          </select>
                                                  </li>

                                                  <li>
                                                          <label for="sl_pausetime">Pause time</label>
                                                          <input type="number" name="sl_pausetime" id="sl_pausetime" value="<?php echo $row->description; ?>" class="text_area" />
                                                  </li>
                                                  <li>
                                                          <label for="sl_changespeed">Change speed</label>
                                                          <input type="number" name="sl_changespeed" id="sl_changespeed" value="<?php echo $row->param; ?>" class="text_area" />
                                                  </li>
                                                  <li>
                                                          <label for="slider_position">Slider Position</label>
                                                          <select name="sl_position" id="slider_position">
                                                                          <option <?php if($row->sl_position == 'left'){ echo 'selected'; } ?>  value="left">Left</option>
                                                                          <option <?php if($row->sl_position == 'right'){ echo 'selected'; } ?>   value="right">Right</option>
                                                                          <option <?php if($row->sl_position == 'center'){ echo 'selected'; } ?>  value="center">Center</option>
                                                          </select>
                                                  </li>
                                          </ul>
                                          </div>
  <!-- ///////////////////////////////////////////////////////// -->
                                          <div id="videogallery-current-options-4" class="videogallery-current-options <?php if($row->huge_it_sl_effects == 4){ echo ' active'; }  ?>">
                                                  <ul id="view4">
                                                          <?php //print_r($row);?>
                                                          <?php //var_dump($row->display_type);?>
                                                            <li>
                                                                  <label for="display_type">Displaying Content</label>
                                                                  <select id="display_type" name="display_type">

                                                                            <option <?php if($row->display_type == 0){ echo 'selected'; } ?>  value="0">Pagination</option>
                                                                                  <option <?php if($row->display_type == 1){ echo 'selected'; } ?>   value="1">Load More</option>
                                                                                  <option <?php if($row->display_type == 2){ echo 'selected'; } ?>   value="2">Show All</option>

                                                                  </select>
                                                                  </li>
                                                          <li id="content_per_page">
                                                                  <label for="content_per_page">Videos Per Page</label>
                                                                  <input type="number" name="content_per_page" id="content_per_page" value="<?php echo $row->content_per_page; ?>" class="text_area" />
                                                          </li>



                                                  </ul>
                                          </div>
                                          <div id="videogallery-current-options-5" class="videogallery-current-options <?php if($row->huge_it_sl_effects == 5){ echo ' active'; }  ?>">
                                                  <ul id="view4">
                                                          <?php //print_r($row);?>
                                                          <?php //var_dump($row->display_type);?>
                                                            <li>
                                                                  <label for="display_type">Displaying Content</label>
                                                                  <select id="display_type" name="display_type">

                                                                            <option <?php if($row->display_type == 0){ echo 'selected'; } ?>  value="0">Pagination</option>
                                                                                  <option <?php if($row->display_type == 1){ echo 'selected'; } ?>   value="1">Load More</option>
                                                                                  <option <?php if($row->display_type == 2){ echo 'selected'; } ?>   value="2">Show All</option>

                                                                  </select>
                                                                  </li>
                                                          <li id="content_per_page">
                                                                  <label for="content_per_page">Videos Per Page</label>
                                                                  <input type="number" name="content_per_page" id="content_per_page" value="<?php echo $row->content_per_page; ?>" class="text_area" />
                                                          </li>



                                                  </ul>
                                          </div>
                                          <div id="videogallery-current-options-6" class="videogallery-current-options <?php if($row->huge_it_sl_effects == 6){ echo ' active'; }  ?>">
                                                  <ul id="view4">
                                                          <?php //print_r($row);?>
                                                          <?php //var_dump($row->display_type);?>
                                                            <li>
                                                                  <label for="display_type">Displaying Content</label>
                                                                  <select id="display_type" name="display_type">

                                                                            <option <?php if($row->display_type == 0){ echo 'selected'; } ?>  value="0">Pagination</option>
                                                                                  <option <?php if($row->display_type == 1){ echo 'selected'; } ?>   value="1">Load More</option>
                                                                                  <option <?php if($row->display_type == 2){ echo 'selected'; } ?>   value="2">Show All</option>

                                                                  </select>
                                                                  </li>
                                                          <li id="content_per_page">
                                                                  <label for="content_per_page">Videos Per Page</label>
                                                                  <input type="number" name="content_per_page" id="content_per_page" value="<?php echo $row->content_per_page; ?>" class="text_area" />
                                                          </li>



                                                  </ul>
                                          </div>
                                          <div id="videogallery-current-options-7" class="videogallery-current-options <?php if($row->huge_it_sl_effects == 7){ echo ' active'; }  ?>">
                                          <ul id="view7">

                                                    <li>
                                                          <label for="display_type">Displaying Content</label>
                                                          <select id="display_type" name="display_type">

                                                                    <option <?php if($row->display_type == 0){ echo 'selected'; } ?>  value="0">Pagination</option>
                                                                          <option <?php if($row->display_type == 1){ echo 'selected'; } ?>   value="1">Load More</option>
                                                                          <option <?php if($row->display_type == 2){ echo 'selected'; } ?>   value="2">Show All</option>

                                                          </select>
                                                          </li>
                                                  <li id="content_per_page">
                                                          <label for="content_per_page">Videos Per Page</label>
                                                          <input type="number" name="content_per_page" id="content_per_page" value="<?php echo $row->content_per_page; ?>" class="text_area" />
                                                  </li>



                                          </ul>
                                          </div>

                                          </ul>

    
    </div>
  </div>
<?php
}
///////////////////////////////////shortcode update/////////////////////////////////////////////


add_action('init', 'hugesl_videogallery_do_output_buffer');
function hugesl_videogallery_do_output_buffer() {
        ob_start();
}
add_action('init', 'videogallery_lang_load');

function videogallery_lang_load()
{
    load_plugin_textdomain('sp_videogallery', false, basename(dirname(__FILE__)) . '/Languages');

}


function huge_it_videogallery_images_list_shotrcode($atts)
{
    extract(shortcode_atts(array(
        'id' => 'no huge_it videogallery',
    
    ), $atts));




    return huge_it_videogallery_images_list($atts['id']);

}


/////////////// Filter videogallery


function videogallery_after_search_results($query)
{
    global $wpdb;
    if (isset($_REQUEST['s']) && $_REQUEST['s']) {
        $serch_word = htmlspecialchars(($_REQUEST['s']));
        $query = str_replace($wpdb->prefix . "posts.post_content", gen_string_videogallery_search($serch_word, $wpdb->prefix . 'posts.post_content') . " " . $wpdb->prefix . "posts.post_content", $query);
    }
    return $query;

}

add_filter('posts_request', 'videogallery_after_search_results');


function gen_string_videogallery_search($serch_word, $wordpress_query_post)
{
    $string_search = '';

    global $wpdb;
    if ($serch_word) {
        $rows_videogallery = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "huge_it_videogallery_galleries WHERE (description LIKE %s) OR (name LIKE %s)", '%' . $serch_word . '%', "%" . $serch_word . "%"));

        $count_cat_rows = count($rows_videogallery);

        for ($i = 0; $i < $count_cat_rows; $i++) {
            $string_search .= $wordpress_query_post . ' LIKE \'%[huge_it_videogallery id="' . $rows_videogallery[$i]->id . '" details="1" %\' OR ' . $wordpress_query_post . ' LIKE \'%[huge_it_videogallery id="' . $rows_videogallery[$i]->id . '" details="1"%\' OR ';
        }
		
        $rows_videogallery = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "huge_it_videogallery_galleries WHERE (name LIKE %s)","'%" . $serch_word . "%'"));
        $count_cat_rows = count($rows_videogallery);
        for ($i = 0; $i < $count_cat_rows; $i++) {
            $string_search .= $wordpress_query_post . ' LIKE \'%[huge_it_videogallery id="' . $rows_videogallery[$i]->id . '" details="0"%\' OR ' . $wordpress_query_post . ' LIKE \'%[huge_it_videogallery id="' . $rows_videogallery[$i]->id . '" details="0"%\' OR ';
        }

        $rows_single = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "huge_it_videogallery_videos WHERE name LIKE %s","'%" . $serch_word . "%'"));

        $count_sing_rows = count($rows_single);
        if ($count_sing_rows) {
            for ($i = 0; $i < $count_sing_rows; $i++) {
                $string_search .= $wordpress_query_post . ' LIKE \'%[huge_it_videogallery_Product id="' . $rows_single[$i]->id . '"]%\' OR ';
            }

        }
    }
    return $string_search;
}


///////////////////// end filter


add_shortcode('huge_it_videogallery', 'huge_it_videogallery_images_list_shotrcode');




function   huge_it_videogallery_images_list($id)
{

    require_once("Front_end/video_gallery_front_end_view.php");
    require_once("Front_end/video_gallery_front_end_func.php");
    if (isset($_GET['product_id'])) {
        if (isset($_GET['view'])) {
            if ($_GET['view'] == 'huge_itvideogallery') {
                return showPublishedvideogallery_1($id);
            } else {
                return front_end_single_product($_GET['product_id']);
            }
        } else {
            return front_end_single_product($_GET['product_id']);
        }
    } else {
        return showPublishedvideogallery_1($id);
    }
}




add_filter('admin_head', 'huge_it_videogallery_ShowTinyMCE');
function huge_it_videogallery_ShowTinyMCE()
{
    // conditions here
    wp_enqueue_script('common');
    wp_enqueue_script('jquery-color');
    wp_print_scripts('editor');
    if (function_exists('add_thickbox')) add_thickbox();
    wp_print_scripts('media-upload');
    if (version_compare(get_bloginfo('version'), 3.3) < 0) {
        if (function_exists('wp_tiny_mce')) wp_tiny_mce();
    }
    wp_admin_css();
    wp_enqueue_script('utils');
    do_action("admin_print_styles-post-php");
    do_action('admin_print_styles');
}


function all_videogallery_frontend_scripts_and_styles() {
    if ( wp_script_is( 'jquery', 'registered' ) )
        wp_enqueue_script('jquery');
    else { 
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', __FILE__ ); 
        wp_enqueue_script('jquery');
    }
//    wp_register_script('colorbox-js', plugins_url('/js/jquery.colorbox.js', __FILE__)); 
//    wp_enqueue_script('colorbox-js');
//    wp_register_script('hugeitmicro-js', plugins_url('/js/jquery.hugeitmicro.min.js', __FILE__)); 
//    wp_enqueue_script('hugeitmicro-js');
    wp_register_script('video_gallery-all-js', plugins_url('/js/video_gallery-all.js', __FILE__)); 
    wp_enqueue_script('video_gallery-all-js' );
    
    wp_register_style( 'style2-os-css', plugins_url('/style/style2-os.css', __FILE__) );
    wp_enqueue_style( 'style2-os-css' );
    wp_register_style( 'lightbox-css', plugins_url('/style/lightbox.css', __FILE__) );   
    wp_enqueue_style( 'lightbox-css' );
    wp_register_style( 'videogallery-all-css', plugins_url('/style/videogallery-all.css', __FILE__) );   
    wp_enqueue_style( 'videogallery-all-css');
    wp_register_style( 'fontawesome-css', plugins_url('/style/css/font-awesome.css', __FILE__) );   
    wp_enqueue_style( 'fontawesome-css' );
}
add_action('wp_enqueue_scripts', 'all_videogallery_frontend_scripts_and_styles');

add_action('admin_menu', 'huge_it_videogallery_options_panel');
function huge_it_videogallery_options_panel()
{
    $page_cat = add_menu_page('Theme page title', 'Video Gallery', 'delete_pages', 'videogallerys_huge_it_videogallery', 'videogallerys_huge_it_videogallery', plugins_url('images/video_gallery_icon.png', __FILE__));
    $page_option = add_submenu_page('videogallerys_huge_it_videogallery', 'General Options', 'General Options', 'manage_options', 'Options_videogallery_styles', 'Options_videogallery_styles');
    $lightbox_options = add_submenu_page('videogallerys_huge_it_videogallery', 'Lightbox Options', 'Lightbox Options', 'manage_options', 'Options_videogallery_lightbox_styles', 'Options_videogallery_lightbox_styles');
	add_submenu_page( 'videogallerys_huge_it_videogallery', 'Licensing', 'Licensing', 'manage_options', 'huge_it_video_gallery_Licensing', 'huge_it_video_gallery_Licensing');
	
	add_submenu_page('videogallerys_huge_it_videogallery', 'Featured Plugins', 'Featured Plugins', 'manage_options', 'huge_it__videogallery_featured_plugins', 'huge_it__videogallery_featured_plugins');

	add_action('admin_print_styles-' . $page_cat, 'huge_it_videogallery_admin_script');
    add_action('admin_print_styles-' . $page_option, 'huge_it_videogallery_option_admin_script');
    add_action('admin_print_styles-' . $lightbox_options, 'huge_it_videogallery_option_admin_script');
}

function huge_it__videogallery_featured_plugins()
{
	include_once("admin/huge_it_featured_plugins.php");
}

function huge_it_video_gallery_Licensing(){

	?>
    <div style="width:95%">
    <p>
	This plugin is the non-commercial version of the Huge IT Video Gallery. If you want to customize to the styles and colors of your website,than you need to buy a license.
Purchasing a license will add possibility to customize the general options and lightbox of the Huge IT Video Gallery. 

 </p>
<br /><br />
<a href="http://huge-it.com/video-gallery/" class="button-primary" target="_blank">Purchase a License</a>
<br /><br /><br />
<p>After the purchasing the commercial version follow this steps:</p>
<ol>
	<li>Deactivate Huge IT Video Gallery Plugin</li>
	<li>Delete Huge IT Video Gallery Plugin</li>
	<li>Install the downloaded commercial version of the plugin</li>
</ol>
</div>
<?php
	}

function huge_it_videogallery_admin_script()
{
	wp_enqueue_media();
	wp_enqueue_style("jquery_ui", plugins_url("style/jquery-ui.css", __FILE__), FALSE);
	wp_enqueue_style("admin_css", plugins_url("style/admin.style.css", __FILE__), FALSE);
	wp_enqueue_script("admin_js", plugins_url("js/admin.js", __FILE__), FALSE);
}


function huge_it_videogallery_option_admin_script()
{
	wp_enqueue_media();
	wp_enqueue_script("simple_slider_js",  plugins_url("js/simple-slider.js", __FILE__), FALSE);
	wp_enqueue_style("simple_slider_css", plugins_url("style/simple-slider_sl.css", __FILE__), FALSE);
	wp_enqueue_style("admin_css", plugins_url("style/admin.style.css", __FILE__), FALSE);
	wp_enqueue_script("admin_js", plugins_url("js/admin.js", __FILE__), FALSE);
	wp_enqueue_script('param_block2', plugins_url("elements/jscolor/jscolor.js", __FILE__));
}


function videogallerys_huge_it_videogallery()
{

    require_once("admin/video_gallery_func.php");
    require_once("admin/video_gallery_view.php");
    if (!function_exists('print_html_nav'))
        require_once("videogallery_function/html_videogallery_func.php");


    if (isset($_GET["task"]))
        $task = $_GET["task"]; 
    else
        $task = '';
    if (isset($_GET["id"]))
        $id = $_GET["id"];
    else
        $id = 0;
    global $wpdb;
    switch ($task) {

        case 'add_cat':
            add_videogallery();
            break;

		case 'popup_posts':
            if ($id)
                popup_posts($id);
            else {
                $id = $wpdb->get_var("SELECT MAX( id ) FROM " . $wpdb->prefix . "huge_it_videogallery_galleries");
                popup_posts($id);
            }
            break;
		case 'videogallery_video':
            if ($id)
                videogallery_video($id);
            else {
                $id = $wpdb->get_var("SELECT MAX( id ) FROM " . $wpdb->prefix . "huge_it_videogallery_galleries");
                videogallery_video($id);
            }
            break;
        case 'edit_cat':
            if ($id)
                editvideogallery($id);
            else {
                $id = $wpdb->get_var("SELECT MAX( id ) FROM " . $wpdb->prefix . "huge_it_videogallery_galleries");
                editvideogallery($id);
            }
            break;

        case 'save':
            if ($id)
                apply_cat($id);
        case 'apply':
            if ($id) {
                apply_cat($id);
                editvideogallery($id);
            } 
            break;
        case 'remove_cat':
            removevideogallery($id);
            showvideogallery();
            break;
        default:
            showvideogallery();
            break;
    }


}
do_action('toplevel_page_videogallerys_huge_it_videogallery');

function Options_videogallery_styles()
{
    require_once("admin/video_gallery_Options_func.php");
    require_once("admin/video_gallery_Options_view.php");
    if (isset($_GET['task']))
        if ($_GET['task'] == 'save')
            save_styles_options();
    showStyles();
}
function Options_videogallery_lightbox_styles()
{
    require_once("admin/video_gallery_lightbox_func.php");
    require_once("admin/video_gallery_lightbox_view.php");
    if (isset($_GET['task']))
        if ($_GET['task'] == 'save')
            save_styles_options();
    showStyles();
}



/**
 * Huge IT Widget
 */
class Huge_it_videogallery_Widget extends WP_Widget {


	public function __construct() {
		parent::__construct(
	 		'Huge_it_videogallery_Widget', 
			'Huge IT Video Gallery', 
			array( 'description' => __( 'Huge IT Video Gallery', 'huge_it_videogallery' ), ) 
		);
	}

	
	public function widget( $args, $instance ) {
		extract($args);

		if (isset($instance['videogallery_id'])) {
			$videogallery_id = $instance['videogallery_id'];

			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $before_widget;
			if ( ! empty( $title ) )
				echo $before_title . $title . $after_title;

			echo do_shortcode("[huge_it_videogallery id={$videogallery_id}]");
			echo $after_widget;
		}
	}


	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['videogallery_id'] = strip_tags( $new_instance['videogallery_id'] );
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}


	public function form( $instance ) {
		$selected_videogallery = 0;
		$title = "";
		$videogallerys = false;

		if (isset($instance['videogallery_id'])) {
			$selected_videogallery = $instance['videogallery_id'];
		}

		if (isset($instance['title'])) {
			$title = $instance['title'];
		}

        

        
		?>
		<p>
			
				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
				</p>
				<label for="<?php echo $this->get_field_id('videogallery_id'); ?>"><?php _e('Select videogallery:', 'huge_it_videogallery'); ?></label> 
				<select id="<?php echo $this->get_field_id('videogallery_id'); ?>" name="<?php echo $this->get_field_name('videogallery_id'); ?>">
				
				<?php
				 global $wpdb;
				$query="SELECT * FROM ".$wpdb->prefix."huge_it_videogallery_galleries ";
				$rowwidget=$wpdb->get_results($query);
				foreach($rowwidget as $rowwidgetecho){
				
				
				?>
					<option <?php if($rowwidgetecho->id == $instance['videogallery_id']){ echo 'selected'; } ?> value="<?php echo $rowwidgetecho->id; ?>"><?php echo $rowwidgetecho->name; ?></option>

					<?php } ?>
				</select>

		</p>
		<?php 
	}
}

add_action('widgets_init', 'register_Huge_it_videogallery_Widget');  

function register_Huge_it_videogallery_Widget() {  
    register_widget('Huge_it_videogallery_Widget'); 
}



//////////////////////////////////////////////////////                                             ///////////////////////////////////////////////////////
//////////////////////////////////////////////////////               Activate videogallery                     ///////////////////////////////////////////////////////
//////////////////////////////////////////////////////                                             ///////////////////////////////////////////////////////
//////////////////////////////////////////////////////                                             ///////////////////////////////////////////////////////


function huge_it_videogallery_activate()
{
    global $wpdb;

/// creat database tables

    $sql_huge_it_videogallery_videos = "
CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "huge_it_videogallery_videos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `videogallery_id` varchar(200) DEFAULT NULL,
  `description` text,
  `image_url` text,
  `sl_url` varchar(128) DEFAULT NULL,
  `sl_type` text NOT NULL,
  `link_target` text NOT NULL,
  `ordering` int(11) NOT NULL,
  `published` tinyint(4) unsigned DEFAULT NULL,
  `published_in_sl_width` tinyint(4) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=5";

    $sql_huge_it_videogallery_galleries = "
CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "huge_it_videogallery_galleries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `sl_height` int(11) unsigned DEFAULT NULL,
  `sl_width` int(11) unsigned DEFAULT NULL,
  `pause_on_hover` text,
  `videogallery_list_effects_s` text,
  `description` text,
  `param` text,
  `sl_position` text NOT NULL,
  `ordering` int(11) NOT NULL,
  `published` text,
   `huge_it_sl_effects` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ";


    $table_name = $wpdb->prefix . "huge_it_videogallery_videos";
    $sql_2 = "
INSERT INTO 

`" . $table_name . "` (`id`, `name`, `videogallery_id`, `description`, `image_url`, `sl_url`, `sl_type`, `link_target`, `ordering`, `published`, `published_in_sl_width`) VALUES
(1, 'People Are Awesome', '1', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p><p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 'https://www.youtube.com/embed/yNHyTk2jYNA', 'http://huge-it.com', 'video', 'on', 0, 1, NULL),
(2, 'Africa Race', '1', '<ul><li>lorem ipsumdolor sit amet</li><li>lorem ipsum dolor sit amet</li></ul><p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 'http://player.vimeo.com/video/62604342', 'http://huge-it.com/fields/order-website-maintenance/', 'video', 'on', 1, 1, NULL),
(3, 'London City In Motion', '1', '<h6>Lorem Ipsum </h6><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p><p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><ul><li>lorem ipsum</li><li>dolor sit amet</li><li>lorem ipsum</li><li>dolor sit amet</li></ul>', 'http://player.vimeo.com/video/99310168', 'http://huge-it.com/fields/order-website-maintenance/', 'video', 'on', 2, 1, NULL),
(4, 'Dubai City As You have Never Seen It Before', '1', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p><h6>Dolor sit amet</h6><p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 'https://www.youtube.com/embed/t5vta25jHQI', 'http://huge-it.com/fields/order-website-maintenance/', 'video', 'on', 3, 1, NULL),
(5, 'Never say no to a Panda !', '1', '<h6>Lorem Ipsum</h6><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 'http://player.vimeo.com/video/15371143', 'http://huge-it.com/', 'video', 'on', 4, 1, NULL),
(6, 'INDO-FLU', '1', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p>', 'http://player.vimeo.com/video/103151169', 'http://huge-it.com/fields/order-website-maintenance/', 'video', 'on', 5, 1, NULL),
(7, 'People Are Awesome Womens Edition', '1', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p><h6>Lorem Ipsum</h6><p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 'https://www.youtube.com/embed/R5avCAn1vs0', 'http://huge-it.com/fields/order-website-maintenance/', 'video', 'on', 6, 1, NULL),
(8, 'Norwey', '1', '<h6>Lorem Ipsum </h6><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p><p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><ul><li>lorem ipsum</li><li>dolor sit amet</li><li>lorem ipsum</li><li>dolor sit amet</li></ul>', 'http://player.vimeo.com/video/31022539', 'http://huge-it.com/fields/order-website-maintenance/', 'video', 'on', 7, 1, NULL),
(9, 'Slow Motion', '1', '<h6>Lorem Ipsum </h6><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p><p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><ul><li>lorem ipsum</li><li>dolor sit amet</li><li>lorem ipsum</li><li>dolor sit amet</li></ul>', 'https://www.youtube.com/embed/gb69WX82Hvs', 'http://huge-it.com/', 'video', 'on', 7, 1, NULL)";


    $table_name = $wpdb->prefix . "huge_it_videogallery_galleries";


    $sql_3 = "

INSERT INTO `$table_name` (`id`, `name`, `sl_height`, `sl_width`, `pause_on_hover`, `videogallery_list_effects_s`, `description`, `param`, `sl_position`, `ordering`, `published`, `huge_it_sl_effects`) VALUES
(1, 'My First Video Gallery', 375, 600, 'on', 'random', '4000', '1000', 'center', 1, '300', '5')";

    $wpdb->query($sql_huge_it_videogallery_videos);
    $wpdb->query($sql_huge_it_videogallery_galleries);

    if (!$wpdb->get_var("select count(*) from " . $wpdb->prefix . "huge_it_videogallery_videos")) {
      $wpdb->query($sql_2);
    }
    if (!$wpdb->get_var("select count(*) from " . $wpdb->prefix . "huge_it_videogallery_galleries")) {
      $wpdb->query($sql_3);
    }

    ////////////////////////////////////
     $imagesAllFieldsInArray = $wpdb->get_results("DESCRIBE " . $wpdb->prefix . "huge_it_videogallery_videos", ARRAY_A);
        $forUpdate = 0;
        foreach ($imagesAllFieldsInArray as $portfoliosField) {
        if ($portfoliosField['Field'] == 'thumb_url') {
            $forUpdate = 1;
        }
    }
        if($forUpdate != 1){
            $wpdb->query("ALTER TABLE ".$wpdb->prefix."huge_it_videogallery_videos ADD thumb_url text DEFAULT NULL");
        }


     ////////////////////////////////////////
  $imagesAllFieldsInArray2 = $wpdb->get_results("DESCRIBE " . $wpdb->prefix . "huge_it_videogallery_galleries", ARRAY_A);
        $fornewUpdate = 0;
        foreach ($imagesAllFieldsInArray2 as $portfoliosField2) {
            if ($portfoliosField2['Field'] == 'display_type') {
                $fornewUpdate = 1;
            }
        }
        if($fornewUpdate != 1){
            $wpdb->query("ALTER TABLE ".$wpdb->prefix."huge_it_videogallery_galleries ADD display_type integer DEFAULT '2' ");
            $wpdb->query("ALTER TABLE ".$wpdb->prefix."huge_it_videogallery_galleries ADD content_per_page integer DEFAULT '5' ");
        }   

}

register_activation_hook(__FILE__, 'huge_it_videogallery_activate');