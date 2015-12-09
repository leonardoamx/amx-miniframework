<?php
	if ($_SERVER['SERVER_NAME'] == 'localhost'){
		mysql_connect ('localhost', 'root', '');
		mysql_select_db ('prosaltl_opdb');
	} elseif ($_SERVER['SERVER_NAME'] == 'indiga.mx'){
		mysql_connect ('localhost', 'indigamx_apasion', 'IXiEZW,hu(uK');
		mysql_select_db ('indigamx_apasionados');
	} else {
		mysql_connect ('localhost', 'appsadoc_mayaadm', '+[ne@{k*&.[N');
		mysql_select_db ('appsadoc_mayapass');
	}

?>