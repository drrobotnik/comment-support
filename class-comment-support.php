<?php
/**
 * Plugin Name.
 *
 * @package   Comment_Support
 * @author    Brandon Lavigne <brandon.lavigne@gmail.com>
 * @license   GPL-2.0+
 * @link      http://caavadesign.com
 * @copyright 2013 Caava Design
 */

/**
 * Plugin class.
 *
 * @package Comment_Support
 * @author  Brandon Lavigne <brandon.lavigne@gmail.com>
 */
class Comment_Support {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'comment-support';

	protected $plugin_cpt = 'support';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'support_post_type' ) );
		add_action( 'save_post', array( $this, 'save_support_meta' ),100 );
		add_action( 'pre_get_posts', array( $this, 'my_slice_orderby' ) );

		add_action( 'add_meta_boxes_support',  array( $this, 'add_meta_boxes' ) );

		// Add the options page and menu item.
		// add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Load admin style sheet and JavaScript.
		#add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		#add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_head', array( $this, 'admin_ajax_url' ) );
		add_action( 'comment_post', array( $this, 'add_meta_settings' ), 1);
		add_action( 'login_form_login', array( $this, 'redirect_nonadmin_fromdash' ) );
		add_action( 'admin_init', array( $this, 'redirect_nonadmin_fromdash' ), 1 );
		add_action( 'pre_get_posts', array( $this, 'restrict_media_library' ) );
		//add_action( 'comment_form_defaults', array( $this, 'form_defaults' ) );

		// Define custom functionality. Read more about actions and filters: http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		//add_action( 'comment_form_top', array( $this, 'attachment_form_fields' ) );
		add_action( 'comment_form_before', array( $this, 'must_log_in_to_comment' ) );
		add_filter('comment_text', array( $this, 'add_attachments_to_comment' ) );
		add_filter('save_post', array( $this, 'force_type_private' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		self::create_role();
		self::remove_default_roles();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		
	}

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Uninstall" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function uninstall( $network_wide ) {
		
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		
	}

	

	public function support_post_type(){
		$post_type = 'support';
		$labels = array(
			'name' => 'Support',
			'singular_name' => 'Support',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Support',
			'edit_item' => 'Edit Support',
			'new_item' => 'New Support',
			'all_items' => 'All Support',
			'view_item' => 'View Support',
			'search_items' => 'Search Support',
			'not_found' =>  'No support found',
			'not_found_in_trash' => 'No support found in Trash', 
			'parent_item_colon' => '',
			'menu_name' => 'Support'
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'query_var' => true,
			'rewrite' => array( 'slug' => 'support' ),
			'capability_type' => 'post',
			'has_archive' => true, 
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title', 'comments' )
		);

		register_post_type( 'support', $args );

		add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'add_role_column_filter' ), 10, 2 );
		
		add_action( "manage_{$post_type}_posts_columns", array( $this, "add_client_column" ) );
		add_action( "manage_{$post_type}_posts_columns", array( $this, "add_consultant_column" ) );

	}

	public function create_role(){
		// Grab default roles.
		$contributor_roles = get_role('contributor');
		$administrator_roles = get_role('administrator');

		// Add new roles.
		add_role('client', 'Client', $contributor_roles->capabilities);
		add_role('consultant', 'Consultant', $administrator_roles->capabilities);
		
		$support_client = get_role( 'client' );
		$support_consultant = get_role( 'consultant' );
		$cpt = apply_filters('clients_post_type', 'support');
		if ( empty($cpt) )
			return false;
		
		$caps_to_add =  array(
			"edit_others_{$cpt}",
			"edit_published_{$cpt}",
			"upload_files"
		);

		foreach( $caps_to_add as $cap ) {
			$support_client->add_cap( $cap );
			$support_consultant->add_cap( $cap );
		}
	}

	public function remove_default_roles() {
		// Remove default roles.
		remove_role( 'editor' );
		remove_role( 'contributor' );
		remove_role( 'subscriber' );
	}

	public function add_meta_boxes() {
		remove_meta_box( 'authordiv', 'support', 'core' );
		add_meta_box( 'clientdiv', 'Client', array( $this, 'user_role_meta_box' ), 'support', 'side', 'high', array('client') );
		add_meta_box( 'consultantdiv', 'Consultant', array( $this, 'user_role_meta_box' ), 'support', 'side', 'high', array('consultant') );
	}

		/**
	 * Display form field with list of consultants.
	 *
	 * @since 2.6.0
	 *
	 * @param object $post
	 */
	public function user_role_meta_box($post, $callback_args) {
		global $user_ID,$post;

		$role = $callback_args['args'][0];
		$nonce = wp_create_nonce( plugin_basename( __FILE__ ) );
		echo "<input type='hidden' name='support_nonce' value='$nonce' />";
	?>
	<label class="screen-reader-text" for="post_<?php echo $role; ?>">Role</label>
	<?php
		$users = array();
		$user_query = new WP_User_Query( array( 'role' => ucfirst( $role ), 'fields' => 'ID' ) );

		if ( ! empty( $user_query->results ) ) {
			foreach ( $user_query->results as $user ) {
				$users[] = $user;
			}

			$selected = get_post_meta( $post->ID, 'post_'.$role, true );

			wp_dropdown_users( array(
				'include' => implode(',', $users),
				'name' => 'post_'.$role,
				'selected' => empty($selected) ? $users[0] : $selected,
				'include_selected' => true
			) );	
		}else{
			?>
			<p>There aren't any <?php echo ucfirst($role) ?>. <a href="<?php echo site_url( '/wp-admin/user-new.php' ); ?>">Add one.</a></p>
			<?php
		}
		
	}

	public function save_support_meta(){
		global $post_id;
		/* in production code, $slug should be set only once in the plugin,
		   preferably as a class property, rather than in each function that needs it.
		 */
		$slug = 'support';
		$roles = array('client','consultant');

		/* check whether anything should be done */
		$_POST += array("{$slug}_nonce" => '');
		if ( $slug != $_POST['post_type'] ) {
			return;
		}

		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( !wp_verify_nonce( $_POST["{$slug}_nonce"], plugin_basename( __FILE__ ) ) )
		{
			return;
		}

		foreach($roles as $role){
			/* Request passes all checks; update the post's metadata */
			if (isset($_REQUEST['post_'.$role])) {
				update_post_meta($post_id, 'post_'.$role, $_REQUEST['post_'.$role]);
			}
		}
	}

	/* Display custom column */
	function add_role_column_filter( $column, $post_id ) {
		$user_id = get_post_meta( $post_id, 'post_'.$column, 1 );
		$user = get_userdata( $user_id );
		echo '<a href="edit.php?post_type=support&'.$column.'='.$user_id.'">'.$user->user_login.'</a>';
	}

	/* Add custom column to post list */
	function add_client_column( $columns ) {
		return array_merge( $columns, array( 'client' => 'Client' ) );
	}

	/* Add custom column to post list */
	function add_consultant_column( $columns ) {
		return array_merge( $columns, array( 'consultant' => 'Consultant' ) );
	}

	
	public function my_slice_orderby( $query ) {
		if( ! is_admin() )
			return;


		$client = $_GET['client'];
		$consultant = $_GET['consultant'];


		if( !empty($client) ) {
			$query->set('meta_key','post_client');
			$query->set('meta_value',$client);
		}

		if( !empty($consultant) ) {
			$query->set('meta_key','post_consultant');
			$query->set('meta_value',$consultant);
		}
	}




	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
		}

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$cpt = apply_filters('clients_post_type', 'support');
		if( is_singular( $cpt )){
			wp_enqueue_media();
		}
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	
	
	public function admin_ajax_url() {
		$cpt = apply_filters('clients_post_type', 'support');
		$is_singular = ( is_singular( $cpt ) ) ? 'true' : 'false';
		echo '<script type="text/javascript">
		var is_singular = ' . $is_singular . ';
		var cpt = "'.$cpt. '";';
		if( is_singular( $cpt )){
			echo "var ajaxurl = '". admin_url( 'admin-ajax.php' )."';";
			echo "var set_to_post_id = " . get_the_ID() . ";";
		}
		echo "</script>";
	}

	public function attachment_form_fields(){
		$cpt = apply_filters('clients_post_type', 'support');
		if( is_user_logged_in() && current_user_can( 'upload_files' ) && is_singular( $cpt )){

			$comment_field = '<div class="uploader"><input type="button" class="button" name="_unique_name_button" data-uploader_title="Attach a File to Your Comment" data-comment-id="<?php the_ID(); ?>" id="cv_image_button" value="Attach Media" /><div class="thumbnails"></div></div>';
			$fields = apply_filters('cs_modify_fields', $comment_field);
			echo $fields;
		}
	}

	public function must_log_in_to_comment(){
		$cpt = apply_filters('clients_post_type', 'support');
		if( ( !is_user_logged_in() || !current_user_can( 'upload_files' ) ) && is_singular( $cpt ))
			return false;
	}
	public function force_type_private() {

		// avoid infinite loop.
		remove_action('save_post', array( $this, 'force_type_private' ));

		$post = $_POST;
		$post_status = $post['post_status'];
		//var_dump($post);die;

		$cpt = apply_filters('clients_post_type', 'support');
		$ignore_statuses = array('auto-draft','draft','inherit','trash');
		
		if ( ( $post['post_type'] == $cpt ) && ( !in_array( $post_status, $ignore_statuses ) ) ){ 
			wp_update_post( array( 'ID'=> $post['post_ID'], 'post_status' => 'private', 'comment_status' => 'open' ) );
		}
	}

	public function add_attachments_to_comment($comment_text){
		global $comment;

		$new_comment_text = '';
		$title = apply_filters( 'cs_attachments_title', "<h2>Attachments</h2>" );
		$before_attachments = apply_filters( 'cs_before_attachments', '<div class="attachment-thumbnails">' );
		$after_attachments = apply_filters( 'cs_after_attachments', '</div>' );
		$attachments = get_comment_meta($comment->comment_ID,"attachment_id", false);
		$thumb = '';

		if(!empty($attachments)){
			foreach($attachments as $attachment){
				$thumb .= wp_get_attachment_link( $attachment, array(60,60), false, true );
			}
			return $comment_text.$title.$before_attachments.$thumb.$after_attachments;
		}
		return $comment_text;
	}

	
	public function add_meta_settings($comment_id) {
		if( isset( $_POST['attachments'] ) ){
			foreach( $_POST['attachments'] as $attachment ){
				add_comment_meta($comment_id, 'attachment_id', (int) $attachment['id'], false);
			}
		}
	}

	public function redirect_nonadmin_fromdash(){
		global $pagenow,$current_user;
		$cpt = apply_filters('clients_post_type', 'support');

		if( $pagenow == 'async-upload.php' || $pagenow == 'admin-ajax.php' ){
			# allow users to upload files
			return true;
		} else if( is_user_logged_in() && !current_user_can( "create_users" ) ){

			$cpt_query = new WP_Query( array( 
				'meta_query' => array(
					array(
						'key' => 'post_client',
						'value' => $current_user->data->ID,
						'compare' => '=',
					)
				),
				'post_type'=> $cpt, 
				'posts_per_page'=>1 
				) );
			if( $cpt_query->have_posts() ){
				while( $cpt_query->have_posts() ){ $cpt_query->the_post();
					wp_safe_redirect( post_permalink( ) );
					exit;
				}
			}else{
				wp_safe_redirect( home_url() );
				exit;
			}
		}
	}

	

	public function restrict_media_library( $wp_query_obj ) {
		global $current_user, $pagenow;

		if( !is_a( $current_user, 'WP_User') )
			return;

		if( 'admin-ajax.php' != $pagenow || $_REQUEST['action'] != 'query-attachments' )
			return;
		
		if( !current_user_can( 'delete_plugins' ) )
			$wp_query_obj->set( 'author', $current_user->ID );
		
		return;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * TODO:
		 *
		 * Change 'Page Title' to the title of your plugin admin page
		 * Change 'Menu Text' to the text for menu item for the plugin settings page
		 * Change 'comment-support' to the name of your plugin
		 */
		$this->plugin_screen_hook_suffix = add_plugins_page(
			__( 'Page Title', $this->plugin_slug ),
			__( 'Menu Text', $this->plugin_slug ),
			'read',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *        Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// TODO: Define your filter hook callback here
	}

}