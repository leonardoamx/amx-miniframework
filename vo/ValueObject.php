<?php
abstract class ValueObject {
    public function updateValues ($data){
        foreach ($data as $property=>$value){
            $this->$property =$value;
        }
    }

    public function getArray (){
        $result =array ();
        foreach ($this as $key=>$value){
            $result[$key] =$value;
        }
        return $result;
    }

    public function getXML (){
        return $this->getNode ($this);
    }
    
    private function getNode ($node){
        $result ='<item>';
        foreach ($node as $field=>$value){
            if (is_array ($value)){
                $result .="<$field>";
                if (!empty ($value)){
                    foreach ($value as $subitem){
                        $result .=$this->getNode ($subitem);
                    }
                }
                $result .="</$field>";
            } else {
                $result .="<$field><![CDATA[$value]]></$field>";
            }
        }
        $result .='</item>';
        return $result;        
    }
}
