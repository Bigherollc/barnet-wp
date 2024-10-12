<?php

class PageEntity extends BarnetEntity
{
    private $pStyle;
    private $pBbImage;
    private $pBackgroundImage;
    private $pTitle;
    private $pShortDescription;
    private $pShowApp;

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
    public function getPStyle()
    {
        return $this->pStyle;
    }

    /**
     * @param mixed $pStyle
     * @return $this
     */
    public function setPStyle($pStyle)
    {
        $this->pStyle = $pStyle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPBbImage()
    {
        return $this->pBbImage;
    }

    /**
     * @param mixed $pBbImage
     * @return $this
     */
    public function setPBbImage($pBbImage)
    {
        $this->pBbImage = $pBbImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPBackgroundImage()
    {
        return $this->pBackgroundImage;
    }

    /**
     * @param mixed $pBackgroundImage
     * @return $this
     */
    public function setPBackgroundImage($pBackgroundImage)
    {
        $this->pBackgroundImage = $pBackgroundImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPShortDescription()
    {
        return $this->pShortDescription;
    }

    /**
     * @param mixed $pShortDescription
     * @return $this
     */
    public function setPShortDescription($pShortDescription)
    {
        $this->pShortDescription = $pShortDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPTitle()
    {
        return $this->pTitle;
    }

    /**
     * @param mixed $pTitle
     * @return $this
     */
    public function setPTitle($pTitle)
    {
        $this->pTitle = $pTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPShowApp()
    {
        return $this->pShowApp;
    }

    /**
     * @param mixed $pShowApp
     * @return $this
     */
    public function setPShowApp($pShowApp)
    {
        $this->pShowApp = $pShowApp;
        return $this;
    }

}
