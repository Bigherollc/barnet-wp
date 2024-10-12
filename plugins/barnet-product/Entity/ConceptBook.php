<?php

class ConceptBookEntity extends BarnetEntity
{
    protected $taxonomyList = array(
        'concept-category'
    );

    protected $relationShipList = array(
        'concepts_book_to_roles',
    );

    private $conceptBookImage;
    private $conceptBookOrder;
    private $conceptBookStyle;
    private $conceptBookArea;

    /**
     * @return mixed
     */
    public function getConceptBookImage()
    {
        return $this->conceptBookImage;
    }

    /**
     * @param mixed $conceptBookImage
     * @return $this
     */
    public function setConceptBookImage($conceptBookImage)
    {
        $this->conceptBookImage = $conceptBookImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptBookOrder()
    {
        return $this->conceptBookOrder;
    }

    /**
     * @param mixed $conceptBookOrder
     * @return $this
     */
    public function setConceptBookOrder($conceptBookOrder)
    {
        $this->conceptBookOrder = $conceptBookOrder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptBookStyle()
    {
        return $this->conceptBookStyle;
    }

    /**
     * @param mixed $conceptBookStyle
     * @return $this
     */
    public function setConceptBookStyle($conceptBookStyle)
    {
        $this->conceptBookStyle = $conceptBookStyle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConceptBookArea()
    {
        return $this->conceptBookArea;
    }

    /**
     * @param mixed $conceptBookArea
     * @return $this
     */
    public function setConceptBookArea($conceptBookArea)
    {
        $this->conceptBookArea = $conceptBookArea;
        return $this;
    }

    public function toArray($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        return $this->checkRoleAndRegion() ? parent::toArray($advanced, $returnSingleData, $fixed, $exceptPropsProtected) : null;
    }
    public function toArrayPublic($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        return  parent::toArray($advanced, $returnSingleData, $fixed, $exceptPropsProtected);
    }
    

    public function checkRoleAndRegion()
    {
        $roleText = "to";
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
            $roles = $this->getRelationship(array('concepts_book_to_roles'));

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

            if (count($roles) > 0 && count(array_intersect($userRoles, $roles)) == 0) {
                return false;
            }
        }

        $userType = isset($user['type']) ? $user['type'] : $globalText;

        if ($userType == $globalText) {
            return true;
        }

        if (!in_array($this->conceptBookArea, array($userType, $globalText))) {
            return false;
        }

        return true;
    }
}