<?php
namespace SPHERE\Application\Setting\Authorization\Group;

use SPHERE\Application\Api\Platform\Gatekeeper\ApiUserGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param array $Group
     *
     * @return Stage
     */
    public function frontendUserGroup($Group = array())
    {
        $Stage = new Stage('Benutzergruppen', 'Verwalten');
        $Stage->addButton((new Primary('Benutzergruppe hinzufügen', ApiUserGroup::getEndpoint(), new Plus()))
        ->ajaxPipelineOnClick(ApiUserGroup::pipelineShowUserGroup('create', $Group)));

//        $receiverGroupList = new BlockReceiver();
        $receiverCreateModal = ApiUserGroup::getModalReceiver('create', new PlusSign() . ' Neue Benutzergruppe anlegen', new Close());
        $receiverEditModal = ApiUserGroup::getModalReceiver('edit', new Pencil() . ' Benutzergruppe bearbeiten', new Close());
        $receiverDestroyModal = ApiUserGroup::getModalReceiver('destroy', new Remove() . ' Benutzergruppe entfernen', new Close());
//        $receiverModalEdit = new ModalReceiver( 'Gruppe bearbeiten', new Close() );
//        $receiverModalDestroy = new ModalReceiver( 'Sind Sie sicher?' );

        $Stage->setContent(
            $receiverCreateModal
            .$receiverEditModal
            .$receiverDestroyModal
            .ApiUserGroup::getServiceReceiver()
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ApiUserGroup::getGroupTableReceiver($this->getGroupTable())
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return TableData
     */
    public function getGroupTable()
    {
        $tblGroupAll = Account::useService()->getGroupAll(
            Consumer::useService()->getConsumerBySession()
        );

        $TableContent = array();
        if ($tblGroupAll) {
            array_walk($tblGroupAll, function (TblGroup $tblGroup) use (&$TableContent) {

                $item['Name'] = $tblGroup->getName();
                $item['Description'] = $tblGroup->getDescription();
                $item['Role'] = '';
                $item['Member'] = '';
                $item['Option'] = (new Standard('', ApiUserGroup::getEndpoint(), new Edit()))
                    ->ajaxPipelineOnClick(ApiUserGroup::pipelineShowUserGroup('edit', array(), $tblGroup->getId()))
                .(new Standard('', ApiUserGroup::getEndpoint(), new CogWheels()))
                .(new Standard('', ApiUserGroup::getEndpoint(), new Remove()))
                ->ajaxPipelineOnClick(ApiUserGroup::pipelineDestroyGroup('destroy', $tblGroup->getId()));

                array_push($TableContent, $item);
            });
        }

        return new TableData(
            $TableContent
            , null, array(
            'Name' => 'Name',
            'Description' => 'Beschreibung',
            'Role' => 'Rechte',
            'Member' => 'Benutzer',
            'Option' => ''
        ), array(
            "columnDefs" => array(
                array("searchable" => false, "targets" => -1),
                array("type" => "natural", "targets" => '_all')
            )
        ));
    }
}
