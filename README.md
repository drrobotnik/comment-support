=== Comment Support ===
Contributors: Brandon Lavigne
Donate link: http://caavadesign.com/
Tags: comments, spam
Requires at least: 3.5.1
Tested up to: 3.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin extends the user comment form for a custom post type and allows the registered user to attach files to the comment.

== Description ==

The default post type that allows file attachments is 'support'. You can apply this plugin to any custom post type by adding add_action('clients_post_type', 'your-post-type'), but you'll need to add register_post_type() to your functions.php file.

comment_text() must be included in your theme for the attachment html to be visible in the comment list.

You can modify the html output by modifying the filters cs_attachments_title, cs_before_attachments, and cs_after_attachments.