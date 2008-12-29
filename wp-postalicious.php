<?php
/*
Plugin Name: Postalicious
Plugin URI: http://neop.gbtopia.com/?p=108
Description: Automatically create posts with your delicious bookmarks.
Version: 2.5
Author: Pablo Gomez
Author URI: http://neop.gbtopia.com
*/

if (!function_exists('neop_pstlcs_options')) :
function neop_pstlcs_options() {
	if(isset($_POST['nd_clearlog'])) {
		update_option('nd_log','');
		update_option('nd_logcount',0);
		exit(0); // Only AJAX requests should get here so there's no reason to continue executing.
	}
	
	if(!class_exists('SimplePie'))
		exit('<div class="wrap"><h2>Postalicious needs the <a href="http://wordpress.org/extend/plugins/simplepie-core/">SimplePie Core</a> plugin to work, please install this plugin and try again. If you run into any problems, please check the FAQ found in the <a href="http://neop.gbtopia.com/?p=108">readme</a> file. If you\'re still lost after that feel free to contact me using <a href="http://neop.gbtopia.com/?page_id=26">this</a> form.</div>');
	global $wpdb, $wp_db_version, $utw, $STagging;
	$numusers = $wpdb->query("SELECT $wpdb->users.ID, $wpdb->users.display_name FROM $wpdb->users,$wpdb->usermeta WHERE $wpdb->users.ID = $wpdb->usermeta.user_id && $wpdb->usermeta.meta_key = '{$wpdb->prefix}user_level' && $wpdb->usermeta.meta_value > 1 ORDER BY $wpdb->users.display_name");
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
	neop_pstlcs_update(); // Check if Postalicious has been udpated since last run.
	
	$message = '';
	$savechanges = 0;
	if(isset($_POST['nd_settingschanged']) && $_POST['nd_settingschanged'] == '1') { // We should save the changes
		if(isset($_POST['nd_hourly_activate'])) $savechanges = 1;
		else if(isset($_POST['nd_hourly_deactivate'])) $savechanges = 2;
		else if(isset($_POST['nd_update'])) $savechanges = 3;
		else $savechanges = 4;
	}
	
	if ($savechanges != 0) { 
		if(isset($_POST['nd_service'])) update_option('nd_service',$_POST['nd_service']);
		update_option('nd_username',stripslashes($_POST['nd_username']));
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
			$message .= 'The minimum number of bookmarks per post should be a number greater than 0.';
		} else update_option('nd_mincount',$nd_mincount);
		
		update_option('nd_limit',$_POST['nd_limit']);
		
		$nd_maxcount = $_POST['nd_maxcount'];
		if($nd_maxcount < 1) {
			if($message != '') $message .= '<br />';
			$message .= 'The maximum number of bookmarks per post should be a number greater than 0.';
		} else if($nd_mincount > $nd_maxcount) {
			if($message != '') $message .= '<br />';
			$message .= 'The maximum number of bookmarks per post should be a greater or equal than the minimum.';
			update_option('nd_maxcount',$nd_mincount);
		} else update_option('nd_maxcount',$nd_maxcount);
		
		$nd_maxhours = $_POST['nd_maxhours'];
		if($nd_maxhours <= 0) {
			if($message != '') $message .= '<br />';
			$message .= 'The number of hours between posts should be greater than 0.';
		} else update_option('nd_maxhours',$nd_maxhours);
		
		$nd_maxdays = $_POST['nd_maxdays'];
		if($nd_maxdays <= 0) {
			if($message != '') $message .= '<br />';
			$message .= 'The number of days between posts should be greater than 0.';
		} else update_option('nd_maxdays',$nd_maxdays);
		
		$nd_max0hour = $_POST['nd_max0hour'];
		$nd_max0mins = $_POST['nd_max0mins'];
		if(0 <= $nd_max0hour && $nd_max0hour <= 12 && 0 <= $nd_max0mins && $nd_max0mins <= 60) {
			update_option('nd_max0hour',$nd_max0hour);
			if(strlen($nd_max0mins) == 1) update_option('nd_max0mins',"0$nd_max0mins");
			else update_option('nd_max0mins',$nd_max0mins);
			update_option('nd_max0meridian',$_POST['nd_max0meridian']);
		} else {
			if($message != '') $message .= '<br />';
			$message .= 'The time in the Limiting options is invalid.';
		}
		
		update_option('nd_poststatus',$_POST['nd_poststatus']);
		update_option('nd_publishbehaviour',$_POST['nd_publishbehaviour']);
		update_option('nd_htmltags',$_POST['nd_htmltags']);
		update_option('nd_whitelist',stripslashes($_POST['nd_whitelist']));
		update_option('nd_blacklist',stripslashes($_POST['nd_blacklist']));
		update_option('nd_datetemplate',stripslashes($_POST['nd_datetemplate']));
		update_option('nd_slugtemplate',stripslashes($_POST['nd_slugtemplate']));
		update_option('nd_titlesingle',stripslashes($_POST['nd_titlesingle']));
		update_option('nd_titledouble',stripslashes($_POST['nd_titledouble']));
		update_option('nd_linktemplate',stripslashes($_POST['nd_linktemplate']));
		update_option('nd_tagtemplate',stripslashes($_POST['nd_tagtemplate']));
		update_option('nd_posttsingle',stripslashes($_POST['nd_posttsingle']));
		update_option('nd_posttdouble',stripslashes($_POST['nd_posttdouble']));
		
		if($_POST['nd_use_post_tags']) update_option('nd_use_post_tags',1);
		else update_option('nd_use_post_tags',0);
		if(isset($_POST['nd_post_tags'])) update_option('nd_post_tags',stripslashes($_POST['nd_post_tags']));
		
		if($message == '') $message = 'Settings saved successfully.';
		else {
			if($savechanges == 1) $message .= '<br />Automatic hourly updates were not activated because the settings were not saved successfully.';
			else if($savechanges == 3) $message .= '<br />Unable to perform update because the settings were not saved successfully.';
			$savechanges = 4; // $savechanges is used to determine if we should attempt to activate daily updates or try an to do an update now.
		}
	}
	
	if(isset($_POST['nd_hourly_activate']) && $savechanges != 4) {
		if ( $wp_db_version < 4509 ) {
			if($message != '') $message .= '<br />';
			$message .= 'Automatic hourly updates activation failed. Sorry, automatic updating is only available with Wordpress 2.1 or later.';
		} else {
			$username = urlencode(get_option('nd_username'));
			if(!($service = get_option('nd_service'))) $service = 0;
			if($username) {
				switch($service) { // [SERVICE]
					case 0 : $rssurl = "http://feeds.delicious.com/v2/rss/$username"; break; // delicious
					case 1 : $rssurl = "http://ma.gnolia.com/rss/lite/people/$username"; break; // ma.gnolia
					case 2 : $rssurl = get_option('nd_username'); break; // Google Reader
					case 3 : $rssurl = get_option('nd_username'); break; // Google Bookmarks
					case 4 : $rssurl = get_option('nd_username'); break; // Reddit
					case 5 : $rssurl = get_option('nd_username'); break; // Yahoo Pipes
				}
				$feed = new SimplePie();
				$feed->set_feed_url($rssurl);
				$feed->enable_cache(false);
				$success = $feed->init();
				$feed->handle_content_type();
				if(!$success) {
					if($message != '') $message .= '<br />';
					$message .= "Automatic hourly updates activation failed. Unable to establish connection. SimplePie said: " . $feed->error();
				} else { // Add cron
					$crontime = time();
					// Set the time to the next hour. 
					$crontime += (60 - date('i',$crontime))*60 - date('s',$crontime);
					
					wp_schedule_event($crontime, 'hourly', 'nd_hourly_update');
					neop_pstlcs_log('Automatic hourly updates activated.',time());
					update_option('nd_hourlyupdates',1);
				}
			} else {
				if($message != '') $message .= '<br />';
				$message .= 'Automatic hourly updates activation failed. No username set up.';
			} 
		}
	}

	if(isset($_POST['nd_hourly_deactivate'])) {
		// Remove cron job
		wp_clear_scheduled_hook('nd_hourly_update');
		neop_pstlcs_log('Automatic hourly updates deactivated.',time());
		update_option('nd_hourlyupdates',0);
	}
	
	if(isset($_POST['nd_update']) && $savechanges != 4) {
		if($message != '') $message .= '<br />';
		$message .= neop_pstlcs_post_new(0);
	}
	if($message != '') { ?>
		<div id="message" class="updated fade"><p style="line-height:150%"><strong>
		<?php echo $message; ?>
		</strong></p></div>
<?php }

	// Prepare variables to dispaly the options page. If an option is not set, use the default setting.
	if(!($nd_hourlyupdates = get_option('nd_hourlyupdates'))) $nd_hourlyupdates = 0;
	if(!($nd_service = get_option('nd_service'))) $nd_service = 0;
	if(!($nd_username = get_option('nd_username'))) $nd_username = '';
	if(!($selecteduser = get_option('nd_idforposts'))) $selecteduser = 1;
	if(!($selectedcatlist = get_option('nd_catforposts'))) $selectedcatlist = get_option('default_category');
	if(!($nd_allowcomments = get_option('nd_allowcomments'))) $nd_allowcomments = get_option('default_comment_status');
	if(!($nd_allowpings = get_option('nd_allowpings'))) $nd_allowpings = get_option('default_comment_status');
	if(!($nd_mincount = get_option('nd_mincount'))) $nd_mincount = 5;
	if(!($nd_limit = get_option('nd_limit'))) $nd_limit = 0;
	if(!($nd_maxcount = get_option('nd_maxcount'))) $nd_maxcount = $nd_mincount + 5;
	if(!($nd_maxhours = get_option('nd_maxhours'))) $nd_maxhours = 1;
	if(!($nd_maxdays = get_option('nd_maxdays'))) $nd_maxdays = 1;
	if(!($nd_max0hour = get_option('nd_max0hour'))) $nd_max0hour = 12;
	if(!($nd_max0mins = get_option('nd_max0mins'))) $nd_max0mins = '00';
	if(!($nd_max0meridian = get_option('nd_max0meridian'))) $nd_max0meridian = 1;
	if(!($nd_poststatus = get_option('nd_poststatus'))) $nd_poststatus = 'publish';
	if(!($nd_publishbehaviour = get_option('nd_publishbehaviour'))) $nd_publishbehaviour = 0;
	if(!($nd_htmltags = get_option('nd_htmltags'))) $nd_htmltags = '';
	if(!($nd_whitelist = get_option('nd_whitelist'))) $nd_whitelist = '';
	if(!($nd_blacklist = get_option('nd_blacklist'))) $nd_blacklist = '';
	
	if(!($nd_datetemplate = get_option('nd_datetemplate'))) $nd_datetemplate = 'F jS';
	if(!($nd_slugtemplate = get_option('nd_slugtemplate'))) $nd_slugtemplate = '';
	if(!($nd_titlesingle = get_option('nd_titlesingle'))) $nd_titlesingle = 'Bookmarks for %datestart% from %datestart{H:i}% to %dateend{H:i}%';
	if(!($nd_titledouble = get_option('nd_titledouble'))) $nd_titledouble = 'Bookmarks for %datestart% through %dateend%';
	if(!($nd_linktemplate = get_option('nd_linktemplate'))) $nd_linktemplate = '<li><a href="%href%">%title%</a> - %description%</li>';
	if(!($nd_tagtemplate = get_option('nd_tagtemplate'))) $nd_tagtemplate = '<a href="%tagurl%">%tagname%</a> ';
	if(!($nd_posttsingle = get_option('nd_posttsingle'))) $nd_posttsingle = "<p>These are my links for %datestart% from %datestart{H:i}% to %dateend{H:i}%:</p>\n<ul>\n%bookmarks%\n</ul>";
	if(!($nd_posttdouble = get_option('nd_posttdouble'))) $nd_posttdouble = "<p>These are my links for %datestart% through %dateend%:</p>\n<ul>\n%bookmarks%\n</ul>";
	
	if(!($nd_use_post_tags = get_option('nd_use_post_tags'))) $nd_use_post_tags = 0;
	if(!($nd_post_tags = get_option('nd_post_tags'))) $nd_post_tags = '';
	
	$selectedcatlist = ','.$selectedcatlist.',';
	$nd_username = htmlentities($nd_username);
	$nd_htmltags = htmlentities($nd_htmltags);
	$nd_whitelist = htmlentities($nd_whitelist);
	$nd_blacklist = htmlentities($nd_blacklist);
	$nd_datetemplate = htmlentities($nd_datetemplate);
	$nd_slugtemplate = htmlentities($nd_slugtemplate);
	$nd_titlesingle = htmlentities($nd_titlesingle);
	$nd_titledouble = htmlentities($nd_titledouble);
	$nd_linktemplate = htmlentities($nd_linktemplate);
	$nd_tagtemplate = htmlentities($nd_tagtemplate);
	$nd_posttsingle = htmlentities($nd_posttsingle);
	$nd_posttdouble = htmlentities($nd_posttdouble);
	$nd_post_tags = htmlentities($nd_post_tags);
	
	if(!($nd_log = get_option('nd_log'))) $nd_log = 'There is no logged activity.';
	
	// [SERVICE]
	$tagsdisabled = 0;
	if($nd_service == 2 || $nd_service == 4 || $nd_service == 5) $tagsdisabled = 1;
	$urlservice = 0;
	if($nd_service >= 2) $urlservice = 1;
	
