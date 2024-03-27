<?php
 require 'Minifier.php';
require_once "scssphp-master/scss.inc.php";
use ScssPhp\ScssPhp\Compiler;

add_action('wp_enqueue_scripts', 'tyto_blocks_add_script_wp_head');
function tyto_blocks_add_script_wp_head(){
    $return = [];
    $themPath = get_template_directory();
    $css_files = scandir($themPath.'/_dev/css');
    $js_files = scandir($themPath.'/_dev/js');

    if( wp_get_environment_type() === 'development' && !isset($_GET['mini'])) {
        wp_enqueue_script('popper-js', get_template_directory_uri() . '/_dev/bootstrap/js/popper.min.js', [], time());
        wp_enqueue_script('bootstrap-js', get_template_directory_uri() . '/_dev/bootstrap/js/bootstrap.min.js', [], time());
        wp_enqueue_style('css-bootstrap', get_template_directory_uri()."/_dev/bootstrap/css/bootstrap.min.css" ,[]);
     
        if(!empty($css_files)){
            foreach($css_files as $css_file){
                $pathinfo = pathinfo($css_file); 
                if($pathinfo['extension']=='css'){
                    $css = get_template_directory_uri()."/_dev/css/{$css_file}";
                    $key = sanitize_title($pathinfo['filename']);
                    wp_enqueue_style('css-'.$key, $css ,[],time());
                } 
            }
        }

        if(!empty($js_files)){
            foreach($js_files as $js_file){
                $pathinfo = pathinfo($js_file); 
                if($pathinfo['extension']=='js'){
                    $js = get_template_directory_uri()."/_dev/js/{$js_file}";
                    $key = sanitize_title($pathinfo['filename']);
                    wp_enqueue_script('js-'.$key, $js, array( 'jquery' ), null, time());
                } 
            }
        }
    }else{
        $files_hash = 'global';
        if( MINI_HASH && !empty(MINI_HASH) && MINI_HASH != '%MINI_HASH_GOES_HERE%'):
            $files_hash = 'global.'.MINI_HASH;
        endif;

        $git_hash = ABSPATH.'/.git/ORIG_HEAD';

        $global_css_file_name = '/_prod/mini/'.$files_hash.'.min.css';
        $global_js_file_name = '/_prod/mini/'.$files_hash.'.min.js';
        $global_css_file = $themPath.$global_css_file_name;
        $global_js_file = $themPath.$global_js_file_name;
        
        if (!file_exists($themPath.'/_prod')) {
            mkdir($themPath.'/_prod', 0775, true);
        }
        
        if (!file_exists($themPath.'/_prod/mini')) {
            mkdir($themPath.'/_prod/mini', 0775, true);
        }
         
        
        $exception = ['globals','owl.carousel.min'];
        
        if(!file_exists($global_css_file) || isset($_GET['mini'])){
            if(!empty($css_files)){
                $css_content = '';
                
                $bootstrap = '/_dev/bootstrap/css/bootstrap.min.css';
                $carousel = '/_dev/css/owl.carousel.min.css';
                $globals = '/_dev/css/globals.css';
                $css_content .= "\n/* {$bootstrap} */\n";        
                $css_content .= minimizeCSSsimple(file_get_contents($themPath.$bootstrap));  
                $css_content .= "\n/* {$carousel} */\n";        
                $css_content .= minimizeCSSsimple(file_get_contents($themPath.$carousel));  
                $css_content .= "\n/* {$globals} */\n";        
                $css_content .= minimizeCSSsimple(file_get_contents($themPath.$globals));
                
                $compiler = new Compiler();
                
                foreach($css_files as $css_file){
                    $pathinfo = pathinfo($css_file); 
                    $full_file_path = $themPath.'/_dev/css/'.$css_file;

                    if($pathinfo['extension']=='scss'){
                        //if(filesize($full_file_path)>1){
                        //    $fcontent = file_get_contents($full_file_path);  
                        //    $css_content .= "\n/* /_dev/css/{$css_file} */\n";
                        //    $compileString = $compiler->compileString($fcontent)->getCss();
                        //    $css_content .= minimizeCSSsimple($compileString)."\n";
                        //}
                        
                        
                    }elseif($pathinfo['extension']=='css' && !in_array($pathinfo['filename'], $exception)){
                        if(filesize($full_file_path)>1){
                            $fcontent = file_get_contents($full_file_path);    
                            $css_content .= "\n/* /_dev/css/{$css_file} */\n";   
                            $css_content .= minimizeCSSsimple($fcontent)."\n";
                        }
                    }
                }

                if(!empty($css_content)){
                    $myCSSfile = fopen($global_css_file, "w");
                    fwrite($myCSSfile, $css_content);
                    fclose($myCSSfile);
                }

                if( empty($css_content) ){
                    do_action('tyto_log', 'css_content is empty');
                    exit('css_content is empty');
                }
                
            }
        }
        
        if(!file_exists($global_js_file) || isset($_GET['mini'])){
            if(!empty($js_files)){
                $js_content = '';
                 
                $popper = '/_dev/bootstrap/js/popper.min.js';
                $bootstrap = '/_dev/bootstrap/js/bootstrap.min.js';
                $carousel = '/_dev/js/owl.carousel.min.js';
                $globals = '/_dev/js/globals.js';
                
                $js_content .= "\n/* {$popper} */\n";        
                $js_content .= \JShrink\Minifier::minify(file_get_contents($themPath.$popper));
                $js_content .= "\n/* {$bootstrap} */\n";        
                $js_content .= \JShrink\Minifier::minify(file_get_contents($themPath.$bootstrap));
                $js_content .= "\n/* {$carousel} */\n";        
                $js_content .= \JShrink\Minifier::minify(file_get_contents($themPath.$carousel));
                $js_content .= "\n/* {$globals} */\n";        
                $js_content .= \JShrink\Minifier::minify(file_get_contents($themPath.$globals));
                foreach($js_files as $js_file){
                   
                    $pathinfo = pathinfo($js_file); 
                    if($pathinfo['extension']=='js' && !in_array($pathinfo['filename'], $exception)){
                        $full_file_path = $themPath.'/_dev/js/'.$js_file;
                        if(filesize($full_file_path)>1){
                        $fcontent = file_get_contents($full_file_path);        
                        $js_content .= "\n/* /_dev/js/{$js_file} */\n";   
                        $js_content .= \JShrink\Minifier::minify($fcontent);
                        }
                    
                    }
                }

                if(!empty($js_content)){
                    $myJSfile = fopen($global_js_file, "w");
                    fwrite($myJSfile, $js_content);
                    fclose($myJSfile);
                }

                if( empty($js_content) ){
                    do_action('tyto_log', 'js_content is empty');
                    exit('js_content is empty');
                }
                
            }
        }
        
        if( !$global_css_file_name && empty($global_css_file_name) ){
            do_action('tyto_log', 'global css is empty');
            do_action('tyto_log', 'MINI_HASH - '.MINI_HASH);
            exit('global css file is empty');
        }

        if( !$global_js_file_name && empty($global_js_file_name) ){
            do_action('tyto_log', 'global js is empty');
            do_action('tyto_log', 'MINI_HASH - '.MINI_HASH);
            exit('global js is empty');
        }
    
        //wp_enqueue_style('css-style-mini', get_template_directory_uri().$global_css_file_name ,[]);
        //wp_enqueue_script('js-js-mini', get_template_directory_uri().$global_js_file_name, array( 'jquery' ), null); 
        wp_enqueue_script('js-js-mini', get_template_directory_uri().$global_js_file_name, array( 'jquery' ), null, true);
}
   
   
   if(isset($_GET['mini'])){
     echo   '<script>  alert( "Assets Files RE-Generated Successfully" );</script>';
    }
}

