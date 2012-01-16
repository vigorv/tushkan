<?php

class CUserfilelist {

    var $path = '/var/myCloud/UsersData/';

    function GetUserPath($id=0) {
        $str = $this->path . $id;
        return $str;
    }
    /**    
     * Make path corect
     * @param string $path
     * @return string $path 
     */
    public function CheckPath(&$path){
        //TO DO: check path
        return $path;
    }

    /**
     * Load filelist in folder
     * @param string $path
     * @return string filelist 
     */
    
    //ls -p -s -b -1 -A -B -X 
    public function LoadFileList(&$path) {
        $user_id = Yii::app()->user->id;
        if (!$user_id)
            $user_id = '';
        $full_path = $this->GetUserPath($user_id) . $path;
        $out = array();
        $content = '';
        $i = '';
        $res = exec("find $full_path -maxdepth 1 -name 'filelist*' -type f", $out);
        foreach ($out as $filelist) {
            $content.=file_get_contents($filelist);
        }
    return $content;
    }
    
    /**
     * Prepare filelist
     * @param string $filelist 
     * @return array
     */
    public function ParseFileList(&$filelist){
        //TO DO: parse filelist
    }
 
}

?>
