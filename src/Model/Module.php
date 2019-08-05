<?php

namespace App\Model;

class Module extends Node
{
    private $dialPlanId;
    private $moduleType;
    private $moduleName;
    private $moduleConfig;
    private $reference;

    public function __construct(int $id)
    {
        parent::__construct($id);
    }


}
