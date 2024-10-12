<?php

class FormulaEntity extends BarnetEntity
{
    const FORMULA_CATEGORY = 'formula-category';

    protected $taxonomyList = array(
        self::FORMULA_CATEGORY
    );

    protected $relationShipList = array(
        'formulas_to_concepts',
        'formulas_to_fattributes',
        'formulas_to_formulas',
        'formulas_to_roles'
    );

    private $formulaCode;
    private $formulaCodeErp;
    private $dateCreated;
    private $dateUpdated;
    private $formulaImage;
    private $formulaDescription;
    private $formulaArea;
    private $formulaSheetDocLabel;
    private $formulaSheetDoc;
    private $formulaCardDocLabel;
    private $formulaCardDoc;
    private $formulaIngredients;
    private $formulaSpecifications;
    private $processSteps;
    private $formulaBase;
    private $formulaVideoResource;
    private $formulaFeatured;
    private $formulaKeyword;
    private $formulaKeywordCustom;
    private $formulaKeyFeature;
    private $formulaHowUse;
    private $formulaFlexible;
    private $formulaKeyIngredients;
    protected $_formulaIdType;
    protected $_formulaIdIcon;
    protected $_formulaIdIconBlack;
    protected $_formulaIcon;
    protected $_formulaIconBlack;
    protected $_formulaImageUrl;

    /**
     * @return mixed
     */
    public function getFormulaCode()
    {
        return $this->formulaCode;
    }