?>
	<script type="text/javascript">
	//<![CDATA[
	<?php echo "var nd_service_js = $nd_service;"; ?>
	var nd_whitelist_tags,nd_blacklist_tags,nd_bookmark_tags,nd_submitbutton;
	
	function nd_servicechanged() {
		oldservice = nd_service_js;
		if(document.getElementById('nd_service_0').checked) nd_service_js = 0;
		else if(document.getElementById('nd_service_1').checked) nd_service_js = 1;
		else if(document.getElementById('nd_service_2').checked) nd_service_js = 2;
		else if(document.getElementById('nd_service_4').checked) nd_service_js = 4;
		else if(document.getElementById('nd_service_5').checked) nd_service_js = 5;
		
		if(oldservice != nd_service_js) document.nd_settingsform.nd_settingschanged.value = "1";
		// [SERVICE]
		nd_status = function (type,service) { // We only use this inside nd_toggle, so no need for a global function.
			switch(type) {
				case 'tags' :
					if(service == 2 || service == 4 || service == 5) return 1;
					else return 0;
					break;
				case 'url' :
					if(service >= 2) return 1;
					else return 0;
					break;
			}
		}
		
		// Handle the tag status
		old_tagsdisabled = nd_status('tags',oldservice);
		new_tagsdisabled = nd_status('tags',nd_service_js);
		if(old_tagsdisabled != new_tagsdisabled) {
			if(new_tagsdisabled == 1) {
				document.getElementById('nd_linktemplate_content').innerHTML = "The following will be replaced with the bookmark's info: %href% - url, %title% - description, %description% - extended description and %tag% - tags ( %tag% will always be \"none\" )";
				
				document.getElementById('nd_use_post_tags_row').style.visibility = 'hidden';
				document.getElementById('nd_whitelist_row').style.visibility = 'hidden';
				document.getElementById('nd_blacklist_row').style.visibility = 'hidden';
			} else { // Enable tag-related features
				document.getElementById('nd_linktemplate_content').innerHTML = "The following will be replaced with the bookmark's info: %href% - url, %title% - description, %description% - extended description and %tag% - tags";
				
				document.getElementById('nd_use_post_tags_row').style.visibility = 'visible';
				document.getElementById('nd_whitelist_row').style.visibility = 'visible';
				document.getElementById('nd_blacklist_row').style.visibility = 'visible';
			}
		}
		
		old_urlservice = nd_status('url',oldservice);
		new_urlservice = nd_status('url',nd_service_js);
		
		if(old_urlservice != new_urlservice) {
			if(new_urlservice == 1) {
				document.getElementById('th_username').innerHTML = "Feed URL";
				document.getElementById('nd_username').size = "50";
			} else {
				document.getElementById('th_username').innerHTML = "Username";
				document.getElementById('nd_username').size = "15";
			}
		}
	}
	
	function nd_limitchanged() {
		document.nd_settingsform.nd_settingschanged.value = "1";
		if(document.getElementById('nd_limit_1').checked && document.getElementById('nd_maxcount').value == 1) {
			document.getElementById('nd_titlesingle_span').innerHTML = "%datestart% and %dateend% will be replaced by the oldest and newest dates of the bookmarks in the post. %title% will be replaced by the bookmark's title.";
			document.getElementById('nd_slugtemplate_span').innerHTML = "%datestart% and %dateend% will be replaced by the oldest and newest dates of the bookmarks in the post. %title% will be replaced by the bookmark's title.";
		} else {
			document.getElementById('nd_titlesingle_span').innerHTML = "%datestart% and %dateend% will be replaced by the oldest and newest dates of the bookmarks in the post.";
			document.getElementById('nd_slugtemplate_span').innerHTML = "%datestart% and %dateend% will be replaced by the oldest and newest dates of the bookmarks in the post.";
		}
	}
	
	function nd_settingssubmitted() {
		var scfield = document.nd_settingsform.nd_settingschanged;
		if(nd_submitbutton == 0) ssfield.value = 1;
		else if(scfield.value == 1) {
			switch(nd_submitbutton) {
				case 1 : shouldupdate = confirm("Do you want to save the changes made to the settings before performing an update? (OK - Yes, Cancel - No)"); break;
				case 2 : shouldupdate = confirm("Do you want to save the changes made to the settings before activating automatic hourly updates? (OK - Yes, Cancel - No)"); break;
				case 3 : shouldupdate = confirm("Do you want to save the changes made to the settings before deactivating automatic hourly updates? (OK - Yes, Cancel - No)"); break;
			}
			if(!shouldupdate) scfield.value = 0;
		}
		return true;
	}
	
	// This is the simplest AJAX code I could come up with since I don't really
	//  need any scalability because only clearing the log uses AJAX.
	function nd_clearthelog(url) {
		clogspan = document.getElementById('nd_clogspan');
	
		clogspan.innerHTML = "Clearing the log...";
		
		req = false;
		if(window.XMLHttpRequest) {
			try { req = new XMLHttpRequest(); }
			catch(e) { req = false; }
		} else if(window.ActiveXObject) {
			try { req = new ActiveXObject("Msxml2.XMLHTTP"); }
			catch(e) {
				try { req = new ActiveXObject("Microsoft.XMLHTTP"); }
				catch(e) { req = false; }
			}
		}
		
		if(req) {
			req.open("POST","<?php echo neop_pstlcs_geturl(); ?>",true);
			req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			req.onreadystatechange = function () {
				if(req.readyState == 4 && req.status == 200) {
					clogspan.innerHTML = "Log cleared successfully.";
					document.getElementById('nd_log').value = "There is no logged activity.";
				}
				else clogspan.innerHTML = "Could not clear the log.";
			}
			req.send("nd_clearlog=1");
		} else clogspan.innerHTML = "Could not clear the log.";
	}
	//]]>
	</script>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Postalicious options</h2>
		<div style="border: 1px solid black; margin:1em; padding:1em;">
		<table width="100%"><tr><td>Postalicious is the result of many hours of hard work, if you enjoy using it, please consider donating by clicking the PayPal button on the right.</td>
		<td align="right">
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display:inline;" />
		<input type="hidden" name="cmd" value="_s-xclick" />
		<input type="hidden" name="hosted_button_id" value="2004483" />
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="" />
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
		</form>
		</td></tr>
		</table>
		</div>
		<form name="nd_settingsform" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" onsubmit="return nd_settingssubmitted()">
		<input type="hidden" name="nd_settingschanged" value="0" />
		<div style="background: <?php if($nd_hourlyupdates == 1) echo "#cf9"; else echo "#f99"; ?> 1em; border: 1px solid <?php if($nd_hourlyupdates == 1) echo "#green"; else echo "#red"; ?>; margin:1em; padding:1em;">
		<table width="100%"><tr><td>
