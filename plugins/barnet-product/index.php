<?php

/**
 * Plugin Name: Barnet Manager V2
 * Description:  Manage Barnet Product/Concept/Formula A php 8.0 or higher plugin
 * Version:     1.7.8
 * Author:      Sutrix Solutions Team @ jayrobin
 */

 define( 'BARNET_PRODUCT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
 define( 'BARNET_PRODUCT_PLUGIN_URI', plugin_dir_url( __FILE__ ) );

require_once dirname(__FILE__) . '/Helper/DataHelper.php';
require_once dirname(__FILE__) . '/Helper/YamlHelper.php';

if (!isset($adminScriptManager) || !isset($wpScriptManager)) {
    require_once dirname(__FILE__) . '/Common/ScriptManager.php';
}

register_activation_hook( __FILE__,  'barnet_product_activation' );

require_once dirname(__FILE__) . '/Common/DefaultText.php';
require_once dirname(__FILE__) . '/Common/FileManager.php';
require_once dirname(__FILE__) . '/Common/RoutesManager.php';
require_once dirname(__FILE__) . '/Common/PageManager.php';
require_once dirname(__FILE__) . '/Common/SearchManager.php';
require_once dirname(__FILE__) . '/Common/PostMetaManager.php';
require_once dirname(__FILE__) . '/Common/RelationshipManager.php';
require_once dirname(__FILE__) . '/Common/SessionCacheManager.php';
require_once dirname(__FILE__) . '/Utils/BarnetDB.php';
require_once dirname(__FILE__) . '/Utils/BarnetSimpleSecure.php';
require_once dirname(__FILE__) . '/Utils/BarnetVideoStream.php';
require_once dirname(__FILE__) . '/Utils/QuickEdit.php';

require_once dirname(__FILE__) . '/include/barnet-data-interface.php';
require_once dirname(__FILE__) . '/include/barnet-data-type.php';
require_once dirname(__FILE__) . '/Entity/Entity.php';

$entityDir = scandir(dirname(__FILE__) . "/Entity");
foreach ($entityDir as $entityFile) {
    if ($entityFile != '.' && $entityFile != '..') {
        require_once dirname(__FILE__) . "/Entity/$entityFile";
    }
}

$barnetDir = scandir(dirname(__FILE__));
$barnetVars = array();
foreach ($barnetDir as $barnetFile) {
    if (strpos($barnetFile, 'barnet') === 0) {
        require_once dirname(__FILE__) . "/$barnetFile";
    }
}

require_once dirname(__FILE__) . '/rest-api.php';
require_once dirname(__FILE__) . '/routes.php';
require_once dirname(__FILE__) . '/barnet-contact.php';
require_once dirname(__FILE__) . '/barnet-recaptcha.php';

$barnetRecaptcha = new BarnetRecaptcha();
$barnetRecaptcha->init();

add_action('admin_print_footer_scripts', 'barnet_product_edit_script');
function barnet_product_edit_script(){
    global $current_screen;

    if ('page' == $current_screen->post_type && 'edit' == $current_screen->parent_base){
        wp_enqueue_script( 'barnet_produt_page_admin', plugin_dir_url(__FILE__) . 'assets/js/barmet_page_admin.js' );
    }  
   
    if ('barnet-product' == $current_screen->post_type && 'post' == $current_screen->base){
        wp_enqueue_script( 'barnet_product_admin', plugin_dir_url(__FILE__) . 'assets/js/barnet_product_admin.js' );
    }

    if ('barnet-concept' == $current_screen->post_type && 'post' == $current_screen->base){
        wp_enqueue_script( 'barnet_concept_admin', plugin_dir_url(__FILE__) . 'assets/js/barnet_concept_admin.js' );
    }

    if ('product-type' == $current_screen->taxonomy && 'barnet-product' == $current_screen->post_type && 'edit-tags' == $current_screen->base){
        wp_enqueue_script( 'barnet_porduct_type_admin', plugin_dir_url(__FILE__) . 'assets/js/barnet_porduct_type_admin.js' );
    }

    if ('concept-type' == $current_screen->taxonomy && 'barnet-concept' == $current_screen->post_type && 'edit-tags' == $current_screen->base){
        wp_enqueue_script( 'barnet_concept_type_admin', plugin_dir_url(__FILE__) . 'assets/js/barnet_concept_type_admin.js' );
    }
    
    
}



function barnet_product_activation(){
    global $wpdb;

    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty( $wpdb->charset ) ) {
            $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $collate .= " COLLATE $wpdb->collate";
        }
    }

    $caching_setting_table     = $wpdb->prefix . 'caching_setting_table';
    $sql_gcaching_setting_table = "
        CREATE TABLE if NOT EXISTS `$caching_setting_table` (
        `ID` INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
        `api_name` VARCHAR(255) NULL,
        `data` LONGTEXT NULL
        )  $collate AUTO_INCREMENT=1 ENGINE=InnoDB";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql_gcaching_setting_table );
}


