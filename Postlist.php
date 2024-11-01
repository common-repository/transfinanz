<?php

/**
 * Standardklasse für die Post-Anzeige eigener Posttypes in WordPress
 *
 * @author KWM
 */
class Postlist {
	/**
	 * Angabe, welche Spalten angezeigt werden sollen.
	 * 
	 * @param array $defaults Standardspalten
	 * @param array $arr Neue Spalten
	 * @return array Anzuzeigende Spalten
	 */
	public static function postlists($defaults, $arr)
	{
		if(is_admin())
		{
			$defaults = $defaults + $arr;

			unset($defaults['date']);
			$defaults['date'] = __('Date');

			return $defaults;
		}
	}
	
	/**
	 * Anzeige von Spalteninhalt neuer Spalten
	 * 
	 * @param string $post_type Posttype Name
	 * @param string $column_name Spaltenname
	 * @param int $post_id Aktuelle Post-ID
	 * @param string/array $meta Angabe des anzuzeigenden Spalteninhalts
	 */
	public static function postlist_column($post_type, $column_name, $post_id, $meta)
	{
		if(is_admin())
		{
			foreach($meta AS $meta_temp)
			{
				if((!is_array($meta_temp) && $meta_temp == $column_name) || (is_array($meta_temp) && $meta_temp['key'] == $column_name))
				{
					if(!is_array($meta_temp))
					{
						$meta_value = ucfirst(get_post_meta($post_id, $meta_temp, true));
						$meta_show = $meta_value;
					}
					else
					{
						$meta_value = ucfirst(get_post_meta($post_id, $meta_temp['key'], true));
						if($meta_temp['value'] == "post_title")
						{
							$post = get_post($meta_value);
							$meta_show = $post->post_title;
						}
						elseif($meta_temp['value'] == "doubletostr")
						{
							$meta_show = doubletostr($meta_value, true, true);
						}
						elseif($meta_temp['value'] == "doubletostrwp")
						{
							$meta_show = doubletostr($meta_value, true);
						}
						elseif($meta_temp['value'] == "date")
						{
							$meta_show = tstodate($meta_value);
						}
					}
					echo ("<a href=" . $_SERVER['PHP_SELF'] . "?post_type=" . $post_type . "&" . $meta_temp . "=" . $meta_value . ">" . $meta_show . "</a>");
					break;
				}
			}
		}
	}
	
	/**
	 * Angabe, welche Spalten sortiert werden können
	 * 
	 * @param array $sort_columns Sortierbare Spalten
	 * @param array $add_columns Zu sortierende Spalten
	 * @return array Sortierbare Spalten
	 */
	public static function postlist_sorting($sort_columns, $add_columns)
	{
		if(is_admin())
		{
			$sort_columns = $sort_columns + $add_columns;
		}
			
		return $sort_columns;
	}
	
	/**
	 * Sortierung der sortierbaren Spalten
	 * 
	 * @param string $post_type Posttype
	 * @param wp_query $vars Query-Variablen
	 * @param array $meta Sortierbare Spalten
	 * @return wp_query Query-Variablen 
	 */
	public static function postlist_orderby($post_type, $vars, $meta)
	{
		if(is_admin())
		{
			if(isset($vars['post_type']) && $vars['post_type'] == $post_type && isset($vars['orderby']) && in_array($vars['orderby'], $meta))
			{
				$column = array_keys($meta, $vars['orderby']);
				$column = $column[0];
				$vars = array_merge($vars, array(
					'meta_key'	=> $column,
					'orderby'	=> 'meta_value_num'
				));
			}

			return $vars;
		}
	}
	
	/**
	 * Anzeige Filter-Formular
	 * 
	 * @global wpdb $wpdb WordPress-Datenbankanbindung
	 * @param string $post_type Posttype
	 * @param string $form_prefix Prefix der Formularnamen
	 * @param array $meta Filterbare Informationen
	 */
	public static function postlist_filtering($post_type, $form_prefix, $meta)
	{
		if(is_admin())
		{
			$screen = get_current_screen();
			if($screen->post_type == $post_type)
			{
				global $wpdb;
				
				$form = new TF_Form(array(
					'action'		=> "",
					'form'			=> false,
					'table'			=> false,
					'prefix'		=> $form_prefix
				));

				foreach($meta AS $key)
				{
					if(is_array($key))
					{
						$show = $key['value'];
						$key = $key['key'];
					}
					$results = $wpdb->get_results("SELECT pm.meta_value FROM " . $wpdb->postmeta . " pm INNER JOIN " . $wpdb->posts . " p ON p.ID = pm.post_id WHERE p.post_type = '" . $post_type . "' AND p.post_status = 'publish' AND pm.meta_key = '" . $key . "' GROUP BY pm.meta_value");

					unset($values);
					$values[0] = "Alle";
					foreach($results AS $meta_data)
					{
						if(isset($show))
						{
							if($show == "post_title")
							{
								$post = get_post($meta_data->meta_value);
								$values[$meta_data->meta_value] = $post->post_title;
							}
							elseif($show == "doubletostr")
							{
								$values[$meta_data->meta_value] = doubletostr($meta_data->meta_value, true);
							}
						}
						else
						{
							$values[$meta_data->meta_value] = $meta_data->meta_value;
						}
					}

					$form->select(array(
						'name'			=> str_replace($form_prefix . "_", "", $key),
						'values'		=> $values,
						'selected'		=> -1,
						'multiple'		=> true,
						'size'			=> 5
					));
				}
				
				unset($form);
			}
		}
	}
	
