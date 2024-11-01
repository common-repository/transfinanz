<?php

/**
 * Description of KontoKategorie
 *
 * @author KWM
 */
class KontoKategorie extends Postlist
{
	//*************************************************************************
	//********************************* OBJEKT ********************************
	//*************************************************************************
	public $id;
	public $name;
	public $art;
	public $ordnung;
	
	public function __construct($id) 
	{
		$this->id = $id;
		
		$post = get_post($id);
		
		$this->name			= $post->post_title;
		$this->art			= get_post_meta($id, "tf_kntkat_art");
		$this->ordnung		= get_post_meta($id, "tf_kntkat_ordnung");
	}
	
	//*************************************************************************
	//******************************* POSTTYPE ********************************
	//*************************************************************************
	private static $post_type = "tf_kontokategorie";
	
	public static function register_kontokategorie()
	{
		parent::register_pt(self::$post_type, "Konto Kategorie", "Konto Kategorien", "KontoKategorie", "kontokategorie");
	}
	
	//*************************************************************************
	//****************************** POST MASKEN ******************************
	//*************************************************************************
	public static function maske($post)
	{
		wp_nonce_field('tf_kntkat', 'tf_kntkat_nonce');
		$form = new TF_Form(array(
			'form'		=> false,
			'table'		=> true,
			'prefix'	=> 'tf_kntkat',
			'submit'	=> 'pt'
		));

		$kntkat = new KontoKategorie($post->ID);
		
		$form->td_radio(array(
			'beschreibung'		=> 'Art des Konto Kategories:',
			'name'				=> 'art',
			'checked'			=> $kntkat->art,
			'checking'			=> 'Checked',
			'values'			=> array(
				'Einnahme'			=> 'Einnahme',
				'Ausgabe'			=> 'Ausgabe'
			)
		));
		
		$form->td_text(array(
			'beschreibung'		=> 'Reihenfolgen Position:',
			'name'				=> 'ordnung',
			'value'				=> $kntkat->ordnung,
			'checking'			=> array('Fill', 'Number')
		));

		TF_Form::jsHead();
		TF_Form::jsScript();
		parent::wpHide();

		unset($form);
	}
	
	public static function metabox()
	{
		add_meta_box("transfinanz_kontokategorie_mb", __("KontoKategorien Meta-Daten", "TransFinanz"), array("KontoKategorie", "maske"), "tf_kontokategorie", "normal", "default");
	}
	
	//*************************************************************************
	//**************************** POST SPEICHERUNG ***************************
	//*************************************************************************
	public static function save($post_id)
	{
		if(get_post_type($post_id) == "tf_kontokategorie")
		{
			if(!isset($_POST['tf_kntkat_nonce']))
			{
				return;
			}

			if (!wp_verify_nonce($_POST['tf_kntkat_nonce'], 'tf_kntkat')) 
			{
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			{
				return;
			}
			
			metaNameUpdate("knt_kat", "konto", $post_id);

			update_post_meta($post_id, "tf_kntkat_art",			sanitize_text_field($_POST['tf_kntkat_art']));
			update_post_meta($post_id, "tf_kntkat_ordnung",		sanitize_text_field($_POST['tf_kntkat_ordnung']));
		}
	}
	
	//*************************************************************************
	//************************* POST ANZEIGE IN LISTE *************************
	//*************************************************************************
	private static $criterias_key = array(
		'tf_kntkat_art'			=> 'Art'
	);

	private static $criterias = array(
		'tf_kntkat_art'
	);
	
	private static $form_prefix = "tf_kntkat";

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
	public static function getValue($art)
	{
		$values = self::getAll($art, true);
		
		return $values;
	}
	
	public static function getAll($art, $publish = false)
	{
		$args = array(
			'post_type'		=> 'tf_kontokategorie',
			'posts_per_page' => -1,
			'meta_query'	=> array(
				array(
					'key'			=> 'tf_kntkat_art',
					'value'			=> $art,
					'compare'		=> '=='
				)
			),
			'meta_key'		=> 'tf_kntkat_ordnung',
			'orderby'		=> 'meta_value_num',
			'order'			=> 'ASC'
		);
		
		if($publish == true)
		{
			$args['status'] = 'publish';
		}
		
		$posts = get_posts($args);
		foreach($posts AS $post)
		{
			$values[$post->ID] = $post->post_title;
		}
		
		return $values;
	}
}
