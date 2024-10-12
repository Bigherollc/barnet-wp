<?php

class BN_Walker_Category_Checklist extends Walker
{
    public $tree_type = 'category';
    public $db_fields = array(
        'parent' => 'parent',
        'id' => 'term_id',
    );

    public function start_lvl(&$output, $depth = 0, $args = array())
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class='children'>\n";
    }

    public function end_lvl(&$output, $depth = 0, $args = array())
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    public function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0)
    {
        if (empty($args['taxonomy'])) {
            $taxonomy = 'category';
        } else {
            $taxonomy = $args['taxonomy'];
        }

        if ('category' === $taxonomy) {
            $name = 'post_category';
        } else {
            $name = 'tax_input[' . $taxonomy . ']';
        }
       
        $args['popular_cats'] = !empty($args['popular_cats']) ? array_map('intval', $args['popular_cats']) : array();

        $class = in_array($category->term_id, $args['popular_cats'], true) ? ' class="popular-category"' : '';

        $args['selected_cats'] = !empty($args['selected_cats']) ? array_map('intval', $args['selected_cats']) : array();
        
        $displayCategory = $category->name;
		$meaning_category=$category->slug;
		$meaning_category_arr=explode("-", $category->slug);
        
		if(isset($meaning_category_arr[0])&& $taxonomy=="sub-concept-category")$meaning_category="-" . $meaning_category_arr[0];
        else $meaning_category="";

        if (is_plugin_active("barnet-products/index.php")) {
            $yamlHelper = new YamlHelper();
            $taxonomyConfig = $yamlHelper->load(__DIR__ . "/Config/taxonomies.yml");
            if (isset($taxonomyConfig[$taxonomy]['display'])) {
                $exportFields = array_map(function ($e) use ($category) {
                    $value = $category->$e;
                    if ($e == "slug") {
                        $keyEx = sanitize_title(str_replace(" ", "", strtolower($category->name)));
                        $exp = explode($keyEx, $value);
                        if (count($exp) > 1) {
                            if (empty($exp[count($exp) - 1])) {
                                unset($exp[count($exp) - 1]);
                            }
                            
                            $value = trim(implode($keyEx, $exp), "-");
                        }
                    }
                    return $value;
                }, $taxonomyConfig[$taxonomy]['display']['fields']);
                $separation = $taxonomyConfig[$taxonomy]['display']['separation'];
                $displayCategory = implode($separation, $exportFields);
            }
        }

        if (!empty($args['list_only'])) {
            $aria_checked = 'false';
            $inner_class = 'category';

            if (in_array($category->term_id, $args['selected_cats'], true)) {
                $inner_class .= ' selected';
                $aria_checked = 'true';
            }

            $output .= "\n" . '<li' . $class . '>' .
                '<div class="' . $inner_class . '" data-term-id=' . $category->term_id .
                ' tabindex="0" role="checkbox" aria-checked="' . $aria_checked . '">' .
                /** This filter is documented in wp-includes/category-template.php */
                esc_html(apply_filters('the_category', $displayCategory, '', '')) . '</div>';
        } else {
            $is_selected = in_array($category->term_id, $args['selected_cats'], true);
            $is_disabled = !empty($args['disabled']);

            $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" .
                '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->term_id . '"' .
                checked($is_selected, true, false) .
                disabled($is_disabled, true, false) . ' /> ' .
                /** This filter is documented in wp-includes/category-template.php */
                esc_html(apply_filters('the_category', $displayCategory, '', '')) . $meaning_category. '</label>';
        }
    }


    public function end_el(&$output, $category, $depth = 0, $args = array())
    {
        $output .= "</li>\n";
    }

}

add_filter('wp_terms_checklist_args', 'my_wp_terms_checklist_args', 10, 2);
function my_wp_terms_checklist_args($args, $post_id)
{
    $args['walker'] = new BN_Walker_Category_Checklist;

    return $args;
}
