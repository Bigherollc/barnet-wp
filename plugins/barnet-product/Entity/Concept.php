<?php

class ConceptEntity extends BarnetEntity
{
    protected $taxonomyList = array(
        'concept-category'
    );

    protected $relationShipList = array(
        'pconcepts_to_concepts',
        'formulas_to_concepts',
        'concepts_to_resources',
        'concepts_to_interactives',
        'concepts_to_roles',
        'concepts_to_concepts'
    );

    private $conceptType;    
    private $conceptTypeTerm;    
    private $conceptDescription;
    private $conceptShortDescription;
    private $conceptArea;
    private $conceptParent;
    private $conceptImage;
    private $conceptStyle;
    private $conceptChildren;
    private $conceptOrder;
    private $conceptThumbnail;
    private $conceptPresentionDocs;
    private $conceptVideosDoc;
    private $conceptFormulaCollection;
    private $conceptInteractiveImage;
    private $conceptInteractiveImageApp;
    private $conceptKeyword;
    private $conceptKeywordCustom;

    protected $_conceptThumbnailURL;

    public function __construct($id, $isPostType = true, $includeData = array())
    {
        parent::__construct($id, $isPostType, $includeData);
        $this->_webType .= isset($this->conceptType) ? "_" . DataHelper::camel2SnakeCase($this->conceptType) : "";
    }

    /**
     * @return mixed
     */
    
    public function getConceptType()
    {
        return $this->conceptType;
    }
    

    /**
     * @param mixed $conceptType
     * @return $this
     */
    
    public function setConceptType($conceptType)
    {
        $this->conceptType = $conceptType;
        return $this;
    }
    

    public function getConceptTypeTerm()
    {
        return $this->conceptTypeTerm;
    }

