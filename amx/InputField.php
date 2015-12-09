<?php
/** Class InputField
* Crea código XHTML para un control de tipo input field.
* PHP version: 5.x
* @version 1.0
* @author: Leonardo Molina; lama_amx AT hotmail DOT com
*
*/
/* CHANGELOG
	1.0 2015-01-05: Versión inicial
*/
class InputField {
		/** String. plantilla a usar para crear el código final. En caso de modificarse, debe mantenerse el formato (regiones, variable)
		 * @see Template
		*/
	public $template;
		/** String. valor para el atributo html 'name' del InputField resultante */
	public $name;
		/** String. Opcional. valor para el atributo html 'id' del InputField resultante. */
	public $id;
		/** String. Texto que acompaña al input field */
	public $label;
		/** String. contenido del atributo value */
	public $value;
		/** String. Tipo de control. Default: text */
	public $type ='text';
		/** Booleano. Indica si el input field debe estar deshabilitado */
	public $disabled;
		/** Booleano. Indica si el input field debe ser de sólo lectura */
	public $readonly;
		/** String. Atributos HTML adicionales */
	public $attr;

		/** Constructor. Crea una instancia de InputField
		* @param $name. String. Nombre del componente.
		* @see $name
		* @param $id. String. Se usará para el ID del control
		* @see $id
		* @return void
		*/
	public function __construct ($name, $id=null){
		$this->template =<<<EOT
item[
	<input id="{id}" name="{name}" type="{type}" value="{value}" {attr} />
]item
label[
	<label for="{id}"><span>{label}</span></label>
]label

EOT;

		$this->name =$name;
		$this->id =($id) ? $id : $name;
	}

		/** Crea y devuelve el código html del InputField
		* @return String. Código HTML resultante.
		*/
	public function getHTML (){
        $result =$this->getInputField ();
        $result .=$this->getLabel ();
        return $result;
	}

    public function getInputField (){
		$tplResult =new Template ();
		$tplResult->source =$this->template;
        $attributes =array ();
        if (!empty ($this->attr)){
            $attributes[] =$this->attr;
        }
        if ($this->disabled){
            $attributes[] ='disabled="disabled"';
        }
        if ($this->readonly){
            $attributes[] ='readonly="readonly"';
        }
        $attributes =implode (' ', $attributes);
        $itemInfo =array (
             'id'    =>$this->id
            ,'name'  =>$this->name
            ,'value'  =>$this->value
            ,'type'  =>$this->type
            ,'attr'  =>$attributes
        );
        $tplResult->addRegion ('item', $itemInfo);
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