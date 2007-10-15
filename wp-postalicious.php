<?php
/*
Plugin Name: Postalicious
Plugin URI: http://neop.gbtopia.com/?p=108
Description: Automatically create posts with your del.icio.us bookmarks.
Version: 1.5
Author: Pablo Gomez
Author URI: http://neop.gbtopia.com
*/

function neop_del_options() {
	global $wpdb, $wp_db_version, $utw, $STagging;
	$numusers = $wpdb->query("SELECT $wpdb->users.ID, $wpdb->users.display_name FROM $wpdb->users,$wpdb->usermeta WHERE $wpdb->users.ID = $wpdb->usermeta.user_id && $wpdb->usermeta.meta_key = 'wp_user_level' && $wpdb->usermeta.meta_value > 1 ORDER BY $wpdb->users.display_name");
	$userids = $wpdb->get_col(NULL,0);
	$userdisplayn = $wpdb->get_col(NULL,1);
	if($wp_db_version >= 6124) {
		$categories = get_categories(array('hide_empty' => false));
		$numcats = count($categories);
	} else {
		$numcats = $wpdb->query("SELECT cat_ID, cat_name FROM $wpdb->categories ORDER BY cat_name");
		$catids = $wpdb->get_col(NULL,0);
		$catdnames = $wpdb->get_col(NULL,1);
	}
	neop_del_update(); // Check if Postalicious has been udpated since last run.
	if(isset($_POST['nd_daily_activate'])) {
		if ( $wp_db_version < 4509 ) {
?>
		<div id="message" class="updated fade"><p><strong>
		Automatic daily updates activation failed. Sorry, automatic updating is only available with Wordpress 2.1
		</strong></p></div>
<?php
		} else {
			// Verify login
			ini_set('user_agent', 'Postalicious v1.0 (http://neop.gbtopia.com/?p=108)');
			$vldelusername = get_option('nd_delusername'); $vldelpassword = get_option('nd_delpassword');
			if($vldelusername && $vldelusername != '' && $vldelpassword && $vldelpassword != '') {
				$apiurl = "https://$vldelusername:$vldelpassword@api.del.icio.us/v1/posts/update";
				
				//  Try to use CURL, but use file_get_contents if CURL is not installed.
				$actsuccessful = 'no';
				if(function_exists('curl_init')) {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_USERAGENT, 'Postalicious v1.0 (http://neop.gbtopia.com/?p=108)');
					curl_setopt ($ch, CURLOPT_URL, $apiurl);
					curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
					$file = curl_exec($ch);
					if(curl_getinfo($ch, CURLINFO_HTTP_CODE) == 401) { ?>
						<div id="message" class="updated fade"><p><strong>
						Automatic daily updates activation failed. Incorrect del.icio.us login/password.
						</strong></p></div>
					<?php } else if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {  ?>
						<div id="message" class="updated fade"><p><strong>
						Automatic daily updates activation failed. Could not contact del.icio.us to verify password.
						</strong></p></div>
					<?php } else $actsuccessful = 'yes';
					curl_close($ch);
				} else  {
					$file = file_get_contents($apiurl);
					if(!$file) {
						if (strstr($http_response_header[0], '401')) { ?>
							<div id="message" class="updated fade"><p><strong>
							Automatic daily updates activation failed. Incorrect del.icio.us login/password.
							</strong></p></div>
						<?php } else { ?>
							<div id="message" class="updated fade"><p><strong>
							Automatic daily updates activation failed. Could not contact del.icio.us to verify password. 
							</strong></p></div>
						<?php } 
					} else $actsuccessful = 'yes';
				}
				if($actsuccessful == 'yes') {
					// Add cron
					
					$time = time();
					// Get current GMT time plus 1 day
					$ttime_gmt = $time - date('Z', $time) + 86400;
					// Get timestamp for tomorrow at 0:30:00
					$crontime = mktime(0,30,0,date('n',$ttime_gmt),date('j',$ttime_gmt),date('Y',$ttime_gmt));
					// Make crontime GMT
					$crontime += date('Z', $time);
					
					wp_schedule_event($crontime, 'daily', 'nd_daily_update');
					update_option('nd_dailyupdates','yes');
				}
			} else { ?>
				<div id="message" class="updated fade"><p><strong>
				Automatic daily updates activation failed. No del.icio.us login/password set up.
				</strong></p></div>
			<?php } 
		}
	}

	if(isset($_POST['nd_daily_deactivate'])) {
		// Remove cron job
		wp_clear_scheduled_hook('nd_daily_update');
		update_option('nd_dailyupdates','no');
	}
	
	if(isset($_POST['nd_update'])) {
		$updatemessage = neop_del_post_new(); ?>
		<div id="message" class="updated fade"><p><strong>
		<?php echo $updatemessage; ?>
		</strong></p></div>
<?php }
	
	if (isset($_POST['info_update'])) { 
		$message = '';
		$newusername = stripslashes($_POST['nd_delusername']);
		$newpassword = stripslashes($_POST['nd_delpassword']);
		if( get_option('nd_dailyupdates') == 'yes' && ($newusername != get_option('nd_delusername') || ($_POST['nd_passchanged'] == 'yes' && $newpassword != get_option('nd_delpassword')))) {
			// Verify new login
			$apiurl = "https://$newusername:$newpassword@api.del.icio.us/v1/posts/update";
			$actsuccessful = 'no';
				if(function_exists('curl_init')) {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_USERAGENT, 'Postalicious v1.0 (http://neop.gbtopia.com/?p=108)');
					curl_setopt ($ch, CURLOPT_URL, $apiurl);
					curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
					$file = curl_exec($ch);
					if(curl_getinfo($ch, CURLINFO_HTTP_CODE) == 401) $message .= 'The new del.icio.us username/password is incorrect. Daily updates have been disabled.';
					else if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) $message .= 'Unable to contact del.icio.us to verify new username/password. Daily updates have been disabled.';
					else $actsuccessful = 'yes';
					curl_close($ch);
				} else  {
					$file = file_get_contents($apiurl);
					if(!$file) {
						if (strstr($http_response_header[0], '401')) $message .= 'The new del.icio.us username/password is incorrect. Daily updates have been disabled.';
						else $message .= 'Unable to contact del.icio.us to verify new username/password. Daily updates have been disabled.'; 
					} else $actsuccessful = 'yes';
				}
				if($actsuccessful != 'yes') {
 					// Remove cron
 					update_option('nd_dailyupdates','no');
					wp_clear_scheduled_hook('nd_daily_update');
				}
		}
		update_option('nd_delusername',$newusername);
		if($_POST['nd_passchanged'] == 'yes') update_option('nd_delpassword',$newpassword);
		update_option('nd_idforposts',$_POST['nd_idforposts']);
		
		$catidlist = '';
		for($i=0;$i<$numcats;$i++) {
			if($wp_db_version >= 6124) $currentcat = $categories[$i]->cat_ID;
			else $currentcat = $catids[$i];
			if($_POST["nd_postincat_$currentcat"]) {
				if($catidlist == '') $catidlist .= $currentcat;
				else $catidlist .= ",$currentcat";
			}
		}
		update_option('nd_catforposts',$catidlist);
		
		if($_POST['nd_allowcomments']) update_option('nd_allowcomments','open');
		else update_option('nd_allowcomments','closed');
		if($_POST['nd_allowpings']) update_option('nd_allowpings','open');
		else update_option('nd_allowpings','closed');
		$nd_mincount = $_POST['nd_mincount'];
		if($nd_mincount < 1) {
			if($message != '') $message .= '<br />';
			$message .= '"Minimum post count" should be a number greater than 0.';
		}
		else update_option('nd_mincount',$nd_mincount);
		update_option('nd_poststatus',$_POST['nd_poststatus']);
		update_option('nd_publishbehaviour',$_POST['nd_publishbehaviour']);
		update_option('nd_tagfilters',stripslashes($_POST['nd_tagfilters']));
		update_option('nd_tagfilters2',stripslashes($_POST['nd_tagfilters2']));
		if($_POST['nd_allowprivate']) update_option('nd_allowprivate','yes');
		else update_option('nd_allowprivate','no');
		update_option('nd_datetemplate',stripslashes($_POST['nd_datetemplate']));
		update_option('nd_titlesingle',stripslashes($_POST['nd_titlesingle']));
		update_option('nd_titledouble',stripslashes($_POST['nd_titledouble']));
		update_option('nd_linktemplate',stripslashes($_POST['nd_linktemplate']));
		update_option('nd_tagtemplate',stripslashes($_POST['nd_tagtemplate']));
		update_option('nd_posttsingle',stripslashes($_POST['nd_posttsingle']));
		update_option('nd_posttdouble',stripslashes($_POST['nd_posttdouble']));
		
		if($_POST['nd_tagging_enabled']) update_option('nd_tagging_enabled','yes');
		else update_option('nd_tagging_enabled','no');
		if($_POST['nd_use_del_tags']) update_option('nd_use_del_tags','yes');
		else update_option('nd_use_del_tags','no');
		if(isset($_POST['nd_post_tags'])) update_option('nd_post_tags',stripslashes($_POST['nd_post_tags']));
		
		if($message == '') $message = 'Settings were updated successfully.';
