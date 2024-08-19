<?php
// Especifica la versión de los archivos
$version = '1.0.1';

// Agregar un estilo CSS
wp_enqueue_style('estilo-personalizado', get_stylesheet_directory_uri() . '/template-parts/blog/css/posts.css', array(), $version);
wp_enqueue_script('script-personalizado', get_stylesheet_directory_uri() . '/template-parts/blog/js/postsajax.js', array('jquery'), $version, true);

// Usar wp_localize_script para crear la variable global ajaxurl
wp_localize_script("script-personalizado", 'myAjax', array(
	'ajaxurl' => admin_url('admin-ajax.php'),
));



$category = get_queried_object();
if ($category) {
	$category_name = $category->name;
	$category_id = $category->term_id;
	$category_type = '';
} else {
	$category_name = '';
	$category_id = '';
	$category_type = '';
}


// if (is_archive()) {
// 	// This is an archive page
// 	echo "This is an archive page.";
// } else {
// 	// This is not an archive page
// 	echo "This is not an archive page.";
// }



// -----
global $biz_shortcode_data;
$array = explode(",", $biz_shortcode_data["data"]);
// $categoryName = $biz_shortcode_data["category"];
// $allName = $biz_shortcode_data["all"];
// $showmore = $biz_shortcode_data["showmore"];
$havePosts = false;
$menuID = isset($biz_shortcode_data["menuid"]) ? $biz_shortcode_data["menuid"] : 7;



// $categories = get_categories(array(
// 	'include' => $array,
// ));

// $bestCategory = [];
// foreach ($categories as $category) {
// }

// -----
if (isset($_GET['s'])) {
	$valorBusqueda = sanitize_text_field($_GET['s']);
} else {
	$valorBusqueda = "";
}



// -----
// Obtener la imagen de la categoría en el frontend
function mostrar_imagen_categoria($categoria_actual)
{
	// Obtener el ID de la categoría actual
	$categoria_id = $categoria_actual->term_id;

	// Obtener la imagen de la categoría actual
	$imagen_categoria = get_term_meta($categoria_id, 'categoria-imagen', true);

	// Mostrar la imagen si está disponible
	if (!empty($imagen_categoria)) {
		echo '<img src="' . esc_url($imagen_categoria) . '" alt="' . esc_attr($categoria_actual->name) . '">';
	}
}



?>

<div class="list-wrapper" id="">
	<div id="top-nav-menu">

		<?php

		class MiWalkerPersonalizado extends Walker_Nav_Menu
		{
			function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
			{

				$indent = ($depth) ? str_repeat("\t", $depth) : '';

				$classes = empty($item->classes) ? array() : (array) $item->classes;
				$classes[] = 'menu-item-' . $item->ID;

				$output .= $indent . '<li id="menu-item-' . $item->ID . '"  class="' . implode(' ', $classes) . '">';

				$attributes  = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
				$attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
				$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
				$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
				$attributes .= ' data-id="' . $item->object_id . '"';
				$attributes .= ' data-title="' . $item->title . '"';
				if ($item->type === "taxonomy") {
					$attributes .= ' data-taxonomy="' . $item->object . '"';
				}

				$item_output = $args->before;
				$item_output .= '<a class="cat-link" ' . $attributes . '>';
				$item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
				$item_output .= '</a>';
				$item_output .= $args->after;

				$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
			}
		}


		wp_nav_menu(
			array(
				'menu' => $menuID, // ID del menú
				'container_class' => 'dropdown',
				'walker' => new MiWalkerPersonalizado(),
			)
		);

		?>

		<button class="toggle-blog-menu" id="toggle-blog-menu">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
				<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
			</svg>
		</button>

		<div class="search-container">
			<input type="text" id="search-post-name" placeholder="Search">
			<button id="search-post-btn">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
					<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
				</svg>
			</button>
		</div>
	</div>

	<!-- -------------- -->
	<div id="posts-display">
		<p class="filter-label">
			<span id="filter-search" style="display:none"><span> </span>
				<svg id="filter-search-close" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
				</svg>
			</span>
		</p>
		<div id="category-filter"></div>
		<!-- -------------- -->
		<div class="lds-roller" style="display: none;">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>

		<ul id="category-display">
			<?php
			// ! taxonomy list -----
			require_once(plugin_dir_path(__FILE__) . 'taxonomy-template.php');
			?>

		</ul>
		<!-- posts list -->
		<div id="posts-listing">
			<?php

			// * print posts -----------
			if (have_posts()) :
				$havePosts = true;
				require_once(plugin_dir_path(__FILE__) . 'post-template.php');
				echo printPostList();
			endif; ?>
		</div>

		<ul id="post-pagination">
			<?php

			function getUrlPage($pageNumber)
			{
				// Obtener la URL actual
				$currentURL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				$paginatedURL = preg_replace('/\/page\/\d+/i', '', $currentURL) . 'page/' . $pageNumber . '/';

				return $paginatedURL;
			}


			global $wp_query;
			$currentPaged = get_query_var('paged') ? get_query_var('paged') : 1;

			if (isset($wp_query->max_num_pages)) {
				$total_pages = $wp_query->max_num_pages;

				for ($i = 1; $i < $total_pages + 1; $i++) {
					$page_url = getUrlPage($i);
					$className = $currentPaged === $i  ? "post-page active" : "post-page";
					echo "<li><a class=\"$className\" href=\"$page_url\" data-id=\"$id\">$i</a></li>";
				}
			} else {
				$total_pages = "";
			}

			?>

		</ul>
	</div>
</div>


<?php
global $wp_query;
$post_type_name = isset($biz_shortcode_data["posttype"]) ? $biz_shortcode_data["posttype"] : $wp_query->query_vars["post_type"];

// $valorBusqueda = isset($valorBusqueda) ?  $valorBusqueda : false;

wp_localize_script("script-personalizado", 'myAjax', array(
	'ajaxurl' => admin_url('admin-ajax.php'),
	'posttype' =>   $post_type_name,
	'havePosts' =>  $havePosts,
	'categoryId' =>  "'" . $category_id . "'",
	'totalPaginas' =>  $total_pages,
	'valorBusqueda' =>   $valorBusqueda
));





?>