<?php
/**
* ownCloud - One Time Password plugin
*
* @author Frank Bongrand
* @copyright 2013 Frank Bongrand fbongrand@free.fr
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library. If not, see <http://www.gnu.org/licenses/>.
*
*/

include_once("user_otp/lib/multiotp/multiotp.class.php");

class OC_User_OTP extends OC_User_Database{
    private $mOtp;

    public function __construct(){
        $this->mOtp =  new multiotp(OCP\Config::getAppValue(
            'user_otp','EncryptionKey','DefaultCliEncryptionKey')
        );
        $this->mOtp->SetUsersFolder(
            OCP\Config::getAppValue(
                'user_otp','UsersFolder',getcwd()."/apps/user_otp/lib/multiotp/users/"
            )
        );
        if(DEBUG===1){
            $this->mOtp->EnableVerboseLog();
        }
        $this->mOtp->SetMaxBlockFailures(
            OCP\Config::getAppValue('user_otp','MaxBlockFailures',6)
        );
    }

    public function userExists($uid) {
        return $this->mOtp->CheckUserExists($uid);
    }
    
    public function implementsActions($actions){
        return true;
    }

    public function checkPassword($uid, $password) {
//    $tmp = $this->userExists($uid);
//    var_dump($tmp);
//    echo $uid.'toto'.$this->mOtp->GetUsersFolder();
//    exit;
        if(!$this->userExists($uid)){
            return parent::checkPassword($uid, $password);
        }else{
            $this->mOtp->SetUser($uid);
            $result = $this->mOtp->CheckToken($password);
            if ($result===0){
                return $uid;
            }else{
                if(isset($this->mOtp->_errors_text[$result])){
                    echo $this->mOtp->_errors_text[$result];
                }
                return false;
            }
        }
    }
    
}
?>
