<?php
$main_menu = wp_get_nav_menu_object('main-menu');
if(!empty($main_menu)){
$main_menu_items = wp_get_nav_menu_items($main_menu->term_id, []);
?>
<div class="header__menu">
	<div class="header__menu">
	  <div class="container">
	    <ul>
	      <?php foreach($main_menu_items as $menu): ?>
	      <li>
	        <a class="--active" href="<?php echo $menu->url; ?>" title="<?php echo $menu->title; ?>"><span><?php echo $menu->title; ?></span></a>
	      </li>
	      <?php endforeach; ?>
	    </ul>
	  </div>
	</div>
</div>
<?php }