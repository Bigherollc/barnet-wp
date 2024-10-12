<?php

class BarnetEntity
{
    const TAXONOMY = 'taxonomy';
    const TAXONOMIES = 'taxonomies';
    const RELATIONSHIP = 'relationship';
    const WIDGET = 'widgets';

    public static $PUBLIC_ALL = array('taxonomy', 'relationship', 'widgets');
    public static $PUBLIC_LANDING = array('taxonomy');
    public static $PUBLIC_SINGLE = array('relationship');
    public static $PUBLIC_DATA = array();

    protected $id;
    protected $postType;
    protected $secure;
    protected $relationshipManager;

    protected $wpPost;
    protected $wpMeta;

    protected $_postDate;
    protected $_postTitle;
    protected $_postExcerpt;
    protected $_postExcerptFull;
    protected $_postName;
    protected $_postModified;
    protected $_permalink;
    protected $_webType;

    protected $taxonomyList = array();
    protected $relationShipList = array();

    public function __construct($id, $isPostType = true, $includeData = array())
    {
        $this->id = $id;
        $this->secure = new BarnetSimpleSecure();

        $this->wpPost = $includeData['post'] ?? null;
        $this->wpMeta = $includeData['meta'] ?? null;

        if ($isPostType) {
            $this->postType = $this->wpPost->post_type ?? $this->wpPost['post_type'] ?? get_post_type($id);
            $this->bind();
            $this->_webType = substr($this->postType, 7, strlen($this->postType) - 7);
        }
    }

    /**
     * @param mixed $relationshipManager
     * @return $this;
     */
    public function setRelationshipManager($relationshipManager)
    {
        $this->relationshipManager = $relationshipManager;
        return $this;
    }

    public function getPostData()
    {
        return $this->wpPost;
    }

    public function getMetaData($key = null)
    {
        if (isset($key)) {
            return isset($this->wpMeta[$key]) ? $this->wpMeta[$key] : null;
        }

        return $this->wpMeta;
    }

    protected function getPost()
    {
        if (!isset($this->wpPost)) {
            global $wpdb;
            $post = BarnetDB::sql("SELECT * FROM $wpdb->posts WHERE ID = $this->id AND post_type = '$this->postType'");

            if (isset($post[0])) {
                $this->wpPost = $post[0];
            }
        }

        if ($this->wpPost instanceof WP_Post) {
            return $this->wpPost->to_array();
        }

        return $this->wpPost;
    }

    protected function getMeta()
    {
        if (!isset($this->wpMeta)) {
            $this->wpMeta = get_post_meta($this->id);
        }

        return array_map(function ($e) {
            return is_array($e) && count($e) == 1 ? $e[0] : $e;
        }, $this->wpMeta);
    }

