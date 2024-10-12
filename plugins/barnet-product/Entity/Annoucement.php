<?php

class AnnoucementEntity extends BarnetEntity
{
    private $anDescription;
    private $anOptional;
    private $anBtnText;
    private $anBtnType;
    private $anAlertBanner;
    private $anAlertBgColor;
    private $anStyle;
    private $anBbImage;
    private $anExpiratedDate;
    private $anLocationType;
    private $anDevice;
    private $anNewWindow;
    private $anArea;

    /**
     * @return mixed
     */
    public function getAnDescription()
    {
        return $this->anDescription;
    }

    /**
     * @param mixed $anDescription
     * @return $this
     */
    public function setAnDescription($anDescription)
    {
        $this->anDescription = $anDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnOptional()
    {
        return $this->anOptional;
    }

    /**
     * @param mixed $anOptional
     * @return $this
     */
    public function setAnOptional($anOptional)
    {
        $this->anOptional = $anOptional;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnBtnText()
    {
        return $this->anBtnText;
    }

    /**
     * @param mixed $anBtnText
     * @return $this
     */
    public function setAnBtnText($anBtnText)
    {
        $this->anBtnText = $anBtnText;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnBtnType()
    {
        return $this->anBtnType;
    }

    /**
     * @param mixed $anBtnType
     * @return $this
     */
    public function setAnBtnType($anBtnType)
    {
        $this->anBtnType = $anBtnType;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getAnAlertBanner()
    {
        return $this->anAlertBanner;
    }

    /**
     * @param mixed $anAlertBanner
     * @return $this
     */
    public function setAnAlertBanner($anAlertBanner)
    {
        $this->anAlertBanner = $anAlertBanner;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnAlertBgColor()
    {
        return $this->anAlertBgColor;
    }

    /**
     * @param mixed $anAlertBgColor
     * @return $this
     */
    public function setAnAlertBgColor($anAlertBgColor)
    {
        $this->anAlertBgColor = $anAlertBgColor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnStyle()
    {
        return $this->anStyle;
    }

    /**
     * @param mixed $anStyle
     * @return $this
     */
    public function setAnStyle($anStyle)
    {
        $this->anStyle = $anStyle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnBbImage()
    {
        return $this->anBbImage;
    }

    /**
     * @param mixed $anBbImage
     * @return $this
     */
    public function setAnBbImage($anBbImage)
    {
        $this->anBbImage = $anBbImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnExpiratedDate()
    {
        return $this->anExpiratedDate;
    }

    /**
     * @param mixed $anExpiratedDate
     * @return $this
     */
    public function setAnExpiratedDate($anExpiratedDate)
    {
        $this->anExpiratedDate = $anExpiratedDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnLocationType()
    {
        return $this->anLocationType;
    }

    /**
     * @param mixed $anLocationType
     * @return $this
     */
    public function setAnLocationType($anLocationType)
    {
        $this->anLocationType = $anLocationType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnDevice()
    {
        return $this->anDevice;
    }

    /**
     * @param mixed $anDevice
     * @return $this
     */
    public function setAnDevice($anDevice)
    {
        $this->anDevice = $anDevice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnNewWindow()
    {
        return $this->anNewWindow;
    }

    /**
     * @param mixed $anNewWindow
     * @return $this
     */
    public function setAnNewWindow($anNewWindow)
    {
        $this->anNewWindow = $anNewWindow;
        return $this;
    }
    /**
     * @return mixed
     */
    public function getAnArea()
    {
        $anArea=null;
        if(isset($this->$anArea))$anArea=$this->$anArea;
        return $anArea;
    }

    /**
     * @param mixed $anarea
     * @return $this
     */
    public function setAnArea($anArea)
    {
        $this->anArea = $anArea;
        return $this;
    }
}
