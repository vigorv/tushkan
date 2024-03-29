<?php

/**
 * @property string $email
 * @property int $userPower
 * @property $userGroupId
 * @property $userZones
 * @property bool $userInZone
 */

    class WebUser extends CBehavior{
        /**
         * @return mixed the unique identifier for the user. If null, it means the user is a guest.
         */
        public function getEmail()
        {
            return Yii::app()->user->getState('__email');
        }

        /**
         * @param mixed $value the unique identifier for the user. If null, it means the user is a guest.
         */
        public function setEmail($value)
        {
            Yii::app()->user->setState('__email',$value);
        }

        /**
         * @return mixed
         */
        public function getUserPower(){
            return Yii::app()->user->getState('__UserPower');
        }

        /**
         * @param mixed $value
         */

        public function setUserPower($value){
            return Yii::app()->user->setState('__UserPower',$value);
        }

        /**
         * @return mixed
         */
        public function getUserGroupId(){
            return Yii::app()->user->getState('__UserGroupId');
        }

        /**
         * @param mixed $value
         */

        public function setUserGroupId($value){
            return Yii::app()->user->setState('__UserGroupId',$value);
        }

        /**
         * @return mixed
         */
        public function getUserZones(){
            return Yii::app()->user->getState('__UserZones');
        }

        /**
         * @param mixed $value
         */

        public function setUserZones($value){
            return Yii::app()->user->setState('__UserZones',$value);
        }


        /**
         * @return mixed
         */
        public function getUserInZone(){
            return Yii::app()->user->getState('__UserInZone');
        }

        /**
         * @param mixed $value
         */

        public function setUserInZone($value){
            return Yii::app()->user->setState('__UserInZone',$value);
        }


    }