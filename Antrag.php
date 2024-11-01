<?php

/**
 * Description of Antrag
 *
 * @author KWM
 */
class Antrag extends Postlist
{
	//*************************************************************************
	//********************************* OBJEKT ********************************
	//*************************************************************************
	public $id;
	public $name;
	public $vorg;
	public $vorg_spende;
	public $status;
	public $auszahlung;
	public $anmerkung;
	public $datum_entstanden;
	public $datum_entstanden_dat;
	public $datum_eingereicht;
	public $datum_eingereicht_dat;
	public $empfaenger_in_id;
	public $empfaenger_in;
	public $empfaenger_in_email;
	public $wert;
	public $spende;
	public $erstattung;
	public $erstattung_art;
	
	public function __construct($id) 
	{
		$this->id = $id;
		
		$post = get_post($id);
		
		$this->name = $post->post_title;
		
		$this->vorg			= new Vorgang(get_post_meta($id, "tf_antr_vorg_id",					true));
		$this->vorg_spende	= new Vorgang(get_post_meta($id, "tf_antr_vorg_id_spende",			true));
		$this->status					= get_post_meta($id, "tf_antr_status",					true);
		$this->auszahlung				= get_post_meta($id, "tf_antr_auszahlung",				true);
		$this->anmerkung				= get_post_meta($id, "tf_antr_anmerkung",				true);
		$this->empfaenger_in_id			= get_post_meta($id, "tf_antr_empfaenger_in_id",		true);
		if($this->empfaenger_in_id != null)
		{
			$user = get_userdata($this->empfaenger_in_id);
			$this->empfaenger_in		= $user->user_login;
			$this->empfaenger_in_email	= $user->user_email;
		}
		else
		{
			$this->empfaenger_in		= get_post_meta($id, "tf_antr_empfaenger_in",			true);
			$this->empfaenger_in_email	= get_post_meta($id, "tf_antr_empfaenger_in_email",		true);
		}
		$this->wert						= get_post_meta($id, "tf_antr_wert",					true);
		$this->spende					= get_post_meta($id, "tf_antr_spende",					true);
		$this->erstattung				= get_post_meta($id, "tf_antr_erstattung",				true);
		$this->erstattung_art			= get_post_meta($id, "tf_antr_erstattung_art",			true);
		if($this->erstattung_art == null)
		{
			$this->erstattung_art = "Euro";
		}
		
		$datum_eingereicht = $post->post_date;
		$temp = explode(" ", $datum_eingereicht);
		$datum_eingereicht = $temp[0];
		
		$this->datum_eingereicht		= datetots($datum_eingereicht, "sql");
		$this->datum_eingereicht_dat	= tstodate($this->datum_eingereicht);
		$this->datum_entstanden			= get_post_meta($id, "tf_antr_datum_entstanden",		true);
		$this->datum_entstanden_dat		= tstodate($this->datum_entstanden);
	}
	
	//*************************************************************************
	//******************************* POSTTYPE ********************************
	//*************************************************************************
	private static $post_type = "tf_antrag";
	
	public static function register_antrag()
	{
		parent::register_pt(self::$post_type, "Antrag", "Anträge", "Antrag", "antrag");
	}
	
	//*************************************************************************
	//****************************** POST MASKEN ******************************
	//*************************************************************************
	public static function status($post)
	{
		if(isset($_GET['status']) && $_GET['status'] == "Annahme")
		{
			update_post_meta($post->ID, "tf_antr_status",				"Angenommen");
		}
		elseif(isset($_GET['status']) && $_GET['status'] == "Ablehnung")
		{
			update_post_meta($post->ID, "tf_antr_status",				"Abgelehnt");
		}
	}
	
