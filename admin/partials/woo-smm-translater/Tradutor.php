<?php
/**
*
** Translation class in PHP using Google Translate.
*
** @author Sumit
*
**/

class Tradutor {
	
	private $de;
	private $para;
	private $proxy;

	/*
	** @param (str) $from Language of the text, set to null for automatic language detection.
	** @param (str) $to Language to translate to.
	** @param IP and port for proxy usage. Example: 127.0.0.1:8080
	*/
	
	public function __construct ($de = 'auto', $para = 'pt', $proxy=null){
		$this->proxy = $proxy;
		$this->de = $de;
		$this->para = $para;
	}
	
	public function getDe (){
		return $this->de;
	}
	
	public function getPara (){
		return $this->para;
	}
	
	public function getProxy (){
		return $this->proxy;
	}
	
	/*
	** Translate text from one language to another.
	*
	** @param (str) $text Text to be translated.
	*
	** @return Returns the translated text.
	*/
	
	public function traduzir($text) {

		$traduzir = $this->Request($text, $this->getDe(), $this->getPara(), $this->getProxy());

		$erro = isset($traduzir->erro) ? $traduzir->erro : null;
		if ($erro) {
			return $erro;
		}

		$traducao = $traduzir->sentences;
		$ret = '';

		foreach ($traducao as $str) {
			$ret .= $str->trans;
		}

		return $ret;
	}

	
	/*
	** @param (str) $from Language of the text, set to null for automatic language detection.
	** @param (str) $to Language to be translated.
	** @param (str) $text Text to translate. 
	*
	** @return Returns the translated text.
	*/
	
	public function traduzLang ($de=null, $para, $text){
		if (strlen($text) >= 5000){
			return 'Limite de caracteres excedido: 500 caract.';
		}
		
		$de = ($de == null) ? 'auto' : $de;
	
		$traduz = $this->Request($text, $de, $para, $this->getProxy());
		$traducao = $traduz->sentences;
		$ret = '';
		
		foreach ($traducao as $str){
			$ret .= $str->trans;
		}
		
		return $ret;
	}
	
	/*
	** Detect the language of the given text.
	*
	** @param (str) $text Text to be translated.
	** @param (bool) $id Return the language ID.
	*
	** @return Returns the language of the text.
	*/
	
	public function detectaIdioma ($text, $id_language=false){
		if (strlen($text) >= 5000){
			return 'Limite de caracteres excedido: 500 caract.';
		}
		
		$detect = $this->Request($text, 'auto', 'pt', $this->getProxy());
	
		if (isset($detect->erro)){
			return $traduzir->erro;
		}
	
		$id = $detect->src;
	
		if ($id_language == true){
			return $id;
		}
	
		// Lista de idiomas suportados pelo Google Tradutor.
		$idiomas = json_decode(file_get_contents('languages_ids.json'));
		$name_language = $idiomas->tl->$id;
	
		if (isset($name_language)){
			return $name_language;
		} else {
			return 'Não foi detectado o idioma deste texto!';
		}
	}
	
	/*
	** Returns an array with all the languages supported by Google Translate.
	*
	** @return Array
	*/
	
	public function listaIdiomas (){
		$lista = json_decode(file_get_contents('languages_ids.json'), true);
		$lista_idiomas = $lista['sl'];
	
		return $lista_idiomas;
	}
	
	private function Request ($q, $sl, $tl, $proxy = null){
		$curl = curl_init();
	
		if ($proxy != null){
			curl_setopt($curl, CURLOPT_PROXY, $proxy);
		}
		
		curl_setopt($curl, CURLOPT_URL, 'https://translate.google.com/translate_a/single?dj=1&q='.urlencode($q).'&sl='.$sl.'&tl='.$tl.'&hl=pt_BR&ie=UTF-8&oe=UTF-8&client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dt=md&dt=ss&dt=ex&source=langchg&otf=1');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, 'GoogleTranslate/5.21.0.RC04.202358723 (Linux; U; Android 4.4.2; SM-G110B)﻿');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$ret = json_decode(curl_exec($curl));
		curl_close($curl);
		
		if (isset($ret->sentences)){
			return $ret;
		} else {
			return json_decode(json_encode(array('erro' => 'Requisições em excesso,use um Proxy.')));
		}
	}
	
}
