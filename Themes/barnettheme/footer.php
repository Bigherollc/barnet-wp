	<footer class="footer">
	    <div class="footer__wrapper">
	        <?php get_template_part('template-parts/footer/footer-sites'); ?>
	    </div>
	    <div class="footer__wrapper --small --dark">
	        <div class="container">
	            <ul class="footer__nav">
	                <li>
	                    <?php echo do_shortcode(get_theme_mod('copyright_text')); ?>
	                </li>
	                <li>
	                    <?php echo do_shortcode(get_theme_mod('terms_of_use_text')); ?>
	                </li>
	                <li>
	                    <?php echo do_shortcode(get_theme_mod('privacy_policy_text')); ?>
	                </li>
	            </ul>
	        </div>
	    </div>
	</footer>
	
	<div class="component-confirm-popup" data-confirm-popup="">
	    <div class="component-confirm-popup__popup">
	        <div class="component-confirm-popup__text-content" data-content=""><?php _e('Are you sure ?'); ?></div>
	        <div class="component-confirm-popup__btn-group">
	            <button class="btn btn-regular" data-btn-no=""><?php _e('Cancel'); ?></button>
	            <button class="btn btn-solid" data-btn-yes=""><?php _e('OK'); ?></button>
	        </div>
	    </div>
	    <div class="component-confirm-popup__overlay"></div>
	</div>
	<?php wp_footer(); ?>
	</body>

	</html>