	public static function daten($post)
	{
		$antrag = new Antrag($post->ID);
		
		$form = new TF_Form(array(
			'form'		=> false,
			'table'		=> true
		));
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'status',
			'beschreibung'	=> 'Status:',
			'anzeige'		=> '<b>' . $antrag->status . '</b>'
		));
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'empfaenger_in',
			'beschreibung'	=> 'Empfänger*in:',
			'anzeige'		=> $antrag->empfaenger_in
		));
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'empfaenger_in_email',
			'beschreibung'	=> 'Empfänger*in E-Mail Adresse:',
			'anzeige'		=> $antrag->empfaenger_in_email
		));
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'wert',
			'beschreibung'	=> 'Antragsgesamtwert:',
			'anzeige'		=> doubletostr($antrag->wert, true)
		));
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'spende',
			'beschreibung'	=> 'Spende:',
			'anzeige'		=> doubletostr($antrag->spende, true)
		));
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'datum_entstanden',
			'beschreibung'	=> 'Datum entstanden:',
			'anzeige'		=> $antrag->datum_entstanden_dat
		));
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'datum_eingereicht',
			'beschreibung'	=> 'Datum eingereicht:',
			'anzeige'		=> $antrag->datum_eingereicht_dat
		));
		
		$dif = $antrag->datum_eingereicht - $antrag->datum_entstanden;
		$dif_days = $dif / 86400;
		$dif_weeks = $dif_days / 7;
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'zeitliche_differenz',
			'beschreibung'	=> 'Zeitliche Differenz:',
			'anzeige'		=> 'Tage: <b>' . $dif_days . '</b><br>Wochen: <b>' . $dif_weeks . '</b>'
		));
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'auszahlung',
			'beschreibung'	=> 'Auszahlung:',
			'anzeige'		=> $antrag->auszahlung
		));
		
		$form->td(array(
			'art'			=> 'anzeige',
			'name'			=> 'anmerkung',
			'beschreibung'	=> 'Anmerkung:',
			'anzeige'		=> $antrag->anmerkung
		));
		
		unset($form);
	}
	
	public static function maske($post)
	{
		if(isset($_SESSION['err_msg'][$post->ID]))
		{
			foreach ($_SESSION['err_msg'][$post->ID] AS $msg)
			{
				echo $msg;
			}
			
			TF_Form::jsHead();
			echo ('			<script>
				$(".updated").hide();
			</script>
');
		}
		
		$antrag = new Antrag($post->ID);
		TF_Form::jsHead();
		if($antrag->status == "Eingereicht" || $antrag->status == "Angenommen")
		{
			wp_nonce_field('tf_vorg', 'tf_vorg_nonce');
			wp_nonce_field('tf_antr', 'tf_antr_nonce');
			$form = new TF_Form(array(
				'form'			=> false,
				'table'			=> true,
				'prefix'		=> 'tf_antr',
				'submit'		=> 'bezahlt',
				'submit_button'	=> true,
				'submit_value'	=> 'Bezahlt'
			));

			if($antrag->erstattung == "")
			{
				$antrag->erstattung = $antrag->wert;
			}

			$form->td_text(array(
				'beschreibung'		=> 'Erstattung:',
				'name'				=> 'erstattung',
				'size'				=> 10,
				'value'				=> doubletostr($antrag->erstattung),
				'checking'			=> array('Fill', 'Number')
			));

			$form->td(array(
				'name'				=> 'Spendenhinweis',
				'spalte'			=> true,
				'art'				=> 'anzeige',
				'anzeige'			=> 'Beachte, das Spenden anteilig der Erstattung berechnet werden. Wird zum Beispiel nur 50% des Gesamtwertes erstattet, werden auch nur 50% deiner angegebenen Spende als solche weiter berücksichtigt.'
			));

			$form->td_radio(array(
				'beschreibung'		=> 'Art der Erstattung:',
				'name'				=> 'erstattung_art',
				'values'			=> array(
					'Prozent'			=> '%',
					'Euro'				=> '&euro;'
				),
				'checked'			=> $antrag->erstattung_art,
				'checking'			=> 'Checked'
			));

			$form->td(array(
				'name'				=> 'hinweis',
				'spalte'			=> true,
				'art'				=> 'anzeige',
				'anzeige'			=> 'Auszahlung: <b><div class="auszahlung">0,00 €</div></b><br>Spende: <b><div class="spende">0,00 €</div></b><br>'
			));

			$form->td_select(array(
				'beschreibung'		=> 'Geldquelle:',
				'name'				=> 'gq',
				'values'			=> Geldquelle::getValue(),
				'selected'			=> $antrag->vorg->geldquelle->id,
				'opt_group'			=> true,
				'erste'				=> true,
				'checking'			=> 'Selection'
			));

			$form->td_select(array(
				'beschreibung'		=> 'Konto:',
				'name'				=> 'knt',
				'values'			=> Konto::getValue("Ausgabe"),
				'selected'			=> $antrag->vorg->konto->id,
				'opt_group'			=> true,
				'erste'				=> true,
				'checking'			=> 'Selection'
			));

			$form->td_select(array(
				'beschreibung'		=> 'Absetzbare Mittel:',
				'name'				=> 'am',
				'values'			=> AbsetzbareMittel::getValue(),
				'selected'			=> $antrag->vorg->absetzbaremittel->id,
				'opt_group'			=> true,
				'erste'				=> true
			));
			
			if(get_option('tf_antraege_spenden') == "Ja" && $antrag->spende != 0)
			{
				$form->td_select(array(
					'beschreibung'		=> 'Konto der Spende:',
					'name'				=> 'knt_spende',
					'values'			=> Konto::getValue("Einnahme"),
					'selected'			=> $antrag->vorg_spende->konto->id,
					'opt_group'			=> true,
					'erste'				=> true,
					'checking'			=> 'Selection'
				));

				$form->td_select(array(
					'beschreibung'		=> 'Absetzbare Mittel der Spende:',
					'name'				=> 'am_spende',
					'values'			=> AbsetzbareMittel::getValue(),
					'selected'			=> $antrag->vorg_spende->absetzbaremittel->id,
					'opt_group'			=> true,
					'erste'				=> true
				));
			}

			if($antrag->status == "Eingereicht")
			{
				$form->td_submit(array(
					'name'				=> 'annahme',
					'value'				=> 'Annehmen'
				));
			}

			$form->td_submit(array(
				'name'				=> 'ablehnung',
				'value'				=> 'Ablehnen'
			));

			TF_Form::jsScript();
			
			echo ('		<script>

				function berechneWerte()
				{
					var erstattung_get;
					var erstattung;
					var art;
					var wert = ' . $antrag->wert . ';
					var spende = ' . $antrag->spende . ';
					var erstattung_wert;
					var erstattung_spende;
					var anzeige

					erstattung_get = document.getElementById("tf_antr_erstattung").value;
					erstattung_get = erstattung_get.replace(",", ".");
					erstattung = parseFloat(erstattung_get);

					if (!$("input:radio[name=tf_antr_erstattung_art]:checked").val())
					{
						art = "Euro";
					}
					else
					{
						art = $("input:radio[name=tf_antr_erstattung_art]:checked").val();
					}

					if(art == "Euro")
					{
						erstattung_spende = (erstattung/wert)*spende;
						erstattung_wert = erstattung-erstattung_spende;
					}
					else
					{
						erstattung_spende = spende*(erstattung/100);
						erstattung_wert = (wert*(erstattung/100))-erstattung_spende;
					}

					$(".auszahlung").html(erstattung_wert.toFixed(2).replace(".", ",") + " &euro;");
					$(".spende").html(erstattung_spende.toFixed(2).replace(".", ",") + " &euro;");
				}

				function berechneWerteCheck()
				{
					if(document.readyState != "complete") {
						window.setTimeout(berechneWerteCheck, 100);
						return false;
					}

					berechneWerte();
				}

				berechneWerteCheck();

				$("#tf_antr_erstattung").change(function()
				{
					berechneWerte();
				});

				$("#tf_antr_erstattung_art_Prozent").change(function()
				{
					berechneWerte();
				});

				$("#tf_antr_erstattung_art_Euro").change(function()
				{
					berechneWerte();
				});

			</script>
');

			unset($form);
		}
		else
		{
			echo ('			Der Antragsstatus lässt keine weitere Bearbeitung mehr zu.
');
		}
		
		echo ('		<script>

				$("#transfinanz_antrag_submit_mb").hide();
				$("#submitdiv").hide();

			</script>
');
		
		parent::wpHide();
	}
	
	public static function frontendMaske()
	{
		if(isset($_POST['tf_antr_submit']))
		{
			wp_insert_post(array(
				'post_title'	=> sanitize_text_field($_POST['tf_antr_name']),
				'post_type'		=> 'tf_antrag',
				'post_status'	=> 'publish'
			));
			
			echo ($_SESSION['antrag_speichert']);
			unset($_SESSION['antrag_speichert']);
		}
		
		if(get_option('tf_antraege_stellen') == "Alle" || (get_option('tf_antraege_stellen') == "Registriert" && get_current_user_id() != 0))
		{
			$form = new TF_Form(array(
				'action'		=> get_current_url(),
				'form'			=> true,
				'table'			=> true,
				'prefix'		=> 'tf_antr',
				'submit_button'	=> true,
				'submit'		=> 'submit',
				'submit_value'	=> 'Antrag stellen'
			));
			
			wp_nonce_field('tf_antr', 'tf_antr_nonce');
			
			if(get_current_user_id() == 0)
			{
				$form->td_text(array(
					'beschreibung'		=> 'Dein Name:',
					'name'				=> 'empfaenger_in',
					'size'				=> 10,
					'checking'			=> 'Fill'
				));
				
				$form->td_text(array(
					'beschreibung'		=> 'Deine E-Mail-Adresse:',
					'name'				=> 'empfaenger_in_email',
					'size'				=> 30,
					'checking'			=> array('Mail', 'Fill')
				));
			}
			
			$form->td_text(array(
				'beschreibung'		=> 'Antrag Kurzbeschreibung:',
				'name'				=> 'name',
				'size'				=> 10,
				'checking'			=> 'Fill'
			));
			
			$form->td_text(array(
				'beschreibung'		=> 'Gesamtwert deines Antrages:',
				'name'				=> 'wert',
				'size'				=> 10,
				'checking'			=> array('Number', 'Fill')
			));
			
			$form->td_text(array(
				'beschreibung'		=> 'Deine Spende:',
				'name'				=> 'spende',
				'size'				=> 10,
				'checking'			=> 'Number'
			));
			
			$form->td(array(
				'name'				=> 'Spendenhinweis',
				'spalte'			=> true,
				'art'				=> 'anzeige',
				'anzeige'			=> 'Beachte, das Spenden anteilig der Erstattung berechnet werden. Wird zum Beispiel nur 50% des Gesamtwertes erstattet, werden auch nur 50% deiner angegebenen Spende als solche weiter berücksichtigt.'
			));
			
			$form->td_text(array(
				'beschreibung'		=> 'Entstanden am:',
				'name'				=> 'datum_entstanden',
				'size'				=> 10,
				'checking'			=> array('Date', 'Fill')
			));
			
			$form->td_radio(array(
				'beschreibung'		=> 'Auszahlung:',
				'name'				=> 'auszahlung',
				'values'			=> array(
					'Bar'				=> 'Bar',
					'Überweisung'		=> '&Uuml;berweisung (Daten nicht hier übermitteln!!!)'
				),
				'checking'			=> 'Checked'
			));
			
			$form->td_textarea(array(
				'anzeige'			=> 'neben',
				'beschreibung'		=> 'Sonstige Anmerkungen',
				'name'				=> 'anmerkung',
				'cols'				=> 20,
				'rows'				=> 5
			));
			
			$form->hidden(array(
				'name'				=> 'location',
				'value'				=> 'frontend'
			));
			
			unset($form);
		}
	}
	
	public static function metabox()
	{
		add_meta_box("transfinanz_antrag_submit_mb", __("SubmitBox", "TransFinanz"), array("Antrag", "status"), "tf_antrag", "normal", "default");
		add_meta_box("transfinanz_antrag_mb", __("Antrag Meta-Daten", "TransFinanz"), array("Antrag", "daten"), "tf_antrag", "side", "default");
		add_meta_box("transfinanz_antrag_form_mb", __("Antrag Formular", "TransFinanz"), array("Antrag", "maske"), "tf_antrag", "normal", "default");
	}
	
	//*************************************************************************
	//*********************************** MENU ********************************
	//*************************************************************************
	public static function AntragMenu()
	{
		if(is_admin())
		{
			remove_submenu_page("edit.php?post_type=tf_antrag", "post-new.php?post_type=tf_antrag");
		}
		
		$posts = get_posts(array(
			'post_type'			=> 'tf_antrag',
			'post_status'		=> 'publish',
			'posts_per_page'	=> -1,
			'meta_query'		=> array(
				array(
					'key'				=> 'tf_antr_status',
					'value'				=> 'Eingereicht',
					'comparsion'		=> '=='
				)
			)
		));
		
		$anzahl = 0;
		foreach($posts AS $post)
		{
			$anzahl++;
		}
		
		global $menu;
		
		foreach ( $menu as $key => $value ) 
		{
			if($menu[$key][2] == "edit.php?post_type=tf_antrag")
			{
				$menu[$key][0] = $menu[$key][0] . ' <span class="update-plugins ' . $anzahl . '"><span class="plugin-count">' . $anzahl . '</span></span>';
				
				return;
			}
		}
	}
	
	//*************************************************************************
	//**************************** POST SPEICHERUNG ***************************
	//*************************************************************************
	public static function save($post_id)
	{
		if(get_post_type($post_id) == "tf_antrag")
		{
			if(!isset($_POST['tf_antr_nonce']))
			{
				return;
			}

			if (!wp_verify_nonce($_POST['tf_antr_nonce'], 'tf_antr')) 
			{
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			{
				return;
			}

			if($_POST['tf_antr_location'] == "frontend")
			{
				if(get_option('tf_antraege_stellen') == "Alle" || (get_option('tf_antraege_stellen') == "Registriert" && get_current_user_id() != 0))
				{
					if(get_current_user_id() == 0)
					{
						update_post_meta($post_id, "tf_antr_empfaenger_in",			sanitize_text_field($_POST['tf_antr_empfaenger_in']));
						update_post_meta($post_id, "tf_antr_empfaenger_in_email",	sanitize_text_field($_POST['tf_antr_empfaenger_in_email']));
					}
					else
					{
						update_post_meta($post_id, "tf_antr_empfaenger_in_id",		get_current_user_id());
					}

					update_post_meta($post_id, "tf_antr_wert",					sanitize_text_field($_POST['tf_antr_wert']));
					update_post_meta($post_id, "tf_antr_spende",				sanitize_text_field($_POST['tf_antr_spende']));
					update_post_meta($post_id, "tf_antr_datum_entstanden",		datetots(sanitize_text_field($_POST['tf_antr_datum_entstanden'])));
					update_post_meta($post_id, "tf_antr_auszahlung",			sanitize_text_field($_POST['tf_antr_auszahlung']));
					update_post_meta($post_id, "tf_antr_anmerkung",				sanitize_text_field($_POST['tf_antr_anmerkung']));
					update_post_meta($post_id, "tf_antr_erstattung",			strtodouble(sanitize_text_field($_POST['tf_antr_wert'])) - strtodouble(sanitize_text_field($_POST['tf_antr_spende'])));
					update_post_meta($post_id, "tf_antr_status",				"Eingereicht");

					$_SESSION['antrag_speichert'] = "Dein Antrag wurde erfolgreich gespeichert.";
				}
			}
			else
			{
				$antrag = new Antrag($post_id);
				
				unset($_SESSION['err_msg'][$post_id]);

				$erstattung_art	= sanitize_text_field($_POST['tf_antr_erstattung_art']);
				$erstattung		= str_replace(",", ".", sanitize_text_field($_POST['tf_antr_erstattung']));

				if($erstattung_art == "Prozent")
				{
					$erstattung = $antrag->wert * ($erstattung/100);
				}

				if($erstattung <= $antrag->wert)
				{
					update_post_meta($post_id, "tf_antr_erstattung",				$erstattung);
					update_post_meta($post_id, "tf_antr_erstattung_art",			$erstattung_art);

					if(isset($_POST['tf_antr_annahme']) && $_POST['tf_antr_annahme'] == "Annehmen")
					{
						update_post_meta($post_id, "tf_antr_status",				"Angenommen");
					}
					elseif(isset($_POST['tf_antr_ablehnung']) && $_POST['tf_antr_ablehnung'] == "Ablehnen")
					{
						update_post_meta($post_id, "tf_antr_status",				"Abgelehnt");
					}
					elseif(isset($_POST['tf_antr_bezahlt']) && $_POST['tf_antr_bezahlt'] == "Bezahlt")
					{
						update_post_meta($post_id, "tf_antr_status",				"Bezahlt");

						$spende		= ($erstattung / $antrag->wert) * $antrag->spende;
						$auszahlung	= $erstattung - $spende;

						$_POST['tf_vorg_wert']				= $auszahlung;
						$_POST['tf_vorg_art']				= "Ausgabe";
						$_POST['tf_vorg_gq']				= $_POST['tf_antr_gq'];
						$_POST['tf_vorg_knt']				= $_POST['tf_antr_knt'];
						$_POST['tf_vorg_am']				= $_POST['tf_antr_am'];
						$_POST['tf_vorg_datum_entstanden']	= $antrag->datum_entstanden_dat;
						$_POST['tf_vorg_empfaenger_in']		= $antrag->empfaenger_in;

						$vorg_id = wp_insert_post(array(
							'post_title'		=> $antrag->name,
							'post_type'			=> 'tf_vorgang',
							'post_status'		=> 'publish'
						));

						update_post_meta($post_id, "tf_antr_vorg_id",				$vorg_id);

						if(get_option('tf_antraege_spende') == 'Ja')
						{
							$_POST['tf_vorg_wert']				= $spende;
							$_POST['tf_vorg_art']				= "Einnahme";
							$_POST['tf_vorg_knt']				= $_POST['tf_antr_knt_spende'];
							$_POST['tf_vorg_am']				= $_POST['tf_antr_am_spende'];
							$_POST['tf_vorg_quelle']			= $antrag->empfaenger_in;

							$vorg_id = wp_insert_post(array(
								'post_title'		=> $antrag->name . " - Spende",
								'post_type'			=> 'tf_vorgang',
								'post_status'		=> 'publish'
							));

							update_post_meta($post_id, "tf_antr_vorg_id_spende",	$vorg_id);
						}
					}
				}
				else
				{
					$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
			<h3>Die Erstattung ist größer, als der Antragswert!</h3>

			Bitte gebe eine gültige Erstattung an.
		</div>
';
				}
			}
		}
	}
	
	//*************************************************************************
	//************************* POST ANZEIGE IN LISTE *************************
	//*************************************************************************
	private static $criterias_key = array(
		'tf_antr_status'		=> 'Status',
		'tf_antr_wert'			=> 'Wert',
		'tf_antr_spende'		=> 'Spende',
		'tf_antr_erstattung'	=> 'Erstattung'
	);

	private static $criterias = array(
		'tf_antr_status',
		array(
			'key'		=> 'tf_antr_wert',
			'value'		=> 'doubletostrwp'
		),
		array(
			'key'		=> 'tf_antr_spende',
			'value'		=> 'doubletostrwp'
		),
		array(
			'key'		=> 'tf_antr_erstattung',
			'value'		=> 'doubletostrwp'
		)
	);
	
	private static $form_prefix = "tf_antr";

	public static function postlist($defaults)
	{
		$values = parent::postlists($defaults, self::$criterias_key);
		
		if(is_admin() && isset($values['date']))
		{
			unset($values['date']);
		}
		
		return $values; 
	}

	public static function postlist_column($column_name, $post_id)
	{
		parent::postlist_column(self::$post_type, $column_name, $post_id, self::$criterias);
	}

	public static function postlist_sorting($columns)
	{
		$values = parent::postlist_sorting($columns, self::$criterias_key);
		
		if(is_admin() && isset($values['date']))
		{
			unset($values['date']);
		}
		
		return $values; 
	}

	public static function postlist_orderby($vars)
	{
		return parent::postlist_orderby(self::$post_type, $vars, self::$criterias_key);
	}

	public static function postlist_filtering()
	{
		if(is_admin())
		{
			$screen = get_current_screen();
			if($screen->post_type == "tf_antrag")
			{
				echo ('<br>');

				$form = new TF_Form(array(
					'form'		=> false,
					'table'		=> false,
					'prefix'	=> 'tf_antr_sort',
					'submit'	=> 'submit'
				));

				echo ('			<table border="0" cellpadding="5" cellspacing="0" width="800">
				<tr>
					<td width="50%" colspan="2"><b>Wert</b></td>
					<td width="50%" colspan="2"><b>Spende</b></td>
				</tr>
				<tr>
					<td width="25%">Start</td>
					<td width="25%">');
				
				TF_Form::jsHead();
				
				if(isset($_GET['tf_antr_sort_wert_start']))
				{
					$value = $_GET['tf_antr_sort_wert_start'];
				}
				$form->text(array(
					'name'		=> 'wert_start',
					'size'		=> 10,
					'checking'	=> 'Number',
					'value'		=> $value
				));

				echo ('</td>
					<td width="25%">Start</td>
					<td width="25%">');
		
				if(isset($_GET['tf_antr_sort_spende_start']))
				{
					$value = $_GET['tf_antr_sort_spende_start'];
				}
				$form->text(array(
					'name'		=> 'spende_start',
					'size'		=> 10,
					'checking'	=> 'Number',
					'value'		=> $value
				));

				echo ('</td>
				</tr>
				<tr>
					<td width="25%">Ende</td>
					<td width="25%">');
		
				if(isset($_GET['tf_antr_sort_wert_ende']))
				{
					$value = $_GET['tf_antr_sort_wert_ende'];
				}
				$form->text(array(
					'name'		=> 'wert_ende',
					'size'		=> 10,
					'checking'	=> 'Number',
					'value'		=> $value
				));

				echo ('</td>
					<td width="25%">Ende</td>
					<td width="25%">');
		
				if(isset($_GET['tf_antr_sort_spende_ende']))
				{
					$value = $_GET['tf_antr_sort_spende_ende'];
				}
				$form->text(array(
					'name'		=> 'spende_ende',
					'size'		=> 10,
					'checking'	=> 'Number',
					'value'		=> $value
				));

				echo ('</td>
				</tr>
				<tr>
					<td width="50%" colspan="2"><b>Erstattung</b></td>
					<td width="25%" rowspan="3">Status</td>
					<td width="25%" rowspan="3">');
				
				global $wpdb;
				$results = $wpdb->get_results("SELECT pm.meta_value FROM " . $wpdb->postmeta . " pm INNER JOIN " . $wpdb->posts . " p ON p.ID = pm.post_id WHERE p.post_type = 'tf_antrag' AND p.post_status = 'publish' AND pm.meta_key = 'tf_antr_status' GROUP BY pm.meta_value");

				unset($values);
				$values[0] = "Alle";
				foreach($results AS $meta_data)
				{
					$values[$meta_data->meta_value] = $meta_data->meta_value;
				}
				if(isset($_GET['tf_antr_sort_status']))
				{
					$value = $_GET['tf_antr_sort_status'];
					foreach($value AS $key)
					{
						$selected['value'][$key] = 1;
					}
				}
				else
				{
					$selected = null;
				}
				$form->select(array(
					'name'			=> 'status',
					'values'		=> $values,
					'multiple'		=> true,
					'size'			=> 5,
					'selected'	=> $selected
				));
				
				echo ('</td>
				</tr>
				<tr>
					<td width="25%">Start</td>
					<td width="25%">');
		
				if(isset($_GET['tf_antr_sort_erstattung_start']))
				{
					$value = $_GET['tf_antr_sort_erstattung_start'];
				}
				$form->text(array(
					'name'		=> 'erstattung_start',
					'size'		=> 10,
					'checking'	=> 'Number',
					'value'		=> $value
				));

				echo ('</td>
				</tr>
				<tr>
					<td width="25%">Ende</td>
					<td width="25%">');
		
				if(isset($_GET['tf_antr_sort_erstattung_ende']))
				{
					$value = $_GET['tf_antr_sort_erstattung_ende'];
				}
				$form->text(array(
					'name'		=> 'erstattung_ende',
					'size'		=> 10,
					'checking'	=> 'Number',
					'value'		=> $value
				));

				echo ('</td>
				</tr>
			</table>
');
				unset($form);
			}
		}
	}

	public static function postlist_filtering_sort($query)
	{
		if(is_admin())
		{
			$screen = get_current_screen();
			if($screen->post_type == "tf_antrag" && $screen->id == "edit-tf_antrag" && $query->query['post_type'] == "tf_antrag")
			{
				$query->query_vars['meta_query'] = array();
				
				if(	(isset($_GET['tf_antr_sort_wert_start']) && $_GET['tf_antr_sort_wert_start'] != "") &&
					(isset($_GET['tf_antr_sort_wert_ende']) && $_GET['tf_antr_sort_wert_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_wert',
						'value'		=> array(
							strtodouble(sanitize_text_field($_GET['tf_antr_sort_wert_start'])),
							strtodouble(sanitize_text_field($_GET['tf_antr_sort_wert_ende']))
						),
						'type'		=> 'numeric',
						'compare'	=> 'BETWEEN'
					)));
				}
				elseif(	(!isset($_GET['tf_antr_sort_wert_start']) || $_GET['tf_antr_sort_wert_start'] == "") &&
						(isset($_GET['tf_antr_sort_wert_ende']) && $_GET['tf_antr_sort_wert_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_wert',
						'value'		=> strtodouble(sanitize_text_field($_GET['tf_antr_sort_wert_ende'])),
						'type'		=> 'numeric',
						'compare'	=> '<='
					)));
				}
				elseif(	(isset($_GET['tf_antr_sort_wert_start']) && $_GET['tf_antr_sort_wert_start'] != "") &&
						(!isset($_GET['tf_antr_sort_wert_ende']) || $_GET['tf_antr_sort_wert_ende'] == "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_wert',
						'value'		=> strtodouble(sanitize_text_field($_GET['tf_antr_sort_wert_start'])),
						'type'		=> 'numeric',
						'compare'	=> '>='
					)));
				}
				
				if(	(isset($_GET['tf_antr_sort_spende_start']) && $_GET['tf_antr_sort_spende_start'] != "") &&
					(isset($_GET['tf_antr_sort_spende_ende']) && $_GET['tf_antr_sort_spende_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_spende',
						'value'		=> array(
							strtodouble(sanitize_text_field($_GET['tf_antr_sort_spende_start'])),
							strtodouble(sanitize_text_field($_GET['tf_antr_sort_spende_ende']))
						),
						'type'		=> 'numeric',
						'compare'	=> 'BETWEEN'
					)));
				}
				elseif(	(!isset($_GET['tf_antr_sort_spende_start']) || $_GET['tf_antr_sort_spende_start'] == "") &&
						(isset($_GET['tf_antr_sort_spende_ende']) && $_GET['tf_antr_sort_spende_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_spende',
						'value'		=> strtodouble(sanitize_text_field($_GET['tf_antr_sort_spende_ende'])),
						'type'		=> 'numeric',
						'compare'	=> '<='
					)));
				}
				elseif(	(isset($_GET['tf_antr_sort_spende_start']) && $_GET['tf_antr_sort_spende_start'] != "") &&
						(!isset($_GET['tf_antr_sort_spende_ende']) || $_GET['tf_antr_sort_spende_ende'] == "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_spende',
						'value'		=> strtodouble(sanitize_text_field($_GET['tf_antr_sort_spende_start'])),
						'type'		=> 'numeric',
						'compare'	=> '>='
					)));
				}
				
				if(	(isset($_GET['tf_antr_sort_erstattung_start']) && $_GET['tf_antr_sort_erstattung_start'] != "") &&
					(isset($_GET['tf_antr_sort_erstattung_ende']) && $_GET['tf_antr_sort_erstattung_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_erstattung',
						'value'		=> array(
							strtodouble(sanitize_text_field($_GET['tf_antr_sort_erstattung_start'])),
							strtodouble(sanitize_text_field($_GET['tf_antr_sort_erstattung_ende']))
						),
						'type'		=> 'numeric',
						'compare'	=> 'BETWEEN'
					)));
				}
				elseif(	(!isset($_GET['tf_antr_sort_erstattung_start']) || $_GET['tf_antr_sort_erstattung_start'] == "") &&
						(isset($_GET['tf_antr_sort_erstattung_ende']) && $_GET['tf_antr_sort_erstattung_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_erstattung',
						'value'		=> strtodouble(sanitize_text_field($_GET['tf_antr_sort_erstattung_ende'])),
						'type'		=> 'numeric',
						'compare'	=> '<='
					)));
				}
				elseif(	(isset($_GET['tf_antr_sort_erstattung_start']) && $_GET['tf_antr_sort_erstattung_start'] != "") &&
						(!isset($_GET['tf_antr_sort_erstattung_ende']) || $_GET['tf_antr_sort_erstattung_ende'] == "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_erstattung',
						'value'		=> strtodouble(sanitize_text_field($_GET['tf_antr_sort_erstattung_start'])),
						'type'		=> 'numeric',
						'compare'	=> '>='
					)));
				}
				
				if(isset($_GET['tf_antr_sort_status']))
				{
					$zaehler = 0;
					foreach($_GET['tf_antr_sort_status'] AS $status)
					{
						$stati[$zaehler] = sanitize_text_field($status);

						$zaehler++;
					}

					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_antr_status',
						'value'		=> $stati,
						'compare'	=> 'IN'
					)));
				}
			}
		}
		
		return $query;
	}
	
	public static function postlist_options($actions, $post)
	{
		$actions = parent::postlist_options($actions, $post, self::$post_type);
		
		$trash = $actions['trash'];
		
		unset($actions['trash']);
		
		$antrag = new Antrag($post->ID);
		if($antrag->status == "Eingereicht" || $antrag->status == "Angenommen")
		{
			$actions['annahme'] = '<a href="post.php?post=' . $post->ID . '&action=edit&status=Annahme">Annehmen</a>';
			$actions['ablehnung'] = '<a href="post.php?post=' . $post->ID . '&action=edit&status=Ablehnung">Ablehnen</a>';
			$actions['bezahlen'] = '<a href="post.php?post=' . $post->ID . '&action=edit">Bezahlen</a>';
		}
		
		$actions['trash'] = $trash;
		
		return $actions;
	}
}

?>