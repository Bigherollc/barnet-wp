<?php

class ProductAttributeEntity extends BarnetEntity
{
    protected $taxonomyList = array(
        'attribute-set'
    );

    private $productAttributeMedia;
    private $productAttributeThumnail;
    private $productAttributeShortDescription;
    private $productAttributeOrder;

    /**
     * @return mixed
     */
    public function getProductAttributeMedia()
    {
        return $this->productAttributeMedia;
    }

    /**
     * @param mixed $productAttributeMedia
     */
    public function setProductAttributeMedia($productAttributeMedia): void
    {
        $this->productAttributeMedia = $productAttributeMedia;
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
    public function getProductAttributeThumnail()
    {
        return $this->productAttributeThumnail;
    }

    /**
     * @param mixed $productAttributeThumnail
     * @return $this
     */
    public function setProductAttributeThumnail($productAttributeThumnail)
    {
        $this->productAttributeThumnail = $productAttributeThumnail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductAttributeShortDescription()
    {
        return $this->productAttributeShortDescription;
    }

    /**
     * @param mixed $productAttributeShortDescription
     * @return $this
     */
    public function setProductAttributeShortDescription($productAttributeShortDescription)
    {
        $this->productAttributeShortDescription = $productAttributeShortDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductAttributeOrder()
    {
        return $this->productAttributeOrder;
    }

    /**
     * @param mixed $productAttributeOrder
     * @return $this
     */
    public function setProductAttributeOrder($productAttributeOrder)
    {
        $this->productAttributeOrder = $productAttributeOrder;
        return $this;
    }

}

class PattributeEntity extends ProductAttributeEntity
{
}
