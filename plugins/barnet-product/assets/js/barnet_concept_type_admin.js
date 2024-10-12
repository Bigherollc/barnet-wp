jQuery(document).ready(function () {
    jQuery(".row-actions .view").remove();
    jQuery(".wp-list-table #posts").remove();
    jQuery("#the-list .posts").remove();
    jQuery("tfoot .column-posts").remove();

    
    
    jQuery(".delete .delete-tag").click(function(){
       count=Number(jQuery(this).closest(".level-0").find('[data-colname="Count"]>a').text());
       if(count!=0){
            alert("This concept type can not be deleted because there exist concepts connected to this concept type.");
            return false;
       }
    });
})