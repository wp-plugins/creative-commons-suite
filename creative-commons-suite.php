<?php

/*
Plugin Name: Creative Commons Suite
Version: 0.5
Plugin URI: http://coenjacobs.net/wordpress/plugins/creative-commons-suite
Description: Makes it easy to display a link to the Creative Commons license you desire in posts and pages.
Author: Coen Jacobs
Author URI: http://coenjacobs.net/
*/

// Determine plugin directory
$cc_suitepath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';

// Add a menu for the plugin
function cc_suite_admin_menu() {
	add_options_page('Creative Commons Suite', 'Creative Commons Suite', 8, 'Creative Commons Suite', 'cc_suite_submenu');
}
add_action('admin_menu', 'cc_suite_admin_menu');

function cc_suite_message($message) {
	echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}

/**
 * Displays the cc-suite admin menu
 */
function cc_suite_submenu() {
	global $cc_suitepluginpath;
	
	if (isset($_REQUEST['save']) && $_REQUEST['save']) {
		check_admin_referer('cc-suite-config');
		
		foreach ( array('usetargetblank', 'tagline', 'url', 'allow_commercial', 'allow_modifications', 'share_alike', 'style_before', 'style_after') as $val ) {
			if ( !$_POST[$val] )
				update_option( 'cc-suite_'.$val, '');
			else
				update_option( 'cc-suite_'.$val, $_POST[$val] );
		}
		
		/**
		 * Update conditional displays
		 */
		$conditionals = Array();
		if (!$_POST['conditionals'])
			$_POST['conditionals'] = Array();
		
		$curconditionals = get_option('cc-suite_conditionals');
		
		if (!array_key_exists('is_home',$curconditionals)) {
			$curconditionals['is_home'] = false;
		}
		if (!array_key_exists('is_single',$curconditionals)) {
			$curconditionals['is_single'] = false;
		}
		if (!array_key_exists('is_page',$curconditionals)) {
			$curconditionals['is_page'] = false;
		}
		if (!array_key_exists('allow_commercial',$curconditionals)) {
			$curconditionals['allow_commercial'] = false;
		}
		if (!array_key_exists('allow_modifications',$curconditionals)) {
			$curconditionals['allow_modifications'] = false;
		}
		if (!array_key_exists('share_alike',$curconditionals)) {
			$curconditionals['share_alike'] = false;
		}

		foreach($curconditionals as $condition=>$toggled)
			$conditionals[$condition] = array_key_exists($condition, $_POST['conditionals']);
			
		update_option('cc-suite_conditionals', $conditionals);

		cc_suite_message(__("Saved changes.", 'cc-suite'));
	}
	
	/**
	 * Display options.
	 */
?>
<form action="<?php echo attribute_escape( $_SERVER['REQUEST_URI'] ); ?>" method="post">
<?php
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('cc-suite-config');
?>

<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e("Creative Commons Suite Options", 'cc-suite'); ?></h2>
	
	<div style="float: right; width: 25%; background: #DEDEDE; color: #000">
		<div style="padding: 10px;">
			<b>About this plugin</b>
			<p style="line-height: 160%;">Creative Commons Suite is developed by <a href="http://coenjacobs.net/">Coen Jacobs</a>. If you like it, please consider making a small <a href="http://coenjacobs.net/donate"><b>donation</b> to the author</a> or if you're on Twitter; <a href="http://twitter.com/coenjacobs">follow <b>@coenjacobs</b></a>!</p>
			<b>Need support?</b>
			<p style="line-height: 160%;">Please use the special <a href="http://wordpress.org/tags/creative-commons-suite?forum_id=10">support forum</a> for this plugin at WordPress.org for support.</p>
		</div>
	</div>
	
	<div style="float: left; width: 75%;">
	<table class="form-table">
	<tr>
		<th scope="row" valign="top">
			<?php _e("Link text", "cc-suite"); ?>
		</th>
		<td>
			<?php
				if(attribute_escape(stripslashes(get_option('cc-suite_tagline'))) == null)
				{
					$form_title = "This content is published under the %1 license.";
				} else {
					$form_title = attribute_escape(stripslashes(get_option('cc-suite_tagline')));
				}
			?>
			<?php _e("Change the text displayed as the link. <code>%1</code> will be replaced by the license title.", 'cc-suite'); ?><br/>
			<input size="80" type="text" name="tagline" value="<?php echo $form_title; ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			<?php _e("License:", "cc-suite"); ?>
		</th>
		<td>
			<?php _e("Select what license you would like to apply on your content.", 'cc_suite'); ?><br/>
			<br/>
			<?php
			$conditionals = get_option('cc-suite_conditionals');
			?>
			<input type="checkbox" name="conditionals[allow_commercial]"<?php echo ($conditionals['allow_commercial']) ? ' checked="checked"' : ''; ?> /> <?php _e("Allow commercial use of your work", 'cc-suite'); ?><br/>
			<input type="checkbox" name="conditionals[allow_modifications]"<?php echo ($conditionals['allow_modifications']) ? ' checked="checked"' : ''; ?> /> <?php _e("Allow modifications of your work", 'cc-suite'); ?><br/>
			<input type="checkbox" name="conditionals[share_alike]"<?php echo ($conditionals['share_alike']) ? ' checked="checked"' : ''; ?> /> <?php _e("Share alike - share modifications under the same license", 'cc-suite'); ?><br/>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			<?php _e("Position:", "cc-suite"); ?>
		</th>
		<td>
			<?php _e("The link to the Creative Commons license can be displayed at the end of each blog post, and posts may show on many different types of pages. Please select where you want to display the link.", 'cc_suite'); ?><br/>
			<br/>
			<input type="checkbox" name="conditionals[is_home]"<?php echo ($conditionals['is_home']) ? ' checked="checked"' : ''; ?> /> <?php _e("Front page", 'cc-suite'); ?><br/>
			<input type="checkbox" name="conditionals[is_single]"<?php echo ($conditionals['is_single']) ? ' checked="checked"' : ''; ?> /> <?php _e("Individual blog posts", 'cc-suite'); ?><br/>
			<input type="checkbox" name="conditionals[is_page]"<?php echo ($conditionals['is_page']) ? ' checked="checked"' : ''; ?> /> <?php _e('Individual WordPress "Pages"', 'cc-suite'); ?><br/>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			<?php _e("Open in new window:", "cc_suite"); ?>
		</th>
		<td>
			<input type="checkbox" name="usetargetblank" <?php checked( get_option('cc-suite_usetargetblank'), true ); ?> /> <?php _e("Use <code>target=_blank</code> on links? (Forces links to open a new window)", "cc_suite"); ?>
		</td>		
	</tr>
	<tr>
		<th scope="row" valign="top">
			<?php _e("Styling", "cc-suite"); ?>
		</th>
		<td>
			<?php
				if(attribute_escape(stripslashes(get_option('cc-suite_style_before'))) == null)
				{
					$style_before = "<p>";
				} else {
					$style_before = attribute_escape(stripslashes(get_option('cc-suite_style_before')));
				}
			?>
			<?php _e("Element before the text:", 'cc-suite'); ?>
			<input size="80" type="text" name="style_before" value="<?php echo $style_before; ?>" /><br />
			<?php
				if(attribute_escape(stripslashes(get_option('cc-suite_style_after'))) == null)
				{
					$style_after = "</p>";
				} else {
					$style_after = attribute_escape(stripslashes(get_option('cc-suite_style_after')));
				}
			?>
			<?php _e("Element after the text:", 'cc-suite'); ?>
			<input size="80" type="text" name="style_after" value="<?php echo $style_after; ?>" />
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<span class="submit"><input name="save" value="<?php _e("Save Changes", 'cc-suite'); ?>" type="submit" /></span>
		</td>
	</tr>
	</table>
</div>


</div>
</form>
<?php
}