    public function getTaxonomies()
    {
        if (isset($this->relationshipManager)) {
            return $this->relationshipManager->getTerms($this->id);
        }

        $result = array();
        $resultParent = array();
        foreach ($this->taxonomyList as $taxonomy) {
            if ($terms = get_the_terms($this->id, $taxonomy)) {
                foreach ($terms as $count => $term) {
                    $term->name = html_entity_decode($term->name);
                    $listTermMeta = get_term_meta($term->term_id);
                    $termMeta = array_map(function ($e) {
                        return is_array($e) ? (count($e) == 1 ? $e[0] : $e) : $e;
                    }, $listTermMeta);

                    foreach ($termMeta as $key => $value) {
                        $term->$key = $value;
                    }

                    foreach ($listTermMeta as $key=>$val) {
                        if ($key == 'order') {
                            if (isset($val[0]) && is_numeric($val[0])) {
                                $terms[$count]->order = $val[0];
                            }
                        } 
                    }

                    if ($term->parent > 0) {
                        $parent_term = get_term_by( 'id', $term->parent, $taxonomy );
                        if (!empty($parent_term)) {
                            $term_vals = get_term_meta($parent_term->term_id);
                            foreach ($term_vals as $key=>$val) {
                                if($key == 'show_app' && $val[0] == 1) {
                                    $parent_term->show_app = "1";
                                } elseif ($key == 'order') {
                                    if (isset($val[0]) && is_numeric($val[0])) {
                                        $parent_term->order = $val[0];
                                    }
                                } 
                            }
                            if ($parent_term->count == 0) {
                                array_push($resultParent, $parent_term);
                            } else {
                                $allPost = array();
                                $allPost = get_posts(array(
                                    'post_type' => 'barnet-resource',
                                    'tax_query' => array(
                                        array(
                                        'taxonomy' => 'resource-folder',
                                        'field' => 'term_id',
                                        'terms' => $parent_term->term_id)
                                    ))
                                );
                                $countMedia = 0;
                                foreach ($allPost as $post) {
                                    $postContent = get_post_meta($post->ID);
                                    $mediaID = $postContent['resource_media'][0];
                                    $type = get_post_mime_type($mediaID);
                                    $arrayType = array('application/pdf', 'diagram', 'video/mp4');
                                    if ( in_array($type, $arrayType) ) {
                                        $countMedia++;
                                    }
                                }
                                if ($countMedia == 0) {
                                    $parent_term->count = 0;
                                    array_push($resultParent, $parent_term);
                                }
                            }
                        }
                    }
                }
                $result = array_merge($result, $terms);
            }
        }
        return array_merge($result, $resultParent);
    }

    public function getUser()
    {
        if (is_user_logged_in()) {
            $currentUser = wp_get_current_user();
            return (new UserEntity($currentUser->data->ID))->toArray(BarnetEntity::$PUBLIC_ALL);
        }

        return null;
    }

    public function getRelationship($relationshipList = array(), $fixed = true)
    {
        $relationshipResult = array();
        $relationshipData = count($relationshipList) > 0 ? $relationshipList : $this->relationShipList;
        if (count($relationshipData) == 0) {
            return array();
        }

        if ($this->postType) {
            $objectName = explode('-', $this->postType)[1];
            $relationships = preg_grep("~{$objectName}~", $relationshipData);
            foreach ($relationships as $relationship) {
                $foreignObjectSpl = explode('_', $relationship);
                $foreignObject = strpos($foreignObjectSpl[0], $objectName) === false ? $foreignObjectSpl[0] : $foreignObjectSpl[2];
                $postType = 'barnet-' . ($foreignObject[-1] == 's' ? substr($foreignObject, 0, strlen($foreignObject) - 1) : $foreignObject);
                if ($postType == 'barnet-concept' && $relationship == 'pconcepts_to_concepts' && $fixed) {
                    if ($this->postType == "barnet-pconcept") {
                        $foreignObject = 'concepts';
                        $postType = "barnet-concept";
                    } else {
                        $foreignObject = 'pconcepts';
                        $postType = "barnet-pconcept";
                    }

                }
                if ($postType == 'barnet-digital') {
                    $postType = 'barnet-digital-code';
                } elseif ($postType == "barnet-interactive") {
                    $postType = "concept-interactive";
                } elseif ($relationship == 'concepts_book_to_roles') {
                    $postType = 'barnet-role';
                }

                $relationshipObjects = $this->queryRelationship(
                    $relationship,
                    $postType,
                    $foreignObject == $foreignObjectSpl[2] ? $this->id : null,
                    $foreignObject == $foreignObjectSpl[0] ? $this->id : null
                );

                if ($relationship == 'concepts_book_to_roles' && $foreignObject == 'to') {
                    $foreignObject = 'roles';
                }

                $relationshipFormatObjects = array();
                $relationshipObjectMetaManager = new BarnetPostMetaManager($relationshipObjects);
                foreach ($relationshipObjects as $key => $relationshipObject) {
                    $entity = ucfirst(explode('-', $postType)[1]) . 'Entity';

                    if (class_exists($entity)) {
                        $objectEntity = new $entity(
                            $relationshipObject->ID,
                            true,
                            array(
                                'post' => $relationshipObject,
                                'meta' => $relationshipObjectMetaManager->getMetaData($relationshipObject->ID)
                            )
                        );
                        $objectEntity->setRelationshipManager($this->relationshipManager);
                        $postTypeObject = $objectEntity->postType;
                        
                        if (in_array($postTypeObject, array('barnet-concept', 'barnet-product', 'barnet-formula', 'barnet-resource'))) {
                            $dataObject = $objectEntity->toArrayAllRoleAndRegion(BarnetEntity::$PUBLIC_LANDING);
                        } else {
                            $dataObject = $objectEntity->toArray(BarnetEntity::$PUBLIC_LANDING);
                        }

                        if (isset($dataObject)) {
                            foreach ($dataObject['data'] as $k => $value) {
                                if (!in_array($k, array('id', 'post_title', 'concept_formula_collection', 'digital_code', 'concept_thumbnail'))) {
                                    unset($dataObject['data'][$k]);
                                }
                            }
                            $relationshipFormatObjects[$key] = $dataObject;
                        }
                    } else {
                        $relationshipObjectMeta = $relationshipObjectMetaManager->getMetaData($relationshipObject->ID);
                        unset($relationshipObjectMeta['_edit_lock']);
                        unset($relationshipObjectMeta['_edit_last']);
                        foreach ($relationshipObjectMeta as $keyMeta => $relationshipObjectMetaItem) {
                            $keyMeta = str_replace('-', '_', $keyMeta);
                            $relationshipObjects[$key]->$keyMeta = $relationshipObjectMetaItem;
                        }

                        $relationshipObjects[$key]->id = $relationshipObject->ID;
                        $relationshipFormatObjects[$key] = $relationshipObjects[$key];
                    }
                }

                $relationshipResult[$foreignObject] = array_values(
                    array_unique(
                        array_filter($relationshipFormatObjects),
                        SORT_REGULAR
                    )
                );
            }
        }

        return $relationshipResult;
    }