<?php
		$nd_lastrun = get_option('nd_lastrun');
		if($nd_hourlyupdates == 1) {
			echo "Automatic hourly updates are active.";
			if($nd_lastrun) echo ' <b>Last update:</b> ' . mysql2date('F j, Y G:i',date('Y-m-d H:i:s',$nd_lastrun));
			echo '</td><td align="right"><input type="submit" name="nd_update" class="button-secondary" value="Update Now" onclick="nd_submitbutton = 1" />&nbsp;&nbsp;<input type="submit" name="nd_hourly_deactivate" class="button-secondary" value="Deactivate Hourly Updates" onclick="nd_submitbutton = 3" />';
		} else {
			echo "Automatic hourly updates are not activated.";
			echo '</td><td align="right"><input type="submit" name="nd_update" class="button-secondary" value="Update Now" onclick="nd_submitbutton = 1" />&nbsp;&nbsp;<input type="submit" name="nd_hourly_activate" class="button-secondary" value="Activate Hourly Updates" onclick="nd_submitbutton = 2" />';
		}
?>
		</td></tr></table>
		</div>
		<br />
		<h3>Account Information</h3>
		<table class="form-table"> 
		<tr>
		<th scope="row">Account type</th>
		<td><fieldset>
		<label><input id="nd_service_0" name="nd_service" type="radio" value="0" <?php if($nd_service == 0) echo 'checked="checked"' ?> onclick="nd_servicechanged();" />
		delicious</label><br />
		<label><input id="nd_service_1" name="nd_service" type="radio" value="1" <?php if($nd_service == 1) echo 'checked="checked"' ?> onclick="nd_servicechanged();" />
		ma.gnolia</label><br />
		<label><input id="nd_service_2" name="nd_service" type="radio" value="2" <?php if($nd_service == 2) echo 'checked="checked"' ?> onclick="nd_servicechanged();" />
		Google Reader</label><br />
		<label><input id="nd_service_4" name="nd_service" type="radio" value="4" <?php if($nd_service == 4) echo 'checked="checked"' ?> onclick="nd_servicechanged();" />
		Reddit</label><br />
		<label><input id="nd_service_5" name="nd_service" type="radio" value="5" <?php if($nd_service == 5) echo 'checked="checked"' ?> onclick="nd_servicechanged();" />
		Yahoo Pipes</label>
		</fieldset></td>
		</tr>
		<tr valign="top"> 
		<th id="th_username"><label for="nd_username"><?php if($urlservice) echo "Feed URL"; else echo "Username"?></label></th> 
		<td colspan="2">
		<input name="nd_username" type="text" id="nd_username" value="<?php echo $nd_username; ?>" size="<?php if($urlservice) echo "50"; else echo "15";?>" onchange="this.form.nd_settingschanged.value=1" />
		</td></tr>
		</table>
		<h3>Post settings</h3>
		<table class="form-table"> 
		<tr valign="top"> 
		<th scope="row"><label for="nd_idforposts">Author</label></th>
		<td><select name="nd_idforposts" onchange="this.form.nd_settingschanged.value=1"><?php
		for($i=0;$i<$numusers;$i++) {
			$currentid = $userids[$i];
			if($currentid == $selecteduser)
				echo "<option value='$currentid' selected='selected'>{$userdisplayn[$i]}</option>";
			else
				echo "<option value='$currentid'>{$userdisplayn[$i]}</option>";
		}
		?></select></td></tr>
		<tr valign="top"> 
		<th scope="row">Post in categories</th>
		<td><fieldset><legend class="hidden">Categories</legend><?php
		if($wp_db_version >= 6124) { // 2.3 or later
			for($i=0;$i<$numcats;$i++) {
				$currentcat = $categories[$i]->cat_ID;
				if(strpos($selectedcatlist,','.$currentcat.',') !== FALSE)
					echo "<input name='nd_postincat_$currentcat' type='checkbox' id='nd_postincat_$currentcat' checked='checked' onchange='this.form.nd_settingschanged.value=1' />
		<label for='nd_postincat_$currentcat'>{$categories[$i]->cat_name}&nbsp;</label>";
				else
					echo "<input name='nd_postincat_$currentcat' type='checkbox' id='nd_postincat_$currentcat' onchange='this.form.nd_settingschanged.value=1' />
		<label for='nd_postincat_$currentcat'>{$categories[$i]->cat_name}&nbsp;</label>";
			}
		} else {
			for($i=0;$i<$numcats;$i++) {
				$currentcat = $catids[$i];
				if(strpos($selectedcatlist,','.$currentcat.',') !== FALSE)
					echo "<input name='nd_postincat_$currentcat' type='checkbox' id='nd_postincat_$currentcat' checked='checked' onchange='this.form.nd_settingschanged.value=1' />
		<label for='nd_postincat_$currentcat'>{$catdnames[$i]}&nbsp;</label>";
				else
					echo "<input name='nd_postincat_$currentcat' type='checkbox' id='nd_postincat_$currentcat' onchange='this.form.nd_settingschanged.value=1' />
		<label for='nd_postincat_$currentcat'>{$catdnames[$i]}&nbsp;</label>";
			}
		}
		?></fieldset></td>
		</tr>
		<tr>
		<th scope="row">Discussion</th>
		<td><fieldset><legend class="hidden">Discussion</legend>
		<input name="nd_allowcomments" type="checkbox" id="nd_allowcomments" <?php if($nd_allowcomments == 'open') echo 'checked="checked"' ?> onchange="this.form.nd_settingschanged.value=1" />
		<label for="nd_allowcomments">Allow comments</label><br />
		<input name="nd_allowpings" type="checkbox" id="nd_allowpings" <?php if($nd_allowpings == 'open') echo 'checked="checked"' ?>  onchange="this.form.nd_settingschanged.value=1" />
		<label for="nd_allowpings">Allow pings</label>
		</fieldset></td>
		</tr>
		</table>
		<h3>Options</h3>
		<table class="form-table"> 
		<tr valign="top"> 
		<th><label for="nd_mincount">Minimum bookmarks</label></th> 
		<td>
		<input name="nd_mincount" type="text" id="nd_mincount" value="<?php echo $nd_mincount; ?>" size="3" onchange="this.form.nd_settingschanged.value=1" />
		<span class="setting-description">This is the minimum number of bookmarks a posts needs to get published by Postalicious.</span>
		</td> 
		</tr>
		<tr>
		<th scope="row">Limiting behavior</th>
		<td><fieldset><legend class="hidden">Limiting behavior</legend>
		<label><input name="nd_limit" onchange="nd_limitchanged()"  type="radio" value="2" <?php if($nd_limit == 2) echo 'checked="checked"' ?> />
		Publish any posts with the minimum number of bookmarks.</label><br />
		<label><input id="nd_limit_1" name="nd_limit" onchange="nd_limitchanged()" type="radio" value="1" <?php if($nd_limit == 1) echo 'checked="checked"' ?> />
		Post at most <input name="nd_maxcount" type="text" id="nd_maxcount" onchange="nd_limitchanged()" value="<?php echo $nd_maxcount; ?>" size="3" /> bookmarks per post.</label><br />
		<label><input name="nd_limit" onchange="nd_limitchanged()" type="radio" value="3" <?php if($nd_limit == 3) echo 'checked="checked"' ?>  />
		Keep at least <input name="nd_maxhours" type="text" id="nd_maxhours" value="<?php echo $nd_maxhours; ?>" size="3" onchange="this.form.nd_settingschanged.value=1" /> hours between posts.</label><br />
		<label><input name="nd_limit" onchange="nd_limitchanged()" type="radio" value="0" <?php if($nd_limit == 0) echo 'checked="checked"' ?>  />
		Post at most once every <input name="nd_maxdays" type="text" id="nd_maxdays" value="<?php echo $nd_maxdays; ?>" size="3" onchange="this.form.nd_settingschanged.value=1" /> days after <input name="nd_max0hour" type="text" id="nd_max0hour" value="<?php echo $nd_max0hour; ?>" size="2" onchange="this.form.nd_settingschanged.value=1" />:<input name="nd_max0mins" type="text" id="nd_max0mins" value="<?php echo $nd_max0mins; ?>" size="2" onchange="this.form.nd_settingschanged.value=1" />&nbsp;<select name="nd_max0meridian" onchange="this.form.nd_settingschanged.value=1"><option value="1"<?php if($nd_max0meridian == 1) echo ' selected="selected"'; ?>>am</option><option value="2"<?php if($nd_max0meridian == 2) echo ' selected="selected"'; ?>>pm</option></select>.</label>
		</fieldset></td>
		</tr>
		<tr>
		<th scope="row">Post status</th>
		<td><fieldset><legend class="hidden">Post Status</legend>
		<label><input name="nd_poststatus" type="radio" value="publish" <?php if($nd_poststatus == 'publish') echo 'checked="checked"' ?> onchange="this.form.nd_settingschanged.value=1" />
		Publish posts</label><br />
		<label><input name="nd_poststatus" type="radio" value="draft" <?php if($nd_poststatus == 'draft') echo 'checked="checked"' ?>  onchange="this.form.nd_settingschanged.value=1" />
		Post as drafts</label>
		</fieldset></td>
		</tr>
		<tr>
		<th>Published posts</th>
		<td><fieldset><legend class="hidden">If a draft created by Postalicious is published by a blog author</legend>
		<label><input name="nd_publishbehaviour" type="radio" value="0" <?php if($nd_publishbehaviour == 0) echo 'checked="checked"' ?> onchange="this.form.nd_settingschanged.value=1" />
		If a draft created by Postalicious is published by a blog author, create a new post</label><br />
		<label><input name="nd_publishbehaviour" type="radio" value="1" <?php if($nd_publishbehaviour == 1) echo 'checked="checked"' ?>  onchange="this.form.nd_settingschanged.value=1" />
		If a draft created by Postalicious is published by a blog author, edit the published post</label>
		</fieldset></td></tr>
		<tr valign="top"> 
		<th><label for="nd_htmltags">HTML Tags</label></th> 
		<td>
		<input name="nd_htmltags" type="text" id="nd_htmltags" value="<?php echo $nd_htmltags; ?>" size="50" onchange="this.form.nd_settingschanged.value=1" />
		<span class="setting-description">These are the allowed HTML tags in the bookmark's description. (Comma separated list) example: a,p,br</span>
		</td></tr>
		<tr valign="top" id="nd_whitelist_row"<?php if($tagsdisabled > 0) echo 'style="visibility:hidden"';?>> 
		<th><label for="nd_whitelist">Tag whitelist</label></th> 
		<td>
		<input name="nd_whitelist" type="text" id="nd_whitelist" value="<?php echo $nd_whitelist; ?>" size="50" onchange="this.form.nd_settingschanged.value=1" />
		<span class="setting-description">Only bookmarks that contain all of these tags will be posted. (Comma separated list)<span>
		</td></tr>
		<tr valign="top" id="nd_blacklist_row"<?php if($tagsdisabled > 0) echo 'style="visibility:hidden"';?>> 
		<th><label for="nd_blacklist">Tag blacklist</label></th> 
		<td>
		<input name="nd_blacklist" type="text" id="nd_blacklist" value="<?php echo $nd_blacklist; ?>" size="50" onchange="this.form.nd_settingschanged.value=1" />
		<span class="setting-description">Bookmarks which contain any of these tags will not be posted. (Comma separated list)</span>
		</td></tr>
		</table>
