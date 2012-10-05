<?php
	require_once ('Utils.php');

/** Simple class to deal with templates
** @author: Leonardo Molina lama_amx at hotmail dot com
** @version: 0.1
** @changelog:
** 0.1 2012.10.03. Port from Template class. Recognition of variables was changed from $variable to {variable}
*/

class Template {
	public $source;

	private $result;

	public function __construct ($file='') {
		$this->source ='';
		$this->result ='';
		if ($file)
			$this->load ($file);
		}
	public function addRegion ($id, $vals=false) {
		$region ='';
		$ini =strpos ($this->source, "$id{")+(strlen ($id)+1);
		$fin =strpos ($this->source, "}$id", $ini);
		$len =$fin-$ini;
		$region =substr ($this->source, $ini, $len);
		if ($vals)
			foreach ($vals as $k =>$v){
				if (!is_array ($v))
					$region =$this->replaceVariable ($region, $k, $v);
				}
		$this->result .="\n$region";
		}
	public function load ($file) {
		$this->source =@file_get_contents ($file);
		if (!$this->source){
			Utils::log ("No se pudo cargar el archivo $file");
			exit ();
			}
		}
	public function getResult () {
		return $this->result;
		}
	public function printResult () {
		echo $this->getResult ();
		}
	public function clearResult () {
		$this->result ='';
		}

	private function replaceVariable ($txt, $id, $val){
		return str_replace ('{'.$id.'}', $val, $txt);
		}
}?>