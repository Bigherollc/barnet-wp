jQuery(document).ready(function($) {
	var $parent = $('.section__all')
	var $listing = $parent.find('.posts-listing');
	var $loader = $parent.find('.pagination__loader');
	var $loadButton = $parent.find('.pagination__btn');
	var ajaxURL = $listing.data('ajax-url');
	var $featuredPost = $('.section__featured').find('.post-item')
	var featuredPostId = $featuredPost.data('post-id')
	var template = $listing.children().first().clone()

	$loadButton.on('click', function(e) {
		e.preventDefault();
		$loader.show()

		$.ajax({
			url: ajaxURL,
			type: 'GET',
			data: {
				action: 'load_posts_by_ajax',
				page: $loadButton.data('page'),
				per_page: 6,
				featured_post_id: featuredPostId
			},
			success: function(response) {
				var data = JSON.parse(response)
				$loadButton.data('page', $loadButton.data('page') + 1);
				
				if(data.page >= data.max_page) {
					$loadButton.hide()
				}

				data.posts.forEach(function(post) {
					$listing.append(renderPost(post))
				})
			},
			complete: function() {
				$loader.hide()
			}
		})
	})

	function renderPost(post) {
		var $template = template.clone()
		$template.data('post-id', post.id)
		$template.attr('id', 'post-' + post.id)
		$template.find('a').attr('href', post.permalink)
		$template.find('.img-thumbnail').attr('src', post.thumbnail)
		$template.find('.post-item__title a').text(post.title)
		return $template
	}
});