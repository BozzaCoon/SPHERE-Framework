<?php
namespace SPHERE\Application\Api\Platform\Gatekeeper;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Authorization\Group\Group;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiUserGroup
 *
 * @package SPHERE\Application\Api\Platform\Gatekeeper
 */
class ApiUserGroup extends Extension implements IApiInterface
{
    use ApiTrait;

    const API_DISPATCHER = 'MethodName';

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function exportApi($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('getGroupTable');

//        $Dispatcher->registerMethod('pieceMemberList');
//        $Dispatcher->registerMethod('pieceUserList');

//        $Dispatcher->registerMethod('pieceAddUser');
//        $Dispatcher->registerMethod('pieceRemoveUser');

        $Dispatcher->registerMethod('showUserGroup');
        $Dispatcher->registerMethod('saveGroup');

        $Dispatcher->registerMethod('destroyUserGroup');

        return $Dispatcher->callMethod($MethodName);
    }

    /**
     * @param string $Identifier
     * @param string $Head
     * @param string $Footer
     *
     * @return ModalReceiver
     */
    public static function getModalReceiver($Identifier , $Head = '', $Footer = '')
    {
        return (new ModalReceiver($Head, $Footer))->setIdentifier('GroupModal'.$Identifier);
    }

    /**
     * @return InlineReceiver
     */
    public static function getServiceReceiver()
    {
        return (new InlineReceiver(''))->setIdentifier('GroupService');
    }

    /**
     * @param $Content
     *
     * @return BlockReceiver
     */
    public static function getGroupTableReceiver($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('GroupTable');
    }

    /**
     * @return TableData
     */
    public function getGroupTable()
    {

        return Group::useFrontend()->getGroupTable();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineTableUserGroup($Identifier = '')
    {

        $pipeline = new Pipeline();
        $emitter = new ServerEmitter(self::getGroupTableReceiver(), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_DISPATCHER => 'getGroupTable',
        ));
        $pipeline->appendEmitter($emitter);
        if($Identifier){
            $pipeline->appendEmitter((new CloseModal(self::getModalReceiver($Identifier)))->getEmitter());
        }

        return $pipeline;
    }