<?php if($utw || $STagging || $wp_db_version >= 6124) { ?>
		 <?php if( $wp_db_version >= 6124 ) { ?>
		 <h3>Tags</h3>
		 <?php } else if($utw) { ?>
		<h3>UltimateTagWarrior Integration</h3>
		<?php } else if($STagging) { ?>
		<h3>Simple Tagging Plugin Integration</h3>
		<?php } ?>
		<table class="form-table"> 
		<tr valign="top" id="nd_use_post_tags_row"<?php if($tagsdisabled > 0) echo 'style="visibility:hidden"';?>> 
		<th>Bookmark's tags</th>
		<td><fieldset><legend class="hidden">Bookmark's tags</legend>
		<label><input name="nd_use_post_tags" type="checkbox" id="nd_use_post_tags" <?php if($nd_use_post_tags == 1) echo 'checked="checked"' ?> onchange="this.form.nd_settingschanged.value=1" />
		Use the bookmark's tags as tags for the post in which the bookmark appears.</label>
		</fieldset></td>  
		</tr> 
		<tr valign="top"> 
		<th><label for="nd_post_tags">Post tags</label></th> 
		<td>
		<input name="nd_post_tags" type="text" id="nd_post_tags" value="<?php echo $nd_post_tags; ?>" size="25" onchange="this.form.nd_settingschanged.value=1" />
		<span class="setting-description">All posts created by Postalicious will have these tags. (Comma separated list)</span>
		</td> 
		</tr>
		</table>
<?php } ?>
		<h3>Templates</h3>
		<table class="form-table"> 
		<tr valign="top"> 
		<th><label for="nd_datetemplate">Default date format</label></th> 
		<td>
		<input name="nd_datetemplate" type="text" id="nd_datetemplate" value="<?php echo $nd_datetemplate; ?>" size="10" onchange="this.form.nd_settingschanged.value=1" /><br />
		<span class="setting-description"><b>Output: </b><?php echo mysql2date($nd_datetemplate,date('Y-m-d H:i:s',time())); ?>. This date format will be used for all dates posted by Postalicious. See PHP's <a href="http://php.net/date">date</a> documentation for date formatting.</span>
		</td></tr>
		<tr valign="top"> 
		<th><label for="nd_slugtemplate">Post Slug Template</label></th> 
		<td>
		<input name="nd_slugtemplate" type="text" id="nd_slugtemplate" value="<?php echo $nd_slugtemplate; ?>" size="30" onchange="this.form.nd_settingschanged.value=1" /><br />
		<span id="nd_slugtemplate_span" class="setting-description">%datestart% and %dateend% will be replaced by the oldest and newest dates of the bookmarks in the post.<?php if($nd_limit == 1 && $nd_maxcount == 1) echo " %title% will be replaced by the bookmark's title."; ?></span>
		</td></tr>
		<tr valign="top"> 
		<th><label for="nd_titlesingle">Post title (single day)</label></th> 
		<td>
		<input name="nd_titlesingle" type="text" id="nd_titlesingle" value="<?php echo $nd_titlesingle; ?>" size="75" onchange="this.form.nd_settingschanged.value=1" /><br />
		<span id="nd_titlesingle_span" class="setting-description">%datestart% and %dateend% will be replaced by the oldest and newest dates of the bookmarks in the post.<?php if($nd_limit == 1 && $nd_maxcount == 1) echo " %title% will be replaced by the bookmark's title."; ?></span>
		</td></tr>
		<tr valign="top"> 
		<th><label for="nd_titledouble">Post title (two days)</label></th> 
		<td>
		<input name="nd_titledouble" type="text" id="nd_titledouble" value="<?php echo $nd_titledouble; ?>" size="75" onchange="this.form.nd_settingschanged.value=1" /><br />
		<span class="setting-description">%datestart% and %dateend% will be replaced by the oldest and newest dates of the bookmarks in the post.</span>
		</td></tr>
		<tr valign="top"> 
		<th><label for="nd_linktemplate">Bookmark</label></th> 
		<td>
		<input name="nd_linktemplate" type="text" id="nd_linktemplate" value="<?php echo $nd_linktemplate; ?>" size="75" onchange="this.form.nd_settingschanged.value=1" /><br />
		<span id="nd_linktemplate_content" class="setting-description">The following will be replaced with the bookmark's info: %href% - url, %title% - description, %description% - extended description %date% - date added and %tag% - tags <?php if(MAGPIE_MOD_VERSION != 'neop' && $nd_service == 1) echo '( %tags% will always be "none" )' ?></span>
		</td></tr>
		<tr valign="top">
		<th><label for="nd_tagtemplate">Tag</label></th>
		<td>
		<input name="nd_tagtemplate" type="text" id="nd_tagtemplate" value="<?php echo $nd_tagtemplate; ?>" size="75" onchange="this.form.nd_settingschanged.value=1" /><br />
		<span class="setting-description">The following will be replaced with the tag's info: %tagname% - name of the tag, %tagurl% - url to the page of the bookmarks you have tagged with this tag</span>
		</td></tr>
		</table>
		<h3>Post template (single day)</h3>
		<p>This is the template for the body of the posts created by Postalicious with bookmarks for one day only. %bookmarks% should be placed where you want the list of bookmarks to be shown, each bookmark will have the format specified by the bookmark template. %datestart% and %dateend% will be replaced by the oldest and newest dates of the bookmarks in the post.</p>
		<textarea name="nd_posttsingle" id="nd_posttsingle" style="width: 98%;" rows="8" cols="50" onchange="this.form.nd_settingschanged.value=1"><?php echo $nd_posttsingle; ?></textarea>
		<h3>Post template (two days)</h3>
		<p>This is the template for the body of the posts created by Postalicious with bookmarks for a range of dates. %bookmarks% should be placed where you want the list of bookmarks to be shown, each bookmark will have the format specified by the bookmark template. %datestart% and %dateend% will be replaced by the oldest and newest dates of the bookmarks in the post.</p>
		<textarea name="nd_posttdouble" id="nd_posttdouble" style="width: 98%;" rows="8" cols="50" onchange="this.form.nd_settingschanged.value=1"><?php echo $nd_posttdouble; ?></textarea>
		<h3>Activity Log</h3>
		<textarea readonly="readonly" name="nd_log" id="nd_log" style="width: 98%;" rows="20" cols="50"><?php echo $nd_log; ?></textarea>
		<input type="button" class="button-secondary" value="Clear Log" onclick="nd_clearthelog()" /><span id="nd_clogspan" style="margin-left:5px;"></span>
		<div class="submit"><input type="submit" name="save_changes" class="button-primary" value="Save Changes" onclick="nd_submitbutton = 0" /></div>
		</form>
	</div>
