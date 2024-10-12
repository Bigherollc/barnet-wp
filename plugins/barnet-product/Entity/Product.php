<?php

class ProductEntity extends BarnetEntity
{
    protected $taxonomyList = array(
        'product-category'
    );

    protected $relationShipList = array(
        'products_to_formulas',
        'products_to_concepts',
        'products_to_pattributes',
        'products_to_digital-code',
        'products_to_roles',
        'pconcepts_to_products',
    );
    private $productTypeTerm;
   // private $productType;
    private $productId;
    private $inciName;
    private $productHeaderImage;
    private $productDescription;
    private $productDescriptionConcept;
    private $productDescriptionLogged;
    private $productKeyword;
    private $productKeywordCustom;
    private $productMsdsDocLabel;
    private $productMsdsDoc;
    private $productSpecDocLabel;
    private $productSpecDoc;
    private $productKissDocLabel;
    private $productKissDoc;
    private $productDossierDocLabel;
    private $productDossierDoc;
    private $productPresentationDocLabel;
    private $productPresentationDoc;
    private $productFormulaDocLabel;
    private $productFormulaDoc;
    private $productSnapshotsDocLabel;
    private $productSnapshotsDoc;
    private $productOtherDocs;
    private $productVideoResource;
    private $productArea;
    private $productRoles;
    private $productUsage;
    private $productIso;
    private $productUpdated;
    private $productFeatured;
    private $productGlobalCompliance;
    private $productArchitectureTechnology;
    private $productAlternateGroup;
    private $productRightSubText;
    private $productPublic;
    protected $_productArchitectureTechnologyNoHtml;

    public function __construct($id, $isPostType = true, $includeData = array())
    {
        parent::__construct($id, $isPostType, $includeData);
        
       // $this->_webType = strtolower($this->productType);
    }

    /**
     * @return mixed
     */
    /*
    public function getProductType()
    {
        return $this->productType;
    }
    */

    /**
     * @param mixed $productType
     * @return $this
     */
    /*
    public function setProductType($productType)
    {
        //echo "here";
       
        $this->productType = $productType;
        return $this;
    }*/

    public function getProductTypeTerm()
    {
        return $this->productTypeTerm;
    }

