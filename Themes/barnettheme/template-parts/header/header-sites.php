<?php
$user = wp_get_current_user();
$urlProfile = '#';
$urlChangePass = '#';
if (function_exists("tml_get_action_url")) {
    $urlProfile = tml_get_action_url('dashboard');
    $urlChangePass = tml_get_action_url('changepassword');
}
?>
<div class="header__main">
    <div class="container">
        <div class="menumobile" data-menumobile>
            <div class="menumobile__toggle" data-toggle><span></span></div>
            <div class="menumobile__menu" data-menu>
                <div class="menumobile__wrap">
                    <ul>
                        <?php get_template_part( 'template-parts/header/header-menus-mobile' ); ?>
                        <li>
                            <a href="/no-samples-selected" title="Samples" data-anchor-link><span>Samples</span><span class="count d-none" data-count-sample>0</span></a>
                        </li>
                    </ul>
                    <ul>
                        <li><a class="--active" href="<?php echo $urlProfile;?>" title="Profile Settings"><span>Profile Settings</span></a>
                        </li>
                        <li><a href="<?php echo $urlChangePass;?>" title="Change Password"><span>Change Password</span></a>
                        </li>
                        <li><a href="<?php echo wp_logout_url();?>" title="Logout"><span>Logout</span></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="header__logo"><a href="/" title="Home" rel="stylesheet"><img src="https://barnet-cosmetics.com/wp-content/themes/barnettheme/assets/images/logo-barnet.svg" alt=""></a>
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