if(isset($_GET['caching_barnet_api'])){
    //add_action("init", "barnet_api_cache_cron_hook_update"); 
    barnet_api_cache_cron_hook_update();    
}
add_action('barnet_api_cache_cron_hook', 'barnet_api_cache_cron_hook_function');
function barnet_api_cache_cron_hook_update() {
    global $wpdb;
    wp_clear_scheduled_hook("barnet_api_cache_cron_hook"); 
    
    $update_interval_api_cache=get_option("update_interval_api_cache","hourly");
    if ( ! wp_next_scheduled( 'barnet_api_cache_cron_hook' ) ) {
       
        wp_schedule_event( time(), $update_interval_api_cache, 'barnet_api_cache_cron_hook' );
    }
    

} 

function barnet_api_cache_cron_hook_function() {
    global $wpdb, $barnetRestAPI;
    $request=new WP_REST_Request();
    $response_data=$barnetRestAPI->getConceptLanding_cache($request);
    $api_name='landing_concept_internal_cache';
    $data = maybe_serialize($response_data);
    $caching_setting_table = $wpdb->prefix . 'caching_setting_table';

    $existing_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $caching_setting_table WHERE api_name = %s", $api_name));

    if ($existing_row) {
        // Row with api_name 'landing_concept' already exists, update the row
        $wpdb->update(
            $caching_setting_table,
            array('data' => $data),
            array('api_name' => 'landing_concept_internal_cache')
        );
    } else {
        // Row with api_name 'landing_concept' does not exist, insert a new row
        $wpdb->insert(
            $caching_setting_table,
            array('api_name' => 'landing_concept_internal_cache' , 'data' => $data),
            array('%s', '%s')
        );
    }    
    "<br/>".var_export( $wpdb->last_error, true )."<br/>";
}

function barnet_api_cache_cron_hook_manual_function(){
    global $wpdb, $barnetRestAPI;
    $request=new WP_REST_Request();
    $response_data=$barnetRestAPI->getConceptLanding_cache($request);
    $api_name='landing_concept_internal_cache';
    $data = maybe_serialize($response_data);
    $caching_setting_table = $wpdb->prefix . 'caching_setting_table';

    $existing_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $caching_setting_table WHERE api_name = %s", $api_name));
    $updated_flag = false;
    if ($existing_row) {
        // Row with api_name 'landing_concept' already exists, update the row
        $updated_flag = $wpdb->update(
            $caching_setting_table,
            array('data' => $data),
            array('api_name' => 'landing_concept_internal_cache')
        );
    } else {
        // Row with api_name 'landing_concept' does not exist, insert a new row
        $updated_flag=$wpdb->insert(
            $caching_setting_table,
            array('api_name' => 'landing_concept_internal_cache' , 'data' => $data),
            array('%s', '%s')
        );
    }    
    "<br/>".var_export( $wpdb->last_error, true )."<br/>";    
    if($updated_flag)add_action( 'admin_notices', 'wpb_admin_rest_api_cashe_notice_updated' );
    else add_action( 'admin_notices', 'wpb_admin_rest_api_cashe_notice_failed' );
}

if(isset($_GET['caching_barnet_api_manual'])){
    //add_action("init", "barnet_api_cache_cron_hook_update"); 
    add_action('init', 'barnet_api_cache_cron_hook_manual_function'); 
}


// Schedule an event to run every hour

function wpb_admin_rest_api_cashe_notice_updated() {
    echo '<div class="notice updated is-dismissible">
          <p>Api cache data was updated</p>
          </div>'; 
 }

 function wpb_admin_rest_api_cashe_notice_failed() {
    echo '<div class="notice notice-warning is-dismissible">
          <p>Api cache data was not updated</p>
          </div>'; 
 }






add_action('admin_head-edit.php','addImportSampleButton');