?>
		<div id="message" class="updated fade"><p><strong>
		<?php echo $message; ?>
		</strong></p></div>
<?php }
	if(!($nd_dailyupdates = get_option('nd_dailyupdates'))) $nd_dailyupdates = 'no';
	if(!($nd_delusername = get_option('nd_delusername'))) $nd_delusername = '';
	if(!($nd_delpassword = get_option('nd_delpassword'))) $nd_delpassword = '';
	if(!($selecteduser = get_option('nd_idforposts'))) $selecteduser = 1;
	if(!($selectedcatlist = get_option('nd_catforposts'))) $selectedcatlist = get_option('default_category');
	if(!($nd_allowcomments = get_option('nd_allowcomments'))) $nd_allowcomments = get_option('default_comment_status');
	if(!($nd_allowpings = get_option('nd_allowpings'))) $nd_allowpings = get_option('default_comment_status');
	if(!($minposts = get_option('nd_mincount'))) $minposts = 5;
	if(!($nd_poststatus = get_option('nd_poststatus'))) $nd_poststatus = 'publish';
	if(!($nd_publishbehaviour = get_option('nd_publishbehaviour'))) $nd_publishbehaviour = 0;
	if(!($nd_tagfilters = get_option('nd_tagfilters'))) $nd_tagfilters = '';
	if(!($nd_tagfilters2 = get_option('nd_tagfilters2'))) $nd_tagfilters2 = '';
	if(!($nd_allowprivate = get_option('nd_allowprivate'))) $nd_allowprivate = 'no';
	
	if(!($nd_datetemplate = get_option('nd_datetemplate'))) $nd_datetemplate = 'F jS';
	if(!($nd_titlesingle = get_option('nd_titlesingle'))) $nd_titlesingle = 'My del.icio.us bookmarks for %date%';
	if(!($nd_titledouble = get_option('nd_titledouble'))) $nd_titledouble = 'My del.icio.us bookmarks for %datestart% through %dateend%';
	if(!($nd_linktemplate = get_option('nd_linktemplate'))) $nd_linktemplate = '<li><a href="%href%">%description%</a> - %extended%</li>';
	if(!($nd_tagtemplate = get_option('nd_tagtemplate'))) $nd_tagtemplate = '<a href="%tagurl%">%tagname%</a> ';
	if(!($nd_posttsingle = get_option('nd_posttsingle'))) $nd_posttsingle = "<p>These are my links for %date%:</p>\n<ul>\n%bookmarks%\n</ul>";
	if(!($nd_posttdouble = get_option('nd_posttdouble'))) $nd_posttdouble = "<p>These are my links for %datestart% through %dateend%:</p>\n<ul>\n%bookmarks%\n</ul>";
	
	if(!($nd_tagging_enabled = get_option('nd_tagging_enabled'))) $nd_tagging_enabled = 'no';
	if(!($nd_use_del_tags = get_option('nd_use_del_tags'))) $nd_use_del_tags = 'no';
	if(!($nd_post_tags = get_option('nd_post_tags'))) $nd_post_tags = '';
	
	$selectedcatlist = ','.$selectedcatlist.',';
	$nd_tagfilters = htmlentities($nd_tagfilters);
	$nd_tagfilters2 = htmlentities($nd_tagfilters2);
	$nd_titlesingle = htmlentities($nd_titlesingle);
	$nd_titledouble = htmlentities($nd_titledouble);
	$nd_linktemplate = htmlentities($nd_linktemplate);
	$nd_tagtemplate = htmlentities($nd_tagtemplate);
	$nd_posttsingle = htmlentities($nd_posttsingle);
	$nd_posttdouble = htmlentities($nd_posttdouble);
	$nd_post_tags = htmlentities($nd_post_tags);
	
