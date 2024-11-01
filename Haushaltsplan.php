<?php

/**
 * Description of Haushaltsplan
 *
 * @author KWM
 */
class Haushaltsplan extends Postlist
{
	//*************************************************************************
	//********************************* OBJEKT ********************************
	//*************************************************************************
	public $id;
	public $name;
	public $jahr;
	public $aufloesung_rueckstellung;
	public $aufbau;
	public $einnahmen;
	public $ausgaben;
	public $aufbau_rueckstellung;
	public $aufloesung;
	
	public function __construct($id) 
	{
		if(isset($id))
		{
			$this->id = $id;
			$post = get_post($id);
		
			$this->name = $post->post_title;
		}
		
		if(is_numeric($id) && isset($id))
		{
			$this->jahr = get_post_meta($id, "tf_plan_jahr", true);
			
			//Abruf aller Metadaten
			global $wpdb;
			
			$sql = "SELECT meta_key FROM " . $wpdb->postmeta . " WHERE post_id = " . $id . " AND meta_key LIKE 'tf_plan_aufloesung_rs_%' ORDER BY meta_id ASC";
			$results = $wpdb->get_results($sql);
			foreach($results AS $result)
			{
				$meta_key = $result->meta_key;
				$meta_key_ex = explode("_", $meta_key);
				$gq_id = count($meta_key_ex) - 1;
				
				$this->aufloesung_rueckstellung[$gq_id] = strtodouble(get_post_meta($id, $meta_key, true));
			}
			
			$sql = "SELECT meta_key FROM " . $wpdb->postmeta . " WHERE post_id = " . $id . " AND meta_key LIKE 'tf_plan_aufbau_%' ORDER BY meta_id ASC";
			$results = $wpdb->get_results($sql);
			foreach($results AS $result)
			{
				$meta_key = $result->meta_key;
				$meta_key_ex = explode("_", $meta_key);
				$gq_id = count($meta_key_ex) - 1;
				
				$this->aufbau[$gq_id] = strtodouble(get_post_meta($id, $meta_key, true));
			}
			
			$sql = "SELECT meta_key FROM " . $wpdb->postmeta . " WHERE post_id = " . $id . " AND meta_key LIKE 'tf_plan_einnahme_%' ORDER BY meta_id ASC";
			$results = $wpdb->get_results($sql);
			foreach($results AS $result)
			{
				$meta_key = $result->meta_key;
				$meta_key_ex = explode("_", $meta_key);
				$knt_id = count($meta_key_ex) - 1;
				
				$this->einnahmen[$knt_id] = strtodouble(get_post_meta($id, $meta_key, true));
			}
			
			$sql = "SELECT meta_key FROM " . $wpdb->postmeta . " WHERE post_id = " . $id . " AND meta_key LIKE 'tf_plan_ausgabe_%' ORDER BY meta_id ASC";
			$results = $wpdb->get_results($sql);
			foreach($results AS $result)
			{
				$meta_key = $result->meta_key;
				$meta_key_ex = explode("_", $meta_key);
				$knt_id = count($meta_key_ex) - 1;
				
				$this->ausgaben[$knt_id] = strtodouble(get_post_meta($id, $meta_key, true));
			}
			
			$sql = "SELECT meta_key FROM " . $wpdb->postmeta . " WHERE post_id = " . $id . " AND meta_key LIKE 'tf_plan_aufloesung_%' ORDER BY meta_id ASC";
			$results = $wpdb->get_results($sql);
			foreach($results AS $result)
			{
				$meta_key = $result->meta_key;
				$meta_key_ex = explode("_", $meta_key);
				$gq_id = count($meta_key_ex) - 1;
				
				$this->aufloesung[$gq_id] = strtodouble(get_post_meta($id, $meta_key, true));
			}
			
			$sql = "SELECT meta_key FROM " . $wpdb->postmeta . " WHERE post_id = " . $id . " AND meta_key LIKE 'tf_plan_aufbau_rs_%' ORDER BY meta_id ASC";
			$results = $wpdb->get_results($sql);
			foreach($results AS $result)
			{
				$meta_key = $result->meta_key;
				$meta_key_ex = explode("_", $meta_key);
				$gq_id = count($meta_key_ex) - 1;
				
				$this->aufbau_rueckstellung[$gq_id] = strtodouble(get_post_meta($id, $meta_key, true));
			}
		}
		elseif(isset($id))
		{
			$this->name = $id;
			
			$this->jahr = substr($id, -4, 4);
			
			$start = get_option('tf_haushalts_jahr') . $this->jahr;
			$ende = get_option('tf_haushalts_jahr') . ($this->jahr+1);
			
			$start_timestamp = datetots($start);
			$ende_timestamp = datetots($ende);
			
			$gq_arten = Geldquelle::getAll();
			
			foreach($gq_arten AS $key => $gqs)
			{
				if($key == "Konten" || $key == "Kassen")
				{
					foreach($gqs AS $gq_id => $name)
					{
						$gq = new Geldquelle($gq_id, false);
						if($this->jahr == get_option('tf_erfassung_start'))
						{
							$this->aufloesung_rueckstellung[$key][$gq->id] = $gq->startgeld;
						}
						else
						{
							$vorgaenge = new Vorgaenge(array(
								'gq'			=> $gq->id,
								'datum_ende'	=> $start
							));
							
							$this->aufloesung_rueckstellung[$key][$gq->id] = $vorgaenge->geldstand;
						}
						
						$vorgaenge = new Vorgaenge(array(
							'gq'			=> $gq->id,
							'datum_ende'	=> $ende
						));

						$this->aufbau_rueckstellung[$key][$gq->id] = $vorgaenge->geldstand;
					}
				}
				elseif($key == "Budgets")
				{
					foreach($gqs AS $gq_id => $name)
					{
						$gq = new Geldquelle($gq_id, false);
						
						$this->aufbau[$key][$gq->id] = $gq->budget[$this->jahr];
						
						if($gq->budgetjahr_beginn == get_option('tf_haushalts_jahr'))
						{
							$vorgaenge = new Vorgaenge(array(
								'gq'			=> $gq->id,
								'datum_beginn'	=> $start,
								'datum_ende'	=> $ende
							));

							$this->aufloesung[$key][$gq->id] = $vorgaenge->geldstand;
						}
						else
						{
							$vorgaenge = new Vorgaenge(array(
								'gq'			=> $gq->id,
								'datum_beginn'	=> $gq->budgetjahr_beginn . ($this->jahr-1),
								'datum_ende'	=> $gq->budgetjahr_beginn . $this->jahr
							));

							$this->aufloesung[$key][$gq->id] = $vorgaenge->geldstand;
							
							$vorgaenge = new Vorgaenge(array(
								'gq'			=> $gq->id,
								'datum_beginn'	=> $gq->budgetjahr_beginn . ($this->jahr-1),
								'datum_ende'	=> $start
							));
							
							$this->aufloesung_rueckstellung[$key][$gq->id] = $vorgaenge->geldstand;
							
							$vorgaenge = new Vorgaenge(array(
								'gq'			=> $gq->id,
								'datum_beginn'	=> $gq->budgetjahr_beginn . $this->jahr,
								'datum_ende'	=> $ende
							));
							
							$this->aufbau_rueckstellung[$key][$gq_id] = $vorgaenge->geldstand;
						}
					}
				}
				elseif($key == "Zeitbudgets")
				{
					foreach($gqs AS $gq_id => $name)
					{
						$gq = new Geldquelle($gq_id, false);
						
						if($start_timestamp <= $gq->zeitbudget_start && $ende_timestamp > $gq->zeitbudget_start)
						{
							$this->aufbau[$key][$gq->id] = $gq->budget;
						}
						elseif($start_timestamp > $gq->zeitbudget_start && $start_timestamp < $gq->zeitbudget_ende)
						{
							$vorgaenge = new Vorgaenge(array(
								'gq'			=> $gq->id,
								'datum_beginn'	=> $gq->zeitbudget_start_dat,
								'datum_ende'	=> $start
							));
							$vorgaenge->berechneGeldstand($gq->zeitbudget_start);
							
							$this->aufloesung_rueckstellung[$key][$gq->id] = $vorgaenge->geldstand;
						}
						
						if($start_timestamp <= $gq->zeitbudget_ende && $ende_timestamp > $gq->zeitbudget_ende)
						{
							$vorgaenge = new Vorgaenge(array(
								'gq'			=> $gq->id,
								'datum_beginn'	=> $gq->zeitbudget_start_dat,
								'datum_ende'	=> $gq->zeitbudget_ende_dat
							));
							$vorgaenge->berechneGeldstand($gq->zeitbudget_start);
							
							$this->aufloesung[$key][$gq->id] = $vorgaenge->geldstand;
						}
						elseif($ende_timestamp > $gq->zeitbudget_start && $ende_timestamp < $gq->zeitbudget_ende)
						{
							$vorgaenge = new Vorgaenge(array(
								'gq'			=> $gq->id,
								'datum_beginn'	=> $gq->zeitbudget_start_dat,
								'datum_ende'	=> $ende
							));
							$vorgaenge->berechneGeldstand($gq->zeitbudget_start);
							
							$this->aufbau_rueckstellung[$key][$gq->id] = $vorgaenge->geldstand;
						}
					}
				}
			}
			
			$knt_einnahmen = Konto::getAll("Einnahme");
			foreach($knt_einnahmen AS $kntkat_name => $knts)
			{
				foreach($knts AS $knt_id => $name)
				{
					$vorgaenge = new Vorgaenge(array(
						'knt'			=> $knt_id,
						'datum_beginn'	=> $start,
						'datum_ende'	=> $ende
					));
					
					$this->einnahmen[$kntkat_name][$knt_id] = $vorgaenge->geldstand;
				}
			}
			
			$knt_ausgaben = Konto::getAll("Ausgabe");
			foreach($knt_ausgaben AS $kntkat_name => $knts)
			{
				foreach($knts AS $knt_id => $name)
				{
					$vorgaenge = new Vorgaenge(array(
						'knt'			=> $knt_id,
						'datum_beginn'	=> $start,
						'datum_ende'	=> $ende
					));
					
					$this->ausgaben[$kntkat_name][$knt_id] = $vorgaenge->geldstand*-1;
				}
			}
		}
	}
	
