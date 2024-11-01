<?php

/**
 * Description of Vorgang
 *
 * @author KWM
 */
class Vorgang extends Postlist
{
	//*************************************************************************
	//********************************* OBJEKT ********************************
	//*************************************************************************
	public $id;
	public $q_id;
	public $name;
	public $geldquelle;
	public $konto;
	public $absetzbaremittel;
	public $art;
	public $datum_entstanden;
	public $datum_entstanden_dat;
	public $wert;
	public $quelle;
	public $empfaenger_in;
	public $umbuch_id;
	
	public function __construct($id)
	{
		$this->id = $id;
		
		$post = get_post($id);
		
		$this->name					= $post->post_title;
		$this->q_id					= get_post_meta($id, "tf_vorg_q", true);
		$this->geldquelle			= new Geldquelle(get_post_meta($id, "tf_vorg_gq", true), false);
		$this->konto				= new Konto(get_post_meta($id, "tf_vorg_knt", true), false);
		$this->absetzbaremittel		= new AbsetzbareMittel(get_post_meta($id, "tf_vorg_am", true), false);
		$this->art					= get_post_meta($id, "tf_vorg_art", true);
		$this->datum_entstanden		= get_post_meta($id, "tf_vorg_datum_entstanden", true);
		$this->datum_entstanden_dat	= tstodate($this->datum_entstanden);
		$this->wert					= get_post_meta($id, "tf_vorg_wert", true);
		$this->quelle				= get_post_meta($id, "tf_vorg_quelle", true);
		$this->empfaenger_in		= get_post_meta($id, "tf_vorg_empfaenger_in", true);
		$this->umbuch_id			= get_post_meta($id, "tf_vorg_umbuch_id", true);
	}
	
	//*************************************************************************
	//******************************* POSTTYPE ********************************
	//*************************************************************************
	private static $post_type = "tf_vorgang";
	
	public static function register_vorgang()
	{
		parent::register_pt(self::$post_type, "Vorgang", "Vorgänge", "Vorgang", "vorgang");
	}
	
	//*************************************************************************
	//****************************** POST MASKEN ******************************
	//*************************************************************************
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
		
		$art = "";
		if(get_post_meta($post->ID, 'tf_vorg_art', true) != null)
		{
			$art = get_post_meta($post->ID, 'tf_vorg_art', true);
		}
		
		if($_GET['vorgang_art'] == "Einnahme" || (isset($_POST['tf_vorg_art']) && $_POST['tf_vorg_art'] == "Einnahme") || $art == "Einnahme")
		{
			$art = "Einnahme";
		}
		elseif($_GET['vorgang_art'] == "Ausgabe" || (isset($_POST['tf_vorg_art']) && $_POST['tf_vorg_art'] == "Ausgabe") || $art == "Ausgabe")
		{
			$art = "Ausgabe";
		}
		elseif($_GET['vorgang_art'] == "Umbuchung" || (isset($_POST['tf_vorg_art']) && $_POST['tf_vorg_art'] == "Umbuchung") || $art == "Ausgehende Umbuchung" || $art == "Eingehende Umbuchung")
		{
			$art = "Umbuchung";
		}
		
		wp_nonce_field('tf_vorg', 'tf_vorg_nonce');
		$form = new TF_Form(array(
			'form'		=> false,
			'table'		=> true,
			'prefix'	=> 'tf_vorg',
			'submit'	=> 'pt'
		));

		$vorgang = new Vorgang($post->ID);
		if($vorgang->art == "Eingehende Umbuchung" || $vorgang->art == "Ausgehende Umbuchung")
		{
			if($vorgang->art != "Ausgehende Umbuchung")
			{
				$vorgang2 = $vorgang;
				$vorgang = new Vorgang($vorgang2->umbuch_id);
			}
			else
			{
				$vorgang2 = new Vorgang($vorgang->umbuch_id);
			}
		}
		
		$form->td_text(array(
			'beschreibung'		=> 'Wert:',
			'name'				=> 'wert',
			'value'				=> doubletostr($vorgang->wert),
			'checking'			=> array('Fill', 'Number')
		));
		
		$form->td_text(array(
			'beschreibung'		=> 'Entstanden am (TT.MM.JJJJ):',
			'name'				=> 'datum_entstanden',
			'value'				=> $vorgang->datum_entstanden_dat,
			'checking'			=> array('Fill', 'Date')
		));
		
		if($art == "Einnahme")
		{
			$values_gq = Geldquelle::getValue(false);
			
			$values_knt = Konto::getValue("Einnahme");
		}
		else
		{
			$values_gq = Geldquelle::getValue();
			
			$values_knt = Konto::getValue("Ausgabe");
		}
		
		$values_am = AbsetzbareMittel::getValue();
		
