<?php

class BarnetQuickEdit
{

    const NUMBER = "number";
    const CHECKBOX = "checkbox";

    protected $name;
    protected $displayName;
    protected $type;
    protected $postType;
    protected $prefix;
    protected $suffixPathJs = "/assets/js/populate";

    public function __construct($name, $displayName, $type, $postType, $prefix = "")
    {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->type = $type;
        $this->postType = $postType;
        $this->prefix = $prefix;
    }

    public function addToQuickEdit($isExist = true)
    {
        if (!$isExist) {
            $this->init();
        }

        $this->addCustomField()->saveCustomField()->popular();
    }

    public function init()
    {
        add_filter("manage_{$this->postType}_posts_columns", function ($columnArray) {
            $columnArray[$this->name] = $this->displayName;

            return $columnArray;
        });

        add_action($this->postType == 'post' ? "manage_posts_custom_column" : "manage_{$this->postType}_posts_custom_column", function ($columnName, $id) {
            switch ($columnName) {
                case $this->name:
                    if (in_array($this->type, array('text', self::NUMBER))) {
                        echo '$' . get_post_meta($id, "{$this->prefix}_{$this->name}", true);
                    } elseif (in_array($this->type, array(self::CHECKBOX))) {
                        if (get_post_meta($id, "{$this->prefix}_{$this->name}", true) == 'on') {
                            echo 'Yes';
                        }
                    }

                    break;
                default:
                    break;
            }
        }, 10, 2);
    }

    public function addCustomField()
    {
        add_action('quick_edit_custom_box', function ($columnName, $postType) {
            $formInput = "";
            if (in_array($this->type, array('text', self::NUMBER))) {
                $formInput = '<label class="alignleft">
                                        <span class="title">' . $this->displayName . '</span>
                                        <span class="input-text-wrap">
                                            <input type="' . $this->type . '" name="' . $this->name . '" value="">
                                        </span>
                                    </label>';
            } elseif (in_array($this->type, array(self::CHECKBOX))) {
                $formInput = '<label class="alignleft">
					<input type="checkbox" name="' . $this->name . '">
					<span class="checkbox-title">' . $this->displayName . '</span>
				</label>';
            }

            switch ($columnName) {
                case $this->name:
                    {
                        wp_nonce_field("{$this->postType}_q_edit_nonce", "{$this->postType}_nonce");
                        echo '<fieldset class="inline-edit-col-right">
                            <div class="inline-edit-col">
                                <div class="inline-edit-group wp-clearfix">
                                    ' . $formInput . '
                                </div>
                            </div>
                        </fieldset>';

                        break;
                    }
                default:
                    break;
            }
        }, 10, 2);
        return $this;
    }

    public function saveCustomField()
    {
        add_action('save_post', function ($post_id) {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }


            if (!isset($_POST["{$this->postType}_nonce"]) || !wp_verify_nonce($_POST["{$this->postType}_nonce"], "{$this->postType}_q_edit_nonce")) {
                return;
            }

            if (isset($_POST['post_type']) && $_POST['post_type'] != $this->postType) {
                if (in_array($_POST['post_type'], array('barnet-concept-book', 'barnet-pattribute', 'barnet-pconcept'))) {
                    if (isset($_POST[$this->name])) {
                        $prefix = "concept_book";
                        if ($_POST['post_type'] == "barnet-pattribute") {
                            $prefix = "product-attribute";
                        } else if ($_POST['post_type'] == "barnet-pconcept") {
                            $prefix = "product_concept";
                        }
                        if (in_array($this->type, array('text', self::NUMBER))) {
                            if (trim($_POST[$this->name]) == "") {
                                delete_post_meta($post_id, "{$prefix}_{$this->name}");
                            } else {
                                update_post_meta($post_id, "{$prefix}_{$this->name}", $_POST[$this->name]);
                            }
                        }
                    }
                    return;
                }
            }

            if (isset($_POST[$this->name])) {
                if (in_array($this->type, array('text', self::NUMBER))) {
                    if (trim($_POST[$this->name]) == "") {
                        delete_post_meta($post_id, "{$this->prefix}_{$this->name}");
                    } else {
                        update_post_meta($post_id, "{$this->prefix}_{$this->name}", $_POST[$this->name]);
                    }

                } elseif (in_array($this->type, array(self::CHECKBOX))) {
                    update_post_meta($post_id, "{$this->prefix}_{$this->name}", 1);
                }
            } else {
                if (in_array($this->type, array(self::CHECKBOX))) {
                    update_post_meta($post_id, "{$this->prefix}_{$this->name}", '');
                }
            }
        });

        return $this;
    }

    public function popular()
    {
        add_action('admin_enqueue_scripts', function ($pageHook) {
            // do nothing if we are not on the target pages
            if ('edit.php' != $pageHook) {
                return;
            }

            $handle = "popular{$this->postType}{$this->name}";
            $jsName = md5($handle);
            $this->generateJs($jsName);
            wp_enqueue_script($handle, get_stylesheet_directory_uri() . "$this->suffixPathJs/$jsName.js", array('jquery'));
        });

        return $this;
    }

    protected function generateJs($jsName)
    {
        $folder = get_template_directory() . $this->suffixPathJs;
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $filePath = get_template_directory() . "$this->suffixPathJs/$jsName.js";
        if (file_exists($filePath)) {
            return $this;
        }

        $populateDataScript = "";
        if (in_array($this->type, array('text', self::NUMBER))) {
            $populateDataScript = "val(d)";
        } elseif (in_array($this->type, array(self::CHECKBOX))) {
            $populateDataScript = "prop(\"checked\",d)";
        }

        file_put_contents($filePath, "jQuery(function(t){var e=inlineEditPost.edit;inlineEditPost.edit=function(i){e.apply(this,arguments);var n=0;if(\"object\"==typeof i&&(n=parseInt(this.getId(i))),n>0){var o=t(\"#edit-\"+n),r=t(\"#post-\"+n),d=t(\".column-order\",r).text();\"Yes\"==t(\".column-featured\",r).text()&&(featured_product=!0),t(':input[name=\"{$this->name}\"]',o).$populateDataScript}}});");

        return $this;
    }
}
