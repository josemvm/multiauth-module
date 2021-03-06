<?php
/**
 * @package    jelix
 * @subpackage auth_driver
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */


/**
 * authentication provider for the multiauth plugin
 *
 * it uses the same table for accounts used by multiauth
 *
 * @package    jelix
 * @subpackage multiauth_provider
 */
class dbaccountsProvider extends \Jelix\MultiAuth\ProviderAbstract
{
    protected $labelLocale = 'multiauth~multiauth.provider.dbaccounts.label';

    /**
     * @inheritdoc
     */
    public function __construct($params)
    {
        parent::__construct($params);
        if (isset($this->_params['automaticAccountCreation'])) {
            unset($this->_params['automaticAccountCreation']);
        }
    }

    /**
     * @inheritdoc
     */
    public function getFeature()
    {
        return self::FEATURE_CHANGE_PASSWORD | self::FEATURE_SUPPORT_PASSWORD |
            self::FEATURE_USE_MULTIAUTH_TABLE;
    }

    /**
     * @inheritdoc
     */
    public function changePassword($login, $newpassword)
    {
        $dao = jDao::get($this->accountsDao, $this->accountsDaoProfile);
        return $dao->updatePassword($login, $this->cryptPassword($newpassword));
    }

    /**
     * @inheritdoc
     */
    public function verifyAuthentication($user, $login, $password)
    {
        if (trim($password) == '') {
            return self::VERIF_AUTH_BAD;
        }

        $result = $this->checkPassword($password, $user->password);
        if ($result === false) {
            return self::VERIF_AUTH_BAD;
        }
        if ($result !== true) {
            // it is a new hash for the password, let's update it persistently
            $user->password = $result;
            $dao = jDao::get($this->accountsDao, $this->accountsDaoProfile);
            $dao->updatePassword($login, $user->password);
        }
        return self::VERIF_AUTH_OK;
    }

    /**
     * @inheritdoc
     */
    public function userExists($login)
    {
        $dao = jDao::get($this->accountsDao, $this->accountsDaoProfile);
        return !!$dao->getByLogin($login);
    }
}
