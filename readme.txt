=== Plugin Name ===
Contributors: scarpenter
Donate link: https://my.fsf.org/donate
Tags: archives, month grid, all posts
Requires at least: 2.6
Tested up to: 3.0.1
Stable tag: 1.1

Displays archives in a month grid with post counts for each month, with yearly and grand totals, and shows titles and comment counts for all posts.

== Description ==

See also: http://www.movingtofreedom.org/2010/07/17/wordpress-plugin-month-grid-and-all-posts-archive/

Gives you two different kinds of archives:

**Month Grid Archive**

Shows monthly post counts, year totals, and a grand total in a grid/table format, with links to month and year archives. Like this (see also the screenshot!):

`      Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec`
`2010    2   4   2  21   4   2   3                       38`
`2009    2   7   5   5   3   1   9   3   5   5   7   5   57`
`                                                        95`

If posts all fall within one calendar year, the grand total won't be shown. (The trailing table row will remain.)

Call from a template file with:

`<?php month_grid_archive() ?>`

Two optional parameters:

* `$style = 'abbr'`
* `$first_header = 'Monthly'`

By default for `$style`, will show month abbreviations (`abbr`) in the header row, e.g. "Jan" and "Feb". Can also show first `'initial'`, e.g. "J" and "F", and `'numeric'`. (`abbr` and `initial` use `wp-config.php locale.)

You can change the first header row from the default of "Monthly", or remove it altogether by passing in an empty string.

For example, to show numeric month headers and suppress the first header:

`<?php month_grid_archive('numeric', '') ?>`

**All Posts Archive**

Show all post titles in descending order along with comment counts (optionally).

Call from template file with:

`<?php all_posts_archive() ?>`

Two optional parameters:

* `$use_table = true`
* `$show_comment_counts` = true`

By default, posts will be displayed in a table (as shown in screenshot), or as a `<ul><li>` list.

Comment counts are included by default but may be suppressed.

For example, to show all posts in a table without comment counts/links:

`<?php all_posts_archive(true, false) ?>`

**CSS**

For example, screenshots were produced from these stylesheet classes/IDs:

`.fa-table { border: 2px solid #cebb90; border-collapse: collapse;
            margin-left: auto; margin-right: auto;}
.fa-table td,th { border: 1px solid #cebb90;
                  padding: 5px; vertical-align: top; }
.fa-table th { border: 2px solid #cebb90; background: #f3e8cc; }
#fa-month-grid { width: 100%; }
.fa-hdr1, .fa-hdr3 { text-align: left; padding: 6px; }
.fa-hdr2 { font-size: .8em; }
.fa-count { font-size: .9em; }
.fa-count, .fa-total, .fa-day, .fa-cmt { text-align: right; }
#fa-all-posts { margin-top: 1.5em; margin-left: auto;
                                   margin-right: auto; }
.fa-day { font-weight: bold; }`

Look at the code or the generated HTML to see where classes are referenced.

== Installation ==

1. Upload `the_french_archives.php` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add `<?php month_grid_archive(); ?>` and/or `<?php all_posts_archive(); ?>` to your template file.

== Frequently Asked Questions ==

= I'm getting a fatal memory error! =

That's not really a question. Maybe try, "Why is your plugin causing a fatal memory error?"

= Why is your crummy plugin causing a fatal memory error? =

(I *hope* this isn't a frequent occurrence for y'all.) I haven't had any problems on my web host, but the "all posts" archive caused this error with my local development instance of WordPress:

`Fatal error: Allowed memory size of 33554432 bytes  
exhausted (tried to allocate 64 bytes) in  
/home/scarpent/yada/yada/www/wp-includes/meta.php on line 307`

One solution I found suggested adding to the `wp-config.php` file:

`define('WP_MEMORY_LIMIT', '64M');`

Which fixed the problem for me.

= What's so French about this thing? =

It has nothing to do with France or the French.

== Screenshots ==

1. Month grid archive.
2. All posts archive.

== Changelog ==

= 1.1 =
* initial version

== Upgrade Notice ==
* nothing
