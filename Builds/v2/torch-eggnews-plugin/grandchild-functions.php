<?php
/*
Plugin Name: Bexley Torch - Grandchild theme plugin
Description: Grandchild theme for the Eggnews magazine child theme. Meant to add custom funcitonality for the torch
Author: Elliot Roe
Author URI: https://bexleytorch.org
Version: 2.0
*/

// TODO: Check theme slug and update function
// Upon plugin activation checks if the correct child theme is activated
function bt_check_for_eggnews()
{
    $theme = wp_get_theme();
    if (1==1) {
        echo '<div class="notice notice-warning is-dismissible">
             <p>The current theme is' . $theme . '</p>
         </div>';
    }
}
// TODO: implement in v2
//register_activation_hook(plugin_dir_path( __FILE__ ) . "grandchild-functions.php", 'bt_check_for_eggnews');

// TODO: Test bt_safe_add_staff_role_field
function bt_safe_add_staff_role_field()
{
    // Create the WP_User_Query object
    $wp_user_query = new WP_User_Query(array('role' => 'Contributor'));

    // Get the results
    $users = $wp_user_query->get_results();

    // Check for results
    if (!empty($users)) {

      // loop trough each author
        foreach ($users as $user) {
            if (!get_user_meta($user->id, 'staff_role', true)) {
                // set all user's roles to a default value of staff reporter if not set
                update_user_meta($user->id, 'staff_role', 'Staff Reporter', true);
            }
        }
    }
}

register_activation_hook(plugin_dir_path(__FILE__) . "grandchild-functions.php", 'bt_safe_add_staff_role_field');

// Adds our new file with styles
function bt_grandchild_add_styles()
{
    wp_register_style('grandchild-style', plugins_url('grandchild-styles.css', __FILE__), array(), '1.0');
    wp_enqueue_style('grandchild-style');
}
add_action('wp_print_styles', 'bt_grandchild_add_styles');

// Adds our new file with scripts
function bt_grandchild_add_scripts()
{
    wp_register_script('grandchild-script', plugins_url('grandchild-scripts.js', __FILE__), array( 'jquery' ), '1.0');
    wp_enqueue_script('grandchild-script');
}
add_action('wp_print_scripts', 'bt_grandchild_add_scripts');

function bt_get_current_user_roles($role)
{
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;
        return in_array($role, $roles);
    } else {
        return false;
    }
}

// TODO: Test bt_staff_role_field
// Adds selection menu of staff roles to the user edit page
function bt_staff_role_field($user)
{

    //All the drop down options
    $roles 	= array( 'Adviser',
      'Co-Editor',
      'Website Coordinator',
      'News Editor',
      'Opinion Editor',
      'In-depth Editor',
      'Feature Editor',
      'Sports Editor',
      'Backpage Editor',
      'Graphics Editor',
      'Assistant Graphics Editor',
      'Graphics Staff',
      'Staff Reporter' );

    $default	= 'Staff Reporter';
    $current_role = get_the_author_meta('staff_role', $user->ID);
    if (!$current_role) {
        $current_role = $default;
    }
    echo $current_role; ?>
    <h3>Staff Role</h3>

    <table class="form-table">
   	 <tr>
   		 <th><label for="staff-role">Staff Role</label></th>
   		 <td>
   			 <select id="staff-role" name="the_role"><?php
                 foreach ($roles as $role) {
                     printf('<option value="%1$s" %2$s>%1$s</option>', $role, selected($current_role, $role), false);
                 } ?></select>
   		 </td>
   	 </tr>
    </table>
    <?php
}

add_action('show_user_profile', 'bt_staff_role_field');
add_action('edit_user_profile', 'bt_staff_role_field');

// Adds save functionality to staff role selection
function bt_save_profile_fields($user_id)
{
    if (! bt_get_current_user_roles('administrator')) {
        return false;
    }

    if (empty($_POST['the_role'])) {
        return false;
    }

    update_usermeta($user_id, 'staff_role', $_POST['the_role']);
}

add_action('personal_options_update', 'bt_save_profile_fields');
add_action('edit_user_profile_update', 'bt_save_profile_fields');

