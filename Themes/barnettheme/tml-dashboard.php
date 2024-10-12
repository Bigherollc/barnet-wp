<?php
$user = wp_get_current_user();
$userInfo = $user->user_extra_info;
$userExtraInfo = array();
if (!empty($userInfo)) {
    $userExtraInfo = unserialize($userInfo);
}
$userCustomer = array();
if (class_exists('UserEntity')) {
    $userEntity = new UserEntity($user->ID);
    $userCustomer = $userEntity->getCustomers();
}

$urlEditProfile = "#";
if (function_exists("tml_get_action_url")) {
    $urlEditProfile = tml_get_action_url('updateprofile' );
}


?>
<?php get_header(); ?>
    <main role="main">
        <section class="component-my-account" data-request-step data-url="/wp-json/barnet/v1/updatenewsletter" data-method="POST">
            <div class="container">
                <div class="component-form">
                    <form class="component-form__form">
                        <div class="component-form__wrapper" style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/bg-hexagon.png)">
                            <div class="component-my-account__infor">
                                <div class="component-my-account__infor--wrapper">
                                    <div class="component-heading-group --dark-mode">
                                        <h2 class="component-heading-group__heading --size-lg to-uppercase">
                                            <?php _e('My Account'); ?>
                                        </h2>
                                    </div>
                                    <div class="component-form__group">
                                        <div class="component-my-account__user">
                                            <p><?php echo $user->first_name;?> <?php echo $user->last_name;?></p>
                                            <p><?php echo $user->user_email;?></p>
                                            <p><?php echo !empty($userExtraInfo["phone"]) ? 'W: ' . $userExtraInfo["phone"] : '';?></p>
                                            <p><?php echo !empty($userExtraInfo["phone_optional"]) ? 'M: ' .$userExtraInfo["phone_optional"] : '';?></p>
                                        </div>
                                        <div class="component-my-account__company">
                                            <p><?php echo isset($userExtraInfo["company_name"]) ? $userExtraInfo["company_name"] : '';?></p>
                                            <p><?php echo isset($userExtraInfo["address"]) ? $userExtraInfo["address"] : '';?></p>
                                            <p><?php echo isset($userExtraInfo["address_optional"]) ? $userExtraInfo["address_optional"] : '';?></p>
                                            <p><?php echo !empty($userExtraInfo["phone_optional"]) ? 'Job Title/Role: ' .$userExtraInfo["job_title_role"] : '';?></p>
                                            <p><?php echo isset($userExtraInfo["province"]) ? $userExtraInfo["province"] : '';?> <?php echo isset($userExtraInfo["postal_code"]) ? $userExtraInfo["postal_code"] : '';?></p>
                                            <p><?php echo isset($userExtraInfo["city"]) ? $userExtraInfo["city"] : '';?></p>
                                            <?php
                                            $country = isset($userExtraInfo["country"]) ? $userExtraInfo["country"] : '';
                                                $getListCountry = Barnet::getListCountries();
                                                foreach ($getListCountry as $k => $v) {
                                                    if ($country == $k) {
                                                        $country = $v;
                                                    }
                                                }
                                            ?>
                                            <p><?php echo $country;?></p>
                                        </div>
                                    </div>
                                    <!--<div class="component-form__group">
                                        <div class="component-form-group__input">
                                            <div class="component-form__item">
                                                <div class="form-checkbox --dark-mode">
                                                    <input type="checkbox" id="checkbox1" name="newsletter" <?php checked(isset($userExtraInfo['newsletter']) ? $userExtraInfo['newsletter'] : 0, 'on'); ?> data-checkbox>
                                                    <label for="checkbox1"> <?php _e('Yes, I want to get the newsletter and stuff.');?>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>-->
                                    <div class="component-form__group">
                                        <a class="btn btn-regula" href="<?php echo $urlEditProfile; ?>" title="Edit">
                                            <?php _e('Edit');?></a>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($userCustomer)) :?>
                            <div class="component-my-account__newsletter">
                                <div class="component-my-account__newsletter--wrapper">
                                    <?php foreach ($userCustomer as $u) : ?>
                                        <?php
                                            if (empty($u['data']['post_title']) || empty($u['relationship']['digitals'])) {
                                                continue;
                                            }
                                            $urlApi = '/wp-json/barnet/v1/customer/' . $u['data']['id'] . '/products';
                                        ?>
                                        <a class="component-my-account__newsletter--item"
                                           href="#" data-gen-pdf
                                           data-api="<?php echo $urlApi;?>"
                                           title="<?php echo esc_html($u['data']['post_title']);?>">
                                            <div class="icon icon-spec"></div>
                                            <div class="component-my-account__content">
                                                <div class="component-my-account__title">
                                                    <?php echo $u['data']['post_title']; ?>
                                                </div>
                                                <div class="component-my-account__date">
                                                    <?php echo date('n/j/Y', strtotime($u['data']['post_modified'])) ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>

                                </div>
                                <div class="component-form__group d-sm-none">
                                    <button class="btn btn-regula" data-next-btn type="submit"><?php _e('Edit');?></button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
<?php get_footer(); ?>