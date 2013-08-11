<?php
if(!defined("VALID_ACCESS"))    {die("Neoprávněný přístup!");}                          //      Ochrana proti neoprávněnému přístupu ke skriptům

class db {

    var $dbc;
    var $vysledek;
    var $debug;
    
    //funkce pro pripojeni k databazi, @ zpusobuje nevypsani hlaseni
    function db($permissions="r", $debug = 0)
    {
    //"ukazatel" k databazi
        if ($permissions == "r") {
            $uzivatel = DB_UZIVATEL_R;
            $heslo = DB_HESLO_R;
        } else {
            $uzivatel = DB_UZIVATEL_W;
            $heslo = DB_HESLO_W;
        }
        $this->debug = $debug;

        $this->dbc = @mysql_connect(DB_HOSTITEL, $uzivatel, $heslo)
        or $this->errorHandle("Nepodařilo se připojit k databázi.",  mysql_error($this->dbc), $debug);
        @mysql_select_db(DB_NAZEVDATABAZE)
        or $this->errorHandle("Nelze vybrat databázi.", mysql_error($this->dbc), $debug);
        
        if (SET_NAMES_QUERY) {
			$this->query(SET_NAMES_QUERY);
        }
    }

    //funkce pro dotaz do databaze
    function query($dotaz, $debug = null)
    {
        if (is_null($debug)) {
            $debug = $this->debug;
        }

        $this->vysledek = @mysql_query($dotaz,$this->dbc)
        or $this->errorHandle("Chybný databázový dotaz. ".$dotaz, mysql_error($this->dbc), $debug);
        return $this->vysledek;
    }
    
    //vrati jeden radek vylsedku
    function fetch_array()
    {
        return mysql_fetch_array($this->vysledek, MYSQL_BOTH); 
    }
    
    //funkce pro stripslashes
    function odstran_problemy($data)
    {
        if ( ini_get('magic_quotes_gpc') ) {
        	if (is_array($data)) {
        		$data = array_map('stripslashes',$data);
        	} else {
            	$data = stripslashes($data);
        	}
        }
        if (is_array($data)) {
        	return array_map('trim',$data);
        } else {
        	return trim($data);
        }
    }

    static function escape_string($data)
    {
        if ( is_null($data) ) {
            return "NULL";
        } else {
            return "\"" . mysql_real_escape_string($data) . "\"";
        }
    }

    //funkce pro addslashes do WHERE kluzule, pozor na NULL
    static function escape_string_where($data)
    {
        if ( is_null($data) ) {
            return " IS NULL";
        } else {
            return "=\"" . mysql_real_escape_string($data) . "\"";
        }
    }

    //uzavreni databaze
    function close($debug = null)
    {
		if (is_null($debug)) {
			$debug = $this->debug;
		}		
        @mysql_close($this->dbc)
        or $this->errorHandle("Nelze zavřít databázi.", mysql_error($this->dbc), $debug);
        
        return true;
    }
    
    //osetreni chyb
    function errorhandle($strError, $mysqlError, $debug = null)
    {
		if (is_null($debug)) {
			$debug = $this->debug;
		}
		echo '
		    <div class="chyba_vstupu">
	            Došlo k chybě na serveru. Omlouváme se za vzniklé potíže, budeme je okamžitě řešit.
		    </div>';

		$timestamp = time();
		$chyba  = '[ ' . date('Y-m-d H:i:s', $timestamp) . ' ]'."\r\n";
		$chyba .= 'Popis chyby: ' . $strError."\r\n";
		$chyba .= 'MySQL hlasi: ' . $mysqlError."\r\n";
		$chyba .= print_r($_SERVER, true);
		$chyba .= "\r\n"."\r\n";
		//$_SERVER]
		if ($debug) {
			echo $chyba;
		}
		
		/* Chybu zapíšeme do souboru */
		if (!$file_log_handle = @fopen(ROOT_DIR.FILE_DB_LOG, 'a')) {
			die();
		}
		fwrite($file_log_handle, $chyba);
		fclose($file_log_handle);
		
		$timestamp_old = intval(file_get_contents(ROOT_DIR.FILE_DB_LOG_TIME));
		
		/* Pokud uběhl alespoň den, pošleme email o chybě */
		if ((($timestamp-$timestamp_old) > ERROR_MAIL_INTERVAL*24*60*60) && !$debug) {
			$headers = 'From: ' . ERROR_MAIL_FROM . "\r\n"
					.'Reply-To: ' . ERROR_MAIL_FROM . "\r\n"
					.'MIME-Version: 1.0'."\r\n"
					.'X-Mailer: PHP'."\r\n"
					.'Content-type: text/plain; charset="utf-8"'."\r\n"
					.'Content-transfer-encoding: 8bit';
			$subject = 'Chyba databaze na ' . SERVER_NAME;
			$body = 'Na serveru ' . SERVER_NAME . ' doslo k chybe databaze.'."\r\n"."\r\n".$chyba;

	        mail(ERROR_MAIL_TO, $subject, $body, $headers); 

	        if (!$file_time_handle = @fopen(ROOT_DIR.FILE_DB_LOG_TIME, 'w')) {
				die();
			}
			fwrite($file_time_handle, $timestamp);
			fclose($file_time_handle);
		}
		
		die();
    }
}
?>
