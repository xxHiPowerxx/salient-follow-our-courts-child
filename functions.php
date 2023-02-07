<?php 

add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);

function salient_child_enqueue_styles() {
		
		$nectar_theme_version = nectar_get_theme_version();
		wp_enqueue_style( 'salient-child-style', get_stylesheet_directory_uri() . '/style.css', '', $nectar_theme_version );

		$child_theme_js_path = '/assets/js/main.js';
		wp_enqueue_script(
			'main-js',
			get_stylesheet_directory_uri() . $child_theme_js_path,
			array('jquery'),
			filemtime( get_stylesheet_directory() . $child_theme_js_path ),
			true
		);

		if ( is_rtl() ) {
			wp_enqueue_style(  'salient-rtl',  get_template_directory_uri(). '/rtl.css', array(), '1', 'screen' );
		}
}

/**
 * Search For Terms - Searches database for Specified Term
 * 
 * @param string - $term The Specified Term EG: Category
 * @return array|false - Returns Array of found Terms or False
 */
function search_for_terms( $term ) {
	// Use search term and cross-referrence Categories as well.
	$search_term = explode( ' ', get_search_query( false ) );
	global $wpdb;
	$select = "
	SELECT DISTINCT t.*, tt.* 
	FROM wp_terms AS t 
	INNER JOIN wp_term_taxonomy AS tt 
	ON t.term_id = tt.term_id 
	WHERE tt.taxonomy IN ('$term')";

	$first = true;
	foreach ( $search_term as $s ) :
		if ( $first ) :
			$select .= " AND (t.name LIKE '%s')";
			$string_replace[] = '%'.$wpdb->esc_like( $s ).'%';
			$first            = false;
		else :
			$select .= " OR (t.name LIKE '%s')";
			$string_replace[] = '%'. $wpdb->esc_like( $s ).'%';
		endif;
	endforeach; // endforeach ( $search_term as $s ) :
	$select .= " ORDER BY t.name ASC";
	$terms       = $wpdb->get_results( $wpdb->prepare( $select, $string_replace ) );
	if ( count( $terms ) > 0 ) :
		add_filter( 'body_class', function( $classes ) {
			return str_replace( 'search-no-results', 'search-results', $classes );
		} );
	endif;

	return $terms;
}

function foc_set_post_views($postID) {
	$count_key = 'foc_post_views_count';
	$count = get_post_meta($postID, $count_key, true);
	if($count==''){
			$count = 0;
			delete_post_meta($postID, $count_key);
			add_post_meta($postID, $count_key, '0');
	}else{
			$count++;
			update_post_meta($postID, $count_key, $count);
	}
}
//To keep the count accurate, lets get rid of prefetching
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

function foc_track_post_views ($post_id) {
	if ( !is_single() ) return;
	if ( empty ( $post_id) ) {
			global $post;
			$post_id = $post->ID;
	}
	foc_set_post_views($post_id);
}
add_action( 'wp_head', 'foc_track_post_views');

function foc_get_post_views($postID){
	$count_key = 'foc_post_views_count';
	$count = get_post_meta( $postID, $count_key, true );
	if( $count=='' ){
			delete_post_meta( $postID, $count_key );
			add_post_meta( $postID, $count_key, '0' );
			return "0 View";
	}
	return $count.' Views';
}

function redirect_to_subscribe() {
	return;
	// if (
	// 	$GLOBALS['pagenow'] === 'wp-login.php'
	// ) :
	// 	if ( ! empty( $_REQUEST['action'] ) ) :
	// 		if ( $_REQUEST['action'] === 'register' ) :
	// 			// We're registering
	// 		endif;
	// 		if ( $_REQUEST['action'] === 'lostpassword' ) :
	// 			// Lost Password
	// 		endif;
	// 	else:
	// 		// Logging In Default
	// 	endif;
	// endif;
	// RegistrationMagic's Default Registration Page
	$reg_page_id    = get_option('rm_option_default_registration_url');
	// RegistrationMagic's Default User Account Page
	$dashboard_link = get_permalink( get_option('rm_option_front_sub_page_id') );
	if ( is_front_page() ) :
		$subscribe_link = ( is_user_logged_in() && ! current_user_can( 'edit_others_pages' ) ) ?
			$dashboard_link :
			get_permalink( $reg_page_id );
		if ( $subscribe_link ) :
			wp_redirect( $subscribe_link, 301 );
			exit;
		endif;
	endif;
	if ( is_page( $reg_page_id ) ) :
		if ( is_user_logged_in() && ! current_user_can( 'edit_others_pages' ) ) :
			wp_redirect( $dashboard_link, 301 );
			exit;
		endif;
	endif;
}
add_action( 'template_redirect', 'redirect_to_subscribe' );

