<?php

// ErrorManager - copyright 2004 intelliJAM S.r.l.
class ErrorManager {
	var $_errors = array();
	
	function ErrorManager($stringa) {
		if ($stringa)
			$this->_setError($stringa);
	}
	function _setError($stringa) {
		$this->_errors[] = $stringa;
		return true;
	}
	function _addErrors($array) {
		if (!is_array($array)) return false;
		foreach($array as $e) {
			$this->_setError($e);
		}
		return true;
	}
	function lastError() {
		return $this->_errors[count($this->_errors) - 1];
	}
	function getErrors() {
		return $this->_errors;
	}
	function showErrors($separatore = "<br>\n\r") {
		echo implode($separatore,$this->_errors);
	}
}