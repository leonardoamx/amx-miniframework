<?php
/** Class CheckBox
* Crea código XHTML para un grupo de controles input de tipo checkbox.
* PHP version: 5.x
* @version 1.0
* @author: Leonardo Molina; lama_amx AT hotmail DOT com
*
*/
/* CHANGELOG
	1.0 2014-12-02: Versión inicial
*/
class CheckBox {
		/** String. plantilla a usar para crear el código final. En caso de modificarse, debe mantenerse el formato (regiones, variable)
		 * @see Template
		*/
	public $template;
		/** String. valor para el atributo html 'name' del CheckBox resultante */
	public $name;
		/** String. Opcional. valor para el atributo html 'id' del CheckBox resultante. */
	public $id;
		/** String. Texto que acompaña al checkbox */
	public $label;
		/** String. contenido del atributo value */
	public $value;
		/** Booleano. Indica si el checkbox debe estar seleccionado */
	public $selected;
		/** Strin. Atributos HTML adicionales */
	public $attr;

		/** Constructor. Crea una instancia de CheckBox
		* @param $name. String. Nombre del componente.
		* @see $name
		* @param $id. String. Se usará para el ID del control
		* @see $id
		* @return void
		*/
	public function __construct ($name, $id=null){
		$this->template =<<<EOT
item[
	<input id="{id}" name="{name}" type="checkbox" value="{value}" {attr} />
]item
selectedItem[
	<input id="{id}" name="{name}" type="checkbox" value="{value}" checked="checked" {attr} />
]selectedItem
label[
	<label for="{id}"><span>{label}</span></label>
]label

EOT;

		$this->name =$name;
		$this->id =($id) ? $id : $name;
	}

		/** Crea y devuelve el código html del CheckBox
		* @return String. Código HTML resultante.
		*/
	public function getHTML (){
        $result =$this->getCheckBox ();
        $result .=$this->getLabel ();
        return $result;
	}

    public function getCheckBox (){
		$tplResult =new Template ();
		$tplResult->source =$this->template;
        $itemInfo =array (
             'id'    =>$this->id
            ,'name'  =>$this->name
            ,'value'  =>$this->value
            ,'attr'  =>$this->attr
        );
        $currentRegion =$this->selected ? 'selectedItem' : 'item';
        $tplResult->addRegion ($currentRegion, $itemInfo);
		return $tplResult->getResult();
    }

    public function getLabel (){
		$tplResult =new Template ();
		$tplResult->source =$this->template;
        $itemInfo =array (
             'id'    =>$this->id
            ,'label'  =>$this->label
        );
        $tplResult->addRegion ("label", $itemInfo);
		return $tplResult->getResult();
    }

}