function addImportSampleButton()
{
    
    global $current_screen;

    // Not our post type, exit earlier
    // You can remove this if condition if you don't have any specific post type to restrict to. 
    if ('barnet-pconcept' != $current_screen->post_type) {
        return;
    }
    $m_query_arg="";
    $m_query_arg= site_url().'/wp-admin/edit.php?post_type=barnet-pconcept';
    ?> <div id="nonce_for_excel_upload">
        <?php ?>
        <?php wp_nonce_field(); ?>
        </div>
        <style>
            div#import_error_msg {
                margin-bottom: 50px;
            }
            #loading_loadmore{
                display: none;
                position: fixed;
                top: 50vh;
                left:50vw;
                z-index: 10000;
            }
            body.blurred{
                -webkit-filter: blur(1px);
                -moz-filter: blur(1px);
                -o-filter: blur(1px);
                -ms-filter: blur(1px);
                filter: blur(1px);    
            }
            #export_sample_data input{
                margin-top: -3px;
            }
            #additional_buttons{
                display: inline-flex;
                gap: 5px;
                align-items: flex-start;
            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready( function($)
            {
                var current_url="<?php echo $m_query_arg;?>";
                encoding_part= Math.floor(1000000000*Math.random());
                jQuery(jQuery(".wrap a")[0]).after("<a  id='import_sample_data' onclick='open_excel_upload_panel()' class='page-title-action'>Import sample data</a><a id='export_samples_csv'  href='"+window.location.href+"&export_samples_csv="+encoding_part+"' class='page-title-action' />Export data</a>");
                
                jQuery(jQuery(".wrap #export_samples_csv")[0]).after('<div class="upload-plugin-wrap"><div id="upload-excel-file" class="upload-plugin">'+
                '<p id="loading_loadmore"><img src="<?php echo BARNET_PRODUCT_PLUGIN_URI."assets/images/loadmore1.gif";?>" alt="Loading"/></p>'+
                '<p class="install-help">If you have a sample data in a .CSV, XLS, and XLSX format, you may import it by uploading it here.</p>'+
                '<input type="hidden" value="<?php echo admin_url('admin-ajax.php'); ?>" id="barnet-ajax_url">'+
                '<form method="post" onsubmit="excelfile_upload(event)"  enctype="multipart/form-data" action="'+current_url+'" class="wp-upload-form">'+jQuery("#nonce_for_excel_upload").html()+
                    
                '<label class="screen-reader-text" for="importSampleExcel">CSV file</label><input type="file" id="importSampleExcel" name="file" accept=".csv,.xls,.xlsx">'+
                '<input type="submit"  name="import-sample-submit" id="import-sample-submit" class="button" value="upload Now" disabled="">'+
                '</form></div><div id="import_error_msg"></div></div>');
                jQuery("#nonce_for_excel_upload").html("");
            });
           function open_excel_upload_panel(){
                
                jQuery("#upload-excel-file").show();
           }
           function excelfile_upload(){
            var formData = new FormData();
            formData.append('file', jQuery('#importSampleExcel')[0].files[0]);
            formData.append('_wpnonce', jQuery('#_wpnonce').val());
            formData.append('_wp_http_referer', jQuery('[name="_wp_http_referer"]').val());
            formData.append('action', 'import_excel_file_ajax');
            ajaxUrl=jQuery('#barnet-ajax_url').val();
            jQuery("#loading_loadmore").show();
            bodybg=jQuery("body").css("background-color");
            //jQuery("body").addClass("blurred");
            jQuery.ajax({
                    url : ajaxUrl,
                    type : 'POST',
                    data : formData,
                    processData: false,  // tell jQuery not to process the data
                    contentType: false,  // tell jQuery not to set contentType
                    success : function(data) {
                        jQuery("#loading_loadmore").hide();
                        //jQuery("body").removeClass("blurred");
                        jQuery("#import_error_msg").html(data+'<a class="button" href="<?php echo site_url('/wp-admin/edit.php?post_type=barnet-pconcept');?>">Mapping List</a>');
                        importedFlag=jQuery("#imported_flag").attr("data-flag");
                        if(importedFlag=="true"){
                            jQuery("#upload-excel-file").hide();
                        }
                    }
                });
            event.preventDefault();
           
                
           }
        </script>
    <?php
}



require_once BARNET_PRODUCT_PLUGIN_DIR.'include/vendor/autoload.php';



use PhpOffice\PhpSpreadsheet\IOFactory;


