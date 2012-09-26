    <div class="tabbable">
        <div class="tab-content">
            <div class="tab-pane active">
            <div class="span12 no-horizontal-margin my-catalog">
                <?php
                $results = 0;
                if (!empty($pstContent)) :
                    $results++;
                    ?>
                    <div class="span12 no-horizontal-margin more-link"><a href="#">Витрины</a></div>
                    <div id="userproductsdiv" class="span12 no-horizontal-margin type">
                        <?php echo $pstContent; ?>
                    </div>
                    <? endif;
                if (!empty($obj)):
                    $results++;
                    ?>
                    <div class="span12 no-horizontal-margin more-link"><a href="#">Моя Библиотека</a></div>
                    <div class="pad-content clearfix">
                        <ul>
                            <?php echo CFiletypes::ParsePrint($obj, 'TL2'); ?>
                        </ul>
                    </div>

                    <?php endif;
                if (!empty($unt)):
                    $results++; ?>
                    <div class="span12 no-horizontal-margin more-link"><a href="#">Мои файлы</a></div>
                    <div class="pad-content">
                        <ul>
                            <?php echo CFiletypes::ParsePrint($unt, 'UTL1'); ?>
                        </ul>
                    </div>
                    <?php endif;
                if (empty($results)): ?>
                    <div class="span12 no-horizontal-margin more-link"><a href="#">Результаты поиска</a></div>
                    <div class="pad-content">
                        <?php echo Yii::t('common', 'Nothing was found'); ?>
                    </div>
                            </div>
                        <?php endif; ?>
            </div>
        </div>
    </div>