    /**
     * @param string $Identifier
     * @param array  $Group
     * @param string $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineShowUserGroup($Identifier = '', $Group = array(), $GroupId = '')
    {

        $pipeline = new Pipeline();
        $emitter = new ServerEmitter(self::getModalReceiver($Identifier), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_DISPATCHER => 'showUserGroup',
        ));
        $emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Group' => $Group,
            'GroupId' => $GroupId,
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param string $Identifier
     * @param array  $Group
     * @param string $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineSaveGroup($Identifier, $Group = array(), $GroupId = '')
    {


        $pipeline = new Pipeline();
        $emitter = new ServerEmitter(self::getModalReceiver($Identifier), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_DISPATCHER => 'saveGroup',
        ));
        $emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Group' => $Group,
            'GroupId' => $GroupId,
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param int    $Confirm
     *
     * @return Pipeline
     */
    public static function pipelineDestroyGroup($Identifier = '', $GroupId = '', $Confirm = 0)
    {

        $pipeline = new Pipeline();
        $emitter = new ServerEmitter(self::getModalReceiver($Identifier), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_DISPATCHER => 'destroyUserGroup',
        ));
        $emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'GroupId' => $GroupId,
            'Confirm' => $Confirm,
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineCloseModal($Identifier)
    {
        $Pipeline = new Pipeline();
        // reload the whole Table
        $Emitter = new ServerEmitter(self::getGroupTableReceiver(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_DISPATCHER => 'getGroupTable'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::getModalReceiver($Identifier)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param array  $Group
     * @param string $GroupId
     *
     * @return Well
     */
    public function showUserGroup($Identifier, $Group = array(), $GroupId = '')
    {
        return new Well($this->formUserGroup($Identifier, $Group, $GroupId));
    }

    /**
     * @param string $Identifier
     * @param array  $Group
     * @param string $GroupId
     *
     * @return bool|Well|Danger|string
     */
    public function saveGroup($Identifier, $Group = array(), $GroupId = '')
    {

        if($form = $this->checkInputGroup($Identifier, $Group, $GroupId)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Group']['Name'] = $Group['Name'];
            $Global->POST['Group']['Description'] = $Group['Description'];
            $Global->savePost();
            return $form;
        }
        $tblConsumer = null;
        if(($tblAccount = Account::useService()->getAccountBySession())){
            $tblConsumer = $tblAccount->getServiceTblConsumer();
        }
        $tblGroup = Group::useService()->getGroupById($GroupId);
        if($tblGroup && Group::useService()->changeGroup($tblGroup, $Group['Name'], $Group['Description'], $tblConsumer)){
            return new Success('Benutzergruppe erfolgreich geändert').self::pipelineCloseModal($Identifier);
        } elseif(Group::useService()->createGroup($Group['Name'], $Group['Description'], $tblConsumer)) {
            return new Success('Benutzergruppe erfolgreich angelegt').self::pipelineCloseModal($Identifier);
        } else {
            return new Danger('Benutzergruppe konnte nicht gengelegt werden');
        }

    }

    /**
     * @param string $Identifier
     * @param array  $Group
     * @param string $GroupId
     *
     * @return Form
     */
    public function formUserGroup($Identifier, $Group = array(), $GroupId = '')
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        $Global = $this->getGlobal();
        if(!isset($Global->POST['Group']['Name']) && $tblGroup){
            $Global->POST['Group']['Name'] = $tblGroup->getName();
        }
        if(!isset($Global->POST['Group']['Description']) && $tblGroup){
            $Global->POST['Group']['Description'] = $tblGroup->getDescription();
        }
        $Global->savePost();

        return (new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Group[Name]', 'Gruppenname', 'Gruppenname'))->setAutoFocus()
                    ),
                    new FormColumn(
                        (new TextArea('Group[Description]', 'Gruppenbeschreibung',
                            'Gruppenbeschreibung'))->setMaxLengthValue(200)
                    ),
                ))
            ), (new Primary('Speichern', self::getEndpoint(), new Save()))
            ->ajaxPipelineOnClick(self::pipelineSaveGroup($Identifier, $Group, $GroupId))
            .new Close('Abbrechen')
        ))->disableSubmitAction();
    }

    /**
     * @param string $Identifier
     * @param array  $Group
     * @param string $GroupId
     *
     * @return bool|Well
     */
    private function checkInputGroup($Identifier, $Group = array(), $GroupId = '')
    {
        $Error = false;

        $form = $this->formUserGroup($Identifier, $Group, $GroupId);

        if (!isset($Group['Name']) || empty($Group['Name'])) {
            $form->setError('Group[Name]', 'Bitte geben Sie einen Namen ein');
            $Error = true;
        } else {
            if (($tblGroup = Account::useService()->getGroupByName($Group['Name'])) && $tblGroup->getId() != $GroupId ) {
                $form->setError('Group[Name]', 'Der angegebene Name wird bereits verwendet');
                $Error = true;
            }
        }
        if($Error){
            return new Well($form);
        }
        return $Error;
    }

    public function destroyUserGroup($Identifier, $GroupId, $Confirm = 0)
    {

        $tblGroup = Account::useService()->getGroupById($GroupId);

        if ($tblGroup) {
            if($Confirm == 0){
                // Löschen Rückfrage
                return new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel('Wollen Sie die Gruppe ' . $tblGroup->getName() . ' wirklich löschen?',
                                    (new Standard('Ja', self::getEndpoint(), new Enable()))->ajaxPipelineOnClick($this->pipelineDestroyGroup($Identifier, $GroupId, 1))
                                    . new Close('Nein', new Disable()), Panel::PANEL_TYPE_DANGER
                                )
                            )
                        )
                    )
                );
            } else {
                // Löschen bestätigt
                Account::useService()->destroyGroup($tblGroup);
                return new Success('Die Benutzergruppe wurde erfolgreich entfernt').self::pipelineTableUserGroup($Identifier);
            }
        } else {
            return 'Gruppe wurde nicht gefunden.';
        }
    }

}