add_action('wp_ajax_import_excel_file_ajax',   'import_excel_file_ajax' );
function import_excel_file_ajax(){
    if(isset($_FILES['file']) && isset($_FILES['file']['tmp_name']) && ($_FILES['file']['name'] != '')) {
        $upload_dir = wp_upload_dir();
        $errorMsg="";
        $uploads_path = $upload_dir['path'];
        $uploads_url = $upload_dir['url'];
        chmod($uploads_path, 0755);
        $temp_name = $_FILES['file']['tmp_name'];      
        $path = $uploads_path."/".$_FILES['file']['name'];
        $urlpath = $uploads_url."/".$_FILES['file']['name'];
        move_uploaded_file($temp_name,$path);
        // Load the spreadsheet file
         // Change the file extension to .xls or .xlsx for Excel files
        try {
            $spreadsheet = IOFactory::load($path);
        } catch (Exception $exception) {
            
            $errorMsg.=$exception->getMessage()."<br>";
        }
        // Get all sheet names
        $sheetNames = $spreadsheet->getSheetNames();
        global $wpdb;
        $count = 0;
        // Loop through each sheet
        $i=0;
        foreach ($sheetNames as $sheetName) {
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            if($i>0)break;
            $i++;
            // Get all rows from the worksheet
            $rows = $worksheet->toArray();
            // Loop through each row and then loop through each cell in the row to get the cell value
            
            foreach ($rows as $rKey => $row) {
                if($rKey==0)continue;

               //$titleConcept = htmlspecialchars(trim($row[2]), ENT_NOQUOTES, 'UTF-8');
               $conceptSection='';
               $conceptSectionSortOrder='';
               $titleConcept='';
               $conceptSortOrder='';
               $productType='';
               $subConcept='';
               $subConceptName='';
               $orderSubConcept='';
               $titleProduct='';
               $productConceptOrder='';
               $productConceptRightText='';
               $desPC='';
               


               if($row[0]){
                    //$conceptSection = convertToUTF8($row[0]);
                    
                    $conceptSection = htmlspecialchars(trim($row[0]), ENT_NOQUOTES, 'UTF-8');
                    if ($conceptSection == '') {
                            $conceptSection = utf8_encode(trim($row[0]));
                    }
                    
                }

                if($row[1]){
                    // $conceptSectionSortOrder = convertToUTF8($row[1]);
                    $conceptSectionSortOrder = htmlspecialchars(trim($row[1]), ENT_NOQUOTES, 'UTF-8');
                    if ($conceptSectionSortOrder == '') {
                            $conceptSectionSortOrder = utf8_encode(trim($row[1]));
                    }
                }

                if($row[2]){
                    //$titleConcept = convertToUTF8($row[2]);
                    $titleConcept = htmlspecialchars(trim($row[2]), ENT_NOQUOTES, 'UTF-8');
                    if ($titleConcept == '') {
                    $titleConcept = utf8_encode(trim($row[2]));
                    }
                }
                if($row[3]){
                    //$conceptSortOrder = convertToUTF8($row[3]);
                    $conceptSortOrder = htmlspecialchars(trim($row[3]), ENT_NOQUOTES, 'UTF-8');
                    if ($conceptSortOrder == '') {
                        $conceptSortOrder = utf8_encode(trim($row[3]));
                    }
                 }
                 if($row[4]){
                    //$productType = convertToUTF8($row[4]);
                    $productType = htmlspecialchars(trim($row[4]), ENT_NOQUOTES, 'UTF-8');
                    if ($productType == '') {
                        $productType = utf8_encode(trim($row[4]));
                    }
                }
                if($row[5]){
                    //$subConcept = convertToUTF8($row[5]);
                    $subConcept = htmlspecialchars(trim($row[5]), ENT_NOQUOTES, 'UTF-8');
                    if ($subConcept == '') {
                        $subConcept = utf8_encode(trim($row[5]));
                    }
                }
                if($row[6]){
                    //$subConcept = convertToUTF8($row[5]);
                    $subConceptName = htmlspecialchars(trim($row[6]), ENT_NOQUOTES, 'UTF-8');
                    if ($subConceptName == '') {
                        $subConceptName = utf8_encode(trim($row[6]));
                    }
                }
                
                if($row[7]){
                    //$orderSubConcept = convertToUTF8($row[6]);
                    $orderSubConcept = htmlspecialchars(trim($row[7]), ENT_NOQUOTES, 'UTF-8');
                    if ($orderSubConcept == '') {
                        $orderSubConcept = utf8_encode(trim($row[7]));
                    }
                }
                if($row[8]){
                    //$titleProduct = convertToUTF8($row[7]);
                     $titleProduct = htmlspecialchars(trim($row[8]), ENT_NOQUOTES, 'UTF-8');
                    if ($titleProduct == '') {
                        $titleProduct = utf8_encode(trim($row[8]));
                    }
                }
                if($row[9]){
                    //$productConceptOrder = convertToUTF8($row[8]);
                    $productConceptOrder = htmlspecialchars(trim($row[9]), ENT_NOQUOTES, 'UTF-8');
                    if ($productConceptOrder == '') {
                        $productConceptOrder = utf8_encode(trim($row[9]));
                    }
                 }
               if($row[10]){
                    //$productConceptRightText = convertToUTF8($row[9]);
                    $productConceptRightText = htmlspecialchars(trim($row[10]), ENT_NOQUOTES, 'UTF-8');
                    if ($productConceptRightText == '') {
                        $productConceptRightText = utf8_encode(trim($row[10]));
                    }
                }
              if($row[11]){
                    //$desPC = convertToUTF8($row[10]);
                    $desPC = htmlspecialchars(trim($row[11]), ENT_NOQUOTES, 'UTF-8');
                    if ($desPC == '') {
                        $desPC = utf8_encode(trim($row[11]));
                    }
              }

              if(trim($titleProduct)==""||trim($titleProduct)==null)break;
                $titleProductId=barnet_get_product_id_by_title($titleProduct);
                $titleConceptId=barnet_get_concept_id_by_title($titleConcept);
               // if($titleProductId==-100)$errorMsg.=" Product( ".$titleProduct." ) was not found in database. Please reimport after register this product.<br>";
                
                if($titleConceptId==-100){
                    $titleConcept = ucfirst($titleConcept);
                    $new_concept = array(
                        'post_type' => 'barnet-concept',
                        'post_title'    => wp_strip_all_tags( $titleConcept),
                       // 'post_content'  => $productDesc,
                        'post_status'   => 'publish',
                        'post_author'   => 1
                    );
                    // Insert the post into the database                   
                    try {
                        $titleConceptId = wp_insert_post( $new_concept );
                    } catch (Exception $exception) {                       
                        $errorMsg.=$exception->getMessage()."<br>";
                        continue;
                    }
                    if ((int)$conceptSortOrder != 0 && is_numeric((int)$conceptSortOrder)) {
                        update_post_meta($titleConceptId, "concept_order", (int)$conceptSortOrder);
                        
                    }
                    if($conceptSection !=""){
                        
                        $term = term_exists( $conceptSection, 'concept-category' );
                        if ( $term !== 0 || $term !== null ) {
                            $conceptSectionArr = explode("-", $conceptSection);
                            $conceptSectionArrUcF=[];
                            foreach($conceptSectionArr as $conceptsectionItem){
                                $conceptSectionArrUcF[] = ucfirst($conceptsectionItem);
                            }
                            $conceptSectionName = implode(" ", $conceptSectionArrUcF);
                            wp_insert_term($conceptSectionName, 'concept-category',	array( 'slug'=> $conceptSection, ));
                        }

						$conceptSection_term = get_term_by('slug', $conceptSection, 'concept-category');
                        if($conceptSection_term)wp_set_post_terms( $titleConceptId, $conceptSection_term->term_id, 'concept-category' );
                        if ((int)$conceptSectionSortOrder != 0 && is_numeric((int)$conceptSectionSortOrder)) {
                            $numberOrderSC = (int)$orderSubConcept;
                            if($conceptSection_term)add_term_meta( $conceptSection_term->term_id, "order" , (int)$conceptSectionSortOrder );
                        }
                    }

                }
                
                if($titleProductId==-100){
                    $titleProduct = ucfirst($titleProduct);
                    $new_product = array(
                        'post_type' => 'barnet-product',
                        'post_title'    => wp_strip_all_tags( $titleProduct),
                       // 'post_content'  => $productDesc,
                        'post_status'   => 'publish',
                        'post_author'   => 1
                    );
                    // Insert the post into the database                   
                    try {
                        $titleProductId = wp_insert_post( $new_product );
                    } catch (Exception $exception) {                       
                        $errorMsg.=$exception->getMessage()."<br>";
                        continue;
                    }
                    if($productType !=""){
                        $term = term_exists( $productType, 'product-type' );
                        if ( $term == 0 || $term == null ) {
                             wp_insert_term($productType, 'product-type');
                         }
						$product_type_term = get_term_by('name', $productType, 'product-type');
						if($product_type_term)update_post_meta( $titleProductId, 'product_type_term', $product_type_term->term_id );
                    }

                }
                
                
                if (isset($row[8]) && $row[8] != '' && $row[2] != '') {
                    $myPC = get_page_by_title(ucfirst($titleProduct) . ' - ' . ucfirst($titleConcept), OBJECT, 'barnet-pconcept');
                    if($titleProductId!=-100 && $titleConceptId!=-100){
                        if (!isset($myPC->ID) || !is_numeric($myPC->ID) || $myPC->ID == null || $myPC->post_status == 'trash') {
                            $count++;
                            
                            //$slug1 = preg_replace('/[^A-Za-z0-9\-]/', '', $row[2]);
                            //$slug2 = preg_replace('/[^A-Za-z0-9\-]/', '', $row[5]);
                           // $slug = strtolower($slug1.'-'.$slug2);
                            
                            $termID = 0;
                            $allTerm = get_terms([
                                'taxonomy' => 'sub-concept-category',
                                'hide_empty' => false,
                            ]);
                            $slug = $subConcept;
                            $subConceptArr=explode("-", $slug);
                            $term = term_exists( $slug, 'sub-concept-category' );

                            /*
                            
                            if($subConceptName==""){
                                foreach ($allTerm as $item){
                                    //if ($item->name == $subConcept && $item->slug == $slug) {
                                    $itemSlug=$item->slug;
                                    $itemSlugArr = explode("-", $itemSlug);
                                    $subSlug=end($itemSlugArr);
                                    $newSubSlug="";
                                    if(isset($subConceptArr)){
                                        $newSubSlug = end($subConceptArr);
                                    }


                                    
                                    if( trim($subSlug) == trim($newSubSlug)){
                                        $subConceptName = $item->name;
                                    }

                                    if ($item->slug == $slug) {
                                        $termID = $item->term_id;
                                    }
                                }
                            }
                            */
                            

                            //Add term to category
                            if ( ($term == 0 || $term == null)  && $slug != '') {

                                if($subConceptName==""){
                                    $subConceptName = ucfirst(end($subConceptArr));
                                }
                                
                               // $termId = wp_insert_term(
                                //    $subConcept,
                                //    'sub-concept-category',
                               //     array(
                               //         'slug' => $slug
                               //     )
                              //  );
                                

                                 wp_insert_term(
                                    $subConceptName,
                                    'sub-concept-category',
                                    array(
                                        'slug' => $slug
                                    )
                                );
                            }

                            if($slug != ''){
                                $subConcept_term = get_term_by('slug', $slug, 'sub-concept-category');

                                if ((int)$orderSubConcept != 0 && is_numeric((int)$orderSubConcept)) {
                                    $numberOrderSC = (int)$orderSubConcept;
                                    add_term_meta( $subConcept_term->term_id, "order" , $numberOrderSC );
                                }
                                $termID = $subConcept_term->term_id;
                            }
                        
                            // Create post object
                            $my_post = array(
                                'post_type' => 'barnet-pconcept',
                                'post_title'    => wp_strip_all_tags( ucfirst($titleProduct) . ' - ' . ucfirst($titleConcept) ),
                                'post_content'  => $desPC,
                                'post_status'   => 'publish',
                                'post_author'   => 1
                            );
                            // Insert the post into the database
                            $post_id=-1;
                            try {
                                $post_id = wp_insert_post( $my_post );
                            } catch (Exception $exception) {
                                
                                $errorMsg.=$exception->getMessage()."<br>";
                                continue;
                            }
                            
                            if($slug != ''){
                                wp_set_post_terms( $post_id, $termID, 'sub-concept-category' ); 
                            }
                            add_post_meta( $post_id, 'product_concept_description', $desPC, true );
                            add_post_meta( $post_id, 'product_concept_right_text', $productConceptRightText, true );
                            
                            if ((int)$productConceptOrder != 0) {
                                add_post_meta( $post_id, 'product_concept_order', (int)$productConceptOrder, true );
                            }
        
                            $table = 'wp_mb_relationships';
                            
                            $myProduct = get_page_by_title(trim($titleProduct), OBJECT, 'barnet-product');
        
                            $data_pc = array('to' => $myProduct->ID,'from' => $post_id, 'type' => 'pconcepts_to_products');
                            $format = array('%s','%d');
                        
                            try {
                                $wpdb->insert($table,$data_pc,$format);
                            } catch (Exception $exception) {
                                
                                $errorMsg.=$exception->getMessage()."<br>";
                                continue;
                            }
                            $wpdb->insert_id;
        
                            $myConcepts = get_page_by_title($titleConcept, OBJECT, 'barnet-concept');
                            $data_pc = array('to' => $myConcepts->ID,'from' => $post_id, 'type' => 'pconcepts_to_concepts');
                            $format = array('%s','%d');
                            try {
                                $wpdb->insert($table,$data_pc,$format);
                            } catch (Exception $exception) {
                                
                                $errorMsg.=$exception->getMessage()."<br>";
                                continue;
                            }
                            $wpdb->insert_id;
                        }
                        else{
                            $errorMsg.=$titleProduct . ' - ' . $titleConcept." is not imported. This mapping already exist!<br>";
                        }
                    }
                }          
                
            }
            
        }


        
    }
    else{
        $errorMsg.=" Upload was failed\n";
    }
    if ($count > 0) {
        echo '<h2 id="imported_flag" data-flag="true" style="color: #008000;" class="component-heading-group__heading">'.$count.' items was imported successfully</h2>
        <h2 style="color: #f00;" class="component-heading-group__heading">'.$errorMsg.'</h2><br/>';
    } else {
        echo '<h2 id="imported_flag" data-flag="false"  style="color: #f00;" class="component-heading-group__heading">No items are inserted!</h2>
        <h2 style="color: #f00;" class="component-heading-group__heading">'.$errorMsg.'</h2><br/>';
    }  
    
    wp_die( );
}

