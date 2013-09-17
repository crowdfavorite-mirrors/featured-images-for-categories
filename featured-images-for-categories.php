<?php

/*
Plugin Name: Featured Images for Categories
Plugin URI: http://helpforwp.com/plugins/featured-images-for-categories/
Description: Assign a featured image to a WordPress category or tag, then use these featured images via a widget area or a shortcode. Custom taxonomies? Check our site for the Pro version.
Version: 1.0
Author: HelpForWP
Author URI: http://HelpForWP.com

------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, 
or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

require_once('featured-images-for-categories-widget.php');

class WPFeaturedImgCategories {

	public function __construct() {
		
		if( is_admin() ) {
			add_action( 'admin_init', array($this, 'wpfifc_register_settings') );
			add_action( 'admin_menu', array($this, 'wpfifc_add_admin_option_page') );
	    }
		add_action( 'widgets_init', create_function( '', 'register_widget( "WPFeaturedImgCategoriesWidget" );' ) );
		
		register_activation_hook( __FILE__, array($this, 'wpfifc_activate' ) );
		register_deactivation_hook( __FILE__, array($this, 'wpfifc_deactivate' ) );
		register_uninstall_hook( __FILE__,  'WPFeaturedImgCategories::wpfifc_remove_option' );
		
		//enale theme support feature iamge
		add_theme_support( 'post-thumbnails' );
		
		//create custome field for taxonomies
		add_action( 'admin_init', array($this, 'wpfifc_enqueue_scripts'), 999 );
		add_action( 'admin_init', array($this, 'wpfifc_taxonomies_add_form_fields'), 999 );
		add_action( 'admin_init', array($this, 'wpfifc_taxonomies_save_form_fields'), 999 );
		
		add_action( 'delete_post', array($this, 'wpfifc_remove_option_when_post_deleted'), 10);
		
		
		//ajax action
		add_action( 'wp_ajax_wpfifc-remove-image', array($this, 'wpfifc_ajax_set_post_thumbnail') );
		
		//shortcodes
		add_shortcode('FeaturedImagesCat', array($this, 'wpfifc_front_show') );
		
		add_action( 'genesis_before_loop', array($this, 'genesis_show_taxonomy_image'), 12 );
	}

	
	function wpfifc_activate() {
		//create a post for save 
		$wpfifc_curr_option =	get_option('wpfifc_image_padding');
		if( !$wpfifc_curr_option ){
			update_option('wpfifc_image_padding', '2' );
		}
		$wpfifc_curr_option =	get_option('wpfifc_default_columns');
		if( !$wpfifc_curr_option ){
			update_option('wpfifc_default_columns', '3' );
		}
		$wpfifc_curr_option =	get_option('wpfifc_default_size');
		if(!$wpfifc_curr_option){
			update_option('wpfifc_default_size', 'thumbnail' );
		}
		$wpfifc_curr_option =	get_option('wpfifc_genesis_taxonomy');
		if(!$wpfifc_curr_option){
			update_option('wpfifc_genesis_taxonomy', array() );
		}
		$wpfifc_curr_option =	get_option('wpfifc_genesis_position');
		if(!$wpfifc_curr_option){
			update_option('wpfifc_genesis_position', 'left');
		}
	}
	
	
	function wpfifc_deactivate(){
		
	}


	function wpfifc_remove_option() {
	 	/*delete_option('wpfifc_post_ids_save_image');
	 
		delete_option('wpfifc_image_padding');
		delete_option('wpfifc_default_columns');	
		delete_option('wpfifc_default_size');
		delete_option('wpfifc_genesis_taxonomy');
		delete_option('wpfifc_genesis_position');
		
		//widget option
		delete_option('widget_wpfifc_widget');
		*/

		return;
	}
	
	function wpfifc_add_admin_option_page(){
		add_options_page('Featured Image for Categories', 'Featured Image for Categories', 'manage_options', 'wpfifc_options', array($this, 'wpfifc_option_page') );
	}
	
	function wpfifc_option_page() {
		if ( ! current_user_can( 'manage_options' ) ){
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}		
		
		require_once('inc/featured-images-for-cat-options.php');
		//for title and body
		wpfifc_options_shown();

		
		//for footer
		require_once('inc/footer.php');
	}
	
	function wpfifc_register_settings() {
		register_setting( 'wpfifc-settings', 'wpfifc_image_padding' );
		register_setting( 'wpfifc-settings', 'wpfifc_default_columns' );
		register_setting( 'wpfifc-settings', 'wpfifc_default_size' );
		register_setting( 'wpfifc-settings', 'wpfifc_genesis_taxonomy' );
		register_setting( 'wpfifc-settings', 'wpfifc_genesis_position' );		
	}
	
	function wpfifc_enqueue_scripts(){
		wp_enqueue_style('thickbox');
		
		wp_enqueue_script('thickbox');
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'wpfifc-admin', plugin_dir_url( __FILE__ ) . 'js/featured-images-for-cat-admin.js', array( 'jquery' ) );
	}
	
	function wpfifc_taxonomies_add_form_fields(){
		add_action( 'category_add_form_fields', array($this, 'wpfifc_taxonomies_new_meta'), 10, 2 );
		add_action( 'category_edit_form_fields', array($this, 'wpfifc_taxonomies_edit_meta'), 10, 2 );
		add_action( 'post_tag_add_form_fields', array($this, 'wpfifc_taxonomies_new_meta'), 10, 2 );
		add_action( 'post_tag_edit_form_fields', array($this, 'wpfifc_taxonomies_edit_meta'), 10, 2 );
	}
	
	// Add term page
	function wpfifc_taxonomies_new_meta( $term ) {
		// this will add the custom meta field to the add new term page
		$post = get_default_post_to_edit( 'post', true );
		$post_ID = $post->ID;

		?>
        <style type="text/css" media="all">.inside a{ font-style:normal; }</style>
		<div class="form-field">
            <div id="postimagediv" class="postbox" style="width:95%;" >
				<div class="handlediv" title="Click to toggle"><br /></div>
				<h3 class='hndle' style="margin:0; padding:7px 10px 7px 10px; font-size:15px; font-weight:400;"><span>Featured Image</span></h3>
				<div class="inside">
                	<?php wp_enqueue_media( array('post' => $post_ID) ); ?>
					<?php
						$thumbnail_id = get_post_meta( $post_ID, '_thumbnail_id', true );
						echo _wp_post_thumbnail_html( $thumbnail_id, $post_ID );
                    ?>
                </div>
                <input type="hidden" name="wpfifc_taxonomies_post_ID" id="wpfifc_taxonomies_post_ID_id" value="<?php echo $post_ID; ?>" />
                <div style="clear:both;"></div>
			</div>
        </div>
	<?php
	}
	
	
	
	//edit term page
	function wpfifc_taxonomies_edit_meta( $term ) {
		global $wpdb;
		
 		// put the term ID into a variable
		$t_id = $term->term_id;
		$post_ID = get_option( "_wpfifc_taxonomy_term_$t_id", 0 ); 
		if ( $post_ID == 0 ){
			$post = get_default_post_to_edit( 'post', true );
			$post_ID = $post->ID;
		}else{
			$thumbnail_id = get_post_meta( $post_ID, '_thumbnail_id', true );
			$image = wp_get_attachment_image_src( $thumbnail_id, $imagesize );
			list($src, $width, $height) = $image;
			if ( !$src ){
				$sql = 'DELETE FROM '.$wpdb->options.' WHERE option_name LIKE "_wpfifc_taxonomy_term_%" AND option_value = '.$post_ID;
				$wpdb->query( $sql );
				
				$post = get_default_post_to_edit( 'post', true );
				$post_ID = $post->ID;
			}
		}
	?>
        <tr class="form-field">
			<th>Set Featured Image</th>
            <td>
            	<div id="postimagediv" class="postbox" style="width:95%;" >
                    <div class="inside">
                        <?php wp_enqueue_media( array('post' => $post_ID) ); ?>
                        <?php
                            $thumbnail_id = get_post_meta( $post_ID, '_thumbnail_id', true );
                            echo _wp_post_thumbnail_html( $thumbnail_id, $post_ID );
                        ?>
                    </div>
                    <input type="hidden" name="wpfifc_taxonomies_post_ID" id="wpfifc_taxonomies_edit_post_ID_id" value="<?php echo $post_ID; ?>" />
                </div>
        	</td>
		</tr>
	<?php
	}
	
	function wpfifc_taxonomies_save_form_fields(){
		add_action('edited_category', array($this, 'wpfifc_taxonomies_save_meta'), 10, 2 );  
		add_action('create_category', array($this, 'wpfifc_taxonomies_save_meta'), 10, 2 );
		add_action('edited_post_tag', array($this, 'wpfifc_taxonomies_save_meta'), 10, 2 );  
		add_action('create_post_tag', array($this, 'wpfifc_taxonomies_save_meta'), 10, 2 );
	}

	function wpfifc_taxonomies_save_meta( $term_id ) {
		if ( isset( $_POST['wpfifc_taxonomies_post_ID'] ) ) {
			update_option( "_wpfifc_taxonomy_term_$term_id", trim($_POST['wpfifc_taxonomies_post_ID']) );
		}
	}  
	
	function wpfifc_remove_option_when_post_deleted( $post_id ){
		global $wpdb;
		
		$sql = 'DELETE FROM '.$wpdb->options.' WHERE option_name LIKE "_wpfifc_taxonomy_term_%" AND option_value = '.$post_id;
		$wpdb->query( $sql );
	}
	function wpfifc_ajax_set_post_thumbnail() {
		global $current_user;

		if ( $current_user->ID < 0 ){
			wp_die( 'ERROR:You are not allowed to do the operation.' );
		}
		$post_ID = intval( $_POST['post_id'] );
		if ( $post_ID < 1 ){
			wp_die( "ERROR:Invalid post ID.".$post_ID );
		}
		$thumbnail_id = intval( $_POST['thumbnail_id'] );
		if ( $thumbnail_id == '-1' ){
			if ( delete_post_thumbnail( $post_ID ) ) {
				$return = _wp_post_thumbnail_html( null, $post_ID );
				wp_die( $return );
			}else{
				wp_die( "ERROR:Remove featured image failed." );
			}
		}
		wp_die( "ERROR" );
	}
	
	function wpfifc_front_show( $atts ){
		extract( shortcode_atts( array(
							  'taxonomy' => '',
							  'columns' => 0,
							  'imagesize' => '',
							  'orderby' => 'name',
							  'order' => 'ASC',
							  'hideempty' => 0), 
						$atts )
			   );
		$taxonomy = strtolower($taxonomy);
		if ( $taxonomy == '' || ($taxonomy != 'category' && $taxonomy != 'post_tag') ){
			return '';
		}
		$taxonomy_obj = get_taxonomy( $taxonomy );
		if ( !$taxonomy_obj ){
			return '';
		}
		$orderby = strtolower($orderby);
		if($orderby != 'name' && $orderby != 'slug' && $orderby != 'id' && $orderby != 'count'){
			$orderby != 'name';
		}
		$order = strtoupper($order);
		if($order != 'ASC' && $order != 'DESC'){
			$order = 'ASC';
		}
		$hideempty = intval($hideempty);
		if($hideempty !== 0 && $hideempty !== 1){
			$hideempty = 0;
		}

		//get terms
		$taxonomy_terms = get_terms( $taxonomy, array('orderby' => $orderby, 'order' => $order, 'hide_empty' => $hideempty) );
		if ( !$taxonomy_terms || count($taxonomy_terms) < 1 ){
			return '';
		}
		//check imagesize
		$imagesize = 'thumbnail';

		//get padding
		$padding = get_option('wpfifc_image_padding');
		//get columns
		if ( $columns == 0 ){
			$columns = get_option('wpfifc_default_columns'); 	
		}
		//caculate column width
		$column_width = floor(100 / $columns);
		
		$output = '<div class="FeaturedImageTax">'."\n";
		$images_str = '';
		$column_item = 0;
		foreach( $taxonomy_terms as $term ){
			$term_id = $term->term_id;
			$post_ID = get_option('_wpfifc_taxonomy_term_'.$term_id, 0);
			if ( $post_ID < 1 ){
				continue;
			}

			$thumbnail_id = get_post_meta( $post_ID, '_thumbnail_id', true );
			$image = wp_get_attachment_image_src( $thumbnail_id, $imagesize );

			list($src, $width, $height) = $image;
			if ( $src ){
				$padding_str = $padding ? $padding.'px;' : '0;';
				$images_str .= '<div style="width:'.$column_width.'%; text-align:center;float:left;">
									<a href="'.get_term_link($term->slug, $taxonomy).'" title="'.$term->name.'">
										<img src="'.$src.'" alt="'.$term->name.'" style="padding:'.$padding_str.'" />
									</a>
								</div>'."\n";
				$column_item++;
				if ( $column_item >= $columns ){
					$column_item = 0;
					$images_str .= "\n".'<div style="clear:both;"></div>'."\n";
				}
			}
		}
		$output .= $images_str;
		$output .= '</div>'."\n";
		
		return $output;
	}
	
	
	function genesis_show_taxonomy_image(){
		if(!defined('PARENT_THEME_NAME') || PARENT_THEME_NAME != 'Genesis'){
			return;
		}
		global $wp_query;

		if (!is_category() && !is_tag()){
			return;
		}
	
		if (get_query_var( 'paged' ) >= 2){
			return;
		}
		$taxonomy = '';
		if(is_category()){
			$taxonomy = 'category';
		}
		if(is_tag()){
			$taxonomy = 'post_tag';
		}
		if(is_tax()){
			return;
		}
		$saved_genesis_taxonomies = get_option('wpfifc_genesis_taxonomy', array());
		$saved_genesis_postion = get_option('wpfifc_genesis_position', 'left');
		$imagesize = get_option('wpfifc_default_size', 'thumbnail');
		if ( $imagesize == '' ){
			$imagesize = 'thumbnail';
		}
		if (!is_array($saved_genesis_taxonomies) || count($saved_genesis_taxonomies) < 1){
			return;
		}
		if(!in_array($taxonomy, $saved_genesis_taxonomies)){
			return;
		}

		$term = $wp_query->get_queried_object();
		if (!$term){
			return;
		}
		$term_id = $term->term_id;
		$post_ID = get_option('_wpfifc_taxonomy_term_'.$term_id, 0);
		if ( $post_ID < 1 ){
			return;
		}
		$thumbnail_id = get_post_meta( $post_ID, '_thumbnail_id', true );
		$image = wp_get_attachment_image_src( $thumbnail_id, $imagesize );
		
		list($src, $width, $height) = $image;
		if ( $src ){
			echo '<img src="'.$src.'" style="float:'.$saved_genesis_postion.';" class="FeaturedImageTax"/>';
		}
		
		return;
	}
}


$wpfifc_instance = new WPFeaturedImgCategories();