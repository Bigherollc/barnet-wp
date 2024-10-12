jQuery(document).ready(function () {
    
    a="";
    if (jQuery("#barnet_concept_type_updated").length) {
        if(jQuery("#barnet_concept_type_updated").val()=="unupdated"){
            a='<a href="'+window.location.href+'&unupdated=unupdated">Please set concept type of all the concept as term.</a>';
        }

    }
    h='<div class="rwmb-field rwmb-select-wrapper"><div class="rwmb-label" id="concept_type-label"><label for="concept_type">Concept Type</label></div>'+'<div class="rwmb-input">'+a+'<select id="concept_type_term" class="rwmb-select" name="concept_type_term">'+jQuery("#Concept-Type-temp #concept_type_term").html()+'</select></div></div>';
    jQuery("#barnet-concept .rwmb-meta-box").prepend(h);
    jQuery("#Concept-Type-temp").remove();
    
})