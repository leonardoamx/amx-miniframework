<?php require_once ('Template2.php');
class Pager {
		/** Associative array. Used to build the final list.
			Using the default template, each element must have the properties id, label & url
			@example
			$pagerInstance->dataProvider =array(
				,array ('id'=>'first' ,'label'=>'The text' ,'url'=>'path.ext')
				,array ('id'=>'second' ,'label'=>'The text' ,'url'=>'path.ext')
			);
		* @see Template class
		*/
	public $dataProvider;
	public $selectedItem;
	public $template ='
ini{
	<ul>
}ini
selectedItem{
		<li><span class="{class}">{label}</span></li>
}selectedItem
item{
		<li><a href="{url}" class="{class}"><span>{label}</span></a></li>
}item
fin{
	</ul>	
}fin
';

	public function __construct (){
		$this->elements =array();
	}
	
}
?>