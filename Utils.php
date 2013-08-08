<?php
/** Clase de funciones varias
** @author: Leonardo Molina lama_amx at hotmail dot com
** @version: 3.7
** @changelog:
	3.6 2013.06.27:  Added booleanValue
	3.6 2013.04.12:  Added containsSomeValue
	3.5 2013.02.15: 
		sql method was moved to LinkSQL
		log methos were moved to Logger Class
	3.4 2012.07.26: Added beginHTML5
	3.3 2011.06.07: Added getItemFromObject method
	3.2 Revision
	3.1 Added a third param in Utils::log and Utils::trace to place an optional text
	3.0 Changed from constants to static properties: $logEnabled ,$logLevel ,$logToFile ,$logPathDeveloment ,$logPathLive
	2.9 Added findInArray
	2.8 sql method, modified.
	2.8 minor changes in log methods
	2.7 log methods enhaced
	2.6 Added $logToFile constant to work with Utils::log & Utils::trace methods
	2.4 Modified string2ascii. Now lowercase transform and whitespaces elimination are optional
	2.3 Added function extractField
	2.2 Improved sequence: Support for incremental & decremental sequences
	2.2 Added getMonthList: returns a list of months
	2.2 Added getWeekList: returns a list of days
	2.1 Added explodeToArray
*/

class Utils {

	private static $htmlLanguage;

		/** Convierte $texto a minúsculas, reemplaza caracteres acentuados por letras simples y retira espacios
		 * @param	$string: Texto a formatear
		 * @return   Texto formateado
		 */
	public static function string2ascii ($string, $lowercase=true, $nowhitespace=true){
		$string1 =utf8_encode ('á,é,í,ó,ú,ü,ñ');
		$string2 = ('a,e,i,o,u,u,n');
		$string  =utf8_encode ($string);
		$string =$lowercase ? strtolower ($string) : $string;
		$string =$nowhitespace ? str_replace (' ', '', $string) : $string;
		$string =str_replace (
				explode (',', $string1),
				explode (',', $string2),
				$string
			);
		return $string;
		}

	public static function beginHTML5 ($lang='es-MX'){
		self::$htmlLanguage =$lang;
		echo <<<EOT
<!DOCTYPE html>
<html lang="$lang">

EOT;
		}

	public static function beginHTML ($lang='es-MX'){
		self::$htmlLanguage =$lang;
		echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="$lang" xml:lang="$lang" dir="ltr">

EOT;
		}

	public static function beginHead ($title='', $encoding=false){
		$charset ='charset=';
		$charset .=$encoding ? $encoding : 'UTF-8';
		$lang =self::$htmlLanguage;
		echo <<<EOT
	<head>
		<title>$title</title>
		<meta http-equiv="Content-Type" content="text/html;  $charset" />
		<meta http-equiv="Content-Languaje" content="$lang" />

EOT;
		}

	public static function endHead (){
		echo <<<EOT
	</head>

EOT;
		}

		/** check if a variable is equal to some value from a list of values
		* @param mixed $variable: Variable to check
		* @param array $values: List of valid values
		* @return bool. True if the variable is equal to some of the values
		*/
	public static function containsSomeValue ($variable, $values){
		if (!is_array ($values))
			$values =array ($values);
		$result =false;
		foreach ($values as $v){
			if ($variable == $v){
				$result =true;
				break;
			}
		}
		return $result;
	}

		/** Divide una cadena en fragmentos, donde se encuentra el separador indicado y asigna cada fragmento a una posición en un arreglo. Elimina espacios al principio y final de cada índice
		* @param $separador: Cadena. Caracter que se usará como separador
		* @param $source: Texto a convertir en Arreglo
		* @return Arreglo con la cadena dividida en fragmentos
		*/
	public static function explodeToArray ($separador, $source){
		$result =array ();
		$result	=explode ($separador, $source);
		foreach ($result as $k =>$v)
			$result[$k] =trim ($v);
		return $result;
		}

