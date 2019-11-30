<?php
/**
 * Plugin Name:     Wpstx Email Notification With Post Type
 * Plugin URI:      https://advent.elliottrichmond.co.uk
 * Description:     Email users directly from WordPress by role using a published custom post type
 * Author:          Elliott Richmond
 * Author URI:      https://elliottrichmond.co.uk
 * Text Domain:     wpstx-email-notification-with-post-type
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wpstx_Email_Notification_With_Post_Type
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register a custom post type to handle all the emails notifications.
 * The labels and arguments here might be overkill but I used https://generatewp.com/post-type/
 * to generate the code so you get what it generated
 * 
 * @source GenerateWP https://generatewp.com/post-type/
 * 
 */
function email_notification() {

	$labels = array(
		'name'                  => _x( 'Emails', 'Post Type General Name', 'wpstx-email-notification-with-post-type' ),
		'singular_name'         => _x( 'Email', 'Post Type Singular Name', 'wpstx-email-notification-with-post-type' ),
		'menu_name'             => __( 'Email Users', 'wpstx-email-notification-with-post-type' ),
		'name_admin_bar'        => __( 'Email', 'wpstx-email-notification-with-post-type' ),
		'archives'              => __( 'Item Archives', 'wpstx-email-notification-with-post-type' ),
		'attributes'            => __( 'Item Attributes', 'wpstx-email-notification-with-post-type' ),
		'parent_item_colon'     => __( 'Parent Item:', 'wpstx-email-notification-with-post-type' ),
		'all_items'             => __( 'All Items', 'wpstx-email-notification-with-post-type' ),
		'add_new_item'          => __( 'Add New Item', 'wpstx-email-notification-with-post-type' ),
		'add_new'               => __( 'Add New', 'wpstx-email-notification-with-post-type' ),
		'new_item'              => __( 'New Item', 'wpstx-email-notification-with-post-type' ),
		'edit_item'             => __( 'Edit Item', 'wpstx-email-notification-with-post-type' ),
		'update_item'           => __( 'Update Item', 'wpstx-email-notification-with-post-type' ),
		'view_item'             => __( 'View Item', 'wpstx-email-notification-with-post-type' ),
		'view_items'            => __( 'View Items', 'wpstx-email-notification-with-post-type' ),
		'search_items'          => __( 'Search Item', 'wpstx-email-notification-with-post-type' ),
		'not_found'             => __( 'Not found', 'wpstx-email-notification-with-post-type' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'wpstx-email-notification-with-post-type' ),
		'featured_image'        => __( 'Featured Image', 'wpstx-email-notification-with-post-type' ),
		'set_featured_image'    => __( 'Set featured image', 'wpstx-email-notification-with-post-type' ),
		'remove_featured_image' => __( 'Remove featured image', 'wpstx-email-notification-with-post-type' ),
		'use_featured_image'    => __( 'Use as featured image', 'wpstx-email-notification-with-post-type' ),
		'insert_into_item'      => __( 'Insert into item', 'wpstx-email-notification-with-post-type' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'wpstx-email-notification-with-post-type' ),
		'items_list'            => __( 'Items list', 'wpstx-email-notification-with-post-type' ),
		'items_list_navigation' => __( 'Items list navigation', 'wpstx-email-notification-with-post-type' ),
		'filter_items_list'     => __( 'Filter items list', 'wpstx-email-notification-with-post-type' ),
	);
	$args = array(
		'label'                 => __( 'Email', 'wpstx-email-notification-with-post-type' ),
		'description'           => __( 'Email notification system', 'wpstx-email-notification-with-post-type' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 25,
		'menu_icon'             => 'dashicons-email',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => false,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capability_type'       => 'post',
	);
	register_post_type( 'email_notification', $args );

}
add_action( 'init', 'email_notification' );

/**
 * Change the button that says "Publish" to "Send" on the post type
 * "email_notifications" to make the UI look more obvious that the
 * user is about to send something.
 * 
 * @source https://developer.wordpress.org/reference/hooks/gettext/
 * 
 * @param 	string 	$translation 	Translated text
 * @param 	string 	$translation 	Text to translate
 * @return 	string 	$translation 	Translated text
 * 
 */
function wpstx_change_publish_to_send( $translation, $text, $domain ) {
	if ( 'email_notification' == get_post_type() && ($text == 'Publish') ) {
		$translation = 'Send';
	}
	return $translation;
}
add_filter( 'gettext', 'wpstx_change_publish_to_send', 10, 3 );

/**
 * Save some post meta with the email_notification post type
 * on auto-draft or when the post if first initiated
 * 
 * @source https://developer.wordpress.org/reference/hooks/save_post/
 * 
 * @param 	int 		$post_id 	Auto generated post ID
 * @param 	object 		$post 		WP post object
 * @param 	boolean 	$update 	Whether this is an existing post being updated or not
 */
function wpstx_save_notification_status( $post_id, $post, $update = false ) {

	if ( ! current_user_can( 'edit_post', $post_id ) || $update) {
        return; // stop if the current user does not have capabilities to edit this or post is an update 
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return; // stop if the current post is auto saving
    }
	if( $post->post_type === 'email_notification' || $post->post_status === 'auto-draft' ) {
		add_post_meta($post_id, 'notification_sent', '0'); // add post meta that we will use in the wp cron later
	}

}
add_action( 'save_post', 'wpstx_save_notification_status', 10, 3 );

/**
 * Register some custom meta to select user role to email to.
 * 
 * @source https://developer.wordpress.org/reference/hooks/add_meta_boxes/
 */
function wpstx_register_meta_boxes() {
    add_meta_box( 'user-role', __( 'Role to Email', 'wpstx-email-notification-with-post-type' ), 'wpstx_my_display_callback', 'email_notification' );
}
add_action( 'add_meta_boxes', 'wpstx_register_meta_boxes' );

/**
 * Render the meta box to display in the backend.
 * We are also getting all the current WordPress roles with the global $wp_roles
 * here so we can isolate what role to set the email notification to send to
 *
 * @param	object	$post	Current post object.
 */
function wpstx_my_display_callback( $post ) {
	global $wp_roles;
	$roles = $wp_roles->roles;

	// for security when saving the data later
	wp_nonce_field( 'role_to_email', 'role_to_email_nonce' ); 
	// get the current value to check against
	$role_to_email = get_post_meta( $post->ID, '_role_to_email', true ); 

	?>
	<!-- set a label with translation just because it's good practice -->
	<label for='role_to_email'>
		<?php _e( 'Select the user role to email', 'wpstx-email-notification-with-post-type' ); ?>
	</label>
	<!-- add the name key '_role_to_email' for indexing the meta -->
	<select name='_role_to_email'>
		<!-- set a default of selected but disabled on render if the an option hasn't already been selected -->
		<option value='' selected disabled>Select Role</option>
		<?php foreach($roles as $k => $role) { ?>
			<!-- the currently selected option using the wp core function "selected" https://developer.wordpress.org/reference/functions/selected/ -->
			<option value='<?php echo esc_attr($k); ?>' <?php selected( $role_to_email, $k, true ); ?>><?php echo esc_attr($role['name']); ?></option>
		<?php } ?>

	</select>

	<?php
}


/**
 * Save meta box content.
 *
 * @param 	int 	$post_id 	Post ID
 * 
 * @source https://developer.wordpress.org/reference/hooks/save_post/
 */
function wpstx_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['role_to_email_nonce'] ) ) {
        return; // stop if the nounce is not set
    }
    if ( ! wp_verify_nonce( $_POST['role_to_email_nonce'], 'role_to_email' ) ) {
        return; // stop if the nounce doesn't varify
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return; // stop if auto saving the post
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return; // stop if the current user does not have capabilities to edit this or post
    }
    if ( isset( $_POST['post_type'] ) && 'email_notification' === $_POST['post_type'] ) {
		// only happens if the post type is "email_notification" 
		if(isset($_POST['_role_to_email'])) {
			// only happens if name key is set
			update_post_meta($post_id, '_role_to_email', esc_attr($_POST['_role_to_email']));
		}
    }
}
add_action( 'save_post', 'wpstx_save_meta_box', 10, 1 );

