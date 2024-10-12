
var barnet_sldier_width = 0;
jQuery(document).ready(function ($) {
    // $(".about_us_menu a ").append("<i class='ico-about-us'></i>");
    var  use = $("#user_login").val();
    $(".login_user a span.menu-image-title-after.menu-image-title").text(use);
  //  $(".careers_menu   a  ,.sign_in_menu  a ,.login_user >a").append("<i class='icon icon-account'></i>");
    $(".samples_menu   a ").append("<i class='icon icon-sample'></i>");
    page_name=jQuery("#barnet-login-landing-page").val();
      if(page_name=="resource"){
        jQuery.ajax({
          type: 'post',
          url: url_ajax,
          data: {
              action: 'search_ajax_t_attr',
              dataType: "post",
              posts_per_page: 20,
              page_number: nPageNumber+1,
              productType:a,
              attrNum:attrNum,
    
          },
          success: function (response) {

          }
      });
    }


    $(".product__loadmore a").click(function(){
      
        if($('.widget_barnet_type_concept .slick-track').length){
          barnet_sldier_width=$(".widget_barnet_type_concept .slick-track").width();
          barnet_slider_item_width=$(".widget_barnet_type_concept .component-image-concepts__item.slick-cloned").first().innerWidth();
          $("#app .widget_barnet_type_concept .slick-track").width(barnet_sldier_width);
          $("#app .widget_barnet_type_concept .component-image-concepts__item.slick-cloned").width(barnet_slider_item_width);
        }
        if($('.widget_barnettype_anoucement .slick-track').length){
          barnet_sldier_width=$(".widget_barnettype_anoucement .slick-track").width();
          barnet_sldier_height=$(".widget_barnettype_anoucement .slick-list").height();
          barnet_slider_item_width=$(".widget_barnettype_anoucement .component-slider-hero__item").first().innerWidth();
          
          $("#app .widget_barnettype_anoucement .slick-list").height(barnet_sldier_height);
          $("#app .widget_barnettype_anoucement .slick-track").width(barnet_sldier_width);
          $("#app .widget_barnettype_anoucement .component-slider-hero__item").width(barnet_slider_item_width);
        }

        if($('.widget_barnet_type_landing .slick-track').length){
          barnet_sldier_width=$(".widget_barnet_type_landing .slick-track").width();
          barnet_slider_item_width=$(".widget_barnet_type_landing .component-image-content__item").first().innerWidth();
          $("#app .widget_barnet_type_landing .slick-track").width(barnet_sldier_width);
          $("#app .widget_barnet_type_landing .component-image-content__item").width(barnet_slider_item_width);
        }
    });


});

function bt_select_tab_by_attr(a){
  jQuery(".product__tabList_by_attr a").removeClass("active");
  jQuery(".product__tabList_by_attr [data-tab-name='"+a+"']").addClass("active");
  jQuery(".product_listing_container .product__listing").hide();
  jQuery(".product_listing_container [data-product-type='"+a+"']").show();
}

function bt_select_tab_by_prduct_type(a){
  jQuery(".product__tabList_by_allProduct a").removeClass("active");
  jQuery(".product__tabList_by_allProduct [data-tab-name='"+a+"']").addClass("active");
  jQuery(".prduct_fliter_box_wrapper_").hide();
  jQuery(".prduct_fliter_box_wrapper_[data-product-type='"+a+"']").show();
  jQuery(".product__listing").hide();
  jQuery(".product__listing[data-product-type='"+a+"']").show();


}

function bt_seeMore_by_att(a){
  page_number=jQuery(".product_listing_container [data-product-type='"+a+"'] #page_number").val();
  var url_ajax = jQuery('.exi-ajax_url').val();
  var attrNum = jQuery('.exi-attr_num').val();
  nPageNumber=  Number(page_number);
  total_item_num_attr= jQuery(".product_listing_container [data-product-type='"+a+"'] .total_item_num_attr").text();
  total_item_num = Number(total_item_num_attr);
  if(nPageNumber*20>=total_item_num)return;
  jQuery.ajax({
      type: 'post',
      url: url_ajax,
      data: {
          action: 'search_ajax_t_attr',
          dataType: "post",
          posts_per_page: 20,
          page_number: nPageNumber+1,
          productType:a,
          attrNum:attrNum,

      },
      success: function (response) {
        jQuery(".product_listing_container [data-product-type='"+a+"'] .component-list-product").append(response);

        jQuery(".product_listing_container [data-product-type='"+a+"'] #page_number").val(nPageNumber+1);
        //page_product_list_num_start = 20*nPageNumber+1;
        page_product_list_num_end = 20*(nPageNumber +1 );
        if(page_product_list_num_end>total_item_num)page_product_list_num_end=total_item_num;
        jQuery(".product_listing_container [data-product-type='"+a+"'] .page_product_list_num").html("1-"+page_product_list_num_end);
      }
  });
}

function seeAllProduct(){
    //jQuery("#see-more-producs-btn").show();
    jQuery("#see-all-products-btn").hide();
    filteringProduct();
}

function clickProductFilter(){
  jQuery("#page_number").val("0");
  jQuery("#see-all-products-btn").hide();
  filteringProduct();
  
}

