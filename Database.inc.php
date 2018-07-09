<?php
// Database - copyright 2003 intelliJAM S.r.l.
	/*V3
	 * mysqli implementation
	 * Database V.2.2
		Aggunto lasterror e getLastError
		Database V.2.1
		aggiunto parametro opzionale al metodo Query che evita il die in caso di query errata.
		ponendo il parametro a true viene stampato il messaggio e restituito false.
	 */

class Database
{ var $HOST;
  var $DB;
  var $User;
  var $PWD;
  var $opened;
  var $current_query;
	var $quante_righe;
	var $lasterror;
  function Database($host=0, $db=0, $user=0, $password=0)
  { global $db_host, $db_name, $db_user, $db_passwd;
  	if (!$host)
  		$this->HOST=$db_host; 
    else
  		$this->HOST=$host; 
    if (!$db)
  		$this->DB=$db_name; 
    else
  		$this->DB=$db; 
    if (!$user)
  		$this->User=$db_user; 
    else
  		$this->User=$user; 
    if (!$password)
  		$this->PWD=$db_passwd;
    else
  		$this->PWD=$password;
    $this->Open();  
  }
  function Open()
  {//Apre la connessione con il database 
    $link = mysqli_connect($this->HOST, $this->User, $this->PWD,$this->DB)
    or die ("Could not select database");
    $this->opened=$link;
  }

  function Query($qry,$pleasedontdie=false) //string
  {//restituisce il riferimento al puntatore sul database, lascia la connessione aperta
//printr($qry);
    if (!$this->opened) $this->Open();
 		$this->quante_righe=0;
    //la riga or $pleasedontdie � aggiunta nella V2.1
    $result = mysqli_query ($this->opened,$qry)
      or ($pleasedontdie && ($this->lasterror=mysqli_error()))
      or die ("Query failed : ".$qry." <br> Mysql error: ".mysqli_error()); 
    $this->current_query=$result;
 		$this->quante_righe=@mysqli_num_rows($result);
    return $result;
  }
  function Close()
  {//chiude la connessione
    mysqli_close($this->opened);
  }
  function LastId() 
  { return mysqli_insert_id($this->opened);
  }
	function getRow($row_number=null) {
		if (!is_null($row_number)&&$row_number>=0&&$row_number<$this->quante_righe) mysqli_data_seek ($this->current_query, (int)$row_number);
		return @mysqli_fetch_array($this->current_query,MYSQLI_ASSOC);
	}
	function getAll() {
		return @mysqli_fetch_all($this->current_query, MYSQLI_ASSOC);
	}
	
	function CountRows() {
		return $this->quante_righe;
	}
	function getLastError() {
		return $this->lasterror;
	}
	function isOpen() {
		return $this->opened;
	}
}
?>