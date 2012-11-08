<?php

/**
 * @property $id
 * @property $product_id
 * @property $original_id
 * @property $task_id
 * @property $cmd_id
 * @property $info
 * @property $priority
 * @property $state
 * @property $station_id
 * @property $partner_id
 * @property $user_id
 * @property $original_variant_id
 * @property $date_start
 * @property $path
 *
 */

class CConvertQueue extends CActiveRecord
{

    /**
     *
     * @param string $className
     * @return CConvertQueue
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{income_queue}}';
    }

    public static function deleteUserQueue($uid, $qid)
    {
        $cmd = Yii::app()->db->createCommand()
            ->select('id')
            ->from('{{income_queue}}')
            ->where('id = :qid AND user_id = :uid');
        $cmd->bindParam(':qid', $qid, PDO::PARAM_INT);
        $cmd->bindParam(':uid', $uid, PDO::PARAM_INT);
        $info = $cmd->queryScalar();
        if ($info) {
            $sql = 'DELETE FROM {{income_queue}} WHERE id = :qid';
            $cmd = Yii::app()->db->createCommand($sql);
            $cmd->bindParam(':qid', $qid, PDO::PARAM_INT);
            $cmd->query();
        }
    }

    public static function restartUserQueue($uid, $qid)
    {
        $cmd = Yii::app()->db->createCommand()
            ->select('id')
            ->from('{{income_queue}}')
            ->where('id = :qid AND user_id = :uid AND original_id > 0');
        $cmd->bindParam(':qid', $qid, PDO::PARAM_INT);
        $cmd->bindParam(':uid', $uid, PDO::PARAM_INT);
        $info = $cmd->queryScalar();
        if ($info) {
            $sql = 'UPDATE {{income_queue}} SET `cmd_id`=0, `state`=0 WHERE id = :qid';
            $cmd = Yii::app()->db->createCommand($sql);
            $cmd->bindParam(':qid', $qid, PDO::PARAM_INT);
            $cmd->query();
        }
    }


    public static function findObjects($search = '', $user_id = 0, $page = 1, $per_page = 10)
    {
        $offset = ($page - 1) * $per_page;
        return Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{income_queue}}')
            ->where('user_id = :user_id AND cmd_id < 50 ', array(':user_id' => $user_id))
            ->offset($offset)
            ->limit($per_page)
            ->queryAll();
    }

    public static function countFoundObjects($search = '', $user_id = 0)
    {
        return Yii::app()->db->createCommand()
            ->select('Count(*)')
            ->from('{{income_queue}}')
            ->where('user_id = :user_id AND cmd_id < 50', array(':user_id' => $user_id))
            ->queryScalar();
    }


    public static function getUserObject($queue_id,$user_id)
    {
        return Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{income_queue}}')
            ->where('user_id = :user_id AND id=:queue_id AND cmd_id < 50', array(':user_id' => $user_id,':queue_id'=>$queue_id))
            ->limit(1)
            ->queryAll();
    }

}

?>