<?php
}
endif;

if (!function_exists('neop_pstlcs_add_options')) :
function neop_pstlcs_add_options() {
	if (function_exists('neop_pstlcs_options')) {
		add_options_page('Postalicious', 'Postalicious', 6, 'wp-postalicious', 'neop_pstlcs_options');
	}
}
endif;

if (!function_exists('neop_pstlcs_update')) :
function neop_pstlcs_update() {
	if(!($nd_version = get_option('nd_version'))) $nd_version = 201; // Because of a bug in 121, get_option('nd_version') will always be at least 150
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
	if($nd_version < 200) {
		// Update Postalicious 1.5 to Postalicious 2.0
		delete_option('nd_allowprivate'); // Private bookmarks can no longer be posted, so this is irrelevant.
		// Rename username option and delete password option.
		update_option('nd_username',get_option('nd_delusername'));
		delete_option('nd_delusername');
		delete_option('nd_delpassword');
		// Rename daily updates to hourly updates. Also, change from 'no' to 0 and 'yes' to 1
		$nd_dailyupdates = get_option('nd_dailyupdates');
		if($nd_dailyupdates == 'yes') update_option('nd_hourlyupdates',1);
		else update_option('nd_hourlyupdates',0);
		delete_option('nd_dailyupdates');
		// Change the wp-cron hook from daily to hourly.
		if($nd_dailyupdates == 'yes') {
			wp_clear_scheduled_hook('nd_daily_update');
			wp_schedule_event($crontime, 'hourly', 'nd_hourly_update');
		}
		// Change nd_use_del_tags to nd_use_post_tags
		$nd_use_del_tags = get_option('nd_use_del_tags');
		if($nd_use_del_tags == 'yes') update_option('nd_use_post_tags',1);
		else update_option('nd_use_post_tags',0);
		// Update the templates to use %date% to %datestart% in single title and single body templates.
		$nd_titlesingle = get_option('nd_titlesingle');
		$nd_titlesingle = str_replace('%date%','%datestart%',$nd_titlesingle);
		update_option('nd_titlesingle',$nd_titlesingle);
		$nd_posttsingle = get_option('nd_posttsingle');
		$nd_posttsingle = str_replace('%date%','%datestart%',$nd_posttsingle);
		update_option('nd_posttsingle',$nd_posttsingle);
		// Change %description% to %title% and %extended% to %description% in the link template
		$nd_linktemplate = get_option('nd_linktemplate');
		$nd_linktemplate = str_replace('%description%','%title%',$nd_linktemplate);
		$nd_linktemplate = str_replace('%extended%','%description%',$nd_linktemplate);
		update_option('nd_linktemplate',$nd_linktemplate);
		// Add nd_draftdate2 option (this is not really the correct behavior, but it's close enough)
		update_option('nd_draftdate2',get_option('nd_lastupdate'));
		$nd_version = 200;
	}
	if($nd_version < 201) {
		$nd_tagging_enabled = get_option('nd_tagging_enabled');
		if($nd_tagging_enabled == 0) {
			update_option('nd_post_tags','');
			update_option('nd_use_post_tags',0);
		}
		delete_option('nd_tagging_enabled');
		$nd_version = 201;
	}
	update_option('nd_version',$nd_version);
}
endif;

