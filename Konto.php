<?php

/**
 * Description of Konto
 *
 * @author KWM
 */
class Konto extends Postlist
{
	//*************************************************************************
	//********************************* OBJEKT ********************************
	//*************************************************************************
	public $id;
	public $name;
	public $art;
	public $kat;
	public $vorgaenge;
	public $geldstand;
	public $ordnung;
	
	public function __construct($id, $vorgaenge = true) 
	{
		$this->id = $id;
		
		$post = get_post($id);
		
		if($vorgaenge == true)
		{
			$this->vorgaenge = new Vorgaenge(array(
				'knt'	=> $id
			));
			
			$this->geldstand = $this->vorgaenge->geldstand;
		}
		
		$this->name			= $post->post_title;
		$this->art			= get_post_meta($id, "tf_knt_art", true);
		$this->kat			= new KontoKategorie(get_post_meta($id, "tf_knt_kat", true));
		$this->ordnung		= get_post_meta($id, "tf_knt_ordnung", true);
	}
	
	//*************************************************************************
	//******************************* POSTTYPE ********************************
	//*************************************************************************
	private static $post_type = "tf_konto";
	
	public static function register_konto()
	{
		parent::register_pt(self::$post_type, "Konto", "Konten", "Konto", "konto");
	}
	
	//*************************************************************************
	//****************************** POST MASKEN ******************************
	//*************************************************************************
	public static function maske($post)
	{
		wp_nonce_field('tf_knt', 'tf_knt_nonce');
		$form = new TF_Form(array(
			'form'		=> false,
			'table'		=> true,
			'prefix'	=> 'tf_knt',
			'submit'	=> 'pt'
		));
		
		$knt = new Konto($post->ID);

		$form->td_radio(array(
			'beschreibung'		=> 'Art des Kontos:',
			'name'				=> 'art',
			'checked'			=> $knt->art,
			'checking'			=> 'Checked',
			'values'			=> array(
				'Einnahme'			=> 'Einnahme',
				'Ausgabe'			=> 'Ausgabe'
			)
		));
		
		$form->td_text(array(
			'beschreibung'		=> 'Reihenfolgen Position:',
			'name'				=> 'ordnung',
			'value'				=> $knt->ordnung,
			'checking'			=> array('Fill', 'Number')
		));
		
		$values_einnahmen	= KontoKategorie::getValue("Einnahme");
		$values_ausgaben	= KontoKategorie::getValue("Ausgabe");
		
		$form->td_select(array(
			'name'			=> 'kat_id',
			'beschreibung'	=> 'Konto Kategorie:',
			'selected'		=> $knt->kat->id,
			'erste'			=> true,
			'checking'		=> 'Selection',
			'hidings'			=> array(
				array(
					'key'				=> 'art_Einnahme',
					'value'				=> 'Einnahme',
					'key_addition'		=> ':checked',
					'operator'			=> '==',
					'after'				=> '||'
				),
				array(
					'key'				=> 'art_Ausgabe',
					'value'				=> 'Ausgabe',
					'key_addition'		=> ':checked',
					'operator'			=> '=='
				)
			)
		));

		TF_Form::jsHead();
		TF_Form::jsScript();
		parent::wpHide();
		echo ('
		<script>
			function optionRemove()
			{
				if($("#tf_knt_art_Einnahme:checked").val() == "Einnahme")
				{
');
		
		foreach($values_einnahmen AS $key => $value)
		{
			if($knt->kat->id == $key)
			{
				$selected = " selected";
			}
			else
			{
				$selected = "";
			}
			echo ('					$("#tf_knt_kat").append(\'<option value="' . $key . '"' . $selected . '>' . $value . '</option>\');
');
			
		}
		foreach($values_ausgaben AS $key => $value)
		{
			echo ('					$("#tf_knt_kat option[value=\'' . $key . '\']").remove(); 
');
		}
		
		echo ('				}

				if($("#tf_knt_art_Ausgabe:checked").val() == "Ausgabe")
				{
');
		
		foreach($values_ausgaben AS $key => $value)
		{
			if($knt->kat->id == $key)
			{
				$selected = " selected";
			}
			else
			{
				$selected = "";
			}
			echo ('					$("#tf_knt_kat").append(\'<option value="' . $key . '"' . $selected . '>' . $value . '</option>\');
');
		}
		foreach($values_einnahmen AS $key => $value)
		{
			echo ('					$("#tf_knt_kat option[value=\'' . $key . '\']").remove(); 
');
		}
		
		echo ('				}
			}
				
			$("#tf_knt_art_Einnahme").change(function()
			{
				optionRemove();
			});
			$("#tf_knt_art_Ausgabe").change(function()
			{
				optionRemove();
			});
			
			optionRemove();

		</script>
');
		
		unset($form);
	}
	
	public static function metabox()
	{
		add_meta_box("transfinanz_konto_mb", __("Konten Meta-Daten", "TransFinanz"), array("Konto", "maske"), "tf_konto", "normal", "default");
	}
	
	//*************************************************************************
	//**************************** POST SPEICHERUNG ***************************
	//*************************************************************************
	public static function save($post_id)
	{
		if(get_post_type($post_id) == "tf_konto")
		{
			if(!isset($_POST['tf_knt_nonce']))
			{
				return;
			}

			if (!wp_verify_nonce($_POST['tf_knt_nonce'], 'tf_knt')) 
			{
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			{
				return;
			}
			
			metaNameUpdate("vorg_knt", "vorgang", $post_id);

			update_post_meta($post_id, "tf_knt_art",		sanitize_text_field($_POST['tf_knt_art']));
			$kntkat = new KontoKategorie(sanitize_text_field($_POST['tf_knt_kat']));
			update_post_meta($post_id, "tf_knt_kat",		$kntkat->id);
			update_post_meta($post_id, "tf_knt_kat_name",	$kntkat->name);
			update_post_meta($post_id, "tf_knt_ordnung",	sanitize_text_field($_POST['tf_knt_ordnung']));
		}
	}
	
	//*************************************************************************
	//************************* POST ANZEIGE IN LISTE *************************
	//*************************************************************************
	private static $criterias_key = array(
		'tf_knt_art'			=> 'Art',
		'tf_knt_kat'			=> 'Kategorie'
	);

	private static $criterias = array(
		'tf_knt_art',
		array(
			'key'		=> 'tf_knt_kat',
			'value'		=> 'post_title'
		)
	);
	
	private static $form_prefix = "tf_knt";

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
		$values = array();
		if($publish == true)
		{
			$kntkat = KontoKategorie::getValue($art);
		}
		else
		{
			$kntkat = KontoKategorie::getAll($art);
		}
		foreach($kntkat AS $kat_id => $name)
		{
			$args = array(
				'post_type'		=> 'tf_konto',
				'posts_per_page' => -1,
				'meta_query'	=> array(
					array(
						'key'			=> 'tf_knt_art',
						'value'			=> $art
					),
					array(
						'key'			=> 'tf_knt_kat',
						'value'			=> $kat_id
					)
				),
				'meta_key'		=> 'tf_knt_ordnung',
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
				$knt = new Konto($post->ID);
				$values[$name][$post->ID] = $knt->name;
			}
		}
		
		return $values;
	}
}
