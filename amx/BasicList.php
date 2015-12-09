<?php
/** Class BasicList
* Crea código HTML para una lista de elementos
* Las opciones del componente se pueden tomar de una matriz (a su vez creada desde una consulta SQL) o ser dados manualmente.
* Permite definir un elemento que será seleccionado
* PHP version: 5.x
* @version 1.1
* @author: Leonardo Molina; lama_amx AT hotmail DOT com
*
*/
/* CHANGELOG
	1.1 2013-04-22: Se agrega el método addElement
	1.0 2013-04-12: Versión inicial
*/
class BasicList {
		/** String. plantilla a usar para crear el código final. En caso de modificarse, debe mantenerse el formato (regiones, variable)
		 * @see Template
		*/
	public $template;
		/** Arreglo Asociativo. fuente de datos a usar con el componente */
	public $dataProvider;
		/** Número. Índice del elemento seleccionado de la lista */
	public $selectedIndex =-1;

		/** Constructor. Crea una instancia de BasicList
		*/
	public function __construct (){
		$this->template =<<<EOT
item[
	<li class="{name}">
		<a href="{url}"><span>{label}</span></a>
	</li>
]item
selectedItem[
	<li class="{name} selected">
		<span>{label}</span>
	</li>
]selectedItem

EOT;

		$this->dataProvider =array ();
	}

		/** Agrega un nuevo elemeto al arreglo dataProvider
		*/
	public function addElement	($item){
		array_push ($this->dataProvider, $item);
	}

		/** Crea y devuelve el código html
		* @return String. Código HTML resultante.
		*/
	public function getHTML (){
		$tplResult =new Template ();
		$tplResult->source =$this->template;
		foreach ($this->dataProvider as $k=>$item){
			$currentRegion ='item';
			if ($this->selectedIndex == $k)
				$currentRegion ='selectedItem';
			$tplResult->addRegion ($currentRegion, $item);
		}
		return $tplResult->getResult();
	}
		/** Envía a pantalla en código HTML del componente
		* @return void
		* @see getHTML
		*/
	public function printHTML (){
		echo $this->getHTML ();
	}
}?>