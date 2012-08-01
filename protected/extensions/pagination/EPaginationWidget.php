<?php
Yii::import('ext.tushkanwidget.ETushkanWidget');
Yii::import('ext.classes.Utils');

class EPaginationWidget extends ETushkanWidget
{
	public $interval = 7;
	public $jump = 10;

	public $params;

    /**
     * возвращает ссылку на указанную страницу
     *
     * @param integer $page
     * @return string
     */
    public function preparePageUrl($page)
    {
    	$params = Utils::prepareUrlParams(false, false);
    	$params['page'] = 'page/' . intval($page);
    	return $this->params['url'] . '/' . implode('/', $params);
    }

    /**
     * возвращает кол-во страниц
     * на основании инициализационных данных виджета
     *
     * @return integer
     */
    public function getPageCount()
    {
    	if (empty($this->params['limit']) || empty($this->params['total'])) return 0;
    	$cnt = $this->params['total'] / $this->params['limit'];
    	$iCnt = intval($cnt);
    	if ($cnt != $iCnt)
    		$iCnt++;
//echo $this->params['total'] . ' / ' . $this->params['limit'] . '<br/>';
    	return $iCnt;
    }

	public function run()
	{
		$this->render('/pagination/pages');
	}

	public function getPagePairs()
	{
		$maxPage = $this->getPageCount() - 1;
		$page = $this->params['page'];

		$output = array();

		$first = 0;
		$last = $maxPage;
		if ($page < $first) $page = 0;
		if ($page > $last) $page = 0;

		$start = $page - intval($this->interval / 2);
		$pgDown = $start - $this->jump;
		$prev = $page - 1;
		if ($pgDown < $first) $pgDown = $first;

		if ($start < 0)
		{
			//$first = -1; //ОТДЕЛЬНУЮ ССЫЛКУ НА ПЕРВУЮ СТРАНИЦУ ВЫВОДИТЬ НЕ БУДЕМ
			$pgDown = -1;
		}
		$start = max($start, 0);
		$finish = $start + $this->interval;
		$finish = min($finish, $maxPage);

		if ($finish - $start < $this->interval)
		{
			$start = $finish - $this->interval;
		}
		$pgDown = $start - $this->jump;
		if ($pgDown < $first) $pgDown = $first;
		if ($start < 0)
		{
			//$first = -1; //ОТДЕЛЬНУЮ ССЫЛКУ НА ПЕРВУЮ СТРАНИЦУ ВЫВОДИТЬ НЕ БУДЕМ
			$pgDown = -1;
		}
		$start = max($start, 0);

		$next = $page + 1;
		$pgUp = $finish + $this->jump;
		if ($pgUp > $last) $pgUp = $last;

		if ($last == $finish)
		{
			//$last = -1; //ОТДЕЛЬНУЮ ССЫЛКУ НА ПОСЛЕДНЮЮ СТРАНИЦУ ВЫВОДИТЬ НЕ БУДЕМ
			$pgUp = -1;
		}

		if (($pgDown >= 0) && ($pgDown < $start))
		{
			$output[] = array('url' => $this->preparePageUrl($pgDown), 'title' => '&laquo;');//REWIND
		}
		if ($prev >= 0)
		{
			$output[] = array('url' => $this->preparePageUrl($prev), 'title' => '&larr;');//PREVIOUS
		}
		if (($first >= 0) && ($first < $start))
		{
			$output[] = array('url' => $this->preparePageUrl($first), 'title' => $first + 1, 'is_current' => ($first == $page));
			$output[] = array('url' => '', 'title' => '...');
		}

		for ($cnt = $start; $cnt <= $finish; $cnt++)
		{
			if ($cnt == $page)
				$output[] = array('url' => '', 'title' => $page + 1, 'is_current' => ($cnt == $page));
			else
				$output[] = array('url' => $this->preparePageUrl($cnt), 'title' => $cnt + 1);
		}
		if (($last >= 0) && ($last > $finish))
		{
			$output[] = array('url' => '', 'title' => '...');
			$output[] = array('url' => $this->preparePageUrl($last), 'title' => $last + 1, 'is_current' => ($last == $page));
		}
		if ($next <= $maxPage)
		{
			$output[] = array('url' => $this->preparePageUrl($next), 'title' => '&rarr;');
		}
		if (($pgUp >= 0) && ($pgUp > $finish))
		{
			$output[] = array('url' => $this->preparePageUrl($pgUp), 'title' => '&raquo;');//FORWARD
		}

		return $output;
	}
}