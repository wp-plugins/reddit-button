<?php
/*
Plugin Name: RedditButton
Plugin URI: http://wordpress.org/plugins/reddit-button/
Description: Displays the <a href="http://reddit.com/buttons">reddit buttons</a> in your posts and can be configured to suit your liking.
Version: 1.3
Author: Christian Inzinger
Author URI: http://github.com/inz
License: GPLv2
*/
/*
Find the description at http://bringsfear.net/redditbutton


This plugin is using famfamfam silk icons (http://famfamfam.com/lab/icons/silk) and the reddit alien (http://reddit.com).

The plugin is licenced under the GPL 2, for further information see http://creativecommons.org/licenses/GPL/2.0/
*/

/** Get the default configuration array
 */
function _rb_get_default_config() {
	return array(
	'rb_home'		=> true,	/* show button on home page */
	'rb_single'		=> true,	/* show button on a single post's page */
	'rb_page'		=> true,	/* show button on a single page */
	'rb_category'		=> true,	/* show button on a category page */
	'rb_author'		=> true,	/* show button on an author's page */
	'rb_date'		=> true,	/* show button on a date-based archive page */
	'rb_search'		=> true,	/* show button on a search result page */
	'rb_feed'		=> false,	/* show button in posts gathered via feeds */

	'rb_buttonstyle'	=> '3',		/* reddit button style */
	'rb_behaviour'		=> "auto",	/* reddit button post inclusion behaviour */
	'rb_vpos'		=> "top",	/* show the button at the top of the post */
	'rb_hpos'		=> "left",	/* show the button on the right side */
	'rb_postcontrols'	=> true,	/* show reddit button controls with posts */
	'rb_debug'		=> false,	/* add debug comments to the output html */
	);
}

add_option('reddit_button_options', _rb_get_default_config(), 'Reddit Button options');
$rb_config = get_option('reddit_button_options');

// Create the option page
add_action('admin_menu', 'add_rb_option_page');

function add_rb_option_page() {
	add_options_page('Reddit Button Options', 'Reddit Button', 8, basename(__FILE__), 'rb_options_page');
}

