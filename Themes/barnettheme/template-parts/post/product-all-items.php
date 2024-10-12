<?php
$posts = $args["posts"];


?>
<div class="service-product-item">
    <img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/product-tab.png' ?>">
    <img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/register-block.png' ?>">
    <div class="product-list" style="width:100%">
        <?php
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;

        if (count($posts) > 0) :
            $postMetaManager = new BarnetPostMetaManager($posts);
            foreach ($posts as $post):
                $postMetas = $postMetaManager->getMetaData($post->ID);
                $product_roles = $postMetas["product_roles"];

                if(!empty($product_roles) && !in_array($roles[0], $product_roles)){
                    continue;
                }

                if ( is_user_logged_in() ) {
                    $description = $postMetas["product_description_logged"][0];
                } else {
                    $description = $postMetas["product_description"][0];
                }

                $arrayMetaDescription = unserialize($description);
                $description = $arrayMetaDescription[0];

                $nameMeta = $postMetas["trade_name"][0];
                ?>
                <h2><a href="<?php echo the_permalink($post->ID); ?>"><?php echo $nameMeta; ?></a></h2>
                <p><?php echo strip_tags($description, '<br>'); ?></p>
            <?php
            endforeach;
        else :
            ?>
            <h2><a href="#" onclick="return false;"><?php echo esc_html__("Products"); ?></a></h2>
            <p><?php echo esc_html__("No results were found."); ?></p>
        <?php
        endif;
        ?>
    </div>
</div>