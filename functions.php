<?php
if (!defined("ABSPATH")) {
	exit;
}

// debug helper ------------------------------------
function debug_this(...$args)
{
	$red = mt_rand(0, 255);
	$green = mt_rand(0, 255);
	$blue = mt_rand(0, 255);
	$color = sprintf("#%02x%02x%02x", $red, $green, $blue);

	echo "<pre style='border:2px solid " . $color . "'>";
	foreach ($args as $arg) {
		if (isset($arg) && empty($arg)) {
			echo "La variable es Undefined";
		} else {
			// print_r($arg);
			var_dump($arg);
		}
		echo "<hr>";
		// var_dump($arg);
	}

	echo "</pre>";
}

// page excerpt ------------------------------------
function add_excerpt_to_pages()
{
	add_post_type_support('page', 'excerpt');
}
add_action('init', 'add_excerpt_to_pages');



// custom font styles ----
wp_enqueue_style('custom-font-style', get_stylesheet_directory_uri() . '/fonts/fonts.css', array(), "1.0");
// function my_theme_enqueue_styles()
// {
// 	wp_enqueue_style('custom-font-style', get_stylesheet_directory_uri() . '/fonts//fonts.css', array(), "1.3");
// }
// add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

//-------------------taxonomy for pages----------------------------------//
function wpturbo_register_page_category(): void
{
	$labels = [
		'name' => _x('Page categories', 'Taxonomy Name', 'wpturbo'),
		'singular_name' => _x('Page category', 'Taxonomy Singular Name', 'wpturbo'),
		'menu_name' => __('Page categories ', 'wpturbo'),
		'all_items' => __('All Page categories ', 'wpturbo'),
		'parent_item' => __('Parent Page category ', 'wpturbo'),
		'parent_item_colon' => __('Parent Page category: ', 'wpturbo'),
		'new_item_name' => __('New Page category ', 'wpturbo'),
		'add_new_item' => __('Add New Page category ', 'wpturbo'),
		'edit_item' => __('Edit Page category ', 'wpturbo'),
		'update_item' => __('Update Page category ', 'wpturbo'),
		'view_item' => __('View Page category ', 'wpturbo'),
		'add_or_remove_items' => __('Add or Remove Page categories ', 'wpturbo'),
		'choose_from_most_used' => __('Choose from most used Page categories ', 'wpturbo'),
		'popular_items' => __('Popular Page categories ', 'wpturbo'),
		'search_items' => __('Search Page categories ', 'wpturbo'),
		'not_found' => __('Not Found ', 'wpturbo'),
		'no_terms' => __('No Page categories ', 'wpturbo'),
		'items_list' => __('Page categories List ', 'wpturbo'),
		'items_list_navigation' => __('Page categories List Navigation ', 'wpturbo'),
	];

	$args = [
		'labels' => $labels,
		'hierarchical' => false,
		'public' => true,
		'show_ui' => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud' => false,
		'rewrite' => [
			'slug' => 'resources',
			'with_front' => false,
			'hierarchical' => false
		],
		'has_archive'        => true,
		'show_in_rest' => true,
		'rest_base' => 'pages_categories',
		'rest_controller_class' => 'WP_REST_pages_categories_Terms_Controller',
	];

	register_taxonomy('pages_categories', ['page'], $args);
}
add_action('init', 'wpturbo_register_page_category');



// Registers custom navigation menus. --------------------
function register_custom_nav_menus(): void
{
	$menus = [
		'My Menu' => __('Menu Description.', 'my-text-domain'),
	];
	register_nav_menus($menus);
}
add_action('init', 'register_custom_nav_menus');


//-------------------child_get_template_part----------------------------------//

$biz_shortcode_data = null;

function child_get_template_part($atts, $content = "")
{
	global $biz_shortcode_data;

	if (defined('REST_REQUEST') || is_admin() || empty($atts["file"])) {
		return;
	}

	$biz_shortcode_data = $atts;

	$file_path = plugin_dir_path(__FILE__) . 'template-parts/' . $atts["file"];

	if (!file_exists($file_path)) {
		return;
	}

	ob_start();
	require_once $file_path;
	$biz_shortcode_data = null;
	return ob_get_clean();
}

add_shortcode('child_get_template_part', 'child_get_template_part');
// [child_get_template_part file="blog/categorylist.php" menuid="1" ]



// AJAX ==========================================
// Agregar un hook para manejar la solicitud AJAX
add_action('wp_ajax_obtener_posts_por_categoria', 'manejar_ajax_obtener_posts_por_categoria');
add_action('wp_ajax_nopriv_obtener_posts_por_categoria', 'manejar_ajax_obtener_posts_por_categoria');

// Función para manejar la solicitud AJAX
function manejar_ajax_obtener_posts_por_categoria()
{
	$valid_post_types = array('post'); // * Your valid post types

	if (isset($_POST['posttype']) && !empty($_POST['posttype']) && in_array($_POST['posttype'], $valid_post_types)) {
		$postType = $_POST['posttype'];
	} else {
		$postType = 'post';
	}

	$taxonomies = isset($_POST['taxonomies']) ? $_POST['taxonomies'] : array();

	$allCategories = !empty($_POST['all']);

	// Calcular el offset para paginación
	$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
	$posts_per_page = 9;
	$offset = ($page - 1) * $posts_per_page;

	// search
	$search = sanitize_text_field($_POST['search']); // Obtener el término de búsqueda y sanitizarlo

	// Argumentos para WP_Query con base en las categorías
	$args = array(
		'post_type' => $postType,
		'posts_per_page' => $posts_per_page,
		// 'category__in' => $categories,
		'page' => $page,
		'offset' => $offset,
		'post_status' => 'publish',
	);



	if (isset($search) && $search !== '' && !$allCategories) {
		$args["s"] = $search;
		$args["sentence"] = true;
	}

	// $main_taxonomy = null;
	if (!empty($taxonomies) && !$allCategories) {
		$args["tax_query"] = array('relation' => 'AND');


		foreach ($taxonomies as $key => $value) {
			// if (is_null($main_taxonomy)) {
			// 	$main_taxonomy = $key;
			// }

			$child_categories = array();
			foreach ($value as $category_id) {
				$child_categories = array_merge($child_categories, get_term_children($category_id, $key));
			}
			$child_categories = array_values(array_unique($child_categories));

			$args["tax_query"][] = array(
				'taxonomy' => $key, // Nombre de la primera taxonomía
				'field'    => 'term_id', // Usamos 'term_id' para buscar por ID
				'terms'    => array_merge($value, $child_categories), // IDs de los términos de la primera 
			);
		}
	}

	$query = new WP_Query($args);

	$response = array();

	if ($query->have_posts()) {

		require_once(plugin_dir_path(__FILE__) . 'template-parts/blog/post-template.php');
		$posts = printPostList($query);
		$response["posts"] = $posts;
		// ---------

	}
	// Error handling
	if (!$query->have_posts()) {
		wp_send_json_error('No posts found');
		wp_die();
	}

	// Calcular el número total de páginas
	$total_pages = $query->max_num_pages;

	// Agregar el número total de páginas a la respuesta
	$response['total_pages'] = $total_pages;

	// Devolver la respuesta en formato JSON
	wp_send_json($response);

	// ¡Terminar la ejecución de WordPress para la solicitud AJAX!
	wp_die();
}