function rb_options_page() {
	global $rb_config;
	if(!empty($_POST['_rb_update'])) { // update configuration
		$rb_config['rb_home'] = $_POST['rb_home'];
		$rb_config['rb_single'] = $_POST['rb_single'];
		$rb_config['rb_page'] = $_POST['rb_page'];
		$rb_config['rb_category'] = $_POST['rb_category'];
		$rb_config['rb_author'] = $_POST['rb_author'];
		$rb_config['rb_date'] = $_POST['rb_date'];
		$rb_config['rb_search'] = $_POST['rb_search'];
		$rb_config['rb_feed'] = $_POST['rb_feed'];

		$rb_config['rb_buttonstyle'] = $_POST['rb_buttonstyle'];
		$rb_config['rb_behaviour'] = $_POST['rb_behaviour'];
		$rb_config['rb_vpos'] = $_POST['rb_vpos'];
		$rb_config['rb_hpos'] = $_POST['rb_hpos'];
		$rb_config['rb_postcontrols'] = $_POST['rb_postcontrols'];
		$rb_config['rb_debug'] = $_POST['rb_debug'];

		update_option('reddit_button_options', $rb_config);

		echo '<div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>';
	} else if(!empty($_POST['_rb_restore'])) { // restore default configuration
		if($rb_config['rb_debug'])
			echo "<!-- rb_debug: restoring default config -->";
		$rb_config = _rb_get_default_config();
		update_option('reddit_button_options', $rb_config);

		echo '<div id="message" class="updated fade"><p><strong>Default options restored.</strong></p></div>';
	} else if(!empty($_GET['_rb_updatepost']) && !empty($_GET['_rb_action'])) {
		$_postid = $_GET['_rb_updatepost'];
		$_action = $_GET['_rb_action'];
		$_post = get_post($_postid);
		if(current_user_can('edit_post', $_postid) && $_post) {
			if($_action == 'auto' ||
			   $_action == 'always' ||
			   $_action == 'dnr') {
			   	if($_action == 'auto')
					$_action = '';
				_rb_update_post_meta($_postid, $_action);
				echo '<div id="message" class="updated fade"><p><strong>The options for "<a href='.get_permalink($_postid).' >'.$_post->post_title.'</a>" have been updated. ';
				if($_action == 'always')
					echo 'The reddit button will always appear for this post.';
				else if($_action == 'dnr')
					echo 'The reddit button will never be shown for this post.';
				else
					echo 'The reddit button will appear with this post as soon as it\' being redd.';
				echo '</div>';
			} else {
				echo '<div id="message" class="error fade"><p><strong>The requested action "'.$_GET['_rb_action'].'" could not be recognized.</strong></p></div>';
			}
		} else {
			echo '<div id="message" class="error fade"><p><strong>The requested post either does not exist or you do not have the necessary permissions to perform the requested action!</strong></p></div>';
		}
	}

	?>

	<div class="wrap">
		<h2>Reddit Button Options</h2>
		<form method="post" action="">
			<table width="100%" cellspacing="2" cellpadding="5" class="editform">
			<tr>
				<th width="33%" scope="row" valign="top"> Reddit button style:</th>
				<td>
					<label><input name="rb_buttonstyle" type="radio" value="1" <?php checked('1', $rb_config['rb_buttonstyle']); ?> /> Style 1: <br />
					<div style="position: relative; left: 20px;">
					<script>reddit_url='http://reddit.com/buttons'</script>
					<script language="javascript" src="http://reddit.com/button.js?t=1"></script></label><br /><br />
					</div>
					<label><input name="rb_buttonstyle" type="radio" value="2" <?php checked('2', $rb_config['rb_buttonstyle']); ?> /> Style 2: <br />
					<div style="position: relative; left: 20px;">
					<script>reddit_url='http://reddit.com/buttons'</script>
					<script language="javascript" src="http://reddit.com/button.js?t=2"></script></label><br /><br />
					</div>
					<label><input name="rb_buttonstyle" type="radio" value="3" <?php checked('3', $rb_config['rb_buttonstyle']); ?> /> Style 3: <br />
					<div style="position: relative; left: 20px;">
					<script>reddit_url='http://reddit.com/buttons'</script>
					<script language="javascript" src="http://reddit.com/button.js?t=3"></script></label>
					</div>
				</td>
			</tr>
			<tr>
				<th width="33%" scope="row" valign="top"> Show the reddit button on:</th>
				<td>
				<?php
					$_binary_display_options = array(
						'rb_home' => 'The home page',
						'rb_single' => 'A single post',
						'rb_page' => 'A single page',
						'rb_category' => 'A category page',
						'rb_author' => 'An author page',
						'rb_date' => 'A date-based archive',
						'rb_search' => 'Search results',
						//'rb_feed' => 'Posts federated using feeds'
						);
					foreach($_binary_display_options as $_optid => $_descr) {
				?>
					<label for="<?php echo $_optid; ?>">
					<input name="<?php echo $_optid; ?>" id="<?php echo $_optid; ?>" type="checkbox" <?php checked('1', $rb_config[$_optid]); ?> value="1" /> <?php echo $_descr; ?></label><br />
				<?php 	} ?>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"> Reddit Button placement:</th>
				<td>
					<label><input name="rb_vpos" type="radio" value="top" <?php checked('top', $rb_config['rb_vpos']); ?> /> At the top of the post</label><br />
					<label><input name="rb_vpos" type="radio" value="bottom" <?php checked('bottom', $rb_config['rb_vpos']); ?> /> Below the post</label><br />
					<br />
					<label><input name="rb_hpos" type="radio" value="left" <?php checked('left', $rb_config['rb_hpos']); ?> /> On the left side</label><br />
					<label><input name="rb_hpos" type="radio" value="right" <?php checked('right', $rb_config['rb_hpos']); ?> /> On the right side</label><br />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"> Reddit Button behaviour:</th>
				<td>
					<label><input name="rb_behaviour" type="radio" value="all" <?php checked('all', $rb_config['rb_behaviour']); ?> /> Show the Reddit Button on all posts</label><br /><div style="position: relative; left: 17px; color:#666;"><em>(You can manually prevent the reddit button from showing up on individual posts using the Reddit Button post controls.)</em></div>
					<label><input name="rb_behaviour" type="radio" value="auto" <?php checked('auto', $rb_config['rb_behaviour']); ?> /> Only show the Reddit Button on posts already redd by users </label><br /><div style="position: relative; left: 17px; color: #666;"><em>(Currently only checks if some page at reddit.com refers the post, not if it has acutally been redd.)</em></div>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"> Advanced options:</th>
				<td>
					<label><input name="rb_postcontrols" type="checkbox" value="1" <?php checked('1', $rb_config['rb_postcontrols']); ?> /> Show Reddit Button controls below each post.<br /><div style="position: relative; left: 17px; color: #666;"><em>(Allows you to individually configure reddit button behaviour for every single post.)</em></div></label>
					<label><input name="rb_debug" type="checkbox" value="1" <?php checked('1', $rb_config['rb_debug']); ?> /> Include debug comments on your blog.</label>
				</td>
			</tr>
			</table>
			<p class="submit">
				<input style="float:right" type='submit' name='_rb_update' id='_rb_update' value='Update Options &raquo;' />
				<input type='submit' name='_rb_restore' id='_rb_restore' value='Restore Default Options' />
			</p>
		</form>
	</div>

	<?php
}

