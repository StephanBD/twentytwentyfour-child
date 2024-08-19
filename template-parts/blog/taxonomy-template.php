<?php
$arrayTaxonomies = ["category", "racecourse"];



$allTax = get_terms(array(
	'taxonomy' => $arrayTaxonomies
));


// debug_this($racecourses);


foreach ($allTax  as $category) {
	// mostrar_imagen_categoria($category);
	$category_link = get_category_link($category->term_id);
	// Obtener la imagen de la categoría
	// $image = get_field('image', $category->term_id);
	// $image = get_field('categoria-imagen', 'category_' .  $category->term_id);


	// Obtener el ID de la categoría actual
	$categoria_id = $category->term_id;

	// Obtener la imagen de la categoría actual
	$imagen_categoria = get_term_meta($categoria_id, 'categoria-imagen', true);
	$bg_image = wp_get_attachment_image_src($imagen_categoria, 'full')[0];
	if (!empty($bg_image)) {
		$style = "background-size: cover;background-position: center;background-image: url('" . esc_url($bg_image) . "'),linear-gradient(180deg, rgb(0 0 0 / 0%) 50%, rgb(0 0 0) 100%);background-blend-mode:multiply;";
	} else {
		$style = "";
	}


	echo '<li class="cat-list cat-list-taxonomy"  style="' . $style . '">';
	echo '<a class="cat-link absolute-link" href="' . $category_link . '" data-id="' . $category->term_id . '" data-taxonomy="' . $category->taxonomy . '" >' . $category->name . '</a>';

	// Mostrar la categoría
	echo '<span>' . $category->name . '</span>';
	// echo "<button class='read-more'>Learn more</button>";

	echo "</li>";

	// debug_this($category);
}
