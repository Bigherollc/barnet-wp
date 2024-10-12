<?php

class RoleEntity extends BarnetEntity
{
    protected $relationShipList = array(
        'products_to_roles',
        'concepts_to_roles',
        'formulas_to_roles'
    );

    private $roleDescription;

    /**
     * @return mixed
     */
    public function getRoleDescription()
    {
        return $this->roleDescription;
    }

    /**
     * @param mixed $roleDescription
     * @return $this
     */
    public function setRoleDescription($roleDescription)
    {
        $this->roleDescription = $roleDescription;
        return $this;
    }
}