		if($art == "Einnahme" || $art == "Ausgabe")
		{
			$form->td_select(array(
				'beschreibung'			=> 'Geldquelle:',
				'name'					=> 'gq',
				'values'				=> $values_gq,
				'opt_group'				=> true,
				'selected'				=> $vorgang->geldquelle->id,
				'erste'					=> true,
				'checking'				=> 'Selection'
			));
			
			$form->td_select(array(
				'beschreibung'			=> 'Konto:',
				'name'					=> 'knt',
				'values'				=> $values_knt,
				'opt_group'				=> true,
				'selected'				=> $vorgang->konto->id,
				'erste'					=> true,
				'checking'				=> 'Selection'
			));
			
			$form->td_select(array(
				'beschreibung'			=> 'Absetzbare Mittel:',
				'name'					=> 'am',
				'values'				=> $values_am,
				'selected'				=> $vorgang->absetzbaremittel->id,
				'erste'					=> true
			));
			
			if($art == "Einnahme")
			{
				$form->td_text(array(
					'beschreibung'			=> 'Quelle:',
					'name'					=> 'quelle',
					'value'					=> $vorgang->quelle
				));
				
				$form->hidden(array(
					'name'		=> 'art',
					'value'		=> 'Einnahme'
				));
			}
			elseif($art == "Ausgabe")
			{
				$form->td_text(array(
					'beschreibung'			=> 'Empfänger*in:',
					'name'					=> 'empfaenger_in',
					'value'					=> $vorgang->empfaenger_in
				));
				
				$form->hidden(array(
					'name'		=> 'art',
					'value'		=> 'Ausgabe'
				));
			}
		}
		elseif($art == "Umbuchung")
		{
			$values_gq_empfangend = Geldquelle::getValue(false);
			
			$form->td_select(array(
				'beschreibung'			=> 'Sendende Geldquelle:',
				'name'					=> 'gq_sendend',
				'values'				=> $values_gq,
				'opt_group'				=> true,
				'selected'				=> $vorgang->geldquelle->id,
				'erste'					=> true,
				'checking'				=> 'Selection'
			));
			
			$form->td_select(array(
				'beschreibung'			=> 'Sendende Absetzbare Mittel:',
				'name'					=> 'am_sendend',
				'values'				=> $values_am,
				'selected'				=> $vorgang->absetzbaremittel->id,
				'erste'					=> true
			));
			
			$form->td_select(array(
				'beschreibung'			=> 'Empfangende Geldquelle:',
				'name'					=> 'gq_empfangend',
				'values'				=> $values_gq_empfangend,
				'opt_group'				=> true,
				'selected'				=> $vorgang2->geldquelle->id,
				'erste'					=> true,
				'checking'				=> 'Selection'
			));
			
			$form->td_select(array(
				'beschreibung'			=> 'Empfangende Absetzbare Mittel:',
				'name'					=> 'am_empfangend',
				'values'				=> $values_am,
				'selected'				=> $vorgang->absetzbaremittel->id,
				'erste'					=> true
			));
			
			$form->hidden(array(
				'name'		=> 'art',
				'value'		=> 'Umbuchung'
			));
		}

		TF_Form::jsHead();
		TF_Form::jsScript();
		parent::wpHide();
		
