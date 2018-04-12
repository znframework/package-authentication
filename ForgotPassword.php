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

use ZN\IS;
use ZN\Inclusion;
use ZN\Singleton;
use ZN\Request\URL;
use ZN\Cryptography\Encode;

class ForgotPassword extends UserExtends
{
    /**
     * Email
     * 
     * @param string $email
     * 
     * @return ForgotPassword
     */
    public function email(String $email) : ForgotPassword
    {
        Properties::$parameters['email'] = $email;

        return $this;
    }

    /**
     * Verification
     * 
     * @param string $verification
     * 
     * @return ForgotPassword
     */
    public function verification(String $verification) : ForgotPassword
    {
        Properties::$parameters['verification'] = $verification;

        return $this;
    }

    /**
     * Forgot Password
     * 
     * @param string $email          = NULL
     * @param string $returnLinkPath = NULL
     * 
     * @return bool
     */
    public function do(String $email = NULL, String $returnLinkPath = NULL) : Bool
    {
        $email          = Properties::$parameters['email']        ?? $email;
        $verification   = Properties::$parameters['verification'] ?? NULL;
        $returnLinkPath = Properties::$parameters['returnLink']   ?? $returnLinkPath;

        Properties::$parameters = [];

        if( ! empty($this->emailColumn) )
        {
            $this->dbClass->where($this->emailColumn, $email);
        }
        else
        {
            $this->dbClass->where($this->usernameColumn, $email);
        }

        $row = $this->dbClass->get($this->tableName)->row();

        if( isset($row->{$this->usernameColumn}) )
        {
            if( ! empty($this->verificationColumn) )
            {
                if( $verification !== $row->{$this->verificationColumn} )
                {
                    return ! Properties::$error = $this->getLang['verificationOrEmailError'];
                }
            }
            
            if( ! IS::url($returnLinkPath) )
            {
                $returnLinkPath = URL::site($returnLinkPath);
            }

            $newPassword    = Encode\RandomPassword::create(10);
            $encodePassword = ! empty($this->encodeType) 
                              ? Encode\Type::create($newPassword, $this->encodeType) 
                              : $newPassword;

            $templateData = array
            (
                'usernameColumn' => $row->{$this->usernameColumn},
                'newPassword'    => $newPassword,
                'returnLinkPath' => $returnLinkPath
            );

            $message = Inclusion\Template::use('UserEmail/ForgotPassword', $templateData, true);

            $emailClass = Singleton::class('ZN\Email\Sender');

            $emailClass->sender($this->senderMail, $this->senderName)
                       ->receiver($email, $email)
                       ->subject($this->getLang['newYourPassword'])
                       ->content($message);

            if( $emailClass->send() )
            {
                if( ! empty($this->emailColumn) )
                {
                    $this->dbClass->where($this->emailColumn, $email, 'and');
                }
                else
                {
                    $this->dbClass->where($this->usernameColumn, $email, 'and');
                }

                if( $this->dbClass->update($this->tableName, [$this->passwordColumn => $encodePassword]) )
                {
                    return Properties::$success = $this->getLang['forgotPasswordSuccess'];
                }

                return ! Properties::$error = $this->getLang['updateError'];
            }
            else
            {
                return ! Properties::$error = $this->getLang['emailError'];
            }
        }
        else
        {
            return ! Properties::$error = $this->getLang['forgotPasswordError'];
        }
    }
}
