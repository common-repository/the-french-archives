<?php
/*
Plugin Name: The French Archives
Plugin URI: http://www.movingtofreedom.org/10/07/17/wordpress-plugin-month-grid-and-all-posts-archive
Description: Display archives in a month grid with post counts, based on <a href="http://rmarsh.com/plugins/compact-archives/">Rob Marsh's "Compact Monthly Archive"</a> (which was based on <a href="http://justinblanton.com/projects/smartarchives/">Justin Blanton's "Smart Archive"</a>), and display a list of all posts, based on <a href="http://wpguy.com/articles/an-archives-page-with-all-the-posts-in-cronological-order/">template code by Wessley Roche</a>.
Version: 1.1
Author: Scott Carpenter
Author URI: http://www.movingtofreedom.org
License: GPL2
*/

/*
	Copyright 2006  Rob Marsh, SJ  (http://rmarsh.com)
	Copyright 2010 Scott Carpenter (scottc@movingtofreedom.org)

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
4/10/10 Scott Carpenter Notes

Code has come from several sources: Me, Rob Marsh, Justin Blanton,
and Wessley Roche. I'm thinking the whole thing should qualify as GPL v2+.

Code from:
	http://rmarsh.com/plugins/compact-archives/
		Which points to source:
			http://justinblanton.com/projects/smartarchives/
	http://wpguy.com/articles/an-archives-page-with-all-the-posts-in-cronological-order/

The "all posts" code was originally written to be included in a template. I've
made it into a function here, added an option to use a table, and also to
show comment counts.

The "compact archive" code (now called "month grid archive") has also been modified
to use a table, and shows the post count for each month, along with some totals.

Renaming compact.php to the_french_archives.php
(A silly name that has nothing to do with France or the French.)
*/