?>
	<div class=wrap>
		<div style="background: <?php if($nd_dailyupdates == 'yes') echo "#cf9"; else echo "#f99"; ?> 1em; border: 1px solid <?php if($nd_dailyupdates == 'yes') echo "#green"; else echo "#red"; ?>; margin:1em; padding:1em;">
		<table width="100%"><tr><td>
<?php
		$nd_lastrun = get_option('nd_lastrun');
		if($nd_dailyupdates == 'yes') {
			echo "Automatic daily updates are active.";
			if($nd_lastrun) echo '<b>Last update:</b> ' . mysql2date('F j, Y G:i',date('Y-m-d H:i:s',$nd_lastrun));
			echo '</td><td align="right"><form method="post" style="display:inline;"><input type="submit" name="nd_update" value="Update Now"></form>&nbsp;&nbsp;<form method="post" style="display:inline;"><input type="submit" name="nd_daily_deactivate" value="Deactivate Daily Updates"></form>';
		} else {
			echo "Automatic daily updates are not activated.";
			echo '</td><td align="right"><form method="post" style="display:inline;"><input type="submit" name="nd_update" value="Update Now"></form>&nbsp;&nbsp;<form method="post" style="display:inline;"><input type="submit" name="nd_daily_activate" value="Activate Daily Updates"></form>';
		}