add_filter('the_content', 'add_reddit_button');
add_filter('the_excerpt', 'add_reddit_button');

function add_reddit_button($content='') {
	global $rb_config;
	if((is_home() && $rb_config['rb_home']) ||
	   (is_single() && $rb_config['rb_single']) ||
	   (is_page() && $rb_config['rb_page']) ||
	   (is_author() && $rb_config['rb_author']) ||
	   (is_category() && $rb_config['rb_category']) ||
	   (is_date() && $rb_config['rb_date']) ||
	   (is_search() && $rb_config['rb_search']) ||
	   (is_feed() && $rb_config['rb_feed']) ||
	   0) {
	   	if($rb_config["rb_debug"])
		   	echo "<!-- redditbutton pos: ".$rb_config['rb_vpos']." -->\n";
	   	if($rb_config['rb_vpos'] == 'top')
			$content = reddit_button_code().$content;
		else
			$content .= reddit_button_code();
		if(current_user_can('edit_post', $post->ID) && $rb_config['rb_postcontrols'])
			$content .= reddit_post_controls();
	}

	return $content;
}

function reddit_button_code() {
	global $post, $rb_config;
	$ret = '';
	$_poststate = get_post_meta($post->ID, '_rb_reddit', true);
	if($rb_config['rb_debug']) {
		$ret .= "<!-- rb_post-meta: ".$_poststate." -->";
		$ret .= "<!-- rb_behaviour:   ".$rb_config['rb_behaviour']." -->";
	}

	if((is_page() || is_single()) && empty($_poststate) && $rb_config['rb_behaviour'] == "auto") {
		$referer = strtolower($_SERVER['HTTP_REFERER']);
		$redditreferer = "http://reddit.com/";
		if($rb_config['rb_debug']) {
			$ret .= "<!-- referrer:  ".$referer." -->\n";
			$ret .= "<!-- permalink: ".get_permalink($post->ID)." -->\n";
		}

		if(strncmp($referer, $redditreferer, strlen($redditreferer)) == 0) {
			_rb_update_post_meta($post->ID, 'redd');
			$_poststate = 'redd';
		}
	}

	if($_poststate == "redd" || $_poststate == "always" || $rb_config['rb_behaviour'] == "all" && $_poststate != "dnr") {
		$_style = "float: ".$rb_config['rb_hpos']."; ";
		if($rb_config['rb_buttonstyle'] == '1') {
			$_style .= "width: 140px; height: 21px;";
		} else if($rb_config['rb_buttonstyle'] == '2') {
			$_style .= "width: 57px; height: 85px;";
		} else if($rb_config['rb_buttonstyle'] == '3') {
			$_style .= "width: 66px; height: 66px;";
		}
		$_style .= " overflow: hidden; position: relative; margin-right: 8px;";
		$ret .= "<div style=\"".$_style."\">";
		$ret .= "<script>//<![CDATA[\nreddit_url=\"".get_permalink($post->ID)."\";\n//]]>\n</script>";
		$ret .= '<script language="javascript" src="http://reddit.com/button.js?t='.$rb_config[rb_buttonstyle].'"></script>';
		$ret .= "</div>";
	}
	return $ret;
}

