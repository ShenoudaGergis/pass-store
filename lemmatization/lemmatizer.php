<?php

namespace lemmatization;


class Lemmatizer {

  public static function getLemma($input = '') {
		$alpha = $input[0];
		$path = __DIR__ . '/data/lemmas_' . $alpha . '.php';
		if (file_exists($path)) {
			require $path;
		}
		if (isset($lemma_map[$input])) {
			return $lemma_map[$input];
		}
		else {
			return $input;
		}
	}

	//-----------------------------------------------------------------------------------

	public static function getWordsFromLemma($input = '') {
		$alpha = $input[0];
		$path = __DIR__ . '/data/roots_' . $alpha . '.php';
		if (file_exists($path)) {
			require $path;
		}
		if (isset($root_map[$input])) {
			return $root_map[$input];
		}
		else {
			return $input;
		}
	}

	//-----------------------------------------------------------------------------------

	public static function getIntersection($inputArr , $dataArr) {
		array_walk($inputArr , function(&$v) {
			$v = Lemmatizer::getLemma(strtolower($v));
		});

		array_walk($dataArr , function(&$v) {
			$v = Lemmatizer::getLemma(strtolower($v));
		});

		return count(array_intersect($inputArr , $dataArr));
	}
}

// print_r(Lemmatizer::getIntersection(["PlaYs"] , ["owner" , "play"]));



