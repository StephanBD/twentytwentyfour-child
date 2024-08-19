jQuery(document).ready(function ($) {
	// ----------------------
	console.clear()
	// ----------------------
	let numDebug = 1;
	const debug_this = (item) => {
		console.log(numDebug++, item);
	}
	// *----------------------
	var isLoading = false
	// var currentPage = 1
	// var activeCategories = {}
	var activeQueryTaxonomies = {}

	var activeQuery = {
		search: { s: null },
		allCategories: false,
		currentPage: 1
	}
	// var currentQuery = {};
	const categoryDisplay = $("#category-display")
	// const postsDisplay = $("#posts-display")
	const postsListing = $("#posts-listing")
	const categoryFilter = $("#category-filter")
	const postPagination = $("#post-pagination")
	const ldsroller = $(".lds-roller")
	const searchInput = $("#search-post-name")
	const searchBtn = $("#search-post-btn")
	const searchFilter = $("#filter-search")
	const searchFilterClose = $("#filter-search-close")
	const allCategoryBtn = $(".all-categories a, #all-categories, title[all-categories], [data-title=all-categories]");
	const toggleBlogMenu = $(".toggle-blog-menu")
	const dropdown = $("#top-nav-menu .dropdown")
	const dropdownButton = $(".dropdown");

	// *-----------------------------------------------------------
	toggleBlogMenu.on('click', function () {
		dropdown.css('display', dropdown.css('display') === 'none' ? 'flex' : '');
	});


	// *-----------------------------------------------------------
	$('.cat-link').on('click', function (e) {
		let taxonomyName = $(this).data("taxonomy");
		let taxId = $(this).data("id");
		// console.log({taxonomyName, taxId});

		e.preventDefault();
		if (taxonomyName) {
			if (addQuery(taxId, $(this).text(), taxonomyName)) {
				callPosts();
			}
		}
	});


	// *-----
	searchBtn.on('click', function (e) {
		e.preventDefault();
		let searchValue = searchInput.val().trim();
		if (searchValue.length >= 3) {
			setSearchQuery(searchValue);
		}
	});

	searchInput.on('keypress', function (event) {
		let searchValue = searchInput.val().trim();
		if (event.which === 13 && searchValue.length > 0) {
			setSearchQuery(searchValue);
		}
	});

	function setSearchQuery(term = null, callAjax = true) {
		let searchValue = term || searchInput.val().trim();
		activeQuery["search"]["s"] = searchValue;
		searchFilter.find("span").text(searchValue);
		searchFilter.show();
		if (callAjax) {
			callPosts();
		}
	}

	// * ----
	searchFilterClose.on("click", cleanSearch);

	function cleanSearch() {
		if (activeQuery["search"]["s"] !== null) {
			activeQuery["search"]["s"] = null
			searchFilter.hide()
			searchFilter.find("span").text("")
			callPosts()
			// toggleCategoryPosts()
		}
	}


	// *-----------------------------------------------------------

	function removeQuery(id, item) {
		if (activeQueryTaxonomies[item]?.[id]) {
			delete activeQueryTaxonomies[item][id];
			callPosts();
		}
	}

	function addQuery(id, categoryName, taxName) {
		if (!taxName || activeQueryTaxonomies?.[taxName]?.[id]) return false;

		activeQueryTaxonomies[taxName] = activeQueryTaxonomies[taxName] || {};
		activeQueryTaxonomies[taxName][id] = categoryName;

		printFilterQuery(id, categoryName, taxName);

		return true;
	}


	// *-----------------------------------------------------------

	function toggleLoading(show) {
		if (show) {
			ldsroller.show();
			postsListing.hide();
			isLoading = true;
		} else {
			ldsroller.hide();
			postsListing.show();
			isLoading = false;
		}
	}


	// * -----------------------------------------------------------
	function callPosts(forceCall = false) {
		if (isLoading) return;
		toggleLoading(true);

		const { currentPage, search, allCategories } = activeQuery;
		const { posttype } = myAjax;

		let data = {
			page: currentPage,
			action: 'obtener_posts_por_categoria',
			taxonomies: {},
			posttype: posttype ? posttype : "post"
		}

		if (search && search.s) {
			data.search = search.s.trim();
		}

		if (allCategories) {
			data.all = true;
		} else {
			for (const key in activeQueryTaxonomies) {
				if (Object.keys(activeQueryTaxonomies[key]).length > 0) {
					data.taxonomies[key] = Object.keys(activeQueryTaxonomies[key]);
				}
			}
		}

		const taxonomiesLength = Object.keys(data.taxonomies).length;
		const shouldReturn = taxonomiesLength === 0 && !data.all && !data.search && !forceCall;
		debug_this(data)
		if (shouldReturn) {
			toggleLoading(false);
			toggleCategoryPosts(false);
			return false;
		}

		$.ajax({
			url: myAjax.ajaxurl,
			type: 'POST',
			data: data,
			success: function (response) {
				if (response.total_pages > 0) {
					printPostJson(response.posts);
					printPagination(response.total_pages);
					totalPages = response.total_pages;
					// currentQuery = JSON.stringify(activeQuery);
					toggleCategoryPosts(true);
					return true;
				} else {
					$('#posts-listing').html('No posts.');
					toggleCategoryPosts(true);
					return false;
				}
			},
			error: function (error) {
				console.log(error);
				toggleCategoryPosts(false);
				return false;
			}
		}).always(function () {
			toggleLoading(false);
		});
	}



	// * -----------------------------------------------------------
	function printPostJson(posts) {
		postsListing.html(posts);
	}
	// *-----------------------------------------------------------
	toggleCategoryPosts(myAjax.havePosts)
	function toggleCategoryPosts(isTrue = false) {
		if (
			isTrue
		) {
			categoryDisplay.hide()
			postsListing.show()
			return
		}
		categoryDisplay.show()
		postsListing.hide()
	}


	// *-----------------------------------------------------------
	function printPagination(totalPages) {
		postPagination.empty()
		if (totalPages <= 1) return;

		for (let index = 1; index < totalPages + 1; index++) {
			var className = activeQuery.currentPage == index ? "post-page active" : "post-page"
			var html = $('<a>').text(index).attr('href', getUrlPage(index))
				.addClass(className)
				.on("click", function (e) {
					e.preventDefault()
					setPagination(index)
				})
			postPagination.append($('<li>').append(html))
		}
	}

	postPagination.find(".post-page").on("click", function (e) {
		e.preventDefault()
		setPagination($(this).text())
	})

	function setPagination(pageID) {
		if (activeQuery.currentPage == pageID) return;
		activeQuery.currentPage = pageID
		callPosts(true)
	}

	function getUrlPage(pageNumber) {
		let currentURL = window.location.href;
		let paginatedURL = `${currentURL.replace(/\/page\/\d+/i, '')}page/${pageNumber}`;
		return paginatedURL;
	}

	// *-----------------------------------------------------------
	function printFilterQuery(index, post, query) {
		var html = $('<span>').addClass("cat-filter")
			.append($("<span>").text(post))
			.append($("<span>").text("x").attr('data-id', index)
				.addClass("remove-filter").on("click", function () {
					removeQuery(index, query)
					$(this).parent().remove()
				}))
		categoryFilter.append(html)
	}


	// * drop down ------------------------------------------------
	dropdownButton.on("click", function (e) {
		if (e.target.matches(".dropdown-button")) {
			$(this).toggleClass("active")
		}
	});

	// Cerrar el menú desplegable si se hace clic fuera de él
	window.addEventListener("click", function (e) {
		if (!e.target.matches(".dropdown-button")) {
			dropdownButton.removeClass("active")
		}
	});

	// -------------------------------
	allCategoryBtn.on("click", function (e) {
		e.preventDefault();
		activeQuery.allCategories = !activeQuery.allCategories;
		allCategoryBtn.toggleClass("active");
		activeQuery.currentPage = 1
		callPosts();
	});

	// *-----------------------------------------------------------
	if (typeof myAjax.categoryId !== 'undefined' && typeof categoryName !== 'undefined') {
		addQuery(myAjax.categoryId, categoryName)
	}

	if (myAjax.valorBusqueda) {
		setSearchQuery(myAjax.valorBusqueda, false)
		searchInput.val(myAjax.valorBusqueda)
	}



});

