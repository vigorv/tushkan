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


    }