    /**
     * @param mixed $productTypeTerm
     * @return $this
     */
    public function setConceptTypeTerm($conceptTypeTerm)
    {
     
        $this->conceptTypeTerm = $conceptTypeTerm;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptDescription()
    {
        return $this->conceptDescription;
    }

    /**
     * @param mixed $conceptDescription
     * @return $this
     */
    public function setConceptDescription($conceptDescription)
    {
        $this->conceptDescription = $conceptDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptShortDescription()
    {
        return $this->conceptShortDescription;
    }

    /**
     * @param mixed $conceptShortDescription
     * @return $this
     */
    public function setConceptShortDescription($conceptShortDescription)
    {
        $this->conceptShortDescription = $conceptShortDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptArea()
    {
        return $this->conceptArea;
    }

    /**
     * @param mixed $conceptArea
     * @return $this
     */
    public function setConceptArea($conceptArea)
    {
        $this->conceptArea = $conceptArea;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptParent()
    {
        return $this->conceptParent;
    }

    /**
     * @param mixed $conceptParent
     * @return $this
     */
    public function setConceptParent($conceptParent)
    {
        $this->conceptParent = $conceptParent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptImage()
    {
        return $this->conceptImage;
    }

    /**
     * @param mixed $conceptImage
     * @return $this
     */
    public function setConceptImage($conceptImage)
    {
        $this->conceptImage = $conceptImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptStyle()
    {
        return $this->conceptStyle;
    }

    /**
     * @param mixed $conceptStyle
     * @return $this
     */
    public function setConceptStyle($conceptStyle)
    {
        $this->conceptStyle = $conceptStyle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptChildren()
    {
        return $this->conceptChildren;
    }

    /**
     * @param mixed $conceptChildren
     * @return $this
     */
    public function setConceptChildren($conceptChildren)
    {
        $this->conceptChildren = $conceptChildren;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptOrder()
    {
        return $this->conceptOrder;
    }

    /**
     * @param mixed $conceptOrder
     * @return $this
     */
    public function setConceptOrder($conceptOrder)
    {
        $this->conceptOrder = $conceptOrder;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getConceptThumbnail()
    {
        return $this->conceptThumbnail;
    }

    /**
     * @param mixed $conceptThumbnail
     * @return $this
     */
    public function setConceptThumbnail($conceptThumbnail)
    {
        $this->conceptThumbnail = $conceptThumbnail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptThumbnailURL()
    {
        $thumbnailURL =  isset($this->conceptThumbnail) ? wp_get_attachment_url($this->conceptThumbnail) : null;
        global $barnet;
        return isset($thumbnailURL) && $thumbnailURL ? $thumbnailURL : $barnet->getDefaultImage("concept_thumb");
    }

    /**
     * @param mixed $_conceptThumbnailURL
     * @return $this
     */
    public function setConceptThumbnailURL($_conceptThumbnailURL)
    {
        $this->_conceptThumbnailURL = $_conceptThumbnailURL;
        return $this;
    }

    /**
     * @return array
     */
    public function getTaxonomyList(): array
    {
        return $this->taxonomyList;
    }

    /**
     * @param array $taxonomyList
     * @return $this
     */
    public function setTaxonomyList(array $taxonomyList)
    {
        $this->taxonomyList = $taxonomyList;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptInteractiveImage()
    {
        return $this->conceptInteractiveImage;
    }

    /**
     * @param mixed $conceptInteractiveImage
     * @return $this
     */
    public function setConceptInteractiveImage($conceptInteractiveImage)
    {
        $this->conceptInteractiveImage = $conceptInteractiveImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptInteractiveImageApp()
    {
        return $this->conceptInteractiveImageApp;
    }

    /**
     * @param mixed $conceptInteractiveImageApp
     * @return $this
     */
    public function setConceptInteractiveImageApp($conceptInteractiveImageApp)
    {
        $this->conceptInteractiveImageApp = $conceptInteractiveImageApp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptPresentionDocs()
    {
        if (!empty($this->conceptPresentionDocs)) {
            if ($this->isSerialized($this->conceptPresentionDocs)) {
                $this->conceptPresentionDocs = unserialize($this->conceptPresentionDocs);
            }
        }
        return $this->conceptPresentionDocs;
    }

    /**
     * @param mixed $conceptPresentionDoc
     * @return $this
     */
    public function setConceptPresentionDocs($conceptPresentionDocs)
    {
        $this->conceptPresentionDocs = $conceptPresentionDocs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptVideosDoc()
    {
        return $this->conceptVideosDoc;
    }

    /**
     * @param mixed $conceptVideosDoc
     * @return $this
     */
    public function setConceptVideosDoc($conceptVideosDoc)
    {
        $this->conceptVideosDoc = $conceptVideosDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptFormulaCollection()
    {
        return $this->conceptFormulaCollection;
    }

    /**
     * @param mixed $conceptFormulaCollection
     * @return $this
     */
    public function setConceptFormulaCollection($conceptFormulaCollection)
    {
        $this->conceptFormulaCollection = $conceptFormulaCollection;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptKeyword()
    {
        return $this->conceptKeyword;
    }

    /**
     * @param mixed $conceptKeyword
     * @return $this
     */
    public function setConceptKeyword($conceptKeyword)
    {
        $this->conceptKeyword = $conceptKeyword;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptKeywordCustom()
    {
        return $this->conceptKeywordCustom;
    }

    /**
     * @param mixed $conceptKeywordCustom
     * @return $this
     */
    public function setConceptKeywordCustom($conceptKeywordCustom)
    {
        $this->conceptKeywordCustom = $conceptKeywordCustom;
        return $this;
    }

    public function getPostExcerpt()
    {
        if (empty($this->_postExcerpt)) {
            $this->_postExcerpt = '';

            if (!empty($this->conceptShortDescription)) {
                $this->_postExcerpt = strip_tags(strlen($this->conceptShortDescription) >= 30 ?
                    $this->conceptShortDescription : substr($this->conceptShortDescription, 0, 30));
            } else {
//                $postDescription = $this->getConceptDescription();
//
//                if (is_array($postDescription) && count($postDescription) > 0) {
//                    $postDescription = $postDescription[0];
//                }
//
//                $splDesc = explode('.', $postDescription);
//
//                $this->_postExcerpt = strip_tags(count($splDesc) >= 1 ? "{$splDesc[0]}" : $postDescription);
            }
        }
        return $this->_postExcerpt;
    }

    public function getPostExcerptFull()
    {
        if (empty($this->_postExcerptFull)) {
            $this->_postExcerptFull = '';

            if (!empty($this->conceptShortDescription)) {
                $this->_postExcerptFull = strip_tags($this->conceptShortDescription);
            }
        }
        return $this->_postExcerptFull;
    }

    public function toArray($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        
        return $this->checkRoleAndRegion() ? parent::toArray($advanced, $returnSingleData, $fixed, $exceptPropsProtected) : null;
    }

    public function toArrayPublic($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        return parent::toArray($advanced, $returnSingleData, $fixed, $exceptPropsProtected);
    }

    

    public function toArrayAllRoleAndRegion($advanced = array(), $returnSingleData = false, $fixed = true)
    {
        return parent::toArray($advanced, $returnSingleData, $fixed);
    }

    public function checkRoleAndRegion()
    {
        if (!is_user_logged_in()) {
            //return false;
        }
        
        $roleText = "roles";
        $globalText = "global";
        $user = $this->getUser();
        $userRoles = isset($user['role']) ? $user['role'] : array();
        foreach ($userRoles as $k => $r) {
            $userRoles[$k] = strtolower($r);
        }

        if (in_array('administrator', $userRoles)) {
            return true;
        }

        if (is_array($userRoles)) {
            if (isset($this->relationshipManager)) {
                $rolesId = $this->relationshipManager->getData('concepts_to_roles', 0, $this->id);
                if (!empty($rolesId)) {
                    $roles = array_map(function ($e) {
                        $p = $this->relationshipManager->getPost($e);
                        return strtolower($p['post_title']);
                    }, $rolesId);
                } else {
                    $roles = array();
                }
            } else {
                $roles = $this->getRelationship(array('concepts_to_roles'));

                if (isset($roles[$roleText]) && count($roles[$roleText]) > 0) {
                    $roles[$roleText] = array_map(function ($e) {
                        if (is_array($e)) {
                            return strtolower($e['data']['post_title']);
                        }

                        return strtolower($e->post_title);
                    }, $roles[$roleText]);
                    $roles = array_values($roles[$roleText]);
                } else {
                    $roles = array();
                }
            }

            if (count($roles) > 0 && count(array_intersect($userRoles, $roles)) == 0) {
                return false;
            }
        }

        $userType = isset($user['type']) ? $user['type'] : $globalText;

        if ($userType == $globalText) {
            return true;
        }

        if (!in_array($this->conceptArea, array($userType, $globalText))) {
            return false;
        }

        return true;
    }

    public function getIconResource($mimeType)
    {
        if ($mimeType == 'application/pdf') { // PDF
            return 'icon-formula-sheet';
        } elseif ($mimeType == 'application/vnd.ms-powerpoint') { // PPT
            return 'icon-presntation-lg';
        } elseif ($mimeType == 'video/mp4') { // MP4
            return 'icon-video';
        } else {
            return 'icon-summary';
        }
    }
}
