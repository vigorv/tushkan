<?php

define('_BANSTATE_READONLY_', 0);
define('_BANSTATE_FULL_', 10);
define('_BANREASON_ABONENTFEE_', 0);
define('_BANREASON_VIOLATION_', 1);
define('_CURRENCY_', 'rur');

define('_VIDEO_HIGH_', 3);
define('_VIDEO_MEDIUM_', 2);
define('_VIDEO_LOW_', 1);
define('_VIDEO_ASIS_', 0);



define("_PD_FILE_", "file");
define("_PD_TEXT_", "text");
define("_PD_TEXTAREA_", "textarea");
define("_PD_PWD_", "pwd");
define("_PD_CHECKBOX_", "checkbox");
define("_PD_RADIO_", "radio");

define("_PD_GROUP_COMMON_", 0);

class Utils {

    /**
     * парсинг выражения записи периода времени
     *
     * @param string $period - цифробуквенное обозначение периода (5d, 1m, 1000i)
     * символы в выражения как у функции time()
     * @param string $from - дата Y-m-d H:i:s, с которой отсчитываем период
     * @return integer
     */
    public function parsePeriod($period, $from = '') {
	$sek = 0;
	if (!empty($period)) {
	    $matches = array();
	    preg_match('/([0-9]{1,})([a-z]){1}/', strtolower($period), $matches);
	    if (!empty($matches[1]) && !empty($matches[2])) {
		if (empty($from))
		    $from = 0;
		else
		    $from = strtotime($from);
		$s = $i = $h = $d = $m = $y = 0;
		$$matches[2] = $matches[1];

		$sek = mktime(
				(date('H', $from) + $h), (date('i', $from) + $i), (date('s', $from) + $s), (date('m', $from) + $m), (date('d', $from) + $d), (date('y', $from) + $y)
			) - $from;
	    }
	}
	return $sek;
    }

    public function spellPeriod($period) {
	$sek = 0;
	if (!empty($period)) {
	    $matches = array();
	    preg_match('/([0-9]{1,})([a-z]){1}/', strtolower($period), $matches);
	    if (!empty($matches[1]) && !empty($matches[2])) {
		$titles = array();
		switch ($matches[2]) {
		    case "h":
			$titles = array(
			    Yii::t('common', 'hour'),
			    Yii::t('common', 'houra'),
			    Yii::t('common', 'hours'),
			);
			break;
		    case "i":
			$titles = array(
			    Yii::t('common', 'minute'),
			    Yii::t('common', 'minutea'),
			    Yii::t('common', 'minutes'),
			);
			break;
		    case "s":
			$titles = array(
			    Yii::t('common', 'second'),
			    Yii::t('common', 'seconda'),
			    Yii::t('common', 'seconds'),
			);
			break;
		    case "y":
			$titles = array(
			    Yii::t('common', 'year'),
			    Yii::t('common', 'yeara'),
			    Yii::t('common', 'years'),
			);
			break;
		    case "m":
			$titles = array(
			    Yii::t('common', 'month'),
			    Yii::t('common', 'montha'),
			    Yii::t('common', 'months'),
			);
			break;
		    case "d":
			$titles = array(
			    Yii::t('common', 'day'),
			    Yii::t('common', 'daya'),
			    Yii::t('common', 'days'),
			);
			break;
		}
		return Utils::pluralForm($matches[1], $titles);
	    }
	}
    }

    /**
     * Функция склонения числительных в русском языке
     *
     * @param int    $number Число которое нужно просклонять
     * @param array  $titles Массив слов для склонения
     * @return string
     * */
    public function pluralForm($number, $titles) {
	$cases = array(2, 0, 1, 1, 1, 2);
	return number_format($number, 0, ',', ' ') . " " . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }

    /**
     * Форматирования размера файла в кБ, Мб, Гб итд
     *
     * @param string $size
     * @return string
     */
    public static function sizeFormat($size) {
	if (abs($size) > pow(1024, 4))
	    return round(($size / pow(1024, 4)), 2) . "&nbsp;Tb";
	if (abs($size) > pow(1024, 3))
	    return round(($size / pow(1024, 3)), 2) . "&nbsp;Gb";
	if (abs($size) > pow(1024, 2))
	    return round(($size / pow(1024, 2)), 2) . "&nbsp;Mb";
	if (abs($size) > pow(1024, 1))
	    return round(($size / pow(1024, 1)), 2) . "&nbsp;kb";
	return $size . "&nbsp;байт";
    }