/**
 * A simple helper function to get all users by role if the parameter is set
 * 
 * @param	string/array	$roles	Key for role set in WordPress roles
 */
function wpstx_get_users($roles = '') {
    $args = array();
    if($roles) {
        $args['role'] = $roles;
    }
    $users = get_users($args);
    return $users;
}

/**
 * This function takes care of all the sending of emails that
 * are hooked to our custom action hook scheduled by the wp cron
 */
function wpstx_send_emails() {

	// get all the posts that are set to published and that
	// do not have a meta key 'notification_sent' set to '1'
	// we will set this to '1' after the email notification is sent
	// this will ensure that we don't send it again
	// https://developer.wordpress.org/reference/functions/get_posts/
	$emails = get_posts(
		array(
			'post_type' => 'email_notification',
			'post_status' => 'publish',
			'meta_key' => 'notification_sent',
			'meta_value' => '1',
			'meta_compare' => '!='
		)
	);

	// $emails will return and array of objects so we'll use a
	// foreach to loop the email notifications ready to send
	foreach ($emails as $send_email) {

		// get role set for sending the email notification 
		$role = get_post_meta($send_email->ID, '_role_to_email', true);
		// if $role is set pass it to helper function 'wpstx_get_users()'
		if(isset($role) && $role != '') {
			$users = wpstx_get_users($role);
		} else {
			$users = wpstx_get_users();
		}

		// now we have all the users to send an email notification to
		// and we can use the $send_email post object to setup some parameters
		foreach ($users as $user) {

			// get the post title by ID and set the subject line for the email
			// https://developer.wordpress.org/reference/functions/get_the_title/
			$subject = get_the_title( $send_email->ID );
			// we only need the post_content of the post and we will filter it using the_content
			// https://developer.wordpress.org/reference/functions/get_post_field/
			// https://developer.wordpress.org/reference/hooks/the_content/
			$content = apply_filters('the_content', get_post_field('post_content', $send_email->ID));
			// load the $message variable with a personalised "Hey username" 
			$message = '<p>Hey ' . $user->user_login . '</p>';
			// concatinate the $content to the end of the "Hey..." $message variable
			$message .= $content;
			// as we are sending html we need to set an email header as an array
			$headers = array('Content-Type: text/html; charset=UTF-8');
			// Send the email to the user with the subject, message and headers.
			// https://developer.wordpress.org/reference/functions/wp_mail/
			wp_mail($user->user_email, $subject, $message, $headers);
			// IMPORTANT!!
			// Your host will have a limit on the amount of eamils you can send per minute/hour from your
			// server so it is super important to set a delay between each emails sent, otherwise
			// you could get your hosting account temporarily blocked on banded for spammy looking activity.
			// I'm simpy using a 10 second delay between each sending of an email, you might need to increase
			// this depending on the amount of users you have to send to
			sleep(10);

		}

		// Once the email has been sent now set the post with a post meta
		// key 'notification_sent' equal to '1' this will ensure we never
		// pick it up as published post to send again as set in the
		// WordPress function get_posts() above.
		update_post_meta($send_email->ID, 'notification_sent', '1');

	}

}
add_action( 'wpstx_check_scheduled_emails', 'wpstx_send_emails' );

