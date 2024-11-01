<?php

	function TransFinanzInit()
	{
		//Registrierung aller Post Types
		Geldquelle::register_geldquelle();
		KontoKategorie::register_kontokategorie();
		Konto::register_konto();
		AbsetzbareMittel::register_absetzbaremittel();
		Vorgang::register_vorgang();
		Antrag::register_antrag();
		Haushaltsplan::register_plan();
		
		if(!session_id())
		{
			session_start();
		}
	}
	
	function tstodate($ts)
	{
		if(is_numeric($ts))
		{
			return (date('d', $ts) . "." . date('m', $ts) . "." . date('Y', $ts));
		}
		else
		{
			return "";
		}
	}
	
	function datetots($date, $format = "normal")
	{
		if($format == "normal")
		{
			$date = explode(".", $date);
		}
		elseif($format == "sql")
		{
			$date = explode("-", $date);
		}
		
		if(checkdate($date[1], $date[0], $date[2]) && $format == "normal")
		{
			return mktime(0, 0, 0, $date[1], $date[0], $date[2]);
		}
		elseif(checkdate($date[1], $date[2], $date[0]) && $format == "sql")
		{
			return mktime(0, 0, 0, $date[1], $date[2], $date[0]);
		}
		else
		{
			return "";
		}
	}
	
	function doubletostr($double, $euro = false, $plus = false)
	{
		if(($double > 0 && $double < 0.01) || ($double < 0 && $double > -0.01))
		{
			$double = 0;
		}
		
		$str = str_replace(".", ",", $double);
		
		$str_ex = explode(",", $str);
		if(!isset($str_ex[0]) || $str_ex[0] == "")
		{
			$str = "0";
		}
		
		if(!isset($str_ex[1]))
		{
			$str = $str . ",00";
		}
		elseif(strlen($str_ex[1]) < 2)
		{
			$str = $str . "0";
		}
		
		if($euro == true)
		{
			$str = $str . " €";
		}
		
		if($plus == true && substr($str, 0, 1) != "-")
		{
			$str = "+" . $str;
		}
		
		return $str;
	}
	
	function strtodouble($str)
	{
		$double = str_replace(",", ".", $str);
		
		return $double;
	}
	
	function get_current_url()
	{
		$current_url  = 'http';
		
		$server_https = $_SERVER["HTTPS"];
		$server_name  = $_SERVER["SERVER_NAME"];
		$server_port  = $_SERVER["SERVER_PORT"];
		$request_uri  = $_SERVER["REQUEST_URI"];
		
		if ($server_https == "on") 
		{
			$current_url .= "s";
		}
		
		$current_url .= "://";
		
		if ($server_port != "80") 
		{
			$current_url .= $server_name . ":" . $server_port . $request_uri;
		}
		else 
		{
			$current_url .= $server_name . $request_uri;
		}
		
		return $current_url;
	}
	
	function importData()
	{
		//Import Geldquellen
		$posts = get_posts(array(
			'post_type'	=> 'tf_geldquelle',
			'posts_per_page' => -1
		));
		
		foreach($posts AS $post)
		{
			$id = $post->ID;
			
			if(get_post_meta($id, "tf_geldquelle_art", true) != null)
			{
				update_post_meta($id, "tf_gq_art",					get_post_meta($id, "tf_geldquelle_art", true));

				if(get_post_meta($id, "tf_geldquelle_art", true) == "Konto")
				{
					update_post_meta($id, "tf_gq_startgeld",			get_post_meta($id, "tf_geldquelle_wert", true));
					update_post_meta($id, "tf_gq_disp",					get_post_meta($id, "tf_geldquelle_giro", true));
				}
				elseif(get_post_meta($id, "tf_geldquelle_art", true) == "Kasse")
				{
					update_post_meta($id, "tf_gq_startgeld",			get_post_meta($id, "tf_geldquelle_wert", true));
				}
				elseif(get_post_meta($id, "tf_geldquelle_art", true) == "Budget")
				{
					update_post_meta($id, "tf_gq_beginn_budgetjahr",	get_post_meta($id, "tf_geldquelle_datum", true));

					$posts_temp = get_posts(array(
						'post_type'		=> 'tf_vorgang',
						'posts_per_page' => -1,
						'meta_query'	=> array(
							array(
								'key'			=> 'tf_vorgang_art',
								'value'			=> 'budget_start'
							)
						)
					));

					foreach($posts_temp AS $post_temp)
					{
						$id_temp = $post_temp->ID;
						$jahr = explode(".", get_post_meta($id_temp, "tf_vorgang_datum_entstanden", true));
						$values[$jahr[0]] = get_post_meta($id_temp, "tf_vorgang_wert", true);
						wp_delete_post($id_temp, true);
					}

					$temp_wert = get_post_meta($id, "tf_geldquelle_wert", true);

					$erfassungs_start = get_option('tf_erfassung_start');
					if($erfassungs_start != -1)
					{
						$abstand = date('Y') - $erfassungs_start;
						$zaehler = 0;
						while(($abstand+1) != $zaehler)
						{
							$jahr = $erfassungs_start + $zaehler;

							if(isset($values[$jahr]))
							{
								$temp_wert = $values[$jahr];
							}

							update_post_meta($id, "tf_gq_budget_" . $jahr, $temp_wert);

							$zaehler++;
						}
					}
				}
				elseif(get_post_meta($id, "tf_geldquelle_art", true) == "Zeitbudget")
				{
					update_post_meta($id, "tf_gq_budget",				get_post_meta($id, "tf_geldquelle_wert", true));
					update_post_meta($id, "tf_gq_zeitfenster_start",	datetots(get_post_meta($id, "tf_geldquelle_datum", true), "sql"));
					update_post_meta($id, "tf_gq_zeitfenster_ende",		datetots(get_post_meta($id, "tf_geldquelle_datum_ende", true), "sql"));
				}

				delete_post_meta($id, "tf_geldquelle_art");
				delete_post_meta($id, "tf_geldquelle_wert");
				delete_post_meta($id, "tf_geldquelle_giro");
				delete_post_meta($id, "tf_geldquelle_datum");
				delete_post_meta($id, "tf_geldquelle_datum_ende");
			}
		}
		
		//Import Konto Kategorien
		$posts = get_posts(array(
			'post_type'	=> 'tf_kontokategorie',
			'posts_per_page' => -1
		));
		
		foreach($posts AS $post)
		{
			$id = $post->ID;
			
			if(get_post_meta($id, "tf_kontokategorie_art", true) != null)
			{
				update_post_meta($id, "tf_kntkat_art",		get_post_meta($id, "tf_kontokategorie_art", true));
				update_post_meta($id, "tf_kntkat_ordnung",	get_post_meta($id, "tf_kontokategorie_ordnung", true));

				delete_post_meta($id, "tf_kontokategorie_art");
				delete_post_meta($id, "tf_kontokategorie_ordnung");
			}
		}
		
		//Import Kategorien
		$posts = get_posts(array(
			'post_type'	=> 'tf_konto',
			'posts_per_page' => -1
		));
		
		foreach($posts AS $post)
		{
			$id = $post->ID;
			
			if(get_post_meta($id, "tf_konto_art", true) != null)
			{
				update_post_meta($id, "tf_knt_art",			get_post_meta($id, "tf_konto_art", true));
				update_post_meta($id, "tf_knt_ordnung",		get_post_meta($id, "tf_konto_ordnung", true));
				
				$kntkat = new KontoKategorie(get_post_meta($id, "tf_konto_kntkat_id", true));
				update_post_meta($id, "tf_knt_kat",			$kntkat->id);
				update_post_meta($id, "tf_knt_kat_name",	$kntkat->name);

				delete_post_meta($id, "tf_konto_art");
				delete_post_meta($id, "tf_konto_ordnung");
				delete_post_meta($id, "tf_konto_kntkat_id");
			}
		}
		
		//Löschen Kategorien
		$posts = get_posts(array(
			'post_type'	=> 'tf_kategorie',
			'posts_per_page' => -1
		));
		
		foreach($posts AS $post)
		{
			$id = $post->ID;
			
			wp_delete_post($id, true);
		}
		
		//Import Absetzbare Mittel
		$posts = get_posts(array(
			'post_type'	=> 'tf_absetzbaremittel',
			'posts_per_page' => -1
		));
		
		foreach($posts AS $post)
		{
			$id = $post->ID;
			
			if(get_post_meta($id, "tf_absetzbaremittel_beginn", true) != null)
			{
				update_post_meta($id, "tf_am_beginn",			get_post_meta($id, "tf_absetzbaremittel_beginn", true));
				update_post_meta($id, "tf_am_rueckzahlung",		get_post_meta($id, "tf_absetzbaremittel_rueckzahlung", true));
				update_post_meta($id, "tf_am_rechnung",			get_post_meta($id, "tf_absetzbaremittel_rechnung", true));

				delete_post_meta($id, "tf_absetzbaremittel_beginn");
				delete_post_meta($id, "tf_absetzbaremittel_rueckzahlung");
				delete_post_meta($id, "tf_absetzbaremittel_rechnung");
			}
		}
		
		//Import Vorgang
		$posts = get_posts(array(
			'post_type'	=> 'tf_vorgang',
			'posts_per_page' => -1
		));
		
		foreach($posts AS $post)
		{
			$id = $post->ID;
			
			if(get_post_meta($id, "tf_vorgang_art", true) != null)
			{
				update_post_meta($id, "tf_vorg_q",					buildQID(sanitize_text_field(get_post_meta($id, "tf_vorgang_datum_entstanden_timestamp", true))));
				
				update_post_meta($id, "tf_vorg_wert",				get_post_meta($id, "tf_vorgang_wert", true));
				update_post_meta($id, "tf_vorg_datum_entstanden",	get_post_meta($id, "tf_vorgang_datum_entstanden_timestamp", true));

				$art = get_post_meta($id, "tf_vorgang_art", true);

				if($art == "Ausgabe" || $art == "Einnahme")
				{
					update_post_meta($id, "tf_vorg_art",				get_post_meta($id, "tf_vorgang_art", true));
					
					$knt = new Konto(get_post_meta($id, "tf_vorgang_knt_id", true), false);
					update_post_meta($id, "tf_vorg_knt",				$knt->id);
					update_post_meta($id, "tf_vorg_knt_name",			$knt->name);

					if($art == "Ausgabe")
					{
						update_post_meta($id, "tf_vorg_empfaenger_in",	get_post_meta($id, "tf_vorgang_bezug", true));
						update_post_meta($id, "tf_vorg_wert_sign",		"-" . get_post_meta($id, "tf_vorgang_wert", true));
					}
					elseif($art == "Einnahme")
					{
						update_post_meta($id, "tf_vorg_quelle",			get_post_meta($id, "tf_vorgang_bezug", true));
						update_post_meta($id, "tf_vorg_wert_sign",		get_post_meta($id, "tf_vorgang_wert", true));
					}
				}
				elseif($art == "Umbuchung_ausgehend" || $art == "Umbuchung_eingehend")
				{
					if($art == "Umbuchung_ausgehend")
					{
						update_post_meta($id, "tf_vorg_art",				"Ausgehende Umbuchung");
						update_post_meta($id, "tf_vorg_wert_sign",			"-" . get_post_meta($id, "tf_vorgang_wert", true));
					}
					elseif($art == "Umbuchung_eingehend")
					{
						update_post_meta($id, "tf_vorg_art",				"Eingehende Umbuchung");
						update_post_meta($id, "tf_vorg_wert_sign",			get_post_meta($id, "tf_vorgang_wert", true));
					}

					update_post_meta($id, "tf_vorg_umbuch_id",			get_post_meta($id, "tf_vorgang_umbuch_id", true));
				}

				$gq = new Geldquelle(get_post_meta($id, "tf_vorgang_gq_id", true), false);
				update_post_meta($id, "tf_vorg_gq",				$gq->id);
				update_post_meta($id, "tf_vorg_gq_name",		$gq->name);
				if(get_post_meta($id, "tf_vorgang_am_id", true) == 0)
				{
					update_post_meta($id, "tf_vorg_am",				-1);
					update_post_meta($id, "tf_vorg_am_name",		"");
				}
				else
				{
					$am = new AbsetzbareMittel(get_post_meta($id, "tf_vorgang_am_id", true), false);
					update_post_meta($id, "tf_vorg_am",				$am->id);
					update_post_meta($id, "tf_vorg_am_name",		$am->name);
				}

				delete_post_meta($id, "tf_vorgang_q_id");
				delete_post_meta($id, "tf_vorgang_gq_id");
				delete_post_meta($id, "tf_vorgang_knt_id");
				delete_post_meta($id, "tf_vorgang_kat_id");
				delete_post_meta($id, "tf_vorgang_am_id");
				delete_post_meta($id, "tf_vorgang_umbuch_id");
				delete_post_meta($id, "tf_vorgang_beschreibung");
				delete_post_meta($id, "tf_vorgang_wert");
				delete_post_meta($id, "tf_vorgang_datum_entstanden");
				delete_post_meta($id, "tf_vorgang_datum_entstanden_timestamp");
				delete_post_meta($id, "tf_vorgang_bezug");
				delete_post_meta($id, "tf_vorgang_art");
			}
		}
		
		//Anträge
		$posts = get_posts(array(
			'post_type'	=> 'tf_antrag',
			'posts_per_page' => -1
		));
		
		foreach($posts AS $post)
		{
			$id = $post->ID;
			
			if(get_post_meta($id, "tf_antrag_status", true) != null)
			{
				update_post_meta($id, "tf_antr_anmerkung",					get_post_meta($id, "tf_antrag_beschreibung", true));
				if(get_post_meta($id, "tf_antrag_status", true) == "Eingereicht" || get_post_meta($id, "tf_antrag_status", true) == "Gelesen")
				{
					update_post_meta($id, "tf_antr_status",					"Eingereicht");
				}
				elseif(get_post_meta($id, "tf_antrag_status", true) == "Annahme - Warte auf Beleg")
				{
					update_post_meta($id, "tf_antr_status",					"Angenommen");
				}
				elseif(get_post_meta($id, "tf_antrag_status", true) == "Zurückgezogen")
				{
					update_post_meta($id, "tf_antr_status",					"Zur&uuml;ckgezogen - Antrag aus alter TransFinanz");
				}
				else
				{
					update_post_meta($id, "tf_antr_status",					get_post_meta($id, "tf_antrag_status", true) . " - Antrag aus alter TransFinanz");
				}
				update_post_meta($id, "tf_antr_auszahlung",					get_post_meta($id, "tf_antrag_auszahlung_art", true));
				update_post_meta($id, "tf_antr_datum_entstanden",			get_post_meta($id, "tf_antrag_datum_entstanden_timestamp", true));
				if(is_numeric(get_post_meta($id, "tf_antrag_empfaenger_in", true)))
				{
					update_post_meta($id, "tf_antr_empfaenger_in_id",		get_post_meta($id, "tf_antrag_empfaenger_in", true));
				}
				else
				{
					update_post_meta($id, "tf_antr_empfaenger_in",			get_post_meta($id, "tf_antrag_empfaenger_in", true));
					update_post_meta($id, "tf_antr_empfaenger_in_email",	get_post_meta($id, "tf_antrag_empfaenger_in_email", true));
				}
				update_post_meta($id, "tf_antr_wert",						get_post_meta($id, "tf_antrag_wert", true));
				update_post_meta($id, "tf_antr_spende",						get_post_meta($id, "tf_antrag_spende", true));
				if(get_post_meta($id, "tf_antrag_erstattung", true) == null || get_post_meta($id, "tf_antrag_erstattung", true) == "0")
				{
					update_post_meta($id, "tf_antr_erstattung",				strtodouble(get_post_meta($id, "tf_antrag_wert", true)) - strtodouble(get_post_meta($id, "tf_antrag_spende", true)));
				}
				else
				{
					update_post_meta($id, "tf_antr_erstattung",				get_post_meta($id, "tf_antrag_erstattung", true));
				}

				delete_post_meta($id, "tf_antrag_beschreibung");
				delete_post_meta($id, "tf_antrag_status");
				delete_post_meta($id, "tf_antrag_auszahlung");
				delete_post_meta($id, "tf_antrag_datum_entstanden");
				delete_post_meta($id, "tf_antrag_datum_entstanden_timestamp");
				delete_post_meta($id, "tf_antrag_empfaenger_in");
				delete_post_meta($id, "tf_antrag_empfaenger_in_email");
				delete_post_meta($id, "tf_antrag_wert");
				delete_post_meta($id, "tf_antrag_spende");
				delete_post_meta($id, "tf_antrag_erstattung");
				delete_post_meta($id, "tf_antrag_gq_id");
				delete_post_meta($id, "tf_antrag_knt_id");
				delete_post_meta($id, "tf_antrag_kat_id");
				delete_post_meta($id, "tf_antrag_am_id");
				delete_post_meta($id, "tf_antrag_knt_id_spende");
				delete_post_meta($id, "tf_antrag_kat_id_spende");
			}
		}
		
		$posts = get_posts(array(
			'post_type'	=> 'tf_planung',
			'posts_per_page' => -1
		));
		
		foreach($posts AS $post)
		{
			wp_delete_post($post->ID, true);
		}
	}
	
	function buildQID($datum_entstanden)
	{
		$q_id = get_option('tf_quittungs_template');
		if(get_option('tf_quittungs_template') != str_replace("[jjjj]", "", get_option('tf_quittungs_template')))
		{
			$q_id = str_replace("[jjjj]", Einstellungen::haushaltsjahrAusgabe($datum_entstanden), $q_id);
		}

		if(get_option('tf_quittungs_template') != str_replace("[jj]", "", get_option('tf_quittungs_template')))
		{
			$q_id = str_replace("[jj]", substr(Einstellungen::haushaltsjahrAusgabe($datum_entstanden), -2, 2), $q_id);
		}

		if(get_option('tf_quittungs_template') != str_replace("[n]", "", get_option('tf_quittungs_template')))
		{
			$q_id = str_replace("[n]", Vorgang::getRunningNumber(), $q_id);
		}

		if(get_option('tf_quittungs_template') != str_replace("[nj]", "", get_option('tf_quittungs_template')))
		{
			$q_id = str_replace("[nj]", Vorgang::getRunningYearNumber($datum_entstanden), $q_id);
		}
		
		if(get_option('tf_quittungs_template') != str_replace("[ne]", "", get_option('tf_quittungs_template')))
		{
			$q_id = str_replace("[n]", Vorgang::getRunningNumber("Einnahme"), $q_id);
		}

		if(get_option('tf_quittungs_template') != str_replace("[nje]", "", get_option('tf_quittungs_template')))
		{
			$q_id = str_replace("[nj]", Vorgang::getRunningYearNumber($datum_entstanden, "Einnahme"), $q_id);
		}
		
		if(get_option('tf_quittungs_template') != str_replace("[na]", "", get_option('tf_quittungs_template')))
		{
			$q_id = str_replace("[n]", Vorgang::getRunningNumber("Ausgabe"), $q_id);
		}

		if(get_option('tf_quittungs_template') != str_replace("[nja]", "", get_option('tf_quittungs_template')))
		{
			$q_id = str_replace("[nj]", Vorgang::getRunningYearNumber($datum_entstanden, "Ausgabe"), $q_id);
		}
		
		return $q_id;
	}
	
	function metaNameUpdate($prefix, $post_type, $post_id)
	{
		if($_POST['original_publish'] != "Veröffentlichen")
		{
			$posts = get_posts(array(
				'post_type'			=> 'tf_' . $post_type,
				'post_status'		=> 'publish',
				'meta_key'			=> 'tf_' . $prefix,
				'meta_value'		=> $post_id,
				'posts_per_page'	=> -1
			));

			foreach($posts AS $post)
			{
				update_post_meta($post->ID, 'tf_' . $prefix . '_name', get_the_title($post_id));
			}
		}
	}
	
	function excelDownload()
	{
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=excelexport.csv');
		header( 'Content-Type: text/csv; charset=UTF-8');
		
		if($_GET['tf_excel_export'] == "Vorgang Excel exportieren")
		{
			Vorgang::excelExport();
		}
	}

?>