?>
		</td></tr></table>
		</div>
		<br />
		<form method="post">
			<h2>Postalicious options</h2>
			<div class="submit"><input type="submit" name="info_update" value="Update Options &raquo;" /></div>
			<fieldset class="options">
				<legend>del.icio.us account</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform"> 
				<tr valign="top"> 
				<th>Username:</th> 
				<td>
				<input name="nd_delusername" type="text" id="nd_delusername" value="<?php echo $nd_delusername; ?>" size="15" />
				</td></tr>
				<tr valign="top"> 
				<th>Password:</th> 
				<td>
				<input name="nd_delpassword" type="password" id="nd_delpassword" onfocus="this.value=''; document.getElementById('nd_passchanged').value='yes'" value="<?php $numchars = strlen($nd_delpassword); for($i=0;$i<$numchars;$i++) echo '*'; ?>" size="15" />
				<input type="hidden" name="nd_passchanged" id="nd_passchanged" value="no">
				</td></tr>
				</table>
			</fieldset>
			<fieldset class="options">
				<legend>Post settings</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform"> 
				<tr valign="top"> 
				<th scope="row">Post under user:</th>
				<td><select name="nd_idforposts"><?php
				for($i=0;$i<$numusers;$i++) {
					$currentid = $userids[$i];
					if($currentid == $selecteduser)
						echo "<option value='$currentid' selected>{$userdisplayn[$i]}</option>";
					else
						echo "<option value='$currentid'>{$userdisplayn[$i]}</option>";
				}
				?></select></td>
				<tr>
				<tr valign="top"> 
				<th scope="row">Post in categories:</th>
				<td><?php
				if($wp_db_version >= 6124) { // 2.3 or later
					for($i=0;$i<$numcats;$i++) {
						$currentcat = $categories[$i]->cat_ID;
						if(strpos($selectedcatlist,','.$currentcat.',') !== FALSE)
							echo "<input name='nd_postincat_$currentcat' type='checkbox' id='nd_postincat_$currentcat' checked='checked' />
				{$categories[$i]->cat_name}   </label>";
						else
							echo "<input name='nd_postincat_$currentcat' type='checkbox' id='nd_postincat_$currentcat' />
				{$categories[$i]->cat_name}   </label>";
					}
				} else {
					for($i=0;$i<$numcats;$i++) {
						$currentcat = $catids[$i];
						if(strpos($selectedcatlist,','.$currentcat.',') !== FALSE)
							echo "<input name='nd_postincat_$currentcat' type='checkbox' id='nd_postincat_$currentcat' checked='checked' />
				{$catdnames[$i]}   </label>";
						else
							echo "<input name='nd_postincat_$currentcat' type='checkbox' id='nd_postincat_$currentcat' />
				{$catdnames[$i]}   </label>";
					}
				}
				?></td>
				</tr>
				<tr>
				<th scope="row">Discussion:</th>
				<td>
				<label for="nd_allowcomments">
				<input name="nd_allowcomments" type="checkbox" id="nd_allowcomments" <?php if($nd_allowcomments == 'open') echo 'checked="checked"' ?> />
				Allow comments</label><br />
				<label for="nd_allowpings"><input name="nd_allowpings" type="checkbox" id="nd_allowpings" <?php if($nd_allowpings == 'open') echo 'checked="checked"' ?>  />
				Allow pings</label>
				</td>
				</tr>
				</table>
			</fieldset>
			<fieldset class="options">
				<legend>Options</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform"> 
				<tr valign="top"> 
				<th>Minimum number of bookmarks per post:</th> 
				<td>
				<input name="nd_mincount" type="text" id="nd_mincount" value="<?php echo $minposts; ?>" size="3" />
				</td> 
				</tr> 
				<tr>
				<th scope="row">Post status:</th>
				<td>
				<label><input name="nd_poststatus" type="radio" value="publish" <?php if($nd_poststatus == 'publish') echo 'checked="checked"' ?> />
				Publish posts</label><br />
				<label><input name="nd_poststatus" type="radio" value="draft" <?php if($nd_poststatus == 'draft') echo 'checked="checked"' ?>  />
				Post as drafts</label>
				</td>
				</tr>
				<tr>
				<th>If a draft created by Postalicious is published by a blog author:</th>
				<td>
				<label><input name="nd_publishbehaviour" type="radio" value="0" <?php if($nd_publishbehaviour == 0) echo 'checked="checked"' ?> />
				Create a new post</label><br />
				<label><input name="nd_publishbehaviour" type="radio" value="1" <?php if($nd_publishbehaviour == 1) echo 'checked="checked"' ?>  />
				Edit the published post</label>
				</td></tr>
				<tr valign="top"> 
				<th>Only post bookmarks that have any of the following tags:</th> 
				<td>
				<input name="nd_tagfilters" type="text" id="nd_tagfilters" value="<?php echo $nd_tagfilters; ?>" size="50" /><br />
				(Comma separated list)
				</td></tr>
				<tr valign="top"> 
				<th>Do not post bookmarks that have any of the following tags:</th> 
				<td>
				<input name="nd_tagfilters2" type="text" id="nd_tagfilters2" value="<?php echo $nd_tagfilters2; ?>" size="50" /><br />
				(Comma separated list)
				</td></tr>
				<tr>
				<th scope="row">Private bookmarks:</th>
				<td>
				<label for="nd_allowprivate">
				<input name="nd_allowprivate" type="checkbox" id="nd_allowprivate" <?php if($nd_allowprivate == 'yes') echo 'checked="checked"' ?> />
				Post private bookmarks.</label>
				</td>
				</tr>
				</table>
			</fieldset>