function reddit_post_controls() {
	global $post;
	$_poststate = get_post_meta($post->ID, '_rb_reddit', true);

	$ret = "\n<style type=\"text/css\">\n<!--\n";
	$ret .= ".rb_control { border: 1px solid #ccc; width: auto; }\n";
	$ret .= ".rb_control td { width: 16px; }\n";
	$ret .= ".rb_control img { border: 0 !important; margin: 0 !important; padding: 0 !important; box-shadow: none; }\n";
	$ret .= "-->\n</style>\n";
	$ret .= '<table class="rb_control" border="0" ><tr>';
	$ret .= '<td><b><a href="'.get_bloginfo('url').'/wp-admin/options-general.php?page=reddit-button.php" title="Edit reddit button options"><img src="'.get_bloginfo('url').'/wp-admin/options-general.php?_rb_img=icon:alien" width="18px" height="18px" alt="reddit" title="Edit reddit button options" /></a></b></td>';
	$ret .= '<th valign="center"><b><a href="'.get_bloginfo('url').'/wp-admin/options-general.php?page=reddit-button.php" title="Edit reddit button options">:</a> </b></td>';
	$ret .= '<td><img " ';
	$ret .= 'src="'.get_bloginfo('url')."/wp-admin/options-general.php?_rb_img=";
	if($_poststate == 'redd') {
		$ret .= 'state:redd" alt="redd" title="This post has already been redd"';
	} else if($_poststate == 'always') {
		$ret .= 'state:always" alt="always show" title="The reddit button will always be shown for this post"';
	} else if($_poststate == 'dnr') {
		$ret .= 'state:dnr" alt="never show" title="The reddit button will never be shown for this post"';
	} else {
		$ret .= 'state:auto" alt="auto" title="This post is waiting to be redd"';
	}
	$ret .= ' width="16" height="16" /></td>';
	$ret .= '<td align="center"><b>|</b></td>';
	$ret .= '<td><a href="'._rb_get_update_uri($post->ID, 'auto').'"><img src="'.get_bloginfo('url').'/wp-admin/options-general.php?_rb_img=action:auto" alt="Reset" title="Reset this post\'s redd state" width="16" height="16" /></a></td>';
	if($_poststate != 'always' && $_poststate != 'redd') {
		$ret .= '<td><a href="'._rb_get_update_uri($post->ID, 'always').'"><img src="'.get_bloginfo('url').'/wp-admin/options-general.php?_rb_img=action:always" alt="Always show" title="Always show the reddit button for this post" width="16" height="16" /></a></td>';
	}
	if($_poststate != 'dnr') {
		$ret .= '<td><a href="'._rb_get_update_uri($post->ID, 'dnr').'"><img src="'.get_bloginfo('url').'/wp-admin/options-general.php?_rb_img=action:dnr" alt="Never show" title="Never show the reddit button for this post" width="16" height="16" /></a></td>';
	}
	$ret .= '</tr></table>';
	return $ret;
}

function _rb_get_update_uri($postID = 0, $action = '') {
	return get_bloginfo('url').
		"/wp-admin/options-general.php?page=reddit-button.php".
		"&_rb_updatepost=$postID".
		"&_rb_action=$action";
}

function _rb_update_post_meta($postID, $value) {
	if(!update_post_meta($postID, '_rb_reddit', $value))
		add_post_meta($postID, '_rb_reddit', $value, true);
}

