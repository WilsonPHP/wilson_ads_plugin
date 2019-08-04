<?php
/**
* Plugin Name: Wilson Ads
* Plugin URI: https://wilson.com.ar
* Description: A basic Ad System for Wordpress for authors to add, edit, and remove ads.
* Version: 1.0
* Author: Wilson Fernandez
* Author URI: https://wilson.com.ar/
**/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function wilson_ads_includes_back(){
  // CSS
  wp_enqueue_style('datetimepicker_css', plugins_url('vendors/datetimepicker/jquery.datetimepicker.min.css',__FILE__ ));
  // JS
  wp_enqueue_script('datetimepicker_js', plugins_url('vendors/datetimepicker/jquery.datetimepicker.full.js',__FILE__ ), array ( 'jquery' ), 1.1, true);
  wp_enqueue_script('custom_back_js', plugins_url('js/custom_back.js',__FILE__ ), array ( 'jquery' ), 1.1, true);
}
add_action( 'admin_enqueue_scripts','wilson_ads_includes_back');

function wilson_ads_includes_front() {
    // CSS
    wp_enqueue_style('custom_css', plugins_url('style.css',__FILE__ ));
    wp_enqueue_style('dscountdown_css', plugins_url('vendors/dscountdown/dscountdown.css',__FILE__ ));
    
    // JS
    wp_enqueue_script('dscountdown_js', plugins_url('vendors/dscountdown/dscountdown.js',__FILE__ ), array ( 'jquery' ), 1.1, true);
    wp_enqueue_script('custom_front_js', plugins_url('js/custom_front.js',__FILE__ ), array ( 'jquery' ), 1.1, true);
}
add_action( 'wp_enqueue_scripts','wilson_ads_includes_front');

// custom post type
function wilson_ads_cpt() {
    register_post_type( 'ads',
        array(
            'labels' => array(
                'name'                => _x( 'Ads' ),
                'singular_name'       => _x( 'Ad' ),
                'menu_name'           => __( 'Wilson Ads' ),
                'parent_item_colon'   => __( 'Parent Ad' ),
                'all_items'           => __( 'All Ads' ),
                'view_item'           => __( 'View Ad' ),
                'add_new_item'        => __( 'Add New Ad' ),
                'add_new'             => __( 'Add New' ),
                'edit_item'           => __( 'Edit Ad' ),
                'update_item'         => __( 'Update Ad' ),
                'search_items'        => __( 'Search Ad' ),
                'not_found'           => __( 'Not Found' ),
                'not_found_in_trash'  => __( 'Not found in Trash' )
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-welcome-widgets-menus',
            'rewrite' => array('slug' => 'ads'),
        )
    );
}
add_action( 'init', 'wilson_ads_cpt' );

// metabox
function wilson_ads_meta_box() {
    	add_meta_box(
    		'your_fields_meta_box',
    		'Custom fields',
    		'wilson_ads_meta_box_html',
    		'ads',
    		'normal',
    		'high'
    	);
    }
 add_action( 'add_meta_boxes', 'wilson_ads_meta_box' );

// metabox content
function wilson_ads_meta_box_html($post){
    $value = get_post_meta($post->ID, '_type_meta_key', true);
    $template = get_post_meta($post->ID, '_template_meta_key', true);
    $bg_color = get_post_meta($post->ID, '_bg_color_meta_key', true);
    $timedatepicker = get_post_meta($post->ID, '_timedatepicker_meta_key', true);
    ?>
    <h4>Type</h4>
    <select name="type" id="type" class="postbox">
        <option value="">Select type...</option>
        <option value="pick" <?php selected($value, 'pick'); ?>>Pick</option>
    </select>
    <h4>Template</h4>
    <select name="template" id="template" class="postbox">
        <option value="">Select template...</option>
        <option value="nfl" <?php selected($template, 'nfl'); ?>>Our NFL Pick: Vikings +3</option>
    </select>
    <h4>Background color</h4>
    <input type="text" name="bg_color" id="bg_color" value="<?php echo $bg_color;?>">
    <h4>When does the counter end?</h4>
    <input type="text" name="timedatepicker" id="timedatepicker" value="<?php echo $timedatepicker;?>"/>
    <h4>Shortcode</h4>
    [wilson_ad id=<?php echo get_the_ID();?>]
    <?php
}

// metabox fields save
function wilson_ads_meta_box_save($post_id){
    if (array_key_exists('type', $_POST)) {
        update_post_meta(
            $post_id,
            '_type_meta_key',
            $_POST['type']
        );
    }
    if (array_key_exists('template', $_POST)) {
        update_post_meta(
            $post_id,
            '_template_meta_key',
            $_POST['template']
        );
    }
    if (array_key_exists('bg_color', $_POST)) {
        update_post_meta(
            $post_id,
            '_bg_color_meta_key',
            $_POST['bg_color']
        );
    }
    if (array_key_exists('timedatepicker', $_POST)) {
        update_post_meta(
            $post_id,
            '_timedatepicker_meta_key',
            $_POST['timedatepicker']
        );
    }
}
add_action('save_post', 'wilson_ads_meta_box_save');

// shortcode
function wilson_ads_shortcode( $atts ) {
		$a = shortcode_atts( array(
				'id' => 19,
				), $atts );
		$post_id = $a['id'];
		
    $queried_post = get_post($post_id);
    $title = $queried_post->post_title;

    $type = get_post_meta($post_id, '_type_meta_key', true);
    $bg_color = get_post_meta($post_id, '_bg_color_meta_key', true);

    $result = wilson_ads_types($type, $bg_color, $post_id);

    return $result;
}
add_shortcode( 'wilson_ad', 'wilson_ads_shortcode' );

// ads custom types
function wilson_ads_types($type, $color, $id){
  
    $back = plugins_url('/templates/'.$type.'/img/image.jpg',__FILE__ );
    $button = plugins_url('/templates/'.$type.'/img/button.jpg',__FILE__ );
    
    // prepared for multiple types
    switch ($type) {
    	case 'pick':
        
        global $post;
        $postcat = get_the_category( $post->ID );
        $cat = $postcat[0]->cat_name;
        
        // category fixed color override
        if($cat){
          switch ($cat) {
    	      case 'NFL':
                $color = 'black';
              break;
            case 'NBA':
                $color = 'orange';
              break;
            case 'MLB':
                $color = 'blue';
              break;
          }
        }
        
        $date = get_post_meta($id, '_timedatepicker_meta_key', true);
    		$type = "
        <input type='hidden' name='date' id='date' value='".$date."'>
        <div class='wilson_ads_wrapper'>
            <div class='wilson_ads_container wilson_ads_container1' style='background-image: url(".$back.");'></div>
            <div class='wilson_ads_container wilson_ads_container2' style='background-color: ".$color."'>
                <div class='wilson_ads_middle_up'>
                    <div class='wilson_ads_counter'></div>
                    <div class='wilson_ads_text1'>
                      Remaining Time<br>To Place Bet
                    </div>
                </div>
                <div class='wilson_ads_middle_down'>
                    <div class='wilson_ads_text2'>Our NFL Pick: Vikings +3</div>
                    <div class='wilson_ads_text3'>Hurry up! 25 people have placed this bet</div>
                </div>
            </div>
            <div class='wilson_ads_container wilson_ads_container3'>
              <div class='wilson_ad_button'><a href='#'><img src='".$button."'></a></div>
              <div class='wilson_ad_white'>Trusted<br>Sportsbetting.ag</div>
            </div>
        </div>
        ";
    		break;
    	default:
    		$type = "<div class='ad_responsive'>Default</div>";
    }
    return $type;
}