    /**
     * @param mixed $formulaCode
     * @return $this
     */
    public function setFormulaCode($formulaCode)
    {
        $this->formulaCode = $formulaCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaCodeErp()
    {
        return $this->formulaCodeErp;
    }

    /**
     * @param mixed $formulaCodeErp
     * @return $this
     */
    public function setFormulaCodeErp($formulaCodeErp)
    {
        $this->formulaCodeErp = $formulaCodeErp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param mixed $dateCreated
     * @return $this
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param mixed $dateUpdated
     * @return $this
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaImage()
    {
        return $this->formulaImage;
    }

    /**
     * @param mixed $formulaImage
     * @return $this
     */
    public function setFormulaImage($formulaImage)
    {
        $this->formulaImage = $formulaImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaImageUrl()
    {
        if (empty($this->formulaImageUrl) && !empty($this->formulaImage)) {
            $this->_formulaImageUrl = wp_get_attachment_url($this->formulaImage);
        }

        global $barnet;
        return $this->_formulaImageUrl ? $this->_formulaImageUrl : $barnet->getDefaultImage("formula_header");
    }

    /**
     * @return mixed
     */
    public function getFormulaDescription()
    {
        return $this->formulaDescription;
    }

    /**
     * @param mixed $formulaDescription
     * @return $this
     */
    public function setFormulaDescription($formulaDescription)
    {
        $this->formulaDescription = $formulaDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaArea()
    {
        return $this->formulaArea;
    }

    /**
     * @param mixed $formulaArea
     * @return $this
     */
    public function setFormulaArea($formulaArea)
    {
        $this->formulaArea = $formulaArea;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaSheetDoc()
    {
        return $this->formulaSheetDoc;
    }

    /**
     * @param mixed $formulaSheetDoc
     * @return $this
     */
    public function setFormulaSheetDoc($formulaSheetDoc)
    {
        $this->formulaSheetDoc = $formulaSheetDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaCardDoc()
    {
        return $this->formulaCardDoc;
    }

    /**
     * @param mixed $formulaCardDoc
     * @return $this
     */
    public function setFormulaCardDoc($formulaCardDoc)
    {
        $this->formulaCardDoc = $formulaCardDoc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaSheetDocLabel()
    {
        return $this->formulaSheetDocLabel;
    }

    /**
     * @param mixed $formulaSheetDocLabel
     * @return $this
     */
    public function setFormulaSheetDocLabel($formulaSheetDocLabel)
    {
        $this->formulaSheetDocLabel = $formulaSheetDocLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaCardDocLabel()
    {
        return $this->formulaCardDocLabel;
    }

    /**
     * @param mixed $formulaCardDocLabel
     * @return $this
     */
    public function setFormulaCardDocLabel($formulaCardDocLabel)
    {
        $this->formulaCardDocLabel = $formulaCardDocLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaVideoResource()
    {
        return $this->formulaVideoResource;
    }

    /**
     * @param mixed $formulaVideoResource
     * @return $this
     */
    public function setFormulaVideoResource($formulaVideoResource)
    {
        $this->formulaVideoResource = $formulaVideoResource;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getFormulaIngredients()
    {
        return $this->formulaIngredients;
    }

    /**
     * @param mixed $formulaIngredients
     * @return $this
     */
    public function setFormulaIngredients($formulaIngredients)
    {
        $this->formulaIngredients = $formulaIngredients;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaSpecifications()
    {
        return $this->formulaSpecifications;
    }

    /**
     * @param mixed $formulaSpecifications
     * @return $this
     */
    public function setFormulaSpecifications($formulaSpecifications)
    {
        $this->formulaSpecifications = $formulaSpecifications;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProcessSteps()
    {
        return $this->processSteps;
    }

    /**
     * @param mixed $processSteps
     * @return $this
     */
    public function setProcessSteps($processSteps)
    {
        $this->processSteps = $processSteps;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaBase()
    {
        return $this->formulaBase;
    }

    /**
     * @param mixed $formulaBase
     * @return $this
     */
    public function setFormulaBase($formulaBase)
    {
        $this->formulaBase = $formulaBase;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaFeatured()
    {
        return $this->formulaFeatured;
    }

    /**
     * @param mixed $formulaFeatured
     * @return $this
     */
    public function setFormulaFeatured($formulaFeatured)
    {
        $this->formulaFeatured = $formulaFeatured;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaKeyword()
    {
        return $this->formulaKeyword;
    }

    /**
     * @param mixed $formulaKeyword
     * @return $this
     */
    public function setFormulaKeyword($formulaKeyword)
    {
        $this->formulaKeyword = $formulaKeyword;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaKeywordCustom()
    {
        return $this->formulaKeywordCustom;
    }

    /**
     * @param mixed $formulaKeywordCustom
     * @return $this
     */
    public function setFormulaKeywordCustom($formulaKeywordCustom)
    {
        $this->formulaKeywordCustom = $formulaKeywordCustom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaIdType()
    {
        if (empty($this->_formulaIdType)) {
            $this->_formulaIdType = 0;
            $termType = get_term_by('slug', 'type', self::FORMULA_CATEGORY);
            if ($termType) {
                $terms = get_the_terms($this->id, self::FORMULA_CATEGORY);
                if ($terms) {
                    foreach ($terms as $term) {
                        if ($term->parent == $termType->term_id) {
                            $this->_formulaIdType = $term->term_id;
                            break;
                        }
                    }

                }
            }
        }
        return $this->_formulaIdType;
    }

    /**
     * @return mixed
     */
    public function getFormulaIdIcon()
    {
        $imageText = "image";
        if (empty($this->_formulaIdIcon)) {
            $this->_formulaIdIcon = 0;
            $idType = $this->getFormulaIdType();
            if ($idType > 0) {
                $termMeta = get_term_meta($idType);
                if ($termMeta && is_array($termMeta) && isset($termMeta[$imageText]) && count($termMeta[$imageText]) > 0) {
                    $this->_formulaIdIcon = intval($termMeta[$imageText][0]);
                }
            }
        }
        return $this->_formulaIdIcon;
    }

    /**
     * @return mixed
     */
    public function getFormulaIcon()
    {
        if (empty($this->_formulaIcon)) {
            $idIcon = $this->getFormulaIdIcon();

            if ($idIcon > 0) {
                $this->_formulaIcon = wp_get_attachment_url($idIcon);
            }
        }

        global $barnet;
        return $this->_formulaIcon ? $this->_formulaIcon : $barnet->getDefaultImage();
    }

    /**
     * @return mixed
     */
    public function getFormulaIdIconBlack()
    {
        $imageBlackText = "image_black";
        if (empty($this->_formulaIdIconBlack)) {
            $this->_formulaIdIconBlack = 0;
            $idType = $this->getFormulaIdType();
            if ($idType > 0) {
                $termMeta = get_term_meta($idType);
                if ($termMeta && is_array($termMeta) && isset($termMeta[$imageBlackText]) && count($termMeta[$imageBlackText]) > 0) {
                    $this->_formulaIdIconBlack = intval($termMeta[$imageBlackText][0]);
                }
            }
        }
        return $this->_formulaIdIconBlack;
    }

    /**
     * @return mixed
     */
    public function getFormulaIconBlack()
    {
        if (empty($this->_formulaIconBlack)) {
            $idIcon = $this->getFormulaIdIconBlack();

            if ($idIcon > 0) {
                $this->_formulaIconBlack = wp_get_attachment_url($idIcon);
            } else {
                return get_template_directory_uri() . '/assets/images/icon-formula.png';
            }
        }

        global $barnet;
        return $this->_formulaIconBlack ? $this->_formulaIconBlack : $barnet->getDefaultImage();
    }

    public function getPostExcerpt()
    {
        $formulaDescription = $this->formulaDescription;

        if (is_array($formulaDescription) && count($formulaDescription) > 0) {
            $formulaDescription = $formulaDescription[0];
        }

        /*$splDesc = explode('.', $formulaDescription);

        return strip_tags(count($splDesc) >= 2 ? "{$splDesc[0]}.{$splDesc[1]}" : $formulaDescription);*/
        return $this->trimStringDes(strip_tags($formulaDescription));
    }

    public function getPostExcerptFull()
    {
        if (!empty($this->_postExcerptFull)) {
            return $this->_postExcerptFull;
        }
        $formulaDescription = $this->formulaDescription;

        if (is_array($formulaDescription) && count($formulaDescription) > 0) {
            $formulaDescription = $formulaDescription[0];
        }

        $this->_postExcerptFull = strip_tags($formulaDescription);

        return $this->trimStringDes($this->_postExcerptFull);
    }

    /**
     * @return mixed
     */
    public function getFormulaKeyFeature()
    {
        return $this->formulaKeyFeature;
    }

    /**
     * @param mixed $formulaKeyFeature
     * @return $this
     */
    public function setFormulaKeyFeature($formulaKeyFeature)
    {
        $this->formulaKeyFeature = $formulaKeyFeature;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaHowUse()
    {
        return $this->formulaHowUse;
    }

    /**
     * @param mixed $formulaHowUse
     * @return $this
     */
    public function setFormulaHowUse($formulaHowUse)
    {
        $this->formulaHowUse = $formulaHowUse;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaFlexible()
    {
        return $this->formulaFlexible;
    }

    /**
     * @param mixed $formulaFlexible
     * @return $this
     */
    public function setFormulaFlexible($formulaFlexible)
    {
        $this->formulaFlexible = $formulaFlexible;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaKeyIngredients()
    {
        return $this->formulaKeyIngredients;
    }

    /**
     * @param mixed $formulaKeyIngredients
     * @return $this
     */
    public function setFormulaKeyIngredients($formulaKeyIngredients)
    {
        $this->formulaKeyIngredients = $formulaKeyIngredients;
        return $this;
    }

    public function toArray($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        return $this->checkRoleAndRegion() ? parent::toArray($advanced, $returnSingleData, $fixed, $exceptPropsProtected) : null;
    }

    public function toArrayPublic($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        return  parent::toArray($advanced, $returnSingleData, $fixed, $exceptPropsProtected) ;
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
            return false;
        }


        if (is_array($userRoles)) {
            if (isset($this->relationshipManager)) {
                $rolesId = $this->relationshipManager->getData('formulas_to_roles', 0, $this->id);
                if (!empty($rolesId)) {
                    $roles = array_map(function ($e) {
                        $p = $this->relationshipManager->getPost($e);
                        return strtolower($p['post_title']);
                    }, $rolesId);
                } else {
                    $roles = array();
                }
            } else {
                $roles = $this->getRelationship(array('formulas_to_roles'));

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

        if (!in_array($this->formulaArea, array($userType, $globalText))) {
            return false;
        }

        return true;
    }
}
