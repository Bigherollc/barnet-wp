jQuery(document).ready(function () {
    current_template=jQuery("#current_page_template").val();
    if("templates/landing-page-product-type.php" != current_template && "templates/product-type-listing-page.php" != current_template){
        jQuery("#Product-Type-Page-temp").hide();
    }
    else{
        jQuery("#Product-Type-Page-temp").show();
    }
    jQuery("#page_template").change(function(){
        selected_template=jQuery(this).val();
        if("templates/landing-page-product-type.php" != selected_template && "templates/product-type-listing-page.php" != selected_template){
            jQuery("#Product-Type-Page-temp").hide();
        }
        else{
            jQuery("#Product-Type-Page-temp").show();
        }
    });
});