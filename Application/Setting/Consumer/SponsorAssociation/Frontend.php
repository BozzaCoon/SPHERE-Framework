<?php
namespace SPHERE\Application\Setting\Consumer\SponsorAssociation;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Entity\TblSponsorAssociation;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Consumer\SponsorAssociation
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Förderverein', 'Übersicht');

        $Stage->setContent(
            new Standard('Förderverein hinzufügen', '/Setting/Consumer/SponsorAssociation/Create')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning('Es ist noch kein Förderverein eingetragen')
                        )
                    ), new Title('')
                )
            )
        );

        if (( $tblSponsorAssociationAll = SponsorAssociation::useService()->getSponsorAssociationAll() )) {

            $Form = null;
            foreach ($tblSponsorAssociationAll as $tblSponsorAssociation) {
                $tblCompany = $tblSponsorAssociation->getServiceTblCompany();
                if ($tblCompany) {
                    $Form .= new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                School::useFrontend()->frontendLayoutCombine($tblCompany)
                            )),
                        ), (new Title(new TagList().' Kontaktdaten', 'von '.$tblCompany->getDisplayName()))
                        ),
                    ));
                }
            }
            $Stage->setContent(
                new Standard('Förderverein hinzufügen', '/Setting/Consumer/SponsorAssociation/Create')
                .new Standard('Förderverein entfernen', '/Setting/Consumer/SponsorAssociation/Delete')
                .$Form);
        }

        return $Stage;
    }

    /**
     * @param $SponsorAssociation
     *
     * @return Stage
     */
    public function frontendSponsorAssociationCreate($SponsorAssociation)
    {

        $Stage = new Stage('Förderverein', 'Hinzufügen');
        $Stage->addButton(new Standard('Zurück', '/Setting/Consumer/SponsorAssociation', new ChevronLeft()));
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            SponsorAssociation::useService()->createSponsorAssociation(
                                $this->formSponsorAssociationCompanyCreate()
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                                $SponsorAssociation
                            )
                        ))
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSponsorAssociationCompanyCreate()
    {

        $PanelSelectCompanyTitle = new PullClear(
            'Förderverein auswählen:'
            .new PullRight(
                new Standard('Neue Institution anlegen', '/Corporation/Company', new Building()
                    , array(), '"Förderverein hinzufügen" verlassen'
                ))
        );
        $tblCompanyAll = Company::useService()->getCompanyAll();
        $TableContent = array();
        if ($tblCompanyAll) {
            array_walk($tblCompanyAll, function (TblCompany $tblCompany) use (&$TableContent) {
                $temp['Select'] = new RadioBox('SponsorAssociation', '&nbsp;', $tblCompany->getId());
                $temp['Content'] = $tblCompany->getName()
                    .new Container($tblCompany->getExtendedName())
                    .new Container(new Muted($tblCompany->getDescription()));
                array_push($TableContent, $temp);
            });
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        !empty( $TableContent ) ?
                            new Panel($PanelSelectCompanyTitle,
                                new TableData($TableContent, null, array(
                                    'Select'  => 'Auswahl',
                                    'Content' => 'Institution',
                                ), array(
                                    'columnDefs' => array(
                                        array('width' => '1%', 'targets' => array(0))
                                    ),
                                    'order' => array(
                                        array(1, 'asc'),
                                    ),
                                ))
                                , Panel::PANEL_TYPE_INFO, null, 15)
                            : new Panel($PanelSelectCompanyTitle,
                            new Warning('Es ist keine Institution vorhanden die ausgewählt werden kann')
                            , Panel::PANEL_TYPE_INFO)
                    ), 12),
                )),
            ))
        );
    }

    /**
     *
     * @return Stage
     */
    public function frontendSponsorAssociationDelete()
    {

        $Stage = new Stage('Förderverein', 'Entfernen');
        $Stage->addButton(new Standard('Zurück', '/Setting/Consumer/SponsorAssociation', new ChevronLeft()));
        $tblSponsorAssociationAll = SponsorAssociation::useService()->getSponsorAssociationAll();
        if ($tblSponsorAssociationAll) {
            array_walk($tblSponsorAssociationAll, function (TblSponsorAssociation &$tblSponsorAssociation) {

                $tblCompany = $tblSponsorAssociation->getServiceTblCompany();
                if ($tblCompany) {
                    $Address = array();
                    $Address[] = $tblCompany->getName().new Container($tblCompany->getExtendedName());
                    $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
                    if ($tblAddressAll) {
                        foreach ($tblAddressAll as $tblAddress) {
                            $Address[] = new Muted(new Small($tblAddress->getTblAddress()->getStreetName().' '
                                . $tblAddress->getTblAddress()->getStreetNumber() . ' '
                                .$tblAddress->getTblAddress()->getTblCity()->getName()));
                        }
                    }
                    $Address[] = (new Standard('', '/Setting/Consumer/SponsorAssociation/Destroy', new Remove(),
                        array('Id' => $tblSponsorAssociation->getId())));
                    $Content = array_filter($Address);
                    $Type = Panel::PANEL_TYPE_WARNING;
                    $tblSponsorAssociation = new LayoutColumn(
                        new Panel('Förderverein', $Content, $Type)
                        , 6);
                } else {
                    $tblSponsorAssociation = false;
                }
            });
            $tblSponsorAssociationAll = array_filter($tblSponsorAssociationAll);

            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblSponsorAssociation
             */
            foreach ($tblSponsorAssociationAll as $tblSponsorAssociation) {
                if ($LayoutRowCount % 3 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($tblSponsorAssociation);
                $LayoutRowCount++;
            }
        } else {
            $LayoutRowList = false;
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    $LayoutRowList
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendSponsorAssociationDestroy($Id, $Confirm = false)
    {

        $Stage = new Stage('Förderverein', 'Löschen');
        if ($Id) {
            $tblSponsorAssociation = SponsorAssociation::useService()->getSponsorAssociationById($Id);
            if ($tblSponsorAssociation->getServiceTblCompany()) {
                if (!$Confirm) {

                    $Address = array();
                    if ($tblSponsorAssociation->getServiceTblCompany()) {
                        $Address[] = $tblSponsorAssociation->getServiceTblCompany()->getName()
                            .new Container($tblSponsorAssociation->getServiceTblCompany()->getExtendedName())
                            .new Container(new Muted($tblSponsorAssociation->getServiceTblCompany()->getDescription()));

                        $tblAddressAll = Address::useService()->getAddressAllByCompany($tblSponsorAssociation->getServiceTblCompany());
                        if ($tblAddressAll) {
                            foreach ($tblAddressAll as $tblAddress) {
                                $Address[] = new Muted(new Small($tblAddress->getTblAddress()->getStreetName().' '
                                    .$tblAddress->getTblAddress()->getStreetNumber().' '
                                    .$tblAddress->getTblAddress()->getTblCity()->getName()));
                            }
                        }
                    }
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(new Question().' Diesen Förderverein wirklich löschen?', $Address,
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Setting/Consumer/SponsorAssociation/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                . new Standard(
                                    'Nein', '/Setting/Consumer/SponsorAssociation', new Disable()
                                )
                            )
                        ))))
                    );
                } else {

                    // Destroy Group
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (SponsorAssociation::useService()->destroySponsorAssociation($tblSponsorAssociation)
                                    ? new Success('Der Förderverein wurde gelöscht')
                                    . new Redirect('/Setting/Consumer/SponsorAssociation', Redirect::TIMEOUT_SUCCESS)
                                    : new Danger('Der Förderverein konnte nicht gelöscht werden')
                                    . new Redirect('/Setting/Consumer/SponsorAssociation', Redirect::TIMEOUT_ERROR)
                                )
                            )))
                        )))
                    );
                }
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger('Der Förderverein konnte nicht gefunden werden'),
                            new Redirect('/Setting/Consumer/SponsorAssociation', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Der Förderverein konnte nicht gefunden werden'),
                        new Redirect('/Setting/Consumer/SponsorAssociation', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }

        return $Stage;
    }

}
