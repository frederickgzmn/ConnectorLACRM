<?php

// Register Custom Post Type
function custom_marker() {

    $labels = array(
        'name'                  => _x( 'markers', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'marker', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'marker', 'text_domain' ),
        'name_admin_bar'        => __( 'marker', 'text_domain' ),
        'archives'              => __( 'Marker Archives', 'text_domain' ),
        'attributes'            => __( 'Marker Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Marker:', 'text_domain' ),
        'all_items'             => __( 'All Markers', 'text_domain' ),
        'add_new_item'          => __( 'Add New Marker', 'text_domain' ),
        'add_new'               => __( 'Add marker', 'text_domain' ),
        'new_item'              => __( 'New marker', 'text_domain' ),
        'edit_item'             => __( 'Edit Marker', 'text_domain' ),
        'update_item'           => __( 'Update Marker', 'text_domain' ),
        'view_item'             => __( 'View Marker', 'text_domain' ),
        'view_items'            => __( 'View Markers', 'text_domain' ),
        'search_items'          => __( 'Search Marker', 'text_domain' ),
        'not_found'             => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into Marker', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this Marker', 'text_domain' ),
        'items_list'            => __( 'Markers list', 'text_domain' ),
        'items_list_navigation' => __( 'Markers list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter Markers list', 'text_domain' ),
    );
    $args = array(
        'label'                 => __( 'marker', 'text_domain' ),
        'description'           => __( 'Mapbox markers', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor' ),
        'taxonomies'            => array( 'category', 'post_tag' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
    );
    register_post_type( 'marker', $args );

}
add_action( 'init', 'custom_marker', 0 );