    public function getWidgets()
    {
        $widgets = $this->getMetaData('panels_data');
        if (!is_array($widgets)) {
            return null;
        }

        if (count($widgets) == 0) {
            return $widgets;
        }

        $widget = $widgets[0];
        if (is_array($widget) && isset($widget['widgets'])) {
            return $widget['widgets'];
        } elseif (@unserialize($widget)) {
            $widget = @unserialize($widget);
            return isset($widget['widgets']) ? $widget['widgets'] : $widget;
        }

        return $widget;
    }

    protected function queryRelationship($id, $postType, $from = null, $to = null)
    {
        $relationship = array('id' => $id);

        if (isset($from)) {
            $relationship['from'] = $from;
        }

        if (isset($to)) {
            $relationship['to'] = $to;
        }

        $args = array(
            'post_type' => $postType,
            'posts_per_page' => -1,
            self::RELATIONSHIP => $relationship,
        );

        if ($postType == "barnet-digital-code") {
            if ($args[self::RELATIONSHIP]['id'] == 'products_to_digital-code') {
                $args[self::RELATIONSHIP]['id'] = 'products_to_digitals';
            }
        }

        $result = new WP_Query($args);
        $response = $result->posts;
        foreach ($response as $key => $post) {
            if (isset($post->post_author)) {
                $post->post_author = (new UserEntity($post->post_author))->toArray(BarnetEntity::$PUBLIC_ALL);
            }

            if ($key == 'ID') {
                $response['id'] = $post;
                unset($response['ID']);
            }

            $response[$key] = $post;
        }

        return array_values($response);
    }

