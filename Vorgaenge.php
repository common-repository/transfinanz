<?php

/**
 * Description of Vorgaenge
 *
 * @author KWM
 */
class Vorgaenge {
	public $sql;
	public $vorgaenge = array();
	public $geldstand = false;
	private $gq_pruef = false;
	private $am_pruef = false;
	private $params;
	
	/**
	 * Vorgangssammlung<br><br>
	 * 
	 * Optimierbar im Konstruktur durch Auslagerung<br><br>
	 * 
	 * @param array $arr Parameterübergabe<br>
	 * - gq<br>
	 * - knt<br>
	 * - am<br>
	 * - datum<br>
	 * - datum_beginn<br>
	 * - datum_ende<br>
	 * - art<br>
	 * - wert
	 */
	public function __construct($arr) 
	{
		global $wpdb;
		$this->params = $arr;
		
		$params = array(
			'tf_vorg_gq'				=> 'gq', 
			'tf_vorg_knt'				=> 'knt', 
			'tf_vorg_am'				=> 'am', 
			'tf_vorg_datum_entstanden'	=> 'datum', 
			'tf_vorg_art'				=> 'art'
		);
		
		if(isset($arr['datum_beginn']) || isset($arr['datum_ende']))
		{
			unset($params['tf_vorg_datum_entstanden']);
		}
		
		$inner_joins = "";
		$coloumn_check = "";
		$where = "";
		foreach($params AS $key => $value)
		{
			if(isset($arr[$value]))
			{
				$inner_joins = $inner_joins . " INNER JOIN " . $wpdb->postmeta . " " . $value . " ON p.ID = " . $value . ".post_id";
				if($coloumn_check != "")
				{
					$coloumn_check = $coloumn_check . " AND ";
				}
				$coloumn_check = $coloumn_check . $value . ".meta_key = '" . $key . "'";
				
				if($where != "")
				{
					$where = $where . " AND ";
				}
				if(is_array($arr[$value]))
				{
					$where = $where . "(";
					$pruef = 0;
					foreach($arr[$value] AS $value_2)
					{
						if($pruef == 1)
						{
							$where = $where . " OR ";
						}
						else
						{
							$pruef = 1;
						}
						$where = $where . $value . ".meta_value = " . $value_2;
					}
					$where = $where . ")";
				}
				else
				{
					$where = $where . $value . ".meta_value = " . $arr[$value];
				}
			}
		}
		
		if(isset($arr['datum_beginn']))
		{
			$temp = explode(".", $arr['datum_beginn']);
			
			if(count($temp) == 3)
			{
				$arr['datum_beginn'] = mktime(0, 0, 0, $temp[1], $temp[0], $temp[2]);
			}
		}
		
		if(isset($arr['datum_ende']))
		{
			$temp = explode(".", $arr['datum_ende']);
			
			if(count($temp) == 3)
			{
				$arr['datum_ende'] = mktime(0, 0, 0, $temp[1], $temp[0], $temp[2]);
			}
		}
		
		$value = "";
		$add = "";
		if(isset($arr['datum_beginn']) && !isset($arr['datum_ende']))
		{
			$value = "db";
			$add = $value . ".meta_value >= " . $arr['datum_beginn'];
			unset($arr['datum_beginn']);
		}
		elseif(isset($arr['datum_ende']) && !isset($arr['datum_beginn']))
		{
			$value = "de";
			$add = $value . ".meta_value < " . $arr['datum_ende'];
			unset($arr['datum_ende']);
		}
		elseif(isset($arr['datum_ende']) && isset($arr['datum_beginn']))
		{
			$value = "d";
			$add = "(" . $value . ".meta_value >= " . $arr['datum_beginn'] . " AND " . $value . ".meta_value < " . $arr['datum_ende'] . ")";
			unset($arr['datum_beginn']);
			unset($arr['datum_ende']);
		}
		
		if($value == "")
		{
			$value = "do";
		}

		$inner_joins = $inner_joins . " INNER JOIN " . $wpdb->postmeta . " " . $value . " ON p.ID = " . $value . ".post_id";
		if($coloumn_check != "")
		{
			$coloumn_check = $coloumn_check . " AND ";
		}
		$coloumn_check = $coloumn_check . $value . ".meta_key = 'tf_vorg_datum_entstanden'";
		
		if($add != "")
		{
			if($where != "")
			{
				$where = $where . " AND ";
			}

			$where = $where . $add;
		}

		if($where != "")
		{
			$where = $where . " AND ";
		}
		$where = $where . "p.post_type = 'tf_vorgang' AND p.post_status = 'publish'";
		
		if($coloumn_check != "")
		{
			$where = " WHERE (" . $coloumn_check . ") AND (" . $where . ")";
		}
		else
		{
			$where = " WHERE " . $where;
		}
		
		$this->sql = "SELECT p.ID FROM " . $wpdb->posts . " p" . $inner_joins . $where . " GROUP BY p.ID ORDER BY " . $value . ".meta_value ASC";
		$results = $wpdb->get_results($this->sql);
		foreach($results AS $result)
		{
			$this->vorgaenge[] = new Vorgang($result->ID);
		}
		
		if(count($arr) == 1 && (
			(isset($arr['gq'])  && !is_array($arr['gq']))  || 
			(isset($arr['knt']) && !is_array($arr['knt'])) || 
			(isset($arr['am'])  && !is_array($arr['am']))
		))
		{
			if(isset($arr['gq']))
			{
				$this->gq_pruef = true;
			}
			
			if(isset($arr['am']))
			{
				$this->am_pruef = true;
			}
			
			$this->berechneGeldstand();
		}
	}
	