		/** Busca un término en un arreglo simple
		* @param $obj: nombre del arreglo
		* @param $term: término de búsqueda
		* @return entero. Índice de la 1er coincidencia o -1 si no la hubo.
		* @example $ind =findInArray ($frutas, 'manzana);
		*/
	public static function findInArray ($obj, $term){
		$ind =-1;
		foreach ($obj as $k=>$v)
			if ($v == $term){
				$ind =$k;
				break;
				}
		return $ind;
		}

		/** Busca un término en una matriz de 2 dimensiones
		* @param $obj: nombre del arreglo
		* @param $prop: campo del arreglo donde se ha de buscar
		* @param $term: término de búsqueda
		* @return entero. Índice de la 1er coincidencia o -1 si no la hubo.
		* @example $ind =findInObject ($frutas, 'nombre', 'manzana);
		*/
	public static function findInObject ($obj, $prop, $term){
		$ind =-1;
		foreach ($obj as $k=>$v){
			if ($v[$prop] == $term){
				$ind =$k;
				break;
			}
		}
if (!is_array ($obj)) {
	$e =new Exception ();
	Logger::log ($e->getTraceAsString(), 'Utils.php findInObject');
}
		return $ind;
		}

		/** Devuelve el primer término hallado en una matriz de 2 dimensiones
		* @param $obj: nombre del arreglo
		* @param $prop: campo del arreglo donde se ha de buscar
		* @param $term: término de búsqueda
		* @return arreglo asociativo. la 1er coincidencia o un arreglo vacío si no la hubo.
		*/
	public static function getItemFromObject ($obj, $prop, $term){
		$result =array ();
		$ind =self::findInObject ($obj, $prop, $term);
		if ($ind != -1)
			$result =$obj[$ind];
		return $result;
		}

		/** Extrae un campo de una matriz bidimensional y devuelve los valores en un arreglo simple
		* @param $obj: nombre de la matriz a evaluar
		* @param $prop: campo del arreglo que se ha de extraer
		* @return Arreglo.
		*/
	public static function extractField ($obj, $prop){
		$result =array ();
		foreach ($obj as $row){
			array_push ($result, $row[$prop]);
			}
		return $result;
		}

		/** Crea una secuencia numérica, incluye los números de inicio y final. Según en primer número sea mayor o menor que el final, la secuencia es ascendente o descendente.
		* @param $start: Entero. Número de inicio
		* @param $end: Entero. Número final.
		* @return: Arreglo. Cada número de la secuencia en un índice del arreglo
		*/
	public static function sequence ($start, $end){
		$return =array ();
		if ($start < $end)
				for ($a=$start; $a<=$end; $a++)
					array_push ($return, $a);
			else
				for ($a=$start; $a>=$end; $a--)
					array_push ($return, $a);

		return $return;
		}

	public static function getMonthList (){
		return array (
			array ('num' => 1, 'nombre' =>'Enero'),
			array ('num' => 2, 'nombre' =>'Febrero'),
			array ('num' => 3, 'nombre' =>'Marzo'),
			array ('num' => 4, 'nombre' =>'Abril'),
			array ('num' => 5, 'nombre' =>'Mayo'),
			array ('num' => 6, 'nombre' =>'Junio'),
			array ('num' => 7, 'nombre' =>'Julio'),
			array ('num' => 8, 'nombre' =>'Agosto'),
			array ('num' => 9, 'nombre' =>'Septiembre'),
			array ('num' =>10, 'nombre' =>'Octubre'),
			array ('num' =>11, 'nombre' =>'Noviembre'),
			array ('num' =>12, 'nombre' =>'Diciembre')
			);
		}

	public static function getWeekList (){
		return array (
			array ('num' => 0, 'nombre' =>'Domingo'),
			array ('num' => 1, 'nombre' =>'Lunes'),
			array ('num' => 2, 'nombre' =>'Martes'),
			array ('num' => 3, 'nombre' =>'Miércoles'),
			array ('num' => 4, 'nombre' =>'Jueves'),
			array ('num' => 5, 'nombre' =>'Viernes'),
			array ('num' => 6, 'nombre' =>'Sábado')
			);
		}

