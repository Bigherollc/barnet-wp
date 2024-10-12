<?php

class CustomerEntity extends BarnetEntity
{
    protected $relationShipList = array(
        'digitals_to_customers'
    );

    private $customerRoles;

    /**
     * @return mixed
     */
    public function getCustomerRoles()
    {
        return $this->customerRoles;
    }

    /**
     * @param mixed $customerRoles
     * @return $this;
     */
    public function setCustomerRoles($customerRoles)
    {
        $this->customerRoles = $customerRoles;
        return $this;
    }
}
