<?php
define('_BANSTATE_READONLY_', 0);
define('_BANSTATE_FULL_', 10);
define('_BANREASON_ABONENTFEE_', 0);
define('_BANREASON_VIOLATION_', 1);
define('_CURRENCY_', 'rur');

class Utils
{
	/**
	 * парсинг выражения записи периода времени
	 *
	 * @param string $period - цифробуквенное обозначение периода (5d, 1m, 1000i)
	 * символы в выражения как у функции time()
	 * @param string $from - дата Y-m-d H:i:s, с которой отсчитываем период
	 * @return integer
	 */
	public function parsePeriod($period, $from = '')
	{
		$sek = 0;
		if (!empty($period))
		{
			$matches = array();
			preg_match('/([0-9]{1,})([a-z]){1}/', strtolower($period), $matches);
			if (!empty($matches[1]) && !empty($matches[2]))
			{
				if (empty($from))
					$from = 0;
				else
					$from = strtotime($from);
				$s = $i = $h = $d = $m = $y = 0;
				$$matches[2] = $matches[1];

				$sek = mktime(
					(date('H', $from) + $h),
					(date('i', $from) + $i),
					(date('s', $from) + $s),
					(date('m', $from) + $m),
					(date('d', $from) + $d),
					(date('y', $from) + $y)
						) - $from;
			}
		}
		return $sek;
	}

	public function spellPeriod($period)
	{
		$sek = 0;
		if (!empty($period))
		{
			$matches = array();
			preg_match('/([0-9]{1,})([a-z]){1}/', strtolower($period), $matches);
			if (!empty($matches[1]) && !empty($matches[2]))
			{
				$titles = array();
				switch ($matches[2])
				{
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
     **/
    function pluralForm($number, $titles)
    {
        $cases = array (2, 0, 1, 1, 1, 2);
        return number_format($number, 0 , ',', ' ' ) . " " . $titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
    }

    /**
     * Форматирования размера файла в кБ, Мб, Гб итд
     *
     * @param string $size
     * @return string
     */
    function sizeFormat($size)
    {
        if (abs($size) > pow(1024, 4)) return round(($size / pow(1024, 4)), 2) . "&nbsp;Tb";
        if (abs($size) > pow(1024, 3)) return round(($size / pow(1024, 3)), 2) . "&nbsp;Gb";
        if (abs($size) > pow(1024, 2)) return round(($size / pow(1024, 2)), 2) . "&nbsp;Mb";
        if (abs($size) > pow(1024, 1)) return round(($size / pow(1024, 1)), 2) . "&nbsp;kb";
        return $size . "&nbsp;байт";
    }

    /**
     * форматирование периода времени в годах, месяцах, днях, часах итд
     *
     * @param unknown_type $size
     * @return unknown
     */
    function timeFormat($size)
    {
    	$t = array();
    	$y = 365*24*60*60;
    	$o = $size % $y;
    	$y = intval($size/$y);
    	if ($y) $t[] = Utils::spellPeriod($y . 'y');

    	$size = $o;
    	$m = 30*24*60*60;
    	$o = $size % $m;
    	$m = intval($size/$m);
    	if ($m) $t[] = Utils::spellPeriod($m . 'm');

    	$size = $o;
    	$d = 24*60*60;
    	$o = $size % $d;
    	$d = intval($size/$d);
    	if ($d) $t[] = Utils::spellPeriod($d . 'd');

    	$size = $o;
    	$h = 60*60;
    	$o = $size % $h;
    	$h = intval($size/$h);
    	if ($h) $t[] = Utils::spellPeriod($h . 'h');

    	$size = $o;
    	$m = 60;
    	$o = $size % $m;
    	$m = intval($size/$m);
    	if ($m) $t[] = Utils::spellPeriod($m . 'i');

    	$size = $o;
    	if ($size) $t[] = Utils::spellPeriod($size . 's');

    	$m = intval($size/365/24/60/60);
    	if ($m) $t[] = $m;
    	$y = intval($size/365/24/60/60);
    	if ($y) $t[] = $y;
        return implode(' ', $t);
    }

}