if(!empty($_GET['_rb_img'])) {
	$_img = $_GET['_rb_img'];

	if($_img == 'icon:alien') {
		header("Content-Type: image/gif");
		echo base64_decode("R0lGODlhEgASAOZxAMrKyujo6IKCgoqKin19fXR0dHZ2doODg6WlpaSkpFhYWIeHh66urm9vb6ampsfHx8nJyaurq4aGhvf395CQkO3t7YuLi3x8fJSUlMzMzH9/f1dXV4WFhYiIiFZWVv+JXZ+fn//OveDg4LOzs/8hAKCgoP/m3f+CWXNzc09PT5ubm/7+/kBAQLy8vJ6env+JZJmZmZycnP+CU//6+VNTU5aWljMzM1tbW9LS0ufn56qqqpGRkXh4eJeXl5iYmHd3d//+/tvb3GZmZm1tbf/l3Gpqav++p46OjpOTk4SEhHt7e3BwcC8vL/+piGFhYZqamsPDwzg4OP/RwMbGxl1dXX5+fqGhoXl5eTc3N1paWunp6Wlpaf/SwcjIyEdHR/39/XV1ddzc3Kenp//Uw/9nL9bW1mJiYv8dAMDAwF9fX2RkZP+NZO7u7lRUVP+/p/+JYv///zP/MwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAHEALAAAAAASABIAAAfYgHGCg4QcDoIbSoSLggM8KgZRFQQMjIQNgwY1aQ+WgwWEVgCeSAdUKRxPllUNF0MGETgBARAuKEsHRTtxSTEFWUFwwsPCaCw6HQkXAgUrcG5vRMJjJ1xwAWYCGHFMInAzZCRNwjJnH8IJWIJqwkBGaybCUi8hwgAWgj8ZxPzDPSCDFLCBM8FHFy05RiAQVkbBog1T4ADQcAXMgTBwxNCwJOQGAggZADyA4YWAJydtSnQYsICCAxsDLCXwgIHfFwJbWjASgIACvwkSIuBbJIHBEX4VNEBZMCgQADs=");
		exit;
	}

	header("Content-Type: image/png");

	if($_img == 'state:redd') {
		echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKfSURBVDjLpZPrS1NhHMf9O3bOdmwDCWREIYKEUHsVJBI7mg3FvCxL09290jZj2EyLMnJexkgpLbPUanNOberU5taUMnHZUULMvelCtWF0sW/n7MVMEiN64AsPD8/n83uucQDi/id/DBT4Dolypw/qsz0pTMbj/WHpiDgsdSUyUmeiPt2+V7SrIM+bSss8ySGdR4abQQv6lrui6VxsRonrGCS9VEjSQ9E7CtiqdOZ4UuTqnBHO1X7YXl6Daa4yGq7vWO1D40wVDtj4kWQbn94myPGkCDPdSesczE2sCZShwl8CzcwZ6NiUs6n2nYX99T1cnKqA2EKui6+TwphA5k4yqMayopU5mANV3lNQTBdCMVUA9VQh3GuDMHiVcLCS3J4jSLhCGmKCjBEx0xlshjXYhApfMZRP5CyYD+UkG08+xt+4wLVQZA1tzxthm2tEfD3JxARH7QkbD1ZuozaggdZbxK5kAIsf5qGaKMTY2lAU/rH5HW3PLsEwUYy+YCcERmIjJpDcpzb6l7th9KtQ69fi09ePUej9l7cx2DJbD7UrG3r3afQHOyCo+V3QQzE35pvQvnAZukk5zL5qRL59jsKbPzdheXoBZc4saFhBS6AO7V4zqCpiawuptwQG+UAa7Ct3UT0hh9p9EnXT5Vh6t4C22QaUDh6HwnECOmcO7K+6kW49DKqS2DrEZCtfuI+9GrNHg4fMHVSO5kE7nAPVkAxKBxcOzsajpS4Yh4ohUPPWKTUh3PaQEptIOr6BiJjcZXCwktaAGfrRIpwblqOV3YKdhfXOIvBLeREWpnd8ynsaSJoyESFphwTtfjN6X1jRO2+FxWtCWksqBApeiFIR9K6fiTpPiigDoadqCEag5YUFKl6Yrciw0VOlhOivv/Ff8wtn0KzlebrUYwAAAABJRU5ErkJggg==");
	} else if($_img == 'state:auto') {
		echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAL5SURBVDjLjZNbSJNhGMd3KdRlN90E1Z0URVfShYKKic7ChFRKyHRNp3lAgq1ppeam5nFuno+JJ8ylzNMSRec3LdFNm7rKPuemznm2nMfl/n3fBw6tiC5+Lw8Pz//H8148LH1VvBNFDIWCgqSwUhxNlETiQ94D9IluHymEbtbGuGtk5eOLClnIuZjcwLNOAFg0LGqYmOsSwzwkw4q2Amu6GqxOVMMyUoZFVSFM73NBtokxWSsAkRcKOd8VlIBwCKZrn00cC5bHyijKsTRcgoUBGea6c0C2ZkDfkAxtWQJUWSGMIC/k/IRDoP5kdB5T9+NbVymm6pMwIgtDn/gOqLVBrY0q7mUUh11AadQVNKQGoFSaDmldl7NDQD99M4fdY/MHWNu2Ye/Qjn2bHes7PzFl3sOocReGtQOQqwdo16xC2mnoPg47BDTK6d13yukd+xw1bN0/gnnLBv3SPmapoPrrDxQpTfaCDoP8ZPiUgKZV+92lTbtFfiS3Ydo4ZMKd4+soVBpnJB2zLr+H/xAcUz+0MqgxWjFq2Ias26j628w/BY1Dy8Pj81aMUQJJ++zgfwvq1cs3mwmT6U1zO7KyslFZWYnUtAwkl/ctCKUK38TERJLupaWlbfB4vKeurq5nHOHaQUtrE7Foz5WWIj8/HxaLBSRJYmBgAOmvc5H4Kg/6z1+O6B5BEMwMm83OZMLVqiVlj24d8s5eCIVCaHQ6iMXp8PPzA4fDgUQigUAgYGpfNtseFBTUSUsSEhK2WA09Oue6QTP6pzchysyBSCRiBDu7e7jl7Y3e3l5oNBqoVCq0tLTA3dMLvCTZDVqQkpKyx9zCpLIYxLAa6ZIKxMbGMQK+8Dk8PDzh5eUFf39/Brr2cHfHwwD3TVrA5XI3Tx3TiCIDnNBgFOTnQP62CXK5HEVFRYiPjwefz2dqutdUV2PLzs7epL6oZ508Z21xBNny8t5u8F1fcDmP8CQqEtEUSfev7r8IvGSO5kXYoqJ4h+Hh4VYfHx+Dm5vb9V9HN9N1j9T0nAAAAABJRU5ErkJggg==");
	} else if($_img == 'state:always' || $_img == 'action:always') {
		echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJvSURBVDjLpZPrS5NhGIf9W7YvBYOkhlkoqCklWChv2WyKik7blnNris72bi6dus0DLZ0TDxW1odtopDs4D8MDZuLU0kXq61CijSIIasOvv94VTUfLiB74fXngup7nvrnvJABJ/5PfLnTTdcwOj4RsdYmo5glBWP6iOtzwvIKSWstI0Wgx80SBblpKtE9KQs/We7EaWoT/8wbWP61gMmCH0lMDvokT4j25TiQU/ITFkek9Ow6+7WH2gwsmahCPdwyw75uw9HEO2gUZSkfyI9zBPCJOoJ2SMmg46N61YO/rNoa39Xi41oFuXysMfh36/Fp0b7bAfWAH6RGi0HglWNCbzYgJaFjRv6zGuy+b9It96N3SQvNKiV9HvSaDfFEIxXItnPs23BzJQd6DDEVM0OKsoVwBG/1VMzpXVWhbkUM2K4oJBDYuGmbKIJ0qxsAbHfRLzbjcnUbFBIpx/qH3vQv9b3U03IQ/HfFkERTzfFj8w8jSpR7GBE123uFEYAzaDRIqX/2JAtJbDat/COkd7CNBva2cMvq0MGxp0PRSCPF8BXjWG3FgNHc9XPT71Ojy3sMFdfJRCeKxEsVtKwFHwALZfCUk3tIfNR8XiJwc1LmL4dg141JPKtj3WUdNFJqLGFVPC4OkR4BxajTWsChY64wmCnMxsWPCHcutKBxMVp5mxA1S+aMComToaqTRUQknLTH62kHOVEE+VQnjahscNCy0cMBWsSI0TCQcZc5ALkEYckL5A5noWSBhfm2AecMAjbcRWV0pUTh0HE64TNf0mczcnnQyu/MilaFJCae1nw2fbz1DnVOxyGTlKeZft/Ff8x1BRssfACjTwQAAAABJRU5ErkJggg==");
	} else if($_img == 'state:dnr' || $_img == 'action:dnr') {
		echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJdSURBVDjLpZP7S1NhGMf9W7YfogSJboSEUVCY8zJ31trcps6zTI9bLGJpjp1hmkGNxVz4Q6ildtXKXzJNbJRaRmrXoeWx8tJOTWptnrNryre5YCYuI3rh+8vL+/m8PA/PkwIg5X+y5mJWrxfOUBXm91QZM6UluUmthntHqplxUml2lciF6wrmdHriI0Wx3xw2hAediLwZRWRkCPzdDswaSvGqkGCfq8VEUsEyPF1O8Qu3O7A09RbRvjuIttsRbT6HHzebsDjcB4/JgFFlNv9MnkmsEszodIIY7Oaut2OJcSF68Qx8dgv8tmqEL1gQaaARtp5A+N4NzB0lMXxon/uxbI8gIYjB9HytGYuusfiPIQcN71kjgnW6VeFOkgh3XcHLvAwMSDPohOADdYQJdF1FtLMZPmslvhZJk2ahkgRvq4HHUoWHRDqTEDDl2mDkfheiDgt8pw340/EocuClCuFvboQzb0cwIZgki4KhzlaE6w0InipbVzBfqoK/qRH94i0rgokSFeO11iBkp8EdV8cfJo0yD75aE2ZNRvSJ0lZKcBXLaUYmQrCzDT6tDN5SyRqYlWeDLZAg0H4JQ+Jt6M3atNLE10VSwQsN4Z6r0CBwqzXesHmV+BeoyAUri8EyMfi2FowXS5dhd7doo2DVII0V5BAjigP89GEVAtda8b2ehodU4rNaAW+dGfzlFkyo89GTlcrHYCLpKD+V7yeeHNzLjkp24Uu1Ed6G8/F8qjqGRzlbl2H2dzjpMg1KdwsHxOlmJ7GTeZC/nesXbeZ6c9OYnuxUc3fmBuFft/Ff8xMd0s65SXIb/gAAAABJRU5ErkJggg==");
	} else if($_img == 'action:auto') {
		echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADpSURBVCjPY/jPgB8y0EmBHXdWaeu7ef9rHuaY50jU3J33v/VdVqkdN1SBEZtP18T/L/7f/X/wf+O96kM3f9z9f+T/xP8+XUZsYAWGfsUfrr6L2Ob9J/X/pP+V/1P/e/+J2LbiYfEHQz+ICV1N3yen+3PZf977/9z/Q//X/rf/7M81Ob3pu1EXWIFuZvr7aSVBOx1/uf0PBEK3/46/gnZOK0l/r5sJVqCp6Xu99/2qt+v+T/9f+L8CSK77v+pt73vf65qaYAVqzPYGXvdTvmR/z/4ZHhfunP0p+3vKF6/79gZqzPQLSYoUAABKPQ+kpVV/igAAAABJRU5ErkJggg==");
	}

	exit;
}

?>
