<?php

abstract class BarnetDataType implements BarnetDataInterface
{
    const FITVH = "--fit-vh";
    const BARNET_PRODUCT = "barnet-product";
    const OBJECT_TYPE = "object_type";
    const POST_TYPE = "post_type";
    const FIELD = "field";
    const TITLE = "title";
    const RECIPROCAL = "reciprocal";
    const NUMBERPOSTS = "numberposts";
    const POSTTITLE = "post_title";
    const ORDERBY = "orderby";
    const ORDER = "order";
    const MAX_CLONE = "max_clone";

    public static $IS_REMOVE_ROLE = true;

    public static $PAGE = array(
        'register' => self::FITVH,
        'lostpassword' => self::FITVH,
        'resetpass' => self::FITVH,
        'contact-us' => self::FITVH
    );

    protected static $MIME_FILE = array(
        'application/pdf',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/svg+xml'
    );

    protected static $MIME_IMAGE = array(
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/svg+xml'
    );

    protected $mime;
    protected $mimeImage;
    protected $prefix;
    protected $domain;
    protected $postType;
    protected $menuSlug;
    protected $menuSlugTaxonomy;
    protected $useRoleManagement = false;
    protected $extraFields = array();

    public function __construct(
        $prefix = 'barnet-product-',
        $postType = self::BARNET_PRODUCT,
        $menuSlug = '',
        $domain = self::BARNET_PRODUCT
    ) {
        $this->mime = implode(',', self::$MIME_FILE);
        $this->mimeImage = implode(',', self::$MIME_IMAGE);
        $this->prefix = $prefix;
        $this->postType = $postType;
        $this->menuSlug = $menuSlug;
        $this->menuSlugTaxonomy = empty($this->menuSlug) ? $postType : $this->menuSlug;
        $this->domain = $domain;
        $this->extraFields = $this->getExtraFields();
    }

    public function init()
    {
        add_action('init', array($this, 'createPostType'));
        add_action('init', array($this, 'createTaxonomy'), 0);
		
        $this->addRelationship();
    }

