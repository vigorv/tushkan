<?php

/**
 * 
 * NlessController v1.0
 * 
 * Important: if you're using php < 5.3 then COMMENT the code block from line 101 to 119 (agar's parser)
 * 
 * 
 * */
class NlessController extends Controller {

    //parses on every request if true, uses cache if false
    public $dev = true;
    //minifies output (comments will be removed anyway)
    public $fullMinify = false;
    //parsers
    public $vendorAlias = 'application.vendors';
    public $lessParser = 'lessphp_leafo'; //lessphp_leafo|lessphp_dresende|lessphp_agar
    public $sassParser = 'phamlp'; //phamlp
    //default opts for SASS parsing
    public $sassOpt = array(
	'cache' => false,
	//'quiet'=>true,
	//'cache_location'=>'',
	//'load_paths'=>array(),
	'style' => 'compressed'//nested|expanded|compact|compressed
    );
    //default options for LESS parsing
    public $lessOpt = array(
	'importDir' => array(
	//...
	)
    );

    //Converts relative url to filesystem path
    function url2path($url) {
	$url = preg_replace('/\?.*/', '', $url); //removing query string
	$path = '';
	if (strpos($url, '/') === 0) {
	    $path = $_SERVER['DOCUMENT_ROOT'] . $url;
	} else {
	    $path = dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME']) . '/' . $url;
	}
	return $path;
    }

    //Adding missing semicolons to the end of lines
    function preprocess($css) {
	return preg_replace('/([^{};,)\s])(\s*[\r\n]\s*[^\s{}])/', '\\1;\\2', $css);
    }

    //Simple css minifier script
    //code based on: http://www.lateralcode.com/css-minifier/
    function minify($css) {

	//$css = preg_replace( '#/\*.*?\*/#Us', '', $css );
	return trim(
			str_replace(
				array('; ', ': ', ' {', '{ ', ', ', '} ', ';}'), array(';', ':', '{', '{', ',', '}', '}'), preg_replace('/\s+/', ' ', $css)
			)
	);
    }

    protected function parseLess($path) {

	if ($this->lessParser == 'lessphp_leafo') {

	    //require_once( Yii::getPathOfAlias($this->vendorAlias) . '/lessphp_leafo/lessc-ext.inc.php');
	    require_once( Yii::getPathOfAlias($this->vendorAlias) . '/lessphp_leafo/lessc.inc.php'); //use the above if you use ELESS from nlacsoft.net

	    $less = new lessc();
	    $this->lessOpt['importDir'][] = dirname($path) . '/';
	    $less->importDir = $this->lessOpt['importDir']; //dirname($path) .'/';
	    $content = $less->parse($this->preprocess(file_get_contents($path)));
	    return $content;
	} elseif ($this->lessParser == 'lessphp_dresende') {

	    require_once( Yii::getPathOfAlias($this->vendorAlias) . '/lessphp_dresende/lib/entities.less.class.php');

	    $less = new LessCode();
	    $less->parse($this->preprocess(file_get_contents($path)));
	    $content = $less->output();
	    return $content;
	} elseif ($this->lessParser == 'lessphp_agar') {

	    $lessLibraryPath = Yii::getPathOfAlias($this->vendorAlias) . '/lessphp_agar/lib/';

	    // Register an autoload function
	    spl_autoload_register(function($className) use ($lessLibraryPath) {
			$fileName = $lessLibraryPath . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
			if (file_exists($fileName)) {
			    require_once $fileName;
			}
		    });

	    $parser = new \Less\Parser();
	    $parser->getEnvironment()->setCompress($this->fullMinify);

	    $css = $parser
		    //->parseFile($path)
		    //->parse("@color: #4D926F; #header { color: @color; } h2 { color: @color; }")
		    ->parse($this->preprocess(file_get_contents($path)))
		    ->getCss();
	    return $css;
	}
    }

    protected function parseSass($path,$ext) {
	if ($this->sassParser == 'phamlp') {

	    require_once( Yii::getPathOfAlias($this->vendorAlias) . '/phamlp/sass/SassParser.php');

	    $this->sassOpt['syntax'] = $ext;
	    $this->sassOpt['filename'] = $path;
	    $sass = new SassParser($this->sassOpt);
	    if ($ext == 'sass')
		$content = $sass->toCss(file_get_contents($path), false);
	    else
		$content = $sass->toCss($this->preprocess(file_get_contents($path)), false);
	    return $content;
	}

	return '';
    }

    public function actionIndex() {

	if ($f = @$_GET['f']) {

	    $path = $this->url2path($f);
	    if (!file_exists($path))
		return;

	    //picking settings
	    if ($config = @Yii::app()->params['NlessController'])
		if (is_array($config))
		    foreach ($config as $key => $val) {
			if (property_exists($this, $key)) {
			    if (is_array($val)) {
				$this->$key = array_merge_recursive($this->$key, $val);
			    } else {
				$this->$key = $val;
			    }
			}
		    }

	    //if (!$this->bDev && $this->noCachePattern && preg_match($this->noCachePattern,$f)>0) {
	    if (isset($_GET['dev'])) {
		$this->dev = true;
		$this->fullMinify = false;
	    }
	    if (isset($_GET['prod'])) {
		$this->dev = false;
		$this->fullMinify = true;
	    }
	    if (@$_GET['parser']) {
		$this->lessParser = $this->sassParser = $_GET['parser'];
	    }

	    //$compiledPath = $path . '.compiled';
	    $pinf = pathinfo($path);
	    $ext = $pinf['extension'];
	    $compiledPath = Yii::getPathOfAlias('application.runtime.cache.nless_compiled.' . $ext);
	    if (!file_exists($compiledPath))
		mkdir($compiledPath, 0777, true);
	    $compiledPath .= '/' . $pinf['basename'];

	    if (!$this->dev && file_exists($compiledPath) && filemtime($path) < filemtime($compiledPath)) {
		header('Content-Type: text/css');
		echo file_get_contents($compiledPath);
		return;
	    }

	    $content = '';

	    try {

		if ($ext == 'less') {
		    $content = $this->parseLess($path);
		} elseif ($ext == 'scss' || $ext == 'sass' || $ext == 'sassc') {
		    $content = $this->parseSass($path,$ext);
		}
	    } catch (Exception $ex) {
		header('Content-Type: text/html');
		//displaying the error in the css
		echo $ex->getMessage();
		//also logging
		Yii::log('parsing ' . $f . ': ' . $ex->getMessage(), 'error');
		return;
	    }

	    //optionally minifying
	    if ($content && $this->fullMinify && ($ext == 'less' || $this->sassOpt['style'] != 'compressed'))
		$content = $this->minify($content);

	    //if (!$this->dev)
	    file_put_contents($compiledPath, $content);

	    header('Content-Type: text/css');
	    echo $content;
	}
    }

    public function controllerAccessRules() {
	return array(
	    array('allow',
		'users' => array('*')
	    )
	);
    }

}