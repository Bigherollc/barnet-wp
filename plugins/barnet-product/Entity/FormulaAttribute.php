<?php

class FormulaAttributeEntity extends BarnetEntity
{
    protected $taxonomyList = array(
        'fattribute-set'
    );

    private $formulaAttributeMedia;
    private $formulaAttributeThumnail;
    private $formulaAttributeShortDescription;
    private $formulaAttributeOrder;

    /**
     * @return mixed
     */
    public function getFormulaAttributeMedia()
    {
        return $this->formulaAttributeMedia;
    }

    /**
     * @param mixed $formulaAttributeMedia
     */
    public function setFormulaAttributeMedia($formulaAttributeMedia): void
    {
        $this->formulaAttributeMedia = $formulaAttributeMedia;
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
    public function getFormulaAttributeThumnail()
    {
        return $this->formulaAttributeThumnail;
    }

    /**
     * @param mixed $formulaAttributeThumnail
     * @return $this
     */
    public function setFormulaAttributeThumnail($formulaAttributeThumnail)
    {
        $this->formulaAttributeThumnail = $formulaAttributeThumnail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaAttributeShortDescription()
    {
        return $this->formulaAttributeShortDescription;
    }

    /**
     * @param mixed $formulaAttributeShortDescription
     * @return $this
     */
    public function setFormulaAttributeShortDescription($formulaAttributeShortDescription)
    {
        $this->formulaAttributeShortDescription = $formulaAttributeShortDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormulaAttributeOrder()
    {
        return $this->formulaAttributeOrder;
    }

    /**
     * @param mixed $formulaAttributeOrder
     * @return $this
     */
    public function setFormulaAttributeOrder($formulaAttributeOrder)
    {
        $this->formulaAttributeOrder = $formulaAttributeOrder;
        return $this;
    }

}

class FattributeEntity extends FormulaAttributeEntity
{
}