	/**
	 * Filterung
	 * 
	 * @param wp_query $query WP_Query
	 * @param string $post_type Posttype
	 * @param array $meta Filterbare Informationen
	 * @return wp_query Neuer WP_Query
	 */
	public static function postlist_filtering_sort($query, $post_type, $meta)
	{
		if(is_admin())
		{
			$screen = get_current_screen();
			if($screen->post_type == $post_type && $screen->id == "edit-" . $post_type)
			{
				$query->query_vars['meta_query'] = array();
				
				foreach($meta AS $value)
				{
					if(is_array($value))
					{
						$value = $value['key'];
					}
					
					if(isset($_GET[$value]) && is_array($_GET[$value]) && $_GET[$value] != 0)
					{
						foreach($_GET[$value] AS $key => $value_temp)
						{
							$values[$key] = sanitize_text_field($value_temp);
						}
						
						$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
							'key'		=> $value,
							'value'		=> $values,
							'compare'	=> 'IN'
						)));
					}
					elseif(isset($_GET[$value]) && $_GET[$value] != 0)
					{
						$query->query_vars['meta_query'] = array_merge($query->query_vars['meta_query'], array(array(
							'key'		=> $value,
							'value'		=> sanitize_text_field($_GET[$value])
						)));
					}
				}
			}
		}
		return $query;
	}
	
	/**
	 * Anzeige Line-Options
	 * 
	 * @param array $actions Optionen
	 * @param post $post Aktueller Post
	 * @param string $post_type Posttype
	 * @return array neue Actions
	 */
	public static function postlist_options($actions, $post, $post_type)
	{
		if($post->post_type == $post_type)
		{
			unset($actions['edit']);
			unset($actions['inline hide-if-no-js']);
			unset($actions['view']);
		}
		
		return $actions;
	}
	
	/**
	 * Register Posttype
	 * 
	 * @param string $post_type Posttype
	 * @param string $singular Posttype Beschreibung Singular
	 * @param string $plural Posttype Beschreibung Plural
	 * @param string $class_name Betreffende Klasse
	 * @param string $slug Linkslug
	 */
	public static function register_pt($post_type, $singular, $plural, $class_name, $slug)
	{
		if(is_admin())
		{
			if(post_type_exists($post_type) == false)
			{
				register_post_type($post_type, array(
						'labels'		=> array(
							'name'					=> __($plural, 'TransFinanz'),
							'singular_name'			=> __($singular, 'TransFinanz'),
							'add_new'				=> __('Neue ' . $plural, 'TransFinanz'),
							'add_new_item'			=> __('Neue ' . $singular, 'TransFinanz'),
							'edit_item'				=> __($singular . ' bearbeiten', 'TransFinanz'),
							'new_item'				=> __('Neue ' . $singular, 'TransFinanz'),
							'seacht_items'			=> __($singular . ' Suchen', 'TransFinanz'),
							'not_found'				=> __('Keine ' . $singular . ' gefunden.', 'TransFinanz'),
							'not_found_in_trash'	=> __('Neue ' . $singular, 'TransFinanz')
						),
						'public'				=> true,
						'supports'				=> array('title', 'editor'),
						'capability_type'		=> 'post',
						'rewrite'				=> array("slug" => $slug),
						'menu_position'			=> 25,
						'register_meta_box_cb'	=> array($class_name, 'metabox'),
						'has_archive'			=> true
					)
				);
			}
		}
	}
	
	/**
	 * Verstecken unwesentlicher Felder
	 */
	public static function wpHide()
	{
		echo ('		<script>
			$(".misc-pub-section").hide();
			$("#preview-action").hide();
			$("#save-action").hide();
			$("#minor-publishing-actions").html("Speichern ist nur möglich, wenn alle Daten richtig eingegeben wurden.");
			$(".submitdelete").hide();
			$("#publish").val("Speichern");
			$("#postdivrich").hide();
		</script>
');
	}
}