function cc_suite_display_hook($content='') {
	$conditionals = get_option('cc-suite_conditionals');
	if ((is_home()     and $conditionals['is_home']) or
	    (is_single()   and $conditionals['is_single']) or
	    (is_page()     and $conditionals['is_page'])) {
		$content .= cc_suite_display();
	}
	return $content;
}

function cc_suite_display($content='') {
	if(get_option("cc-suite_usetargetblank")) {
		$extra = " target=\"_blank\"";
	} else {
		$extra = "";
	}
	
	$license = "by";
	$title = "Attribution";
	
	$conditionals = get_option('cc-suite_conditionals');
	
	if($conditionals['allow_commercial']) {
		$license .= "";
	} else {
		$license .= "-nc";
		$title .= "-Noncommercial";
	}

	if(!$conditionals['share_alike']) {
		if(!$conditionals['allow_modifications']) {
			$license .= "-nd";
			$title .= "-No Derivative Works";
		} else {
			$license .= "";
		}
	} else {
		$license .= "-sa";
		$title .= "-Share Alike";
	}
	
	$title .= " 3.0 Unported";
	
	$url = "<a href=\"http://creativecommons.org/licenses/".$license."/3.0/\" ".$extra.">".$title."</a>";
	$tagline = str_replace('%1', $url, get_option("cc-suite_tagline"));
	$content .= stripslashes(get_option("cc-suite_style_before")).$tagline.stripslashes(get_option("cc-suite_style_after"));
	return $content;
}

function cc_suite_plugin_actions( $links, $file ){
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
	
	if ( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=Creative Commons Suite">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}
add_filter( 'plugin_action_links', 'cc_suite_plugin_actions', 10, 2 );

add_filter('the_content', 'cc_suite_display_hook');
add_filter('the_excerpt', 'cc_suite_display_hook');

?>