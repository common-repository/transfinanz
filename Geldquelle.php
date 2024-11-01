<?php

/**
 * Description of Geldquelle
 *
 * @author KWM
 */
class Geldquelle extends Postlist
{
	//*************************************************************************
	//********************************* OBJEKT ********************************
	//*************************************************************************
	public $id;
	public $name;
	public $art;
	public $geldstand;
	public $vorgaenge;
	public $startgeld;
	public $dispo;
	public $budget;
	public $zeitbudget_start;
	public $zeitbudget_ende;
	public $zeitbudget_start_dat;
	public $zeitbudget_ende_dat;
	public $budgetjahr_beginn;
	
	public function __construct($id, $vorgaenge = true) {
		$this->id = $id;
		
		$post = get_post($id);
		
		$this->name = $post->post_title;
		$this->art = get_post_meta($id, "tf_gq_art", true);
		
		if($this->art == "Konto")
		{
			$this->startgeld				= get_post_meta($id, "tf_gq_startgeld", true);
			$this->dispo					= get_post_meta($id, "tf_gq_dispo", true);
		}
		else if($this->art == "Kasse")
		{
			$this->startgeld				= get_post_meta($id, "tf_gq_startgeld", true);
		}
		else if($this->art == "Budget")
		{
			$erfassungs_start = get_option('tf_erfassung_start');
			if($erfassungs_start != -1)
			{
				$abstand = date('Y') - $erfassungs_start;
				$zaehler = 0;
				while(($abstand+1) != $zaehler)
				{
					$jahr = date('Y') - $zaehler;
					$this->budget[$jahr]	= get_post_meta($id, "tf_gq_budget_" . $jahr, true);
					$zaehler++;
				}
			}
			$this->budgetjahr_beginn		= get_post_meta($id, "tf_gq_beginn_budgetjahr", true);
		}
		else if($this->art == "Zeitbudget")
		{
			$this->budget					= get_post_meta($id, "tf_gq_budget", true);
			$this->zeitbudget_start			= get_post_meta($id, "tf_gq_zeitfenster_start", true);
			$this->zeitbudget_start_dat		= tstodate($this->zeitbudget_start);
			$this->zeitbudget_ende			= get_post_meta($id, "tf_gq_zeitfenster_ende", true);
			$this->zeitbudget_ende_dat		= tstodate($this->zeitbudget_ende);
		}
		
		if($vorgaenge == true)
		{
			if($this->art == "Konto" || $this->art == "Kasse")
			{
				$this->vorgaenge = new Vorgaenge(array(
					'gq'	=> $id
				));
				
				$this->geldstand = $this->vorgaenge->geldstand;
			}
			elseif($this->art == "Budget")
			{
				$jahr = Einstellungen::haushaltsjahrAusgabe();
				$this->vorgaenge = new Vorgaenge(array(
					'gq'			=> $id,
					'datum_beginn'	=> $this->budgetjahr_beginn . $jahr
				));
				
				$this->geldstand = $this->vorgaenge->geldstand;
			}
			elseif($this->art == "Zeitbudget")
			{
				if(time() < $this->zeitbudget_start || time() > $this->zeitbudget_ende)
				{
					$this->geldstand = 0;
				}
				else
				{
					$this->vorgaenge = new Vorgaenge(array(
						'gq'	=> $id
					));
					
					$this->geldstand = $this->vorgaenge->geldstand;
				}
			}
		}
	}
	
	//*************************************************************************
	//******************************* POSTTYPE ********************************
	//*************************************************************************
	private static $post_type = "tf_geldquelle";
	
	public static function register_geldquelle()
	{
		parent::register_pt(self::$post_type, "Geldquelle", "Geldquellen", "Geldquelle", "geldquelle");
	}
	