add_action("admin_init", "download_csv");

function convertToUTF8($string) {
    // Detect the current encoding of the string
    $currentEncoding = mb_detect_encoding($string, mb_list_encodings());

    // Check if the current encoding is not UTF-8
    if ($currentEncoding !== 'UTF-8') {
        // Convert the string to UTF-8 encoding
        $string = mb_convert_encoding($string, 'UTF-8', $currentEncoding);
    }

    return $string;
}


function download_csv() {

    if (isset($_GET['export_samples_csv'])) {

        function outputCsv( $fileName) {
            ob_clean();
            header( 'Pragma: public' );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Cache-Control: private', false );
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename=' . $fileName );

            $headerArray=[
                // "C",
                "Concept Section",
                "Concept Section Sort Order",
                "Concept",
                "Concept Sort Order",
                "Active/System",
                "Sub-Concept(slug)", 
                "Sub-Concept(name)", 
                "Sub-Concept Sort Order",
                "Featured Product",
                "Featured Product Sort Order",
                "Featured Product Right Sub-Category",
                "Featured Product Description",
            ];

            $fp = fopen( 'php://output', 'w' );
            fputcsv( $fp, $headerArray );
            $args = array(
                'post_type' => 'barnet-pconcept',
                'post_status' => 'publish',
                'numberposts' => -1,
            );  
            $dataPosts = get_posts($args);
            foreach ($dataPosts as $p) {
                
               // $rowarray =  explode(" - ",$p -> post_title,2);
                $rowarray=["","","","","","","","","","","","",];
                
                //print_r($get_terms);
                global $wpdb;
           
                $postId = $p -> ID;
                $queryC = "SELECT `to` FROM wp_mb_relationships WHERE `from` = {$postId} AND `type` = 'pconcepts_to_concepts'";
                $resultListObjConcept = $wpdb->get_results($queryC, ARRAY_A);
                $listObjId = array_map(function ($e) {
                    return $e['to'];
                }, $resultListObjConcept);
    
                $queryC = "SELECT `to` FROM wp_mb_relationships WHERE `from` = {$postId} AND `type` = 'pconcepts_to_products'";
                $resultListObjProduct = $wpdb->get_results($queryC, ARRAY_A);
                $listObjId = array_map(function ($e) {
                    return $e['to'];
                }, $resultListObjProduct);      
    
                if($resultListObjConcept!=[]&&isset($resultListObjProduct[0])){
                    
                    $postItem_product_id = intval($resultListObjProduct[0]['to']);
                    $rowarray[8]=mb_convert_encoding(get_the_title( $postItem_product_id ), 'UTF-8', 'HTML-ENTITIES');
					$product_type_term = get_post_meta( $postItem_product_id, 'product_type_term', true );
                    $product_type_term_obj=null;
                    if($product_type_term) $product_type_term_obj= get_term( $product_type_term );
                    if($product_type_term_obj) $rowarray[4] =  $product_type_term_obj->name;
                    $postItem_concept_id = intval($resultListObjConcept[0]['to']);
                    $rowarray[2]=get_the_title( $postItem_concept_id );
                    $rowarray[3] = get_post_meta( $postItem_concept_id, 'concept_order', true );
                    
                    $get_termsc = get_the_terms( $postItem_concept_id, 'concept-category' );
                   
                    if($get_termsc){
                        foreach ($get_termsc as $termc) {
                            
                            $rowarray[0]=$termc->slug;
                            $rowarray[1]=get_term_meta( $termc->term_id, 'order', true);
                            
                            break;
                        }
    
                    }   
                //$result[] = $productConcept->toArray(BarnetEntity::$PUBLIC_ALL, false, false);
                }
                else{
                    $post_title = $p -> post_title;
                    $pos_flag=1;
                    $start=0;
                    $find_flag=false;
                    while($pos_flag){
                        
                        $pos_flag=strpos($post_title," - ", $start);
                       
                        $p_title=mb_convert_encoding(trim(substr($post_title,0,$pos_flag)), 'UTF-8', 'HTML-ENTITIES');
                       
                        $start=$pos_flag+3;
                        $c_title=trim(substr($post_title,$start,strlen( $post_title)));
                        $p_id=barnet_get_product_id_by_title($p_title);
                        $c_id=barnet_get_concept_id_by_title($c_title);
                        if( $p_id !=-100 && $c_id!=-100 ){
                            $pos_flag=FALSE;
                            $find_flag=true;
                            $rowarray[8]=$p_title;
							$product_type_term = get_post_meta( $p_id, 'product_type_term', true );
							if($product_type_term)$rowarray[4] =  get_term( $product_type_term )->name;
                            $rowarray[2]=$c_title;
                            $rowarray[3] = get_post_meta( $c_id, 'concept_order', true );

                            $get_termsc = get_the_terms( $c_id, 'concept-category' );
                            if($get_termsc){
                                foreach ($get_termsc as $termc) {
                                    
                                    $rowarray[0]=$termc->slug;
                                    $rowarray[1]=get_term_meta( $termc->term_id, 'order', true);
                                    break;
                                }
            
                            }   

                        }
                    }
                    if(!$find_flag){
                        echo "Could not find concept and product.(ID:".$postId."    title: ". $post_title.")";
                    }

                }
                
                $get_terms = get_the_terms( $postId, 'sub-concept-category' );
                if($get_terms){
                    foreach ($get_terms as $term) {
                        
                        $rowarray[5]=$term->slug;
                        $rowarray[6]=$term->name;
                        $rowarray[7]=get_term_meta( $term->term_id, 'order', true);
                        break;
                    }

                }
                $rowarray[11] = strip_tags( html_entity_decode(get_post_meta( $postId, 'product_concept_description', true )));
                
                $rowarray[10] = get_post_meta( $postId, 'product_concept_right_text', true );
                $rowarray[9] = get_post_meta( $postId, 'product_concept_order', true );
                
                fputcsv( $fp, $rowarray );
            }         

            fclose( $fp );
            
            ob_flush();
        }

        // This is dummy data. 


        $fileName="exportSample". time().".csv";
        outputCsv(  $fileName);

        exit; // This is really important - otherwise it shoves all of your page code into the download

    }

}

function barnet_get_product_id_by_title( string $title = '' ): int {
    $posts = get_posts(
        array(
            'post_type'              => 'barnet-product',
            'title'                  => $title,
            'numberposts'            => 1,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'orderby'                => 'post_date ID',
            'order'                  => 'ASC',
            'fields'                 => 'ids'
        )
    );

    return empty( $posts ) ? -100 : $posts[0];
}

function barnet_get_concept_id_by_title( string $title = '' ): int {
    $posts = get_posts(
        array(
            'post_type'              => 'barnet-concept',
            'title'                  => $title,
            'numberposts'            => 1,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'orderby'                => 'post_date ID',
            'order'                  => 'ASC',
            'fields'                 => 'ids'
        )
    );

    return empty( $posts ) ? -100 : $posts[0];
}
