<?php

/**
 * Description of Einstellungen
 *
 * @author KWM
 */
class Einstellungen 
{
	//*************************************************************************
	//*********************************** MENU ********************************
	//*************************************************************************
	public static function EinstellungenMenu()
	{
		add_submenu_page("options-general.php", "TransFinanz", "TransFinanz", 1, "transfinanz", array('Einstellungen', 'maske'));
	}
	
	//*************************************************************************
	//***************************** OPTION REGISTER ***************************
	//*************************************************************************
	public static function register()
	{
		add_option("tf_haushalts_jahr", "1.1.");
		add_option("tf_plan_einsicht", "Geheim");
		add_option("tf_antraege_stellen", "Keine_r");
		add_option("tf_antraege_spenden", "Nein");
		add_option("tf_quittungs_template", "[n]");
		add_option("tf_erfassung_start", -1);
		add_option("tf_import", "-");
		register_setting("tf_option", "tf_haushalts_jahr");
		register_setting("tf_option", "tf_plan_einsicht");
		register_setting("tf_option", "tf_antraege_stellen");
		register_setting("tf_option", "tf_antraege_spenden");
		register_setting("tf_option", "tf_quittungs_template");
		register_setting("tf_option", "tf_erfassung_start");
		register_setting("tf_option", "tf_import");
	}
	