	//*************************************************************************
	//****************************** POST MASKEN ******************************
	//*************************************************************************
	public static function maske($post)
	{
		wp_nonce_field('tf_gq', 'tf_gq_nonce');
		$form = new TF_Form(array(
			'form'		=> false,
			'table'		=> true,
			'prefix'	=> 'tf_gq',
			'submit'	=> 'pt'
		));
		
		$gq = new Geldquelle($post->ID);

		$form->td_radio(array(
			'beschreibung'		=> 'Art der Geldquelle:',
			'name'				=> 'art',
			'checked'			=> $gq->art,
			'checking'			=> 'Checked',
			'values'			=> array(
				'Konto'				=> 'Konto',
				'Kasse'				=> 'Kasse',
				'Budget'			=> 'Budget',
				'Zeitbudget'		=> 'Zeitbudget'
			)
		));

		$form->td_text(array(
			'beschreibung'		=> 'Geldstand ab Beginn der Aufzeichnung:',
			'name'				=> 'startgeld',
			'size'				=> 10,
			'value'				=> $gq->startgeld,
			'checking'			=> array('Number', 'Fill'),
			'hidings'			=> array(
				array(
					'key'				=> 'art_Konto',
					'value'				=> 'Konto',
					'key_addition'		=> ':checked',
					'operator'			=> '==',
					'after'				=> '||'
				),
				array(
					'key'				=> 'art_Kasse',
					'value'				=> 'Kasse',
					'key_addition'		=> ':checked',
					'operator'			=> '=='
				)
			)
		));

		$form->td_check(array(
			'beschreibung'		=> '',
			'name'				=> 'dispo',
			'values'			=> array(
				true				=> 'Dispokredit möglich'
			),
			'checked'			=> $gq->dispo,
			'hidings'			=> array(
				array(
					'key'				=> 'art_Konto',
					'value'				=> 'Konto',
					'key_addition'		=> ':checked',
					'operator'			=> '==',
					'field_addition'	=> 'dispo'
				)
			)
		));

		$erfassungs_start = get_option('tf_erfassung_start');
		if($erfassungs_start != -1)
		{
			$abstand = date('Y') - $erfassungs_start;
			$zaehler = 0;
			while(($abstand+1) != $zaehler)
			{
				$jahr = date('Y') - $zaehler;
				$form->td_text(array(
					'beschreibung'		=> 'Budget ' . $jahr . ':',
					'name'				=> 'budget_' . $jahr,
					'size'				=> 10,
					'value'				=> $gq->budget[$jahr],
					'checking'			=> array('Number', 'Fill'),
					'hidings'			=> array(
						array(
							'key'				=> 'art_Budget',
							'value'				=> 'Budget',
							'key_addition'		=> ':checked',
							'operator'			=> '=='
						)
					)
				));
				$zaehler++;
			}
		}
		
		$form->td_text(array(
			'beschreibung'		=> 'Beginn Budgetjahr:',
			'name'				=> 'beginn_budgetjahr',
			'size'				=> 10,
			'value'				=> $gq->budgetjahr_beginn,
			'checking'			=> array('DateWY', 'Fill'),
			'hidings'			=> array(
				array(
					'key'				=> 'art_Budget',
					'value'				=> 'Budget',
					'key_addition'		=> ':checked',
					'operator'			=> '=='
				)
			)
		));

		$form->td_text(array(
			'beschreibung'		=> 'Budget:',
			'name'				=> 'zeitbudget',
			'size'				=> 10,
			'value'				=> $gq->budget,
			'checking'			=> array('Number', 'Fill'),
			'hidings'			=> array(
				array(
					'key'				=> 'art_Zeitbudget',
					'value'				=> 'Zeitbudget',
					'key_addition'		=> ':checked',
					'operator'			=> '=='
				)
			)
		));

		$form->td_text(array(
			'beschreibung'		=> 'Zeitfenster:',
			'name'				=> 'zeitfenster',
			'size'				=> 10,
			'value'				=> $gq->zeitbudget_start_dat . " - " . $gq->zeitbudget_ende_dat,
			'checking'			=> array('DoubleDate', 'Fill'),
			'hidings'			=> array(
				array(
					'key'				=> 'art_Zeitbudget',
					'value'				=> 'Zeitbudget',
					'key_addition'		=> ':checked',
					'operator'			=> '=='
				)
			)
		));

		TF_Form::jsHead();
		TF_Form::jsScript();
		parent::wpHide();

		unset($form);
	}

	public static function metabox()
	{
		add_meta_box("transfinanz_geldquelle_mb", __("Geldquelle Meta-Daten", "TransFinanz"), array("Geldquelle", "maske"), "tf_geldquelle", "normal", "default");
	}

