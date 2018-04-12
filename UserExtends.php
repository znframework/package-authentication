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

use ZN\Lang;
use ZN\Config;
use ZN\Singleton;
use ZN\Cryptography\Encode;

class UserExtends
{
    /**
     * Get user config
     * 
     * @var array
     */
    protected $getConfig;

    /**
     * Magic constructor
     * 
     * @param void
     * 
     * @return void
     */
    public function __construct()
    {
        # If no configuration file is found, predefined settings will be enabled.
        $this->getConfig  = Config::default('ZN\Authentication\AuthenticationDefaultConfiguration')
                                  ::get('Authentication');
        
        # When the user is registered in, 
        # the algorithm to encrypt the password is set.
        $this->encodeType = $this->getConfig['encode'];

        # Table name where user information will be stored.
        $this->tableName  = $this->getConfig['matching']['table'];

        # The User class contains matching information for column 
        # information that is required for some operations.
        $matchingColumn = $this->getConfig['matching']['columns'];

        $this->usernameColumn     = $matchingColumn['username'];
        $this->passwordColumn     = $matchingColumn['password'];
        $this->emailColumn        = $matchingColumn['email'];
        $this->bannedColumn       = $matchingColumn['banned'];
        $this->activeColumn       = $matchingColumn['active'];
        $this->activationColumn   = $matchingColumn['activation'];
        $this->bannedColumn       = $matchingColumn['banned'];
        $this->verificationColumn = $matchingColumn['verification'];  
        $this->otherLoginColumns  = $matchingColumn['otherLogin'];     

        # If the user's information is stored in more than one tablature, 
        # this table is used so that it can be accessed by the User class.
        $joining = $this->getConfig['joining'];

        $this->joinTables = $joining['tables'];
        $this->joinColumn = $joining['column'];

        # It contains pre-defined e-mail and name information 
        # for user class's e-mail sending methods.
        $emailSenderInfo = $this->getConfig['emailSenderInfo'];

        $this->senderMail = $emailSenderInfo['mail'];
        $this->senderName = $emailSenderInfo['name'];
        
        # If no language file is found, predefined settings will be enabled.
        $this->getLang = Lang::default('ZN\Authentication\AuthenticationDefaultLanguage')
                             ::select('Authentication');

        # PThe necessary classes are called for the User class.
        $this->dbClass      = Singleton::class('ZN\Database\DB');
        $this->sessionClass = Singleton::class('ZN\Storage\Session');
        $this->cookieClass  = Singleton::class('ZN\Storage\Cookie');
    }

    /**
     * Set column 
     * 
     * @param string $column
     * @param mixed  $value
     * 
     * @return UserExtends
     */
    public function column(String $column, $value) : UserExtends
    {
        Properties::$parameters['column'][$column] = $value;

        return $this;
    }

    /**
     * Return link
     * 
     * @param string $returnLink
     * 
     * @return UserExtends
     */
    public function returnLink(String $returnLink) : UserExtends
    {
        Properties::$parameters['returnLink'] = $returnLink;

        return $this;
    }

    /**
     * Get user table by username
     */
    protected function getUserTableByUsername($username)
    {
        return $this->dbClass->where($this->usernameColumn, $username)->get($this->tableName);
    }

    /**
     * Protected set error message
     */
    protected function setErrorMessage($string)
    {
        return ! Properties::$error = $this->getLang[$string];
    }

    /**
     * Protected set error message
     */
    protected function setSuccessMessage($string)
    {
        return Properties::$success = $this->getLang[$string];
    }

    /**
     * Protected get encryption password
     */
    protected function getEncryptionPassword($password)
    {
        return ! empty($this->encodeType) ? Encode\Type::create($password, $this->encodeType) : $password;
    }

    /**
     * protected multi username columns
     * 
     * @param string $value
     * 
     * @return void
     */
    protected function _multiUsernameColumns($value)
    {
        if( ! empty($this->otherLoginColumns) )
        {
            foreach( $this->otherLoginColumns as $column )
            {
                $this->dbClass->where($column, $value, 'or');
            }
        }
    }
}