	public function berechneGeldstand($time = 0)
	{
		if($time == 0)
		{
			$time = time();
		}
		
		$this->geldstand = 0;
		$pruef = 0;
		
		if($this->gq_pruef == true)
		{
			$this->startgeldGQ($time);
		}
		
		if($pruef == 0)
		{
			if($this->am_pruef == true)
			{
				$id = $this->params['am'];
			
				$am = new AbsetzbareMittel($id, false);
			}
			
			foreach($this->vorgaenge AS $vorgang)
			{				
				switch($vorgang->art)
				{
					case "Einnahme":
					case "Eingehende Umbuchung":
						$this->geldstand = $this->geldstand + $vorgang->wert;
					break;
					case "Ausgabe":
					case "Ausgehende Umbuchung":
						$this->geldstand = $this->geldstand - $vorgang->wert;
					break;
				}
				
				if($this->am_pruef == true && $this->geldstand < 0 && $am->rechnung == "Ja")
				{
					$this->geldstand = 0;
				}
			}
		}
	}
	
	public function genugGeld($wert, $post_id, $time = 0)
	{
		if($this->gq_pruef == false && $this->am_pruef == false)
		{
			return true;
		}
		elseif($this->gq_pruef == true && $this->am_pruef == false)
		{
			$id = $this->params['gq'];
			
			$gq = new Geldquelle($id, false);
			if($gq->art == "Konto" && $gq->dispo == 1)
			{
				return true;
			}
		}
		elseif($this->gq_pruef == false && $this->am_pruef == true)
		{
			$id = $this->params['am'];
			
			$am = new AbsetzbareMittel($id, false);
			if($am->rechnung == "Ja")
			{
				return true;
			}
		}
		
		if($time == 0)
		{
			$time = time();
		}
		
		if($this->gq_pruef == true)
		{
			$this->startgeldGQ($time);
		}
		
		foreach($this->vorgaenge AS $vorgang)
		{				
			switch($vorgang->art)
			{
				case "Einnahme":
				case "Eingehende Umbuchung":
					$this->geldstand = $this->geldstand + $vorgang->wert;
				break;
				case "Ausgabe":
				case "Ausgehende Umbuchung":
					$this->geldstand = $this->geldstand - $vorgang->wert;
				break;
			}
			
			if(($this->geldstand - $wert) < 0 && $vorgang->datum_entstanden > $time && $post_id != $vorgang->id)
			{
				return false;
			}
		}
		
		return true;
	}
	
	public function startgeldGQ($time)
	{
		$id = $this->params['gq'];
		
		$gq = new Geldquelle($id, false);
		
		if($gq->art == "Kasse" || $gq->art == "Konto")
		{
			$this->geldstand = $gq->startgeld;
		}
		elseif($gq->art == "Budget")
		{
			$haushalts_jahr = Einstellungen::haushaltsjahrAusgabe($time);
			if(isset($gq->budget[$haushalts_jahr]))
			{
				$this->geldstand = $gq->budget[$haushalts_jahr];
			}
			else
			{
				$pruef2 = 0;
				while($pruef2 == 0)
				{
					$haushalts_jahr--;
					if(isset($gq->budget[$haushalts_jahr]))
					{
						$this->geldstand = $gq->budget[$haushalts_jahr];
						$pruef2 = 1;
					}

					if($haushalts_jahr == 2008)
					{
						echo ('			<div id="message" class="error">
				<h3>Für das Budget ' . $gq->name . ' ist kein Wert hinterlegt.</h3>
				
				Das ist seltsam. Wende dich bitte an die TransFinanz-Programmierung zur Behebung des Fehlers.
			</div>
');
						$this->geldstand = "FEHLER";
						$pruef = 1;
						$pruef2 = 1;
					}
				}
			}
		}
		elseif($gq->art == "Zeitbudget")
		{
			if($time >= $gq->zeitbudget_start && $time <= $gq->zeitbudget_ende)
			{
				$this->geldstand = $gq->budget;
			}
			else
			{
				$this->geldstand = 0;
				$pruef = 1;
			}
		}
	}
}

?>