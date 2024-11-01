<?php

/**
 * FormularEngine - Die Formular Engine produziert Formulare als Objekte.
 * Mittels Methoden können Formular-Elemente geladen werden. Ebenso kann
 * mit diversen Methoden die Eingabe überprüft werden.
 * 
 * @author KWM
 * @copyright Copyright 2007, strauss esolutions
 * @version 1.0
 * @since 10.11.2008
 */

$GLOBALS['checks'] = array();
$GLOBALS['hidings'] = array();

class TF_Form
{
	/**
	 *
	 * @var string $submit_name Speichert den Namen, den der Button haben soll
	 */
	private $submit_name = "";
	/**
	 *
	 * @var string $submit_value Speichert die Anzeige eines Buttons
	 */
	private $submit_value = "";
	/**
	 *
	 * @var string $submit_button Speichert die Eigenschaft, ob im Formular ein Button angezeigt werden soll
	 */
	private $submit_button = false;
	/**
	 *
	 * @var string $prefix Speichert den Prefix, nach dem Formular-Namen folgen
	 */
	private $prefix = "";
	/**
	 *
	 * @var boolean $table Speichert die Eigenschaft, ob Formular in Tabelle organisiert wird
	 */
	private $table = false;
	/**
	 *
	 * @var boolean $form Speichert die Eigenschaft, ob Formular mit Form-Tag angezeigt werden soll
	 */
	private $form = false;
	/**
	 *
	 * @var array $errors Speichert alle Fehler zu Formular Elemente
	 */
	public $errors = array();