/**
 * This sets the next event time for our action hook
 * "wpstx_check_scheduled_emails" that fires "wpstx_send_emails"
 */
wp_next_scheduled( 'wpstx_check_scheduled_emails' );

/**
 * This checks if the event is set or past and sets a new one if it has past
 * or at least that's the way I interpret the way it works :)
 */
if ( ! wp_next_scheduled( 'wpstx_check_scheduled_emails' ) ) {
	// now we can tap in to the WordPress cron schedule, there is a choice of
	// ‘hourly’, ‘twicedaily’, and ‘daily’
	// you can set your own custom time intervals as well 
	// https://developer.wordpress.org/plugins/cron/understanding-wp-cron-scheduling/
	// in this case we are firing the custom action hook "wpstx_check_scheduled_emails"
	// on the next hourly interval
    wp_schedule_event( time(), 'hourly', 'wpstx_check_scheduled_emails' );
}

/**
 * Setup a deactivation hook to remove this cron if this plugin is disabled
 * 
 * @source https://developer.wordpress.org/reference/functions/register_deactivation_hook/
 */
function wpstx_deactivate_email_notification_cron() {
	// retrieve the event
	$timestamp = wp_next_scheduled( 'wpstx_check_scheduled_emails' );
	// unset the event
	wp_unschedule_event( $timestamp, 'wpstx_check_scheduled_emails' );
}
register_deactivation_hook( __FILE__, 'wpstx_deactivate_email_notification_cron' );