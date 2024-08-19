(function () {
	document.addEventListener("DOMContentLoaded", function () {
		function query(node, whete = document) {
			return whete.querySelector(node);
		}

		var searchInput = query("#resource-search-term")
		var searchBtn = query("#glosssary-search-btn")
		var selectContentType = query(".content_type")
		const thisUrl = "{{ content.absolute_url }}"

		// Obtener la cadena de consulta (query string)
		const queryString = window.location.search;
		// Crear un objeto URLSearchParams a partir de la cadena de consulta
		const params = new URLSearchParams(queryString);
		// Obtener un parámetro específico por su nombre
		const page_num = params.get('page_num') ? params.get('page_num') : false;
		const term = params.get('term') ? params.get('term') : false;
		const content_type = params.get('content_type') ? params.get('content_type') : false;

		// if (term) { searchInput.value = term }
		// if (content_type) { selectContentType.value = content_type }

		selectContentType.addEventListener("input", queryUpdeted)

		function queryUpdeted(e) {
			if (e.target.value !== content_type || term !== searchInput.value) {
				redirect()
				// searchBtn.classList.add("rubber-band-x")
			} else {
				searchBtn.classList.remove("rubber-band-x")
			}
		}

		searchBtn.addEventListener("click", redirect)

		function redirect() {
			const params = new URLSearchParams();

			if (selectContentType.value != "") {
				// console.log("selectContentType");
				params.append('content_type', selectContentType.value);
			}
			if (searchInput.value.trim() != "") {
				// console.log("searchInput");
				params.append('term', searchInput.value);
			}
			let url = thisUrl
			// 			console.log(params);
			if (params && params.size) {
				url = thisUrl + `?${params.toString()}`;
			}
			// 			console.log(url);
			window.location.href = url + "#focus"
		}

		// search by pressing "Enter"
		searchInput.addEventListener('keypress', function (event) {
			if (event.keyCode === 13) {
				redirect();
			}
		});

	});
})();



(function () {
	document.addEventListener("DOMContentLoaded", function () {
		// Obtener la URL actual del navegador
		const urlActual = window.location.href;

		// Crear un objeto URL para analizar la URL
		const urlObj = new URL(urlActual);

		// Obtener el valor del parámetro "file"
		const valorParametro = urlObj.searchParams.get('file');

		// Imprimir el valor del parámetro en la consola
		console.log('Valor del parámetro "file":', valorParametro);

		document.querySelectorAll(".hidden-btn").forEach(btn => {
			console.log(btn);
			if (!btn.classList.contains(valorParametro)) {
				btn.remove()

			} else {
				btn.style.display = "block"
			}
		});
	});
})();


// ------------


(function () {
	document.addEventListener("DOMContentLoaded", function () {
		var themeSwitch = document.querySelector("#themeSwitch")
		if (!themeSwitch) return;
		var html = document.querySelector("html")
		let onpageLoad = localStorage.getItem("cf-theme-dark") || "";

		if (onpageLoad !== "" && onpageLoad !== null || html.classList.contains("cf-theme-dark")) {
			themeSwitch.checked = true
		}

		console.log(themeSwitch);
		themeSwitch.addEventListener("change", function (input) {
			if (themeSwitch.checked) {
				html.classList.add("cf-theme-dark")
				localStorage.setItem("cf-theme-dark", "cf-theme-dark");
				return
			}
			html.classList.remove("cf-theme-dark")
			localStorage.setItem("cf-theme-dark", "");
		})

	});
})();
// On page load set the theme.
(function () {
	let onpageLoad = localStorage.getItem("cf-theme-dark") || "";
	document.querySelector("html").classList.add(onpageLoad)
})();

