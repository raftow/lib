<?php
class AfwConfigObject extends AFWRoot
{
    private $configArr;
    private $data;
    /**
     * __construct
     * Constructor
     * @param string $table
     */
    public function __construct($configArr, $data) 
    {        
        if (!$configArr) throw new AfwRuntimeException("AfwConfigObject constructor need a valid config array");                
        
        $this->configArr = $configArr;
        $this->data = $data;
    }

    public function getVal($attribute)
    {
        if($attribute == "settings") return $this->configArr;
        if($attribute == "data") return $this->data;
        return $this->configArr[$attribute];
    }


    public function setData($attribute, $value)
    {
        $this->data[$attribute] = $value;
    }

    public function getData($attribute)
    {
        return $this->data[$attribute];
    }
}