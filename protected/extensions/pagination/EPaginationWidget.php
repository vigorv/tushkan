<?php

class EPaginationWidget extends CWidget
{
	public $params;

	/**
	 * переопределяем путь к отображениям виджета с учетом текущей темы(шаблона)
	 *
	 * @param bool $checkTheme
	 * @return string
	 */
	public function getViewPath($checkTheme=false)
    {
        $themeManager = Yii::app()->themeManager;
        return $themeManager->basePath.DIRECTORY_SEPARATOR.Yii::app()->theme->name.DIRECTORY_SEPARATOR.'views/widgets';
    }

    /**
     * возвращает ссылку на указанную страницу
     *
     * @param integer $page
     * @return string
     */
    public function preparePageUrl($page)
    {
    	return $this->params['url'] . '/page/' . $page;
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
    	return $iCnt;
    }

	public function run()
	{
		$this->render('/pagination/pages');
	}
}