<?php

/**
 * CFiletypes class file
 * static func for formated out
 *
 * @author Snow <snowcanbe@gmail.com>
 * @copyright Copyright &copy; 2011
 */
class CFiletypes {

    static function ParsePrint($array, $type) {
	switch ($type) {
	    case 'V1':
		foreach ($array as $file) {
		    if (!empty($file['filename']))
			$poster = Yii::app()->params['tushkan']['postersURL'] . '/smallposter/' . $file['filename'];
		    else
			$poster = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';
		    echo '<li id="v' . $file['id'] . '"><img src=' . $poster.='><br/>';
		    echo '<span>' . $file['title'] . '</span>';
		    echo '</li>';
		}
		break;
	    case 'AA1':
		foreach ($array as $file) {
		    echo '<li fname="' . $file['filename'] . '><img width="100px" height="150px"/><br/>';
		    echo '<span>' . $file['name'] . '</span>';
		    echo '</li>';
		}
		break;
	    case 'FL1':
		foreach ($array as $file) {
		    $img_path = '/images/files/';
		    //var_dump($file);
		    $ads = '';
		    if ($file['is_dir']) {
			$img = 'folder.png';
			$ads = 'dir=1';
			echo '<li fid="' . $file['id'] . '" ' . $ads . ' >
			    <a href="/files/dview/' . $file['id'] . '">
			    <img width="32px" height="32px" src="' . $img_path . $img . '" /><br/>
			    <span>' . $file['title'] . '</span>
			    </a>
			    </li>';
		    } else {
			$ftype = pathinfo($file['title'], PATHINFO_EXTENSION);
			switch ($ftype) {
			    case 'txt':
				$img = '64x64/mimetypes/txt.png';
				break;
			    default:
				$img = '64x64/mimetypes/unknown.png';
			}
			echo '<li fid="' . $file['id'] . '" ' . $ads . ' >
			    <a href="/files/fview/' . $file['id'] . '">
			    <img width="32px" height="32px" src="' . $img_path . $img . '" /><br/>
			    <span>' . $file['title'] . '</span>
			    </a>
			    </li>';
		    }
		}
		break;
	    default:
	}
    }

}
