<?php
/* Template Name: Import Map Concept Product */
$rooFolder = getcwd();
include($rooFolder . '/spreadsheet-reader-master/php-excel-reader/excel_reader2.php');
include($rooFolder . '/spreadsheet-reader-master/SpreadsheetReader.php');
?>
<?php get_header(); ?>
    <main role="main">
    <?php if (is_user_logged_in()):?>
        <section class="component-my-account">
            <div class="container">
                <h1 style="color: #fff;" class="component-heading-group__heading --size-lg">Import excel file(.xls)</h1><br/>
                <?php
                        if(isset($_FILES['file']) && isset($_FILES['file']['tmp_name']) && ($_FILES['file']['name'] != '')) {
                            chmod($rooFolder . '/wp-content/uploads/2022/06', 0777);
                            $temp_name = $_FILES['file']['tmp_name'];
                            $path = $rooFolder . '/wp-content/uploads/2022/06/'.$_FILES['file']['name'];
                            move_uploaded_file($temp_name,$path);
                            
                            $data = new Spreadsheet_Excel_Reader($path);
                            global $wpdb;
                            $count = 0;
                             
                            for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
                                $cell = $data->sheets[0]['cells'][$i];
                                $titleConcept = htmlspecialchars(trim($cell[4]), ENT_NOQUOTES, 'UTF-8');
                                if ($titleConcept == '') {
                                    $titleConcept = utf8_encode(trim($cell[4]));
                                }

                                $titleProduct = htmlspecialchars(trim($cell[9]), ENT_NOQUOTES, 'UTF-8');
                                if ($titleProduct == '') {
                                    $titleProduct = utf8_encode(trim($cell[9]));
                                }

                                $desPC = htmlspecialchars(trim($cell[12]), ENT_NOQUOTES, 'UTF-8');
                                if ($desPC == '') {
                                    $desPC = utf8_encode(trim($cell[12]));
                                }

                                $subConcept = htmlspecialchars(trim($cell[7]), ENT_NOQUOTES, 'UTF-8');
                                if ($subConcept == '') {
                                    $subConcept = utf8_encode(trim($cell[7]));
                                }

                                $orderSubConcept = htmlspecialchars(trim($cell[8]), ENT_NOQUOTES, 'UTF-8');
                                if ($orderSubConcept == '') {
                                    $orderSubConcept = utf8_encode(trim($cell[8]));
                                }

                                $productConceptOrder = htmlspecialchars(trim($cell[10]), ENT_NOQUOTES, 'UTF-8');
                                if ($productConceptOrder == '') {
                                    $productConceptOrder = utf8_encode(trim($cell[10]));
                                }

                                $productConceptRightText = htmlspecialchars(trim($cell[11]), ENT_NOQUOTES, 'UTF-8');
                                if ($productConceptRightText == '') {
                                    $productConceptRightText = utf8_encode(trim($cell[11]));
                                }

                                if (isset($cell[9]) && $cell[9] != '' && $cell[4] != '') {
                                    $myPC = get_page_by_title($titleProduct . ' – ' . $titleConcept, OBJECT, 'barnet-pconcept');
                                    if (!isset($myPC->ID) || !is_numeric($myPC->ID) || $myPC->ID == null || $myPC->post_status == 'trash') {
                                        $count++;
                                        $slug1 = preg_replace('/[^A-Za-z0-9\-]/', '', $cell[4]);
                                        $slug2 = preg_replace('/[^A-Za-z0-9\-]/', '', $cell[7]);
                                        $slug = strtolower($slug1.'-'.$slug2);
                                        $termID = 0;
                                        $allTerm = get_terms([
                                            'taxonomy' => 'sub-concept-category',
                                            'hide_empty' => false,
                                        ]);
                                        foreach ($allTerm as $item){
                                            //if ($item->name == $subConcept && $item->slug == $slug) {
                                            if ($item->slug == $slug) {
                                                $termID = $item->term_id;
                                            }
                                        }
                                        
                                        //Add term to category
                                        if ($termID == 0 && $subConcept != '') {
                                            $termId = wp_insert_term(
                                                $subConcept,
                                                'sub-concept-category',
                                                array(
                                                    'slug' => $slug
                                                )
                                            );
                                            if ((int)$orderSubConcept != 0 && is_numeric((int)$orderSubConcept)) {
                                                $numberOrderSC = (int)$orderSubConcept;
                                                add_term_meta( $termId['term_id'], "order" , $numberOrderSC );
                                            }
                                            $termID = $termId['term_id'];
                                        }
                                        // Create post object
                                        $my_post = array(
                                            'post_type' => 'barnet-pconcept',
                                            'post_title'    => wp_strip_all_tags( $titleProduct . ' – ' . $titleConcept ),
                                            'post_content'  => $desPC,
                                            'post_status'   => 'publish',
                                            'post_author'   => 1
                                        );
                                        // Insert the post into the database
                                        $post_id = wp_insert_post( $my_post );
                                        wp_set_post_terms( $post_id, $termID, 'sub-concept-category' );
                                        add_post_meta( $post_id, 'product_concept_description', $desPC, true );
                                        add_post_meta( $post_id, 'product_concept_right_text', $productConceptRightText, true );
                                        
                                        if ((int)$productConceptOrder != 0) {
                                            add_post_meta( $post_id, 'product_concept_order', (int)$productConceptOrder, true );
                                        }

                                        $table = 'wp_mb_relationships';
                                        
                                        $myProduct = get_page_by_title(trim($titleProduct), OBJECT, 'barnet-product');

                                        $data_pc = array('to' => $myProduct->ID,'from' => $post_id, 'type' => 'pconcepts_to_products');
                                        $format = array('%s','%d');
                                        $wpdb->insert($table,$data_pc,$format);
                                        $wpdb->insert_id;

                                        $myConcepts = get_page_by_title($titleConcept, OBJECT, 'barnet-concept');
                                        $data_pc = array('to' => $myConcepts->ID,'from' => $post_id, 'type' => 'pconcepts_to_concepts');
                                        $format = array('%s','%d');
                                        $wpdb->insert($table,$data_pc,$format);
                                        $wpdb->insert_id;
                                    }
                                }  
                            }
                            if ($count > 0) {
                                echo '<h2 style="color: #fff;" class="component-heading-group__heading">Imported successfully</h2><br/>';
                            } else {
                                echo '<h2 style="color: #fff;" class="component-heading-group__heading">No items are inserted!</h2><br/>';
                            }
                        }
                    ?>
                <div class="component-form">
                    <form class="component-form__form" method="post" action="/import-product-concept-mapping" enctype="multipart/form-data">
                        <fieldset>
                            <div class="form-group">
                                <input style="color: #fff;" type="file" name="file" class="form-control" id="customFile" />
                            </div>
                            <input class="btn btn-success" type="submit" name="submit_file" value="Import"/>
                        </fieldset>
                        
                    </form>
                </div>
                <br/><h3><span style="color: #fff;">Should format the order columns to numbers in the excel file</span></h3>
            </div>
        </section>
    <?php endif;?>    
    </main>
<?php get_footer(); ?>