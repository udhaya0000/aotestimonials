<?php
/**
* Plugin Name:     AO Testimonials
* Description:     A plugin creates testimonials custom post type
* version:         1.0.0
* Author:          Udhayakumar Sadagopan
* Author URI:      http://www.udhayakumars.com
**/


if( ! defined( 'T_VERSION' ) ) {
	define( 'T_VERSION', 1.0 );
} // end if

class Testimonials {

	/* --------------------------------------------
	 * Attributes
	 -------------------------------------------- */

   // Represents the nonce value used to save the post media
	 private $nonce = 'wp_testimonials_nonce';
	 private $singular_label = "Testimonial";
	 private $plural_label = "Testimonials";


	/* --------------------------------------------
	 * Constructor
	 -------------------------------------------- */

	 /**
	  * Initializes localiztion, sets up JavaScript, and displays the meta box for saving the file
	  * information.
	  */
	 public function __construct() {

	 	// Localization, Styles, and JavaScript
	 	add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) , 10, 1 );

	 	// Setup the meta box hooks
    add_action( 'init', array($this, 'create_cpt') );
		add_action( 'admin_enqueue_scripts', array($this, 'load_wp_media_files') );

		$this->add_meta_box();
	 } // end construct

	 function load_wp_media_files() {
	 	wp_enqueue_media();
	 }

	/* --------------------------------------------
	 * Localization, Styles, and JavaScript
	 -------------------------------------------- */

	/**
	 * Addings the admin JavaScript
	 */
	public function register_admin_scripts() {
    wp_enqueue_script( 'testimonials-meta-js', plugins_url( 'js/index.js', __FILE__ ), array('jquery'), T_VERSION);
    wp_enqueue_script( 'testimonial-js', plugins_url( 'js/testimonial.js', __FILE__ ), array('jquery'), T_VERSION);
    wp_localize_script( 'testimonial-js', 'testimonial_image',
        array(
          'title' => __( 'Choose or Upload Media' ),
          'button' => __( 'Use this media' ),
        )
      );
	} // end register_scripts

	/* --------------------------------------------
	 * Hooks
	 -------------------------------------------- */

	/**
	 * Introduces the file meta box for uploading the file to this post.
	 */
	public function create_cpt() {

    $theme = "estpal";
    // Set UI labels for Custom Post Type
    $labels = array(
            'name'                => _x($this->plural_label, 'Post Type General Name', $theme),
            'singular_name'       => _x($this->singular_label, 'Post Type Singular Name', $theme),
            'menu_name'           => __($this->plural_label, $theme),
            'parent_item_colon'   => __('Parent '.$this->singular_label, $theme),
            'all_items'           => __('All '.$this->plural_label, $theme),
            'view_item'           => __('View '.$this->singular_label, $theme),
            'add_new_item'        => __('Add New '.$this->singular_label, $theme),
            'add_new'             => __('Add New', $theme),
            'edit_item'           => __('Edit '.$this->singular_label, $theme),
            'update_item'         => __('Update '.$this->singular_label, $theme),
            'search_items'        => __('Search '.$this->singular_label, $theme),
            'not_found'           => __('Not Found', $theme),
            'not_found_in_trash'  => __('Not found in Trash', $theme),
        );

    // Set other options for Custom Post Type

    $args = array(
            'label'               => __('testimonials', $theme),
            'description'         => __('List of '.$this->plural_label, $theme),
            'labels'              => $labels,
            // Testimonials this CPT supports in Post Editor
            'supports'            => array('title', 'revisions'),
            /* A hierarchical CPT is like Pages and can have
            * Parent and child items. A non-hierarchical CPT
            * is like Posts.
            */
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 7,
            'menu_icon'           => 'dashicons-testimonial',
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );

    // Registering your Custom Post Type
    register_post_type('testimonials', $args);
		$this->create_taxonomy();
	} // add_file_meta_box

	// Register Custom Taxonomy
	private function create_taxonomy()
	{
		$labels = array(
				'name' => _x( $this->singular_label.' Categories', 'taxonomy general name' ),
				'singular_name' => _x( $this->singular_label.' Category', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search '.$this->singular_label.' Categories' ),
				'all_items' => __( 'All '.$this->singular_label.' Categories' ),
				'parent_item' => __( 'Parent '.$this->singular_label.' Category' ),
				'parent_item_colon' => __( 'Parent '.$this->singular_label.' Category:' ),
				'edit_item' => __( 'Edit '.$this->singular_label.' Category' ),
				'update_item' => __( 'Update '.$this->singular_label.' Category' ),
				'add_new_item' => __( 'Add New '.$this->singular_label.' Category' ),
				'new_item_name' => __( 'New '.$this->singular_label.' Category Name' ),
				'menu_name' => __( $this->singular_label.' Categories' ),
			);

		// Now register the taxonomy

			register_taxonomy('testimonial_categories',array('testimonials'), array(
				'public' => true,
				'publicly_queryable' => true,
				'hierarchical' => true,
				'labels' => $labels,
				'show_ui' => true,
				'show_admin_column' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'testimonials', 'with_front' => true, ),
			));
	}

	public function add_meta_box()
	{
			add_action('cmb2_admin_init', array($this, 'register_metabox'));
	}

	/**
	 * Hook in and add a metabox that only appears on the 'About' page
	 */
	public function register_metabox()
	{
			$prefix = strtolower($this->singular_label).'_';

			$cmb_page = new_cmb2_box([
			 'id'           => $prefix . 'metabox',
			 'title'        => esc_html__($this->singular_label.' Info', 'cmb2'),
			 'object_types' => array( strtolower($this->plural_label) ), // Post type
			 'context'      => 'normal',
			 'priority'     => 'default',
			 'show_names'   => true, // Show field names on the left
			]);


		$cmb_page->add_field( array(
			'name' => 'Author Location / Title',
			'desc' => '',
			'id'   => $prefix.'location',
			'type' => 'text',
		) );

		$cmb_page->add_field( array(
			'name' => 'Author Feedback',
			'id'   => $prefix.'feedback',
			'type' => 'textarea_small',
		) );

		$cmb_page->add_field( array(
			'name' => 'Author Image',
			'id'   => $prefix.'img',
			'type' => 'file',
		) );
	}
} // end class

$GLOBALS['testimonials'] = new Testimonials();