function get_stc_categories() {
	global $wpdb;
	$user_id = get_current_user_id();
	$user_info = get_userdata( $user_id );
	$email = $user_info->user_email;
	$result = $wpdb->get_row( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status='publish' AND post_title = %s AND post_type = %s", $email, 'stc' ) );

	return get_the_terms($result->ID, 'category');
}

function get_topics_of_interest() {
	$user_id = get_current_user_id();
	$categories = get_user_meta( $user_id, 'topics_of_interest', true );
	$result = array();
	if ( $categories ) :
		foreach ( $categories as $category ) :
			$term = get_term_by( 'name', $category, 'category');
			if ( $term ) :
				$result[] = $term;
			endif;
		endforeach;
	endif;
	return $result;
}

// My function to modify the main query object
function add_chosen_categories_to_dom_window() {
	if ( is_admin() ) :
		return;
	endif;
	// $categories = get_stc_categories();
	$categories = get_topics_of_interest();
	$chosen_categories = array();

	if ( $categories != false ) :
		foreach ( $categories as $category ) : 
			$chosen_categories[] = array(
				'name' => $category->name,
				'slug' => $category->slug,
			);
		endforeach;
		$chosen_categories_object = (object) $chosen_categories;
		?>
		<script>
			window.topicsOfInterest = <?php echo json_encode( $chosen_categories_object ); ?>;
		</script>
		<?php
	endif; // endif ( $categories != false ) :
}
// Hook my above function to the pre_get_posts action
add_action( 'wp_print_scripts', 'add_chosen_categories_to_dom_window' );

function modify_nectar_user_account_url() {
	global $nectar_options;
	if ( empty( $nectar_options ) ) :
		return;
	endif;
	if ( is_user_logged_in() ) :
		$dashboard_link = get_permalink( get_option('rm_option_front_sub_page_id') );
		$nectar_options['header-account-button-url'] = $dashboard_link;
	else:
		$subscribe_link = get_permalink( get_option('rm_option_default_registration_url') );
		if ( $subscribe_link ) :
			$nectar_options['header-account-button-url'] = $subscribe_link;
		endif;
	endif;
	return $nectar_options['header-account-button-url'];
}
add_action('init', 'modify_nectar_user_account_url');

function tag_manager_head() {
	if ( strpos( $_SERVER['SERVER_NAME'], 'localhost' ) === false ) :
		?>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-54RRGGD');</script>
		<!-- End Google Tag Manager -->
		<?php
	endif;
}
add_action('wp_head','tag_manager_head', 20);

function tag_manager_body(){
	if ( strpos( $_SERVER['SERVER_NAME'], 'localhost' ) === false ) :
		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-54RRGGD"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
	endif;
}
add_action('__before_header','tag_manager_body', 20);


// function my_et_enqueue_styles() {
// 	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
// 	wp_enqueue_script( 'divi', get_stylesheet_directory_uri() . '/js/scripts.js', array( 'jquery', 'divi-custom-script' ), '0.1.1', true );
	
// }
// add_action( 'wp_enqueue_scripts', 'my_et_enqueue_styles' );
	
/* === Add your own functions below this line ===
* ——————————————– */
// Allow subscribers to see Private posts and pages
/*
function allow_subscribers_read_private() {
	$sub_role = get_role( 'subscriber' );
	
	$sub_role->add_cap( 'read_private_posts' );
	$sub_role->add_cap( 'read_private_pages' );
}
add_action( 'after_setup_theme', 'allow_subscribers_read_private' );

function redirect_private_page_to_subscribe() {
	return;
	global $wp_query,
		$wpdb;
	if ( is_404() ) :
		$private = $wpdb->get_row($wp_query->request);
		$reg_page_id    = get_option('rm_option_default_registration_url');
		$subscribe_link = get_permalink( $reg_page_id );
		
		wp_redirect( $subscribe_link, 301 );
		// Figure out how to get $queried_object_id (get_queried_object()->ID) in order to finish this redirect.
		// Suggest using the global $wp_query or $wpdb to get this if it is not already available.
		// $queried_object_id = ???
		// wp_redirect( home_url( '/login?redirect=' . get_permalink( $queried_object_id ) ) );
	endif;
}
add_action('template_redirect', 'redirect_private_page_to_subscribe', 9);

// function allow_private_posts_on_archive() {
	// $args = array( 'capability_type' => 'read_private_posts' );
 
	// // If not set, default to the setting for 'show_ui'.
	// // if ( null === $args['show_in_menu'] || ! $args['show_ui'] ) {
	// // 		$args['show_in_menu'] = $args['show_ui'];
	// // }
	// $this->cap = get_post_type_capabilities( (object) $args );
	// unset( $args['capabilities'] );
// }

// function wp1482371_custom_post_type_args( $args, $post_type ) {
// 	if ( is_archive() ) :
// 		if ( $post_type == "post" ) {
// 				$args['rewrite'] = array(
// 						'capabilities' => array(array('read_private_posts'=>'read_private_posts')),
// 				);
// 		}

// 		return $args;
// 	endif;
// }
// add_filter( 'register_post_type_args', 'wp1482371_custom_post_type_args', 20, 2 );




/**
 * Load ACF Fields
 */
function foc_json_load_point( $paths ) {

	// remove original path (optional).
	unset( $paths[0] );

	// append path.
	$paths[] = get_stylesheet_directory() . '/acf-json';

	// return.
	return $paths;
}
add_filter( 'acf/settings/load_json', 'foc_json_load_point' );

/**
 * Save ACF Fields
 */
function foc_json_save_point( $path ) {

	// update path
	$path = get_stylesheet_directory() . '/acf-json';

	// return
	return $path;
}
// Save fields. REMOVE THIS FOR PRODUCTION!
add_filter( 'acf/settings/save_json', 'foc_json_save_point' );

// Add Admin Page.
function foc_email_blast_admin_page() {
	add_menu_page(
		__( 'Email Blast', 'salient' ),
		__( 'Email Blast menu', 'salient' ),
		'publish_posts',
		'foc-email-blast',
		'foc_email_blast_func',
		'dashicons-email-alt',
		31
	);
}
add_action( 'admin_menu', 'foc_email_blast_admin_page' );

function foc_email_blast_func() {
	include get_stylesheet_directory() . '/inc/foc-email-blast-template.php';
}

function acf_foc_site_settings_js() {
	if ( get_current_screen()->parent_base != 'foc-site-settings' ) :
		return;
	endif;
	?>
	<script type="text/javascript">
		(function($) {
			$('.metabox_submit').click(function(e) {
				e.preventDefault();
				$('#publish').click();
			});
		})(jQuery);
	</script>
	<?php
}
add_action('acf/input/admin_footer', 'acf_foc_site_settings_js');

function foc_acf_settings_page() {
	// Check function exists.
	if( function_exists('acf_add_options_page') ) :
		// Register options page.
		$option_page = acf_add_options_page( array(
			'page_title' => __( 'FOC Site Settings' ),
			'menu_title' => __( 'FOC Site Settings' ),
			'menu_slug'  => 'foc-site-settings',
			'capability' => 'edit_posts',
			'redirect'   => false,
			'position'   => '30',
		) );
	endif; // endif( function_exists('acf_add_options_page') ) :
}
add_action('acf/init', 'foc_acf_settings_page');

function change_post_thumbnail_size( $size ) {
	return $size = array( 690, null );
}
function add_post_thumbnail_size_filter( $post_id ) {
	add_filter( 'post_thumbnail_size', 'change_post_thumbnail_size' );
}
add_action( 'stc_before_message', 'add_post_thumbnail_size_filter', 10, 2 );

function remove_post_thumbnail_size_filter( $post_id ) {
	remove_filter( 'post_thumbnail_size', 'change_post_thumbnail_size' );
}
add_action( 'stc_after_message', 'remove_post_thumbnail_size_filter', 10, 2 );

/**
 * Widget areas.
 */
require get_stylesheet_directory() . '/inc/widget-areas.php';

if ( ! function_exists( 'merge_inner_blocks_with_parent' ) ) :
	/**
	 * Recursively Merge Inner Blocks Array with Parent Array.
	 * 
	 * @param array $blocks - Parent Blocks.
	 * @return array $blocks - Returns Blocks merged with found $block['innerBlocks'].
	 */
	function merge_inner_blocks_with_parent( $blocks ) {
		foreach ( $blocks as $block ) {
			if ( ! empty( $block['innerBlocks'] ) ) {
				// or call the function recursively, to find heading blocks in inner blocks
				$blocks = array_merge( $blocks, merge_inner_blocks_with_parent( $block['innerBlocks'] ) );
			}
		}
		return $blocks;
	}
endif; // endif ( ! function_exists( 'merge_inner_blocks_with_parent' ) ) :


if ( ! function_exists( 'foc_trim_string' ) ) :
	/**
	 * Trim String and use Excerpt apply ellipsis on end.
	 *
	 * @param string $string - String to be trimmed.
	 * @param int $max_words - Number of words before string is trimmed.
	 */
	function foc_trim_string($string, $max_words) {
		$stripped_content = strip_tags( $string );
		$excerpt_length   = apply_filters( 'excerpt_length', $max_words );
		$excerpt_more     = apply_filters( 'excerpt_more', ' [...]' );
		return wp_trim_words( $stripped_content, $excerpt_length, $excerpt_more );
	}
endif; // endif ( ! function_exists( 'foc_trim_string' ) ) :

if ( ! function_exists( 'foc_get_post_meta_description' ) ) :
	/**
	 * Get Meta Description, Excerpt, or create an excerpt.
	 * @param obj - $post Post Object
	 * @return string - post Meta Description, Excerpt, or create an excerpt.
	 */
	function foc_get_post_meta_description( $post = null ) {
		if ( ! $post ) :
			global $post;
		endif;

		$post_id = $post->ID;
		$description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);

		if ( empty( $description ) ) :
			$description = $post->post_excerpt;
		endif;

		if ( empty( $description ) ) :
			$description = foc_trim_string(
				$post->post_content,
				30
			);
		endif;

		return $description;
	}