/*
Display the monthly archive of posts in a grid form, with post counts and totals

      Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec
2010    2   4   2  21   4   2   3                       38
2009    2   7   5   5   3   1   9   3   5   5   7   5   57
                                                        95
Month column headers, $style=:

abbr (default): Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec
                (using wp-config.php locale)

initial: J F M A M J J A S O N D

numeric: 01 02 03 04 05 06 07 08 09 10 11 12

usage:
		<?php month_grid_archive(); ?>
*/
function month_grid_archive($style='abbr', $first_header='Monthly')
{
	global $wpdb, $wp_version;
	setlocale(LC_ALL,WPLANG); // set localization language

	$year_results = $wpdb->get_results("SELECT distinct year(post_date) as year
		FROM $wpdb->posts
		WHERE post_status='publish'
		AND post_password=''
		AND post_type='post'
		ORDER BY year desc");

	if (!$year_results) {
		echo '<h2>Archive is empty</h2>';
	}
	if (!empty($first_header)) {
		$first_header = '<tr><th class="fa-hdr1" colspan="14">' .
		                "$first_header</th></tr>\n";
	}
	echo '<table id="fa-month-grid" class="fa-table">' . "\n$first_header<tr><th>&nbsp;</th>\n";

	for ( $month = 1; $month <= 12; $month += 1) {
		$dummydate = strtotime("$month/01/2001");
		// get the month name; strftime() localizes
		$month_name = strftime("%B", $dummydate);
		switch ($style) {
			case 'initial':
				$month_hdr = $month_name[0]; // the inital of the month
				break;
			case 'numeric':
				$month_hdr = strftime("%m", $dummydate); // get the month number, e.g., '04'
				break;
			default:
				$month_hdr = strftime("%b", $dummydate); // get the short month name; strftime() localizes
		}
		echo '<th class="fa-hdr2">' . $month_hdr . "</th>\n";
	}
	echo "<th>&nbsp;</th></tr>\n";

	$grand_total = 0;
	$num_years = 0;
	foreach ($year_results as $year_result) {
		$num_years += 1;
		$year = $year_result->year;
		if ($year == 0) continue;
		echo '<tr><td class="fa-year"><a href="' . get_year_link($year) . '">' . $year . "</a></td>\n";
		$year_total_post_count = 0;

		for ( $month = 1; $month <= 12; $month += 1) {
			$num_posts_in_month = $wpdb->get_var("SELECT count(*)
				FROM $wpdb->posts
				WHERE year(post_date)='$year'
				AND month(post_date)='$month'
				AND post_status='publish'
				AND post_password=''
				AND post_type='post'");

			$year_total_post_count += $num_posts_in_month;

			$dummydate = strtotime("$month/01/2001");
			// get the month name; strftime() localizes
			$month_name = strftime("%B", $dummydate);

			if ($num_posts_in_month > 0) {
				echo '<td class="fa-count"><a href="' . get_month_link($year, $month) . '" title="' . $month_name . ' ' . $year . '">' . $num_posts_in_month . "</a></td>\n";
			} else {
				echo "<td>&nbsp;</td>\n";
			}
		}
		echo '<td class="fa-total">' . $year_total_post_count . "</td></tr>\n";
		$grand_total += $year_total_post_count;
	}
	if ($num_years > 1) {	// only show grand total if more than one year
		echo '<tr><th colspan="13">&nbsp;</th><th class="fa-total">' . $grand_total . "</th></tr>\n";
	} else {
		echo '<tr><th colspan="14">&nbsp;</th></tr>' . "\n";
	}
	echo "</table>\n";
}

/*
Shows all posts in descending order, in a table or in a list.
	
usage:
		<?php all_posts_archive(); ?>

4-19-10 sc -- adding code for rowspan to group multiple entries in one day
           -- makes things complicated but looks so much nicer
*/
function all_posts_archive($use_table=true, $show_comment_counts=true)
{
	$previous_year = $year = 0;
	$previous_month = $month = 0;
	$previous_day = $day = 0;
	$day_array = array();
	$num_posts_in_day = 0;
	$num_comments = "";
	$ul_open = false;

	$myposts = get_posts('numberposts=-1&orderby=post_date&order=DESC');

	$top_of_table = true;
	if ($use_table) {
		echo '<table id="fa-all-posts" class="fa-table">' . "\n";
	}
	
	foreach ($myposts as $post) {

		//setup_postdata($post);	//needed for current functionality

		$year = mysql2date('Y', $post->post_date);
		$month = mysql2date('n', $post->post_date);
		$day = mysql2date('j', $post->post_date);

		if ($year != $previous_year || $month != $previous_month) {

			if ($ul_open && !$use_table) {
			echo '</ul>';
			}
			if ($use_table) {
				if (!$show_comment_counts) {
					$the_colspan = '2';
				} else if ($top_of_table) {
					$top_of_table = false;
					$the_colspan = '2';
					$comment_hdr = "\n\t" . '<th class="fa-hdr3 fa-hdr-comment"><abbr title="Comments">C</abbr></th>';
				} else {
					$the_colspan = '3';
					$comment_hdr = '';
				}
			// old simple way, just echo it
			//echo '<tr><th cla2ss="lft" colspan="' . $the_colspan . '">' . date('F Y', strtotime($post->post_date)) . "</th>$comment_hdr</tr>";

			// new! complicated and gnarly way -- set this to be printed below after final day
			// from previous month (with final day meaning the earliest entry of the month)
			$month_header = "<tr>\t" . '<th class="fa-hdr3" colspan="' . $the_colspan . '">' . date('F Y', strtotime($post->post_date)) . "</th>$comment_hdr</tr>\n";
			} else {
				echo '<span class="fa-hdr4">' . date('F Y', strtotime($post->post_date)) . "</span>\n";
			}

			if (!$use_table) {
				echo '<ul class="fa-ul" >' . "\n";
				$ul_open = true;
			}
		} // end new year/month check

		if ($show_comment_counts) {
			$num_comments = $post->comment_count;
			if ($num_comments > 0) {
				if ($use_table) {
					$num_comments = "\n\t" . '<td class="fa-cmt"><a href="' . get_permalink($post->ID) .
									"#comments\">\n\t$num_comments</a></td>";
				} else {
					$num_comments = ' <span class="fa-cmt">[<a href="' . get_permalink($post->ID) . "#comments\">$num_comments</a>]</span>";
				}
			} else {
				if ($use_table) {
					$num_comments = "\n\t<td></td>";
				} else {
					$num_comments = '';
				}
			}
		}
		if ($use_table) {
			// old simple way where we'd just print day number for each row when multiple rows in same day
			// echo '<tr><td class="fa-day"><b>' . $day . '</b></td><td><a href="' .
			//		get_permalink($post->ID) . '">' . $post->post_title . "</a>$num_comments</tr>\n";

			if ($day != $previous_day || $month != $previous_month || $year != $previous_year) {
				// print array from previous day
				print_all_posts_day_array($day_array, $num_posts_in_day, $previous_day);
				$num_posts_in_day = 0;
				$day_array = array();
			}
			if ($month_header) {
				echo $month_header;
				$month_header = NULL;
			}
			// add title and comments columns to array
			$day_array[$num_posts_in_day] = '<td><a href="' . get_permalink($post->ID) .
											'">' . "\n\t" . $post->post_title . "</a></td>$num_comments</tr>\n";
			$num_posts_in_day++;

		} else {
			echo '<li><span class="fa-day">' . $day . '</span> <a href="' . get_permalink($post->ID) . '">' . $post->post_title . "</a>$num_comments</li>\n";
		}
		$previous_year = $year;
		$previous_month = $month;
		$previous_day = $day;		// only used in <table> code
	} /* end foreach($myposts as $post) */

	if ($use_table == true) {
		// print final array
		print_all_posts_day_array($day_array, $num_posts_in_day, $previous_day);
		echo "</table>\n";
	} else {
		echo "</ul>\n";
	}
} //end all posts function
function print_all_posts_day_array($day_array, $num_posts_in_day, $previous_day)
{
	$first_rec = true;
	foreach ($day_array as $day_entry) {
		if ($first_rec) {
			$first_rec = false;
			echo "<tr>\t" . '<td class="fa-day" rowspan="' . $num_posts_in_day . '">' .
				 $previous_day . "</td>\n\t$day_entry";
		} else {
			echo "<tr>\t$day_entry";
		}
	}
}
?>