if (!function_exists('neop_pstlcs_post_new')) :
function neop_pstlcs_post_new($automatic = 1) {
	if(!class_exists('SimplePie')) exit();
	global $wpdb, $wp_db_version, $utw, $STagging;
	$nd_updating = get_option('nd_updating');
	if($nd_updating && get_option('nd_lastrun') + 300 > time()) {
		$lastrun = time();
		update_option('nd_lastrun',$lastrun);
		neop_pstlcs_log('Update Failed. Potalicious is already updating at the moment.',$lastrun);
		return 'Update Failed. Potalicious is already updating at the moment.';
	} else
		update_option('nd_updating',1);
	
	neop_pstlcs_update(); // Check if Postalicious has been udpated since last run.
	
	// Build URL for delicious RSS feed or exit if username has not been set.
	$username = urlencode(get_option('nd_username'));

	if(!($service = get_option('nd_service'))) $service = 0;
	if($username) { // [SERVICE]
		switch($service) {
			case 0 : $rssurl = "http://feeds.delicious.com/v2/rss/$username"; break; // delicious
			case 1 : $rssurl = "http://ma.gnolia.com/rss/lite/people/$username"; break; // ma.gnolia
			case 2 : $rssurl = get_option('nd_username'); break; // Google Reader
			case 3 : $rssurl = get_option('nd_username'); break; // Google Bookmarks
			case 4 : $rssurl = get_option('nd_username'); break; // Reddit
			case 5 : $rssurl = get_option('nd_username'); break; // Yahoo Pipes
		}
	}
	else {
		$lastrun = time();
		update_option('nd_lastrun',$lastrun);
		update_option('nd_updating',0);
		neop_pstlcs_log('Username not set up.',$lastrun);
		return 'Username not set up.';
	}
	
	if(!($draftid = get_option('nd_lastdraftid'))) $draftid = -1;
	if(!($nd_publishbehaviour = get_option('nd_publishbehaviour'))) $nd_publishbehaviour = 0;
	
	// Check to see if a draft created by Postalicious exists.
	$currentpost = -1;
	if($draftid != -1) { 
		$draftstatus = $wpdb->get_var("SELECT post_status FROM $wpdb->posts WHERE ID = $draftid ORDER BY ID DESC LIMIT 1");
		if($draftstatus == 'draft' || $draftstatus == 'future' || ($draftstatus == 'publish' && $nd_publishbehaviour == 1)) {
			if($draftstatus == 'publish') $publishedpost = 1;
			else $publishedpost = 0;
			$currentpost = $draftid;
			if(!($nd_unpublishedcount = get_option('nd_unpublishedcount'))) $nd_unpublishedcount = 0;
			$count = $nd_unpublishedcount;
			$datestart = get_option('nd_draftdate');
			$dateend = get_option('nd_draftdate2');
		}
	}
	
	$lastupdate = get_option('nd_lastupdate');
	// Initiate a SimplePie instance for the feed.
	$feed = new SimplePie();
	$feed->set_feed_url($rssurl);
	$feed->enable_cache(false);
	$success = $feed->init();
	$feed->handle_content_type();
		
	if(!$success) {
		if(!($failed = get_option('nd_failedcount'))) $failed = 0;
		if($automatic) $failed++;
		if($failed >= 24) {
			$message = "Automatic hourly updates have been deactivated because the last 24 updates failed.";
			wp_clear_scheduled_hook('nd_hourly_update');
			update_option('nd_hourlyupdates',0);
			update_option('nd_failedcount',0);
		} else {
			$message = "Unable to establish connection. SimplePie said: " . $feed->error();
			update_option('nd_failedcount',$failed);
		}
		$lastrun = time();
		update_option('nd_lastrun',$lastrun);
		update_option('nd_updating',0);
		neop_pstlcs_log($message,$lastrun);
		return $message;
	} else
		update_option('nd_failedcount',0);
	
	if($lastupdate) {
		if(!($nd_linktemplate = get_option('nd_linktemplate'))) $nd_linktemplate = '<li><a href="%href%">%title%</a> - %description%</li>';
		if(!($nd_tagtemplate = get_option('nd_tagtemplate'))) $nd_tagtemplate = '<a href="%tagurl%">%tagname%</a> ';
	
		if($nd_whitelist = get_option('nd_whitelist')) $nd_whitelist = ',' . str_replace(' ','',$nd_whitelist) . ','; 
		else $nd_whitelist = ',,';
		if($nd_blacklist = get_option('nd_blacklist')) $nd_blacklist = ',' . str_replace(' ','',$nd_blacklist) . ','; 
		else $nd_blacklist = ',,';
		
		if(!($nd_limit = get_option('nd_limit'))) $nd_limit = 0;
		if($nd_limit == 1) {
			if(!($nd_maxcount = get_option('nd_maxcount'))) $nd_limit = 0;
		}
		$post_count = 0;
		
		$newposts = '';
		$newtags = '';
		$totalcount = 0;
		$filteredcount = 0;
		$nd_use_post_tags = get_option('nd_use_post_tags');
		
		// Prepare the arrays allow html tags.
		$pattern = array();
		$replacement = array();
		$nd_htmltags = get_option('nd_htmltags');
		
		switch($service) { // [SERVICE] Some services need certain tags to be allowed.
			case 1 : $nd_htmltags .= 'p,strong,a,img'; break; // ma.gnolia
			case 2 : $nd_htmltags .= 'br';break; // Google Reader
			case 4 : $nd_htmltags = 'a,br'; break; // Reddit (the user's allowed tags don't matter)
		}
		
		if($nd_htmltags) {
			$nd_htmltags = preg_replace('/\s*,\s*/',',',$nd_htmltags); // Remove spaces before and after commas.
  			$nd_htmltags = trim($nd_htmltags); // Remove spaces at the start and end of the string.
  			$nd_htmltags = preg_replace('/,,+/',',',$nd_htmltags); // Remove consecutive commas.
			$htmltagarray = explode(',',$nd_htmltags);
			$htmltagarray = array_flip(array_flip($htmltagarray)); // Remove duplicates.
			foreach($htmltagarray as $tag) {
				$tag = trim($tag);
				array_push($pattern,"/&lt;\s*$tag(.*?)&gt;/ise","/&lt;\s*\/\s*$tag\s*&gt;/is");
				array_push($replacement,"'<$tag'.@html_entity_decode('$1',ENT_QUOTES,'UTF-8').'>'","</$tag>");
			}
		}
		
		foreach(array_reverse($feed->get_items()) as $item) {
			// Consolidate the info from the feed in a single array so that we can use that instead of the service specific ones.
			switch($service) { // [SERVICE]
				case 0: // delicious
					$bookmark[title] = $item->get_title();
					$bookmark[link] = $item->get_link();
					$bookmark[description] = $item->get_description();
					$bookmark[date] = $item->get_date('Y-m-d H:i:s T');
					
					$arr = $item->get_item_tags('', 'category');
					$bookmark[tags] = '';
					if($arr) foreach($arr as $arritm) $bookmark[tags] .= ",{$arritm[data]}";
					$bookmark[tags] = ltrim($bookmark[tags],",");
					break;
				case 1: // ma.gnolia
					$bookmark[title] = $item->get_title();
					$bookmark[link] = $item->get_link();
					$bookmark[description] = $item->get_description();
					$bookmark[date] = $item->get_date('Y-m-d H:i:s T');
					
					$arr = $item->get_item_tags('', 'category');
					$bookmark[tags] = '';
					if($arr) foreach($arr as $arritm) $bookmark[tags] .= ",{$arritm[data]}";
					$bookmark[tags] = ltrim($bookmark[tags],",");
					break;
				case 2 : // Google Reader
					$bookmark[title] = $item->get_title();
					$bookmark[link] = $item->get_link();
					$bookmark[description] = neop_pstlcs_arrelm($item->get_item_tags('http://www.google.com/schemas/reader/atom/', 'annotation'),0,'child','http://www.w3.org/2005/Atom','content',0,'data');
					$bookmark[date] = $item->get_date('Y-m-d H:i:s T');
					$bookmark[tags] = '';
					break;
				case 3 : // Google Bookmarks
					$bookmark[title] = $item->get_title();
					$bookmark[link] = $item->get_link();
					$bookmark[description] = neop_pstlcs_arrelm($item->get_item_tags('http://www.google.com/history/', 'bkmk_annotation'),0,'data');
					$bookmark[date] = $item->get_date('Y-m-d H:i:s T');
					
					$arr = $item->get_item_tags('http://www.google.com/history/', 'bkmk_label');
					$bookmark[tags] = '';
					if($arr) foreach($arr as $arritm) $bookmark[tags] .= ",{$arritm[data]}";
					$bookmark[tags] = ltrim($bookmark[tags],",");
					break;
				case 4 : // Reddit
					$bookmark[title] = $item->get_title();
					$bookmark[link] = $item->get_link();
					$bookmark[description] = $item->get_description();
					$bookmark[date] = $item->get_date('Y-m-d H:i:s T');
					$bookmark[tags] = '';
					break;
				case 5 : // Yahoo pipes
					$bookmark[title] = $item->get_title();
					$bookmark[link] = $item->get_link();
					$bookmark[description] = $item->get_description();
					$bookmark[date] = $item->get_date('Y-m-d H:i:s T');
					$bookmark[tags] = '';
					break;
			}
						
			$ptime = strtotime($bookmark[date]);
			
			// strtotime seems to have some problems with certain dates in some PHP installations, so let's have some fallback code
			if($ptime == -1 || $ptime === false) {
				if($service == 2) { // [SERVICE]
					// Note: removed service 0 since delicious 2.0 changed the date format, so this will no longer work.
					$timea = explode(':',substr($bookmark[date],11,8));
					$datea = explode('-',substr($bookmark[date],0,10));
					$ptime = mktime($timea[0],$timea[1],$timea[2],$datea[1],$datea[2],$datea[0]);
				}
			}
			
			$filtered = 0;
			if($ptime > $lastupdate) { // If check posts newer than the last update time.
				$totalcount++;
				// Set the time of the newest post Postalicious has processed
				if(!$newlastupdate) $newlastupdate = $ptime;
				else if($ptime > $newlastupdate) $newlastupdate = $ptime;
				$ftags = explode(',',$bookmark[tags]);
				if($nd_whitelist == ',,') $filtered = 1;
				else {
					foreach($ftags as $t) {
						if(strpos($nd_whitelist,','.$t.',') !== FALSE) {
							$filtered = 1;
							break;
						}
					}
				} if($nd_blacklist != ',,') {
					foreach($ftags as $t) {
						if(strpos($nd_blacklist,','.$t.',') !== FALSE) {
							$filtered = 0;
							break;
						}
					}
				}
			}
			
			if($filtered == 1) {
				$filteredcount++;
				// Sets $dateend and $datestart to the newest and oldest dates that will be included in the post.
				if(!$dateend) $dateend = $ptime;
				else if($ptime > $dateend) $dateend = $ptime;
				if(!$datestart) $datestart = $ptime;
				else if($ptime < $datestart) $datestart = $ptime;
				
				$currentlink = $nd_linktemplate;
				$currentlink = str_replace("%href%",$bookmark[link],$currentlink);
				if(version_compare(PHP_VERSION,'5.2.3',">=")) {
					$currentlink = str_replace("%title%",htmlentities($bookmark[title],NULL,"UTF-8",FALSE),$currentlink);
					// Add the description to $currentlink but with the proper html tags escaped.
					$currentlink = str_replace("%description%",preg_replace($pattern,$replacement,htmlentities($bookmark[description],ENT_QUOTES,"UTF-8",FALSE)),$currentlink);
				} else {
					$tempvar = @html_entity_decode($bookmark[title],ENT_QUOTES,"UTF-8");
					$tempvar = htmlentities($tempvar,ENT_QUOTES,"UTF-8");
					$currentlink = str_replace("%title%",$tempvar,$currentlink);
					$tempvar = @html_entity_decode($bookmark[description],ENT_QUOTES,"UTF-8");
					$tempvar = htmlentities($tempvar,ENT_QUOTES,"UTF-8");
					$currentlink = str_replace("%description%",preg_replace($pattern,$replacement,$tempvar),$currentlink);
				}
				// Replace dates
				if(!($nd_datetemplate = get_option('nd_datetemplate'))) $nd_datetemplate = 'F jS';
				
				$lptime = $ptime - date('Z') + (get_option('gmt_offset') * 3600); // Get date in the WordPress time zone.
				
				$currentlink = str_replace('%date%',mysql2date($nd_datetemplate,date('Y-m-d H:i:s',$lptime)),$currentlink); // Default format
				$currentlink = preg_replace('/%date\{([^\}]*)\}%/e','mysql2date(\'$1\',date(\'Y-m-d H:i:s\',$lptime))',$currentlink); // Custom format
				
				$tag = '';
				if($bookmark[tags] != '') {
					switch($service) { // [SERVICE]
						case 0 : $nd_site_tagurl = 'http://delicious.com/'.urlencode($username).'/'; break; //delicious
						case 1 : $nd_site_tagurl = 'http://ma.gnolia.com/people/'.urlencode($username).'/tags/'; break; // ma.gnolia
						case 2 : $nd_site_tagurl = '#'; break; // Google Reader (we should never get here)
						case 3 : $nd_site_tagurl = '#'; break; // Google Bookmarks does not have a public tag url.
						case 4 : $nd_site_tagurl = '#'; break; // Reddit (we should never get here)
						case 5 : $nd_site_tagurl = '#'; break; // Yahoo pipes (we should never get here)
					}
					$tags = explode(',',$bookmark[tags]);
					foreach($tags as $t) {
						$currenttag = $nd_tagtemplate;
						$currenttag = str_replace("%tagurl%",$nd_site_tagurl.urlencode($t),$currenttag);
						if(version_compare(PHP_VERSION,'5.2.3',">=")) $currenttag = str_replace("%tagname%",htmlentities($t,ENT_QUOTES,"UTF-8",FALSE),$currenttag);
						else {
							$tempvar = @html_entity_decode($t,ENT_QUOTES,"UTF-8");
							$tempvar = htmlentities($tempvar,ENT_QUOTES,"UTF-8");
							$currenttag = str_replace("%tagname%",$tempvar,$currenttag);
						}
						$tag .= $currenttag . ' ';
					}
					if($nd_use_post_tags == 1) {
						if($newtags == '') $newtags .= $bookmark[tags];
						else $newtags .= ',' . $bookmark[tags];
					}
				} else $tag = 'none';
				$currentlink = str_replace("%tag%",$tag,$currentlink);
				
				$currentlink .= "\n";
				$newposts[$count] = $currentlink;
				$count++;
				
				// Submit depending on maximum settings.
				if($nd_limit == 1 && $count == $nd_maxcount) {
					$newposts_array[$post_count] = array($currentpost,$newposts,$newtags,$count,$datestart,$dateend,$publishedpost);
					if($nd_maxcount == 1) array_push($newposts_array[$post_count],$bookmark[title]);
					$post_count++;
					$currentpost = -1;
					$newposts = '';
					$newtags = '';
					$count = 0;
					$datestart = 0;
					$dateend = 0;
				}
			} // If filtered
		} // foreach items in feed
		$newposts_array[$post_count] = array($currentpost,$newposts,$newtags,$count,$datestart,$dateend,$publishedpost);
		$post_count++;
		
		$ccount = array(0,0,0,0);
		$cposts = array(0,0,0,0);
		$publishedunchanged = 0;
		
		for($i=0;$i<$post_count;$i++) {
			// Sleep one second to ensure that different posts have different post dates, otherwise they won't necessarily show up in the right order.
			if($i > 0) sleep(1);
			$postcode = neop_pstlcs_push_post($i,$newposts_array[$i]);
			if($postcode == 5) $publishedunchanged = 1;
			else if($postcode != 0) {
				$cposts[$postcode-1]++;
				if($i == 0 && $nd_unpublishedcount) $ccount[$postcode-1] += $newposts_array[$i][3] - $nd_unpublishedcount;
				else $ccount[$postcode-1] += $newposts_array[$i][3];
			}
		}

		if($totalcount > 0) {
			$message = "Found $totalcount new ".neop_pstlcs_adds($totalcount,'bookmark').".";
			if($filteredcount > 0) {
				if($totalcount != $filteredcount) $message .= " $filteredcount met the tag filtering criteria.";
				
				if($cposts[0] != 0) $message .= " {$ccount[0]} ".neop_pstlcs_adds($ccount[0],'bookmark')." published in {$cposts[0]} ".neop_pstlcs_adds($cposts[0],'post').".";
				if($cposts[1] != 0) $message .= " {$ccount[1]} ".neop_pstlcs_adds($ccount[1],'bookmark')." added in {$cposts[1]} new ".neop_pstlcs_adds($cposts[1],'draft').".";
				if($cposts[2] != 0) $message .= " {$ccount[2]} ".neop_pstlcs_adds($ccount[2],'bookmark')." added to {$cposts[2]} existing ".neop_pstlcs_adds($cposts[2],'draft')." and published.";
				if($cposts[3] != 0) $message .= " {$ccount[3]} ".neop_pstlcs_adds($ccount[3],'bookmark')." added in {$cposts[3]} existing ".neop_pstlcs_adds($cposts[3],'draft').".";
			} else $message = "Found $totalcount new ".neop_pstlcs_adds($totalcount,'bookmark').", but ".neop_pstlcs_adds($totalcount,'none of them met','it did not meet')." the tag filtering criteria.";
		} else $message = "No new bookmarks found.";
		
		if($publishedunchanged) $message .= " Published an unmodified draft.";
		if($automatic) $message .= " (Automatic)";
		
		$lastrun = time();		
		update_option('nd_lastrun',$lastrun);
		if($newlastupdate) update_option('nd_lastupdate',$newlastupdate);
		update_option('nd_updating',0);
		neop_pstlcs_log($message,$lastrun);
		return $message;
		
	} else { // if lastupdate
		foreach($feed->get_items() as $item) {
			$bdate = $item->get_date('Y-m-d H:i:s');; // [SERVICE] SimplePie does a pretty good job, so for now this does not depend on the service.
			$ptime = strtotime($bdate);
			if(!$dateend) $dateend = $ptime;
			else if($ptime > $dateend) $dateend = $ptime;
		}
		$lastrun = time();
		update_option('nd_lastrun',$lastrun);
		update_option('nd_lastupdate',$dateend);
		update_option('nd_updating',0);
		neop_pstlcs_log('Postalicious was run for the first time. No new bookmarks added.',$lastrun);
		return 'Postalicious was run for the first time. No new bookmarks added.';
	}
} // neop_pstlcs_post_new
endif;

