<?php

class ProductConceptEntity extends BarnetEntity
{
    protected $taxonomyList = array(
        'sub-concept-category'
    );

    protected $relationShipList = array(
        'pconcepts_to_products',
        'pconcepts_to_concepts'
    );

    private $productConceptDescription;
    private $productConceptRightText;
    private $productConceptOrder;

    /**
     * @return mixed
     */
    public function getProductConceptDescription()
    {
        return $this->productConceptDescription;
    }

    /**
     * @param mixed $productConceptDescription
     * @return $this
     */
    public function setProductConceptDescription($productConceptDescription)
    {
        $this->productConceptDescription = $productConceptDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductConceptRightText()
    {
        return $this->productConceptRightText;
    }

    /**
     * @param mixed $productConceptRightText
     * @return $this
     */
    public function setProductConceptRightText($productConceptRightText)
    {
        $this->productConceptRightText = $productConceptRightText;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductConceptOrder()
    {
        if (isset($this->productConceptOrder) && trim($this->productConceptOrder) == "") {
            $this->productConceptOrder = null;
        }
        return $this->productConceptOrder;
    }

    /**
     * @param mixed $productConceptOrder
     * @return $this
     */
    public function setProductConceptOrder($productConceptOrder)
    {
        $this->productConceptOrder = $productConceptOrder;
        return $this;
    }
}

class PconceptEntity extends ProductConceptEntity
{
}
