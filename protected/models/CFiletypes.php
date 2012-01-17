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
                    echo '<li><img src=' . $poster.='><br/>';
                    echo '<span>' . $file['title'] . '</span>';
                    echo '</li>';
                }
                break;
            case 'AA1':
                foreach ($array as $file) {
                    echo '<li><img width="100px" height="150px"/><br/>';
                    echo $file['name'];
                    echo '</li>';
                }
                break;
            case 'FL1':
                foreach ($array as $file) {
                    echo '<li><img /><br/>';
                    echo $file['name'];
                    echo '</li>';
                }
                break;
            default:
        }
    }

}
