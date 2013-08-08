<?php
/** Class RadioButtonGroup
* Crea código XHTML para un grupo de controles input de tipo radio.
* Las opciones del componente se pueden tomar de una matriz (a su vez creada desde una consulta SQL) o ser dados manualmente.
* Permite definir un elemento que será seleccionado por defecto
* PHP version: 5.x
* @version 1.0
* @author: Leonardo Molina; lama_amx AT hotmail DOT com
*
*/
/* CHANGELOG
	1.0 2013-04-08: Versión inicial
*/
class RadioButtonGroup {
		/** String. plantilla a usar para crear el código final. En caso de modificarse, debe mantenerse el formato (regiones, variable)
		 * @see Template
		*/
	public $template;
		/** String. valor para el atributo html 'name' del RadioButtonGroup resultante */
	public $name;
		/** String. valor para el atributo html 'id' del RadioButtonGroup resultante, o el mismo de 'name' si no se usa */
	public $idPrefix;
		/** Arreglo Asociativo. fuente de datos a usar con el componente */
	public $dataProvider;
		/** String. Campo de dataProvider a usar como etiqueta del RadioButtonGroup, o 'label' si no se indica' */
	public $labelField ='label';
		/** String. Campo de dataProvider a usar como dato del RadioButtonGroup, o 'data' si no se indica' */
	public $dataField ='data';
		/** String o Número. Elemento preseleccionado del RadioButtonGroup. debe coincidir con una valor de la lista de datos o si no, la lista de etiquetas */
	public $selectedValue =null;

		/** Constructor. Crea una instancia de RadioButtonGroup
		* @param $name. String. Nombre del componente.
		* @see $name
		* @param $idPrefix. String. Se usará para los IDs de cada control, añadiendo números al final (0, 1, ...)
		* @see $idPrefix
		* @return void
		*/
	public function __construct ($name, $idPrefix=false){
		$this->template =<<<EOT
item[
	<input id="{id}" name="{name}" type="radio" value="{data}" />
	<label for="{id}"><span>{label}</span></label>
]item
selectedItem[
	<input id="{id}" name="{name}" type="radio" value="{data}" checked="checked" />
	<label for="{id}"><span>{label}</span></label>
]selectedItem

EOT;

		$this->name =$name;
		$this->idPrefix =$name;
		if ($idPrefix)
			$this->idPrefix =$idPrefix;
		$this->dataProvider =array ();
	}
		/** Asigna una lista de etiquetas a la instancia de RadioButtonGroup
		* @param $labels. Arreglo simple. Lista de etiquetas
		* @return void
		*/
	public function setLabels ($labels){
		foreach ($labels as $k=>$v){
			$this->dataProvider[$k]['label'] =$v;
			if (empty ($this->dataProvider[$k]['data']))
				$this->dataProvider[$k]['data'] =$v;
		}
	}
		/** Asigna una lista de valores a la instancia de RadioButtonGroup, para el atributo html 'value'
		* @param $data. Arreglo simple. Lista de valores
		* @return void
		*/
	public function setData ($data){
		foreach ($data as $k=>$v){
			$this->dataProvider[$k]['data'] =$v;
		}
	}
		/** Crea y devuelve el código html del RadioButtonGroup
		* @return String. Código HTML resultante.
		*/
	public function getHTML (){
		$selected ='';
		$tplResult =new Template ();
		$tplResult->source =$this->template;
		foreach ($this->dataProvider as $k=>$row){
			$data ='';
			if (!empty ($row[$this->dataField]) || $row[$this->dataField]==0){
				$data =$row[$this->dataField];
			}
			$itemInfo =array (
				 'id'    =>$this->idPrefix .$k
				,'name'  =>$this->name
				,'data'  =>$data
				,'label' =>$row[$this->labelField]
			);
			$currentRegion ='item';
			if (!is_null ($this->selectedValue)){
				if ($data==$this->selectedValue)
					$currentRegion ='selectedItem';
				elseif ($itemInfo['label']==$this->selectedValue)
					$currentRegion ='selectedItem';
			}
			$tplResult->addRegion ($currentRegion, $itemInfo);
		}
		return $tplResult->getResult();
	}
		/** Envía a pantalla en código HTML del grupo de componentes
		* @return void
		* @see getHTML
		*/
	public function printHTML (){
		echo $this->getHTML ();
	}
}?>