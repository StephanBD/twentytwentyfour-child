<?php
function printPostList($query = null)
{


	// return "xxxxxxxxxxxxxxxxxxxx";
	// $main_category
	$html = '';
	while (have_posts() || (!empty($query) && $query->have_posts())) {

		if (have_posts()) {
			the_post();
		} else if (!empty($query)) {
			$query->the_post();
		}
		$post_classes = esc_attr(implode(' ', get_post_class("post-list ")));
		$permalink = get_the_permalink();
		$title = get_the_title();
		$thumbnail = has_post_thumbnail() ? get_the_post_thumbnail(get_the_ID(), 'large') : '';
		$categories = get_the_category();
		$excerpt = get_the_excerpt();
		$post_id = get_the_ID();

		$main_category_link = '';
		if ($categories) {
			$main_category =  $categories[0]->name === "featured" ? $categories[1] : $categories[0];
			// $main_category = $categories[0];
			$main_category_link = '<p class="post-category"><a  href="'
				. esc_url(get_category_link($main_category->term_id)) . '">'
				. esc_html($main_category->name) . '</a></p>';
		}

		// $main_country_link = '';
		// // Obtener los términos de la taxonomía "country" asociados a la publicación
		// $terms = get_the_terms($post_id, 'country');
		// if (!empty($terms) && !is_wp_error($terms)) {
		// 	$first_term = $terms[0];
		// 	$main_country_link = "<p class='post-country'><a  href='" . esc_url(get_category_link($main_category->term_id)) . "' >$first_term->name</a></p>";
		// }


		// print_r($thumbnail);
		$html .= <<<EOT
					<article class="$post_classes">
							<a href="$permalink" class="absolute-link">$title</a>
							<div class="feature-img-container">
								$thumbnail
							</div>
							$main_category_link							
							<div class='post-list-body'>
								$main_country_link
								<h4><a href="$permalink">$title</a></h4>
								$excerpt
							</div>
					</article>
			EOT;
	}


	return $html;
}
// echo  printPostList();
// echo $algo;