endif; // endif ( ! function_exists( 'foc_get_post_meta_description' ) ) :

function has_wlm_membership_level( $level ) {
	if ( !function_exists( 'wlmapi_is_user_a_member' ) ) :
		return true;
	endif;

	$level_id = -1;
	if ( is_numeric( $level ) ) :
		$level_id = $level;
	elseif ( function_exists( 'wlmapi_get_levels' ) ) :
		$api_response = wlmapi_get_levels();
		if ( !!$api_response['success'] ) :
			foreach ( $api_response['levels']['level'] as $api_level ) :
				if ( $api_level['name'] === $level ) :
					$level_id = $api_level['id'];
					break;
				endif;
			endforeach;
		endif; // endif ( !!$api_response['success'] ) :
	endif; // endif ( is_numeric( $level ) ) :

	return $level_id === -1 || wlmapi_is_user_a_member( $level_id, get_current_user_id() );
}

function is_a_bot(){
	$is_bot = false;
	$user_agents = array(
		'GTmetrix',
		'Googlebot',
		'Googlebot/2.1',
		'http://www.googlebot.com/bot.html',
		'Bingbot',
		'Mozilla/5.0',
		'http://www.bing.com/bingbot.htm',
		'BingPreview',
		'msnbot',
		'slurp',
		'http://help.yahoo.com/help/us/ysearch/slurp',
		'Mozilla/5.0',
		'Baidu',
		'DuckDuckBot',
		'AOLBuild'
	);
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	foreach ( $user_agents as $agent ) :
		if ( strpos( $user_agent, $agent) ) :
			$is_bot = true;
		endif;
	endforeach;
	return $is_bot;
}