	//*************************************************************************
	//******************************* POSTTYPE ********************************
	//*************************************************************************
	private static $post_type = "tf_haushaltsplan";
	
	public static function register_plan()
	{
		parent::register_pt(self::$post_type, "Haushaltsplan", "Haushaltspläne", "Haushaltsplan", "haushaltsplan");
	}
	
	//*************************************************************************
	//****************************** POST MASKEN ******************************
	//*************************************************************************
	public static function anzeige()
	{
		if(!isset($_GET['aus']))
		{
			//Auswahl Haushälte		
			if(isset($_POST['tf_plan_submit']))
			{
				$zaehler = 0;
				foreach($_POST['tf_plan_id'] AS $id)
				{
					$zaehler++;
					$plan[$zaehler] = $id;
					if($zaehler == 4)
					{
						break;
					}
				}
			}
			else
			{
				$jahre = Einstellungen::haushaltsjahrAusgabe() - get_option('tf_erfassung_start');
				if($jahre > 4)
				{
					$jahre = 4;
				}

				$zaehler = 0;
				while($zaehler != ($jahre+1))
				{
					$plan[$zaehler+1] = "IST-" . (Einstellungen::haushaltsjahrAusgabe()-$zaehler);
					$zaehler++;
				}

				if(count($plan) < 4)
				{
					$rest = 4 - count($plan);

					$plaene = get_post(array(
						'post_type'			=> 'tf_haushaltsplan',
						'status'			=> 'publish',
						'posts_per_page'	=> $rest
					));

					if(isset($plaene))
					{
						foreach($plaene AS $plan)
						{
							$rest++;
							$plan[$rest] = $plan->ID;
						}
					}
				}
			}

			//Initialisierung anzuzeigende Pläne
			if(isset($plan[1]))
			{
				$plan[1] = new Haushaltsplan($plan[1]);
			}

			if(isset($plan[2]))
			{
				$plan[2] = new Haushaltsplan($plan[2]);
			}

			if(isset($plan[3]))
			{
				$plan[3] = new Haushaltsplan($plan[3]);
			}

			if(isset($plan[4]))
			{
				$plan[4] = new Haushaltsplan($plan[4]);
			}

			//Auswahl Pläne zum anzeigen
			$form = new TF_Form(array(
				'action'		=> get_current_url(),
				'form'			=> true,
				'table'			=> true,
				'prefix'		=> "tf_plan",
				'submit'		=> "submit",
				'submit_button'	=> true,
				'submit_value'	=> "Anzeigen"
			));

			$form->td_select(array(
				'beschreibung'	=> 'Anzuzeigende Pläne:',
				'name'			=> 'id',
				'values'		=> Haushaltsplan::getValue(),
				'size'			=> 5,
				'multiple'		=> 4
			));

			TF_Form::jsHead();
			TF_Form::jsScript();

			unset($form);
		
			echo ('			<br><br>
			<table border="0" cellpadding="5" cellspacing="0" width="100%">
				<tr>
					<td width="20%"></td>
					<td width="20%" align="right"><b>');
					
						if(isset($plan[1]))
						{
							echo ($plan[1]->name);
						}

						echo ('</b></td>
					<td width="20%" align="right"><b>');
					
						if(isset($plan[2]))
						{
							echo ($plan[2]->name);
						}

						echo ('</b></td>
					<td width="20%" align="right"><b>');
					
						if(isset($plan[3]))
						{
							echo ($plan[3]->name);
						}

						echo ('</b></td>
					<td width="20%" align="right"><b>');
					
						if(isset($plan[4]))
						{
							echo ($plan[4]->name);
						}

						echo ('</b></td>
				</tr>
');
		
			$summen = array();

			//Auflösungen von Rückstellungen
			$summen = self::bereich($plan, Geldquelle::getAll(), "aufl_rs", $summen, "Auflösungen von Rückstellungen");

			//Aufbau	
			$gqs = Geldquelle::getAll();
			unset($gqs['Kassen']);
			unset($gqs['Konten']);
			$summen = self::bereich($plan, $gqs, "auf", $summen, "Aufbau von Budgets");

			//Einnahmen
			$summen = self::bereich($plan, Konto::getAll("Einnahme"), "ein", $summen, "Sonstige Einnahmen");

			//Anzeige Einnahmen Gesamt
			self::summen("ein", $summen);


			//Ausgaben
			$summen = self::bereich($plan, Konto::getAll("Ausgabe"), "aus", $summen, "Sonstige Ausgaben");

			//Auflösung		
			$summen = self::bereich($plan, $gqs, "aufl", $summen, "Auflösung von Budgets");

			//Aufbau von Rückstellungen
			$summen = self::bereich($plan, Geldquelle::getAll(), "auf_rs", $summen, "Aufbau von Rückstellungen");

			//Anzeige Ausgaben Gesamt
			self::summen("aus", $summen);


			//Anzeige Differenz
			self::summen("dif", $summen);

			echo ('			</table>
');
		}
		else
		{
			self::auszuege();
		}
	}
	
	public static function auszuege()
	{
		if((isset($_GET['aus']) && is_numeric($_GET['aus'])) || (isset($_POST['tf_plan_aus']) && is_numeric($_POST['tf_plan_aus'])))
		{
			if(isset($_GET['aus']) && is_numeric($_GET['aus']))
			{
				$id = sanitize_text_field($_GET['aus']);
			}
			else
			{
				$id = sanitize_text_field($_POST['tf_plan_aus']);
			}
			
			$args = array();
			$post_type = get_post_type($id);
			switch($post_type)
			{
				case 'tf_geldquelle':
					$args['gq'] = $id;
					break;
				case 'tf_konto':
					$args['knt'] = $id;
					break;
				case 'tf_absetzbaremittel':
					$args['am'] = $id;
					break;
			}
			
			$vorgaenge = new Vorgaenge($args);
			
			//Seitenwechsel
			$anzahl_vorgaenge = count($vorgaenge->vorgaenge);
			
			if(isset($_GET['a']))
			{
				$pro_seite = sanitize_title($_GET['a']);
				if(!is_numeric($pro_seite))
				{
					$pro_seite = 10;
				}
			}
			else
			{
				$pro_seite = 10;
			}
			
			$seiten = ceil($anzahl_vorgaenge/$pro_seite);
			if(isset($_GET['s']))
			{
				$seite = sanitize_text_field($_GET['s']);
				if(!is_numeric($seite))
				{
					$seite = 1;
				}
			}
			else
			{
				$seite = 1;
			}
			
			$zaehler = 0;
			
			if(get_current_url() != str_replace("?", "", get_current_url()))
			{
				if(get_current_url() != str_replace("&s=", "", get_current_url()))
				{
					$url = str_replace("&s=" . $_GET['s'], "", get_current_url());
				}
				else if(get_current_url() != str_replace("?s=", "", get_current_url()))
				{
					$url = str_replace("?s=" . $_GET['s'], "", get_current_url());
				}
				else 
				{
					$url = get_current_url();
				}
				
				$url = $url . "&s=";
			}
			else
			{
				$url = get_current_url() . "?s=";
			}
			
			while($zaehler != $seiten)
			{
				$zaehler++;
				if($seiten > 7)
				{
					if($zaehler == 1 || $zaehler == $seiten)
					{
						if(	$zaehler == $seiten && 
							($seite+3) != $zaehler && 
							($seite+2) != $zaehler && 
							($seite+1) != $zaehler &&
							$seite != $zaehler
						)
						{
							echo ('...&nbsp;&nbsp;');
						}
						echo ('<a href="' . $url . $zaehler . '">' . $zaehler . '</a>&nbsp;&nbsp;');
						if($zaehler == 1 && 
							($seite-3) != $zaehler && 
							($seite-2) != $zaehler && 
							($seite-1) != $zaehler &&
							$seite != $zaehler
						)
						{
							echo ('...&nbsp;&nbsp;');
						}
					}
					else {
						if(	($seite-2) == $zaehler ||
							($seite-1) == $zaehler ||
							$seite == $zaehler ||
							($seite+1) == $zaehler ||
							($seite+2) == $zaehler
						)
						{
							echo ('<a href="' . $url . $zaehler . '">' . $zaehler . '</a>&nbsp;&nbsp;');
						}
					}
				}
				else
				{
					echo ('<a href="' . $url . $zaehler . '">' . $zaehler . '</a>&nbsp;&nbsp;');
				}
			}
			
			$url = str_replace("?s=" . $_GET['s'], "", get_current_url());
			
			echo ('&nbsp;&nbsp;&nbsp;&nbsp;Anzeigen: 
			<a href="' . $url . '&a=10">10</a>&nbsp;&nbsp;
			<a href="' . $url . '&a=25">25</a>&nbsp;&nbsp;
			<a href="' . $url . '&a=50">50</a>&nbsp;&nbsp;
			<a href="' . $url . '&a=100">100</a>&nbsp;&nbsp;
			<a href="' . $url . '&a=999999">Alle</a>');
			
			$anfang = ($seite-1)*$pro_seite;
			$ende = $seite*$pro_seite;
			
			$vorgaenge = array_reverse($vorgaenge->vorgaenge);
			
			while($anfang != $ende)
			{
				if(isset($vorgaenge[$anfang]))
				{
					$vorgang_temp[$anfang] = $vorgaenge[$anfang];
					$anfang++;
				}
				else
				{
					$anfang = $ende;
				}
			}
			
			//Anzeige
			echo ('<br><br>
				
			<table border="0" cellpadding="5" cellspacing="0" width="100%">
				<tr>
					<td valign="top"><b>Datum</b></td>
					<td valign="top"><b>Quittung-ID</b></td>
					<td valign="top"><b>Name</b></td>
					<td valign="top"><b>Geldquelle</b></td>
					<td valign="top"><b>Konto</b></td>
					<td valign="top"><b>Wert</b></td>
				</tr>
				<tr>
					<td colspan="6"><hr></td>
				</tr>
');
			
			if(count($vorgang_temp) > 0)
			{
				foreach($vorgang_temp AS $vorgang)
				{
					echo ('				<tr>
					<td valign="top">' . $vorgang->datum_entstanden_dat . '</td>
					<td valign="top">' . $vorgang->q_id . '</td>
					<td valign="top"><b>' . $vorgang->name . '</b>');
				
					if(is_admin())
					{
						if($vorgang->art == "Ausgabe")
						{
							echo ('<br>an ' . $vorgang->empfaenger_in);
						}
						elseif($vorgang->art == "Einnahme")
						{
							echo ('<br>von ' . $vorgang->quelle);
						}
					}

					echo ('</td>
					<td valign="top">' . $vorgang->geldquelle->name . '</td>
					<td valign="top">' . $vorgang->konto->name . '</td>
					<td valign="top">');
				
					switch ($vorgang->art)
					{
						case 'Ausgehende Umbuchung':
						case 'Ausgabe':
							echo ('-');
							break;
						case 'Eingehende Umbuchung':
						case 'Einnahme':
							echo ('+');
							break;
					}

					echo (doubletostr($vorgang->wert, true) . '</td>
				</tr>
				<tr>
					<td colspan="6"><hr></td>
				</tr>
');
				}
			}
			else
			{
				echo ('				<tr>
					<td colspan="6"><b>Keine Vorgänge vorhanden</b></td>
				</tr>
');
			}
			
			echo ('			</table>
');
		}
		else
		{
			echo ('			Die Auswahl ist ungültig.
');
		}
	}
	
	public static function maske()
	{
		TF_Form::jsHead();
		
		$jahr = sanitize_text_field($_GET['art']);
		
		if($jahr >= get_option('tf_erfassung_start'))
		{
			wp_nonce_field('tf_plan', 'tf_plan_nonce');
			$form = new TF_Form(array(
				'form'			=> false,
				'table'			=> true,
				'prefix'		=> 'tf_plan',
				'submit'		=> 'pt'
			));

			$start = get_option('tf_haushalts_jahr') . $jahr;
			$ende = get_option('tf_haushalts_jahr') . ($jahr-1);

			$start_timestamp = datetots($start);
			$ende_timestamp = datetots($ende);

			//Auflösungen von Rückstellungen
			$form->td(array(
				'art'			=> 'anzeige',
				'spalte'		=> false,
				'name'			=> 'aufloesung_rs',
				'anzeige'		=> '<h2>Auflösungen von Rückstellungen</h2>'
			));
			$gq_arten = Geldquelle::getValue();
			$pruef = array();
			foreach($gq_arten AS $art => $gqs)
			{
				if($art == "Budgets")
				{
					foreach($gqs AS $gq_id => $name)
					{
						$gq = new Geldquelle($gq_id, false);

						if($gq->budgetjahr_beginn == get_option('tf_haushalts_jahr'))
						{
							unset($gqs[$gq_id]);
						}
					}
				}

				if($art == "Zeitbudgets")
				{
					foreach($gqs AS $gq_id => $name)
					{
						$gq = new Geldquelle($gq_id, false);

						if($start_timestamp > $gq->zeitbudget_start && $start_timestamp < $gq->zeitbudget_ende)
						{
							unset($gqs[$gq_id]);
						}
					}
				}

				foreach($gqs AS $gq_id => $name)
				{		
					if(!isset($pruef[$art]))
					{
						$form->td(array(
							'art'			=> 'anzeige',
							'spalte'		=> false,
							'name'			=> strtolower($art),
							'anzeige'		=> '<h3>' . $art . '</h3>'
						));

						$pruef[$art] = 1;
					}

					$form->td_text(array(
						'beschreibung'		=> $name,
						'name'				=> 'aufloesung_rs_' . $gq_id,
						'size'				=> 20,
						'checking'			=> array('Fill', 'Number')
					));
				}
			}

			//Aufbau von Budgets
			$gq_arten = Geldquelle::getValue();
			unset($gq_arten['Kassen']);
			unset($gq_arten['Konten']);
			$pruef = array();
			foreach($gq_arten AS $art => $gqs)
			{
				if($art == "Zeitbudgets")
				{
					foreach($gqs AS $gq_id => $name)
					{
						$gq = new Geldquelle($gq_id, false);

						if($start_timestamp > $gq->zeitbudget_start || $ende_timestamp < $gq->zeitbudget_start)
						{
							unset($gqs[$gq_id]);
						}
					}
				}

				foreach($gqs AS $gq_id => $name)
				{
					if(!isset($pruef['anzeige']))
					{
						$form->td(array(
							'art'			=> 'anzeige',
							'spalte'		=> false,
							'name'			=> 'aufbau',
							'anzeige'		=> '<h2>Aufbau von Budgets</h2>'
						));

						$pruef['anzeige'] = 1;
					}

					if(!isset($pruef[$art]))
					{
						$form->td(array(
							'art'			=> 'anzeige',
							'spalte'		=> false,
							'name'			=> strtolower($art),
							'anzeige'		=> '<h3>' . $art . '</h3>'
						));

						$pruef[$art] = 1;
					}

					$form->td_text(array(
						'beschreibung'		=> $name,
						'name'				=> 'aufbau_' . $gq_id,
						'size'				=> 20,
						'checking'			=> array('Fill', 'Number')
					));
				}
			}

			//Einnahmen
			$form->td(array(
				'art'			=> 'anzeige',
				'spalte'		=> false,
				'name'			=> 'einnahmen',
				'anzeige'		=> '<h2>Einnahmen</h2>'
			));
			$kats = Konto::getValue("Einnahme");
			$pruef = array();
			foreach($kats AS $kat => $knts)
			{
				if(!isset($pruef[$kat]))
				{
					$form->td(array(
						'art'			=> 'anzeige',
						'spalte'		=> false,
						'name'			=> strtolower($kat),
						'anzeige'		=> '<h3>' . $kat . '</h3>'
					));

					$pruef[$kat] = 1;
				}

				foreach($knts AS $knt_id => $name)
				{
					$form->td_text(array(
						'beschreibung'		=> $name,
						'name'				=> 'einnahme_' . $knt_id,
						'size'				=> 20,
						'checking'			=> array('Fill', 'Number')
					));
				}
			}

			//Ausgaben
			$form->td(array(
				'art'			=> 'anzeige',
				'spalte'		=> false,
				'name'			=> 'ausgaben',
				'anzeige'		=> '<h2>Ausgaben</h2>'
			));
			$kats = Konto::getValue("Ausgabe");
			$pruef = array();
			foreach($kats AS $kat => $knts)
			{
				if(!isset($pruef[$kat]))
				{
					$form->td(array(
						'art'			=> 'anzeige',
						'spalte'		=> false,
						'name'			=> strtolower($kat),
						'anzeige'		=> '<h3>' . $kat . '</h3>'
					));

					$pruef[$kat] = 1;
				}

				foreach($knts AS $knt_id => $name)
				{
					$form->td_text(array(
						'beschreibung'		=> $name,
						'name'				=> 'ausgabe_' . $knt_id,
						'size'				=> 20,
						'checking'			=> array('Fill', 'Number')
					));
				}
			}

			//Auflösung von Budgets
			$gq_arten = Geldquelle::getValue();
			unset($gq_arten['Kassen']);
			unset($gq_arten['Konten']);
			$pruef = array();
			foreach($gq_arten AS $art => $gqs)
			{
				if($art == "Zeitbudgets")
				{
					foreach($gqs AS $gq_id => $name)
					{
						$gq = new Geldquelle($gq_id, false);

						if($start_timestamp > $gq->zeitbudget_ende || $ende_timestamp < $gq->zeitbudget_ende)
						{
							unset($gqs[$gq_id]);
						}
					}
				}

				foreach($gqs AS $gq_id => $name)
				{
					if(!isset($pruef['anzeige']))
					{
						$form->td(array(
							'art'			=> 'anzeige',
							'spalte'		=> false,
							'name'			=> 'aufloesung',
							'anzeige'		=> '<h2>Auflösung von Budgets</h2>'
						));

						$pruef['anzeige'] = 1;
					}

					if(!isset($pruef[$art]))
					{
						$form->td(array(
							'art'			=> 'anzeige',
							'spalte'		=> false,
							'name'			=> strtolower($art),
							'anzeige'		=> '<h3>' . $art . '</h3>'
						));

						$pruef[$art] = 1;
					}

					$form->td_text(array(
						'beschreibung'		=> $name,
						'name'				=> 'aufloesung_' . $gq_id,
						'size'				=> 20,
						'checking'			=> array('Fill', 'Number')
					));
				}
			}

			//Aufbau von Rückstellungen
			$form->td(array(
				'art'			=> 'anzeige',
				'spalte'		=> false,
				'name'			=> 'aufbau_rs',
				'anzeige'		=> '<h2>Auflösungen von Rückstellungen</h2>'
			));
			$gq_arten = Geldquelle::getValue();
			$pruef = array();
			foreach($gq_arten AS $art => $gqs)
			{
				if($art == "Budgets")
				{
					foreach($gqs AS $gq_id => $name)
					{
						$gq = new Geldquelle($gq_id, false);

						if($gq->budgetjahr_beginn == get_option('tf_haushalts_jahr'))
						{
							unset($gqs[$gq_id]);
						}
					}
				}

				if($art == "Zeitbudgets")
				{
					foreach($gqs AS $gq_id => $name)
					{
						$gq = new Geldquelle($gq_id, false);

						if($ende_timestamp > $gq->zeitbudget_start && $ende_timestamp < $gq->zeitbudget_ende)
						{
							unset($gqs[$gq_id]);
						}
					}
				}

				foreach($gqs AS $gq_id => $name)
				{		
					if(!isset($pruef[$art]))
					{
						$form->td(array(
							'art'			=> 'anzeige',
							'spalte'		=> false,
							'name'			=> strtolower($art),
							'anzeige'		=> '<h3>' . $art . '</h3>'
						));

						$pruef[$art] = 1;
					}

					$form->td_text(array(
						'beschreibung'		=> $name,
						'name'				=> 'aufbau_rs_' . $gq_id,
						'size'				=> 20,
						'checking'			=> array('Fill', 'Number')
					));
				}
			}

			$form->hidden(array(
				'name'			=> 'jahr',
				'value'			=> $jahr
			));

			TF_Form::jsScript();

			unset($form);
		}
		else 
		{
			echo ('			Angegebenes Haushaltsjahr liegt nicht in der Erfassung der TransFinanz.');
		}
		
		parent::wpHide();
	}
	
	public static function metabox()
	{
		add_meta_box("transfinanz_plan_mb", __("Haushaltsdaten", "TransFinanz"), array("Haushaltsplan", "maske"), "tf_haushaltsplan", "normal", "default");
	}
	
	//*************************************************************************
	//*********************************** MENU ********************************
	//*************************************************************************
	public static function HaushaltsplanMenu()
	{
		if(is_admin())
		{
			$jahr = Einstellungen::haushaltsjahrAusgabe();
			
			remove_submenu_page("edit.php?post_type=tf_haushaltsplan",	"post-new.php?post_type=tf_haushaltsplan");
			remove_submenu_page("edit.php?post_type=tf_haushaltsplan",	"edit.php?post_type=tf_haushaltsplan");
			add_submenu_page("edit.php?post_type=tf_haushaltsplan",		"Haushaltspläne",				"Haushaltspläne",			1, "haushalt", array('Haushaltsplan', 'anzeige'));
			add_submenu_page("edit.php?post_type=tf_haushaltsplan",		"Neuer Plan " . $jahr,			"Neuer Plan " . $jahr,		2, "post-new.php?post_type=tf_haushaltsplan&art=" . $jahr);
			if(($jahr-1) >= get_option('tf_erfassung_start'))
			{
				add_submenu_page("edit.php?post_type=tf_haushaltsplan", "Neuer Plan " . ($jahr-1),		"Neuer Plan " . ($jahr-1),	3, "post-new.php?post_type=tf_haushaltsplan&art=" . ($jahr-1));
			}
			add_submenu_page("edit.php?post_type=tf_haushaltsplan",		"Neuer Plan " . ($jahr+1),		"Neuer Plan " . ($jahr+1),	4, "post-new.php?post_type=tf_haushaltsplan&art=" . ($jahr+1));
		}
	}
	
	//*************************************************************************
	//**************************** POST SPEICHERUNG ***************************
	//*************************************************************************
	public static function save($post_id)
	{
		if(get_post_type($post_id) == "tf_haushaltsplan")
		{
			if(!isset($_POST['tf_plan_nonce']))
			{
				return;
			}

			if (!wp_verify_nonce($_POST['tf_plan_nonce'], 'tf_plan')) 
			{
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			{
				return;
			}
			
			$jahr = sanitize_text_field($_POST['tf_plan_jahr']);

			if($jahr >= get_option('tf_erfassung_start'))
			{
				update_post_meta($post_id, 'tf_plan_jahr', sanitize_text_field($_POST['tf_plan_jahr']));
				
				$start = get_option('tf_haushalts_jahr') . $jahr;
				$ende = get_option('tf_haushalts_jahr') . ($jahr-1);

				$start_timestamp = datetots($start);
				$ende_timestamp = datetots($ende);

				//Auflösungen von Rückstellungen
				$gq_arten = Geldquelle::getValue();
				foreach($gq_arten AS $art => $gqs)
				{
					if($art == "Zeitbudgets")
					{
						foreach($gqs AS $gq_id => $name)
						{
							$gq = new Geldquelle($gq_id, false);

							if($start_timestamp > $gq->zeitbudget_start && $start_timestamp < $gq->zeitbudget_ende)
							{
								unset($gqs[$gq_id]);
							}
						}
					}

					foreach($gqs AS $gq_id => $name)
					{		
						update_post_meta($post_id, "tf_plan_aufloesung_rs_" . $gq_id, sanitize_text_field($_POST['tf_plan_aufloesung_rs_' . $gq_id]));
					}
				}

				//Aufbau von Budgets
				$gq_arten = Geldquelle::getValue();
				unset($gq_arten['Kassen']);
				unset($gq_arten['Konten']);
				foreach($gq_arten AS $art => $gqs)
				{
					if($art == "Zeitbudgets")
					{
						foreach($gqs AS $gq_id => $name)
						{
							$gq = new Geldquelle($gq_id, false);

							if($start_timestamp > $gq->zeitbudget_start || $ende_timestamp < $gq->zeitbudget_start)
							{
								unset($gqs[$gq_id]);
							}
						}
					}

					foreach($gqs AS $gq_id => $name)
					{
						update_post_meta($post_id, "tf_plan_aufbau_" . $gq_id, sanitize_text_field($_POST['tf_plan_aufbau_' . $gq_id]));
					}
				}

				//Einnahmen
				$kats = Konto::getValue("Einnahme");
				foreach($kats AS $kat => $knts)
				{
					foreach($knts AS $knt_id => $name)
					{
						update_post_meta($post_id, "tf_plan_einnahme_" . $knt_id, sanitize_text_field($_POST['tf_plan_einnahme_' . $knt_id]));
					}
				}

				//Ausgaben
				$kats = Konto::getValue("Ausgabe");
				foreach($kats AS $kat => $knts)
				{
					foreach($knts AS $knt_id => $name)
					{
						update_post_meta($post_id, "tf_plan_ausgabe_" . $knt_id, sanitize_text_field($_POST['tf_plan_ausgabe_' . $knt_id]));
					}
				}

				//Auflösung von Budgets
				$gq_arten = Geldquelle::getValue();
				unset($gq_arten['Kassen']);
				unset($gq_arten['Konten']);
				foreach($gq_arten AS $art => $gqs)
				{
					if($art == "Zeitbudgets")
					{
						foreach($gqs AS $gq_id => $name)
						{
							$gq = new Geldquelle($gq_id, false);

							if($start_timestamp > $gq->zeitbudget_ende || $ende_timestamp < $gq->zeitbudget_ende)
							{
								unset($gqs[$gq_id]);
							}
						}
					}

					foreach($gqs AS $gq_id => $name)
					{
						update_post_meta($post_id, "tf_plan_aufloesung_" . $gq_id, sanitize_text_field($_POST['tf_plan_aufloesung_' . $gq_id]));
					}
				}

				//Aufbau von Rückstellungen
				$gq_arten = Geldquelle::getValue();
				foreach($gq_arten AS $art => $gqs)
				{
					if($art == "Budgets")
					{
						foreach($gqs AS $gq_id => $name)
						{
							$gq = new Geldquelle($gq_id, false);

							if($gq->budgetjahr_beginn == get_option('tf_haushalts_jahr'))
							{
								unset($gqs[$gq_id]);
							}
						}
					}

					if($art == "Zeitbudgets")
					{
						foreach($gqs AS $gq_id => $name)
						{
							$gq = new Geldquelle($gq_id, false);

							if($ende_timestamp > $gq->zeitbudget_start && $ende_timestamp < $gq->zeitbudget_ende)
							{
								unset($gqs[$gq_id]);
							}
						}
					}

					foreach($gqs AS $gq_id => $name)
					{		
						update_post_meta($post_id, "tf_plan_aufbau_rs_" . $gq_id, sanitize_text_field($_POST['tf_plan_aufbau_rs_' . $gq_id]));
					}
				}
			}
		}
	}
	
	//*************************************************************************
	//************************* POST ANZEIGE IN LISTE *************************
	//*************************************************************************
	private static $criterias_key = array(
		'tf_plan_jahr'		=> 'Jahr'
	);

	private static $criterias = array(
		'tf_plan_jahr'
	);
	
	private static $form_prefix = "tf_plan";

	public static function postlist($defaults)
	{
		return parent::postlists($defaults, self::$criterias_key);
	}

	public static function postlist_column($column_name, $post_id)
	{
		parent::postlist_column(self::$post_type, $column_name, $post_id, self::$criterias);
	}

	public static function postlist_sorting($columns)
	{
		return parent::postlist_sorting($columns, self::$criterias_key);
	}

	public static function postlist_orderby($vars)
	{
		return parent::postlist_orderby(self::$post_type, $vars, self::$criterias_key);
	}

	public static function postlist_filtering()
	{
		parent::postlist_filtering(self::$post_type, self::$form_prefix, self::$criterias);
	}

	public static function postlist_filtering_sort($query)
	{
		return parent::postlist_filtering_sort($query, self::$post_type, self::$criterias);
	}
	
	public static function postlist_options($actions, $post)
	{
		return parent::postlist_options($actions, $post, self::$post_type);
	}
	
	//*************************************************************************
	//****************************** SONSTIGES ********************************
	//*************************************************************************
	public static function getValue()
	{
		$jahr = Einstellungen::haushaltsjahrAusgabe();
		
		$differenz = $jahr - get_option('tf_erfassung_start');
		
		$zaehler = -1;
		while($zaehler != $differenz)
		{
			$zaehler++;
			$values["IST-" . ($jahr-$zaehler)] = "IST-" . ($jahr-$zaehler);
		}
		
		$plaene = get_post(array(
			'post_type'			=> 'tf_haushaltsplan',
			'status'			=> 'publish',
			'posts_per_page'	=> -1
		));

		if(isset($plaene))
		{
			foreach($plaene AS $plan)
			{
				$values[$plan->ID] = "SOLL-" . $plan->post_title;
			}
		}
		
		return $values;
	}
	
	public function returnVar($var, $key1, $key2)
	{
		switch ($var)
		{
			case "aufl_rs":
				if(isset($this->aufloesung_rueckstellung[$key1][$key2]))
				{
					return $this->aufloesung_rueckstellung[$key1][$key2];
				}
				return null;
			case "auf":
				if(isset($this->aufbau[$key1][$key2]))
				{
					return $this->aufbau[$key1][$key2];
				}
				return null;
			case "ein":
				if(isset($this->einnahmen[$key1][$key2]))
				{
					return $this->einnahmen[$key1][$key2];
				}
				return null;
			case "aus":
				if(isset($this->ausgaben[$key1][$key2]))
				{
					return $this->ausgaben[$key1][$key2];
				}
				return null;
			case "aufl":
				if(isset($this->aufloesung[$key1][$key2]))
				{
					return $this->aufloesung[$key1][$key2];
				}
				return null;
			case "auf_rs":
				if(isset($this->aufbau_rueckstellung[$key1][$key2]))
				{
					return $this->aufbau_rueckstellung[$key1][$key2];
				}
				return null;
			default:
				return null;
		}
		return null;
	}
	
	public static function bereich($plan, $values, $art, $summen, $beschreibung)
	{
		$anzeige = array();
		foreach($values AS $value_kat => $value)
		{
			foreach($value AS $value_id => $name)
			{
				if(	(isset($plan[1]) && $plan[1]->returnVar($art, $value_kat, $value_id) !== null) ||
					(isset($plan[2]) && $plan[2]->returnVar($art, $value_kat, $value_id) !== null) ||
					(isset($plan[3]) && $plan[3]->returnVar($art, $value_kat, $value_id) !== null) ||
					(isset($plan[4]) && $plan[4]->returnVar($art, $value_kat, $value_id) !== null)
				)
				{
					if(!isset($anzeige[$art]))
					{
						echo ('				<tr>
					<td width="100%" colspan="5"><h2>' . $beschreibung . '</h2></td>
				</tr>
');
						$anzeige[$art] = array();
					}
					
					if(!isset($anzeige[$art][$value_kat]))
					{
						echo ('				<tr>
					<td width="100%" colspan="5"><h3>' . $value_kat . '</h3></td>
				</tr>
');
						$anzeige[$art][$value_kat] = 1;
					}
					
					$url = get_current_url();
					if($url != str_replace("?", "", $url))
					{
						$url = $url . "&aus=" . $value_id;
					}
					else
					{
						$url = $url . "?aus=" . $value_id;
					}

					echo ('				<tr>
					<td width="20%"><a href="' . $url . '">' . $name . '</a></td>
');
					
					$plan_index = 0;
					while($plan_index < 4)
					{
						$plan_index++;
						echo ('					<td width="20%" align="right">');

						if(isset($plan[$plan_index]) && $plan[$plan_index]->returnVar($art, $value_kat, $value_id) !== null)
						{
							echo (doubletostr($plan[$plan_index]->returnVar($art, $value_kat, $value_id), true));
							$summen[$art][$plan_index] = $summen[$art][$plan_index] + $plan[$plan_index]->returnVar($art, $value_kat, $value_id);
						}
						elseif(isset($plan[$plan_index]))
						{
							echo (doubletostr(0));
						}

						echo ('</td>
');
					}					
					
					echo ('				</tr>
');
				}
			}
		}
		if(isset($anzeige[$art]))
		{
			self::summen($art, $summen, $beschreibung);
		}
		
		return $summen;
	}
	
	public static function summen($art, $summen, $beschreibung = "")
	{
		if($art == "ein")
		{
			$anzeige = "Summen<br>Einnahmen Gesamt";
			$key1 = "aufl_rs";
			$key2 = "auf";
			$key3 = "ein";
		}
		elseif($art == "aus")
		{
			$anzeige = "Summen<br>Ausgaben Gesamt";
			$key1 = "aufl";
			$key2 = "auf_rs";
			$key3= "aus";
		}
		elseif($art == "dif")
		{
			$anzeige = "Differenz";
			$key1 = "aufl_rs";
			$key2 = "auf";
			$key3 = "ein";
			$key4 = "aufl";
			$key5 = "auf_rs";
			$key6= "aus";
		}
		else
		{
			$anzeige = "Summen<br>" . $beschreibung;
		}
		
		echo ('				<tr><td colspan="5" width="100%"><hr></td></tr>
				<tr>
					<td width="20%"><b>' . $anzeige . ':</b></td>
');
		
		$plan_index = 0;
		while($plan_index < 4)
		{
			$plan_index++;
			echo ('					<td width="20%" align="right">');
			
			if((isset($summen[$key1][$plan_index]) || isset($summen[$key2][$plan_index]) || isset($summen[$key3][$plan_index])) && $art == "dif")
			{
				echo (doubletostr((($summen[$key1][$plan_index] + $summen[$key2][$plan_index] + $summen[$key3][$plan_index])-($summen[$key4][$plan_index] + $summen[$key5][$plan_index] + $summen[$key6][$plan_index])), true));
			}
			elseif((isset($summen[$key1][$plan_index]) || isset($summen[$key2][$plan_index]) || isset($summen[$key3][$plan_index])) && ($art == "aus" || $art == "ein"))
			{
				echo (doubletostr(($summen[$key1][$plan_index] + $summen[$key2][$plan_index] + $summen[$key3][$plan_index])));
			}
			elseif(isset($summen[$art][$plan_index]))
			{
				echo (doubletostr($summen[$art][$plan_index], true));
			}

			echo ('</td>
');
		}
		
		echo ('				</tr>
');
	}
}

?>