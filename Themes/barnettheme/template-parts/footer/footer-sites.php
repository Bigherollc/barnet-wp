<?php
$footer_infor = get_theme_mod( 'site_information','');
$com_address = get_theme_mod( 'company_address','');
$com_phone = get_theme_mod( 'company_phone','');
?>
<div class="container">
  <div class="row">
    <div class="col-sm-6">
      <div class="footer__description">
        <div class="footer__description--logo"><a href=/" title="Logo"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-barnet-medium.svg" alt="Logo"></a>
        </div>
        <div class="footer__description--content">
          <p><?php echo $footer_infor; ?></p>
        </div>
      <a class="btn" href="/about-us/" title="About Us">About Us</a>
         <a class="btn" href="/careers/" title="Careers">Careers</a>
        </div>
    </div>
    <div class="col-sm-4 offset-sm-2">
      <div class="footer__information">
        <div class="footer__information--content">
          <?php echo $com_address; ?>
          <p> <a href="tel:#<?php echo $com_phone; ?>" title="phone"><?php echo $com_phone; ?></a>
          </p>
        </div>
        <a class="btn btn-gtm-contact-us" href="/contact-us/" title="Contact Us">Contact Us</a>
      </div>
    </div>
  </div>
</div>