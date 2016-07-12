=== Plugin Name ===
Contributors: f13dev
Donate link: http://f13dev.com/wordpress-plugin-github-repo-shortcode/
Tags: github, repo, repository, profile, code, programmer
Requires at least: 3.0.1
Tested up to: 4.5.3
Stable tag: 1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add a snapshot of your GitHub repository to any page or post on your WordPress blog.

== Description ==

If you are a programmer who uses GitHub then why not share your coding projects via your WordPress blog by adding a snapshot
of your repository on any page or post using shortcode.

Simply install the plugin and add the shortcode [gitrepo author="author" repo="repo"] to the desired location; changing the attributes to that of your desired repository.

Features include:

* Cached using Transient
* Styles appearance with a GitHub banner
* Shows the repository description
* Adds a link to the repository on GitHub
* Shows statistics for Forks, Stars, Open Issues
* Provides a link to the latest tag (if one exists)
* Displays the code to clone the repository
* Can be used with or without a GitHub API Token

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Add the shortcode [wpplugin author="author" repo="repo"] to the desired location
4. If desired, add a GitHub API Token and/or alter the cache timeout on the admin page admin->GitHub Settings.

== Frequently Asked Questions ==

= Is a GitHub API token required =

No... Although it is recommended, especially if you wish to have the cache timeout set to a low value, or if you use multiple instances of the shortcode.

== Screenshots ==

1. An example showing the results of the shortcode [gitrepo author="wordpress" repo="wordpress"]

== Changelog ==

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.0 =
* Initial release
