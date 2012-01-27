<?php

class CFileTreeExt extends CTreeView {

    public function init() {
        if (isset($this->htmlOptions['id']))
            $id = $this->htmlOptions['id'];
        else
            $id = $this->htmlOptions['id'] = $this->getId();
        if ($this->url !== null)
            $this->url = CHtml::normalizeUrl($this->url);
        $cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('treeview');
        $options = $this->getClientOptions();
        $options = $options === array() ? '{}' : CJavaScript::encode($options);
        $cs->registerScript('/js/jquery.treeview.js', "jQuery(\"#{$id}\").treeview($options);");
       // if ($this->cssFile === null)
//            $cs->registerCssFile('/css/jquery.treeview.css');
//        else if ($this->cssFile !== false)
//            $cs->registerCssFile($this->cssFile);

        echo CHtml::tag('ul', $this->htmlOptions, false, false) . "\n";
        echo self::saveDataAsHtml($this->data);
    }

}