function wrap_post_content_in_wlm_private_tag( $content ) {
	if (
		! function_exists( 'has_wlm_membership_level' ) ||
		get_post_type() !== 'post' ||
		is_a_bot() // Let Bots Crawl Content.
	) :
		return $content;
	endif;

	$level = 'subscribers';
	// var_dump( is_user_logged_in(), current_user_can( 'edit_others_pages' ) );
	// die;
	// if ( has_wlm_membership_level( $level ) ) :
	if ( is_user_logged_in() && current_user_can( 'edit_others_pages' ) ) :
		return $content;
	else:
		$max_words_per_story       = get_field( 'max_words_per_story', 'options' );
		$offset                    = $max_words_per_story ? : 55;

		$words                     = explode(' ', $content);
		$free_words                = array_slice( $words, 0, $offset, true );
		$free_content              = join(' ', $free_words);

		$protected_words           = array_splice($words, $offset);
		$protected_content         = join(' ', $protected_words);

		$wlm_start_tag             = '[wlm_private "subscribers"]';
		$wlm_end_tag               = '[/wlm_private]';
		$wrapped_protected_content = $wlm_start_tag . $protected_content . $wlm_end_tag;
		$full_content              = $free_content . $wrapped_protected_content;

		return do_shortcode( $full_content );
	endif; // endif ( has_wlm_membership_level( $level ) ) :
}
add_filter( 'the_content', 'wrap_post_content_in_wlm_private_tag', 1 );

