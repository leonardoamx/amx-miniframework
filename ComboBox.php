<?
/** Class ComboBox
* Crea el c�digo XHTML de un ComboBox.
* Las opciones del componente se pueden tomar de una matriz (a su vez creada desde una consulta SQL) o ser dados manualmente.
* Permite definir un elemento que ser� seleccionado por defecto y un mensaje (prompt) para dar instrucciones
* PHP version: 5.x
* @version 2.3
* @author: Leonardo Molina; lama_amx AT hotmail DOT com
*
*/
/* CHANGELOG 
	2.3 2013-07-01: Se agrega la propiedad $size para mostrar una lista en lugar de un combobox
	2.2 2010-05-10: Se agrega la propiedad $showPrompt, para mostrar u omitir el texto de la propiedad $prompt
*/
class ComboBox {
		/** String. valor para el atributo html 'name' del combobox resultante */
	public $name;
		/** String. valor para el atributo html 'id' del combobox resultante, o el mismo de 'name' si no se usa */
	public $id;
		/** Arreglo Asociativo. fuente de datos a usar con el componente */
	public $dataProvider;
		/** String. Campo de dataProvider a usar como etiqueta del combobox, o 'label' si no se indica' */
	public $labelField ='label';
		/** String. Campo de dataProvider a usar como dato del combobox, o 'data' si no se indica' */
	public $dataField ='data';
		/** String o N�mero. Elemento preseleccionado del combobox. debe coincidir con una valor de la lista de datos o si no, la lista de etiquetas */
	public $selectedValue =null;
		/** String. Texto a mostrar como primer opci�n del combo (por default, en blanco) */
	public $prompt ='&nbsp;';
		/** Boolean. Determina si se muestra una opci�n prompt */
	public $showPrompt =true;
		/** N�mero. Define la cantidad de items a mostrar. �sto hace que en lugar de un combobox se muestre una lista de selecci�n */
	public $size =0;

		/** Constructor. Crea una instancia de ComboBox
		* @param $name. String. Nombre del componente.
		* @see $name
		* @param $id. String. ID del componente
		* @see $id
		* @return void
		*/
	public function __construct ($name, $id=false){
		$this->name =$name;
		$this->id =$id OR $name;
		$this->dataProvider =array ();
	}
		/** Asigna una lista de etiquetas a la instancia de ComboBox
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
		/** Asigna una lista de valores a la instancia de ComboBox, para el atributo html 'value'
		* @param $data. Arreglo simple. Lista de valores
		* @return void
		*/
	public function setData ($data){
		foreach ($data as $k=>$v){
			$this->dataProvider[$k]['data'] =$v;
			}
		}
		/** Crea y devuelve el c�digo html del ComboBox
		* @return String. C�digo HTML resultante.
		*/
	public function getHTML (){
		$selected ='';
		$size ='';
		if ($this->size > 0)
			$size ='size="'.$this->size.'"';
		$html ='';
		$html .=<<<EOT

					<select id="{$this->id}" name="{$this->name}" $size>
EOT;
		if ($this->showPrompt){
			$html .=<<<EOT
						<option value="-1">{$this->prompt}</option>
EOT;
		}
		foreach ($this->dataProvider as $row){
			$data ='';
			$dataStr ='';
			if (!empty ($row[$this->dataField]) || $row[$this->dataField]==0){
				$data =$row[$this->dataField];
				$dataStr ="value=\"$data\"";
				}
			$label =$row[$this->labelField];
			if (!is_null ($this->selectedValue)){
				$selected = $data==$this->selectedValue ? 'selected="selected"' : '';
				if (!$selected)
					$selected = $label==$this->selectedValue ? 'selected="selected"' : '';
				}
			$html .="
						<option $dataStr $selected>$label</option>";
		}
		$html .='
					</select>';
		return $html;
	}
		/** Env�a a pantalla en c�digo HTML del combobox
		* @return void
		* @see getHTML
		*/
	public function printHTML (){
		echo $this->getHTML ();
	}
}?>