if (!function_exists('neop_pstlcs_push_post')) :
function neop_pstlcs_push_post($numero,$postarray) {
	global $wpdb, $wp_db_version, $utw, $STagging;
	// Give nice name to the variables in the array.
	$postid = $postarray[0];
	$newposts = $postarray[1];
	$newtags = $postarray[2];
	$count = $postarray[3];
	$rdstart = $postarray[4];
	$rdend = $postarray[5];
	$publishedpost = $postarray[6];
	$bookmarkt = $postarray[7];
	
	if(!($upcount = get_option('nd_unpublishedcount'))) $upcount = 0;
	
	if($count == 0) return 0;
	else if($postid != -1 && $count == $upcount) $nothingnew = 1;
	else $nothingnew = 0;
	
	if($nothingnew) {
		$newposts = '';
		$newtags = '';
	}
	else $newposts = implode('', array_reverse($newposts));
	
	if(!($nd_mincount = get_option('nd_mincount'))) $nd_mincount = 5;
	
	// Create title and body
	if(!($nd_datetemplate = get_option('nd_datetemplate'))) $nd_datetemplate = 'F jS';
	
	// Fix the dates so that the date posted is based on the timezone set in the WordPress options tab
	$gmt_offset = (get_option('gmt_offset') * 3600) - date('Z');
	$ldstart = $rdstart + $gmt_offset;
	$ldend = $rdend + $gmt_offset;
	
	if(date('dmY',$ldstart) == date('dmY',$ldend)) { // Single day template
		if(!($posttitle = get_option('nd_titlesingle'))) $posttitle = 'Bookmarks for %datestart% from %datestart{H:i}% to %dateend{H:i}%';
		if(!($postbody = get_option('nd_posttsingle'))) $postbody = "<p>These are my links for %datestart% from %datestart{H:i}% to %dateend{H:i}%:</p>\n<ul>\n%bookmarks%\n</ul>";
	} else { // Two day template
		if(!($posttitle = get_option('nd_titledouble'))) $posttitle = 'Bookmarks for %datestart% through %dateend%';
		if(!($postbody = get_option('nd_posttdouble'))) $postbody = "<p>These are my links for %datestart% through %dateend%:</p>\n<ul>\n%bookmarks%\n</ul>";
	}
	
	// If we were given a draft, add the links from the draft.
	if($postid != -1)
		$postlinks = $newposts . get_option('nd_draftcontent');
	else
		$postlinks = $newposts;
	
	// Replace the dates with default format.
	$datestart = mysql2date($nd_datetemplate,date('Y-m-d H:i:s',$ldstart));
	$dateend = mysql2date($nd_datetemplate,date('Y-m-d H:i:s',$ldend));
	
	$posttitle = str_replace("%datestart%",$datestart,$posttitle);
	$posttitle = str_replace("%dateend%",$dateend,$posttitle);
	$postbody = str_replace("%datestart%",$datestart,$postbody);
	$postbody = str_replace("%dateend%",$dateend,$postbody);
	
	// Replace dates with custom format.
	$posttitle = preg_replace('/%datestart\{([^\}]*)\}%/e','mysql2date(\'$1\',date(\'Y-m-d H:i:s\',$ldstart))',$posttitle);
	$posttitle = preg_replace('/%dateend\{([^\}]*)\}%/e','mysql2date(\'$1\',date(\'Y-m-d H:i:s\',$ldend))',$posttitle);
	$postbody = preg_replace('/%datestart\{([^\}]*)\}%/e','mysql2date(\'$1\',date(\'Y-m-d H:i:s\',$ldstart))',$postbody);
	$postbody = preg_replace('/%dateend\{([^\}]*)\}%/e','mysql2date(\'$1\',date(\'Y-m-d H:i:s\',$ldend))',$postbody);
	
	// Create the post body
	$postbody = str_replace("%bookmarks%",$postlinks,$postbody);
	
	// Replace the title if we have it
	if($bookmarkt) {
		if(version_compare(PHP_VERSION,'5.2.3',">=")) $posttitle = str_replace("%title%",htmlentities($bookmarkt,ENT_QUOTES,"UTF-8",FALSE),$posttitle);
		else {
			$tempvar = @html_entity_decode($bookmarkt,ENT_QUOTES,"UTF-8");
			$tempvar = htmlentities($tempvar,ENT_QUOTES,"UTF-8");
			$posttitle = str_replace("%title%",htmlentities($tempvar,ENT_QUOTES,"UTF-8"),$posttitle);
		}
	}
	
	// Escape the body and title
	$postbody = $wpdb->escape($postbody);
	$posttitle = $wpdb->escape($posttitle);
	
	// Get everyting we need to create the post.
	if(!($nd_idforposts = get_option('nd_idforposts'))) $nd_idforposts = 1;
	if(!($nd_catforposts = get_option('nd_catforposts'))) $nd_catforposts = get_option('default_category');
	if(!($nd_allowcomments = get_option('nd_allowcomments'))) $nd_allowcomments = get_option('default_comment_status');
	if(!($nd_allowpings = get_option('nd_allowpings'))) $nd_allowpings = get_option('default_comment_status');
	if(!($nd_slugtemplate = get_option('nd_slugtemplate'))) $nd_slugtemplate = '';
	
	$categoryarray = explode(',',$nd_catforposts);
	if(!($nd_poststatus = get_option('nd_poststatus'))) $nd_poststatus = 'publish';

	$nd_use_post_tags = get_option('nd_use_post_tags');
	$nd_post_tags = get_option('nd_post_tags');
	$post_tags = $nd_post_tags;
	
	if($nd_use_post_tags == 1 || $post_tags) {
		if($post_tags) {
			$post_tags = preg_replace('/\s*,\s*/',',',$post_tags); // Remove spaces before and after commas.
			$post_tags = trim($post_tags); // Remove spaces at the start and end of the string.
			$post_tags = preg_replace('/,,+/',',',$post_tags); // Remove consecutive commas.
			$post_tags = explode(',',$post_tags);
			$tags = $post_tags;
		}
		if($nd_use_post_tags == 1) {
  			if($postid != -1) $draft_tags = get_option('nd_drafttags') . "," . $newtags;
  			else $draft_tags = $newtags;
  			$draft_tags = preg_replace('/\s*,\s*/',',',$draft_tags); // Remove spaces before and after commas.
  			$draft_tags = trim($draft_tags); // Remove spaces at the start and end of the string.
  			$draft_tags = preg_replace('/,,+/',',',$draft_tags); // Remove consecutive commas.
  			$tags = array_merge(explode(',',$draft_tags) ,(array)$post_tags);
		}
		$tags = array_flip(array_flip($tags)); // Remove duplicates
	}
	
	// Create array for wp_update_post or wp_insert_post
	$parray = array('post_title'=>$posttitle,'post_content'=>$postbody,'no_filter' => true);
	if($nd_slugtemplate != '') {
		// Replace dates with default format
		$nd_slugtemplate = str_replace("%datestart%",$datestart,$nd_slugtemplate);
		$nd_slugtemplate = str_replace("%dateend%",$dateend,$nd_slugtemplate);
		// Replace dates with custom format
		$nd_slugtemplate = preg_replace('/%datestart\{([^\}]*)\}%/e','mysql2date(\'$1\',date(\'Y-m-d H:i:s\',$ldstart))',$nd_slugtemplate);
		$nd_slugtemplate = preg_replace('/%dateend\{([^\}]*)\}%/e','mysql2date(\'$1\',date(\'Y-m-d H:i:s\',$ldend))',$nd_slugtemplate);
		// Replace the title if we have it
		if($bookmarkt) {
			if(version_compare(PHP_VERSION,'5.2.3',">=")) $nd_slugtemplate = str_replace("%title%",htmlentities($bookmarkt,ENT_QUOTES,"UTF-8",FALSE),$nd_slugtemplate);
			else {
				$tempvar = @html_entity_decode($bookmarkt,ENT_QUOTES,"UTF-8");
				$tempvar = htmlentities($tempvar,ENT_QUOTES,"UTF-8");
				$nd_slugtemplate = str_replace("%title%",htmlentities($tempvar,ENT_QUOTES,"UTF-8"),$nd_slugtemplate);
			}
		}
		// Add to the array
		$parray['post_name'] = $nd_slugtemplate;
	}
	if($postid != -1) $parray['ID'] = $postid;
	else {
		$parray['post_status'] = 'draft';
		$parray['post_author'] = $nd_idforposts;
		$parray['comment_status'] = $nd_allowcomments;
		$parray['ping_status'] = $nd_allowpings;
		$parray['post_category'] = $categoryarray;
	}
	
	// Is the post ready for prime time?
	if(!($nd_limit = get_option('nd_limit'))) $nd_limit = 0;
	switch($nd_limit) {
		case 1 : if(!($nd_maxcount = get_option('nd_maxcount'))) $nd_limit = 0; break;
		case 3 : if(!($nd_maxhours = get_option('nd_maxhours'))) $nd_maxhours = 1; break;
		case 0 : if(!($nd_maxdays = get_option('nd_maxdays'))) $nd_maxdays = 1;
				 if(!($nd_max0hour = get_option('nd_max0hour'))) $nd_max0hour = 12;
				 if(!($nd_max0mins = get_option('nd_max0mins'))) $nd_max0mins = 0;
				 if(!($nd_max0meridian = get_option('nd_max0meridian'))) $nd_max0meridian = 1;
				 break;
	}
	
	$primetime = 0;
	if($count >= $nd_mincount) {
		switch($nd_limit) {
			case 2 : $primetime = 1; break;
			case 1 : if($nd_maxcount >= $count) $primetime = 1; break;
			case 3 : if(!($nd_lastpostdate = get_option('nd_lastpostdate'))) $primetime = 1;
					 else if(($nd_lastpostdate + ($nd_maxhours * 3600)) < time()) $primetime = 1;
					 break;
			case 0 : if($lastupdate = get_option('nd_lastupdate')) {
					 	if(!($nd_lastpostdate = get_option('nd_lastpostdate'))) $primetime = 1;
					 	else {
					 		$ltime = $nd_lastpostdate - $server_gmt_offset + $gmt_offset;
							$stime = gmmktime(0,0,0,date('n',$ltime),date('j',$ltime),date('Y',$ltime));
							$stime -= $gmt_offset; // This is the time of the start of the day when the last post was published.
							if($nd_max0hour == 12) {
								if($nd_max0meridian == 1) $nd_max0hour = 0;
								else $nd_max0hour = 6;
							}
							$stime += ($nd_maxdays * 86400) + ($nd_max0hour * $nd_max0meridian * 3600) + ($nd_max0mins * 60);
							if($stime < time()) $primetime = 1;
					 	}
					 }
					 break;
		}	
	}
	
	if($primetime && !$publishedpost) $parray['post_status'] = $nd_poststatus;

	// The array is ready, use it. (Only if there's something new or if the post will be published)
	if(!$nothingnew || $primetime) {
		if($draftid == -1) $newid = wp_insert_post($parray);
		else $newid = wp_update_post($parray);
	}

	// Update the options related to the draft.
	if($primetime) { // Erase all draft information
		update_option('nd_draftcontent','');
		update_option('nd_unpublishedcount',0);
		update_option('nd_lastdraftid',-1);
		update_option('nd_draftdate','');
		update_option('nd_draftdate2','');
		if($nd_use_post_tags == 1 || $nd_post_tags) update_option('nd_drafttags','');
		update_option('nd_lastpostdate',time());
	} else if(!$nothingnew) { // Created or edited a draft. (Only change the options if there's something new)
		update_option('nd_draftcontent',$postlinks);
		update_option('nd_unpublishedcount',$count);	
		update_option('nd_draftdate',$rdstart);
		update_option('nd_draftdate2',$rdend);
		if($nd_use_post_tags == 1 || $nd_post_tags) update_option('nd_drafttags',$draft_tags);
		if($postid == -1) update_option('nd_lastdraftid',$newid); // New draft.
	}
	
	// Set up the tags for the post, if there's nothing new, only do it if the post will be published.
	// We can probably just set the tags every time, but let's be safe.
	if(($nd_use_post_tags == 1 || $nd_post_tags) && !empty($tags) && (!$nothingnew || $primetime)) {
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
			if( $STagging && $primetime ) {
				$stvals = "('$newid','".implode("'),('$newid','",$tags)."')";
				$wpdb->query("INSERT IGNORE INTO {$STagging->info['stptable']} VALUES $stvals");
			}
		}
	}

	if($postid == -1) {
		if($primetime) $postcode = 1; // Published a new post.
		else $postcode = 2; // Created a new draft.
	} else {
		if($primetime) {
			if($nothingnew) $postcode = 5; // Published an unchanged draft.
			else $postcode = 3; // Published a draft.
		}
		else {
			if($nothingnew) $postcode = 0; // Nothing was done.
			else $postcode = 4; // Added to an existing draft.
		}
	}
	return $postcode;
}
endif;

