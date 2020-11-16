<?php
/*
Plugin Name: Bexley Torch - Grandchild theme plugin
Description: Grandchild theme for the Eggnews magazine child theme. Meant to add custom funcitonality for the torch
Author: Elliot Roe
Author URI: https://bexleytorch.org
Version: 1.0
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
    $roles 	= array( 'Supervisor',
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

// TODO: Test bt_save_profile_fields
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

// TODO: Test bt_staff_directory
// Shortcode for staff directory
function bt_staff_directory()
{

  //Enqueues style sheet needed
    wp_enqueue_style('er-staff-style', plugin_dir_url(__FILE__) . 'css/er-staff-style.css');

    //Creates sections in the staff directory and directs where each staff roll goes

    $sections = array(
    "Management" => array( "Supervisor",
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
        $content .= '<div class="block-header"><h3 class="er-section-title block-title">' . $section_title . '</h3></div>';
        $content .= '<div class="er-section-wrapper">';

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
                $content .= '<a class="er-staff-link" href="' . $user_url . '">';
                $content .= '<div class="er-staff-widget" id="' . $safe_username . '">';
                $content .= '<img src="' . $pic_url . '" alt="' . $first . ' ' . $last . 'Staff Picture' . '" class="er-staff-picture">';
                $content .= '<span class="er-staff-text">';
                $content .= '<h5 class="er-staff-name">' . $first . ' ' . $last . '</h5>';
                $content .= '<p class="er-staff-position">'. $staff_role .'</p>';
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
    $icon_url = plugins_url('torch-eggnews-plugin/icons/Torch.png');
    $position   = 4;
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);

    $upload_page_title = 'Upload Posts';
    $upload_menu_title = 'Upload Posts';
    $upload_capability = 'manage_options';
    $upload_menu_slug  = 'bexley-torch-upload';
    $upload_function   = 'bt_upload_plugin_page';
    add_submenu_page($menu_slug, $upload_page_title, $upload_menu_title, $capability, $menu_slug, $function);
}

add_action('admin_menu', 'bt_plugin_menu');

// TODO: Finish HTML content for admin page and figure out how to do settings
// Actual HTML content of the admin page
function bt_plugin_page() { ?>
  <h1>Bexley Torch Plugin Settings</h1>
<?php
}

// TODO: Firgure out how to do an upload field, unzip a file, convert .docx and other files to .txt, how to save drafts as plugins
function bt_upload_plugin_page() { ?>
  <h1>Upload Story Zips</h1>
<?php
}

// TODO: Test bt_author_template_loader
function bt_author_template_loader($template)
{
    if (is_author()) {
        $new_template = untrailingslashit(plugin_dir_path(__FILE__)) . '/templates/author.php';
        return $new_template;
    }
    return $template;
}

add_filter('template_include', 'bt_author_template_loader');


  ?>
