<?php

/* 
 * WhiteWebWorx
 * MQ
 */
class tyto_blocksMetaBox{

	private $screen = array(
                'tyto-blocks',
                        
	);

	private $meta_fields = array(
                array(
                    'label' => 'Name',
                    'id' => 'name',
                    'default' => '',
                    'type' => 'text',
                ),
                array(
                    'label' => 'Preview Image Help',
                    'id' => 'preview_image_help',
                    'default' => '',
                    'type' => 'media',
                    'returnvalue' => 'url'
                ),
                array(
                    'label' => 'Description',
                    'id' => 'description',
                    'default' => '',
                    'type' => 'text',
                ),
                array(
                    'label' => 'Category',
                    'id' => 'category',
                    'default' => '',
                    'type' => 'text',
                ),
                array(
                    'label' => 'Icon <br> <small>(<a href="https:'
                    . '//developer.wordpress.org/resource/dashicons/" target="_blank">info</a>)</small>',
                    'id' => 'icon',
                    'default' => 'admin-generic',
                    'type' => 'text',
                ),
                array(
                    'label' => 'Keywords (separated with ,)',
                    'id' => 'keywords',
                    'default' => '',
                    'type' => 'text',
                )

	);

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
                add_action( 'admin_footer', array( $this, 'media_fields' ) );
		add_action( 'save_post', array( $this, 'save_fields' ) );
		add_action( 'edit_form_after_title', array( $this, 'show_cp_alert' ) );
                