if (!function_exists('neop_pstlcs_adds')) :
function neop_pstlcs_adds($number,$singular,$plural=false) {
	if($plural === false) $plural = $singular . 's';
	if($number == 1) return $singular;
	else return $plural;
}
endif;

if (!function_exists('neop_pstlcs_geturl')) :
function neop_pstlcs_geturl() {
	$url = 'http';
	
	if ($_SERVER["HTTPS"] == "on") $url .= "s";
	
	$url .= "://";
	
	if ($_SERVER["SERVER_PORT"] != "80")
		$url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	else
		$url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

	return $url;
}
endif;

if (!function_exists('neop_pstlcs_log')) :
function neop_pstlcs_log($string,$time) {
	if(!($log = get_option('nd_log'))) $log = '';
	if(!($logcount = get_option('nd_logcount'))) $logcount = 0;

	$time = $time + (get_option('gmt_offset') * 3600) - date('Z');
	
	if($logcount >= 200) {
		$position = strripos($log,"\n");
		$log = "[".date('Y-m-d H:i:s',$time)."] $string\n" . substr($log, 0, $position);
	} else {
		if($log == '') $log = "[".date('Y-m-d H:i:s',$time)."] $string";
		else $log = "[".date('Y-m-d H:i:s',$time)."] $string\n" . $log;
		update_option('nd_logcount',$logcount + 1);
	}
	
	update_option('nd_log',$log);
}
endif;

// Add strripos if PHP4 is being used.
if (function_exists('strripos') == false) :
function strripos($haystack, $needle) {
	$pos = strlen($haystack) - strpos(strrev($haystack), strrev($needle));
	if ($pos == strlen($haystack)) { $pos = 0; }
	return $pos;
}
endif;

// Useful little helper function, works like $var[param_1]...[param_n]
if(!function_exists('neop_pstlcs_arrelm') == false) :
function neop_pstlcs_arrelm($var) {
	for ($i = 0; $i < func_num_args()-1; $i++) $var = $var[func_get_arg($i+1)];
     return $var;
}
endif;

if (!function_exists('neop_pstlcs_user_deleted')) :
function neop_pstlcs_user_deleted($deletedid) {
	if(!($nd_idforposts = get_option('nd_idforposts'))) $nd_idforposts = 1;
	if($deletedid == $nd_idforposts) update_option('nd_idforposts',1);
	
}
endif;

if (!function_exists('neop_pstlcs_category_deleted')) :
function neop_pstlcs_category_deleted($deletedid) {
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
endif;

if (!function_exists('neop_deactivate_del')) :
function neop_deactivate_del() {
	wp_clear_scheduled_hook('nd_hourly_update');
	update_option('nd_hourlyupdates',0);
	update_option('nd_updating',0);
}
endif;

add_action('admin_menu', 'neop_pstlcs_add_options');
add_action('delete_user', 'neop_pstlcs_user_deleted');
add_action('delete_category', 'neop_pstlcs_category_deleted');
add_action('nd_hourly_update', 'neop_pstlcs_post_new');
register_deactivation_hook(__FILE__, 'neop_deactivate_del');
?>