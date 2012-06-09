<?php

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

    }