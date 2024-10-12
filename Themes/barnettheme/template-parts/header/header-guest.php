<div class="header__main">
    <div class="container">
        <div class="menumobile" data-menumobile>
            <div class="menumobile__toggle" data-toggle><span></span></div>
            <div class="menumobile__menu" data-menu>
                <div class="menumobile__wrap">
                    <ul>
                        <?php wp_nav_menu (array('theme_location' => 'mobile','menu_class' => ''));?>
                    </ul>
                    <ul>
                        <li><a href="<?php echo wp_login_url();?>" title="Sign In"><span>Sign In</span></a>
                        </li>
                        <li>
					<a href="/no-samples-selected" title="Samples" rel="stylesheet" data-anchor-link><i class="icon icon-sample"></i><span class="header__samples-text">Samples</span><span class="header__samples-count" data-count-sample>0</span></a>
                    </li>
                    </ul>

                </div>
            </div>
        </div>
        <div class="header__logo"><a href="/" title="Home" rel="stylesheet"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-barnet.svg" alt=""></a>
        </div>
       <div class="header__right">
         <div class="header__menuNav">
            <?php wp_nav_menu (array('theme_location' => 'menu_topbar','menu_class' => ''));?>
            
            </div>
            <div class="header__samples">
                <a href="/no-samples-selected" title="Samples" rel="stylesheet" data-anchor-link><i class="icon icon-sample"></i><span class="header__samples-text">Samples</span><span class="header__samples-count" data-count-sample>0</span></a>
            </div> 
            <div class="header__search-toggle" data-search-toggle><i class="icon icon-search icon-gtm-search"></i></div>
        </div>
    </div>
</div>