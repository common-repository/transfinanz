<?php

/**
 * Description of AbsetzbareMittel
 *
 * @author KWM
 */
class AbsetzbareMittel extends Postlist
{
	//*************************************************************************
	//********************************* OBJEKT ********************************
	//*************************************************************************
	public $id;
	public $name;
	public $beginn;
	public $rueckzahlung;
	public $rechnung;
	public $vorgaenge;
	public $geldstand;
	
	public function __construct($id, $vorgaenge = true) 
	{
		$this->id = $id;
		
		$post = get_post($id);
		
		if($vorgaenge == true)
		{
			$this->vorgaenge = new Vorgaenge(array(
				'am'	=> $id
			));

			$this->geldstand	= $this->vorgaenge->geldstand;
		}
		
		$this->name			= $post->post_title;
		$this->beginn		= get_post_meta($id, "tf_am_beginn", true);
		$this->rueckzahlung	= get_post_meta($id, "tf_am_rueckzahlung", true);
		$this->rechnung		= get_post_meta($id, "tf_am_rechnung", true);
	}

	//*************************************************************************
	//******************************* POSTTYPE ********************************
	//*************************************************************************
	private static $post_type = "tf_absetzbaremittel";
	
	public static function register_absetzbaremittel()
	{
		parent::register_pt(self::$post_type, "Absetzbare Mittel", "Absetzbare Mittel", "AbsetzbareMittel", "absetzbaremittel");
	}
	
	//*************************************************************************
	//****************************** POST MASKEN ******************************
	//*************************************************************************
	public static function maske($post)
	{
		wp_nonce_field('tf_am', 'tf_am_nonce');
		$form = new TF_Form(array(
			'form'		=> false,
			'table'		=> true,
			'prefix'	=> 'tf_am',
			'submit'	=> 'pt'
		));

		$am = new AbsetzbareMittel($post->ID);
		
		$form->td_text(array(
			'beschreibung'		=> 'Beginn Haushaltsjahr (TT.MM.):',
			'name'				=> 'beginn',
			'value'				=> $am->beginn,
			'checking'			=> array('Fill', 'DateWY')
		));
		
		$form->td_check(array(
			'beschreibung'		=> 'Rückzahlungspflicht der Restgelder eines Jahres:',
			'name'				=> 'rueckzahlung',
			'values'			=> array("Ja"	=> "Ja"),
			'checked'			=> $am->rueckzahlung
		));
		
		$form->td_check(array(
			'beschreibung'		=> 'Rechnungen über das Budget hinaus:',
			'name'				=> 'rechnung',
			'values'			=> array("Ja"	=> "Ja"),
			'checked'			=> $am->rechnung
		));

		TF_Form::jsHead();
		TF_Form::jsScript();
		parent::wpHide();

		unset($form);
	}
	
	public static function metabox()
	{
		add_meta_box("transfinanz_absetzbaremittel_mb", __("Absetzbare Mittel Meta-Daten", "TransFinanz"), array("AbsetzbareMittel", "maske"), "tf_absetzbaremittel", "normal", "default");
	}
	
	//*************************************************************************
	//**************************** POST SPEICHERUNG ***************************
	//*************************************************************************
	public static function save($post_id)
	{
		if(get_post_type($post_id) == "tf_absetzbaremittel")
		{
			if(!isset($_POST['tf_am_nonce']))
			{
				return;
			}

			if (!wp_verify_nonce($_POST['tf_am_nonce'], 'tf_am')) 
			{
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			{
				return;
			}
			
			metaNameUpdate("vorg_am", "vorgang", $post_id);

			update_post_meta($post_id, "tf_am_beginn", sanitize_text_field($_POST['tf_am_beginn']));
			if(!isset($_POST['tf_am_rueckzahlung']) || $_POST['tf_am_rueckzahlung'] != "Ja")
			{
				update_post_meta($post_id, "tf_am_rueckzahlung", "Nein");
			}
			else
			{
				update_post_meta($post_id, "tf_am_rueckzahlung", sanitize_text_field($_POST['tf_am_rueckzahlung']));
			}
			if(!isset($_POST['tf_am_rechnung']) || $_POST['tf_am_rechnung'] != "Ja")
			{
				update_post_meta($post_id, "tf_am_rechnung", "Nein");
			}
			else
			{
				update_post_meta($post_id, "tf_am_rechnung", sanitize_text_field($_POST['tf_am_rechnung']));
			}
		}
	}
	
	//*************************************************************************
	//************************* POST ANZEIGE IN LISTE *************************
	//*************************************************************************
	private static $criterias_key = array(
		'tf_am_beginn'			=> 'Beginn',
		'tf_am_rechnung'		=> 'Begrenzung',
		'tf_am_rueckzahlung'	=> 'Rückzahlungspflicht'
	);

	private static $criterias = array(
		'tf_am_beginn',
		'tf_am_rechnung',
		'tf_am_rueckzahlung'
	);
	
	private static $form_prefix = "tf_am";

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
	
	public static function postlist_options($actions, $page)
	{
		return parent::postlist_options($actions, $page, self::$post_type);
	}
	
	public static function getValue()
	{
		$posts = get_posts(array(
			'post_type'		=> 'tf_absetzbaremittel',
			'status'		=> 'publish',
			'posts_per_page' => -1
		));
		
		foreach($posts AS $post)
		{
			$am = new AbsetzbareMittel($post->ID);
			$values[$am->id] = $am->name . " (" . doubletostr($am->geldstand, true) . ")";
		}
		
		return $values;
	}
}
