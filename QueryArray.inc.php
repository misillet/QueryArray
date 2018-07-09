<?php
// QueryArray - copyright 2003-2018 intelliJAM S.r.l.

require_once(CLASS_DIR."Database.inc.php");
class QueryArray { 
	var $chiavi; //array contenente le chiavi del risultato della query
	var $conta; //conta il numero di record restituiti 1 based.
	var $riga; //array a 2 dimensioni contenente i risultati delle query.
	var $stato; //boolean
 	var $query; //contiene l'ultima query sql eseguita/formata.
  	var $lasterror; //contiene l'ultimo errore.
	var $lastid; //ultimo autoincrement creato
	
	var $DB; // la connessione corrente (oggetto Database)
	
  function QueryArray($qry="",$host=0, $db=0, $user=0, $password=0)
  { 
  	$this->chiavi=Array();
    $this->conta=0;
    $this->riga=Array();
    $this->stato=0;
    if ($qry!="") {
		    	$this->Esegui($qry,$host, $db, $user, $password);
	} else {
		$this->DB = new Database($host, $db, $user, $password);
	}
  }
  function EseguiDontDie($qry,$host=0, $db=0, $user=0, $password=0) {
  	$out=$this->Esegui($qry,$host, $db, $user, $password, true); //V2.4 aggiunti i parametri di db.
  		return $this->stato;
  }
  function Esegui($qry=NULL,$host=0, $db=0, $user=0, $password=0,$dontdie=false)
  {//restituisce l'array con i risultati della query.  
  	//printr("\n\n".$qry."\n\n",'lamase');
    if (is_null($qry)) $qry=$this->query;
  	$this->query=$qry; //era quache riga pi� gi� fixed nella V2.1
  			
	if (!$this->DB->isOpen()) {
		$this->DB = new Database($host, $db, $user, $password);
	}

    $ref = $this->DB->Query($qry,$dontdie); //aggiunto il secondo paramtro in V2.0
    if (!$ref) {
			$this->lasterror=$this->DB->getLastError();
	    //$DB->Close();
    	$this->stato=0;
    	return false;
    }
    $this->riga=Array();

	
/*
    $temp = null;
    while ($this->riga[] = $this->DB->getRow()) {}
    unset($this->riga[count($this->riga)-1]);
*/
	$this->riga= $this->DB->getAll();

    $this->conta = count($this->riga);
    $last_id = $this->DB->LastId();


 //    $db->Close(); //V2.3  tolta questa chiamata.
    //unset($db);
    $this->stato=1;
    if ($this->conta) $this->chiavi = array_keys($this->riga[0]);
    //$this->query=$qry; //spostato alla seconda riga della funzione nella V2.1
    $this->lastid=$last_id;
    return $last_id;
  }

  function TabellaSemplice()
  {//crea una tabella a partire dall'oggetto corrente
    $temp= "\n<table>\n<tr>\n";
    foreach($this->chiavi as $t) $temp.= "<td>".$t."</td>\n";
    $temp.= "</tr>\n";
    foreach($this->riga as $riga)
    { $temp.= "<tr>\n";
      foreach($riga as $v) $temp.= "<td>".$v."</td>\n";
      $temp.= "</tr>\n";
    }
    $temp.= "</table>\n";
    return $temp;
  }
  function EstraiColonna($chiave)
  { //restituisce un array monodimensionale con i dati della colonna;
    $temp=Array();
    if (in_array($chiave,$this->chiavi))
    	foreach($this->riga as $riga) 
    		$temp[]=$riga[$chiave];
    return $temp;
  }
  function EstraiColonne($chiavi)
  { //restituisce un array bidimensionale con i dati della colonna;
  	if (!is_array($chiavi)) $chiavi=Array($chiavi);
  	//toglie da $chiavi quelle inesistenti;
    for ($i=0;$i<count($chiavi);$i++) {
    	if (!in_array($chiavi[$i],$this->chiavi)) unset($chiavi[$i]);
    }
    $temp=Array();
    for ($i=0;$i<$this->Conta();$i++) {
    	foreach($chiavi as $key) {
    		$temp[$i][$key]=$this->riga[$i][$key];
    	}
    }
    return $temp;
  }
  function Conta()
  { return $this->conta;
  }
	function FormaQuerySemplice ($what,$from,$where=1,$where_op="AND",$limit_first=NULL,$limit_count=NULL,$group=NULL, $order=NULL) {
		if (is_null($what)||is_null($from)) {
			$this->stato=0;
			return false;
		}
		if (is_array($what)) $what=implode(",",$what);
		if (is_array($where)) $where=implode(" ".$where_op." ",$where);
		
		$this->query="SELECT ".$what." FROM ".$from." WHERE ".$where;
		if (!is_null($limit_first)&&!is_null($limit_count)) {
			$this->query.=" LIMIT ".$limit_first.",".$limit_count;
		}
		if (!is_null($group)) {
			if (is_array($group)) $group=implode(",",$group);
			$this->query.=" GROUP BY ".$group;
		}
		if (!is_null($order)) {
			if (is_array($order)) $order=implode(",",$order);
			$this->query.=" ORDER BY ".$order;
		}
	}
	function CSV($intestazioni=FALSE,$separatore=";") {
		//crea un output CSV per il risutato della query.
		//il separatore " � proibito e viene sostituito da ;
		$separatore=substr($separatore,0,1);
		if ($separatore=='"') $separatore=";";
		//conta le colonne:
		$colonne=count($this->chiavi);
		if (!$colonne) return ""; //il CSV � vuoto.		
		if ($intestazioni) {
			$temp_out ='"';
			foreach($this->chiavi as $c)
				$temp_chiavi[]=str_replace('"','""',$c);
			
			$temp_out.=implode('","',$temp_chiavi);
			$temp_out.='"';
			$temp_out.="\n";
		}
		foreach ($this->riga as $r) {
			$temp_out.='"';
			$temp_campi=Array();
			foreach($r as $c)
				$temp_campi[]=str_replace('"','""',$c);
			$temp_out.=implode('","',$temp_campi);
			$temp_out.='"';
			$temp_out.="\n";
		}
		return $temp_out;
	}
	function getLastError() {
		return $this->lasterror;
	}
	function getQueryString() {
		return $this->query;
	}
	function getLastId() {
		return $this->lastid;
	}

}
