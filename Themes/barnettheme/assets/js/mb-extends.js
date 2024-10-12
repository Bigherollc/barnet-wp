//var prod = [];

(function($){

  $(document).ready(function(){
    showInteractiveImage();
    jQuery("#concepts_to_interactives_from").change(function () {
      showInteractiveImage();
    })

    var $allSelectIngredients = $('.rwmb-field.rwmb-post-wrapper select[id*="formula_ingredients"]');

    $allSelectIngredients.get().forEach(element => {
      $(element).parent().parent().attr('onclick', 'checkLink(this)');
    });
  });

}(jQuery))

function checkLink(e){
  ip = jQuery(e).find("select[data-select2-id*='formula_ingredients']");
    ip.change(function(){
      $el = jQuery(this);
      index = $el.val();
      prod = JSON.parse($el.attr('data-options'));
      if(prod['data'] != undefined && prod['data'][index] != undefined){
        var data = prod['data'][index].split('*|*');
        var root = jQuery(this).parent().parent().parent();
        root.find("input[id*='formula_ingredients'][name*='f_material']").val(data[0]);
        root.find("input[id*='formula_ingredients'][name*='f_inci']").val(data[1]);
        root.find("input[id*='formula_ingredients'][name*='f_supplier']").val('BARNET');
      }
    })
}

function showInteractiveImage() {
  if (jQuery("#bn_concept_interactive_image_block").length == 1) {
    jQuery("#bn_concept_interactive_image_block").empty();
  } else {
    jQuery("#bn_concept_interactive_image_block").remove();
    if (jQuery("#ia_coordinates").length == 1) {
      jQuery("#ia_coordinates").attr('readonly','readonly');
      jQuery("#ia_coordinates").parent().append('<hr><div id="bn_concept_interactive_image_block" style="margin-top:10px;"></div>');
    }
  }

  var inputConcept = jQuery("#concepts_to_interactives_from");
  var htmlInterActive = "";
  if (inputConcept.length == 1) {
    if (inputConcept.val() != "") {
      wp.ajax.post('barnet_get_concept_interactive_image', {
        id: inputConcept.val()
      }).done(function (response) {
        _le = jQuery("#ia_coordinates").val();
        _le= _le.split(',');
        
        jQuery("#bn_concept_interactive_image_block").html('<img id="bn_concept_interactive_image" src="' + response +'" style="width:100%;">').click(function(e){
          jQuery('.ia_click').remove();
          _x = e.offsetX*100/jQuery(jQuery(this).find('#bn_concept_interactive_image')).width();
          _y = e.offsetY*100/jQuery(jQuery(this).find('#bn_concept_interactive_image')).height();
          jQuery("#ia_coordinates").val(_x.toFixed(2)+','+_y.toFixed(2));
          jQuery(this).append('<div class="ia_click" style="left:'+(_x.toFixed(2))+'%;top:'+(_y.toFixed(2))+'%;"></div>');
        });

        jQuery("#bn_concept_interactive_image_block").append('<div class="ia_click" style="left:'+(parseFloat(_le[0]))+'%;top:'+(parseFloat(_le[1]))+'%;"></div>');
      }).fail(function () {
      });
    }
  }

}