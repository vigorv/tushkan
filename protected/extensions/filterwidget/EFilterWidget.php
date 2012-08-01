<?php
Yii::import('ext.tushkanwidget.ETushkanWidget');

class EFilterWidget extends ETushkanWidget
{
	public $filterName; //ИМЯ ФИЛЬТРА (СПОЛЬЗУЕТСЯ КАК ИМЯ ОТОБРАЖЕНИЯ ФИЛЬТРА)
	public $method = 'POST'; //МЕТОД ОТПРАВКИ ДАННЫХ ФИЛЬТРА
	public $fields = array(); //МАССИВ ДАННЫХ ДЛЯ ФОРМИРОВАНИЯ ПОЛЕЙ, СТРУКТУРА НА СВОЕ УСМОТРЕНИЕ

	public function run()
	{
		$formHead = $this->render('/filter/formhead', array('method' => $this->method), true);
		$formEnd = $this->render('/filter/formend', array(), true);
		$this->render('/filter/' . $this->filterName, array('formHead' => $formHead, 'formEnd' => $formEnd, 'fields' => $this->fields));
	}
}

?>