		unset($form);
	}
	
	public static function metabox()
	{
		add_meta_box("transfinanz_vorgang_mb", __("Vorgänge Meta-Daten", "TransFinanz"), array("Vorgang", "maske"), "tf_vorgang", "normal", "default");
	}
	
	//*************************************************************************
	//*********************************** MENU ********************************
	//*************************************************************************
	public static function VorgangMenu()
	{
		if(is_admin())
		{
			add_submenu_page("edit.php?post_type=tf_vorgang", "Einnahme", "Einnahme", 1, "post-new.php?post_type=tf_vorgang&vorgang_art=Einnahme");
			add_submenu_page("edit.php?post_type=tf_vorgang", "Ausgabe", "Ausgabe", 1, "post-new.php?post_type=tf_vorgang&vorgang_art=Ausgabe");
			add_submenu_page("edit.php?post_type=tf_vorgang", "Umbuchung", "Umbuchung", 1, "post-new.php?post_type=tf_vorgang&vorgang_art=Umbuchung");
			remove_submenu_page("edit.php?post_type=tf_vorgang", "post-new.php?post_type=tf_vorgang");
		}
	}
	
	//*************************************************************************
	//**************************** POST SPEICHERUNG ***************************
	//*************************************************************************
	public static function save($post_id)
	{
		if(get_post_type($post_id) == "tf_vorgang")
		{
			if(!isset($_POST['tf_vorg_nonce']))
			{
				return;
			}

			if (!wp_verify_nonce($_POST['tf_vorg_nonce'], 'tf_vorg')) 
			{
				return;
			}

			unset($_POST['tf_vorg_nonce']);

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			{
				return;
			}
			
			$pruef = true;
			$ausgabe = false;
			if($_POST['tf_vorg_art'] == "Ausgabe")
			{
				$pruef = "Ausgabe";
				$gq		= new Geldquelle(sanitize_text_field($_POST['tf_vorg_gq']), false);
				$am		= new AbsetzbareMittel(sanitize_text_field($_POST['tf_vorg_am']), false);
				$ausgabe = true;
			}
			elseif($_POST['tf_vorg_art'] == "Umbuchung")
			{
				$gq		= new Geldquelle(sanitize_text_field($_POST['tf_vorg_gq_sendend']), false);
				$am		= new AbsetzbareMittel(sanitize_text_field($_POST['tf_vorg_am_sendend']), false);
				$ausgabe = true;
			}

			if(isset($_SESSION['err_msg']))
			{
				unset($_SESSION['err_msg']);
			}
			$_POST['tf_vorg_wert'] = str_replace(",", ".", $_POST['tf_vorg_wert']);
			$pruef = self::isWert($_POST['tf_vorg_wert'], $pruef, $post_id);
			$pruef = self::isDate($_POST['tf_vorg_datum_entstanden'], $pruef, $post_id);
			$pruef = self::issetGeldquelle($pruef);
			if($_POST['tf_vorg_art'] != "Umbuchung")
			{
				$pruef = self::issetKonto($pruef, $post_id);
			}
			$pruef = self::datumZukunft($_POST['tf_vorg_datum_entstanden'], $pruef, $post_id);
			$pruef = self::inErfassung($_POST['tf_vorg_datum_entstanden'], $pruef, $post_id);
			if($ausgabe == true)
			{
				$pruef = self::isBudgetAM($gq, $am, $pruef, $post_id);
				$pruef = self::gqGenugGeld($gq, $_POST['tf_vorg_datum_entstanden'], $_POST['tf_vorg_wert'], $pruef, $post_id);
				$pruef = self::amGenugGeld($am, $_POST['tf_vorg_datum_entstanden'], $_POST['tf_vorg_wert'], $pruef, $post_id);
				$pruef = self::inZeitbudget($gq, $_POST['tf_vorg_datum_entstanden'], $pruef, $post_id);
			}

			if($pruef == false)
			{
				wp_update_post(array(
					'ID'			=> $post_id,
					'post_status'	=> 'draft'
				));
			}
			
			if(get_post_meta($post_id, "tf_vorgang_q", true) == null)
			{
				update_post_meta($post_id, "tf_vorg_q",				buildQID(sanitize_text_field(datetots($_POST['tf_vorg_datum_entstanden']))));
			}
			
			update_post_meta($post_id, "tf_vorg_wert",				sanitize_text_field($_POST['tf_vorg_wert']));
			update_post_meta($post_id, "tf_vorg_datum_entstanden",	sanitize_text_field(datetots($_POST['tf_vorg_datum_entstanden'])));

			if($_POST['tf_vorg_art'] == "Einnahme" || $_POST['tf_vorg_art'] == "Ausgabe")
			{
				$gq = new Geldquelle(sanitize_text_field($_POST['tf_vorg_gq']), false);
				update_post_meta($post_id, "tf_vorg_gq",		$gq->id);
				update_post_meta($post_id, "tf_vorg_gq_name",	$gq->name);
				
				$knt = new Konto(sanitize_text_field($_POST['tf_vorg_knt']), false);
				update_post_meta($post_id, "tf_vorg_knt",		$knt->id);
				update_post_meta($post_id, "tf_vorg_knt_name",	$knt->name);
				
				$am = new AbsetzbareMittel(sanitize_text_field($_POST['tf_vorg_am']), false);
				update_post_meta($post_id, "tf_vorg_am",		$am->id);
				update_post_meta($post_id, "tf_vorg_am_name",	$am->name);

				if($_POST['tf_vorg_art'] == "Einnahme")
				{
					update_post_meta($post_id, "tf_vorg_quelle",		sanitize_text_field($_POST['tf_vorg_quelle']));
					update_post_meta($post_id, "tf_vorg_art",			"Einnahme");
					update_post_meta($post_id, "tf_vorg_wert_sign",		sanitize_text_field($_POST['tf_vorg_wert']));
				}
				elseif($_POST['tf_vorg_art'] == "Ausgabe")
				{
					update_post_meta($post_id, "tf_vorg_empfaenger_in",	sanitize_text_field($_POST['tf_vorg_empfaenger_in']));
					update_post_meta($post_id, "tf_vorg_art",			"Ausgabe");
					update_post_meta($post_id, "tf_vorg_wert_sign",		"-" . sanitize_text_field($_POST['tf_vorg_wert']));
				}
			}
			elseif($_POST['tf_vorg_art'] == "Umbuchung") 
			{
				if($_POST['original_publish'] == "Veröffentlichen")
				{
					$post_id_eingehend = wp_insert_post(array(
						'post_title'	=> sanitize_text_field($_POST['post_title']),
						'post_type'		=> 'tf_vorgang',
						'post_status'	=> 'publish'
					));
					
					update_post_meta($post_id_eingehend, "tf_vorg_art",					"Eingehende Umbuchung");
					update_post_meta($post_id,			 "tf_vorg_art",					"Ausgehende Umbuchung");
					update_post_meta($post_id_eingehend, "tf_vorg_umbuch_id",			$post_id);
					update_post_meta($post_id,			 "tf_vorg_umbuch_id",			$post_id_eingehend);
				}
				else
				{
					$post_id_eingehend = get_post_meta($post_id, "tf_vorg_umbuch_id", true);
				}
				
				update_post_meta($post_id_eingehend,	"tf_vorg_wert",					sanitize_text_field($_POST['tf_vorg_wert']));
				update_post_meta($post_id_eingehend,	"tf_vorg_datum_entstanden",		sanitize_text_field($_POST['tf_vorg_datum_entstanden']));
				
				update_post_meta($post_id,				"tf_vorg_wert_sign",			"-" . sanitize_text_field($_POST['tf_vorg_wert']));
				update_post_meta($post_id_eingehend,	"tf_vorg_wert_sign",			sanitize_text_field($_POST['tf_vorg_wert']));
				
				$gq = new Geldquelle(sanitize_text_field($_POST['tf_vorg_gq_sendend']), false);
				update_post_meta($post_id,				"tf_vorg_gq",					$gq->id);
				update_post_meta($post_id,				"tf_vorg_gq_name",				$gq->name);
				
				$am = new AbsetzbareMittel(sanitize_text_field($_POST['tf_vorg_am_sendend']), false);
				update_post_meta($post_id,				"tf_vorg_am",					$am->id);
				update_post_meta($post_id,				"tf_vorg_am_name",				$am->name);

				$gq = new Geldquelle(sanitize_text_field($_POST['tf_vorg_gq_empfangend']), false);
				update_post_meta($post_id_eingehend,	"tf_vorg_gq",					$gq->id);
				update_post_meta($post_id_eingehend,	"tf_vorg_gq_name",				$gq->name);

				$am = new AbsetzbareMittel(sanitize_text_field($_POST['tf_vorg_am_empfangend']), false);
				update_post_meta($post_id_eingehend,	"tf_vorg_am",					$am->id);
				update_post_meta($post_id_eingehend,	"tf_vorg_am_name",				$am->name);
			}
		}
	}
	
	public static function trash($post_id)
	{
		if(get_post_type($post_id) == "tf_vorgang")
		{
			$vorgang = new Vorgang($post_id);
			
			if(($vorgang->art == "Eingehende Umbuchung" || $vorgang->art == "Ausgehende Umbuchung") && get_post_status($vorgang->umbuch_id) != "trash")
			{
				wp_trash_post($vorgang->umbuch_id);
			}
		}
	}
	
	public static function untrash($post_id)
	{
		if(get_post_type($post_id) == "tf_vorgang")
		{
			$vorgang = new Vorgang($post_id);
			
			if(($vorgang->art == "Eingehende Umbuchung" || $vorgang->art == "Ausgehende Umbuchung") && get_post_status($vorgang->umbuch_id) != "publish")
			{
				wp_untrash_post($vorgang->umbuch_id);
			}
		}
	}
	
	public static function delete($post_id)
	{
		if(get_post_type($post_id) == "tf_vorgang" && !isset($_SESSION['umbuchung_delete']))
		{
			$vorgang = new Vorgang($post_id);
			
			if($vorgang->art == "Eingehende Umbuchung" || $vorgang->art == "Ausgehende Umbuchung")
			{
				$_SESSION['umbuchung_delete'] = $post_id;
				wp_delete_post($vorgang->umbuch_id, true);
				unset($_SESSION['umbuchung_delete']);
			}
		}
	}
	
	//*************************************************************************
	//************************* POST ANZEIGE IN LISTE *************************
	//*************************************************************************
	public static $criterias_key = array(
		'tf_vorg_datum_entstanden'	=> 'Entstanden',
		'tf_vorg_q'					=> 'Quittung',
		'tf_vorg_gq_name'			=> 'Geldquelle',
		'tf_vorg_knt_name'			=> 'Konto',
		'tf_vorg_am_name'			=> 'Absetzbare-Mittel',
		'tf_vorg_wert_sign'			=> 'Wert'
	);

	public static $criterias = array(
		array(
			'key'	=> 'tf_vorg_datum_entstanden',
			'value'	=> 'date'
		),
		'tf_vorg_q',
		'tf_vorg_gq_name',
		'tf_vorg_knt_name',
		'tf_vorg_am_name',
		array (
			'key'	=> 'tf_vorg_wert_sign',
			'value'	=> 'doubletostr'
		)
	);
	
	private static $form_prefix = "tf_vorg";

	public static function postlist($defaults)
	{
		if(is_admin())
		{
			$defaults = parent::postlists($defaults, self::$criterias_key);
			if(count(AbsetzbareMittel::getValue()) == 0)
			{
				unset($defaults['tf_vorg_am_name']);
			}

			unset($defaults['date']);

			return $defaults;
		}
	}

	public static function postlist_column($column_name, $post_id)
	{
		parent::postlist_column(self::$post_type, $column_name, $post_id, self::$criterias);
	}

	public static function postlist_sorting($columns)
	{
		if(is_admin())
		{
			$columns = parent::postlist_sorting($columns, self::$criterias_key);

			if(count(AbsetzbareMittel::getValue()) == 0)
			{
				unset($columns['tf_vorg_am']);
			}

			unset($columns['date']);

			return $columns;
		}
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
			if($screen->post_type == "tf_vorgang")
			{
				if(isset($_SESSION['meta_query']))
				{
					$posts = get_posts(array(
						'post_type'			=> 'tf_vorgang',
						'posts_per_page'	=> -1,
						'meta_query'		=> $_SESSION['meta_query']
					));

					unset($_SESSION['meta_query']);

					$_SESSION['ids'] = array();
					foreach($posts AS $post)
					{
						array_push($_SESSION['ids'], $post->ID);
					}
				}

				echo ('<br>');

				$form = new TF_Form(array(
					'form'		=> false,
					'table'		=> false,
					'prefix'	=> 'tf_vorg_sort',
					'submit'	=> 'submit'
				));

				echo ('			<table border="0" cellpadding="5" cellspacing="0" width="800">
				<tr>
					<td width="50%" colspan="2"><b>Wert</b></td>
					<td width="50%" colspan="2"><b>Datum Entstanden</b></td>
				</tr>
				<tr>
					<td width="25%">Start</td>
					<td width="25%">');
		
				TF_Form::jsHead();

				if(isset($_GET['tf_vorg_sort_wert_start']))
				{
					$value = $_GET['tf_vorg_sort_wert_start'];
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
		
				if(isset($_GET['tf_vorg_sort_datum_start']))
				{
					$value = $_GET['tf_vorg_sort_datum_start'];
				}
				$form->text(array(
					'name'		=> 'datum_start',
					'size'		=> 10,
					'checking'	=> 'Date',
					'value'		=> $value
				));

				echo ('</td>
				</tr>
				<tr>
					<td width="25%">Ende</td>
					<td width="25%">');
		
				if(isset($_GET['tf_vorg_sort_wert_ende']))
				{
					$value = $_GET['tf_vorg_sort_wert_ende'];
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
		
				if(isset($_GET['tf_vorg_sort_datum_ende']))
				{
					$value = $_GET['tf_vorg_sort_datum_ende'];
				}
				$form->text(array(
					'name'		=> 'datum_ende',
					'size'		=> 10,
					'checking'	=> 'Number',
					'value'		=> $value
				));

				echo ('</td>
				</tr>
				<tr>
					<td width="25%"><b>Geldquelle</b></td>
					<td width="25%">');
		
				if(isset($_GET['tf_vorg_sort_gq']))
				{
					$value = $_GET['tf_vorg_sort_gq'];
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
					'name'		=> 'gq',
					'values'	=> Geldquelle::getValue(),
					'multiple'	=> true,
					'size'		=> 5,
					'selected'	=> $selected,
					'opt_group'	=> true
				));

				echo ('</td>
					<td width="25%"><b>Konto</b></td>
					<td width="25%">');
		
				if(isset($_GET['tf_vorg_sort_knt']))
				{
					$value = $_GET['tf_vorg_sort_knt'];
					foreach($value AS $key)
					{
						$selected['value'][$key] = 1;
					}
				}
				else
				{
					$selected = null;
				}
				$values['Einnahme'] = Konto::getValue('Einnahme');
				$values['Ausgabe'] = Konto::getValue('Ausgabe');
				$form->select(array(
					'name'		=> 'knt',
					'values'	=> $values,
					'multiple'	=> true,
					'size'		=> 5,
					'selected'	=> $selected,
					'opt_group'	=> true
				));

				echo ('</td>
				</tr>
				<tr>
					<td width="25%"><b>Art</b></td>
					<td width="25%">');
		
				if(isset($_GET['tf_vorg_sort_art']))
				{
					$value = $_GET['tf_vorg_sort_art'];
					foreach($value AS $key)
					{
						$selected['value'][$key] = 1;
					}
				}
				else
				{
					$selected = null;
				}
				$values = array(
					'Ausgabe'				=> 'Ausgabe',
					'Ausgehende Umbuchung'	=> 'Ausgehende Umbuchung',
					'Einnahme'				=> 'Einnahme',
					'Eingehende Umbuchung'	=> 'Eingehende Umbuchung'
				);
				$form->select(array(
					'name'		=> 'art',
					'values'	=> $values,
					'multiple'	=> true,
					'size'		=> 5,
					'selected'	=> $selected
				));

				echo ('</td>
');
				if(count(AbsetzbareMittel::getValue()) != 0)
				{
					echo ('					<td width="25%"><b>Absetzbare Mittel</b></td>
					<td width="25%">');
		
					if(isset($_GET['tf_vorg_sort_am']))
					{
						$value = $_GET['tf_vorg_sort_am'];
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
						'name'		=> 'am',
						'values'	=> AbsetzbareMittel::getValue(),
						'multiple'	=> true,
						'size'		=> 5,
						'selected'	=> $selected
					));

					echo ('</td>
');
				}
				else
				{
					echo ('					<td width="50%" colspan="2"></td>
');
				}

				echo ('				</tr>
			</table>
');
		
				$form->submit(array(
					'name'		=> 'tf_excel_export',
					'value'		=> 'Vorgang Excel exportieren'
				));

				TF_Form::jsScript();

				unset($form);
			}
		}
	}

	public static function postlist_filtering_sort($query)
	{
		if(is_admin())
		{
			$screen = get_current_screen();
			if($screen->post_type == "tf_vorgang" && $screen->id == "edit-tf_vorgang" && $query->query['post_type'] == "tf_vorgang")
			{
				$query->query_vars['meta_query'] = array();

				if(	(isset($_GET['tf_vorg_sort_wert_start']) && $_GET['tf_vorg_sort_wert_start'] != "") &&
					(isset($_GET['tf_vorg_sort_wert_ende']) && $_GET['tf_vorg_sort_wert_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_wert_sign',
						'value'		=> array(
							strtodouble(sanitize_text_field($_GET['tf_vorg_sort_wert_start'])),
							strtodouble(sanitize_text_field($_GET['tf_vorg_sort_wert_ende']))
						),
						'type'		=> 'numeric',
						'compare'	=> 'BETWEEN'
					)));
				}
				elseif(	(!isset($_GET['tf_vorg_sort_wert_start']) || $_GET['tf_vorg_sort_wert_start'] == "") &&
						(isset($_GET['tf_vorg_sort_wert_ende']) && $_GET['tf_vorg_sort_wert_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_wert_sign',
						'value'		=> strtodouble(sanitize_text_field($_GET['tf_vorg_sort_wert_ende'])),
						'type'		=> 'numeric',
						'compare'	=> '<='
					)));
				}
				elseif(	(isset($_GET['tf_vorg_sort_wert_start']) && $_GET['tf_vorg_sort_wert_start'] != "") &&
						(!isset($_GET['tf_vorg_sort_wert_ende']) || $_GET['tf_vorg_sort_wert_ende'] == "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_wert_sign',
						'value'		=> strtodouble(sanitize_text_field($_GET['tf_vorg_sort_wert_start'])),
						'type'		=> 'numeric',
						'compare'	=> '>='
					)));
				}

				if(	(isset($_GET['tf_vorg_sort_datum_start']) && $_GET['tf_vorg_sort_datum_start'] != "") &&
					(isset($_GET['tf_vorg_sort_datum_ende']) && $_GET['tf_vorg_sort_datum_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_datum_entstanden',
						'value'		=> array(
							datetots(sanitize_text_field($_GET['tf_vorg_sort_datum_start'])),
							datetots(sanitize_text_field($_GET['tf_vorg_sort_datum_ende']))
						),
						'type'		=> 'numeric',
						'compare'	=> 'BETWEEN'
					)));
				}
				elseif(	(!isset($_GET['tf_vorg_sort_datum_start']) || $_GET['tf_vorg_sort_datum_start'] == "") &&
						(isset($_GET['tf_vorg_sort_datum_ende']) && $_GET['tf_vorg_sort_datum_ende'] != "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_datum_entstanden',
						'value'		=> datetots(sanitize_text_field($_GET['tf_vorg_sort_datum_ende'])),
						'type'		=> 'numeric',
						'compare'	=> '<='
					)));
				}
				elseif(	(isset($_GET['tf_vorg_sort_datum_start']) && $_GET['tf_vorg_sort_datum_start'] != "") &&
						(!isset($_GET['tf_vorg_sort_datum_ende']) || $_GET['tf_vorg_sort_datum_ende'] == "")
				)
				{
					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_datum_entstanden',
						'value'		=> strtodouble(sanitize_text_field($_GET['tf_vorg_sort_datum_start'])),
						'type'		=> 'numeric',
						'compare'	=> '>='
					)));
				}

				if(isset($_GET['tf_vorg_sort_art']))
				{
					$zaehler = 0;
					foreach($_GET['tf_vorg_sort_art'] AS $art)
					{
						$arten[$zaehler] = sanitize_text_field($art);

						$zaehler++;
					}

					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_art',
						'value'		=> $arten,
						'compare'	=> 'IN'
					)));
				}

				if(isset($_GET['tf_vorg_sort_gq']))
				{
					$zaehler = 0;
					foreach($_GET['tf_vorg_sort_gq'] AS $gq_id)
					{
						$gq_ids[$zaehler] = sanitize_text_field($gq_id);

						$zaehler++;
					}

					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_gq',
						'value'		=> $gq_ids,
						'compare'	=> 'IN'
					)));
				}

				if(isset($_GET['tf_vorg_sort_knt']))
				{
					$zaehler = 0;
					foreach($_GET['tf_vorg_sort_knt'] AS $knt_id)
					{
						$knt_ids[$zaehler] = sanitize_text_field($knt_id);

						$zaehler++;
					}

					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_knt',
						'value'		=> $knt_ids,
						'compare'	=> 'IN'
					)));
				}

				if(isset($_GET['tf_vorg_sort_am']))
				{
					$zaehler = 0;
					foreach($_GET['tf_vorg_sort_am'] AS $am_id)
					{
						$am_ids[$zaehler] = sanitize_text_field($am_id);

						$zaehler++;
					}

					$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
						'key'		=> 'tf_vorg_am',
						'value'		=> $am_ids,
						'compare'	=> 'IN'
					)));
				}

				unset($_SESSION['ids']);
				$_SESSION['meta_query'] = $query->query_vars['meta_query'];
			}
		}
		
		return $query;
	}
	
	public static function postlist_options($actions, $page)
	{
		return parent::postlist_options($actions, $page, self::$post_type);
	}
	
	//*************************************************************************
	//**************************** HILFSFUNKTIONEN ****************************
	//*************************************************************************
	public static function datumZukunft($date, $return, $post_id)
	{
		$datum_entstanden = explode(".", sanitize_text_field($date));
		
		if(	$datum_entstanden[2] > date('Y') ||
			($datum_entstanden[2] == date('Y') && $datum_entstanden[1] > date('n')) ||
			($datum_entstanden[2] == date('Y') && $datum_entstanden[1] == date('n') && $datum_entstanden[0] > date('j'))
		)
		{
			$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Das angegebene Datum liegt in der Zukunft.</h3>

			Bitte gebe ein gültiges Datum an.
			</div>
';
			
			return false;
		}
		
		return $return;
	}
	
	public static function inErfassung($date, $return, $post_id)
	{
		$datum_entstanden = explode(".", sanitize_text_field($date));
		
		$erfassung_start = get_option('tf_erfassung_start');
		$haushalt_start = get_option('tf_haushalts_jahr');
		$haushalt_start = explode(".", $haushalt_start);
		
		if(	$datum_entstanden[2] < $erfassung_start ||
			($datum_entstanden[2] == $erfassung_start && $datum_entstanden[1] < $haushalt_start[1]) ||
			($datum_entstanden[2] == $erfassung_start && $datum_entstanden[1] == $haushalt_start[1] && $datum_entstanden[0] < $haushalt_start[0])
		)
		{
			$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Das angegebene Datum liegt nicht in der Erfassung der TransFinanz.</h3>

			Bitte melde dich bei deiner Schatzmeisterei.
			</div>
';
			
			return false;
		}
		
		return $return;
	}
	
	public static function inZeitbudget($gq, $date, $return, $post_id)
	{
		if($gq->art == "Zeitbudget" && $gq->zeitbudget_start >= datetots(sanitize_text_field($date)) && $gq->zeitbudget_ende <= datetots(sanitize_text_field($date)))
		{
			$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Das angegebene Datum liegt nicht innerhalb des Zeitfensters des Zeitbudgets.</h3>

			Bitte gebe ein gültiges Datum an.
			</div>
';
			
			return false;
		}
		
		return $return;
	}
	
	public static function amGenugGeld($am, $date, $wert, $return, $post_id)
	{
		if($am->id != -1)
		{
			$jahr = Einstellungen::haushaltsjahrAusgabe(datetots(sanitize_text_field($date)), $am->beginn);
			
			$vorgaenge = new Vorgaenge(array(
				'am'			=> $am->id,
				'datum_beginn'	=> $am->beginn . "." . $jahr,
				'datum_ende'	=> $am->beginn . "." . ($jahr+1)
			));
			
			if($vorgaenge->genugGeld(sanitize_text_field($wert), $post_id, datetots(sanitize_text_field($date))) == false)
			{
				$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Es ist nicht genug Geld bei den absetzbaren Mitteln für diesen Vorgang.</h3>

			Dieser Vorgang sorgt innerhalb des Budgetjahres, in dem der Vorgang verbucht wurde, für einen negativen Wert.
			</div>
';
				
				return false;			
			}
		}
		
		return $return;
	}
	
	public static function gqGenugGeld($gq, $date, $wert, $return, $post_id)
	{
		if($gq->art == "Budget" || $gq->art == "Zeitbudget")
		{
			$jahr = Einstellungen::haushaltsjahrAusgabe(datetots(sanitize_text_field($date)), $gq->budgetjahr_beginn);
			
			$vorgaenge = new Vorgaenge(array(
				'gq'			=> $gq->id,
				'datum_beginn'	=> $gq->budgetjahr_beginn . "." . $jahr,
				'datum_ende'	=> $gq->budgetjahr_beginn . "." . ($jahr+1)
			));
			
			if($vorgaenge->genugGeld(sanitize_text_field($wert), $post_id, datetots(sanitize_text_field($date))) == false)
			{
				$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Es ist nicht genug Geld im Budget für diesen Vorgang.</h3>

			Dieser Vorgang sorgt innerhalb des Budgetjahres, in dem der Vorgang verbucht wurde, für einen negativen Wert.
			</div>
';
				
				return false;
			}
		}
		else
		{
			$vorgaenge = new Vorgaenge(array(
				'gq'			=> $gq->id
			));
			
			if($vorgaenge->genugGeld(sanitize_text_field($_POST['tf_vorg_wert']), $post_id, datetots(sanitize_text_field($date))) == false)
			{
				$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Es ist nicht genug Geld in der Kasse oder dem Nichtgiro-Konto für diesen Vorgang.</h3>

			Dieser Vorgang sorgt innerhalb der Kasse oder dem Nichtgiro-Konto für einen negativen Wert.
			</div>
';
				
				return false;
			}
		}
		
		return $return;
	}
	
	public static function isBudgetAM($gq, $am, $return, $post_id)
	{
		if($gq->art == "Budget" && $am->id != -1)
		{
			$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Absetzbare Mittel können nicht von Budgets aus getätigt werden.</h3>

			Bitte gebe die Geldquelle an, auf der sich absetzbare Mittel befinden können.
			</div>
';

			return false;
		}
		
		return $return;
	}
	
	public static function issetKonto($return, $post_id)
	{
		if(!isset($_POST['tf_vorg_knt']) || $_POST['tf_vorg_knt'] == -1)
		{
			$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Das Konto ist ungültig!</h3>
				
				Bitte gebe ein gültiges Konto an.
			</div>
';
			return false;
		}
		
		return $return;
	}
	
	public static function issetGeldquelle($return, $post_id)
	{
		if(	(
				$_POST['tf_vorg_art'] != "Umbuchung" &&
				(
					!isset($_POST['tf_vorg_gq']) ||
					$_POST['tf_vorg_gq'] == -1
				)
			) ||
			(
				$_POST['tf_vorg_art'] == "Umbuchung" &&
				(
					!isset($_POST['tf_vorg_gq_sendend']) ||
					$_POST['tf_vorg_gq_sendend'] == -1 ||
					!isset($_POST['tf_vorg_gq_empfangend']) ||
					$_POST['tf_vorg_gq_empfangend'] == -1
				)
			)
		)
		{
			$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Die Geldquelle ist ungültig!</h3>
				
				Bitte gebe eine gültige Geldquelle an.
			</div>
';
			
			return false;
		}
		
		return $return;
	}
	
	public static function isDate($date, $return, $post_id)
	{
		$test = explode(".", $date);
		
		if(	!isset($date) ||
			count($test) != 3 ||
			!checkdate($test[1], $test[0], $test[2])
		)
		{
			$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Das Datum ist ungültig!</h3>
				
				Bitte gebe ein gültiges Datum an.
			</div>
';
			
			return false;
		}
		
		return $return;
	}
	
	public static function isWert($wert, $return, $post_id)
	{
		$wert_ex = explode(".", $wert);
		if(!isset($wert) || $wert == "" || !is_numeric($wert) || strlen($wert_ex[1]) > 2)
		{
			$_SESSION['err_msg'][$post_id][] = '			<div id="message" class="error">
				<h3>Der Wert ist ungültig!</h3>
				
				Bitte gebe einen nummerischen Vorgangswert an.
			</div>
';
			
			return false;
		}
		
		return $return;
	}
	
	public static function getRunningNumber($art = "")
	{
		if($art == "Einnahme")
		{
			$vorgaenge = new Vorgaenge(array(
				'tf_vorgang_art' => "Einnahme"
			));
			
			$count = count($vorgaenge->vorgaenge);
			
			$vorgaenge = new Vorgaenge(array(
				'tf_vorgang_art' => "Eingehende Umbuchung"
			));
			
			$count = $count + count($vorgaenge->vorgaenge);
		}
		elseif($art == "Ausgabe")
		{
			$vorgaenge = new Vorgaenge(array(
				'tf_vorgang_art' => "Ausgabe"
			));
			
			$count = count($vorgaenge->vorgaenge);
			
			$vorgaenge = new Vorgaenge(array(
				'tf_vorgang_art' => "Ausgehende Umbuchung"
			));
			
			$count = $count + count($vorgaenge->vorgaenge);
		}
		else
		{
			$vorgaenge = new Vorgaenge(array());
			
			$count = count($vorgaenge->vorgaenge);
		}
		
		return ($count+1);
	}
	
	public static function getRunningYearNumber($date, $art = "")
	{
		$jahr = Einstellungen::haushaltsjahrAusgabe($date);
		
		$start = get_option('tf_haushalts_jahr') . $jahr;
		$ende = get_option('tf_haushalts_jahr') . ($jahr+1);
		
		if($art == "Einnahme")
		{
			$vorgaenge = new Vorgaenge(array(
				'datum_beginn'		=> datetots($start),
				'datum_ende'		=> datetots($ende),
				'tf_vorgang_art' => "Einnahme"
			));
			
			$count = count($vorgaenge->vorgaenge);
			
			$vorgaenge = new Vorgaenge(array(
				'datum_beginn'		=> datetots($start),
				'datum_ende'		=> datetots($ende),
				'tf_vorgang_art' => "Eingehende Umbuchung"
			));
			
			$count = $count + count($vorgaenge->vorgaenge);
		}
		elseif($art == "Ausgabe")
		{
			$vorgaenge = new Vorgaenge(array(
				'datum_beginn'		=> datetots($start),
				'datum_ende'		=> datetots($ende),
				'tf_vorgang_art' => "Ausgabe"
			));
			
			$count = count($vorgaenge->vorgaenge);
			
			$vorgaenge = new Vorgaenge(array(
				'datum_beginn'		=> datetots($start),
				'datum_ende'		=> datetots($ende),
				'tf_vorgang_art' => "Ausgehende Umbuchung"
			));
			
			$count = $count + count($vorgaenge->vorgaenge);
		}
		else
		{
			$vorgaenge = new Vorgaenge(array(
				'datum_beginn'		=> datetots($start),
				'datum_ende'		=> datetots($ende)
			));
			
			$count = count($vorgaenge->vorgaenge);
		}
		
		return ($count+1);
	}
	
	public static function excelExport()
	{
		$ids = $_SESSION['ids'];
			
		$head = "Q-ID;"
			. "Name;"
			. "Geldquelle;"
			. "Konto;"
			. "Absetzbare Mittel;"
			. "Datum;"
			. "Quelle / Empfänger;"
			. "Wert";
		
		$content = $head . "\r\n";
		
		foreach($ids AS $id)
		{
			$vorgang = new Vorgang($id);
			
			switch ($vorgang->art)
			{
				case 'Ausgabe':
				case 'Ausgehende Umbuchung':
					$wert = "-" . $vorgang->wert;
					break;
				case 'Einnahme':
				case 'Eingehende Umbuchung':
					$wert = "+" . $vorgang->wert;
					break;
			}
			
			$content = $content 
					. "=\"" . $vorgang->q_id . "\";"
					. $vorgang->name . ";"
					. $vorgang->geldquelle->name . ";"
					. $vorgang->konto->name . ";"
					. $vorgang->absetzbaremittel->name . ";"
					. $vorgang->datum_entstanden_dat . ";"
					. $vorgang->empfaenger_in . $vorgang->quelle . ";"
					. doubletostr($wert, true);
			
			$content = $content . "\r\n";
		}
		
		$charset = mb_detect_encoding($content, "UTF-8, ISO-8859-1, ISO-8859-15", true);
		$content = mb_convert_encoding($content, "Windows-1252", $charset);

		echo ($content);
		die;
	}
}
