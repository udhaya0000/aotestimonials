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
    add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
    add_action( 'save_post', array( $this, 'save_testimonials_meta' ) );

	 } // end construct

	/* --------------------------------------------
	 * Localization, Styles, and JavaScript
	 -------------------------------------------- */

	/**
	 * Addings the admin JavaScript
	 */
	public function register_admin_scripts() {
    wp_enqueue_script( 'testimonials-meta-js', plugins_url( 'js/index.js', __FILE__ ), array('jquery'), T_VERSION);
    wp_enqueue_script( 'testimonials-js', plugins_url( 'js/testimonials.js', __FILE__ ), array('jquery'), T_VERSION);
    wp_localize_script( 'testimonials-js', 'testimonial-image',
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
    $singlur_label = "Testimonial";
    $plural_label = "Testimonials";

    // Set UI labels for Custom Post Type
    $labels = array(
            'name'                => _x($plural_label, 'Post Type General Name', $theme),
            'singular_name'       => _x($singlur_label, 'Post Type Singular Name', $theme),
            'menu_name'           => __($plural_label, $theme),
            'parent_item_colon'   => __('Parent '.$singlur_label, $theme),
            'all_items'           => __('All '.$plural_label, $theme),
            'view_item'           => __('View '.$singlur_label, $theme),
            'add_new_item'        => __('Add New '.$singlur_label, $theme),
            'add_new'             => __('Add New', $theme),
            'edit_item'           => __('Edit '.$singlur_label, $theme),
            'update_item'         => __('Update '.$singlur_label, $theme),
            'search_items'        => __('Search '.$singlur_label, $theme),
            'not_found'           => __('Not Found', $theme),
            'not_found_in_trash'  => __('Not found in Trash', $theme),
        );

    // Set other options for Custom Post Type

    $args = array(
            'label'               => __('testimonials', $theme),
            'description'         => __('List of '.$plural_label, $theme),
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
            'menu_icon'           => 'dashicons-slides',
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );

    // Registering your Custom Post Type
    register_post_type('testimonials', $args);

	} // add_file_meta_box

  public function add_meta_box() {
    add_meta_box(
      "testimonial-meta-box",
      "Testimonial Info",
      array($this, "display_testimonials_meta"),
      "testimonials",
      "advanced",
      "high",
      null);
  }

  public function save_testimonials_meta($post_id) {
    // First, make sure the user can save the post
		if( $this->user_can_save( $post_id, $this->nonce ) ) {

      $this->save_testimonials($post_id);
		} // end if

  }

  public function display_testimonials_meta() {
    global $post;
    wp_nonce_field( plugin_basename( __FILE__ ), $this->nonce );

    $values = get_post_custom($post->ID);
    $testimonial = isset($values['testimonial']) ? json_decode($values['testimonial'][0]) : null;
    //var_dump($testimonial);
    ?>
    <div class="js-form-group form-group">
      <div>Testimonial Icon</div>
      <div class="js-img-wrap">
        <?php if(isset($testimonial)): ?>
          <img src="<?php echo $testimonial->icon_url; ?>" alt=""/>
        <?php endif; ?>
      </div>
      <div class="input-group">
        <input id="testimonial_icon_url" type="hidden" class="form-control" name="testimonial[icon_url]" value="<?php echo isset($testimonial) ? $testimonial->icon_url : ""; ?>">
        <div class="input-group-append">
          <button type="button" data-media-uploader-target="#testimonial_icon_url" class="btn btn-default js-add-icon">Add Icon</button>
        </div>
      </div>
    </div>
    <div class="form-group">
      <div>Testimonial Content</div>
      <textarea name="testimonial[text]" class="form-control" ><?php echo isset($testimonial) ? $testimonial->text : ""; ?></textarea>
    </div>

    <?php
  }

  public function save_testimonials($post_id) {
    if(isset($_POST['testimonial'])) {
      update_post_meta($post_id, "testimonial", json_encode($_POST['testimonial']));
    }
  }
  /**
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @param		int		$post_id	The ID of the post being save
	 * @param		bool				Whether or not the user has the ability to save this post.
	 */
	function user_can_save( $post_id, $nonce ) {

	    $is_autosave = wp_is_post_autosave( $post_id );
	    $is_revision = wp_is_post_revision( $post_id );
	    $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) );

	    // Return true if the user is able to save; otherwise, false.
	    return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;

	} // end user_can_save
} // end class

$GLOBALS['testimonial'] = new Testimonials();
