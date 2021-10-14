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
	// return;
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
	$nectar_options['header-account-button-url'] = is_user_logged_in() ?
		$user_account_btn_url :
		( ! empty( $nectar_options['header-account-button-url'] ) )
			? $nectar_options['header-account-button-url'] :
			'';
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

function crf_registration_form() {
	if ( class_exists( 'STC_Subscribe' ) ) {
		$stc_subscribe = new STC_Subscribe();
	}
	$form = $stc_subscribe->stc_subscribe_render( '' );
	$form_sans_tag = str_replace( '<form', '<div', $form );
	$form_sans_tag = str_replace( '</form>', '</div>', $form_sans_tag );
	$form_sans_action_input = str_replace( 'name="action"', '', $form_sans_tag );
	echo $form_sans_action_input;

	$original_post_action = $_POST['action'];
	// add_filter( 'change_post_action', function( $string ){
		$_POST['action'] = 'stc_subscribe_me';
		$_POST['stc_email'] = $_POST['user_email'];
		$stc_subscribe->collect_post_data();
		$_POST['action'] = $original_post_action;
		// var_dump(' foobar1');
		// die;
	// });
}
add_action( 'register_form', 'crf_registration_form' );

function crf_user_register( $user_id ) {
	// $_POST['action'] = $_POST['action'] === 'stc_subscribe_me' ? 'register' : $_POST['action'];
	$original_post_action = $_POST['action'];
	apply_filters( 'change_post_action', '' );
	var_dump($original_post_action, ' foobar2');
	die;
	$_POST['action'] = $original_post_action;
}
// add_action( 'user_register', 'crf_user_register' );
