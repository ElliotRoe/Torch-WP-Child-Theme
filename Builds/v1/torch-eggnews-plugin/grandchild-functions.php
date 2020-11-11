<?php
/*
Plugin Name: Bexley Torch - Grandchild theme plugin
Description: Grandchild theme for the Eggnews magazine child theme. Meant to add custom funcitonality for the torch
Author: Elliot Roe
Author URI: https://bexleytorch.org
Version: 1.0
*/

// Upon plugin activation checks if the correct child theme is activated
function bt_check_for_eggnews() {
  $theme = wp_get_theme();
  if (True) {
    echo '<div class="notice notice-warning is-dismissible">
             <p>The current theme is' . $theme . '</p>
         </div>';
  }
}
register_activation_hook( __FILE__, 'bt_check_for_eggnews' );

function bt_safe_add_staff_role_field() {
  // Create the WP_User_Query object
  $wp_user_query = new WP_User_Query(array('role' => 'Contributor'));

  // Get the results
  $users = $wp_user_query->get_results();

  // Check for results
  if (!empty($users)) {

      // loop trough each author
      foreach ($users as $user)
      {
          // add points meta all the user's data
          add_user_meta( $user->id, 'staff_role', 'Staff Reporter', true );
      }
  }
}

register_activation_hook( __FILE__, 'bt_add_staff_role_field' );

// Adds our new file with styles
function bt_grandchild_add_styles() {
	wp_register_style( 'grandchild-style', plugins_url( 'grandchild-styles.css', __FILE__ ), array(), '1.0' );
	wp_enqueue_style( 'grandchild-style' );
}
add_action( 'wp_print_styles', 'grandchild_add_styles' );

// Adds our new file with scripts
function bt_grandchild_add_scripts() {
	wp_register_script( 'grandchild-script', plugins_url( 'grandchild-scripts.js', __FILE__ ), array( 'jquery' ), '1.0' );
	wp_enqueue_script( 'grandchild-script' );
}
add_action( 'wp_print_scripts', 'grandchild_add_scripts' );

function er_staff_directory() {

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

      // Searches for users with specific id
      $users = new WP_User_Query( array(
          'search'         => '*'.esc_attr( $position_id ),
          'search_columns' => array('user_login'),
      ) );
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

        /*
        $description = get_user_meta($id, 'description', true);

        // Processes bio make it at most 123 characters long
        if (strlen($description)>50) {
          // Cuts bio down to 123 characters
          $description = substr($description, 0, 50);
          // Cuts off the partial word if there is one
          $description = substr($description, 0, strrpos($description,' ')) . ' [...]';
        }
        */

        // Makes sure that the function exists before calling it
        if ( $local_set ) {
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
        $content .= '<p class="er-staff-position">'. $position .'</p>';
        $content .= '</span>';
        $content .= '</div>';
        $content .= '</a>';
      }
    }
    $content .= '</div>';
  }

  return $content;

}

add_shortcode('staff-directory','er_staff_directory');
  ?>