function set_previous_uri() {
	if (
		// Don't set if in Admin Area.
		! is_admin() &&
		// Don't set if on Login page.
		isset( $GLOBALS['pagenow'] ) AND 'wp-login.php' !== $GLOBALS['pagenow']
	) :
			$current_uri = "//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$_SESSION['previous_location'] = $current_uri;
	endif;
}
add_action( 'init', 'set_previous_uri' );


function foc_no_admin_access_to_subscribers() {
		// Do not run if the user is logged in and trying to log out
		// This might need one or two more checks.
		// Especially if you have custom login/logout/reset password/etc rules and routes set up.
		if (
			! is_admin() ||
			(
				is_user_logged_in() && isset( $GLOBALS['pagenow'] ) AND
				'wp-login.php' === $GLOBALS['pagenow']
			)
		) :
			return;
		endif;
    $redirect = isset( $_SESSION['previous_location'] ) ?
			$_SESSION['previous_location'] :
			home_url( '/' );
		$level = 'subscribers';
		if (
			// Ensure that AJAX does not get blocked by RM Magic Popup login functionality.
			( defined('DOING_AJAX') && DOING_AJAX ) === false &&
			// Ensure Wishlist Membership Level is met.
			has_wlm_membership_level( $level ) &&
			// Ensure User is Not Above Wordpress User - Subscriber Level.
			! current_user_can( 'edit_posts' )
		) :
			var_dump( $redirect );
			die;
			exit( wp_redirect( $redirect ) );
		endif;
}
// add_action( 'admin_init', 'foc_no_admin_access_to_subscribers', 100 );

function facebook_meta_verification_head() {
	if ( strpos( $_SERVER['SERVER_NAME'], 'localhost' ) === false ) :
		?>
		<meta name="facebook-domain-verification" content="o1zmn4ba4xiye2uqn0hspak555rk65" />
		<?php
	endif;
}
add_action('wp_head','facebook_meta_verification_head', 20);

/**
 * Year shortcode.
 */
function get_year_func() {
	$year = date( 'Y' );
	return $year;
}
add_shortcode( 'year', 'get_year_func' );

/**
 * Redirect Users to Previous Story after login.
 */
function set_up_after_login_redirection() {
	if (session_status() === PHP_SESSION_NONE) :
		session_start();
	endif;

	function is_login_page() {
		return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
	}

	function sc_capture_before_login_page_url(){
		if( ! is_user_logged_in() && ! is_login_page()):
			$_SESSION['referer_url'] = get_the_permalink();
		endif;
	}
	add_action( 'wp', 'sc_capture_before_login_page_url' );

	/*@ After login redirection */
	if( !function_exists('foc_after_login_redirection') ):
		function foc_after_login_redirection($redirect_to, $request, $user) {
			if (
				( isset( $user->roles ) && is_array( $user->roles ) ) &&
				( isset( $user->allcaps ) && $user->allcaps['edit_posts'] != true )
			) :
				$prev_url = $_SESSION['referer_url'] ? : $_SERVER['HTTP_REFERER'];
				if ( isset( $prev_url ) ):
					$redirect_to  = $prev_url;
					unset( $_SESSION['referer_url'] );
				endif;

				if (session_status() === PHP_SESSION_NONE) :
					session_write_close();
				endif;
			endif; // endif ( isset( $user->roles ) && is_array( $user->roles ) ) :

			return $redirect_to;
		}
		add_filter('login_redirect', 'foc_after_login_redirection', 10, 3);
	endif;
};
set_up_after_login_redirection();

/**
 * Set Custom Duration for Users to Stay Logged In
 */
function foc_keep_users_logged_in_longer( $expirein ) {
	// 1 month in seconds
	$duration = 2628000;
	return $duration;
}
add_filter( 'auth_cookie_expiration', 'foc_keep_users_logged_in_longer' );