// Shortcode for staff directory
function bt_staff_directory()
{

  //Enqueues style sheet needed
    wp_enqueue_style('bt-staff-directory-style', plugin_dir_url(__FILE__) . 'bt-staff-directory-style.css');

    //Creates sections in the staff directory and directs where each staff roll goes

    $sections = array(
    "Management" => array( "Adviser",
      "Co-Editor",
      "Website Coordinator"
    ),
    "News" => array( "News Editor" ),
    "Opinion" => array( "Opinion Editor" ),
    "In-depth" => array( "In-depth Editor" ),
    "Feature" => array( "Feature Editor" ),
    "Sports" => array( "Sports Editor" ),
    "Backpage" => array( "Backpage Editor" ),
    "Graphics" => array( "Graphics Editor",
      "Assistant Graphics Editor",
      "Graphics Staff"),
    "Staff Reporters" => array( "Staff Reporter" ),
  );

    // Gets a defualt profile picture
    $defualt_url = 'https://bexleytorch.org/wp-content/uploads/2020/10/Torch-Logo.png';
    // Makes sure that the global exists before calling it
    global $simple_local_avatars;
    $local_set = isset($simple_local_avatars);

    $content = '';
    foreach ($sections as $section_title => $staff_role_array) {
        $content .= '<div class="block-header"><h3 class="bt-section-title block-title">' . $section_title . '</h3></div>';
        $content .= '<div class="bt-section-wrapper">';

        // Creates widget for each user containing a id specified
        foreach ($staff_role_array as $staff_role) {

      // Searches for users with specific role
            $users = new WP_User_Query(array(
          'meta_key'         => 'staff_role',
          'meta_value' => $staff_role
      ));
            $users_found = $users->get_results();
            // Creates a widget for each user found in the search
            foreach ($users_found as $user) {
                // Gets suer id for meta searches
                $id = $user->ID;
                // Bunch of meta searches for the info needed
                $user_url = get_author_posts_url($id);
                $safe_username = str_replace('.', '', get_user_meta($id, 'username', true));
                $first = get_user_meta($id, 'first_name', true);
                $last = get_user_meta($id, 'last_name', true);

                // Makes sure that the function exists before calling it
                if ($local_set) {
                    $pic_url = $simple_local_avatars->get_simple_local_avatar_url($id, 100);
                    if (empty($pic_url)) {
                        $pic_url = $defualt_url;
                    }
                }

                // Building html
                $content .= '<a class="bt-staff-link" href="' . $user_url . '">';
                $content .= '<div class="bt-staff-widget" id="' . $safe_username . '">';
                $content .= '<img src="' . $pic_url . '" alt="' . $first . ' ' . $last . 'Staff Picture' . '" class="bt-staff-picture">';
                $content .= '<span class="bt-staff-text">';
                $content .= '<h5 class="bt-staff-name">' . $first . ' ' . $last . '</h5>';
                $content .= '<p class="bt-staff-position">'. $staff_role .'</p>';
                $content .= '</span>';
                $content .= '</div>';
                $content .= '</a>';
            }
        }
        $content .= '</div>';
    }

    return $content;
}

add_shortcode('bt-staff-directory', 'bt_staff_directory');

// TODO: Test bt_plugin_menu
// Manages the creation of admin menu for the plugin
function bt_plugin_menu()
{
    $page_title = 'Bexley Torch Plugin Settings';
    $menu_title = 'Bexley Torch';
    $capability = 'manage_options';
    $menu_slug  = 'bexley-torch';
    $function   = 'bt_plugin_page';
    $icon_url   = plugins_url('torch-eggnews-plugin/icons/Torch.png');
    $position   = 4;
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
}

add_action('admin_menu', 'bt_plugin_menu');

// TODO: Test bt_plugin_page
// Actual HTML content of the admin page
function bt_plugin_page()
{
    wp_enqueue_script('upload-script', plugins_url('bt-upload-script.js', __FILE__), [ 'jquery', 'wp-api-request' ], '1.0', true );?>
  <h1>Bexley Torch Plugin Settings</h1>
  <form class="bt-form" id="bt-upload-form" enctype="multipart/form-data">
  Select story zip file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload Zip" id="submitButton" name="submit">
  </form>
  <div id="error_message" style="width:100%; height:100%; display:none; ">
    <h4>
        <span>Fatal Error!!</span> No stories were posted
    </h4>
  </div>
  <div id="success_message" style="width:100%; height:100%; display:none; ">
    <h2>Success!</h2>
    <h4 class="message_info_header" id="posted_message">The following stories were posted</h4>
    <h4 class="message_info_header" id="posted_warn_message">The following stories were posted with <b>warnings</b></h4>
    <h4 class="message_info_header" id="posted_fatal_message">The following stories were not posted due to a fatal error with them</h4>
  </div>
  <?php
}

add_action('rest_api_init', 'add_custom_users_api');

function add_custom_users_api()
{
    register_rest_route('bt/v2', '/upload/', array(
        'methods' => 'POST',
        'callback' => 'bt_upload_handler',
    ));
}

