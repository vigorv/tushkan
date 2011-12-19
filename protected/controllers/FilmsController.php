<?php

class FilmsController extends Controller
{

	public function actionIndex()
	{
		$this->breadcrumbs = array(
			Yii::t('common', 'Admin index'),
		);
		$this->render('/films/index');
	}

	public function actionAdmin()
	{
		$this->layout = '//layouts/admin';
		$this->breadcrumbs = array(
			Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
			Yii::t('common', 'Administrate films'),
		);

		$films = Yii::app()->db->createCommand()
			->select('f.id, f.y, f.title, p.filename, GROUP_CONCAT(DISTINCT c.title SEPARATOR ", ") AS country')
    		->from('{{films}} f')
    		->join('{{countries_films}} cf', 'cf.film_id=f.id')
    		->join('{{countries}} c', 'cf.country_id=c.id')
    		->leftJoin('{{film_pictures}} p', 'p.film_id=f.id AND p.tp="smallposter"')
    		->group('f.id')
    		->queryAll();

		$this->render('/films/admin', array('films' => $films));
	}

	/**
	 * действие инлайн редактирования
	 *
	 */
	public function actionEdit($id = 0)
	{
		$this->layout = '//layouts/admin';
		$this->breadcrumbs = array(
			Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
			Yii::t('common', 'Administrate films') => array($this->createUrl('films/admin')),
			Yii::t('common', 'Edit'),
		);

		$cmd = Yii::app()->db->createCommand()
			->select('f.id, f.y, f.title, d.description, p.filename, GROUP_CONCAT(DISTINCT c.title SEPARATOR ", ") AS country')
    		->from('{{films}} f')
    		->join('{{film_descriptions}} d', 'd.film_id=f.id')
    		->join('{{countries_films}} cf', 'cf.film_id=f.id')
    		->join('{{countries}} c', 'cf.country_id=c.id')
    		->leftJoin('{{film_pictures}} p', 'p.film_id=f.id AND p.tp="smallposter"')
    		->where('f.id=:id')
    		->group('f.id');

    	$cmd->bindParam(':id', $id, PDO::PARAM_INT);
    	$film = $cmd->queryAll();


		$this->render('/films/edit', array('film' => $film));
	}

	/**
	 * действие формы добавления
	 *
	 */
	public function actionForm()
	{
		$this->layout = '//layouts/admin';
		$this->breadcrumbs = array(
			Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
			Yii::t('common', 'Administrate films') => array($this->createUrl('films/admin')),
			Yii::t('films', 'Add Film'),
		);

		$cLst = Yii::app()->db->createCommand()
			->select('id, title')
			->from('{{countries}}')
			->queryAll();

		$countries = $chkCountries = array();

		$filmForm = new FilmForm();
		if(isset($_POST['FilmForm']))
		{
		    $filmForm->attributes = $_POST['FilmForm'];

		    if($filmForm->validate())
		    {
		        //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
		        $films = new films();
				$attrs = $filmForm->getAttributes();
				foreach ($attrs as $k => $v)
				{
		        	$films->{$k} = $v;
				}
				$films->created = date('Y-m-d H:i:s');
				$films->modified = date('Y-m-d H:i:s');
		        $films->save();
				Yii::app()->user->setFlash('success', Yii::t('films', 'Film Saved'));
				//$this->redirect('/films/admin');
		    }

		    if (!empty($_POST['FilmForm']['countries']))
		    {
				$chkCountries = $_POST['FilmForm']['countries'];
			}
			$countries = array();
	    	foreach ($cLst as $country)
	    	{
	    		$countries[$country['id']] = $country['title'];
	    	}
		}
		else
		{
	    	foreach ($cLst as $country)
	    	{
	    		$countries[$country['id']] = $country['title'];
	    	}
		}
		$this->render('/films/form', array('model' => $filmForm, 'countries' => $countries, 'chkCountries' => $chkCountries));
	}
}