    protected function addDataRelationship(
        $from,
        $to,
        $id = null,
        $reciprocal = false,
        $labelFrom = null,
        $labelTo = null,
        $oneToManyRelationship = false,
        $manyToOneRelationship = false,
        $oneToOneRelationship = false,
        $notInFilter = array()
    ) {
        $obj1 = count($splFrom = explode('-', $from)) > 1 ? $splFrom[1] : null;
        $obj2 = count($splTo = explode('-', $to)) > 1 ? $splTo[1] : null;
        $id = isset($id) ? $id : (isset($obj1) && isset($obj2) ? "{$obj1}s_to_{$obj2}s" : null);
        if (!isset($labelFrom)) {
            $labelFrom = isset($obj2) ?
                ucfirst($obj2[-1] == 'y' ?
                    substr($obj2, 0, strlen($obj2) - 1) . "ies" :
                    "{$obj2}s") :
                null;
        }

        if (!isset($labelTo)) {
            $labelTo = isset($obj1) ?
                ucfirst($obj1[-1] == 'y' ?
                    substr($obj1, 0, strlen($obj1) - 1) . "ies" :
                    "{$obj1}s") :
                null;
        }

        if (isset($id) && (!isset($labelFrom) || !isset($labelTo))) {
            if (count($splId = explode('_', $id)) > 2) {
                $labelFrom = ucfirst($splId[2]);
                $labelTo = ucfirst($splId[0]);
            }
        }

        if (isset($id, $from, $to, $labelFrom, $labelTo)) {
            add_action('mb_relationships_init', function () use (
                $id,
                $from,
                $to,
                $labelFrom,
                $labelTo,
                $reciprocal,
                $oneToManyRelationship,
                $manyToOneRelationship,
                $oneToOneRelationship,
                $notInFilter
            ) {
                if ($oneToManyRelationship) {
                    MB_Relationships_API::register(array(
                        'id' => $id,
                        'from' => array(
                            self::OBJECT_TYPE => 'post',
                            self::POST_TYPE => $from,
                            self::FIELD => array(
                                'name' => $labelTo,
                                self::TITLE => $labelTo
                            ),
                            BarnetProduct::QUERYARGS => array(
                                'post__not_in' => $notInFilter
                            )
                        ),
                        'to' => array(
                            self::OBJECT_TYPE => 'post',
                            self::POST_TYPE => $to,
                            self::FIELD => array(
                                'name' => $labelFrom,
                                self::TITLE => $labelFrom,
                                self::MAX_CLONE => 1,
                            )
                        ),
                        self::RECIPROCAL => $reciprocal
                    ));
                } elseif ($manyToOneRelationship) {
                    MB_Relationships_API::register(array(
                        'id' => $id,
                        'from' => array(
                            self::OBJECT_TYPE => 'post',
                            self::POST_TYPE => $from,
                            self::FIELD => array(
                                'name' => $labelTo,
                                self::TITLE => $labelTo,
                                self::MAX_CLONE => 1,
                            )
                        ),
                        'to' => array(
                            self::OBJECT_TYPE => 'post',
                            self::POST_TYPE => $to,
                            self::FIELD => array(
                                'name' => $labelFrom,
                                self::TITLE => $labelFrom,
                            ),
                            BarnetProduct::QUERYARGS => array(
                                'post__not_in' => $notInFilter
                            )
                        ),
                        self::RECIPROCAL => $reciprocal
                    ));
                } elseif ($oneToOneRelationship) {
                    MB_Relationships_API::register(array(
                        'id' => $id,
                        'from' => array(
                            self::OBJECT_TYPE => 'post',
                            self::POST_TYPE => $from,
                            self::FIELD => array(
                                'name' => $labelTo,
                                self::TITLE => $labelTo,
                                self::MAX_CLONE => 1,
                            )
                        ),
                        'to' => array(
                            self::OBJECT_TYPE => 'post',
                            self::POST_TYPE => $to,
                            self::FIELD => array(
                                'name' => $labelFrom,
                                self::TITLE => $labelFrom,
                                self::MAX_CLONE => 1,
                            )
                        ),
                        self::RECIPROCAL => $reciprocal
                    ));
                } else {
                    MB_Relationships_API::register(array(
                        'id' => $id,
                        'from' => $from,
                        'to' => $to,
                        'label_from' => __($labelFrom, $this->domain),
                        'label_to' => __($labelTo, $this->domain),
                        self::RECIPROCAL => $reciprocal
                    ));
                }
            });
        }
    }

    protected function addTermRelationship($from, $to, $id = null, $reciprocal = false)
    {
        $obj1 = count($splFrom = explode('-', $from)) > 1 ? $splFrom[1] : null;
        $obj2 = count($splTo = explode('-', $to)) > 1 ? $splTo[1] : null;
        $id = isset($id) ? $id : (isset($obj1) && isset($obj2) ? "{$obj1}s_to_{$obj2}s" : null);
        $labelFrom = isset($obj2) ?
            ucfirst($obj2[-1] == 'y' ?
                substr($obj2, 0, strlen($obj2) - 1) . "ies" :
                "{$obj2}s") :
            null;
        $labelTo = isset($obj1) ?
            ucfirst($obj1[-1] == 'y' ?
                substr($obj1, 0, strlen($obj1) - 1) . "ies" :
                "{$obj1}s") :
            null;

        if (isset($id) && (!isset($labelFrom) || !isset($labelTo))) {
            if (count($splId = explode('_', $id)) > 2) {
                $labelFrom = ucfirst($splId[2]);
                $labelTo = ucfirst($splId[0]);
            }
        }

        if (isset($id, $from, $to, $labelFrom, $labelTo)) {
            add_action('mb_relationships_init', function () use ($id, $from, $to, $labelFrom, $labelTo, $reciprocal) {
                MB_Relationships_API::register([
                    'id' => $id,
                    'from' => array(
                        self::OBJECT_TYPE => 'term',
                        'taxonomy' => $from,
                    ),
                    'to' => $to,
                    'label_from' => __($labelFrom, $this->domain),
                    'label_to' => __($labelTo, $this->domain),
                    self::RECIPROCAL => $reciprocal,
                ]);
            });
        }
    }