                add_action('acf/init', array( $this, 'acf_init_block_types'));
                
	}

	public function add_meta_boxes() {
		foreach ( $this->screen as $single_screen ) {
			add_meta_box(
				'Register Block Type',
				__( 'Register Block Type', '' ),
				array( $this, 'meta_box_callback' ),
				$single_screen,
				'normal',
				'default'
			);
		}
	}

	public function meta_box_callback( $post ) {
		wp_nonce_field( 'Register-Block-Type_data', 'Register-Block-Type_nonce' );
		$this->field_generator( $post );
	}
        
        
	public function field_generator( $post ) {
		$output = '';
		foreach ( $this->meta_fields as $meta_field ) {
			$label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
			$meta_value = get_post_meta( $post->ID, $meta_field['id'], true );
                       // echo "<pre>";
                       // print_r($post);
                       // print_r($meta_field);
                        $dis = '';
                        if ($post->post_status== 'publish'  && $meta_field['id']=='name'){
                           $dis = 'disabled';  
                        }
                        
                       
                        
			if ( empty( $meta_value ) ) {
				if ( isset( $meta_field['default'] ) ) {
					$meta_value = $meta_field['default'];
				}
			}
			switch ( $meta_field['type'] ) {
                                case 'media':
                                    $meta_url = '';
                                        if ($meta_value) {
                                            if ($meta_field['returnvalue'] == 'url') {
                                                $meta_url = $meta_value;
                                            } else {
                                                $meta_url = wp_get_attachment_url($meta_value);
                                            }
                                        }
                                    $input = sprintf(
                                        '<input style="display:none;" id="%s" name="%s" type="text" value="%s"  data-return="%s"><div id="preview%s" style="margin-right:10px;border:1px solid #e2e4e7;background-color:#fafafa;display:inline-block;width: 100px;height:100px;background-image:url(%s);background-size:cover;background-repeat:no-repeat;background-position:center;"></div><input style="width: 19%%;margin-right:5px;" class="button new-media" id="%s_button" name="%s_button" type="button" value="Select" /><input style="width: 19%%;" class="button remove-media" id="%s_buttonremove" name="%s_buttonremove" type="button" value="Clear" />',
                                        $meta_field['id'],
                                        $meta_field['id'],
                                        $meta_value,
                                        $meta_field['returnvalue'],
                                        $meta_field['id'],
                                        $meta_url,
                                        $meta_field['id'],
                                        $meta_field['id'],
                                        $meta_field['id'],
                                        $meta_field['id']
                                    );
                                    break;


				default:
                                    $input = sprintf(
                                        '<input %s id="%s" name="%s" type="%s" value="%s" %s>',
                                        $meta_field['type'] !== 'color' ? 'style="width: 100%"' : '',
                                        $meta_field['id'],
                                        $meta_field['id'],
                                        $meta_field['type'],
                                        $meta_value,
                                        $dis
                                    );
			}
			$output .= $this->format_rows( $label, $input );
		}
		echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
	}

	public function format_rows( $label, $input ) {
		return '<tr><th>'.$label.'</th><td>'.$input.'</td></tr>';
	}

	public function save_fields( $post_id ) {
		if ( ! isset( $_POST['Register-Block-Type_nonce'] ) )
			return $post_id;
		$nonce = $_POST['Register-Block-Type_nonce'];
		if ( !wp_verify_nonce( $nonce, 'Register-Block-Type_data' ) )
			return $post_id;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		foreach ( $this->meta_fields as $meta_field ) {
			if ( isset( $_POST[ $meta_field['id'] ] ) ) {
				switch ( $meta_field['type'] ) {
					case 'email':
						$_POST[ $meta_field['id'] ] = sanitize_email( $_POST[ $meta_field['id'] ] );
						break;
					case 'text':
						$_POST[ $meta_field['id'] ] = sanitize_text_field( $_POST[ $meta_field['id'] ] );
						break;
				}
				update_post_meta( $post_id, $meta_field['id'], $_POST[ $meta_field['id'] ] );
			} else if ( $meta_field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, $meta_field['id'], '0' );
			}
                }
	}
        
        public function media_fields() {
            ?><script>
                jQuery(document).ready(function($){
                    if ( typeof wp.media !== 'undefined' ) {
                        var _custom_media = true,
                        _orig_send_attachment = wp.media.editor.send.attachment;
                        $('.new-media').click(function(e) {
                            var send_attachment_bkp = wp.media.editor.send.attachment;
                            var button = $(this);
                            var id = button.attr('id').replace('_button', '');
                            _custom_media = true;
                                wp.media.editor.send.attachment = function(props, attachment){
                                if ( _custom_media ) {
                                    if ($('input#' + id).data('return') == 'url') {
                                        $('input#' + id).val(attachment.url);
                                    } else {
                                        $('input#' + id).val(attachment.id);
                                    }
                                    $('div#preview'+id).css('background-image', 'url('+attachment.url+')');
                                } else {
                                    return _orig_send_attachment.apply( this, [props, attachment] );
                                };
                            }
                            wp.media.editor.open(button);
                            return false;
                        });
                        $('.add_media').on('click', function(){
                            _custom_media = false;
                        });
                        $('.remove-media').on('click', function(){
                            var parent = $(this).parents('td');
                            parent.find('input[type="text"]').val('');
                            parent.find('div').css('background-image', 'url()');
                        });
                    }
                });
            </script><?php
        }
        
        
        
        public function show_cp_alert(){
            
            global $post;
            // confirm if the post_type is 'post'
            if ($post->post_type!== 'tyto-blocks')
              return;
            // you can also perform a condition for post_status, for example, if you don't want to display the message if post is already published:
            if ($post->post_status!== 'publish' && $post->filter!== 'edit')
              return;
            
            $themePath = get_template_directory();
            $name = get_post_meta( $post->ID, 'name', true );
            $blockSettings = $this->block_settings($name);
            
            $render = $blockSettings['render'];
            $css = $blockSettings['css'];
            $js = $blockSettings['js'];
            $acf = site_url()."/wp-admin/post.php?post={$blockSettings['acf']}&action=edit";
            
           // print_r($blockSettings);
            
             echo '<div class="notice notice-info">
                    <b>Block Template Settings:</b>
                    <p>Block Template Render Path: <code>'.$render.'</code> </p>
                    <p>Block Template CSS: <code>'.$css.'</code> </p>
                    <p>Block Template JS: <code>'.$js.'</code> </p>
                    <p>ACF Field Group: <code><a href="'.$acf.'">Click here</a></code> </p>
                </div>';
             
             // check and create files 
            
             
            if(!file_exists($themePath.$render)){
                $r = fopen($themePath.$render, "w+");
                fwrite($r, $this->basic_render());
                fclose($r);
            }
             
            if(!file_exists($themePath.$css)){
                $c = fopen($themePath.$css, "w+");
                fclose($c);
            }
             
            if(!file_exists($themePath.$js)){
                $j = fopen($themePath.$js, "w+");
                fclose($j);
            }
            
            $this->acf_init_block_group($post->ID);
            //add_action('acf/init', array( $this, 'acf_init_block_group'));

        }
        
        public function get_blocks($filter=[],$postID=0){
           
            $return = [];
            $args = array(
                'post_type' => 'tyto-blocks',
                'posts_per_page' => -1
            );
            
            if(!empty($filter))
            $args = array_merge($args,$filter);
            
           
            $query = new WP_Query( $args );
            if($query->have_posts()):
                while($query->have_posts()) : $query->the_post();
                    $ID = get_the_ID();
                    $return[$ID] = [
                    'title'=> get_the_title(),    
                    'status'=> get_post_status(),    
                    ];
                    
                    foreach ( $this->meta_fields as $meta_field ) {
                    $meta_value = get_post_meta( $ID, $meta_field['id'], true );
                    $return[$ID][$meta_field['id']] = $meta_value;
                    }
                    
                endwhile;
            endif;
            wp_reset_query();
            return $return;
            
        }
        
        public function acf_init_block_types(){
            
            if( function_exists('acf_register_block_type') ){
                $blocks = $this->get_blocks(['post_status' => 'publish']);
                
                if(!empty($blocks)){
                    foreach($blocks as $blockID=>$block){
                        
                        $blockSettings = $this->block_settings($block['name']);
                        $preview_image_help = get_post_meta( $blockID, 'preview_image_help', true );
                       // pre($block);
                       $st =  acf_register_block_type(array(
                            'name'              => $blockSettings['key'],
                            'title'             => __($block['title']),
                            'description'       => __($block['description']),
                            'render_template'   => $blockSettings['render'],
                            'category'          => $block['category'],
                            'icon'              => $block['icon'] ?? 'admin-generic',
                            'keywords'          => explode(',',$block['keywords']),
                            'example'  => array(
                                'attributes' => array(
                                    'mode' => 'preview',
                                    'data' => array(
                                            'preview_image_help' => $preview_image_help,
                                    )
                                )
                            )
                            
                        ));
                        // pre($st);
                        

                    }
                }
            }
        }
        public function acf_init_block_group($postID){

            if( function_exists('acf_add_local_field_group') ){
                $blocks = $this->get_blocks(['p'=>$postID]);
                
                if(!empty($blocks)){
         
                    foreach($blocks as $blockID=>$block){
                        
                        $blockSettings = $this->block_settings($block['name']);
                        $group_id = $this->get_acf_group_id($blockSettings['key']);
                        if(!$group_id){
                            global $wpdb;
                            $table = $wpdb->prefix.'posts';
                            $content = [
                                'location'=>[
                                    [
                                        [
                                        'param' => 'block',
                                        'operator' => '==',
                                        'value' => 'acf/'.$blockSettings['key'],
                                        ]
                                    ]
                                ],
                                
                            'position' => 'normal',
                            'style' => 'default',
                            'label_placement' => 'top',
                            'instruction_placement' => 'label',
                            'hide_on_screen' => '',
                            'description' => '',
                            'show_in_rest' => 0
                                
                            ];
                            $data = array(
                                'post_author' => 1,
                                'post_title' => $block['name'],
                                'post_content' => maybe_serialize($content),
                                'post_excerpt' => $blockSettings['key'],
                                'post_status' => 'publish',
                                'comment_status' => 'closed',
                                'ping_status' => 'closed',
                                'post_name' => 'group_'.uniqid(),
                                'post_parent' => 0,
                                'post_type' => 'acf-field-group',
                                );
                            
                            $format = array('%d','%s','%s','%s','%s','%s','%s','%s','%d','%s');
                            $wpdb->insert($table,$data,$format);
                            $my_id = $wpdb->insert_id;
                            
 
                            update_post_meta( $blockID, 'acf_group', $my_id );
                        
                            
                        global $wpdb;    
                        $uniqs = [];
                        $fields = [];
                        $the_post_content = [];
                        $basic_buttons = json_decode($this->basic_buttons(),true);
                        if(!empty($basic_buttons) && is_array($basic_buttons)){
                            
                            $basic_button = $basic_buttons[0]['fields'][0];
                              //  pre($basic_button);
                                
                                $uniqs[$basic_button['key']] = 'field_'.uniqid();
                                
                                $the_post_content = [
                                    'type'=>$basic_button['type'],
                                    'instructions'=>$basic_button['instructions'],
                                    'required'=>$basic_button['required'],
                                    'conditional_logic'=>$basic_button['conditional_logic'],
                                    'wrapper'=>$basic_button['wrapper'],
                                    'collapsed'=>$basic_button['collapsed'],
                                    'min'=>$basic_button['min'],
                                    'max'=>$basic_button['max'],
                                    'layout'=>$basic_button['layout'],
                                    'button_label'=>$basic_button['button_label'],
                                ];
                                $fields_primary = [
                                    'post_author'=>1,
                                    'post_title'=>$basic_button['label'],
                                    'post_excerpt'=>$basic_button['name'],
                                    'post_status'=>'publish',
                                    'comment_status'=>'closed',
                                    'ping_status'=>'closed',
                                    'post_name'=>'field_'.uniqid(), // uniq
                                    'post_parent'=>$my_id,
                                    'post_type'=>'acf-field',
                                    'post_content'=>maybe_serialize($the_post_content)
                                ];
                               $table = $wpdb->prefix.'posts'; 
                               $ins = $wpdb->insert($table,$fields_primary);
                               $fid = $wpdb->insert_id;
                              // pre($wpdb->last_error);
                              // pre($wpdb->last_query);
                              // pre($ins);
                              // pre($fields_primary);
                               $sub_buttons = $basic_button['sub_fields']; 
                               
                               
                               foreach($sub_buttons as $sub_button){
                                   
                                   $uniqs[$sub_button['key']] = 'field_'.uniqid();
                                   
                                   $the_post_content = [
                                    'type'=>$sub_button['type'],
                                    'instructions'=>$sub_button['instructions'],
                                    'required'=>$sub_button['required'],
                                    'conditional_logic'=>$sub_button['conditional_logic'],
                                    'wrapper'=>$sub_button['wrapper'],
                                    'choices'=>$sub_button['choices'],
                                    'default_value'=>$sub_button['default_value'],
                                    'allow_null'=>$sub_button['allow_null'],
                                    'multiple'=>$sub_button['multiple'],
                                    'ui'=>$sub_button['ui'],
                                    'ajax'=>$sub_button['ajax'],
                                    'return_format'=>$sub_button['return_format'],
                                    'placeholder'=>$sub_button['placeholder'],

                                ];
                                   
                                 if(isset($the_post_content['conditional_logic']) && !empty($the_post_content['conditional_logic'])){
                                     $the_post_content['conditional_logic'][0][0]['field'] = $uniqs[$the_post_content['conditional_logic'][0][0]['field']];
                           
                                 }  
                                   
                                $fields_sub = [
                                    'post_author'=>1,
                                    'post_title'=>$sub_button['label'],
                                    'post_excerpt'=>$sub_button['name'],
                                    'post_status'=>'publish',
                                    'comment_status'=>'closed',
                                    'ping_status'=>'closed',
                                    'post_name'=>$uniqs[$sub_button['key']], // uniq
                                    'post_parent'=>$fid,
                                    'post_type'=>'acf-field',
                                    'post_content'=>maybe_serialize($the_post_content)
                                ];
                                //pre($fields_sub);
                                $wpdb->insert($table,$fields_sub);
                                
                               }
                            
                            
                            //pre($sub_buttons);
                            //pre($fields);
                        }
                            
                            
                            /*
                               global $wpdb;
                               $basic_buttons = json_decode($this->basic_buttons());
                               print_r($basic_buttons);
                               
                        $basic_buttons = array_filter(explode("\n",$this->basic_buttons()));
                            //pre($basic_buttons);
                        
                 
                         $uniq1='field_'.uniqid(); 
                         $uniq2='field_'.uniqid(); 
                         $uniq3='field_'.uniqid(); 
                         $uniq4='field_'.uniqid(); 
                         $uniq5='field_'.uniqid(); 
                         $uniq6='field_'.uniqid(); 
                         $uniq7='field_'.uniqid(); 
                         $uniq8='field_'.uniqid(); 
                         $uniq9='field_'.uniqid(); 
                         $uniq10='field_'.uniqid(); 
                         $uniq11='field_'.uniqid(); 
                         $uniq12='field_'.uniqid(); 
                         $uniq13='field_'.uniqid(); 
                         $uniq14='field_'.uniqid(); 
           
                     
                        $fid = 0;    
                        if(!empty($basic_buttons)){
                            foreach($basic_buttons as $k=>$basic_button){
                                $uniq = 'uniq'.($k+1);
                                if($k==0){
                                  $in = str_replace(['{GID}','{uniq1}'],[$my_id,$$uniq],$basic_button);  
                                  $wpdb->query($in);
                                  $fid = $wpdb->insert_id;

                                 // pre($fid);
                                }else{
                                    $in = str_replace(['{FID}','{uniq1}'],[$fid,$my_id,$$uniq],$basic_button);  
                                    $wpdb->query($in);
                                }
                                
                               // pre($in);
                            }
                        }
                            */
                            
                        }
                        
                         
                       
                               
                            
                            
                        
                        
                        /*
                        acf_add_local_field_group(array(
                                'key' => $blockSettings['key'],
                                'title' => $block['name'],
                                'fields' => array (),
                                'location' => array (
                                        array (
                                                array (
                                                        'param' => 'block',
                                                        'operator' => '==',
                                                        'value' => $blockSettings['key'],
                                                ),
                                        ),
                                ),
                        ));
                         * 
                         */
                        

                    }
                }
            }
        }
        
        
        public function block_settings($name){
            $name = sanitize_title($name);
            //$themePath = get_template_directory();
            $themePath = '';
            $acfID = $this->get_acf_group_id($name);
            return [
            'key'=> $name,
            'render'=> "$themePath/template-parts/blocks/{$name}.php",
            'css'=> "$themePath/_dev/css/{$name}.css",
            'js'=> "$themePath/_dev/js/{$name}.js",
            'acf'=> $acfID,
            ];
            
        }
        
        
        public function get_acf_group_id($group_name){
            global $wpdb;

            return $wpdb->get_var("
                SELECT ID
                FROM $wpdb->posts
                WHERE post_type='acf-field-group' AND post_excerpt='$group_name';
            ");
        }
        
        
        public function basic_render(){
            return file_get_contents(plugin_dir_path( __FILE__ )."basic_render.txt");
        }
        
        public function basic_buttons(){
            return file_get_contents(plugin_dir_path( __FILE__ )."Buttons.json");
        }
        
}

if (class_exists('tyto_blocksMetaBox')) {
	 new tyto_blocksMetaBox;

};
