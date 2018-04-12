<?php namespace ZN\Authentication;
/**
 * ZN PHP Web Framework
 * 
 * "Simplicity is the ultimate sophistication." ~ Da Vinci
 * 
 * @package ZN
 * @license MIT [http://opensource.org/licenses/MIT]
 * @author  Ozan UYKUN [ozan@znframework.com]
 */

use ZN\Cryptography\Encode;

class Login extends UserExtends
{
    /**
     * Username
     * 
     * @param string $username
     * 
     * @return Login
     */
    public function username(String $username) : Login
    {
        Properties::$parameters['username'] = $username;

        return $this;
    }

    /**
     * Password
     * 
     * @param string $password
     * 
     * @return Login
     */
    public function password(String $password) : Login
    {
        Properties::$parameters['password'] = $password;

        return $this;
    }

    /**
     * Remember
     * 
     * @param bool $remember = true
     * 
     * @return Login
     */
    public function remember(Bool $remember = true) : Login
    {
        Properties::$parameters['remember'] = $remember;

        return $this;
    }

    /**
     * Do Login
     * 
     * @param string $username   = NULL
     * @param string $password   = NULL
     * @param mixed  $rememberMe = false
     * 
     * @return bool
     */
    public function do(String $username = NULL, String $password = NULL, $rememberMe = false) : Bool
    {
        $username   = Properties::$parameters['username'] ?? $username;
        $password   = Properties::$parameters['password'] ?? $password;
        $rememberMe = Properties::$parameters['remember'] ?? $rememberMe;

        Properties::$parameters = [];

        if( ! is_scalar($rememberMe) )
        {
            $rememberMe = false;
        }

        $password   = ! empty($this->encodeType) ? Encode\Type::create($password, $this->encodeType) : $password;

        $this->_multiUsernameColumns($username);

        $r = $this->dbClass->where($this->usernameColumn, $username)
               ->get($this->tableName)
               ->row();

        if( ! isset($r->{$this->passwordColumn}) )
        {
            return ! Properties::$error = $this->getLang['loginError'];
        }

        $passwordControl   = $r->{$this->passwordColumn};
        $bannedControl     = '';
        $activationControl = '';

        if( ! empty($this->bannedColumn) )
        {
            $banned = $this->bannedColumn ;
            $bannedControl = $r->$banned ;
        }

        if( ! empty($this->activationColumn) )
        {
            $activationControl = $r->{$this->activationColumn};
        }

        if( ! empty($r->{$this->usernameColumn}) && $passwordControl == $password )
        {
            if( ! empty($this->bannedColumn) && ! empty($bannedControl) )
            {
                return ! Properties::$error = $this->getLang['bannedError'];
            }

            if( ! empty($this->activationColumn) && empty($activationControl) )
            {
                return ! Properties::$error = $this->getLang['activationError'];
            }

            $this->sessionClass->insert($this->usernameColumn, $username);
            $this->sessionClass->insert($this->passwordColumn, $password);

            if( ! empty($rememberMe) )
            {
                if( $this->cookieClass->select($this->usernameColumn) !== $username )
                {
                    $this->cookieClass->insert($this->usernameColumn, $username);
                    $this->cookieClass->insert($this->passwordColumn, $password);
                }
            }

            if( ! empty($this->activeColumn) )
            {
                $this->dbClass->where($this->usernameColumn, $username)->update($this->tableName, [$this->activeColumn  => 1]);
            }

            return Properties::$success = $this->getLang['loginSuccess'];
        }
        else
        {
            return ! Properties::$error = $this->getLang['loginError'];
        }
    }

    /**
     * Is Login
     * 
     * @param void
     * 
     * @return bool
     */
    public function is() : Bool
    {
        $getUserData = (new Data)->get($this->tableName);

        if( ! empty($this->bannedColumn) && ! empty($getUserData->{$this->bannedColumn}) )
        {
             (new Logout)->do();
        }

        $cUsername  = $this->cookieClass->select($this->usernameColumn);
        $cPassword  = $this->cookieClass->select($this->passwordColumn);
        $result     = NULL;

        if( ! empty($cUsername) && ! empty($cPassword) )
        {
            $result = $this->dbClass->where($this->usernameColumn, $cUsername, 'and')
                        ->where($this->passwordColumn, $cPassword)
                        ->get($this->tableName)
                        ->totalRows();
        }

        if( isset($getUserData->{$this->usernameColumn}) )
        {
            $isLogin = true;
        }
        elseif( ! empty($result) )
        {
            $this->sessionClass->insert($this->usernameColumn, $cUsername);
            $this->sessionClass->insert($this->passwordColumn, $cPassword);

            $isLogin = true;
        }
        else
        {
            $isLogin = false;
        }

        return $isLogin;
    }
}
