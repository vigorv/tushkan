<?php
/**
 * компонент фильтра выборки с учетом входных данных
 *
 */
class CPageFilter
{
//	public $_params = array();
	//public $_lst = array();

	/**
	 * запомнить параметры текущей страницы
	 */
	public function setParams($params)
	{
		$this->_params = $params;
	}

	/**
	 * сгенерировать список параметров для подстановки в ссылку
	 *
	 * @param mixed $overloadParams - массив параметров (каждый параметр описан паой ключей 'name', 'value')
	 * @return string
	 */
	public function renderParams($overloadParams)
	{
		$params = $this->_params;
		foreach ($overloadParams as $p)
			$params[$p['name']] = $p['value'];
		$urlParams = '';
		if(!empty($params))
		foreach ($params as $k => $v)
		{
			if (($k === '') || ($v === ''))
				continue;
			if (!in_array($k, $this->_lst))
				continue;
			$urlParams .= '/' . $k . '/' . $v;
		}
		return $urlParams;
	}

	/**
	 * массив мзвестных названий параметров фильтра
	 *
	 * @param mixed $lst
	 */
	public function initParams($lst)
	{
		$this->_lst = $lst;
	}

	/**
	 * выборка из GET-параметров параметров сортировки (префикс "s_")
	 *
	 * @return unknown
	 */
	public function getFilterSort(&$sql)
	{
		$get = $_GET;
		$result = array();
		if (!empty($get))
		{
			$prefix = "s_";
			$sql = array();
			foreach ($get as $k => $v)
			{
				if (!in_array($k, $this->_lst))
					continue;
				if (substr($k, 0, 2) == $prefix)
				{
					$k = substr($k, 2);
					$result[$k] = $v;
					$sql[] = $k . ' ' . $v;
				}
			}
		}
		if (!empty($sql))
		{
			$sql = implode(',', $sql);
		}
		return $result;
	}
}