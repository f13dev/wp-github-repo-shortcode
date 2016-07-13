<?php
/*
Plugin Name: GitHub Repository Shortcode
Plugin URI: http://f13dev.com
Description: This plugin enables you to enter shortcode on any page or post in your blog to show information and statistics about a repository on GitHub.
Version: 1.0
Author: Jim Valentine - f13dev
Author URI: http://f13dev.com
Text Domain: f13-github-repo-shortcode
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
add_shortcode( 'gitrepo', 'f13_github_repo_shortcode');
// Register the css
add_action( 'wp_enqueue_scripts', 'f13_github_repo_style');
// Register the admin page
add_action('admin_menu', 'f13_grs_create_menu');

/**
 * The main function to create the shortcode results
 * storing or retreiving the data from cache
 * @param  [type] $atts    [description]
 * @param  [type] $content [description]
 * @return [type]          [description]
 */
function f13_github_repo_shortcode( $atts, $content = null )
{

    // Get the attributes
    extract( shortcode_atts ( array (
        'author' => 'none',
        'repo' => 'none' // Default slug won't show a plugin
    ), $atts ));

    // Set the cache name for this instance of the shortcode
    $cache = get_transient('wpgrs' . md5(serialize($atts)));

    if ($cache)
    {
        // If the cache exists, return it rather than re-creating it
        return $cache;
    }
    else
    if ($author != null || $repo != null)
    {
        // Get the plugin settings variables for timeout and token
        $timeout = esc_attr( get_option('cache_timeout')) * 60;
        // If the timeout is set to 0, or is not a number, change it to 1 second, so effectively not
        // caching, but saves a cache that doesnt timeout if the setting is changed later
        if ($timeout == 0 || !is_numeric($timeout))
        {
            $timeout = 1;
        }
        $token = esc_attr( get_option('token'));
        // If the cache doesn't exist, create it and return the shortcode
        // Generate the API results for the repository
        $repository = f13_get_github_api('https://api.github.com/repos/' . $author . '/' . $repo, $token);
        // Generate the API results for the tags
        $tags = f13_get_github_api('https://api.github.com/repos/' . $author . '/' . $repo . '/tags', $token);
        // Get the response of creating the shortcode
        $response = f13_format_github_repo($repository, $tags);
        // Store the output of the shortcode into the cache
        set_transient('wpgrs' . md5(serialize($atts)), $response, $timeout);
        // Return the response
        return $response;
    }
    else
    {
        return 'The author and repo attributes are required, enter [gitrepo author="anAuhor" repo="aRepo"] to use this shortcode.';
    }
}

/**
 * A function to register the stylesheet
 * @return [type] [description]
 */
function f13_github_repo_style()
{
    wp_register_style( 'f13github-style', plugins_url('wp-github-repo-shortcode.css', __FILE__) );
    wp_enqueue_style( 'f13github-style' );
}

/**
 * A function to retrieve the repository information via
 * the GitHub API.
 * @param  $author The author of the GitHub repository
 * @param  $repo   The name of the GitHub repository
 * @param  $token  The API token used to access the GitHub API
 * @return         A decoded array of information about the GitHub repository
 */
 function f13_get_github_api($url, $token)
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
function f13_format_github_repo($repository, $tags)
 {

     $latestTag = f13_get_github_latest_tag($tags);
     if ($latestTag != 'None')
     {
         $latestTag = '<a href="https://github.com/' . $repository['full_name'] . '/releases/tag/' . $latestTag . '">' . $latestTag . '</a>';
     }
     $string = '
     <div class="gitContainer">
        <div class="gitHeader">
            <span class="gitTitle">
                <a href="' . $repository['html_url'] . '">'. $repository['name'] . '</a>
            </span>
        </div>';
        if ($repository['description'] != null)
        {
            $string .= '
                <div class="gitDescription">
                    ' . $repository['description'] . '
                </div>';
        }
        $string .='
        <div class="gitLink">
            <a href="' . $repository['html_url'] . '">' . $repository['html_url'] . '</a>
        </div>
        <div class="gitStats">
            <div class="gitForks">
                Forks: ' . $repository['forks_count'] . '
            </div>
            <div class="gitStars">
                Stars: ' . $repository['stargazers_count'] . '
            </div>
            <div class="gitOpenIssues">
                Open issues: ' . $repository['open_issues_count'] . '
            </div>
            <div class="gitLatestTag">
                Latest tag: ' . $latestTag . '
            </div>
        </div>
        <div class="gitClone">
            git clone ' . $repository['clone_url'] . '
        </div>
     </div>
     ';
     return $string;
 }

/**
 * A function to return the latest tag name
 * if one or more tags exist, otherwise returns
 * none.
 * @param  [type] $tags [description]
 * @return [type]       [description]
 */
function f13_get_github_latest_tag($tags)
{
    if (count($tags) > 0)
    {
        return $tags[0]['name'];
    }
    else
    {
        return 'None';
    }
}

/**
 * Functions to create the backend
 */

/**
 * A function to register the menu page
 * @return [type] [description]
 */
function f13_grs_create_menu()
{
    // Create the top-level menu
    add_options_page('F13Devs GitHub Repo Shortcode Settings', 'F13 GitHub Repo Shortcode', 'administrator', 'f13-github-repo-shortcode', 'f13_grs_settings_page');
    // Retister the Settings
    add_action( 'admin_init', 'f13_grs_settings');
}

/**
 * A function to register the settings group
 * @return [type] [description]
 */
function f13_grs_settings()
{
    // Register settings for token and timeout
    register_setting( 'f13-grs-settings-group', 'token');
    register_setting( 'f13-grs-settings-group', 'cache_timeout');
}

/**
 * A function to create the settings page
 * @return [type] [description]
 */
function f13_grs_settings_page()
{
?>
    <div class="wrap">
        <h2>GitHub Settings</h2>
        <p>
            Welcome to the settings page for GitHub Repo Shortcode.
        </p>
        <p>
            This plugin can be used without an API token, although it is recommended to use one as the number of API calls is quite restrictive without one.
        </p>
        <p>
            To obtain a GitHub API token:
            <ol>
                <li>
                    Log-in to your GitHub account.
                </li>
                <li>
                    Visit <a href="https://github.com/settings/tokens">https://github.com/settings/tokens</a>.
                </li>
                <li>
                    Click the 'Generate new token' button at the top of the page/
                </li>
                <li>
                    Re-enter your GitHub password for security.
                </li>
                <li>
                    Enter a description, such as 'my wordpress site'.
                </li>
                <li>
                    Click the 'Generate token' button at the bottom of the page, no other setting changes are required.
                </li>
                <li>
                    Copy and paste your new API token. Please note, your access token will only be visible once.
                </li>
            </ol>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields( 'f13-grs-settings-group' ); ?>
            <?php do_settings_sections( 'f13-grs-settings-group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        GitHub Token
                    </th>
                    <td>
                        <input type="password" name="token" value="<?php echo esc_attr( get_option( 'token' ) ); ?>" style="width: 50%;"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        Cache timeout (minutes)
                    </th>
                    <td>
                        <input type="number" name="cache_timeout" value="<?php echo esc_attr( get_option( 'cache_timeout' ) ); ?>" style="width: 75px;"/>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <h3>Shortcode example</h3>
        <p>
            If you wish to display a widget showing details of a project at: https://github.com/f13dev/wp-github-repo-shortcode use the following shortcode:
        </p>
        <p>
            [gitrepo author="f13dev" repo="wp-github-repo-shortcode"]
        </p>
    </div>
<?php
}