<?php if($utw || $STagging || $wp_db_version >= 6124) { ?>
			<fieldset class="options">
			 <?php if( $wp_db_version >= 6124 ) { ?>
			 	<legend>Tags</legend>
			 <?php } else if($utw) { ?>
				<legend>UltimateTagWarrior Integration</legend>
			<?php } else if($STagging) { ?>
				<legend>Simple Tagging Plugin Integration</legend>
			<?php } ?>
				<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform"> 
				<tr valign="top">
				<th>Enabled:</th>
				<td>
				<label><input name="nd_tagging_enabled" type="checkbox" id="nd_tagging_enabled" <?php if($nd_tagging_enabled == 'yes') echo 'checked="checked"' ?> />
				Use tags for new posts.</label>
				</td>
				</tr>
				<tr valign="top"> 
				<th>del.icio.us tags:</th> 
				<td>
				<label><input name="nd_use_del_tags" type="checkbox" id="nd_use_del_tags" <?php if($nd_use_del_tags == 'yes') echo 'checked="checked"' ?> />
				Use del.icio.us bookmark tags as tags for the post in which the bookmark appears.</label>
				</td>  
				</tr> 
				<tr valign="top"> 
				<th>Use these tags for all Postalicious posts:</th> 
				<td>
				<input name="nd_post_tags" type="text" id="nd_post_tags" value="<?php echo $nd_post_tags; ?>" size="25" /><br />
				(Comma separated list)
				</td> 
				</tr>
				</table>
			</fieldset>
<?php } ?>
			<fieldset class="options">
				<legend>Templates</legend>
				<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform"> 
				<tr valign="top"> 
				<th>Date format:</th> 
				<td>
				<input name="nd_datetemplate" type="text" id="nd_datetemplate" value="<?php echo $nd_datetemplate; ?>" size="10" /><br />
				<b>Output: </b><?php echo mysql2date($nd_datetemplate,date('Y-m-d H:i:s',time())); ?>. This date format will be used for all dates posted by Postalicious. See PHP's <a href="http://php.net/date">date</a> documentation for date formatting.
				</td></tr>
				<tr valign="top"> 
				<th>Post title (single date):</th> 
				<td>
				<input name="nd_titlesingle" type="text" id="nd_titlesingle" value="<?php echo $nd_titlesingle; ?>" size="75" /><br />
				%date% will be replaced by the date of the del.icio.us bookmarks in the post.
				</td></tr>
				<tr valign="top"> 
				<th>Post title (two dates):</th> 
				<td>
				<input name="nd_titledouble" type="text" id="nd_titledouble" value="<?php echo $nd_titledouble; ?>" size="75" /><br />
				%datestart% and %dateend% will be replaced by the first and last days of the del.icio.us bookmarks in the post.
				</td></tr>
				<tr valign="top"> 
				<th>Bookmark:</th> 
				<td>
				<input name="nd_linktemplate" type="text" id="nd_linktemplate" value="<?php echo $nd_linktemplate; ?>" size="75" /><br />
				The following will be replaced with the bookmark's info: %href% - url, %description% - description, %extended% - extended description and %tag% - tags
				</td></tr>
				<tr valign="top">
				<th>Tag:</th>
				<td>
				<input name="nd_tagtemplate" type="text" id="nd_tagtemplate" value="<?php echo $nd_tagtemplate; ?>" size="75" /><br />
				The following will be replaced with the tag's info: %tagname% - name of the tag, %tagurl% - url to your del.icio.us page
				</td></tr>
				</table>
			</fieldset>
			<fieldset class="options">
			<legend>Post template (single date)</legend>
			<p>This is the template for the body of the posts created by Postalicious with bookmarks for one day only. %bookmarks% should be placed where you want the list of bookmarks to be shown, each bookmark will have the format specified by the bookmark template. %date% will be replaced by the date of the del.icio.us bookmarks in the post.</p>
			<textarea name="nd_posttsingle" id="nd_posttsingle" style="width: 98%;" rows="8" cols="50"><?php echo $nd_posttsingle; ?></textarea>
			</fieldset>
			<fieldset class="options">
			<legend>Post template (two dates)</legend>
			<p>This is the template for the body of the posts created by Postalicious with bookmarks for a range of dates. %bookmarks% should be placed where you want the list of bookmarks to be shown, each bookmark will have the format specified by the bookmark template. %datestart% and %dateend% will be replaced by the first and last days of the del.icio.us bookmarks in the post.</p>
			<textarea name="nd_posttdouble" id="nd_posttdouble" style="width: 98%;" rows="8" cols="50"><?php echo $nd_posttdouble; ?></textarea>
			</fieldset>
			<div class="submit"><input type="submit" name="info_update" value="Update Options &raquo;" /></div>
		</form>
	</div>
<?php
}

function neop_del_add_options() {
	global $user_level;
	if($user_level >= 6) {
		if (function_exists('neop_del_options')) {
			add_options_page('Postalicious', 'Postalicious', 1, basename(__FILE__), 'neop_del_options');
		}
	}
}

function neop_del_update() {
	if(!($nd_version = get_option('nd_version'))) $nd_version = 120; // Because of a bug in 121, get_option('nd_version') will always be at least 150
	if($nd_version < 121) {
		if(get_option('nd_utw_enabled') == 'yes') {
			update_option('nd_tagging_enabled','yes');
			update_option('nd_use_del_tags','yes');
		}
		else update_option('nd_utw_enabled','no');
		if($nd_utw_tags = get_option('nd_utw_tags')) update_option('nd_post_tags',$nd_utw_tags);
		
		delete_option('nd_utw_enabled');
		delete_option('nd_utw_tags');
		
		$nd_version = 130;
	}
	if($nd_version < 150) {
		$nd_version = 150;
	}
	update_option('nd_version',$nd_version);
}