	public static function convert2ascii ($text){
		$text = htmlentities($text, ENT_QUOTES, 'UTF-8');
		$text = strtolower($text);
		$patron = array (
			// Espacios, puntos y comas por guion
			'/[\., ]+/' => '-',

			// Vocales
			'/&agrave;/' => 'a',
			'/&egrave;/' => 'e',
			'/&igrave;/' => 'i',
			'/&ograve;/' => 'o',
			'/&ugrave;/' => 'u',

			'/&aacute;/' => 'a',
			'/&eacute;/' => 'e',
			'/&iacute;/' => 'i',
			'/&oacute;/' => 'o',
			'/&uacute;/' => 'u',

			'/&acirc;/' => 'a',
			'/&ecirc;/' => 'e',
			'/&icirc;/' => 'i',
			'/&ocirc;/' => 'o',
			'/&ucirc;/' => 'u',

			'/&atilde;/' => 'a',
			'/&etilde;/' => 'e',
			'/&itilde;/' => 'i',
			'/&otilde;/' => 'o',
			'/&utilde;/' => 'u',

			'/&auml;/' => 'a',
			'/&euml;/' => 'e',
			'/&iuml;/' => 'i',
			'/&ouml;/' => 'o',
			'/&uuml;/' => 'u',

			'/&auml;/' => 'a',
			'/&euml;/' => 'e',
			'/&iuml;/' => 'i',
			'/&ouml;/' => 'o',
			'/&uuml;/' => 'u',

			// Otras letras y caracteres especiales
			'/&aring;/' => 'a',
			'/&ntilde;/' => 'n',

			// Agregar aqui mas caracteres si es necesario

		);

		$text = preg_replace(array_keys($patron),array_values($patron),$text);
		return $text;
	}
	
	public static function fromUTF8toEntities ($text){
		$patron =array (
			 'Á' =>'&Aacute;'
			,'É' =>'&Eacute;'
			,'Í' =>'&Iacute;'
			,'Ó' =>'&Oacute;'
			,'Ú' =>'&Uacute;'
			,'Ü' =>'&Uuml;'
			,'Ñ' =>'&Ntilde;'
			,'á' =>'&aacute;'
			,'é' =>'&eacute;'
			,'í' =>'&iacute;'
			,'ó' =>'&oacute;'
			,'ú' =>'&uacute;'
			,'ü' =>'&uuml;'
			,'ñ' =>'&ntilde;'

//			,'Á' =>'&aacute;'
//			,'É' =>'&eacute;'
//			,'Í' =>'&iacute;'
//			,'Ó' =>'&oacute;'
//			,'Ú' =>'&uacute;'
//			,'Ü' =>'&uuml;'
//			,'Ñ' =>'&ntilde;'
			,'ã¡' =>'&aacute;'
			,'ã©' =>'&eacute;'
			,'ã­' =>'&iacute;'
			,'ã³' =>'&oacute;'
			,'ãº' =>'&uacute;'
//			,'ü' =>'&uuml;'
			,'ã‘' =>'&ntilde;'
			,'ã±' =>'&ntilde;'
			,'ã' =>'&ntilde;'
			,',' =>''
			,'.' =>''
			,'"' =>''
			,"'" =>''
		);
//		$text =utf8_decode ($text);
		$text =utf8_encode ($text);
		if (strpos ($text, 'Ã') !== false)
			$text =utf8_decode ($text);
		return str_replace(array_keys($patron), array_values($patron), $text);
	}

	public static function booleanToString ($v, $trueVal='Sí', $falseVal='No'){
		return (boolean) $v ? $trueVal : $falseVal;
	}
	
	public static function booleanValue ($value){
		return self::containsSomeValue ($value, array (1,'true','TRUE','YES',true));
	}
}?>