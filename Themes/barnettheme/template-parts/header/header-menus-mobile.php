<?php
if (is_user_logged_in() || is_plugin_active("user-menus/user-menus.php") && has_nav_menu('main-menu')) :
    $main_menu = wp_get_nav_menu_object('main-menu');
   if(!empty($main_menu)){
    $main_menu_items = wp_get_nav_menu_items($main_menu->term_id, []);
    ?>

    <?php foreach ($main_menu_items as $menu) : ?>
        <li>
            <a class="--active" href="<?php echo $menu->url; ?>"
               title="<?php echo $menu->title; ?>"><span><?php echo $menu->title; ?></span></a>
        </li>
    <?php endforeach; ?>
<?php } endif; ?>