function neop_del_post_new() {
	neop_del_update(); // Check if Postalicious has been udpated since last run.
	// Build URL for del.icio.us API or exit if username/password have not been set.
	$username = urlencode(get_option('nd_delusername'));
	global $neop_del_username;
	$neop_del_username = $username;
	$password = urlencode(get_option('nd_delpassword'));
	if($username && $password)
		$apiurl = "https://$username:$password@api.del.icio.us/v1/posts/get?";
	else return "del.icio.us username/password not set up.";

	// We need a non-standard useragent
	ini_set('user_agent', 'Postalicious v1.0 (http://neop.gbtopia.com/?p=108');
	
	// Get current time
	$time = time();
	// Get current GMT time minus 1 day
	$ytime_gmt = $time - date('Z', $time) - 86400;
	// Get timestamp for yesterday at 0:00:00
	$updatetime = mktime(0,0,0,date('n',$ytime_gmt),date('j',$ytime_gmt),date('Y',$ytime_gmt));
	// Set the date for the first update.
	$currenttime = $updatetime;

	global $count;
	if(!($nd_unpublishedcount = get_option('nd_unpublishedcount'))) $nd_unpublishedcount = 0;
	$count = $nd_unpublishedcount;
	
	// Find out how many days since the last update
	$nd_lastupdate = get_option('nd_lastupdate');
	if($nd_lastupdate) {
		$daysbehind = ($updatetime - $nd_lastupdate)/86400;
	} else $daysbehind = 1;

	// Request XML from del.icio.us API and add it to $newposts
	global $newposts, $wpdb, $neop_del_linktemplate, $neop_del_tagtemplate, $nd_tagging_status, $nd_use_del_tags_status, $newtags, $neop_del_tagfilter, $neop_del_tagfilter2, $neop_del_allowprivate;
	if(!($neop_del_linktemplate = get_option('nd_linktemplate'))) $neop_del_linktemplate = '<li><a href="%href%">%description%</a> - %extended%</li>';
	if(!($neop_del_tagtemplate = get_option('nd_tagtemplate'))) $neop_del_tagtemplate = '<a href="%tagurl%">%tagname%</a> ';
	
	if($neop_del_tagfilter = get_option('nd_tagfilters')) $neop_del_tagfilter = ',' . str_replace(' ','',$neop_del_tagfilter) . ','; 
	else $neop_del_tagfilter = ',,';
	if($neop_del_tagfilter2 = get_option('nd_tagfilters2')) $neop_del_tagfilter2 = ',' . str_replace(' ','',$neop_del_tagfilter2) . ','; 
	else $neop_del_tagfilter2 = ',,';
	
	if(!($neop_del_allowprivate = get_option('nd_allowprivate'))) $neop_del_allowprivate = 'no';

	
	$unsuccessful = 'no';
	$newposts = '';
	$nd_tagging_status = get_option('nd_tagging_enabled');
	$nd_use_del_tags_status = get_option('nd_use_del_tags');
	$newtags = '';

	for($i=0;$i<$daysbehind;$i++) {
		if($i != 0) {
			$currenttime -= 86400;
			sleep(1);
		}
		$apidate = date("Y-m-d",$currenttime);
		$currenturl = $apiurl . "dt=$apidate";

		//  Try to use CURL, but use file_get_contents if CURL is not installed.
		if(function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, 'Postalicious v1.0 (http://neop.gbtopia.com/?p=108)');
			curl_setopt ($ch, CURLOPT_URL, $currenturl);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			$rawxml = curl_exec($ch);
			if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) { $unsuccessful = 'yes'; break; }
		} else {
			$rawxml = file_get_contents($currenturl);
			if(!$rawxml) { $unsuccessful = 'yes'; break; }
		}
		//$rawxml = utf8_decode($rawxml);
		$parser = xml_parser_create('ISO-8859-1');
		xml_set_element_handler($parser,'neop_del_start_handler','neop_del_end_handler');
		xml_parse($parser, $rawxml);
		xml_parser_free($parser);
	}

	if($unsuccessful == 'yes') {
		if($ch && function_exists('curl_init')) {
			$curlhttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curlerror = curl_error($ch);
			curl_close($ch);
			if($curlhttp == 401) return 'Incorrect del.icio.us login';
			else return 'Could not connect to del.icio.us.';
		}
		else if(strstr($http_response_header[0], '401'))
			return 'Incorrect del.icio.us login';
		else return 'Could not connect to del.icio.us';
	}
	else {
		if($daysbehind < 1) return "Postalicious has already been updated today.";
		if($ch && function_exists('curl_init')) curl_close($ch);
		if($newposts == '') {
			update_option('nd_lastrun',time());
			update_option('nd_lastupdate',$updatetime);
			return 'Update successful, no new posts found.';
		}
	}
	
	if(!($nd_mincount = get_option('nd_mincount'))) $nd_mincount = 5;
	if(!($nd_lastdraftid = get_option('nd_lastdraftid'))) $nd_lastdraftid = -1;

	if($nd_lastdraftid == -1) { // There is no draft.
		if($count < $nd_mincount) $action = 2; // There's not enough links, create a new draft.
		else $action = 3; // Publish the new links.
	} else { // There is already a draft.
		$draftstatus = $wpdb->get_var("SELECT post_status FROM $wpdb->posts WHERE ID = $nd_lastdraftid ORDER BY ID DESC LIMIT 1");
		if($draftstatus == 'draft' || $draftstatus == 'future') {
			if($count < $nd_mincount) $action = 1; // Not enough links added, just edit the draft.
			else $action = 4; // Add to draft and publish.
		} else if($draftstatus == 'publish') {
			if(!($nd_publishbehaviour = get_option('nd_publishbehaviour'))) $nd_publishbehaviour = 0;
			if($nd_publishbehaviour == 1) $action = 1; // It doesn't matter if it was published, just edit the draft.
			else {
				if($count < $nd_mincount) $action = 2; // There's not enough links, create a new draft.
				else {
					if(($count - $nd_unpublishedcount) < $nd_mincount) $action = 2; // There's not enough new links, so we'll make a new draft.
					else $action = 3; // The are enough new links, publish a new post.
				}
			}
		} else { // The post was deleted or is set to private.
			if(($count - $nd_unpublishedcount) < $nd_mincount) $action = 2; // There's not enough new links, so we'll make a new draft.
			else $action = 3; // The are enough new links, publish a new post.
		}
	}
	
	if(!($nd_idforposts = get_option('nd_idforposts'))) $nd_idforposts = 1;
	if(!($nd_catforposts = get_option('nd_catforposts'))) $nd_catforposts = get_option('default_category');
	if(!($nd_allowcomments = get_option('nd_allowcomments'))) $nd_allowcomments = get_option('default_comment_status');
	if(!($nd_allowpings = get_option('nd_allowpings'))) $nd_allowpings = get_option('default_comment_status');

	if(!($nd_datetemplate = get_option('nd_datetemplate'))) $nd_datetemplate = 'F jS';
	
	if($action == 1 || $action == 4) $posttype = 2;
	else {
		if($daysbehind > 1) $posttype = 2;
		else $postype = 1;
	}
	
	if($posttype == 2) { // Create the post title and body strings using the templates for two dates
		if(!($nd_titledouble = get_option('nd_titledouble'))) $nd_titledouble = 'My del.icio.us bookmarks for %datestart% through %dateend%';
		if(!($nd_posttdouble = get_option('nd_posttdouble'))) $nd_posttdouble = "<p>These are my links for %datestart% through %dateend%:</p>\n<ul>\n%bookmarks%\n</ul>";
		$posttitle = $nd_titledouble;
		$postbody = $nd_posttdouble;
		if($action == 1 || $action == 4) {
			$datestart = mysql2date($nd_datetemplate,date('Y-m-d H:i:s',get_option('nd_draftdate')));
			$postlinks = $newposts . get_option('nd_draftcontent');
		} else { 
			$datestart = mysql2date($nd_datetemplate,date('F j, Y G:i',$updatetime));
			$postlinks = $newposts;
		}
		$dateend = mysql2date($nd_datetemplate,date('Y-m-d H:i:s',$updatetime));
		$posttitle = str_replace("%datestart%",$datestart,$posttitle);
		$posttitle = str_replace("%dateend%",$dateend,$posttitle);
		$postbody = str_replace("%datestart%",$datestart,$postbody);
		$postbody = str_replace("%dateend%",$dateend,$postbody);
		$postbody = str_replace("%bookmarks%",$postlinks,$postbody);
	} else { // Create the post title and body strings using the single date templates
		if(!($nd_titlesingle = get_option('nd_titlesingle'))) $nd_titlesingle = 'My del.icio.us bookmarks for %date%';
		if(!($nd_posttsingle = get_option('nd_posttsingle'))) $nd_posttsingle = "<p>These are my links for %date%:</p>\n<ul>\n%bookmarks%\n</ul>";
		$posttitle = $nd_titlesingle;
		$postbody = $nd_posttsingle;
		$date = mysql2date($nd_datetemplate,date('Y-m-d H:i:s',$updatetime));
		$postlinks = $newposts;
		$posttitle = str_replace("%date%",$date,$posttitle);
		$postbody = str_replace("%date%",$date,$postbody);
		$postbody = str_replace("%bookmarks%",$postlinks,$postbody);
	}
	
	$postbody = $wpdb->escape($postbody);
	$posttitle = $wpdb->escape($posttitle);
	
	$categoryarray = explode(',',$nd_catforposts);
	if(!($nd_poststatus = get_option('nd_poststatus'))) $nd_poststatus = 'publish';
	
	if($nd_tagging_status == 'yes') {
		$post_tags = $wpdb->escape(get_option('nd_post_tags'));
		$post_tags = explode(',',$post_tags);
		$tags = $post_tags;
		
		if($nd_use_del_tags_status == 'yes') {
  	  		$newtags = $wpdb->escape($newtags);
  			if($action == 1 || $action == 4) $draft_tags = get_option('nd_drafttags') . " " . $newtags;
  			else $draft_tags = $newtags;
  			$draft_tags = trim($draft_tags);
  			$tags = array_merge(explode(' ',$draft_tags) ,$post_tags);
		}
	}
	
	switch($action) {
		case 1: // Add new links to the existing draft.
			$newid = wp_update_post(array('ID'=>$nd_lastdraftid,'post_title'=>$posttitle,'post_content'=>$postbody,'no_filter' => true));
			update_option('nd_draftcontent',$postlinks);
			if($nd_tagging_status == 'yes') update_option('nd_drafttags',$draft_tags);
			update_option('nd_unpublishedcount',$count);
			break;
		case 2: // Create a new draft.
			$newid = wp_insert_post(array('post_author'=>$nd_idforposts,'post_title'=>$posttitle,'post_content'=>$postbody,'post_status'=>'draft','comment_status'=>$nd_allowcomments,'ping_status'=>$nd_allowpings,'post_category'=>$categoryarray,'no_filter' => true));
			update_option('nd_lastdraftid',$newid);
			update_option('nd_draftdate',$updatetime);
			update_option('nd_draftcontent',$postlinks);
			if($nd_tagging_status == 'yes') update_option('nd_drafttags',$draft_tags);
			update_option('nd_unpublishedcount',$count - $nd_unpublishedcount);
			break;
		case 3: // Create a new post and publish it.
			$newid = wp_insert_post(array('post_author'=>$nd_idforposts,'post_title'=>$posttitle,'post_content'=>$postbody,'post_status'=>$nd_poststatus,'comment_status'=>$nd_allowcomments,'ping_status'=>$nd_allowpings,'post_category'=>$categoryarray,'no_filter' => true));
			update_option('nd_lastdraftid',-1);
			update_option('nd_draftdate','');
			update_option('nd_draftcontent','');
			if($nd_tagging_status == 'yes') update_option('nd_drafttags','');
			update_option('nd_unpublishedcount',0);
			break;
		case 4: // Add new link to the existing draft and publish the post.
			$newid = wp_update_post(array('ID'=>$nd_lastdraftid,'post_title'=>$posttitle,'post_content'=>$postbody,'post_status'=>$nd_poststatus,'no_filter' => true));
			update_option('nd_lastdraftid',-1);
			update_option('nd_draftdate','');
			update_option('nd_draftcontent','');
			if($nd_tagging_status == 'yes') update_option('nd_drafttags','');
			update_option('nd_unpublishedcount',0);
			break;
	}
	
	if( $nd_tagging_status == 'yes' && !empty($tags) ) {
		global $utw, $STagging, $wp_db_version;
		if($wp_db_version >= 6124) {
			wp_set_post_tags($newid, implode(',',$tags),true);
		}
		else if($utw) {
			$utw->SaveTags($newid, $tags);
			if (get_option('utw_include_categories_as_tags') == "yes") {
				$utw->SaveCategoriesAsTags($newid);
				$utw->ClearTagPostMeta($newid);
			}
		} else if($STagging) {
			if( $STagging && $action >= 3 ) {
				$stvals = "('$newid','".implode("'),('$newid','",$tags)."')";
				$wpdb->query("INSERT IGNORE INTO {$STagging->info['stptable']} VALUES $stvals");
			}
		}
	}

	update_option('nd_lastrun',time());
	update_option('nd_lastupdate',$updatetime);
	
	return "Update successful.";
}

