<?php

// Encola el estilo
wp_enqueue_style(
	'mystyles',
	get_stylesheet_directory_uri() . '/template-parts/app/style.css',
	array(),
	'1.0',
	'all'
);
wp_enqueue_script(
	'myscript',
	get_stylesheet_directory_uri() . '/template-parts/app/app.js',
	array(),
	'1.0',
	'all'
);


echo $content;

?>

<div id="my-thing" style="position: absolute;top:10px;right:10px;padding:24px;">

</div>

<script>
	// Crear el elemento div
	const miElemento = document.createElement('textarea');

	// Establecer el ID y estilos del elemento
	miElemento.id = 'my-thing';
	miElemento.style.position = 'absolute';
	miElemento.style.top = '10px';
	miElemento.style.right = '10px';
	miElemento.style.padding = '24px';
	miElemento.style.background = '#e6cc48';
	miElemento.style.zIndex = '1000';
	miElemento.style.maxWidth = '500px';
	miElemento.style.minWidth = '100%';
	miElemento.style.borderRadius = '16px';
	miElemento.textContent = args.text;

	// Agregar el elemento al cuerpo del documento (DOM)
	document.body.appendChild(miElemento);
</script>