    /**
     * @param mixed $productTypeTerm
     * @return $this
     */
    public function setProductTypeTerm($productTypeTerm)
    {
     
        $this->productTypeTerm = $productTypeTerm;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param mixed $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInciName()
    {
        return $this->inciName;
    }

    /**
     * @param mixed $inciName
     * @return $this
     */
    public function setInciName($inciName)
    {
        $this->inciName = $inciName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductHeaderImage()
    {
        return $this->productHeaderImage;
    }

    /**
     * @param mixed $productHeaderImage
     * @return $this
     */
    public function setProductHeaderImage($productHeaderImage)
    {
        $this->productHeaderImage = $productHeaderImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductDescription()
    {
        return $this->productDescription;
    }

    /**
     * @param mixed $productDescription
     * @return $this
     */
    public function setProductDescription($productDescription)
    {
        $this->productDescription = $productDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductDescriptionConcept()
    {
        return $this->productDescriptionConcept;
    }

    /**
     * @param mixed $productDescriptionConcept
     * @return $this
     */
    public function setProductDescriptionConcept($productDescriptionConcept)
    {
        $this->productDescriptionConcept = $productDescriptionConcept;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductDescriptionLogged()
    {
        return $this->productDescriptionLogged;
    }

    /**
     * @param mixed $productDescriptionLogged
     * @return $this
     */
    public function setProductDescriptionLogged($productDescriptionLogged)
    {
        $this->productDescriptionLogged = $productDescriptionLogged;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductKeyword()
    {
        return $this->productKeyword;
    }

    /**
     * @param mixed $productKeyword
     * @return $this
     */
    public function setProductKeyword($productKeyword)
    {
        $this->productKeyword = $productKeyword;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductKeywordCustom()
    {
        return $this->productKeywordCustom;
    }

    /**
     * @param mixed $productKeywordCustom
     * @return $this
     */
    public function setProductKeywordCustom($productKeywordCustom)
    {
        $this->productKeywordCustom = $productKeywordCustom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductMsdsDoc()
    {
        return $this->productMsdsDoc;
    }

    /**
     * @param mixed $productMsdsDoc
     * @return $this
     */
    public function setProductMsdsDoc($productMsdsDoc)
    {
        $this->productMsdsDoc = $productMsdsDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductSpecDoc()
    {
        return $this->productSpecDoc;
    }

    /**
     * @param mixed $productSpecDoc
     * @return $this
     */
    public function setProductSpecDoc($productSpecDoc)
    {
        $this->productSpecDoc = $productSpecDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductKissDoc()
    {
        return $this->productKissDoc;
    }

    /**
     * @param mixed $productKissDoc
     * @return $this
     */
    public function setProductKissDoc($productKissDoc)
    {
        $this->productKissDoc = $productKissDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductDossierDoc()
    {
        return $this->productDossierDoc;
    }

    /**
     * @param mixed $productDossierDoc
     * @return $this
     */
    public function setProductDossierDoc($productDossierDoc)
    {
        $this->productDossierDoc = $productDossierDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductPresentationDoc()
    {
        return $this->productPresentationDoc;
    }

    /**
     * @param mixed $productPresentationDoc
     * @return $this
     */
    public function setProductPresentationDoc($productPresentationDoc)
    {
        $this->productPresentationDoc = $productPresentationDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductFormulaDoc()
    {
        return $this->productFormulaDoc;
    }

    /**
     * @param mixed $productFormulaDoc
     * @return $this
     */
    public function setProductFormulaDoc($productFormulaDoc)
    {
        $this->productFormulaDoc = $productFormulaDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductSnapshotsDoc()
    {
        return $this->productSnapshotsDoc;
    }

    /**
     * @param mixed $productSnapshotsDoc
     * @return $this
     */
    public function setProductSnapshotsDoc($productSnapshotsDoc)
    {
        $this->productSnapshotsDoc = $productSnapshotsDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductMsdsDocLabel()
    {
        return $this->productMsdsDocLabel;
    }

    /**
     * @param mixed $productMsdsDocLabel
     * @return $this
     */
    public function setProductMsdsDocLabel($productMsdsDocLabel)
    {
        $this->productMsdsDocLabel = $productMsdsDocLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductSpecDocLabel()
    {
        return $this->productSpecDocLabel;
    }

    /**
     * @param mixed $productSpecDocLabel
     * @return $this
     */
    public function setProductSpecDocLabel($productSpecDocLabel)
    {
        $this->productSpecDocLabel = $productSpecDocLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductKissDocLabel()
    {
        return $this->productKissDocLabel;
    }

    /**
     * @param mixed $productKissDocLabel
     * @return $this
     */
    public function setProductKissDocLabel($productKissDocLabel)
    {
        $this->productKissDocLabel = $productKissDocLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductDossierDocLabel()
    {
        return $this->productDossierDocLabel;
    }

    /**
     * @param mixed $productDossierDocLabel
     * @return $this
     */
    public function setProductDossierDocLabel($productDossierDocLabel)
    {
        $this->productDossierDocLabel = $productDossierDocLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductPresentationDocLabel()
    {
        return $this->productPresentationDocLabel;
    }

    /**
     * @param mixed $productPresentationDocLabel
     * @return $this
     */
    public function setProductPresentationDocLabel($productPresentationDocLabel)
    {
        $this->productPresentationDocLabel = $productPresentationDocLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductFormulaDocLabel()
    {
        return $this->productFormulaDocLabel;
    }

    /**
     * @param mixed $productFormulaDocLabel
     * @return $this
     */
    public function setProductFormulaDocLabel($productFormulaDocLabel)
    {
        $this->productFormulaDocLabel = $productFormulaDocLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductSnapshotsDocLabel()
    {
        return $this->productSnapshotsDocLabel;
    }

    /**
     * @param mixed $productSnapshotsDocLabel
     * @return $this
     */
    public function setProductSnapshotsDocLabel($productSnapshotsDocLabel)
    {
        $this->productSnapshotsDocLabel = $productSnapshotsDocLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductOtherDocs()
    {
        if (!empty($this->productOtherDocs)) {
            if ($this->isSerialized($this->productOtherDocs)) {
                $this->productOtherDocs = unserialize($this->productOtherDocs);
            }
        }
        return $this->productOtherDocs;
    }

    /**
     * @param mixed $productOtherDocs
     * @return $this
     */
    public function setProductOtherDocs($productOtherDocs)
    {
        $this->productOtherDocs = $productOtherDocs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductArea()
    {
        return $this->productArea;
    }

    /**
     * @param mixed $productArea
     * @return $this
     */
    public function setProductArea($productArea)
    {
        $this->productArea = $productArea;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductRoles()
    {
        return $this->productRoles;
    }

    /**
     * @param mixed $productRoles
     * @return $this
     */
    public function setProductRoles($productRoles)
    {
        $this->productRoles = $productRoles;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductUsage()
    {
        return $this->productUsage;
    }

    /**
     * @param mixed $productUsage
     * @return $this
     */
    public function setProductUsage($productUsage)
    {
        $this->productUsage = $productUsage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductIso()
    {
        return $this->productIso;
    }

    /**
     * @param mixed $productIso
     * @return $this
     */
    public function setProductIso($productIso)
    {
        $this->productIso = $productIso;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductUpdated()
    {
        return $this->productUpdated;
    }

    /**
     * @param mixed $productUpdated
     * @return $this
     */
    public function setProductUpdated($productUpdated)
    {
        $this->productUpdated = $productUpdated;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductFeatured()
    {
        return $this->productFeatured;
    }

    /**
     * @param mixed $productFeatured
     * @return $this
     */
    public function setProductFeatured($productFeatured)
    {
        $this->productFeatured = $productFeatured;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductGlobalCompliance()
    {
        return $this->productGlobalCompliance;
    }

    /**
     * @param mixed $productGlobalCompliance
     * @return $this
     */
    public function setProductGlobalCompliance($productGlobalCompliance)
    {
        $this->productGlobalCompliance = $productGlobalCompliance;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductArchitectureTechnology()
    {
        return $this->productArchitectureTechnology;
    }
    /**
     * @return mixed
     */
    public function getProductArchitectureTechnologyNoHtml()
    {
        if (empty($this->_productArchitectureTechnologyNoHtml)) {
            if ($this->isSerialized($this->productArchitectureTechnology)) {
                $value = unserialize($this->productArchitectureTechnology);
                if (isset($value['at_description'])) {
                    $value['at_description'] = strip_tags($value['at_description']);
                }
                $this->_productArchitectureTechnologyNoHtml = serialize($value);
            }
        }

        return $this->_productArchitectureTechnologyNoHtml;
    }

    /**
     * @param mixed $productArchitectureTechnology
     * @return $this
     */
    public function setProductArchitectureTechnology($productArchitectureTechnology)
    {
        $this->productArchitectureTechnology = $productArchitectureTechnology;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductAlternateGroup()
    {
        return $this->productAlternateGroup;
    }

    /**
     * @param mixed $productAlternateGroup
     * @return $this
     */
    public function setProductAlternateGroup($productAlternateGroup)
    {
        $this->productAlternateGroup = $productAlternateGroup;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductRightSubText()
    {
        return $this->productRightSubText;
    }

    /**
     * @return mixed
     */
    public function getProductVideoResource()
    {
        return $this->productVideoResource;
    }

    /**
     * @param mixed $productVideoResource
     * @return $this
     */
    public function setProductVideoResource($productVideoResource)
    {
        $this->productVideoResource = $productVideoResource;
        return $this;
    }

    /**
     * @param mixed $productRightSubText
     * @return $this
     */
    public function setProductRightSubText($productRightSubText)
    {
        $this->productRightSubText = $productRightSubText;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductPublic()
    {
        return $this->productPublic;
    }

    /**
     * @param mixed $productPublic
     * @return $this
     */
    public function setProductPublic($productPublic)
    {
        $this->productPublic = $productPublic;
        return $this;
    }

    public function getPostExcerpt()
    {
        if (!empty($this->_postExcerpt)) {
            return $this->_postExcerpt;
        }
		$productDescription="";
        global $barnetUserLoggedIn;

        if ($barnetUserLoggedIn) {
            $productDescription = $this->isSerialized($this->productDescriptionLogged) ? unserialize($this->productDescriptionLogged) : $this->productDescriptionLogged;
        } else {
            $productDescription = $this->isSerialized($this->productDescription) ? unserialize($this->productDescription) : $this->productDescription;
        }

        if (is_array($productDescription) && count($productDescription) > 0) {
            $productDescription = $productDescription[0];
        }

        /*$splDesc = explode('.', $productDescription);

        return strip_tags(count($splDesc) >= 2 ? "{$splDesc[0]}.{$splDesc[1]}" : $productDescription);*/
		if(!$productDescription)$productDescription="";
        return $this->trimStringDes(strip_tags($productDescription));
    }

    public function getPostExcerptFull()
    {
        if (!empty($this->_postExcerptFull)) {
            return $this->_postExcerptFull;
        }

        global $barnetUserLoggedIn;

        if ($barnetUserLoggedIn) {
            $productDescription = $this->isSerialized($this->productDescriptionLogged) ? unserialize($this->productDescriptionLogged) : $this->productDescriptionLogged;
        } else {
            $productDescription = $this->isSerialized($this->productDescription) ? unserialize($this->productDescription) : $this->productDescription;
        }

        if (is_array($productDescription) && count($productDescription) > 0) {
            $productDescription = $productDescription[0];
        }
		if(!$productDescription)$productDescription="";
        $this->_postExcerptFull = strip_tags($productDescription);
        return $this->trimStringDes($this->_postExcerptFull);
    }

    public function toArray($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        return $this->checkRoleAndRegion() ? parent::toArray($advanced, $returnSingleData, $fixed, $exceptPropsProtected) : null;
    }

    public function toArrayAllRoleAndRegion($advanced = array(), $returnSingleData = false, $fixed = true)
    {
        return parent::toArray($advanced, $returnSingleData, $fixed);
    }

    public function checkRoleAndRegion()
    {
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

        if (!is_user_logged_in()) {
            if ($this->productPublic == 1) {
                return true;
            }
            return false;
        }

        if (is_array($userRoles)) {
            if (isset($this->relationshipManager)) {
                $rolesId = $this->relationshipManager->getData('products_to_roles', 0, $this->id);
                if (!empty($rolesId)) {
                    $roles = array_map(function ($e) {
                        $p = $this->relationshipManager->getPost($e);
                        return strtolower($p['post_title']);
                    }, $rolesId);
                } else {
                    $roles = array();
                }
            } else {
                $roles = $this->getRelationship(array('products_to_roles'));

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

        $area = $this->getProductArea();

        $userType = isset($user['type']) ? $user['type'] : $globalText;

        if ($userType == $globalText) {
            return true;
        }

        if (!in_array($area, array($userType, $globalText))) {
            return false;
        }

        return true;
    }

     /**
     * @return mixed
     */
    public function getOtherIcon()
    {
        return $this->otherIcon;
    }

    /**
     * @param mixed $otherIcon
     * @return $this
     */
    public function setOtherIcon($otherIcon)
    {
        $this->otherIcon = $otherIcon;
        return $this;
    }
}