function neop_del_start_handler($parser, $name, $attrs)
{
	global $newposts, $count, $neop_del_linktemplate, $neop_del_tagtemplate, $nd_tagging_status, $nd_use_del_tags_status, $neop_del_username, $newtags, $neop_del_tagfilter, $neop_del_tagfilter2, $neop_del_allowprivate;
   
	if($name == 'POST') {
		$filtered = 'no';
		$ftags = explode(' ',$attrs['TAG']);
		if($neop_del_tagfilter == ',,') $filtered = 'yes';
		else {
			foreach($ftags as $t) {
				if(strpos($neop_del_tagfilter,','.$t.',') !== FALSE) {
					$filtered = 'yes';
					break;
				}
			}
		} if($neop_del_tagfilter2 != ',,') {
			foreach($ftags as $t) {
				if(strpos($neop_del_tagfilter2,','.$t.',') !== FALSE) {
					$filtered = 'no';
					break;
				}
			}
		}
		if($neop_del_allowprivate == 'no' && $attrs['SHARED'] == 'no') $filtered = 'no';
		if($filtered == 'yes') {
			$count++;
			$currentlink = $neop_del_linktemplate;
			$currentlink = str_replace("%href%",$attrs['HREF'],$currentlink);
			$currentlink = str_replace("%description%",htmlentities($attrs['DESCRIPTION'],NULL,'ISO-8859-1'),$currentlink);
			$currentlink = str_replace("%extended%",htmlentities($attrs['EXTENDED'],NULL,'ISO-8859-1'),$currentlink);
			
			if($attrs['TAG'] != 'system:unfiled') {
				$nd_del_tagurl = 'http://del.icio.us/'.urlencode($neop_del_username).'/';
				$tags = explode(' ',$attrs['TAG']);
				foreach($tags as $t) {
					$currenttag = $neop_del_tagtemplate;
					$currenttag = str_replace("%tagurl%",$nd_del_tagurl.urlencode($t),$currenttag);
					$currenttag = str_replace("%tagname%",htmlentities($t,NULL,'ISO-8859-1'),$currenttag);
					$tag .= $currenttag . ' ';
				}
				if($nd_tagging_status == 'yes' && $nd_use_del_tags_status == 'yes') {
					if($newtags == '') $newtags .= $attrs['TAG'];
					else $newtags .= ' ' . $attrs['TAG'];
				}
			} else $tag = 'none';
			$currentlink = str_replace("%tag%",$tag,$currentlink);
			
			$currentlink .= "\n";
			$newposts .= $currentlink;
		}
	}
}

