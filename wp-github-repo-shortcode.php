<?php
/*
Plugin Name: GitHub Repository Shortcode
Plugin URI: http://f13dev.com
Description: This plugin enables you to enter shortcode on any page or post in your blog to show information and statistics about a repository on GitHub.
Version: 1.0
Author: Jim Valentine - f13dev
Author URI: http://f13dev.com
Text Domain: wp-github-repo-shortcode
License: GPLv3
*/

/*
Copyright 2016 James Valentine - f13dev (jv@f13dev.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// Register the shortcode
add_shortcode( 'wpplugin', 'f13_github_repo_shortcode');
// Register the css
add_action( 'wp_enqueue_scripts', 'f13_github_repo_style');

// Handle the shortcode
function f13_github_repo_shortcode( $atts, $content = null )
{
    // Get the attributes
    extract( shortcode_atts ( array (
        'author' => 'none',
        'repo' => 'none' // Default slug won't show a plugin
    ), $atts ));

    // Check that the author and/or repo have been set
    if ($author != null || $repo != null)
    {
        $token = '';
        // Generate the API results for the repository
        $repository = f13_get_github_api('https://api.github.com/repos/' . $author . '/' . $repo, $token);
        // Generate the API results for the tags
        $tags = f13_get_github_api('https://api.github.com/repos/' . $author . '/' . $repo . '/tags', $token)
        // Send the api results to be formatted
        return f13_format_github_repo($repository, $tags);
    }
    else
    {
        return 'The author and repo attributes are required, enter [gitrepo author="anAuhor" repo="aRepo"] to use this shortcode.';
    }
}

// Add the stylesheet
function f13_github_repo_style()
{
    wp_register_style( 'f13github-style', plugins_url('css/f13_github.css', __FILE__));
    wp_enqueue_style( 'f13github-style' );
}

/**
 *
 */
/**
 * A function to retrieve the repository information via
 * the GitHub API.
 * @param  $author The author of the GitHub repository
 * @param  $repo   The name of the GitHub repository
 * @param  $token  The API token used to access the GitHub API
 * @return         A decoded array of information about the GitHub repository
 */
 private function f13_get_github_api($url, $token)
 {
     // Start curl
     $curl = curl_init();
     // Set curl options
     curl_setopt($curl, CURLOPT_URL, $url);
     curl_setopt($curl, CURLOPT_HTTPGET, true);

     // Check if a token is set
     if (preg_replace('/\s+/', '', $token) != '' || $token != null)
     {
         // If a token is set attempt to send it in the header
         curl_setopt($curl, CURLOPT_HTTPHEADER, array(
             'Content-Type: application/json',
             'Accept: application/json',
             'Authorization: token ' . $token
         ));
     }
     else
     {
         // If no token is set, send the header as unauthenticated,
         // some features may not work and a lower rate limit applies.
         curl_setopt($curl, CURLOPT_HTTPHEADER, array(
             'Content-Type: application/json',
             'Accept: application/json'
         ));
     }
     // Set the user agent
     curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
     // Set curl to return the response, rather than print it
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

     // Get the results
     $result = curl_exec($curl);

     // Close the curl session
     curl_close($curl);

     // Decode the results
     $result = json_decode($result, true);

     // Return the results
     return $result;
 }

/**
 * A function to convert an array of informatino regarding a GitHub
 * repository and return a HTML & CSS formatted widget.
 * @param  [type] $results An array of information regarding a GitHub repository
 * @return [type]          A HTML formatted string of information regarding a GitHub repository
 */
 private function f13_format_github_repo($repository, $tags)
 {
 }