	/**
	 * Aufbau des Formular Objektes und Darstellung des Form-Startags
	 * 
	 * @param array Parameterübergabe<br>
	 * - action							: Actionlink (wenn Form muss gesetzt sein!)<br>
	 * - form							: Bei True mit Form-Tags<br>
	 * - table							: Bei True in Tabelle<br>
	 * - prefix			(Standard "")	: Prefix aller Formular-Feldernamen<br>
	 * - submit_button	(Standard false)	: Ob Submit-Button angezeigt werden soll<br>
	 * - submit							: Submit-Buttonname (wenn Submit-Button muss gesetzt sein!)<br>
	 * - submit_value					: Submit-Buttonanzeige (Wenn Submit-Button muss gesetzt sein!)
	 */
	function __construct($arr)
	{
		//Beschickung der Variablen
		if(isset($arr["prefix"]))
		{
			$this->prefix				= $arr["prefix"];
		}
		
		if(isset($arr["submit"]))
		{
			$this->submit_name			= $arr["submit"];
			if($this->submit_name != "pt")
			{
				$this->submit_name		= $this->prefixChange($this->submit_name);
			}
			
			if(isset($arr["submit_button"]))
			{
				$this->submit_button	= $arr["submit_button"];
				$this->submit_value		= $arr["submit_value"];
			}
		}
		
		
		
		//Formular Starttag
		if(isset($arr["form"]) && $arr["form"] == true)
		{
			echo ('<form action="' . $arr["action"] . '" method="post">
');
			$this->form = true;
		}

		//Wenn Tabelle ja, zeige mit an
		if(isset($arr["table"]) && $arr["table"] == true)
		{
			echo ('	<table border="0" cellpadding="5" cellspacing="0" width="100%">
');
			$this->table = true;
		}
	}

	/**
	 * Schließung des Objektes mit Schließ-Form-Tag
	 */
	function __destruct()
	{
		//Standardwert für Submit
		if($this->submit_value === "") {
			$this->submit_value = "Abschicken";
		}

		//Anzeige Submit
		if($this->table == true && $this->submit_button == true)
		{
			$this->td_submit();
		}
		elseif($this->table == false && $this->submit_button == true)
		{
			$this->submit();
		}
		
		//Anzeige Table-Endtag
		if($this->table == true)
		{
			echo ('	</table>
');
		}
			
		//Anzeige Form-Endtag
		if($this->form == true)
		{
			echo ('</form>
');
		}
	}
	
	public static function jsHead()
	{
		echo ('			<script src="http://kwm-server.de/jquery-1.11.0.min.js"></script>
');
	}
	
	public static function jsScript()
	{
		echo ('			<script>

			var submitStatus = false;
			
			function checkNumberForm(fill, field, message, submitName)
			{
				var checkVar = document.getElementById(field).value;
				checkVar = checkVar.replace(",", ".");
				
				field = field + "Error";

				submitCheck(submitName, field, false);
				
				//Ueberpruefung ob Leer und nicht gefuellt werden muss
				if(checkVar == "" && fill == false)
				{
					$("." + field).html("");
					submitCheck(submitName, field, true);
				}
				//Wenn aber doch gefuellt ist oder gefuellt sein muss
				else
				{
					//Wenn nicht nummerisch
					if(isNaN(checkVar) && checkVar != "")
					{
						//Wenn Message angezeigt weden soll
						if(message == true)
						{
							$("." + field).html("Dieser Wert ist keine Nummer.");
						}
					}
					//Wenn Leer und gefuellt sein muss
					else if(fill == true && checkVar == "")
					{
						if(message == true)
						{
							$("." + field).html("Dieser Wert muss angegeben werden.");
						}
					}
					//Wenn gefuellt und Fuellung in Ordnung ist
					else
					{
						$("." + field).html("");
						submitCheck(submitName, field, true);
					}
				}
			}

			function checkFillForm(field, message, submitName)
			{
				var checkVar = document.getElementById(field).value;

				field = field + "Error";

				submitCheck(submitName, field, false);

				//Wenn leer
				if(checkVar == "")
				{
					//Wenn Message angezeigt werden soll
					if(message == true)
					{
						$("." + field).html("Dieser Wert muss zwingend angegeben werden.");
					}
				}
				//Wenn gefuellt
				else
				{
					$("." + field).html("");
					submitCheck(submitName, field, true);
				}
			}

			function checkDate(checkVar)
			{
				var arr = checkVar.split(".");

				var checkLeap = arr[2] / 4;

				if(arr.length != 3)
				{
					return false;
				}
				else if(isNaN(arr[0]) || isNaN(arr[1]) || isNaN(arr[2]))
				{
					return false;
				}
				else if(arr[2].length != 4 || arr[2] < 1980 || arr[2] > 2100)
				{
					return false;
				}
				else if(arr[1].length > 2 || arr[1].length == 0 || arr[1] > 12 || arr[1] < 1)
				{
					return false;
				}
				else if(arr[0].length > 2 || arr[0].length == 0 || arr[0] > 31 || arr[0] < 1)
				{
					return false;
				}
				else if(arr[0] == 31 && (arr[1] == 2 || arr[1] == 4 || arr[1] == 6 || arr[1] == 9 || arr[1] == 11))
				{
					return false;
				}
				else if(arr[0] == 30 && arr[1] == 2)
				{
					return false;
				}
				else if(arr[0] == 29 && arr[1] == 2 && parseInt(checkLeap) != checkLeap)
				{
					return false;
				}
				else
				{
					return true;
				}
			}

			function checkDateForm(fill, double, field, message, submitName)
			{
				var checkVar = document.getElementById(field).value;

				field = field + "Error";

				submitCheck(submitName, field, false);

				var check = true;
				var pastCheck = true;
				var lengthCheck = true;

				if(double == true)
				{
					if(checkVar.search(" - ") == -1)
					{
						check = false;
						lengthCheck = false;
					}
					else
					{					
						var arr = checkVar.split(" - ");

						if(arr.length != 2)
						{
							check = false;
							lengthCheck = false;
						}

						if(checkDate(arr[0]) == false)
						{
							check = false;
						}

						if(checkDate(arr[1]) == false)
						{
							check = false;
						}

						if(check == true)
						{
							var arr1 = arr[0].split(".");
							var date1 = new Date(arr1[2], arr1[1], arr1[0]);
							var arr2 = arr[1].split(".");
							var date2 = new Date(arr2[2], arr2[1], arr2[0]);

							if(date2.getTime() < date1.getTime())
							{
								check = false;
								pastCheck = false;
							}
						}
					}
				}
				else
				{
					check = checkDate(checkVar);
				}

				//Ueberpruefung ob Leer und nicht gefuellt werden muss
				if(checkVar == "" && fill == false)
				{
					$("." + field).html("");
					submitCheck(submitName, field, true);
				}
				//Wenn doch gefuellt sein muss oder CheckVar nicht leer ist
				else
				{
					//Wenn gefuellt werden muss und checkVar leer ist
					if(fill == true && checkVar == "")
					{
						if(message == true)
						{
							$("." + field).html("Die Datumsangabe ist notwendig.");
						}
					}
					//Wenn Datum falsch ist
					else if(check == false && pastCheck == true && lengthCheck == true)
					{
						if(message == true)
						{
							$("." + field).html("Die Daten sind ung&uuml;ltig.");
						}
					}
					//Wenn letzteres Datum vor ersterem liegt
					else if(check == false && pastCheck == false && lengthCheck == true)
					{
						if(message == true)
						{
							$("." + field).html("Das letzte Datum liegt vor dem ersten.");
						}
					}
					//Wenn bei Doppelt unvollständig
					else if(check == false && pastCheck == true && lengthCheck == false)
					{
						if(message == true)
						{
							$("." + field).html("Die zweite Datumsangabe fehlt.");
						}
					}
					//Wenn alles in Ordnung ist
					else
					{
						$("." + field).html("");
						submitCheck(submitName, field, true);
					}
				}
			}
			
			function checkDateWY(checkVar)
			{
				var arr = checkVar.split(".");

				if(arr.length != 3)
				{
					return false;
				}
				else if(isNaN(arr[0]) || isNaN(arr[1]) || arr[2] != "")
				{
					return false;
				}
				else if(arr[1].length > 2 || arr[1].length == 0 || arr[1] > 12 || arr[1] < 1)
				{
					return false;
				}
				else if(arr[0].length > 2 || arr[0].length == 0 || arr[0] > 31 || arr[0] < 1)
				{
					return false;
				}
				else if(arr[0] == 31 && (arr[1] == 2 || arr[1] == 4 || arr[1] == 6 || arr[1] == 9 || arr[1] == 11))
				{
					return false;
				}
				else if(arr[0] == 30 && arr[1] == 2)
				{
					return false;
				}
				else if(arr[0] == 29 && arr[1] == 2 && parseInt(checkLeap) != checkLeap)
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			
			function checkDateWYForm(fill, field, message, submitName)
			{
				var checkVar = document.getElementById(field).value;

				field = field + "Error";

				submitCheck(submitName, field, false);

				var check = true;

				check = checkDateWY(checkVar);

				//Ueberpruefung ob Leer und nicht gefuellt werden muss
				if(checkVar == "" && fill == false)
				{
					$("." + field).html("");
					submitCheck(submitName, field, true);
				}
				//Wenn doch gefuellt sein muss oder CheckVar nicht leer ist
				else
				{
					//Wenn gefuellt werden muss und checkVar leer ist
					if(fill == true && checkVar == "")
					{
						if(message == true)
						{
							$("." + field).html("Die Datumsangabe ist notwendig.");
						}
					}
					//Wenn Datum falsch ist
					else if(check == false && pastCheck == true && lengthCheck == true)
					{
						if(message == true)
						{
							$("." + field).html("Die Daten sind ung&uuml;ltig.");
						}
					}
					//Wenn alles in Ordnung ist
					else
					{
						$("." + field).html("");
						submitCheck(submitName, field, true);
					}
				}
			}

			function checkTime(checkVar)
			{
				var arr = checkVar.split(":");

				if(arr.length != 2)
				{
					return false;
				}
				else if(arr[0] > 23 || arr[0] < 0 || isNaN(arr[0]))
				{
					return false;
				}
				else if(arr[1] > 59 || arr[1] < 0 || isNaN(arr[1]))
				{
					return false;
				}
				else
				{
					return true;
				}
			}

			function checkTimeForm(fill, double, field, message, submitName)
			{
				var checkVar = document.getElementById(field).value;

				field = field + "Error";

				submitCheck(submitName, field, false);

				var check = true;
				var pastCheck = true;
				var lengthCheck = true;

				if(double == true)
				{
					if(checkVar.search(" - ") == -1)
					{
						check = false;
						lengthCheck = false;
					}
					else
					{					
						var arr = checkVar.split(" - ");

						if(arr.length != 2)
						{
							check = false;
							lengthCheck = false;
						}

						if(checkTime(arr[0]) == false)
						{
							check = false;
						}

						if(checkTime(arr[1]) == false)
						{
							check = false;
						}

						if(check == true)
						{
							var arr1 = arr[0].split(":");
							var arr2 = arr[1].split(":");

							if(arr2[0] < arr1[0] || (arr2[0] == arr1[0] && arr2[1] <= arr1[1]))
							{
								check = false;
								pastCheck = false;
							}
						}
					}
				}
				else
				{
					check = checkTime(checkVar);
				}

				//Ueberpruefung ob Leer und nicht gefuellt werden muss
				if(checkVar == "" && fill == false)
				{
					$("." + field).html("");
					submitCheck(submitName, field, true);
				}
				//Wenn doch gefuellt sein muss oder CheckVar nicht leer ist
				else
				{
					//Wenn gefuellt werden muss und CheckVar leer ist
					if(fill == true && checkVar == "")
					{
						if(message == true)
						{
							$("." + field).html("Die Uhrzeitangabe ist notwendig.");
						}
					}
					//Wenn Uhrzeit falsch ist
					else if(check == false && pastCheck == true && lengthCheck == true)
					{
						if(message == true)
						{
							$("." + field).html("Die Uhrzeiten sind ung&uuml;ltig.");
						}
					}
					//Wenn letztere Uhrzeit vor der ersten liegt
					else if(check == false && pastCheck == false && lengthCheck == true)
					{
						if(message == true)
						{
							$("." + field).html("Das letzte Uhrzeit liegt vor der ersten.");
						}
					}
					//Wenn zweite Uhrzeit fehlt
					else if(check == false&& pastCheck == true && lengthCheck == false)
					{
						if(message == true)
						{
							$("." + field).html("Die zweite Uhrzeit fehlt.");
						}
					}
					//Wenn alles ok ist
					else
					{
						$("." + field).html("");
						submitCheck(submitName, field, true);
					}
				}
			}
			
			function checkCheckedForm(field, message, submitName)
			{
				var field_temp = field;
				field = field + "Error";
				submitCheck(submitName, field, false);

				if (!$("input:radio[name=" + field_temp + "]:checked").val())
				{
					if(message == true)
					{
						$("." + field).html("Es muss eine Auswahl getätigt werden.");
					}
					submitCheck(submitName, field, false);
				}
				else
				{
					submitCheck(submitName, field, true);
				}
			}

			function checkMailForm(fill, field, message, submitName)
			{
				var checkVar = document.getElementById(field).value;
				
				field = field + "Error";

				submitCheck(submitName, field, false);

				//Ueberpruefung ob Leer und nicht gefuellt werden muss
				if(checkVar == "" && fill == false)
				{
					$("." + field).html("");
					submitCheck(submitName, field, true);
				}
				//Wenn doch gefuellt sein muss oder CheckVar nicht leer ist
				else
				{
					//Wenn gefuellt sein muss und checkVar leer ist
					if(checkVar == "" && fill == true)
					{
						if(message == true)
						{
							$("." + field).html("Eine E-Mail-Adresse muss angegeben werden.");
						}
					}
					//Wenn nicht ein @ oder ein . in checkVar ist
					else if(checkVar.search("@") == -1 || checkVar.search(".") == -1)
					{
						if(message == true)
						{
							$("." + field).html("Die eingegebene E-Mail-Adresse ist ung&uuml;ltig.");
						}
					}
					//Wenn alles ok
					else
					{
						var arr = checkVar.split("@");
						var arr2 = arr[1].split(".");

						//Wenn @ und . nicht an der richtigen Stelle ist
						if(arr.length != 2 || arr2.length < 2)
						{
							if(message == true)
							{
								$("." + field).html("Die eingegebene E-Mail-Adresse ist ung&uuml;ltig.");
							}
						}
						else
						{
							$("." + field).html("");
							submitCheck(submitName, field, true);
						}
					}
				}
			}

			function checkSelectionForm(field, message, submitName)
			{
				var checkVar = document.getElementById(field).value;
				
				field = field + "Error";

				submitCheck(submitName, field, false);

				if(checkVar == -1)
				{
					if(message == true)
					{
						$("." + field).html("Eine Auswahl muss get&auml;tigt werden.");
					}
					submitCheck(submitName, field, false);
				}
				else
				{
					$("." + field).html("");
					submitCheck(submitName, field, true);
				}
			}

			function hideCheck()
			{
				var pruef = 0;
');
		
		$keys = array();
		
		foreach($GLOBALS['hidings'] AS $submitName => $form)
		{
			foreach($form AS $field => $values)
			{
				echo ('				if(');
				
				foreach($values AS $value)
				{
					if(isset($value['before']))
					{
						echo ($value['before']);
					}
					
					if(!isset($value['key_addition']))
					{
						$value['key_addition'] = "";
					}
					
					echo ('$("#' . $value['key'] . $value['key_addition'] . '").val() ' . $value['operator'] . ' "' . $value['value'] . '"');
					
					if(array_search($value['key'], $keys) === false)
					{
						array_push($keys, $value['key']);
					}
						
					if(isset($value['after']))
					{
						echo (' ' . $value['after'] . ' ');
					}
				}
				
				if(!isset($value['field_addition']))
				{
					$value['field_addition'] = "";
				}
				else
				{
					$value['field_addition'] = "_" . $value['field_addition'];
				}
				
				echo (')
				{
					$("#' . $field . $value['field_addition'] . '").show();
					$("#' . $field . '_dis").show();
					pruef = 1;
');
				
				if(isset($GLOBALS['checks'][$submitName][$field]))
				{
					echo ('					' . str_replace("'", "\"", $GLOBALS['checks'][$submitName][$field]) . ';
');
				}
				
				echo ('				}
				else
				{
					$("#' . $field . $value['field_addition'] . '").hide();
					$("#' . $field . '_dis").hide();
');
				
				if(isset($GLOBALS['checks'][$submitName][$field]))
				{
					echo ('					submitCheck("' . $submitName . '", "' . $field . 'Error", true);
');
				}
				
				echo ('				}
					
');
			}
		}
		
		echo('
				if(pruef == 0)
				{
');
		
		if($submitName == "pt")
		{
			echo ('				$("#original_publish").hide();
				$("#publish").hide();
');
		}
		else
		{
			echo ('				document.getElementById(' . $submitName . ').disabled = true;
');
		}
		
		echo('				}
			}

');

		foreach($keys AS $key)
		{
			echo ('			$("#' . $key . '").change(function()
			{
				hideCheck();
			});
');
		}
		
		echo('
			
			var errors = new Array();
			var errorNumbers = new Array();

			function submitCheck(submitName, field, status)
			{
				var pruef = false;
				//Kontrolle alle (start)
				if(submitName == "all")
				{
');
		
		foreach($GLOBALS['checks'] as $checks)
		{
			foreach($checks as $check)
			{
				echo ('					' . $check . '
');
			}
		}
		
		echo ('
					hideCheck();
				}
				//Nach Formulareingabe
				else
				{
					//Nicht geeignet für zwei Formular-Buttons!!! Eventuell umprogrammieren!!!
					if(errorNumbers.indexOf(field) == -1)
					{
						errors.push(status);
						errorNumbers.push(field);
					}
					else
					{
						errors[errorNumbers.indexOf(field)] = status;
					}
					
					pruef = true;
					errors.forEach(function(value, index, ar)
					{
						if(value == false)
						{
							pruef = false;
						}
					});
				}
				
				if(pruef == false && submitName == "pt")
				{
					$("#original_publish").hide();
					$("#publish").hide();
				}
				else if(pruef == true && submitName == "pt")
				{
					$("#original_publish").show();
					$("#publish").show();
				}
				else if(pruef == false && submitName == "opt")
				{
					$("#submit").hide();
					$("#tf_option_SpeicherHinweis_dis").show();
				}
				else if(pruef == true && submitName == "opt")
				{
					$("#submit").show();
					$("#tf_option_SpeicherHinweis_dis").hide();
				}
				else if(pruef == false)
				{
					document.getElementById(submitName).disabled = true;
				}
				else if(pruef == true)
				{
					document.getElementById(submitName).disabled = false;
				}
			}
			
			function FormsCheck()
			{
				if(document.readyState != "complete") {
					window.setTimeout(FormsCheck, 100);
					return false;
				}
				
				submitCheck("all", null, null);
			}

			FormsCheck();		
');
		
		//Auswahl begrenzen
		//Vielleicht intelligenter???
		if(isset($GLOBALS['select_mult']))
		{
			foreach($GLOBALS['select_mult'] AS $key => $anzahl)
			{
				echo ('
			delete ' . $key . 'Auswahl;
			var ' . $key . 'Auswahl = [];
			function ' . $key . 'SelectCheck()
			{
				var anzahl = ' . $anzahl . ';
				var zaehler = 0;

				//Jedes anklicken speicherung
				$("#' . $key . ' option:selected").each(function(){
					' . $key . 'Auswahl[anzahl] = $(this).val();
					
					$("#' . $key . ' option[value=\'" + ' . $key . 'Auswahl[0] + "\']").prop("selected", false);

					zaehler = 0;
					while(zaehler != anzahl)
					{
						if(typeof ' . $key . 'Auswahl[zaehler+1] !== "undefined" && ' . $key . 'Auswahl[zaehler+1])
						{
							' . $key . 'Auswahl[zaehler] = ' . $key . 'Auswahl[zaehler+1];
						}
						zaehler++;
					}
					delete ' . $key . 'Auswahl[anzahl];
				});
				
				//selected aller bisher angeklickten
				' . $key . 'Auswahl.forEach(' . $key . 'Selection);
			}
			
			function ' . $key . 'Selection(elem)
			{
				$("#' . $key . ' option[value=\'" + elem + "\']").prop("selected", true);
			}
			
			$("#' . $key . '").change(function()
			{
				' . $key . 'SelectCheck();
			});
');
			}
		}
		
		echo ('
			
		</script>
');
	}
	
	function jsSelection($select, $name)
	{
		$fill = 'false';
		
		if(is_array($select))
		{
			foreach($select AS $check)
			{
				if($check == "Fill")
				{
					$fill = 'true';
				}
				else
				{
					$value = $check;
				}
			}
		}
		else
		{
			$value = $select;
		}
		
		if(!isset($GLOBALS['checks'][$this->submit_name]))
		{
			$GLOBALS['checks'][$this->submit_name] = array();
		}
		
		switch ($value)
		{
			case 'Number':
				$storage = "checkNumberForm(" . $fill . ", '" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkNumberForm(' . $fill . ', \'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			case 'Fill':
				$storage = "checkFillForm('" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkFillForm(\'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			case 'Date':
				$storage = "checkDateForm(" . $fill . ", false, '" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkDateForm(' . $fill . ', false, \'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			case 'DateWY':
				$storage = "checkDateWYForm(" . $fill . ", '" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkDateWYForm(' . $fill . ', \'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			case 'DoubleDate':
				$storage = "checkDateForm(" . $fill . ", true, '" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkDateForm(' . $fill . ', true, \'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			case 'Time':
				$storage = "checkTimeForm(" . $fill . ", false, '" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkTimeForm(' . $fill . ', false, \'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			case 'DoubleTime':
				$storage = "checkTimeForm(" . $fill . ", true, '" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkTimeForm(' . $fill . ', true, \'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			case 'Mail':
				$storage = "checkMailForm(" . $fill . ", '" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkMailForm(' . $fill . ', \'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			case 'Checked':
				$storage = "checkCheckedForm('" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkCheckedForm(\'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			case 'Selection':
				$storage = "checkSelectionForm('" . $name . "', false, '" . $this->submit_name . "')";
				$GLOBALS['checks'][$this->submit_name][$name] = $storage;
				return ' onchange="checkSelectionForm(\'' . $name . '\', true, \'' . $this->submit_name . '\')"';
			default:
				return '';
		}
	}
	
	function jsHiding($name, $values)
	{
		if(!isset($GLOBALS['hidings'][$this->submit_name][$name]))
		{
			$GLOBALS['hidings'][$this->submit_name][$name] = array();
		}
		
		foreach($values AS $value)
		{
			$value['key'] = $this->prefixChange($value['key']);
			array_push($GLOBALS['hidings'][$this->submit_name][$name], $value);
		}
	}

	/**
	 * Input Formular-Element, Vorlage für weitere Formular-Elemente
	 * 
	 * @param array Parameterübergabe<br>
	 * - art						: Art des Inputfeldes (muss immer gesetzt sein!)<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - size		(Standard 10)	: Feldgröße<br>
	 * - value		(Standard "")	: Vorbelegung des Feldes<br>
	 * - checked	(Standard false): Bei Radio und Check: Vorselektierung<br>
	 * - checking	(Standard "")	: Angabe des Pflichtinhaltes
	 */
	function input($arr)
	{
		//Variablen Befüllung
		if($arr["art"] != "submit")
		{
			$arr["name"] = $this->prefixChange($arr["name"]);
		}
		
		if(!isset($arr["size"]))
		{
			$arr["size"] = 10;
		}
		
		if(!isset($arr["value"]))
		{
			$arr["value"] = "";
		}

		if(!isset($arr["checked"]))
		{
			$arr["checked"] = "";
		}
		
		if(!isset($arr["checking"]))
		{
			$arr["checking"] = "";
		}
		
		
		
		//Formularaufbereitung
		if(!isset($arr['sel_beschreibung']))
		{
			$arr['sel_beschreibung'] = "";
			$id = $arr['name'];
		}
		else
		{
			$id = $arr['name'] . "_" . $arr['value'];
		}
		
		if(is_array($arr["value"]))
		{
			$arr["value"] = $arr["value"][$arr["name"]];
			$arr["value"] = ' value="' . $arr["value"] . '"';
		}
		elseif($arr["value"] != "")
		{
			$arr["value"] = ' value="' . $arr["value"] . '"';
		}

		if($arr["art"] != "checkbox" && $arr["art"] != "radio")
		{
			$arr["size"] = ' size="' . $arr["size"] . '"';
		}
		else
		{
			$arr["size"] = '';
		}
		
		if($arr["checked"] == true && ($arr["art"] == "checkbox" || $arr["art"] == "radio"))
		{
			$arr["checked"] = " checked";
		}

		if(isset($arr["checking"]) && $arr["checking"] != "")
		{
			$arr["checking"] = $this->jsSelection($arr["checking"], $arr["name"]);
		}
		
		if(isset($arr['hidings']))
		{
			$this->jsHiding($arr['name'], $arr['hidings']);
		}
		
		
		if(isset($arr['class']))
		{
			$arr['class'] = ' class="button"';
		}
		else
		{
			$arr['class'] = "";
		}
		
		//Anzeige Formular
		echo ('<input type="' . $arr["art"] . '" id="' . $id . '" name="' . $arr["name"] . '"' . $arr["size"] . $arr["value"] . $arr["checked"] . $arr["checking"] . $arr['class'] . '> ' . $arr["sel_beschreibung"]);
		
		if(isset($arr['checking']) && $arr['checking'] != "" && strlen($arr['checking']) == strlen(str_replace("checkChecked", "", $arr['checking'])))
		{
			$this->errorShow($arr['name']);
		}
	}

	/**
	 * Text-Formular-Element (einzeilig)
	 * 
	 * @param array Parameterübergabe
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - size						: Formulargröße<br>
	 * - value						: Vorbelegung des Feldes<br>
	 * - checking					: Angabe des Pflichtinhaltes
	 */
	function text($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "text";
		
		//Weitergabe
		$this->input($arr);
	}

	/**
	 * HTML5-Element: E-Mail-Formular-Element
	 * 
	 * @param array Parameterübergabe<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - size						: Formulargröße<br>
	 * - value						: Vorbelegung des Feldes<br>
	 * - checking					: Angabe des Pflichtinhaltes
	 */
	function email($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "email";
		
		//Weitergabe
		$this->input($arr);
	}

	/**
	 * Password-Formular-Element
	 * 
	 * @param array Parameterübergabe<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - size						: Formulargröße<br>
	 * - value						: Vorbelegung des Feldes<br>
	 * - checking					: Angabe des Pflichtinhaltes
	 */
	function password($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "password";
		
		//Weitergabe
		$this->input($arr);
	}

	/**
	 * Verstecktes-Formular-Element
	 * 
	 * @param array Parameterübergabe<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - value						: Vorbelegung des Feldes
	 */
	function hidden($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "hidden";
		
		//Weitergabe
		$this->input($arr);
	}

	/**
	 * Submit-Button-Element
	 * 
	 * @param array Parameterübergabe<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - value						: Vorbelegung des Feldes
	 */
	function submit($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "submit";
		if(!isset($arr['name']))
		{
			$arr['name'] = $this->submit_name;
		}

		if(!isset($arr['value']))
		{
			$arr['value'] = $this->submit_value;
		}
		
		$arr['class'] = "button";
		
		//Weitergabe
		$this->input($arr);
	}

	/**
	 * Anzeige von Check- oder Radiobox-Element
	 * 
	 * @param array Parameterübergabe<br>
	 * - art						: Art des Auswahlfeldes (Radio oder Checkbox)<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - values						: Auswahlmöglichkeiten<br>
	 * - checked					: Vorselektierungen
	 */
	function inputSelection($arr)
	{
		//Variablen Zwischenspeicherung
		$checked = $arr['checked'];
		
		//Weitergabe
		$erstes = false;
		//Je Value ein Element
		foreach ($arr['values'] as $id => $value)
		{
			if($erstes === false)
			{
				$erstes = true;
			}
			else
			{
				echo ('<br>
');
			}

			//Variablen Aufbereitung
			$arr['checked'] = false;
			if((is_array($checked) && isset($checked[$id])) || $checked == $id)
			{
				$arr['checked'] = true;
			}
			
			$arr['value'] = $id;

			$arr['sel_beschreibung'] = $value;
			
			//Formular Anzeige
			$this->input($arr);
		}
			
		if(isset($arr['checking']) && $arr['checking'] == "Checked")
		{
			$this->errorShow($this->prefixChange($arr['name']));
		}
	}

	/**
	 * Checkbox-Formular-Element
	 * 
	 * @param array Parameterübergabe<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - values						: Auswahlmöglichkeiten<br>
	 * - checked					: Vorselektierungen
	 */
	function check($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "checkbox";
		
		//Weitergabe
		$this->inputSelection($arr);
	}

	/**
	 * Radiobutton-Formular-Element
	 * 
	 * @param array Parameterübergabe<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - values						: Auswahlmöglichkeiten<br>
	 * - checked					: Vorselektierungen
	 */
	function radio($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "radio";
		
		//Weitergabe
		$this->inputSelection($arr);
	}

	/**
	 * Textarea-Element (Mehrzeilig)
	 * 
	 * @param array Parameterübergabe<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - value						: Vorbelegung des Formulars<br>
	 * - cols						: Spaltengröße des Formulars<br>
	 * - rows						: Zeilengröße des Formulars<br>
	 * - checking					: Angabe des Pflichtinhaltes
	 */
	function textarea($arr)
	{
		//Variablen Befüllung
		$arr['name'] = $this->prefixChange($arr['name']);
		
		if(is_array($arr['value']))
		{
			$arr['value'] = $arr['value'][$arr['name']];
		}
		
		if(isset($arr['checking']) && $arr['checking'] != "")
		{
			$arr['checking'] = $this->jsSelection($arr['checking'], $arr['name']);
		}
			
		if(isset($arr['hidings']))
		{
			$this->jsHiding($arr['name'], $arr['hidings']);
		}
		
		//Formular Anzeige
		echo ('<textarea id="' . $arr['name'] . '" name="' . $arr['name'] . '" cols="' . $arr['cols'] . '" rows="' . $arr['rows'] . '"' . $arr['checking'] . '>' . $arr['value'] . '</textarea>
');
		
		if(isset($arr['checking']) && $arr['checking'] != "")
		{
			$this->errorShow($arr['name']);
		}
	}

	/**
	 * Auswahl-Element
	 * 
	 * @param array Parameterübergabe<br>
	 * - name							: Feldname (muss immer gesetzt sein!)<br>
	 * - values							: Auswahlmöglichkeiten<br>
	 * - multiple	(Standard: false)	: Angabe ob mehrere Auswahlmöglichkeiten ausgewählt werden könnnen<br>
	 * - size		(Standard: 1)		: Größe des Formularfeldes<br>
	 * - selected	(Standard: null)		: Vorselektierung<br>
	 * - opt_group	(Standard: false)	: Angabe ob Auswahlmöglichkeiten in Option-Groups angezeigt werden<br>
	 * - erste		(Standard: false)	: Angabe ob ein erstes Formularfeld gesetzt wird<br>
	 * - checking						: Angabe des Pflichtinhaltes
	 */
	function select($arr)
	{
		//Variablen Befüllung
		$arr['name'] = $this->prefixChange($arr['name']);
		
		if(isset($arr['multiple']) && ($arr['multiple'] === true || is_numeric($arr['multiple'])))
		{
			if(is_numeric($arr['multiple']))
			{
				$GLOBALS['select_mult'][$arr['name']] = $arr['multiple'];
			}
			
			$arr['multiple_name'] = "[]";
			$arr['multiple'] = " multiple";
		}
		else
		{
			$arr['multiple_name'] = "";
			$arr['multiple'] = "";
		}
		
		if(!isset($arr['size']))
		{
			$arr['size'] = 1;
		}
		
		if(isset($arr['selected']) && is_array($arr['selected']))
		{
			$arr['selected'] = $arr['selected']['value'];
		}
		elseif(!isset($arr['selected'])) {
			$arr['selected'] = array();
		}
		
		if(isset($arr['checking']) && $arr['checking'] != "")
		{
			$arr['checking'] = $this->jsSelection($arr['checking'], $arr['name']);
		}
		else
		{
			$arr['checking'] = "";
		}
		
		if(isset($arr['hidings']))
		{
			$this->jsHiding($arr['name'], $arr['hidings']);
		}

		//Formularanzeige
		echo ('<select id="' . $arr['name'] . '" name="' . $arr['name']. $arr['multiple_name'] . '" size="' . $arr['size'] . '"' . $arr['multiple'] . $arr['checking'] . '>
');

		//Auswahlmöglichkeiten
		if(isset($arr['erste']) && $arr['erste'] === true)
		{
			$this->option(-1, "--- Bitte ausw&auml;hlen ---", -1);
		}

		if(isset($arr['values']))
		{
			//Prüf, ob Option-Group angezeigt werden soll
			if(isset($arr['opt_group']) && $arr['opt_group'] === true) 
			{
				//Anzeige erste Option-Group
				$this->option_group($arr['values'], $arr['selected']);
			}
			else
			{
				foreach ($arr['values'] as $id => $value)
				{
					$this->option($id, $value, $arr['selected']);
				}
			}
		}

		echo ('</select>
');

		if(isset($arr['checking']) && $arr['checking'] != "")
		{
			$this->errorShow($arr['name']);
		}
	}
	
	/*
	 * Option-Group mit Option Anzeige
	 * 
	 * @param string $values Anzeigewerte
	 * @param string $selected Vorbelegung
	 * @param integer $stage Angabe der Stufe (Option Group)
	 */
	function option_group($values, $selected, $stage = 0)
	{
		$stage++;
		$prefix = str_repeat("&nbsp;", $stage*4);
		
		foreach ($values as $key_1 => $value_1) {
			echo (str_repeat("	", $stage) . '	<optgroup label="' . $prefix . $key_1 . '">
');
				
			foreach ($value_1 as $key_2 => $value_2)
			{
				if(is_array($value_2))
				{
					$this->option_group($value_1, $selected, $stage);
					break;
				}
				else
				{
					$this->option($key_2, $value_2, $selected, $stage);
				}
			}
			
			echo (str_repeat("	", $stage) . '	</optgroup>
');
		}
	}

	/**
	 * Option-Element für Select
	 * 
	 * @param integer $id Übergabewert für Option
	 * @param string $value Anzeigewert in Auswahl
	 * @param string $selected Vorbelegung
	 * @param integer $stage Angabe der Stufe (Option Group)
	 */
	function option($id, $value, $selected, $stage = -1)
	{
		$stage++;
		$prefix = "";
		if($stage != 0)
		{
			$prefix = str_repeat("&nbsp;", (($stage*4)+2));
		}
		
		if(is_array($selected) && isset($selected[$id]))
		{
			$selected_uebergabe = " selected";
		}
		elseif(!is_array($selected) && $selected == $id)
		{
			$selected_uebergabe = " selected";
		}
		else
		{
			$selected_uebergabe = "";
		}

		echo (str_repeat("	", $stage) . '		<option value="' . $id . '"' . $selected_uebergabe . '>' . $prefix . $value . '</option>
');
	}


	//Tabellen Funktion für alle Felder-Arten
	/**
	 * Anzeige aller Formularelemente in Tabelle
	 * 
	 * @param boolean $spalte Information, ob Tabelle in Spalten organisiert wird
	 * @param string $beschreibung Anzeige der Beschreibung des Formularelements
	 * @param array $array Information zum Formular-Element
	 */
	function td($arr)
	{
		echo ('		<tr id="' . $this->prefixChange($arr['name']) . '_dis">
');

		if(!isset($arr['spalte']) || $arr['spalte'] === true)
		{
			echo ('			<td width="30%" valign="top">' . $arr['beschreibung'] . '</td>
		<td width="70%">
');
		}
		else 
		{
			echo ('			<td width="100%" colspan="2">
');
		}

		switch($arr['art']) 
		{
			case 'text':
				$this->text($arr);
				break;
			case 'email':
				$this->email($arr);
				break;
			case 'password':
				$this->password($arr);
				break;
			case 'submit':
				$this->submit($arr);
				break;
			case 'check':
				$this->check($arr);
				break;
			case 'radio':
				$this->radio($arr);
				break;
			case 'textarea':
				$this->textarea($arr);
				break;
			case 'select':
				$this->select($arr);

				break;
			case 'anzeige':
				echo ('				' . $arr['anzeige'] . '
');
				break;
			default:
				echo ('				' . $arr['beschreibung'] . '
');
		}

		echo ('			</td>
	</tr>
');			
	}

	/**
	 * Table Text-Element (einzeilig)
	 * 
	 * @param array $arr Parameterübergabe<br>
	 * - beschreibung				: Beschreibung des Formularfeldes<br>
	 * - name						: Name des Formularfeldes (zwingend notwendig!)<br>
	 * - size						: Größe des Formularfeldes<br>
	 * - value						: Vorbelegung des Feldes<br>
	 * - checking					: Angabe der Pflichteingabe
	 */
	function td_text($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "text";
		
		//Weitergabe
		$this->td($arr);
	}

	/**
	 * HTML5-Element: E-Mail-Element
	 * 
	 *  @param array $arr Parameterübergabe<br>
	 * - beschreibung				: Beschreibung des Formularfeldes<br>
	 * - name						: Name des Formularfeldes (zwingend notwendig!)<br>
	 * - size						: Größe des Formularfeldes<br>
	 * - value						: Vorbelegung des Feldes<br>
	 * - checking					: Angabe der Pflichteingabe
	 */
	function td_email($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "email";
		
		//Weitergabe
		$this->td($arr);
	}

	/**
	 * Password-Formular-Element
	 * 
	 * @param array $arr Parameterübergabe<br>
	 * - beschreibung				: Beschreibung des Formularfeldes<br>
	 * - name						: Name des Formularfeldes (zwingend notwendig!)<br>
	 * - size						: Größe des Formularfeldes<br>
	 * - value						: Vorbelegung des Feldes<br>
	 * - checking					: Angabe der Pflichteingabe
	 */
	function td_password($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "password";
		
		//Weitergabe
		$this->td($arr);
	}

	/**
	 * Checkbox
	 * 
	 * @param array $arr Parameterübergabe<br>
	 * - beschreibung				: Beschreibung des Formularfeldes<br>
	 * - name						: Name des Formularfeldes (zwingend notwendig!)<br>
	 * - values						: Auswahlmöglichkeiten<br>
	 * - checked					: Vorselektierte Auswahlmöglichkeiten
	 */
	function td_check($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "check";
		
		//Weitergabe
		$this->td($arr);
	}

	/**
	 * Radiobox
	 * 
	 * @param array $arr Parameterübergabe<br>
	 * - beschreibung				: Beschreibung des Formularfeldes<br>
	 * - name						: Name des Formularfeldes (zwingend notwendig!)<br>
	 * - values						: Auswahlmöglichkeiten<br>
	 * - checked					: Vorselektierte Auswahlmöglichkeiten
	 */
	function td_radio($arr)
	{
		//Variablen Befüllung
		$arr['art'] = "radio";
		
		//Weitergabe
		$this->td($arr);
	}

	/**
	 * Submit-Button
	 * 
	 * @param array Parameterübergabe<br>
	 * - name						: Feldname (muss immer gesetzt sein!)<br>
	 * - value						: Vorbelegung des Feldes
	 */
	function td_submit($arr = "")
	{
		//Variablen Befüllung
		$arr['art'] = "submit";
		$arr['name'] = $this->submit_name;
		$arr['beschreibung'] = "";
		
		//Weitergabe
		$this->td($arr);
	}

	/**
	 * Texteingabe-Element (mehrzeilig)
	 * 
	 * @param array $arr Parameterübergabe<br>
	 * - anzeige		(Standard: "neben")	: Anzeige der Beschreibung
	 * - beschreibung						: Beschreibung des Formularfeldes<br>
	 * - name								: Name des Formularfeldes (zwingend notwendig!)<br>
	 * - cols								: Anzahl der Spalten
	 * - rows								: Anzahl der Zeilen
	 * - value								: Vorbelegung des Feldes
	 * - checking							: Angabe der Pflichteingabe
	 */
	function td_textarea($arr)
	{
		//Anzeige Beschreibung über Formularfeld
		if($arr['anzeige'] === "oben")
		{
			$arr['art'] = "anzeige";
			$arr['spalte'] = false;
			$this->td($arr); 
		}

		//Weitergabe
		$arr['art'] = "textarea";
		$this->td($arr);
	}

	/**
	 * Select-Element
	 * 
	 * @param array $arr Parameterübergabe<br>
	 * - name							: Feldname (muss immer gesetzt sein!)<br>
	 * - values							: Auswahlmöglichkeiten<br>
	 * - multiple	(Standard: false)	: Angabe ob mehrere Auswahlmöglichkeiten ausgewählt werden könnnen<br>
	 * - size		(Standard: 1)		: Größe des Formularfeldes<br>
	 * - selected	(Standard: null)		: Vorselektierung<br>
	 * - opt_group	(Standard: false)	: Angabe ob Auswahlmöglichkeiten in Option-Groups angezeigt werden<br>
	 * - erste		(Standard: false)	: Angabe ob ein erstes Formularfeld gesetzt wird<br>
	 * - checking						: Angabe des Pflichtinhaltes
	 */
	function td_select($arr) 
	{
		//Variablen Befüllung
		$arr['art'] = "select";

		//Weitergabe
		$this->td($arr);
	}







	//Hilfsmethoden
	/**
	 * Hinzufügen des Prefixes zum Namen
	 * 
	 * @param string $name Formular-Name
	 * @return string Ausgabe des neuen Namens
	 */
	public function prefixChange($name)
	{
		if($this->prefix != "")
		{
			$name = $this->prefix . "_" . $name;
		}

		return $name;
	}






	
	//Prüfungen
	/**
	 * Prüfung, ob POST-Wert gesetzt ist
	 * 
	 * @param string $name Index des Formularwertes
	 * @return boolean Zustandsübergabe
	 */
	public static function issetTest($name)
	{
		if(!isset($_POST[$name])) 
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Prüfung, ob POST-Wert gesetzt ist und nicht leer ist
	 * 
	 * @param string $name Index des Formularwertes
	 * @return boolean Zustandsübergabe
	 */
	public static function contentTest($name)
	{
		if(self::issetTest($name) === false || $_POST[$name] === "") 
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Prüfung, ob POST-Wert gesetzt ist und nicht "Bitte auswählen..." ist
	 * 
	 * @param string $name Index des Formularwertes
	 * @return boolean Zustandsübergabe
	 */
	public static function selectionTest($name)
	{
		if(self::issetTest($name) === false || $_POST[$name] === "0") 
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Prüfung, ob POST-Wert gesetzt ist und Zahl ist
	 * 
	 * @param string $name Index des Formularwertes
	 * @return boolean Zustandsübergabe
	 */
	public static function numericTest($name)
	{
		if(self::issetTest($name) === false)
		{
			return false;
		}
		else 
		{
			$test = str_replace(",", ".", $_POST[$name]);
			if(is_numeric($test))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Prüfung, ob POST-Wert gesetzt ist und Boolean ist
	 * 
	 * @param string $name Index des Formularwertes
	 * @return boolean Zustandsübergabe
	 */
	public static function boolTest($name)
	{
		if(self::issetTest($name) === false) {
			$_POST[$name] = false;
		}
		elseif($_POST[$name] == 1)
		{
			$_POST[$name] = true;
		}

		if(is_bool($_POST[$name]) === false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Prüfung, ob POST-Wert gesetzt ist und Datum existiert
	 * 
	 * @param string $name Index des Formularwertes
	 * @return boolean Zustandsübergabe
	 */
	public static function dateTest($name)
	{
		if(self::contentTest($name) === true)
		{
			$explode = explode(".", $_POST[$name]);
			if(!isset($explode[2]) || $explode[2] === "")
			{
				$explode[2] = date('Y');
			}

			if(checkdate($explode[1], $explode[0], $explode[2]) === true)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Prüfung, ob POST-Wert gesetzt ist und E-Mail ist
	 * 
	 * @param string $name Index des Formularwertes
	 * @return boolean Zustandsübergabe
	 */
	public static function emailTest($name)
	{
		if(self::issetTest($name) === true)
		{
			$explode = explode("@", $_POST[$name]);
			$anzahl = count($explode);
			if($anzahl === 2)
			{
				$explode[1] = explode(".", $explode[1]);
				$anzahl = count($explode[1]);
				if($anzahl > 1)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Prüfung, ob POST-Wert gesetzt und Uhrzeit ist
	 * 
	 * @param string $name Index des Formularwertes
	 * @return boolean Zustandsübergabe
	 */
	public static function timeTest($name)
	{
		if(self::issetTest($name) === true)
		{
			$explode = explode(":", $_POST[$name]);
			if(count($explode) == 2) 
			{
				if(is_numeric($explode[0]) && is_numeric($explode[1]) && $explode[0] < 25 && $explode[0] > -1 && $explode[1] < 61 && $explode[1] > -1)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Wenn für aktuelles Formular-Element ein Fehler vorliegt, zeige ihn an.
	 * 
	 * @param array $name Fehler zu Formular Elemente
	 */
	function errorShow($name)
	{
		echo ('<br>
			<div class="' . $name . 'Error">
');
		
		if(isset($this->errors[$name]))
		{
			echo ($this->errors[$name]);
		}
		
		echo ('			</div>
');
	}
}

?>