// We don't really need to do anything here, but php's xml parser needs this function
function neop_del_end_handler($parser, $name) { }

function neop_activate_del() {
	// default author
	update_option('nd_unpublishedcount',0);
}

function neop_deactivate_del() {
}

function neop_del_user_deleted($deletedid) {
	if(!($nd_idforposts = get_option('nd_idforposts'))) $nd_idforposts = 1;
	if($deletedid == $nd_idforposts) update_option('nd_idforposts',1);
	
}

function neop_del_category_deleted($deletedid) {
	if(!($nd_catforposts = get_option('nd_catforposts'))) $nd_catforposts = get_option('default_category');
	$catlist = ',' . $nd_catforposts . ',';
	if(strpos($nd_catforposts,','.$deletedid.',')) {
		$newcatlist = '';
		$catarray = explode($nd_catforposts);
		foreach($nd_catforposts as $ccat) {
			if($ccat != $deletedid) {
				if($newcatlist == '') $newcatlist = $ccat;
				else $newcatlist .= ',' . $ccat;
			}
		}
		if($newcatlist == '') $newcatlist = get_option('default_category');
		update_option('nd_catforposts',$newcatlist);
	}
	
}

add_action('admin_menu', 'neop_del_add_options');
add_action('delete_user', 'neop_del_user_deleted');
add_action('delete_category', 'neop_del_category_deleted');
add_action('nd_daily_update', 'neop_del_post_new');
//register_activation_hook(__FILE__, 'neop_activate_del');
//register_deactivation_hook(__FILE__, 'neop_deactivate_del');
?>