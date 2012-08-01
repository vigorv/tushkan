<?php

class ETushkanWidget extends CWidget
{
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
}