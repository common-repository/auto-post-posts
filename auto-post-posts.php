<?php
/*
	Plugin Name: Auto Post Posts
	Plugin URI: http://www.keklabprogramming.com/auto-post-posts.zip
	Description: Adds the option to automatically submit your drafted posts
	Author: Henric Johansson, henric-johansson@hotmail.com
	Version: 1.0.1
	Author URI: http://www.keklabprogramming.com
	Text Domain: auto-post-posts
	Domain Path: /

    Copyright 2011  Henric Johansson  (email : henric-johansson@hotmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

#To allow this to be as extensible as possible, make sure $table_prefix is globalised, we also need the $wpdb class functions too
global $table_prefix, $wpdb;
#Create the 'name' of our table which is prefixed by the standard WP table prefix (which you specified when you installed WP)
$wp_autopost = $table_prefix . "autopost";
#Check to see if the table exists already, if not, then create it
if($wpdb->get_var("show tables like '$wp_autopost'") != $wp_autopost) {
	$cTable = "CREATE TABLE  `$wp_autopost` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`time_posted` DATETIME NOT NULL
			) ENGINE = INNODB;";
	#We need to include this file so we have access to the dbDelta function below (which is used to create the table)
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($cTable);
}

function app_options_page()
{
	if(isset($_POST['app_time'])) {
		$app_time = $_POST['app_time'];

		if(is_numeric($app_time))
			update_option('app_time_per_post', $app_time);
		else 
			$terr = true;
	}

	if(isset($_POST['app_activate'])) {
		if($_POST['app_activate'] == "act") {
			//Checkbox was checked.
			update_option('app_activated', '1');
		} else {
			update_option('app_activated', '0');
		}
	}

	if(isset($_POST['app_firstin'])) {
		if($_POST['app_firstin'] == 'act') {
			update_option('app_firstin', 1);
		} else {
			update_option('app_firstin', 0);
		}
	}

	$app_time = get_option('app_time_per_post');
	$app_activated = get_option('app_activated');
	$app_firstin = get_option('app_firstin');


echo <<<END
<div class="wrap" >
	<h2>Auto Post Posts 1.0</h2>
			
	
	 <div id="mainblock" style="width:710px">
		<div class="dbx-content">
			<div style="border: 1px solid #cacaca; padding: 10px;">
		 	<form action="" method="post">
		 	<h3 style="background: #cacaca; border: 1px solid #9d9d9d;">Time between posts (in seconds, 3600 = 1 hour, 7200 = 2 hours etc.):</h3>
		 	<input type="text" name="app_time" value="$app_time"/><br /><br />
			<input type="submit" value="Change Time" />
END;
	if(isset($terr)) 
		echo '<br />You may only enter numbers';
echo <<<END
			</form>
			</div>

			<br /><br />
			<div style="border: 1px solid #cacaca; padding: 10px;">
			<h3 style="background: #cacaca; border: 1px solid #9d9d9d;">Activate Auto Posting (activating this will make the plugin start posting your drafted posts):</h3>
			<form action="" method="post">
END;
	if($app_activated == 1) {
		echo '<input type="radio" name="app_activate" value="act" checked/> Yes - activate auto posting <br />
			<input type="radio" name="app_activate" value="dea" /> No - deactivate auto posting<br /><br />
			<input type="submit" value="Change!" />';
	} else {
		echo 'Yes - activate auto posting <input type="radio" name="app_activate" value="act" /><br />
			No - deactivate auto posting <input type="radio" name="app_activate" value="dea" checked/><br />
			<input type="submit" value="Change!" />';
	}
echo <<<END
			</form>
			</div><br /><br />
			<div style="border: 1px solid #cacaca; padding: 10px;">
			<h3 style="background: #cacaca; border: 1px solid #9d9d9d;">First in first out (latest post gets posted latest):</h3>
			<form action="" method="post">
END;
	if($app_firstin == 1) {
		echo '<input type="radio" name="app_firstin" value="act" checked/> Yes - first in first out <br />
			<input type="radio" name="app_firstin" value="dea" /> No - first in last out<br /><br />
			<input type="submit" value="Change!" />';
	} else {
		echo '<input type="radio" name="app_firstin" value="act" /> Yes - first in first out <br />
			 <input type="radio" name="app_firstin" value="dea" checked/> No - first in last out<br />
			<input type="submit" value="Change!" />';
	}
echo <<<END
			</form>
			</div><br /><br />
			<div style="border: 1px solid #cacaca; padding: 10px;">
					<h3 style="background: #cacaca; border: 1px solid #9d9d9d;">Latest News:</h3>
END;
					echo file_get_contents('http://www.keklabprogramming.com/app_news.php');
echo <<<END
				</div>
   		</div>
   	</div>


<h5>A Wordpress plugin written by <a href="http://www.keklabprogramming.com/">Henric Johansson</a></h5>
<br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="RYG6UC4V2E9SG">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<br /><br />
Latest Donors:<br />
END;
echo file_get_contents('http://www.keklabprogramming.com/donor.php');
echo <<<END

</div>
END;
}

function app_autopost()
{
	if(get_option('app_activated') >= 1) {
		$DraftPosts = mysql_num_rows(mysql_query("select * from wp_posts where post_status='draft' order by post_date desc"));
		//If we have drafted posts
		if($DraftPosts > 0) {

			$LastPostExists = mysql_num_rows(mysql_query("select time_posted from wp_autopost order by time_posted desc"));

			if($LastPostExists == 0) {
				//If there was no last post, create one.
				$query = "insert into wp_autopost (time_posted) values (NOW())";
				mysql_query($query);
			}

			//Get timediff
			$query = "select TIMESTAMPDIFF(SECOND, time_posted, NOW()) as timed from wp_autopost order by time_posted desc";
			$row = mysql_fetch_array(mysql_query($query));

			$app_time = get_option('app_time_per_post');

			if($row['timed'] >= $app_time) {
				//Update latest post time to now
				$query = "insert into wp_autopost (time_posted) values (NOW())";
				mysql_query($query);
				//1 hour has passed, process the post.
				//get the ID

				$firstIn = get_option('app_firstin');

				if($firstIn == 1) {
					$asc = "asc";
				} else {
					$asc = "desc";
				}
				$query = "select id, post_title, guid from wp_posts where post_status='draft' order by post_date " . $asc;
				$id = mysql_fetch_array(mysql_query($query));

				wp_publish_post($id['id']);
				// Update post_name
				$my_post = array();
				$my_post['ID'] = $id['id'];
				$now = date("Y-m-d H:i:s");
				$now = strtotime($now . " + 2 hour");
				$my_post['post_date'] = date("Y-m-d H:i:s", $now);
				$my_post['post_date_gmt'] = date("Y-m-d H:i:s");

				// Update the post into the database
		  		wp_update_post( $my_post );
			}
		}
	}
}

function app_install()
{
	if(!get_option('app_time_per_post')) {
		add_option('app_time_per_post', 60);
	}
	if(!get_option('app_activated')) {
		add_option('app_activated', 1);
	}
	if(!get_option('app_firstin')) {
		add_option('app_firstin', 1);
	}
}

function app_options()
{
	add_options_page('Auto Post Options', 'Auto Post Posts', 8, __FILE__, 'app_options_page');            
}

// HOOKS
add_action('admin_menu', 'app_options');
add_action('plugins_loaded', 'app_install');
add_action('wp_head', 'app_autopost');

?>