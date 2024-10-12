<?php

class DigitalCodeEntity extends BarnetEntity
{
    private $digitalCode;

    /**
     * @return mixed
     */
    public function getDigitalCode()
    {
        return $this->digitalCode;
    }

    /**
     * @param mixed $digitalCode
     * @return $this
     */
    public function setDigitalCode($digitalCode)
    {
        $this->digitalCode = $digitalCode;
        return $this;
    }
}

class DigitalEntity extends BarnetEntity
{
    private $digitalCode;

    /**
     * @return mixed
     */
    public function getDigitalCode()
    {
        return $this->digitalCode;
    }

    /**
     * @param mixed $digitalCode
     * @return $this
     */
    public function setDigitalCode($digitalCode)
    {
        $this->digitalCode = $digitalCode;
        return $this;
    }
}