jQuery(document).ready(function () {
    
    a="";
    if (jQuery("#barnet_product_type_updated").length) {
        if(jQuery("#barnet_product_type_updated").val()=="unupdated"){
            a='<a href="'+window.location.href+'&unupdated=unupdated">Please set product type of all the product as term.</a>';
        }

    }
    h='<div class="rwmb-field rwmb-select-wrapper"><div class="rwmb-label" id="product_type-label"><label for="product_type">Product Type</label></div>'+'<div class="rwmb-input">'+a+'<select id="product_type_term" class="rwmb-select" name="product_type_term">'+jQuery("#Product-Type-temp #product_type_term").html()+'</select></div></div>';
    jQuery("#barnet-product .rwmb-meta-box").prepend(h);
    jQuery("#Product-Type-temp").remove();
    
})