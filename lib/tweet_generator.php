<?php
	define('URL_REGEX', "/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?)/i");
	define('VALID_REGEX', "/[^\w\s,.'@\#!?\$%\^&\*-+=:;\/]*/");

	class Tweet_Generator {
		public $map = array();
		public $separators = array(',','.','#','!','?','$','%','^','&','*','-','+','=',':',';');
		public $space_after = array('.', ',',';','?','!', ':');
		public $no_space_after = array('#');
		public $htmlEntities = array(

		);
		private $hashedWords = array();

		public function add_tweet($t){
			$m = $this->map;
			$words = $this->get_words_from_string($t);
			for($i = 0;$i<sizeof($words);$i++){
				$next = ($i+1 >= sizeof($words)) ? false : $words[$i+1];
				if(array_key_exists(strtolower($words[$i]), $m)){
					array_push($m[strtolower($words[$i])], $next);
				}
				else{
					$nextWords = array();
					array_push($nextWords, $next);
					$m[strtolower($words[$i])] = $nextWords;
				}
			}
			$this->map = $m;
		}

		public function generate_tweet(){
			$m = $this->map;
			$result = '';
			$words = array();
			$keys = array_keys($m);
			$key = array_rand($keys);
			$seed = '';
			while(!ctype_alpha($seed)) {
				$seed = $keys[array_rand($keys)]; 
			}
			while(sizeof($words) < 28){
				$nextWords = $m[$seed];
				$word = $nextWords[array_rand($nextWords)];
				if($word === false) {
					if(sizeof($words) < 10){
						$seed = $keys[array_rand($keys)];
						continue;
					}
					else break;
				}
				array_push($words, $word);
				$seed = strtolower($word);
			}
			if($words[sizeof($words) - 1] == '#' || $words[sizeof($words) - 1] == '@') {
				$words = array_slice($words, 0, $words[sizeof($words) - 2]);
			}
			for($i = 0;$i<sizeof($words);$i++) {
				foreach ($this->hashedWords as $hash => $word) {
					if ($words[$i] == $hash) {
						$words[$i] = $word;
					}
				}
				if($i > 0){
					if(in_array($words[$i-1], $this->space_after)){	
							if(in_array($words[$i], $this->space_after)){
								$result .= $words[$i];
							}
							else{
								$result .= " ".$words[$i];
							}
					}
					else if(in_array($words[$i-1], $this->no_space_after)){
						$result .= $words[$i];
					}
					else if(in_array($words[$i], $this->space_after)) {
						$result .= $words[$i];
					}
					else {
						$result .= " ".$words[$i];
					}
				} else {
					$result .= $words[$i];
				}
			}
			return $result;
		}

		private function hashUniqueWords($t) {
			if (preg_match_all(URL_REGEX, $t, $urls)) {
				foreach ($urls as $url) {
					$hash = hash('sha256', $url[0]);
					$this->hashedWords[$hash] = $url[0];
					$t = str_replace($url[0], $hash, $t);
				}
			}
			return $t;
		}

		private function get_words_from_string($t){
			$t = html_entity_decode($t);
			$t = $this->hashUniqueWords($t);
			$t = preg_replace(VALID_REGEX, '', $t);
			foreach($this->separators as $delimiter){
				$t = str_replace($delimiter, " " . $delimiter . " ", $t);
			}
			$words = explode(' ', $t);
			for($i = 0;$i<sizeof($words);$i++) {
				if(is_null($words[$i]) || $words[$i] == '') array_splice($words, $i, 1);
			}
			return $words;
		}
	}
?>