    protected function getRoleList()
    {
        $roleList = array();
        foreach ((new WP_Roles())->role_names as $key => $value) {
            $roleList[$key] = esc_html__($value, $this->domain);
        }

        return $roleList;
    }

    protected function getPPTList()
    {
        $dirPath = __DIR__ . '/../../../uploads/ppt';
        if (!is_dir($dirPath)) {
            return array();
        }

        $scanDir = array_diff(scandir($dirPath), array('..', '.'));
        $result = array();
        foreach ($scanDir as $item) {
            $result[$item] = $item;
        }

        return $result;
    }

    protected function getProducts()
    {
        $productList = array();
        $args = [
            self::NUMBERPOSTS => -1,
            self::ORDERBY => self::POSTTITLE,
            self::ORDER => 'ASC',
            self::POST_TYPE => self::BARNET_PRODUCT,
        ];

        $posts = get_posts($args);
        /** @var WP_Post $post */
        foreach ($posts as $post) {
            $productList[$post->ID] = $post->post_title;
        }

        return $productList;
    }

    protected function getConcepts()
    {
        $List = array();
        $args = [
            self::NUMBERPOSTS => -1,
            self::ORDERBY => self::POSTTITLE,
            self::ORDER => 'ASC',
            self::POST_TYPE => 'barnet-concept',
        ];

        $posts = get_posts($args);
        /** @var WP_Post $post */
        foreach ($posts as $post) {
            $List[$post->ID] = $post->post_title;
        }

        return $List;
    }

    protected function getResourceFile()
    {
        $List = array();
        $args = [
            self::NUMBERPOSTS => -1,
            self::ORDERBY => self::POSTTITLE,
            self::ORDER => 'ASC',
            self::POST_TYPE => 'barnet-resource',
        ];

        $posts = get_posts($args);
        /** @var WP_Post $post */
        foreach ($posts as $post) {
            $List[$post->ID] = $post->post_title . ' (pdf)';
        }

        return $List;
    }

    protected function getProductStringData()
    {
        $List = array();
        $args = [
            self::NUMBERPOSTS => -1,
            self::ORDERBY => self::POSTTITLE,
            self::ORDER => 'ASC',
            self::POST_TYPE => self::BARNET_PRODUCT,
        ];

        $posts = get_posts($args);
        $postMetaManager = new BarnetPostMetaManager($posts);
        /** @var WP_Post $post */
        foreach ($posts as $post) {
            $prod = new ProductEntity(
                $post->ID,
                true,
                array(
                    'post' => $post,
                    'meta' => $postMetaManager->getMetaData($post->ID)
                )
            );

            //var_dump($prod);die;
            $List[$post->ID] = $post->post_title . '*|*' . $prod->getInciName();
        }

        return $List;
    }

    protected function getCapabilities()
    {
        $suffix = DataHelper::camel2Display(str_replace('Barnet', '', get_class($this)));

        return array(
            "edit_post" => "Edit $suffix",
            "read_post" => "Read $suffix",
            "delete_post" => "Delete $suffix",
            "edit_others_posts" => "Edit others {$suffix}s",
            "delete_posts" => "Delete {$suffix}s",
            "publish_posts" => "Publish {$suffix}s",
            "read_private_posts" => "Read Private {$suffix}s",
            "read" => "read",
            "delete_private_posts" => "Delete Private {$suffix}s",
            "delete_published_posts" => "Delete Published {$suffix}s",
            "delete_others_posts" => "Delete others {$suffix}s",
            "edit_private_posts" => "Edit Private {$suffix}s",
            "edit_published_posts" => "Edit Published {$suffix}s",
            "create_posts" => "Create $suffix"
        );
    }