function bt_upload_handler()
{
    // Just for debugging
  error_reporting(-1); // reports all errors
  ini_set("display_errors", 1); // shows all errors
  ini_set("log_errors", 1);
    ini_set("error_log", __DIR__ . "/tmp-error.log");
    error_log("Test");
    //$path = preg_replace('/wp-content.*$/', '', __DIR__);
    //require_once($path.'wp-load.php');

    //ob_start();
    //var_dump($_FILES);
    //var_dump($_POST);
    //$result = ob_get_clean();
    //error_log($result);
    $success_array = array('fatalError' => '', 'postedStories' => array(), 'postedWarningStories' => array(), 'failedStories' => array());

    $target_dir = __DIR__ . "/stories";
    //$target_dir = "/tmp";
    //$target_file = $target_dir . "/" . basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . "/torchStory.zip";
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $success_array['errors']['fileUpload'] = $_FILES["fileToUpload"]['error'];

    // Allow certain file formats
    if ($fileType == "zip") {
        if (is_uploaded_file($_FILES['fileToUpload']['tmp_name'])) {
            // Move file to correct location
            $success_array['errors']['fileMove'] = move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
            if ($success_array['errors']['fileMove']) {
                // File extraction
                $zip = new ZipArchive();
                if ($zip->open($target_file) === true) {
                    $zip->extractTo(untrailingslashit($target_dir) . '/torchStoryUnzipped/');
                    $zip->close();
                    // Reading docx file
                    $dir = new DirectoryIterator(untrailingslashit($target_dir) . '/torchStoryUnzipped/');
                    foreach ($dir as $fileinfo) {
                        if (!$fileinfo->isDot()) {
                            if (!strpos($fileinfo->getFilename(), ".docx")) {
                                $unfiltered_content = bt_read_docx($fileinfo->getFilename());
                                if (!$unfiltered_content) {
                                    $nl_index = strpos($unfiltered_content, '\n');
                                    $category = substr($unfiltered_content, 0, $nl_index);
                                    $filtered_content = substr($unfiltered_content, $nl_index);
                                    $nl_index = strpos($filtered_content, '\n');
                                    $headline = substr($unfiltered_content, 0, $nl_index);
                                    $author = substr($unfiltered_content, $nl_index, strpos($filtered_content, '\n', $nl_index));
                                    $author = trim($author);

                                    $cat_slug = str_replace(' ', '-', trim(strtolower($category)));
                                    $cat_ID = get_category_by_slug($cat_slug);
                                    if ($cat_ID==0) {
                                        $success_array['postedWarningStories'][$headline][] = $category . " category could not be found with slug: ". $cat_slug .". Posted under uncategorized";
                                        $cat_ID = 1;
                                    }
                                    // Gets authors last name in order to use it as a key word in a user query search
                                    $keyword = substr($author, strlen($author)-strpos(strrev($author), " "));

                                    $user_query = new WP_User_Query(array( 'search' => $keyword ));

                                    if (!empty($user_query->get_results())) {
                                        $auth_ID = $user_query->get_results()[0]->ID;
                                    } else {
                                        $success_array['postedWarningStories'][$headline][] = "No author found. Searched with keyword: " . $keyword . ". Will post under defualt staff reporter (Bexley.StaffReporter)";
                                        $auth_ID = get_user_by('login', 'Bexley.StaffReporter')->ID;
                                    }

                                    $post_arr = array(
                                        'post_author' => $author,
                                        'post_content' => $filtered_content,
                                        'post_title' => $headline,
                                        'comment_status' => 'closed',
                                        'post_category' => $cat_ID,
                                      );

                                    if (wp_insert_post($post_arr)==0) {
                                        $success_array['failedStories'][$headline] = $success_array['postedWarningStories'][$headline];
                                        unset($success_array['postedWarningStories'][$headline]);
                                        $success_array['failedStories'][$headline][] = "Failed to post the story: " . $headline . ". wp_insert_post failed.";
                                    } else {
                                        if (!isset($success_array['postedWarningStories'][$headline])) {
                                            $success_array['postedStories'][] = $headline;
                                        }
                                    }
                                }
                            } else {
                                $success_array['failedStories'][$headline] = array("Not .docx file");
                            }
                        }
                    }
                } else {
                    $success_array['fatalError'] = "Could not extract file";
                }
            } else {
                $success_array['fatalError'] = "Could not move ".$_FILES["fileToUpload"]["tmp_name"]." to " . $target_file;
            }
        } else {
            $success_array['fatalError'] = $_FILES["fileToUpload"]["tmp_name"]." is uploaded successfully";
        }
    } else {
        $success_array['fatalError'] = "Only zip files are allowed.";
    }
    echo json_encode($success_array);
}

function bt_read_docx($filename)
{
    $striped_content = '';
    $content = '';

    $zip = zip_open($filename);

    if (!$zip || is_numeric($zip)) {
        return false;
    }

    while ($zip_entry = zip_read($zip)) {
        if (zip_entry_open($zip, $zip_entry) == false) {
            continue;
        }

        if (zip_entry_name($zip_entry) != "word/document.xml") {
            continue;
        }

        $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

        zip_entry_close($zip_entry);
    }

    zip_close($zip);

    $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
    $content = str_replace('</w:r></w:p>', "\r\n", $content);
    $striped_content = strip_tags($content);

    return $striped_content;
}

add_filter('template_include', 'bt_author_template_loader');
function bt_author_template_loader($template)
{
    if (is_author()) {
        wp_enqueue_style('bt-author-style', plugin_dir_url(__FILE__) . 'bt-author-style.css');
        $new_template = untrailingslashit(plugin_dir_path(__FILE__)) . '/templates/author.php';
        return $new_template;
    }
    return $template;
}



  ?>
