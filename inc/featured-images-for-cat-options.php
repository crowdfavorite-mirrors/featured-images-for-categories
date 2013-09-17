<?php

// ********** OPTIONS PAGE **********
// **********************************

function wpfifc_options_shown() {

	$action = 'options.php';
	
	// collect some URL for use in the settings
	
	$h4wp_admin_url = get_admin_url();
?>
    <div class="wrap">
        <img src="<?PHP echo plugins_url(); ?>/featured-images-for-categories/images/help-for-wordpress-small.png" alt="Help For WordPress Logo" style="float:left;" />
        <h2 style="padding:10px 0 10px 10px; font-size:26px; font-weight: normal;">Featured Images for Categories</h2>
    	
    	<form action="<?php echo $action; ?>" method="POST" id="wpls_settings">
        <?php 
			settings_fields( 'wpfifc-settings' );
	    ?>
        <?php 
			$padding = get_option('wpfifc_image_padding', 0); 
			$columns = get_option('wpfifc_default_columns'); 
			$saved_size = get_option('wpfifc_default_size', 'thumbnail');
			$image_sizes = get_intermediate_image_sizes();
		?>
        <h3>Plugin Settings</h3>
      
    	<table>
        	<tr style="width:160px;">
            	<td>
                	<input name="wpfifc_image_padding" type="text" id="wpfifc_image_padding_id" value="<?php echo $padding; ?>" style="width:20px;" maxlength="1" />&nbsp;&nbsp;
                </td>
                <td>Number of px to place around the image when output.</td>
            </tr>
        	<tr>
            	<td style="width:160px;">
                	<select name="wpfifc_default_columns" id="wpfifc_default_columns_id" style="width:150px;">
                        <option value="1"<?php if ($columns == 1) echo ' selected="selected"' ?>>1</option>	
                        <option value="2"<?php if ($columns == 2) echo ' selected="selected"' ?>>2</option>	
                        <option value="3"<?php if ($columns == 3) echo ' selected="selected"' ?>>3</option>	
                        <option value="4"<?php if ($columns == 4) echo ' selected="selected"' ?>>4</option>	
                        <option value="5"<?php if ($columns == 5) echo ' selected="selected"' ?>>5</option>	
                        <option value="6"<?php if ($columns == 6) echo ' selected="selected"' ?>>6</option>	
                    </select>
                </td>
                <td>
		            Choose the number of columns for the output (This sets a default, you can override this in the shortcode)     
                </td>
            </tr>
       
        	<tr>
            	<td colspan="2"><p style="margin-top: 20px"><button class="button-primary" type="submit" id="wpls_admin_submit">Save Settings</button></p></td>
            </tr>
        </table>
        </form>
        
        <h3>Quick Start Guide</h3>
    	<img src="<?PHP echo plugins_url(); ?>/featured-images-for-categories/images/category-screen-shot.png" align="right" />
    	<p>Once activated this plugin will add the ability to assign a featured image to WordPress categories and tags (support for custom taxonomies is available in our <a target="_blank" href="http://helpforwp.com/downloads/featured-images-for-categories/?utm_source=FIMAGESFCATEGORIES&utm_medium=Plugin&utm_campaign=FIMAGESFCATEGORIES">Pro version</a>).</p>
    	
    	<p>Visit the <a href="<?PHP echo $h4wp_admin_url;?>edit-tags.php?taxonomy=category">Category page here</a> in your dashboard to see the new featured images option for each category, it works for tags too!</p>
    	
    	<p>You can assign a featured image to a new category when you're creating it or to an existing category when editing it, simply edit the category and click 'Set featured image'.</p>
    	
    	<h4>Display featured images with a shortcode</h4>
    	<p>To display featured images for categories or tags on a page or post in your WordPress site use this shortcode.</p>
    	<p>
	    	[FeaturedImagesCat taxonomy='category' columns='3']
	    	
    	</p>
    	
    	<p>To use with tags instead change the 'category' value to 'post_tags' like this</p>
    	<p>[FeaturedImagesCat taxonomy='post_tag' columns='3'] </p>
    	<p>The columns value can be adjusted to determine the number of columns the shortcode will produce.
    	
    	<h4>Display featured images with a widget</h4>
    	<p>Visit the <a href="<?PHP echo $h4wp_admin_url;?>widgets.php">Widget section</a> of your WordPress Dashboard and you'll see a new widget "Featured Images for Categories". Drag this into a widget area to display your featured images.</p>
    	<p>
    	The widget allows you to select Categories or Tags, choose the number of columns of the output, sort order and more. 
    	</p>
    	<img src="<?PHP echo plugins_url(); ?>/featured-images-for-categories/images/Widget-screenshot.png" />
    	
    	<p>More detailed instructions are available <a target="_blank" href="http://helpforwp.com/plugins/featured-images-for-categories/?utm_source=FIMAGESFCATEGORIES&utm_medium=Plugin&utm_campaign=FIMAGESFCATEGORIES">here on HelpForWP.com</a></p>
        
        
    </div>
<?php
}
?>