	//*************************************************************************
	//********************************* MASKEN ********************************
	//*************************************************************************
	public static function maske()
	{
		echo ('			<div class="wrap">
				<h1>TransFinanz Einstellungen</h1>
				Ein Wordpress-Plugin von Kay Wilhelm Mähler (KWM::Sources). Mehr auf <a href="http://www.kay-wilhelm.de" target="_blank">www.kay-wilhelm.de</a>
				<br><br><br>
				<form action="options.php" method="post">
');
		$form = new TF_Form(array(
			'action'		=> 'options.php',
			'form'			=> false,
			'table'			=> true,
			'submit'		=> 'opt',
			'prefix'		=> 'tf'
		));
		
		settings_fields("tf_option");
		
		$form->td_text(array(
			'beschreibung'		=> 'Beginn des Haushaltsjahres (TT.MM.):',
			'name'				=> 'haushalts_jahr',
			'value'				=> get_option('tf_haushalts_jahr'),
			'checking'			=> array('Fill', 'DateWY')
		));
		$form->td(array(
			'name'				=> 'Hinweis',
			'spalte'			=> true,
			'art'				=> 'anzeige',
			'anzeige'			=> '<b>Beachte: Die Änderung des Beginns des Haushaltsjahres kann nachwirkende Folgen haben! Es empfiehlt sich nur eine Einstellung zu Beginn der Nutzung der TransFinanz.</b><br><br>'
		));
		
		$zaehler = date('Y') - 2009;
		$zaehler_2 = 0;
		$values = array();
		while(($zaehler+1) != $zaehler_2)
		{
			$values[2009 + $zaehler_2] = 2009 + $zaehler_2;
			$zaehler_2++;
		}
		$form->td_select(array(
			'beschreibung'		=> 'Beginn der Erfassung:',
			'name'				=> 'erfassung_start',
			'values'			=> $values,
			'selected'			=> get_option('tf_erfassung_start'),
			'erste'				=> true,
			'checking'			=> 'Selection'
		));
		
		$form->td_radio(array(
			'beschreibung'		=> 'Planeinsicht:',
			'name'				=> 'plan_einsicht',
			'values'			=> array(
				'Öffentlich'		=> 'Vollkommen Öffentlich',
				'Registriert'		=> 'Nur Registrierte Nutzer*innen',
				'Geheim'			=> 'Nur für\'s Backend'
			),
			'checked'			=> get_option('tf_plan_einsicht'),
			'checking'			=> 'Checked'
		));
		
		$form->td_radio(array(
			'beschreibung'		=> 'Anstragsstellung:',
			'name'				=> 'antraege_stellen',
			'values'			=> array(
				'Alle'				=> 'Alle können Anträge stellen',
				'Registriert'		=> 'Nur Registrierte Nutzer*innen',
				'Keine_r'			=> 'Niemand kann Anträge stellen'
			),
			'checked'			=> get_option('tf_antraege_stellen'),
			'checking'			=> 'Checked'
		));
		
		$form->td_radio(array(
			'beschreibung'		=> 'Verbuchung von Spenden aus Anträgen:',
			'name'				=> 'antraege_spenden',
			'values'			=> array(
				'Nein'				=> 'Nein',
				'Ja'				=> 'Ja'
			),
			'checked'			=> get_option('tf_antraege_spenden'),
			'checking'			=> 'Checked'
		));
		
		$form->td_text(array(
			'beschreibung'		=> 'Quittungstemplate:',
			'name'				=> 'quittungs_template',
			'value'				=> get_option('tf_quittungs_template'),
			'checking'			=> 'Fill'
		));
		$form->td(array(
			'name'				=> 'Quittungshinweis',
			'spalte'			=> true,
			'art'				=> 'anzeige',
			'anzeige'			=> 'Folgende Elemente können im Quittungstemplate genutzt werden:<br>[jj] - Zweistelliges Haushaltsjahr<br>[jjjj]- Vierstelliges Haushaltsjahr<br>[n] - Laufende Nummer<br>[nj] - Laufende Nummer im Haushaltsjahr<br>[ne] - Laufende Nummer Einnahmen<br>[nje] - Laufende Nummer Einnahmen im Haushaltsjahr<br>[na] - Laufende Nummer Ausgaben<br>[nja] - Laufende Nummer Ausgaben im Haushaltsjahr<br><br>'
		));
		
		$form->td(array(
			'name'				=> 'ImportHinweis',
			'spalte'			=> true,
			'art'				=> 'anzeige',
			'anzeige'			=> 'Wenn du die TransFinanz schon vor dem Update 1.0.0 genutzt hast, kannst du hier deine alten Daten importieren.<br><b>VORSICHT:</b> Auf Grund eines schwerwiegenden, fachlichen Fehlers werden SOLL-Haushaltspläne <b>NICHT</b> mit in die neue TransFinanz übertragen. Alle SOLL-Haushaltspläne werden gelöscht und müssen deshalb manuell gesichert werden.<br>Bedenke, dass dies einige Zeit dauern kann.'
		));
		$form->td_submit(array(
			'name'				=> 'import',
			'value'				=> 'Alt-TransFinanz-Daten konvertieren'
		));
		
		$form->td(array(
			'name'				=> 'SpeicherHinweis',
			'spalte'			=> true,
			'art'				=> 'anzeige',
			'anzeige'			=> '<br><br>Die Einstellungen können erst gespeichert werden, wenn alle Einstellungen richtig getätigt wurden.'
		));
		
		TF_Form::jsHead();
		TF_Form::jsScript();
		
		unset($form);
		
		submit_button();
		
		echo ('					</form>
			</div>
');
	}
	
	public static function errorMessage()
	{
		global $err_text;
		
		if(get_option('tf_erfassung_start') == -1)
		{
			$err_text = $err_text . '				<h3>Du hast die TransFinanz-Einstellungen noch nicht getätigt!</h3>
				
				Klicke <a href="options-general.php?page=transfinanz">hier</a>, um die notwendigen Einstellungen zu vervollständigen.';
		}
		
		if($err_text != "")
		{
			echo ('			<div id="message" class="error">
				' . $err_text . '
			</div>
');
		}
	}
	
	public static function haushaltsjahrAusgabe($time = 0, $date = "")
	{
		if($time == 0)
		{
			$time = time();
		}
		
		if($date == "")
		{
			$haushaltsjahr_start = explode(".", get_option("tf_haushalts_jahr"));
		}
		else
		{
			$haushaltsjahr_start = explode(".", $date);
		}
		
		if($haushaltsjahr_start[1] < date('n', $time) || ($haushaltsjahr_start[1] == date('n', $time) && $haushaltsjahr_start[0] < date('j', $time)))
		{
			return date('Y', $time);
		}
		else
		{
			return (date('Y', $time) - 1);
		}
	}
}
