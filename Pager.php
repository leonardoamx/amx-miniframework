<?php //require_once ('Template2.php');
	/** Prints a control to navigate through paginated records. It doesn't manage pages itself; it only prints a toolbar with buttons.
		v0.1
		
	Changelog:
		0.1 2012-10-16 First release
	*/
class Pager {

	public $url;
	public $paramName ='p';
	public $currentPage;
	public $totalPages;
	public $template ='
ini{
	<p class="amxPager">
}ini
firstItem{
		<a class="button first" href="{url}"><span>	Primero		</span></a>
}firstItem
prevItem{
		<a class="button prev" href="{url}"><span>	Anterior	</span></a>
}prevItem
nextItem{
		<a class="button next" href="{url}"><span>	Siguiente	</span></a>
}nextItem
lastItem{
		<a class="button last" href="{url}"><span>	Último		</span></a>
}lastItem
labels{
			<span class="label value">{currentPage}</span>
			<span class="label separator">/</span>
			<span class="label total">{totalPages}</span>
}labels
firstItemDisabled{
		<a class="button first disabled" href="{url}"><span>Primero		</span></a>
}firstItemDisabled                                   
prevItemDisabled{                                    
		<a class="button prev disabled" href="{url}"><span>	Anterior	</span></a>
}prevItemDisabled                                    
nextItemDisabled{                                    
		<a class="button next disabled" href="{url}"><span>	Siguiente	</span></a>
}nextItemDisabled                                    
lastItemDisabled{                                    
		<a class="button last disabled" href="{url}"><span>	Último		</span></a>
}lastItemDisabled
fin{
	</p>
}fin
';

	private $result;

	public function __construct (){
		$this->result =new Template();
		$this->result->source =$this->template;
	}

	public function getHTML (){
		$result ='';
		if ($this->validateData() == 0){
			if (strstr ($this->url, '?'))
				$url ="{$this->url}&amp;{$this->paramName}=";
			else
				$url ="{$this->url}?{$this->paramName}=";

			$this->result->addRegion ('ini');
			if ($this->currentPage > 1){
				$this->result->addRegion ('firstItem'	,array('url'=>$url.'1'));
				$this->result->addRegion ('prevItem'	,array('url'=>$url.intval ($this->currentPage-1)));
			} else {
				$this->result->addRegion ('firstItemDisabled'	,array('url'=>$url.'1'));
				$this->result->addRegion ('prevItemDisabled' 	,array('url'=>$url.'1'));
			}
			$this->result->addRegion ('labels', array(
				 'currentPage'=>$this->currentPage
				,'totalPages'=>$this->totalPages
			));
			if ($this->currentPage < $this->totalPages){
				$this->result->addRegion ('nextItem', array('url'=>$url.intval ($this->currentPage+1)));
				$this->result->addRegion ('lastItem', array('url'=>$url.$this->totalPages));
			} else {
				$this->result->addRegion ('nextItemDisabled', array('url'=>$url.$this->totalPages));
				$this->result->addRegion ('lastItemDisabled', array('url'=>$url.$this->totalPages));
			}
			$this->result->addRegion ('fin');
			$result =$this->result->getResult();
		}
		return $result;
	}

	public function printHTML (){
		echo $this->getHTML();
	}

	private function validateData (){
		$invalid =0;
		if (empty ($this->currentPage)){
			throw new Exception ('$currentPage property not set in Pager instance');
			$invalid++;
		}
		if (empty ($this->totalPages)){
			throw new Exception ('$totalPages property not set in Pager instance');
			$invalid++;
		}
		if (empty ($this->url)){
			throw new Exception ('$url property not set in Pager instance');
			$invalid++;
		}
		return $invalid;
	}

}
?>