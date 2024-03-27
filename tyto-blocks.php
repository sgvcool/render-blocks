<?php
/*
Plugin Name:  Tyto Blocks
Plugin URI:   https://www.whiteweb.co.il/
Description:  Create and manage blocks
Version:      1.0
Author:       WhiteWebWorx
Author URI:   https://www.whiteweb.co.il/
Text Domain:  tyto-blocks
Domain Path:       /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TYTO_BLOCKS_VERSION', '1.0.0' );


require_once plugin_dir_path( __FILE__ ) . '/tyto-blocks-functions.php';
require_once plugin_dir_path( __FILE__ ) . '/tyto-blocks-render.php';

function setup_tyto_blocks() {
    $labels = array(
		'name'                  => _x( 'Tyto Blocks', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Tyto Blocks', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Tyto Blocks', 'text_domain' ),
		'name_admin_bar'        => __( 'Tyto blocks', 'text_domain' ),
		'archives'              => __( 'Block Archives', 'text_domain' ),
		'attributes'            => __( 'Block Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Block:', 'text_domain' ),
		'all_items'             => __( 'All Blocks', 'text_domain' ),
		'add_new_item'          => __( 'Add New Block', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Block', 'text_domain' ),
		'edit_item'             => __( 'Edit Block', 'text_domain' ),
		'update_item'           => __( 'Update Block', 'text_domain' ),
		'view_item'             => __( 'View Block', 'text_domain' ),
		'view_items'            => __( 'View Blocks', 'text_domain' ),
		'search_items'          => __( 'Search Block', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Blocks list', 'text_domain' ),
		'items_list_navigation' => __( 'Blocks list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Tyto blocks', 'text_domain' ),
		'description'           => __( 'tyto-blocks', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'custom-fields'),
		'hierarchical'          => true,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-editor-kitchensink',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'rewrite'               => false,
		'capability_type'       => 'post',
	);
	return register_post_type( 'tyto-blocks', $args );
} 
add_action( 'init', 'setup_tyto_blocks' );
setup_tyto_blocks(); 


function disable_autosave() {
wp_deregister_script('autosave');
}
add_action('wp_print_scripts','disable_autosave');

/**
 * Activate the plugin.
 */
function activate_tyto_blocks() { 
    // Trigger our function that registers the custom post type plugin.
    setup_tyto_blocks(); 
    // Clear the permalinks after the post type has been registered.
    flush_rewrite_rules(); 
    
   
}
register_activation_hook( __FILE__, 'activate_tyto_blocks' );



/**
 * Deactivation hook.
 */
function deactivate_tyto_blocks() {
    // Unregister the post type, so the rules are no longer in memory.
    unregister_post_type( 'book' );
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'deactivate_tyto_blocks' );





add_filter('manage_posts_columns', 'admin_thumbnail_column_tyto_blocks', 2);


function admin_thumbnail_column_tyto_blocks($columns)
{
 
    // Check if post type is 'Product'
    global $pagenow, $typenow;
    //echo $pagenow;
    if( 'tyto-blocks' === $typenow && 'edit.php' === $pagenow )
    {
         
 
        $new = array();
        foreach ($columns as $key => $title) {
            if ($key == 'title'){
                $new['preview_image_help'] = __('Preview Image Help');
            }
            elseif ($key == 'date'){
                 $new['name'] = __('Name');
                 $new['description'] = __('Description');
                 $new['category'] = __('Category');
            }
     
            $new[$key] = $title;
            
        }
        //print_r($new);
       
        
        return $new;
 
 
    }
 
    else {
        return $columns;
    }
 
 
 
}



add_action('manage_pages_custom_column', 'admin_show_post_thumbnail_column_tyto_blocks', 5, 2);
 
// Get featured-thumbnail size post thumbnail and display it
function admin_show_post_thumbnail_column_tyto_blocks($theme_columns, $theme_id)
{
 
    // Check if post type is 'Product'
    global $pagenow, $typenow;
    if( 'tyto-blocks' === $typenow && 'edit.php' === $pagenow )
    {
        
  
        switch ($theme_columns) {
            case 'preview_image_help':
                $preview_image_help = get_post_meta( get_the_ID(), 'preview_image_help', true );
                 $permalink = get_edit_post_link();
                if(!empty($preview_image_help)){
                    
                     echo '<a href="' . $permalink . '"><img src="' . $preview_image_help . '" style="width:130px"></a>';
                }
            break;    
            case 'description':
                echo  get_post_meta( get_the_ID(), 'description', true );
            break;    
            case 'name':
                echo  get_post_meta( get_the_ID(), 'name', true );
            break;    
            case 'category':
                echo  get_post_meta( get_the_ID(), 'category', true );
            break;    
            
              
               
     
               
        }
 
    }
    else {
 
 
        return $theme_columns;
    }
            
     
}


