<?php

/*
Plugin Name: Featured Images for Categories
Plugin URI: http://helpforwp.com/plugins/featured-images-for-categories/
Description: Assign a featured image to a WordPress category or tag, then use these featured images via a widget area or a shortcode. Custom taxonomies? Check our site for the Pro version.
Version: 1.2.2
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
	
	var $_database_version = 132;
	var $_database_option_name = '_wpfifc_taxonomy_term_database_version_';

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
		
		//check and update database format
		$this->wpfifc_upgrade_database();
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
		add_action( 'category_edit_form_fields', array($this, 'wpfifc_taxonomies_edit_meta'), 10, 2 );
		add_action( 'post_tag_edit_form_fields', array($this, 'wpfifc_taxonomies_edit_meta'), 10, 2 );
	}
	
	//edit term page
	function wpfifc_taxonomies_edit_meta( $term ) {
		global $wpdb;
		
 		// put the term ID into a variable
		$term_id = $term->term_id;
		$post = get_default_post_to_edit( 'post', true );
		$post_ID = $post->ID;
	?>
        <tr class="form-field">
			<th>Set Featured Image</th>
            <td>
            	<div id="postimagediv" class="postbox" style="width:95%;" >
                    <div class="inside">
                        <?php wp_enqueue_media( array('post' => $post_ID) ); ?>
                        <?php
							$thumbnail_id = get_option( '_wpfifc_taxonomy_term_'.$term_id.'_thumbnail_id_', 0 );
                            echo _wp_post_thumbnail_html( $thumbnail_id, $post_ID );
                        ?>
                    </div>
                    <input type="hidden" name="wpfifc_taxonomies_edit_post_ID" id="wpfifc_taxonomies_edit_post_ID_id" value="<?php echo $post_ID; ?>" />
                    <input type="hidden" name="wpfifc_taxonomies_edit_term_ID" id="wpfifc_taxonomies_edit_term_ID_id" value="<?php echo $term_id; ?>" />
                </div>
        	</td>
		</tr>
	<?php
	}
	
	function wpfifc_taxonomies_save_form_fields(){
		add_action('edited_category', array($this, 'wpfifc_taxonomies_save_meta'), 10, 2 );  
		add_action('edited_post_tag', array($this, 'wpfifc_taxonomies_save_meta'), 10, 2 );  
	}

	function wpfifc_taxonomies_save_meta( $term_id ) {
		if ( isset( $_POST['wpfifc_taxonomies_create_post_ID'] ) ) {
			$default_post_ID = $_POST['wpfifc_taxonomies_create_post_ID'];
		}else if ( isset( $_POST['wpfifc_taxonomies_edit_post_ID'] ) ) {
			$default_post_ID = $_POST['wpfifc_taxonomies_edit_post_ID'];
		}
		$thumbnail_id = get_post_meta( $default_post_ID, '_thumbnail_id', true );
		if( $thumbnail_id ){
			update_option( '_wpfifc_taxonomy_term_'.$term_id.'_thumbnail_id_', $thumbnail_id );
		}
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
		delete_post_thumbnail( $post_ID );

		$thumbnail_id = intval( $_POST['thumbnail_id'] );
		if ( $thumbnail_id == '-1' ){
			//delete option which used to saving thumbnail id
			if( $_POST['term_id'] > 0 ){
				delete_option( '_wpfifc_taxonomy_term_'.$_POST['term_id'].'_thumbnail_id_' );
			}
			$return = _wp_post_thumbnail_html( null, $post_ID );
			wp_die( $return );
			
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
							  'hideempty' => 0,
							  'showcatname' => 0,
							  'showcatdesc' => 0), 
						$atts )
			   );
		$show_cat_name = false;
		$show_cat_desc = false;
		if( $showcatname && is_string($showcatname) ){
			$show_cat_name = $showcatname == 'true' ? true : false;
		}else if( is_bool($showcatname) ){
			$show_cat_name = $showcatname;
		}
		$show_cat_desc = false;
		if( $showcatdesc && is_string($showcatdesc) ){
			$show_cat_desc = $showcatdesc == 'true' ? true : false;
		}else if( is_bool($showcatdesc) ){
			$show_cat_desc = $showcatdesc;
		}
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
			$thumbnail_id = get_option( '_wpfifc_taxonomy_term_'.$term_id.'_thumbnail_id_', 0 );
			if ( $thumbnail_id < 1 ){
				continue;
			}
			$image = wp_get_attachment_image_src( $thumbnail_id, $imagesize );

			list($src, $width, $height) = $image;
			if ( $src ){
				$padding_str = $padding ? $padding.'px;' : '0;';
				$images_str .= '<div style="width:'.$column_width.'%; text-align:center;float:left;">
									<a href="'.get_term_link($term->slug, $taxonomy).'" title="'.$term->name.'">
										<img src="'.$src.'" alt="'.$term->name.'" style="padding:'.$padding_str.'" />
									</a>';
				if( $show_cat_name ){
					$images_str .= '
									<a href="'.get_term_link($term->slug, $taxonomy).'" title="'.$term->name.'">
										<h2 class="FeaturedImageCat">'.$term->name.'</h2>
									</a>';
				}
				if( $show_cat_desc ){					
					$images_str .= '<div class="FeaturedImageCatDesc">'.$term->description.'</div>';
				}
				
				$images_str .= '	</div>'."\n";
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
	
	function wpfifc_upgrade_database(){
		global $wpdb;
		
		$exist_database_version = get_option( $this->_database_option_name, 0 );
		if( $exist_database_version >= $this->_database_version ){
			return;
		}
		
		//convert old data format to new and save database version
		$sql = 'SELECT `option_id`, `option_name`, `option_value` FROM `'.$wpdb->options.'` WHERE `option_name` LIKE "_wpfifc_taxonomy_term_%"';
		$results = $wpdb->get_results( $sql );
		if( !$results || count($results) < 1 ){
			update_option( $this->_database_option_name, $this->_database_version );
			return;
		}
		$old_option_ids = array();
		foreach( $results as $record ){
			$old_option_ids[] = $record->option_id;
			$term_id = str_replace('_wpfifc_taxonomy_term_', '', $record->option_name);
			$term_id = intval($term_id);
			$post_ID = $record->option_value;
			//check if the post still in database
			$sql_post = 'SELECT * FROM `'.$wpdb->posts.'` WHERE `ID` = '.$post_ID;
			if( !$wpdb->get_results( $sql_post ) ){
				continue;
			}
			//get thumbnail id
			$sql_postmeta = 'SELECT * FROM `'.$wpdb->postmeta.'` WHERE `post_id` = '.$post_ID.' AND `meta_key` = "_thumbnail_id"';
			$thumbnail_id_obj = $wpdb->get_results( $sql_postmeta );
			if( !$thumbnail_id_obj ){
				continue;
			}
			$thumbnail_id = $thumbnail_id_obj[0]->meta_value;
			$thumbnail_id = intval($thumbnail_id);
			if( !$thumbnail_id ){
				continue;
			}
			//write new option
			$new_option = '_wpfifc_taxonomy_term_'.$term_id.'_thumbnail_id_';
			update_option( $new_option, $thumbnail_id );
		}
		
		//remove old options
		$optons_ids_str = implode( ',', $old_option_ids );
		$optons_ids_str = trim( $optons_ids_str );
		$sql = 'DELETE FROM '.$wpdb->options.' WHERE option_id IN ('.$optons_ids_str.')';
		$wpdb->query( $sql );
		
		update_option( $this->_database_option_name, $this->_database_version );
	}
}


$wpfifc_instance = new WPFeaturedImgCategories();