function filteringProduct(){
 
  var url_ajax = jQuery('.exi-ajax_url').val();
  product_type=jQuery(".product__listing").attr("data-product-type");
  
  page_number=jQuery("#page_number").val();
  nPageNumber=  Number(page_number);
  total_item_num_attr= jQuery(".product__listing .total_item_num_attr").text();
  total_item_num = Number(total_item_num_attr);
  if(nPageNumber)if(nPageNumber*20>=total_item_num)return;

  var terms_array=[];
  jQuery('.product-filter-wrapp input:checked').each(function(){

     t_name=jQuery(this).attr("data-slug");
     t_tax=jQuery(this).attr("taxonomy");
     /*
     newFlag=true;
     for(i=0;i<terms_array.length;i++){
         if(terms_array[i].taxonomy==t_tax){
             terms_array[i].terms.push(t_name);
             terms_array[i].field="slug";
             newFlag=false;
         }
     }
     if(newFlag){
         terms_array.push({taxonomy:t_tax, field: "slug", terms: [t_name] });
     }
     */
     terms_array.push({taxonomy:t_tax, field: "slug", terms: [t_name] });
  });

  jQuery("#loadmre_loading_img").show();

  jQuery.ajax({
    type: 'post',
    url: url_ajax,
    data: {
        action: 'search_ajax_t',
        dataType: "post",
        posts_per_page: 20,
        page_number: nPageNumber+1,
        productType:product_type,
        terms_array: terms_array, 
    },
    success: function (response) {
      if(nPageNumber==0){
        jQuery(".component-list-product").html('');
      }
      jQuery(".product__listing #see-more-producs-btn").remove();
      jQuery(".product__listing .component-list-product").append(response);
      jQuery("#page_number").val(nPageNumber+1);
      jQuery("#loadmre_loading_img").hide();
    }
  });
}

function filteringProductByAll(a){
    var url_ajax = jQuery('.exi-ajax_url').val();
    product_type=a;
    page_number=jQuery(".product__listing[data-product-type='"+a+"'] #page_number").val();
    nPageNumber=  Number(page_number);
    total_item_num_attr= jQuery(".product__listing[data-product-type='"+a+"'] .total_item_num_attr").text();
    total_item_num = Number(total_item_num_attr);
    if(nPageNumber)if(nPageNumber*20>=total_item_num)return;
    var terms_array=[];
    jQuery(".prduct_fliter_box_wrapper_[data-product-type='"+a+"'] input:checked").each(function(){

      t_name=jQuery(this).attr("data-slug");
      t_tax=jQuery(this).attr("taxonomy");
    /*
      newFlag=true;
      for(i=0;i<terms_array.length;i++){
          if(terms_array[i].taxonomy==t_tax){
              terms_array[i].terms.push(t_name);
              terms_array[i].field="slug";
              newFlag=false;
          }
      }
      if(newFlag){
          terms_array.push({taxonomy:t_tax, field: "slug", terms: [t_name] , operator:'IN'});
      }
      */
     terms_array.push({taxonomy:t_tax, field: "slug", terms: [t_name] });
  });

  jQuery("#loadmre_loading_img").show();

  jQuery.ajax({
    type: 'post',
    url: url_ajax,
    data: {
        action: 'search_ajax_t_allPro',
        dataType: "post",
        posts_per_page: 20,
        page_number: nPageNumber+1,
        productType:product_type,
        terms_array: terms_array, 
    },
    success: function (response) {
      if(nPageNumber==0){
        jQuery(".product__listing[data-product-type='"+a+"'] .component-list-product").html('');
      }
      jQuery(".product__listing[data-product-type='"+a+"'] #see-more-producs-btn").remove();
      jQuery(".product__listing[data-product-type='"+a+"'] .component-list-product").append(response);
      jQuery(".product__listing[data-product-type='"+a+"'] #page_number").val(nPageNumber+1);
      total_item_num_attr= jQuery(".product__listing[data-product-type='"+a+"'] .total_item_num_attr").text();
      jQuery(".product__tabList_by_allProduct [data-tab-name='"+a+"'] span").text(total_item_num_attr);
      jQuery("#loadmre_loading_img").hide();
    }
  });
}

function clickfilterByall(a){
  jQuery(".product__listing[data-product-type='"+a+"'] #page_number").val("0");
  filteringProductByAll(a);  
}

function seeAllResource(){
  jQuery("#see-all-products-btn").hide();
  jQuery(".product__resourceLanding").hide();
  jQuery(".product__listing").show();
  jQuery(".product__listing #page_number").val("0");
 // jQuery(".product-container").show();
  //filteringProduct(); 
  filteringResource();
}

function filteringResource(){
  var url_ajax = jQuery('.exi-ajax_url').val();
  page_number=jQuery("#page_number").val();
  nPageNumber=  Number(page_number);
  total_item_num_str= jQuery(".product__listing .resource-total-numbers").text();
  total_item_num = Number(total_item_num_str);
  if(nPageNumber)if(nPageNumber*20>=total_item_num)return;

  var terms_array=[];
  jQuery('.product-filter-wrapp input:checked').each(function(){

     t_name=jQuery(this).attr("data-slug");
     t_tax=jQuery(this).attr("taxonomy");
     /*
     newFlag=true;
     for(i=0;i<terms_array.length;i++){
         if(terms_array[i].taxonomy==t_tax){
             terms_array[i].terms.push(t_name);
             terms_array[i].field="slug";
             newFlag=false;
         }
     }
     if(newFlag){
         terms_array.push({taxonomy:t_tax, field: "slug", terms: [t_name] });
     }
     */
     terms_array.push({taxonomy:t_tax, field: "slug", terms: [t_name] });
  });

  jQuery("#loadmre_loading_img").show();

  jQuery.ajax({
    type: 'post',
    url: url_ajax,
    data: {
        action: 'search_ajax_t_resource',
        dataType: "post",
        posts_per_page: 20,
        page_number: nPageNumber+1,
        terms_array: terms_array, 
    },
    success: function (response) {
      if(nPageNumber==0){
        jQuery(".component-list-product").html('');
      }
      //jQuery(".product__listing #see-more-producs-btn").remove();
      jQuery(".product__listing .component-related-video").append(response);
      jQuery("#page_number").val(nPageNumber+1);
      jQuery("#loadmre_loading_img").hide();
    }
  });
}