    protected function buildArgs($name, $singularName, $taxonomies = array(), $capabilityType = null)
    {
        $args = array(
            'labels' => array(
                'name' => __($name),
                'singular_name' => __($singularName)
            ),
            'public' => true,
            "show_ui" => true,
            "show_in_menu" => empty($this->menuSlug) ? true : 'edit.php?post_type='.$this->menuSlug,
            "show_in_nav_menus" => true,
            "show_in_admin_bar" => true,
            'has_archive' => true,
            'rewrite' => array('slug' => $this->postType),
            'show_in_rest' => true
        );

        if (count($taxonomies) > 0) {
            $args['taxonomies'] = $taxonomies;
        }

        if ($this->isUseRoleManagement()) {
            $args['capability_type'] = isset($capabilityType) ?
                $capabilityType : DataHelper::camel2Display($this->postType);
            $args['capabilities'] = $this->getCapabilities();
            $args['map_meta_cap'] = true;
        }

        return $args;
    }

    protected function getExtraFields()
    {
        $fieldNames = array();
        $yamlHelper = new YamlHelper();
        if (file_exists(__DIR__ . "/../Config/searches.yml")) {
            $config = $yamlHelper->loadFile(__DIR__ . "/../Config/searches.yml");
            if (isset($config['relationship'])) {
                foreach ($config['relationship'] as $postType => $config) {
                    if ($postType != $this->postType) {
                        continue;
                    }

                    foreach ($config as $configName => $configData) {
                        $fieldName = DataHelper::compactString($configName, '_');
                        foreach ($configData as $rlPostType => $rlData) {
                            $fieldName .= '_' . DataHelper::compactString($rlPostType);
                            foreach ($rlData as $rlFieldKey => $rlPoint) {
                                if ($rlFieldKey == 'relation_key') {
                                    continue;
                                }

                                if ($rlFieldKey != 'taxonomy') {
                                    $fieldNames[] = $fieldName . '_' . DataHelper::compactString($rlFieldKey, '_');
                                } else {
                                    $fieldName .= '_tax';
                                    foreach ($rlPoint as $taxName => $taxData) {
                                        $fieldName .= '_' . DataHelper::compactString($taxName);
                                        foreach ($taxData as $taxField => $taxValue) {
                                            $fieldNames[] = $fieldName . '_' . $taxField;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $fieldNames;
    }

    public function removeRoleMetaBox()
    {
        if (static::$IS_REMOVE_ROLE) {
            add_action('add_meta_boxes', function () {
                remove_meta_box('members-cp', $this->postType, 'advanced');
            }, 11);
        }
    }

    public function createTaxonomy()
    {
    }

    public function addCustomFieldTaxonomy()
    {
        return array();
    }

    public function addRelationship()
    {
    }

    public function getTitlePlaceHolder()
    {
        return array(
            $this->postType => DataHelper::camel2Display(str_replace('Barnet', '', get_class($this))) . " Name"
        );
    }

    /**
     * @return bool
     */
    public function isUseRoleManagement()
    {
        return $this->useRoleManagement;
    }

    /**
     * @param bool $useRoleManagement
     * @return $this
     */
    public function setUseRoleManagement(bool $useRoleManagement)
    {
        $this->useRoleManagement = $useRoleManagement;
        return $this;
    }

    public function addAdminColumn($column_title, $cb)
    {

        // Column Header
        add_filter('manage_' . $this->postType . '_posts_columns', function ($columns) use ($column_title) {
            $columns[sanitize_title($column_title)] = $column_title;
            return $columns;
        });

        // Column Content
        add_action(
            'manage_' . $this->postType . '_posts_custom_column',
            function ($column, $post_id) use ($column_title, $cb) {
                if (sanitize_title($column_title) === $column) {
                    $cb($post_id);
                }
            },
            10,
            2
        );
    }

    public static function getProductDigitalCodes()
    {
        global $wpdb;

        $query = "SELECT DISTINCT `to` FROM wp_mb_relationships where type = 'products_to_digitals'";
        $resultListId = array_map(function ($e) {
            return $e['to'];
        }, $wpdb->get_results($query, ARRAY_A));

        return $resultListId;
    }
}