    /**
     * форматирование периода времени в годах, месяцах, днях, часах итд
     *
     * @param unknown_type $size
     * @return unknown
     */
    function timeFormat($size) {
	$t = array();
	$y = 365 * 24 * 60 * 60;
	$o = $size % $y;
	$y = intval($size / $y);
	if ($y)
	    $t[] = Utils::spellPeriod($y . 'y');

	$size = $o;
	$m = 30 * 24 * 60 * 60;
	$o = $size % $m;
	$m = intval($size / $m);
	if ($m)
	    $t[] = Utils::spellPeriod($m . 'm');

	$size = $o;
	$d = 24 * 60 * 60;
	$o = $size % $d;
	$d = intval($size / $d);
	if ($d)
	    $t[] = Utils::spellPeriod($d . 'd');

	$size = $o;
	$h = 60 * 60;
	$o = $size % $h;
	$h = intval($size / $h);
	if ($h)
	    $t[] = Utils::spellPeriod($h . 'h');

	$size = $o;
	$m = 60;
	$o = $size % $m;
	$m = intval($size / $m);
	if ($m)
	    $t[] = Utils::spellPeriod($m . 'i');

	$size = $o;
	if ($size)
	    $t[] = Utils::spellPeriod($size . 's');

	$m = intval($size / 365 / 24 / 60 / 60);
	if ($m)
	    $t[] = $m;
	$y = intval($size / 365 / 24 / 60 / 60);
	if ($y)
	    $t[] = $y;
	return implode(' ', $t);
    }

    /**
     * заполнить ключи массива значениями из елемента массива
     *
     * @param string $indexName - название ключа, значения которого, пойдут в ключи результата
     * @param mixed $arr
     * @return mixed
     */
    public function pushIndexToKey($indexName, $arr) {
	$result = array();
	if (!empty($arr)) {
	    foreach ($arr as $a) {
		if (isset($a[$indexName])) {
		    $result[$a[$indexName]] = $a;
		} else {
		    return false;
		}
	    }
	}
	return $result;
    }

    /**
     * вернуть массив в виде ключ - значение
     *
     * @param mixed $arr - исходный массив
     * @param string $kName - название ключа, значения которого пойдут в ключи результата
     * @param string $vName - название ключа, значения которого пойдут в значения результата
     * @return mixed
     */
    public function arrayToKeyValues($arr, $kName, $vName) {
	$result = array();
	foreach ($arr as $a) {
	    $result[$a[$kName]] = $a[$vName];
	}
	return $result;
    }

    /**
     * получить список статусов видимости объекта
     *
     * @return mixed
     */
    public function getActiveStates() {
	return array(
	    _IS_GUEST_ => Yii::t('users', 'Visible for all'),
	    _IS_USER_ => Yii::t('users', 'Visible for users'),
	    _IS_MODERATOR_ => Yii::t('users', 'Visible for moderators'),
	    _IS_ADMIN_ => Yii::t('users', 'Visible for admins'),
	);
    }

    /**
     * получить список качества конвертирования видео
     * @param $ret = 'id' - с индексами в виде id, иначе - с индексами в виде строк
     * @return mixed
     */
    public function getVideoConverterQuality($ret = 'id') {
	$qArr = array(
	    _VIDEO_HIGH_ => 'High',
	    _VIDEO_MEDIUM_ => 'Medium',
	    _VIDEO_LOW_ => 'Low',
	    _VIDEO_ASIS_ => 'As is',
	);
	$res = array();
	foreach ($qArr as $k => $v) {
	    if ($ret == 'id') {
		$res[$k] = Yii::t('common', $v);
	    } else {
		$res[$v] = Yii::t('common', $v);
	    }
	}
	return $res;
    }

    /**
     * получить список типов параметров персональных данных
     *
     * @return mixed
     */
    public function getVideoConverterQuality() {
	return array(
	    _VIDEO_HIGH_ => Yii::t('common', 'High'),
	    _VIDEO_MEDIUM_ => Yii::t('common', 'Medium'),
	    _VIDEO_LOW_ => Yii::t('common', 'Low'),
	    _VIDEO_ASIS_ => Yii::t('common', 'As is'),
	);
    }

    /**
     * getSectionIdByName($name)
     * 
     * @param string $name 
     * @return mixed
     */
    public static function getSectionIdByName($name) {
	switch ($name) {
	    case 'video': return 1;
	    case 'audio': return 2;
	    case 'photo': return 3;
	    case 'docs': return 4;
	    default: return 0;
	}
    }

    /**
     * getSectionIdByName($ext)
     * 
     * @param string $ext
     * @return int
     */
    public static function getSectionIdByExt($ext) {
	$supported_video_ext = array('avi', 'mp4', 'mkv', 'flv', '3gp');
	$supported_audio_ext = array('mp3', 'm4a', 'flac', 'ogg', 'wma');
	if (in_array($ext, $supported_video_ext))
	    return 1;
	if (in_array($ext, $supported_audio_ext))
	    return 1;
	return 0;
    }

    public function getPersonaldataUItypes() {
	return array(
	    _PD_TEXT_ => Yii::t('params', 'Text'),
	    _PD_TEXTAREA_ => Yii::t('params', 'Textarea'),
	    _PD_FILE_ => Yii::t('params', 'File'),
	    _PD_PWD_ => Yii::t('params', 'Pwd'),
	    _PD_CHECKBOX_ => Yii::t('params', 'Checkbox'),
	    _PD_RADIO_ => Yii::t('params', 'Radio'),
	);
    }

    /**
     * получить список имен групп параметров персональных данных
     *
     * @return mixed
     */
    public function getPersonaldataGroups() {
	return array(
	    _PD_GROUP_COMMON_ => Yii::t('params', 'Common'),
	);
    }

}
