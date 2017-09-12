<?php

namespace SPHERE\Application\Platform\Gatekeeper\Authentication;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Agb\Agb;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Globe;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\MoreItems;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Off;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Picture;
use SPHERE\Common\Frontend\Icon\Repository\Shield;
use SPHERE\Common\Frontend\Icon\Repository\StopSign;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\ITextInterface;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage('Willkommen', '', '');

        $tblIdentificationSearch = Account::useService()->getIdentificationByName(TblIdentification::NAME_USER_CREDENTIAL);
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount && $tblIdentificationSearch) {
            $tblAuthentication = Account::useService()->getAuthenticationByAccount($tblAccount);
            if ($tblAuthentication && ($tblIdentification = $tblAuthentication->getTblIdentification())) {
                if ($tblIdentificationSearch->getId() == $tblIdentification->getId()) {
                    $IsEqual = false;
                    $tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount);
                    if ($tblUserAccount) {
                        $Password = $tblUserAccount->getAccountPassword();
                        if ($tblAccount->getPassword() == $Password) {
                            $IsEqual = true;
                        }
                    }

                    if ($IsEqual) {
                        $Stage->setContent(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn('', 2),
                                        new LayoutColumn(
                                            new Center(new Panel('Warnung',
                                                new Warning('Bitte ändern Sie ihr Passwort um eine vollständige
                                            Sicherheit zu gewährleisten.')
                                                , Panel::PANEL_TYPE_DANGER,
                                                new Standard('Passwort ändern', '/Setting/MyAccount/Password'
                                                    , new Key(), array(), 'Schnellzugriff der Passwort Änderung')))
                                            , 8)
                                    ))
                                )
                            )
                        );
                        return $Stage;
                    }
                }
            }
        }

        $Stage->setContent($this->getCleanLocalStorage());

        return $Stage;
    }

    /**
     * @return string
     */
    private function getCleanLocalStorage()
    {

        return '<script language=javascript>
            //noinspection JSUnresolvedFunction
            executeScript(function()
            {
                Client.Use("ModCleanStorage", function()
                {
                    jQuery().ModCleanStorage();
                });
            });
        </script>';
    }

    /**
     * Step 1/3
     *
     * @param string $CredentialName
     * @param string $CredentialLock
     *
     * @return Stage
     */
    public function frontendIdentificationCredential($CredentialName = null, $CredentialLock = null)
    {
        $View = new Stage(new Nameplate().' Anmelden', '', $this->getIdentificationEnvironment());

        // Search for matching Account
        $tblAccount = null;
        $tblIdentification = null;
        if ($CredentialName && $CredentialLock) {
            if (!$tblAccount) {
                // Check Credential
                $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_CREDENTIAL);
                $tblAccount = Account::useService()
                    ->getAccountByCredential($CredentialName, $CredentialLock, $tblIdentification);
            }
            if (!$tblAccount) {
                // Check Credential with Token
                $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_TOKEN);
                $tblAccount = Account::useService()
                    ->getAccountByCredential($CredentialName, $CredentialLock, $tblIdentification);
            }
            if (!$tblAccount) {
                // Check Credential with Token (System-Admin)
                $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_SYSTEM);
                $tblAccount = Account::useService()
                    ->getAccountByCredential($CredentialName, $CredentialLock, $tblIdentification);
            }
        }

        // Matching Account found?
        if ($tblAccount && $tblIdentification) {
            switch ($tblIdentification->getName()) {
                case TblIdentification::NAME_TOKEN:
                case TblIdentification::NAME_SYSTEM:
                    return $this->frontendIdentificationAgb($tblAccount->getId(), $tblIdentification->getId());
//                    return $this->frontendIdentificationToken($tblAccount->getId(), $tblIdentification->getId());
                case TblIdentification::NAME_CREDENTIAL:
                    return $this->frontendIdentificationAgb($tblAccount->getId(), $tblIdentification->getId());
            }
        }

        // Field Definition
        $CredentialNameField = (new TextField('CredentialName', 'Benutzername', 'Benutzername', new Person()))
            ->setRequired()->setAutoFocus();
        $CredentialLockField = (new PasswordField('CredentialLock', 'Passwort', 'Passwort', new Lock()))
            ->setRequired()->setDefaultValue($CredentialLock, true);

        // Error Handling
        if ($CredentialName !== null) {
            if (empty($CredentialName)) {
                $CredentialNameField->setError('Bitte geben Sie Ihren Benutzernamen an');
            }
        }
        if ($CredentialLock !== null) {
            if (empty($CredentialLock)) {
                $CredentialLockField->setError('Bitte geben Sie Ihr Passwort an');
            }
        }
        $FormError = new Container('');
        if ($CredentialName && $CredentialLock && !$tblAccount) {
            $CredentialNameField->setError('');
            $CredentialLockField->setError('');
            $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
        }

        // Create Form
        $Form = new Form(
            new FormGroup(array(
                    new FormRow(
                        new FormColumn(array(
                            new Headline('Bitte geben Sie Ihre Zugangsdaten ein'),
                            new Ruler(),
                            new Listing(array(
                                new Container($CredentialNameField) .
                                new Container($CredentialLockField)
                            )),
                            $FormError
                        ))
                    ),
                    new FormRow(
                        new FormColumn(array(
                            (new Primary('Anmelden')),
                        ))
                    )
                )
            )
        );

        $View->setContent($this->getIdentificationLayout($Form));

        return $View;
    }

    /**
     * Environment Information
     *
     * @return ITextInterface
     */
    private function getIdentificationEnvironment()
    {
        switch (strtolower($this->getRequest()->getHost())) {
            case 'www.schulsoftware.schule':
            case 'www.kreda.schule':
                return new InfoText('');
                break;
            case 'demo.schulsoftware.schule':
            case 'demo.kreda.schule':
                return new Danger(new Picture().' Demo-Umgebung');
                break;
            default:
                return new WarningText( new Globe().' '.$this->getRequest()->getHost());
        }
    }

    /**
     * @param int $tblAccount
     * @param int $tblIdentification
     * @param null|string $CredentialKey
     * @return Stage
     */
    public function frontendIdentificationToken($tblAccount, $tblIdentification, $CredentialKey = null)
    {
        $View = new Stage(new YubiKey().' Anmelden', '', $this->getIdentificationEnvironment());

        $tblAccount = Account::useService()->getAccountById($tblAccount);
        $tblIdentification = Account::useService()->getIdentificationById($tblIdentification);

        // Return on Input Error
        if (
            !$tblAccount
            || !$tblIdentification
            || !$tblAccount->getServiceTblIdentification()
            || !$tblAccount->getServiceTblIdentification()->getId() == $tblIdentification->getId()
        ) {
            // Restart Identification Process
            return $this->frontendIdentificationCredential();
        }

        // Field Definition
        $CredentialKeyField = (new PasswordField('CredentialKey', 'YubiKey', 'YubiKey', new YubiKey()))
            ->setRequired()->setAutoFocus();

        // Search for matching Token
        $FormError = new Container('');
        if ($CredentialKey) {
            $Identifier = $this->getModHex($CredentialKey)->getIdentifier();
            $tblToken = Token::useService()->getTokenByIdentifier($Identifier);
            if (
                $tblToken
                && $tblAccount->getServiceTblToken()
                && $tblAccount->getServiceTblToken()->getId() == $tblToken->getId()
            ) {
                // Credential correct, Token correct -> LOGIN
                Account::useService()->createSession($tblAccount);
                $View->setTitle( new Ok().' Anmelden' );
                $View->setContent(
                    $this->getIdentificationLayout(
                        new Headline('Anmelden', 'Bitte warten...')
                        . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                    )
                );
                return $View;
            } else {
                // Error Token not registered
                $CredentialKeyField->setError('');
                $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
            }
        }

        // Switch User/Account (Restart Identification Process)
        $FormInformation = array(
            'Mandant: ' . $tblAccount->getServiceTblConsumer()->getAcronym() . ' - ' . $tblAccount->getServiceTblConsumer()->getName(),
            'Benutzer: ' . $tblAccount->getUsername()
            . new PullRight(new Small(new Link('Mit einem anderen Benutzer anmelden', new Route(__NAMESPACE__))))
        );
        $tblUserAll = Account::useService()->getUserAllByAccount($tblAccount);
        if (!empty($tblUserAll)) {
            foreach ($tblUserAll as $tblUser) {
                $tblPerson = $tblUser->getServiceTblPerson();
                if ($tblPerson) {
                    array_push($FormInformation, 'Name: ' . $tblPerson->getFullName());
                }
            }
        }

        // Create Form
        $Form = new Form(
            new FormGroup(array(
                    new FormRow(
                        new FormColumn(array(
                            new Headline('Bitte geben Sie Ihre Zugangsdaten ein'),
                            new Ruler(),
                            new Listing($FormInformation),
                            new Listing(array(
                                new Container($CredentialKeyField)
                            )),
                            $FormError
                        ))
                    ),
                    new FormRow(
                        new FormColumn(array(
                            (new Primary('Bestätigen'))
                        ))
                    )
                )
            )
            , null, new Route(__NAMESPACE__ . '/Token'), array(
            'tblAccount' => $tblAccount,
            'tblIdentification' => $tblIdentification
        ));

        $View->setContent($this->getIdentificationLayout($Form));

        return $View;
    }

    /**
     * Stage Layout
     *
     * @param $Content
     * @return Layout
     */
    private function getIdentificationLayout($Content)
    {
        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    ''
                    , 2),
                new LayoutColumn(
                    $Content
                    , 8),
                new LayoutColumn(
                    ''
                    , 2),
            )),
        )));
    }

    public function frontendIdentificationAgb($tblAccount, $tblIdentification, $doAccept = 0)
    {
        $View = new Stage(new MoreItems().' Anmelden', '', $this->getIdentificationEnvironment());

        $tblAccount = Account::useService()->getAccountById($tblAccount);
        $tblIdentification = Account::useService()->getIdentificationById($tblIdentification);

        // Return on Input Error
        if (
            !$tblAccount
            || !$tblIdentification
            || !$tblAccount->getServiceTblIdentification()
            || !$tblAccount->getServiceTblIdentification()->getId() == $tblIdentification->getId()
        ) {
            // Restart Identification Process
            return $this->frontendIdentificationCredential();
        }

        $Headline = 'Allgemeine Geschäftsbedingungen';

        // IS Accepted?
        // Sanatize Agb Setting
        $tblSetting = Account::useService()->getSettingByAccount($tblAccount, 'ABG');
        if (!$tblSetting) {
            $tblSetting = Account::useService()->setSettingByAccount($tblAccount, 'ABG', TblSetting::VAR_EMPTY_AGB);
        }
        // Check/Set Agb Setting
        if( $tblSetting->getValue() == TblSetting::VAR_ACCEPT_AGB || $doAccept == 1 ) {
            if( $doAccept == 1 ) {
                Account::useService()->setSettingByAccount($tblAccount, 'ABG', TblSetting::VAR_ACCEPT_AGB);
            }
            // Credential correct, Agb accepted -> LOGIN
            Account::useService()->createSession($tblAccount);
            $View->setTitle( new Ok().' Anmelden' );
            $View->setContent(
                $this->getIdentificationLayout(
                    new Headline('Anmelden', 'Bitte warten...')
                    . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                )
            );
            return $View;
        }

        // NOT Accepted?
        // Check if Parent-Account
        $tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount);
        if( $tblUserAccount && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_CUSTODY ) {
            // IS Parent-Account
            if($tblSetting->getValue() == TblSetting::VAR_UPDATE_AGB) {
                $Headline = 'Allgemeine Geschäftsbedingungen - Aktualisierung';
            }
            $View->setDescription( $Headline );
        } else {
            // NOT Parent-Account
            // Credential correct, NO Agb check -> LOGIN
            Account::useService()->createSession($tblAccount);
            $View->setTitle( new Ok().' Anmelden' );
            $View->setContent(
                $this->getIdentificationLayout(
                    new Headline('Anmelden', 'Bitte warten...')
                    . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                )
            );
            return $View;
        }

        // Switch User/Account (Restart Identification Process)
        $FormInformation = array(
            'Mandant: ' . $tblAccount->getServiceTblConsumer()->getAcronym() . ' - ' . $tblAccount->getServiceTblConsumer()->getName(),
            'Benutzer: ' . $tblAccount->getUsername()
            . new PullRight(new Small(new Link('Mit einem anderen Benutzer anmelden', new Route(__NAMESPACE__))))
        );
        $tblUserAll = Account::useService()->getUserAllByAccount($tblAccount);
        if (!empty($tblUserAll)) {
            foreach ($tblUserAll as $tblUser) {
                $tblPerson = $tblUser->getServiceTblPerson();
                if ($tblPerson) {
                    array_push($FormInformation, 'Name: ' . $tblPerson->getFullName());
                }
            }
        }

        // Create Form
        $Form = new Layout(
            new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Headline('Ich möchte das elektronische Notenbuch nutzen und bin mit den o.g. Regelungen einverstanden:'),
                            new Ruler(),
                            new Listing($FormInformation)
                        ))
                    ),
                    new LayoutRow(
                        new LayoutColumn(array(
                            new PullLeft( new Success('Einwilligen',new Route(__NAMESPACE__ . '/Agb'), new Enable(), array(
                                'tblAccount' => $tblAccount,
                                'tblIdentification' => $tblIdentification,
                                'doAccept' => 1
                            )) ),
                            new PullRight( new DangerLink('Ablehnen',new Route(__NAMESPACE__ ), new Disable(), array()) )
                        ))
                    )
                )
            ));

        $View->setContent(
            $this->getIdentificationLayout(
                new Listing(array(new Header( new Bold($Headline)),Agb::useFrontend()->getAgbContent()))
                . $Form
            )
        );

        return $View;
    }

    /**
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     *
     * @return Stage
     */
    public function frontendIdentification($CredentialName = null, $CredentialLock = null, $CredentialKey = null)
    {

        if ($CredentialName !== null) {
            Protocol::useService()->createLoginAttemptEntry($CredentialName, $CredentialLock, $CredentialKey);
        }

        $View = new Stage('Anmelden');
        $View->setMessage('Bitte geben Sie Ihre Benutzerdaten ein');

        // Get Identification-Type (Credential,Token,System)
        $Identifier = $this->getModHex($CredentialKey)->getIdentifier();
        if ($Identifier) {
            $tblToken = Token::useService()->getTokenByIdentifier($Identifier);
            if ($tblToken) {
                if ($tblToken->getServiceTblConsumer()) {
                    $Identification = Account::useService()->getIdentificationByName('Token');
                } else {
                    $Identification = Account::useService()->getIdentificationByName('System');
                }
            } else {
                $Identification = Account::useService()->getIdentificationByName('Credential');
            }
        } else {
            $Identification = Account::useService()->getIdentificationByName('Credential');
            $tblToken = null;
        }

        if (!$Identification) {
            $Protocol = (new Database())->frontendSetup(false, true);

            $Stage = new Stage(new Danger(new Hospital()) . ' Installation', 'Erster Aufruf der Anwendung');
            $Stage->setMessage('Dieser Schritt wird automatisch ausgeführt wenn die Datenbank nicht die notwendigen Einträge aufweist. Üblicherweise beim ersten Aufruf.');
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(array(
                                new Panel('Was ist das?', array(
                                    (new Info(new Shield() . ' Es wird eine automatische Installation der Datenbank und eine Überprüfung der Daten durchgeführt')),
                                ), Panel::PANEL_TYPE_PRIMARY,
                                    new PullRight(strip_tags((new Redirect(self::getRequest()->getPathInfo(), 110)),
                                        '<div><a><script><span>'))
                                ),
                                new Panel('Protokoll', array(
                                    $Protocol
                                ))
                            ))
                        )
                    )
                )
            );
            return $Stage;
        }

        // Create Form
        $Form = new Form(
            new FormGroup(array(
                    new FormRow(
                        new FormColumn(
                            new Panel('Benutzername & Passwort', array(
                                (new TextField('CredentialName', 'Benutzername', 'Benutzername', new Person()))
                                    ->setRequired(),
                                (new PasswordField('CredentialLock', 'Passwort', 'Passwort', new Lock()))
                                    ->setRequired()->setDefaultValue($CredentialLock, true)
                            ), Panel::PANEL_TYPE_INFO)
                        )
                    ),
                    new FormRow(array(
                        new FormColumn(
                            new Panel('Hardware-Schlüssel *', array(
                                new PasswordField('CredentialKey', 'YubiKey', 'YubiKey', new YubiKey())
                            ), Panel::PANEL_TYPE_INFO,
                                new Small('* Wenn zu Ihrem Zugang ein YubiKey gehört geben Sie zuerst oben Ihren Benutzername und Passwort an, stecken Sie dann bitte den YubiKey an, klicken in das Feld YubiKey und drücken anschließend auf den metallischen Sensor am YubiKey. <br/>Das Formular wird daraufhin automatisch abgeschickt.'))
                        )
                    ))
                )
            ), new Primary('Anmelden')
        );

        // Switch Service
        if ($tblToken) {
            $FormService = Account::useService()->createSessionCredentialToken(
                $Form, $CredentialName, $CredentialLock, $CredentialKey, $Identification
            );
        } else {
            $FormService = Account::useService()->createSessionCredential(
                $Form, $CredentialName, $CredentialLock, $Identification
            );
        }

        $View->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        ''
                        , 3),
                    new LayoutColumn(
                        new Well($FormService)
                        , 6),
                    new LayoutColumn(
                        ''
                        , 3),
                )),
            )))
        );
        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendDestroySession()
    {
        $View = new Stage(new Off().' Abmelden', '', $this->getIdentificationEnvironment());

        $View->setContent(
            $this->getIdentificationLayout(
                new Headline('Abmelden', 'Bitte warten...').
                Account::useService()->destroySession(
                    new Redirect('/Platform/Gatekeeper/Authentication', Redirect::TIMEOUT_SUCCESS)
                ) . $this->getCleanLocalStorage()
            )
        );

        return $View;
    }
}
