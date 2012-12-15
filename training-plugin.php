<?php
  /*
    Plugin Name: Training Plugin
    Plugin URI: http://seravo.fi
    Description: Experimental training plugin
    Version: 1.0
    Author: Tomi Toivio
    Author URI: http://seravo.fi
    License: GPL2
 */
  /*  Copyright 2012 Tomi Toivio (email: tomi@seravo.fi)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/* Requires the Events manager plugin */

/* Add custom trainings role */

add_role('trainings', 'Trainings', array(
	'read' => true,
	'edit_posts' => false,
	'publish_events' => false,
	'delete_others_events' => false,
	'edit_others_events' => false,
	'delete_events' => true,
	'edit_events' => true,
	'read_private_events' => true,
	'publish_recurring_events' => false,
 	'delete_others_recurring_events' => false,
 	'edit_others_recurring_events' => false,
 	'delete_recurring_events' => false,
 	'edit_recurring_events' => true,
	'publish_locations' => false, 
 	'delete_others_locations' => false,
 	'edit_others_locations' => false,
 	'delete_locations' => false, 
 	'edit_locations' => true,
 	'read_private_locations' => true,
 	'read_others_locations' => false,
	'delete_event_categories' => false,
 	'edit_event_categories' => false,
 	'manage_others_bookings' => false,
 	'manage_bookings' => true,
 	'upload_event_images' => true
));


/* Make Trainings users see only their own Events in Admin */ 

function tm_posts_for_trainings_author($query) {
global $user_ID;
global $current_user;
$user_roles = $current_user->roles;
$user_role = array_shift($user_roles);
if($query->is_admin) {
	if ($user_role == "trainings") {
			global $user_ID;
			$query->set('author', $user_ID);
		echo '<style type="text/css">
		.subsubsub { display: none !important; }
		</style>';
}
}
	return $query;	
}
add_filter('pre_get_posts', 'tm_posts_for_trainings_author');


/* Remove stuff from Trainings user's edit page */
 
function tm_trainings_meta_boxes() {
	global $current_user;
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	global $blog_id;
	if (is_admin()) {
	if($user_role == "trainings") {
    	remove_meta_box('event-categoriesdiv', 'event', 'side');
				}
		}
}
add_action( 'admin_menu', 'tm_trainings_meta_boxes' );

/* Trainings users can only post Trainings category events */ 

function tm_add_category_trainings($result, $EM_Event) {
	global $current_user;
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	if (is_admin()) {
		if($user_role == "trainings"){ 
			wp_set_object_terms($EM_Event->post_id, 'trainings', 'event-categories');
 		} 
	}
return $result;
}
add_filter('em_event_save', 'tm_add_category_trainings',10,2);

/* Contact methods for Trainings users */

function tm_add_trainings_contactmethods($contactmethods) {

	$contactmethods['company'] = 'Company (trainings)';
	$contactmethods['companyurl'] = 'Company Website (trainings)';	
	$contactmethods['address'] = 'Address (trainings)';
	$contactmethods['zip'] = 'Zip Code (trainings)';
	$contactmethods['city'] = 'City (trainings)';

	return $contactmethods;
}
add_filter('user_contactmethods','tm_add_trainings_contactmethods',10,1);

/* Additional business info and tags for Training posts */ 

function tm_trainings_post_author($content){
	if (has_term( 'trainings', 'event-categories')) {
		$content .= '<h2>Company</h2><p><a href="' . get_the_author_meta('companyurl') .  '">' .  get_the_author_meta('company') . '</a></p><p><a href="' . the_author_posts_link() . '">Trainings by this company</a></p>';
		$tags = get_the_terms($EM_Event->post_id, EM_TAXONOMY_TAG);
		if( is_array($tags) && count($tags) > 0 ){
			$content .= '<h2>Training tags</h2>';
			$tags_list = array();
			foreach($tags as $tag){
			$link = get_term_link($tag->slug, EM_TAXONOMY_TAG);
			if ( is_wp_error($link) ) $link = '';
			$tags_list[] = '<a href="'. $link .'">'. $tag->name .'</a>';
		}
		$content .= '<p>' . implode(', ', $tags_list) . '</p>';
	}
	}
	return $content;
}
add_filter('em_event_output','tm_trainings_post_author');

/* Add shortcode for event tags */ 
function tm_trainings_tags( $atts ){
 	$tm_trainings_tags = get_terms('event-tags','hide-empty=0&orderby=id');
	$sep = '';
	echo '<h2>Training tags</h2><p>';
	foreach ( $tm_trainings_tags as $tm_trainings_tags ) {
			if( ++$count > 60 ) break;  
			echo $sep . '<a href="' . get_term_link($tm_trainings_tags) . '">' . $tm_trainings_tags->name . '</a>';
			$sep = ', '; 
		}
	echo '</p>';
}
add_shortcode( 'tm_trainings_tags', 'tm_trainings_tags' );

/* Add shortcode for training provider list */ 
function tm_trainings_providers(){
	$training_providers = get_users('role=trainings');
	echo '<h2>Training providers</h2>';
	foreach ($training_providers as $provider) {
		echo '<p><a href="' . get_author_posts_url($provider->ID) . '">' . $provider->company . '</a></p>';
		}
}
add_shortcode('tm_trainings_providers', 'tm_trainings_providers' );

/* Add empty placeholder admin page */
function tm_trainings_admin_page() {
	echo "<h2>Placeholder</h2>";
	echo "<p>...</p>";
	}	
function tm_trainings_admin() {
	add_menu_page('html title','Trainings Plugin','manage_options','tm_trainings_plugin_admin',tm_trainings_admin_page);
}
add_action('admin_menu','tm_trainings_admin');
?>