function minimizeCSSsimple($css){
    $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
    $css = preg_replace('/\s{2,}/', ' ', $css);
    $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return $css;
}


function minimizeJavascriptSimple($javascript){
return preg_replace(array("/\s+\n/", "/\n\s+/", "/ +/"), array("\n", "\n ", " "), $javascript);
}




function custom_toolbar_link($wp_admin_bar) {
    $args = array(
        'id' => 'regenerateassets',
        'title' => 'Regenerate Assets Files', 
        'href' => site_url('?mini=true'), 
        'meta' => array(
            'class' => 'regenerateassets', 
            'title' => 'Regenerate Assets Files'
            )
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'custom_toolbar_link', 999);

function acf_feilds_by_name (){
    $json = file_get_contents(plugin_dir_path( __FILE__ ).'/Buttons.json');
    $fields_from_file  = json_decode($json,true);
    $fields = [];
     
    if(isset($fields_from_file['sub_fields']) && !empty($fields_from_file['sub_fields'])){
        foreach($fields_from_file['sub_fields'] as $item){
            $fields[$item['name']] = $item;
        }
    }
    
    return $fields;
}

if(isset($_GET['acf_feilds_by_name']))
    acf_feilds_by_name();

function acf_buttons_field( $field ) {
    $the_fields = acf_feilds_by_name();
    $json = file_get_contents(plugin_dir_path( __FILE__ ).'/Buttons.json');
    $fields_from_file  = json_decode($json,true);

		$fieldTMP = $field;
		
        if(isset($field['layout'])){
            $field['layout'] = "row";
        }
            
        $field['sub_fields'] = $fields_from_file['sub_fields'];

        // check if post popups field existed 
        $keys = ['field_6328390e0f2a1','field_630369c4c377b','field_630369c4c377b0101'];
        foreach($keys as $key){
            $the_post = get_posts(['name'=>$key,'post_type'=>'acf-field']);
            if(empty($the_post)){
                wp_insert_post(
                        [
                            'post_content'=>'a:11:{s:4:"type";s:11:"post_object";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:9:"post_type";a:1:{i:0;s:6:"popups";}s:8:"taxonomy";s:0:"";s:10:"allow_null";i:0;s:8:"multiple";i:0;s:13:"return_format";s:6:"object";s:2:"ui";i:1;}',
                            'post_name'=>$key,
                            'post_type'=>'acf-field',
                            'post_status'=>'publish',
                            'post_author'=>1,
                            'post_title'=>'pop_ups',
                            'post_excerpt'=>'pop_ups',
                            ]
                        );
            }
        }
    
        foreach($field['sub_fields'] as $k=>$item){
            if($item['name']=='button_visible' || $item['name']=='button_analytics' || $item['name']=='analytics'){
                unset($field['sub_fields'][$k]);
            }
        }
 
    return $field;
}


add_filter('acf/load_field/name=button', 'acf_buttons_field'); 
add_filter('acf/load_field/name=buttons', 'acf_buttons_field'); 
add_filter('acf/load_field/name=header_buttons', 'acf_buttons_field'); 
add_filter('acf/load_field/name=request_a_demo_link', 'acf_buttons_field'); 
add_filter('acf/load_field/name=talk_to_us_link', 'acf_buttons_field'); 
add_filter('acf/load_field/name=contact_button_footer', 'acf_buttons_field'); 



function acf_get_buttons_fields ($array){
    
    $return = [];
    
    
    if(is_array($array)){
        foreach($array as $k=>$items){
            if(is_array($items)){
                foreach($items as $k2=>$item){
                    if(isset($item['name']) && $item['name']=='button'){
                        //echo "Button ".print_r($item,true)." \n";
                        $item = acf_buttons_field( $item );
                        //echo "Button ".print_r($array['fields'][$k],true)." \n";
                    }elseif(isset($item['sub_fields'])){
                        $item['sub_fields'] = acf_get_buttons_fields($item['sub_fields']);
                    }
                }
                
                
            }
        }
    }
    
    return $array;
}