    protected function bind()
    {
        $reflection = new ReflectionClass($this);
      	$dataPost = [];
      
        if($this->getPost() && $this->getMeta())$dataPost = array_merge($this->getPost(), $this->getMeta());
        $propsPrivate = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

        $propsProtected = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($propsProtected as $key => $prop) {
            $propName = $prop->getName();
            if ($propName[0] != '_') {
                continue;
            }

            $prop->setAccessible(true);

            foreach ($dataPost as $keyPost => $valuePost) {
                $camelKeyPost = DataHelper::snake2CamelCase($keyPost);
                if ("_$camelKeyPost" == $propName) {
                    $setFunc = "set" . ucfirst($camelKeyPost);
                    $this->$setFunc($valuePost);
                }
            }

            $prop->setAccessible(false);
        }

        foreach ($propsPrivate as $key => $prop) {
            $prop->setAccessible(true);
            $propName = $prop->getName();
            foreach ($dataPost as $keyPost => $valuePost) {
                if (DataHelper::snake2CamelCase($keyPost) == $propName) {
                    $setFunc = "set" . ucfirst($propName);
                    $this->$setFunc($valuePost);
                }
            }

            $prop->setAccessible(false);
        }

        return $this;
    }

    public function isSerialized($data)
    {
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                    return true;
                }
                break;
            case 'b':
            case 'i':
            case 'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                    return true;
                }
                break;
            default:
                break;
        }
        return false;
    }

    protected function formatMedia($data)
    {
        return is_array($data) ? array_map(function ($e) {
            return wp_get_attachment_url($e);
        }, $data) : wp_get_attachment_url($data);
    }

    public function toArray($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        $result = array('id' => intval($this->id));
        $reflection = new ReflectionClass($this);
     
        if (isset($this->postType)) {
            $propsProtected = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
 
            foreach ($propsProtected as $key => $prop) {
                $propName = $prop->getName();
                if ($propName[0] != '_' || (count($exceptPropsProtected) > 0 && in_array($propName, $exceptPropsProtected)) ) {
                    continue;
                }

                $prop->setAccessible(true);

                $getFunc = "get" . ucfirst(str_replace('_', '', $propName));
                $value = $this->$getFunc();
                if ($this->isSerialized($value)) {
                    $value = unserialize($value);
                }
                if ($propName != '_postName') {
                    $result[DataHelper::camel2SnakeCase($propName)] = is_numeric($value) ? intval($value) : $value;
                } else {
                    $result[DataHelper::camel2SnakeCase($propName)] = $value;
                }

                $prop->setAccessible(false);
            }
            if ($this->postType == 'barnet-product') {
                $product_only_for_code_list = 0;
                $product_only_for_code_list = get_post_meta(intval($this->id), 'product_only_for_code_list', TRUE);
                $result['product_only_for_code_list'] = intval($product_only_for_code_list);
                
            }
        }

       
        $propsPrivate = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
 
        foreach ($propsPrivate as $key => $prop) {
            $prop->setAccessible(true);

            $getFunc = "get" . ucfirst($prop->getName());
            $value = $this->$getFunc();
            if ($this->isSerialized($value)) {
                $value = unserialize($value);
            }
            $result[DataHelper::camel2SnakeCase($prop->getName())] = is_numeric($value) ? intval($value) : $value;

            $prop->setAccessible(false);
        }

        if ($this->postType == 'barnet-product') {
            $product_type_term = get_post_meta(intval($this->id), 'product_type_term', TRUE);
            $result['product_type_term'] = $product_type_term;
			if($product_type_term){
				$product_term=get_term( $product_type_term );
				$result['product_type']=$product_term->name;
			}
			else {
				$result['product_type'] = "";
			}
            $result['web_type'] = strtolower( $result['product_type'] );
        }

        if ($this->postType == 'barnet-concept') {
            $concept_type_term = get_post_meta(intval($this->id), 'concept_type_term', TRUE);
            $result['concept_type_term'] = $concept_type_term;
			if($concept_type_term){
				$concept_term=get_term( $concept_type_term );
				$result['concept_type']=$concept_term->name;
			}
			else {
				$result['concept_type'] = "";
			}
            $result['web_type']='concept';
            $result['web_type'] .= $result['concept_type']  ? "_" . DataHelper::camel2SnakeCase($result['concept_type'] ) : "";
        }
         
        $response = array(
            'data' => $result
        );

        if ($returnSingleData) {
            return $response;
        }
       
        if (isset($this->postType)) {
            if (in_array(self::TAXONOMY, $advanced)) {
                $response[self::TAXONOMIES] = $this->getTaxonomies();
            }

            if (in_array(self::RELATIONSHIP, $advanced)) {
                $response[self::RELATIONSHIP] = $this->getRelationship(array(), $fixed);
            }

            if (in_array(self::WIDGET, $advanced)) {
                $response[self::WIDGET] = $this->getWidgets();
            }

            return $response;
        } else {
            return $response['data'];
        }
    }

    public static function getSearchField($class)
    {
        $reflection = new ReflectionClass($class);
        $metaFields = array_map(function ($e) {
            return DataHelper::camel2SnakeCase($e->getName());
        }, $reflection->getProperties(ReflectionProperty::IS_PRIVATE));

        return array(
            "post" => array(
                "post_title"
            ),
            "meta" => $metaFields
        );
    }

    public static function getSubclassesOf()
    {
        $result = array();
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, self::class)) {
                $result[] = $class;
            }
        }
        return $result;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostDate()
    {
        return $this->_postDate;
    }

    /**
     * @param mixed $postDate
     * @return $this
     */
    public function setPostDate($postDate)
    {
        $this->_postDate = $postDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostTitle()
    {
        return $this->_postTitle;
    }

    /**
     * @param mixed $postTitle
     * @return $this
     */
    public function setPostTitle($postTitle)
    {
        $this->_postTitle = $postTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostExcerpt()
    {
        return empty($this->_postExcerpt) ? get_the_excerpt($this->id) : $this->_postExcerpt;
    }

    /**
     * @param mixed $postExcerpt
     * @return $this
     */
    public function setPostExcerpt($postExcerpt)
    {
        $this->_postExcerpt = $postExcerpt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostExcerptFull()
    {
        return empty($this->_postExcerptFull) ? get_the_excerpt($this->id) : $this->_postExcerptFull;
    }

    /**
     * @param mixed $postExcerptFull
     * @return $this
     */
    public function setPostExcerptFull($postExcerptFull)
    {
        $this->_postExcerptFull = $postExcerptFull;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostName()
    {
        return strval($this->_postName);
    }

    /**
     * @param mixed $postName
     * @return $this
     */
    public function setPostName($postName)
    {
        $this->_postName = $postName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostModified()
    {
        return $this->_postModified;
    }

    /**
     * @param mixed $postModified
     * @return $this
     */
    public function setPostModified($postModified)
    {
        $this->_postModified = $postModified;
        return $this;
    }

    public function getPermalink()
    {
        return isset($this->_permalink) ? $this->_permalink : get_permalink($this->id);
    }

    /**
     * @return mixed
     */
    public function getWebType()
    {
        return $this->_webType;
    }

    /**
     * @param mixed $webType
     * @return $this
     */
    public function setWebType($webType)
    {
        $this->_webType = $webType;
        return $this;
    }

    /**
     * @return false|string
     */
    public function getPostType()
    {
        return $this->postType;
    }

    /**
     * @param $videoId
     * @return string
     */
    public function getMediaExternalVideoURL($videoId = 0)
    {
        if (!empty($videoId)) {
            $permalink = wp_get_attachment_url($videoId);
            $fileName = basename($permalink);
            $ex = explode(".", $fileName);
            $titleFile = sanitize_title($ex[0]);
            $rss = array(
                'id' => $videoId,
                'file' => basename($permalink),
                'domain' => $_SERVER['SERVER_NAME'],
            );

            $token = $this->secure->encode(serialize($rss));
            return get_rest_url() . "barnet/v1/attachment/$titleFile?file=$fileName&token=$token";
        }
        return '';
    }

    /**
     * @param $str
     * @return string
     */
    public function trimStringDes($str = "")
    {
        $str = html_entity_decode($str);
        $str = str_replace(array("&nbsp;", "&amp;"), array(" ", "&"), $str);
        $str = preg_replace("/ {1,}/", " ", $str);
        return trim($str);
    }
}