	//*************************************************************************
	//**************************** POST SPEICHERUNG ***************************
	//*************************************************************************
	public static function save($post_id)
	{
		if(get_post_type($post_id) == "tf_geldquelle")
		{
			if(!isset($_POST['tf_gq_nonce']))
			{
				return;
			}

			if (!wp_verify_nonce($_POST['tf_gq_nonce'], 'tf_gq')) 
			{
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			{
				return;
			}
			
			metaNameUpdate("vorg_gq", "vorgang", $post_id);

			if($_POST['tf_gq_art'] == "Konto")
			{
				update_post_meta($post_id, "tf_gq_art",			"Konto");
				update_post_meta($post_id, "tf_gq_startgeld",	sanitize_text_field($_POST['tf_gq_startgeld']));
				if(isset($_POST['tf_gq_dispo']))
				{
					$tf_gq_disp = true;
				}
				else
				{
					$tf_gq_disp = false;
				}
				update_post_meta($post_id, "tf_gq_dispo",		$tf_gq_disp);
			}
			else if($_POST['tf_gq_art'] == "Kasse")
			{
				update_post_meta($post_id, "tf_gq_art",			"Kasse");
				update_post_meta($post_id, "tf_gq_startgeld",	sanitize_text_field($_POST['tf_gq_startgeld']));
			}
			else if($_POST['tf_gq_art'] == "Budget")
			{
				update_post_meta($post_id, "tf_gq_art",					"Budget");
				$erfassungs_start = get_option('tf_erfassung_start');
				if($erfassungs_start != -1)
				{
					$abstand = date('Y') - $erfassungs_start;
					$zaehler = 0;
					while(($abstand+1) != $zaehler)
					{
						$jahr = date('Y') - $zaehler;
						update_post_meta($post_id, "tf_gq_budget_" . $jahr,	sanitize_text_field($_POST['tf_gq_budget_' . $jahr]));
						$zaehler++;
					}
				}
				update_post_meta($post_id, "tf_gq_beginn_budgetjahr",	sanitize_text_field($_POST['tf_gq_beginn_budgetjahr']));
			}
			else if($_POST['tf_gq_art'] == "Zeitbudget")
			{
				update_post_meta($post_id, "tf_gq_art",			"Zeitbudget");
				update_post_meta($post_id, "tf_gq_budget",		sanitize_text_field($_POST['tf_gq_zeitbudget']));

				$temp = explode(" - ", $_POST['tf_gq_zeitfenster']);

				update_post_meta($post_id, "tf_gq_zeitfenster_start",	sanitize_text_field(datetots($temp[0])));
				update_post_meta($post_id, "tf_gq_zeitfenster_ende",	sanitize_text_field(datetots($temp[1])));
			}
		}
	}

	//*************************************************************************
	//************************* POST ANZEIGE IN LISTE *************************
	//*************************************************************************
	private static $criterias_key = array(
		'tf_gq_art'			=> 'Art'
	);

	private static $criterias = array(
		'tf_gq_art'
	);
	
	private static $form_prefix = "tf_gq";

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
	
	//*************************************************************************
	//****************************** SONSTIGES ********************************
	//*************************************************************************
	public static function getValue($budget = true)
	{
		$values = self::getAll(true);
		
		if($budget == false)
		{
			unset($values['Budgets']);
			unset($values['Zeitbudgets']);
		}
		
		foreach($values AS $gq_art => $gqs)
		{
			foreach($gqs AS $gq_id => $name)
			{
				$gq = new Geldquelle($gq_id);
				$values[$gq_art][$gq_id] = $name . " (" . doubletostr($gq->geldstand, true) . ")";
			}
		}
		
		return $values;
	}
	
	public static function getAll($publish = false)
	{
		$args = array(
			'post_type'		=> 'tf_geldquelle',
			'posts_per_page' => -1
		);
		
		if($publish == true)
		{
			$args['status'] = 'publish';
		}
		
		$posts = get_posts($args);
		
		$values = array();
		foreach($posts AS $post)
		{
			$gq = new Geldquelle($post->ID);
			$values[$gq->art][$post->ID] = $gq->name;
		}
		
		if(isset($values['Kasse']))
		{
			$return['Kassen'] = $values['Kasse'];
		}
		
		if(isset($values['Konto']))
		{
			$return['Konten'] = $values['Konto'];
		}
		
		if(isset($values['Budget']))
		{
			$return['Budgets'] = $values['Budget'];
		}
		
		if(isset($values['Zeitbudget']))
		{
			$return['Zeitbudgets'] = $values['Zeitbudget'];
		}
		
		return $return;
	}
}

?>