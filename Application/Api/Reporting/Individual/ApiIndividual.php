<?php

namespace SPHERE\Application\Api\Reporting\Individual;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiIndividual
 * @package SPHERE\Application\Api\Reporting\Individual
 */
class ApiIndividual extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('getNewNavigation');
        $Dispatcher->registerMethod('removeFieldAll');
        $Dispatcher->registerMethod('savePreset');
        $Dispatcher->registerMethod('addField');
        $Dispatcher->registerMethod('buildFilter');


        $Dispatcher->registerMethod('getStudentNavigation');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverNavigation($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverNavigation');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverFilter($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverFilter');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverService($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverService');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverResult($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverResult');
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {
        return (new ModalReceiver('', new Close()))
            ->setIdentifier('ModalReceiver');
    }

    public static function pipelineDelete()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'removeFieldAll'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getStudentNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'buildFilter'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public static function pipelineSavePreset()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'savePreset'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public static function pipelineNewNavigation()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNewNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'buildFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineAddField($Field, $View, $NavigationTarget)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'addField'
        ));
        $Emitter->setPostPayload(array(
            'Field' => $Field,
            'View'  => $View
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => $NavigationTarget
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'buildFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineStudentNavigation()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getStudentNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'buildFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineDisplayFilter()
    {
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'buildFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public function removeFieldAll()
    {

        Individual::useService()->removeWorkSpaceFieldAll();
    }

    public function savePreset()
    {

        $tblPresetList = Individual::useService()->getPresetAll();
        $TableContent = array();

        if ($tblPresetList) {
            array_walk($tblPresetList, function (TblPreset $tblPreset) use (&$TableContent) {
                $Item['Name'] = $tblPreset->getName();
                $Item['FieldCount'] = '';

                $tblPresetSetting = Individual::useService()->getPresetSettingAllByPreset($tblPreset);
                if ($tblPresetSetting) {
                    $Item['FieldCount'] = count($tblPresetSetting);
                }
                $TableContent = array_merge($TableContent, $Item);
            });
        }

        $Content = new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null,
                            array(
                                'Name'       => 'Gespeicherte Filterung',
                                'FieldCount' => 'Anzahl Filter'
                            ))
                    )
                )
            )
        );

        return $Content;
    }

    public function getNewNavigation()
    {

        return new Panel('Verfügbar', array(
            new Panel('Auswertung über', array(
                'Schüler'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))->ajaxPipelineOnClick(
                    self::pipelineStudentNavigation()
                )),
//                'Lehrer'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
            ), Panel::PANEL_TYPE_PRIMARY),
        ));
    }

    public function addField($Field, $View)
    {

        $Position = 1;
        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                if ($tblWorkSpace->getPosition() >= $Position) {
                    $Position = $tblWorkSpace->getPosition();
                }
            }
            $Position++;
        }

        Individual::useService()->addWorkSpaceField($Field, $View, $Position);
    }

    public function getStudentNavigation()
    {

//        $Test = (new ViewStudent())->getArrayList();

//        Debugger::screenDump($Test);
//
//        return new Panel('Verfügbare Felder', 'haha');

//        return new Code(print_r($FieldList, true));

        // remove every entry that is Choosen
        $WorkSpaceList = array();
        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                $WorkSpaceList[] = $tblWorkSpace->getField();
            }
        }

        $ViewStudentBlockList = Individual::useService()->getStudentViewList();

        $PanelList = array();
        if ($ViewStudentBlockList) {
            foreach ($ViewStudentBlockList as $Block => $FieldList) {

                $FieldListArray = array();
                if ($FieldList) {
                    foreach ($FieldList as $FieldTblName) {

                        $FieldName = Individual::useService()->getFieldLabelByFieldName($FieldTblName);

                        if (!in_array($FieldTblName, $WorkSpaceList)) {
                            $FieldListArray[$FieldTblName] = new PullClear($FieldName.new PullRight((new Primary('',
                                    self::getEndpoint(), new Plus()))
                                    ->ajaxPipelineOnClick(self::pipelineAddField($FieldTblName, 'ViewStudent',
                                        'getStudentNavigation'))));
                        }
                    }
                }

                if (!empty($FieldListArray)) {
                    $PanelList[] = new Panel($Block, $FieldListArray, Panel::PANEL_TYPE_PRIMARY);
                }
            }
        }

//        $FieldList = array();
//
//        $FieldList[ViewStudent::TBL_SALUTATION_SALUTATION] = new PullClear('Anrede'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_SALUTATION_SALUTATION, 'ViewStudent', 'getStudentNavigation'))));
//        $FieldList[ViewStudent::TBL_PERSON_FIRST_NAME] = new PullClear('Vorname'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_PERSON_FIRST_NAME, 'ViewStudent', 'getStudentNavigation'))));
//        $FieldList[ViewStudent::TBL_PERSON_SECOND_NAME] = new PullClear('Zweiter-Vorname'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_PERSON_SECOND_NAME, 'ViewStudent', 'getStudentNavigation'))));
//        $FieldList[ViewStudent::TBL_PERSON_LAST_NAME] = new PullClear('Nachname'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_PERSON_LAST_NAME, 'ViewStudent', 'getStudentNavigation'))));
//        $FieldList[ViewStudent::TBL_COMMON_BIRTHDATES_BIRTHDAY] = new PullClear('Geburtstag'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_COMMON_BIRTHDATES_BIRTHDAY, 'ViewStudent', 'getStudentNavigation'))));
//        $FieldList[ViewStudent::TBL_COMMON_BIRTHDATES_BIRTHPLACE] = new PullClear('Geburtsort'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_COMMON_BIRTHDATES_BIRTHPLACE, 'ViewStudent', 'getStudentNavigation'))));
//        $FieldList[ViewStudent::TBL_COMMON_INFORMATION_DENOMINATION] = new PullClear('Konfession'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_COMMON_INFORMATION_DENOMINATION, 'ViewStudent', 'getStudentNavigation'))));
//
//        $FieldList[ViewStudent::TBL_COMMON_INFORMATION_NATIONALITY] = new PullClear('Staatsangehörigkeit'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_COMMON_INFORMATION_NATIONALITY, 'ViewStudent', 'getStudentNavigation'))));
//        $FieldList[ViewStudent::TBL_COMMON_INFORMATION_IS_ASSISTANCE] = new PullClear('Mitarbeitsbereitschaft'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_COMMON_INFORMATION_IS_ASSISTANCE, 'ViewStudent', 'getStudentNavigation'))));
//        $FieldList[ViewStudent::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY] = new PullClear('Mitarbeitsbereitschaft - Tätigkeit'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY, 'ViewStudent', 'getStudentNavigation'))));
//        $FieldList[ViewStudent::SIBLINGS_COUNT] = new PullClear('Anzahl Geschwister'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::SIBLINGS_COUNT, 'ViewStudent', 'getStudentNavigation'))));

//        // remove every entry that is Choosen
//        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
//        if ($tblWorkSpaceList) {
//            foreach ($tblWorkSpaceList as $tblWorkSpace) {
//                if (isset($FieldList[$tblWorkSpace->getField()]) && $FieldList[$tblWorkSpace->getField()]) {
//                    $FieldList[$tblWorkSpace->getField()] = false;
//                }
//            }
//        }
//        $FieldList = array_filter($FieldList);

        return new Panel('Verfügbare Felder', array(
            (new Danger('Löschen', ApiIndividual::getEndpoint(), new Disable()))->ajaxPipelineOnClick(
                ApiIndividual::pipelineDelete()
            ).(new Primary('Speichern', ApiIndividual::getEndpoint(), new Save()))->ajaxPipelineOnClick(
                ApiIndividual::pipelineSavePreset()
            ),
//            (new Accordion())->addItem('Schüler Grunddaten',
                new Layout(new LayoutGroup(new LayoutRow(
                    new LayoutColumn(
                        $PanelList
//                        new Listing(
//                            $FieldList
//                        )
                    )
                )))
//                , true)
        ,
//            new Panel('Schüler Kontaktdaten', array(
//                'Straße'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Straßennr.'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Ort'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Ortsteil'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'PLZ'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Bundesland'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Land'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Telefonnummern'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'E-Mail Adressen'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//            ), Panel::PANEL_TYPE_PRIMARY),
//            new Panel('Schüler Klasse', array(
//                'Stufe'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Gruppe'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Jahr'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Bildungsgang'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Schulart'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//            ), Panel::PANEL_TYPE_PRIMARY),
//            new Panel('Sorgeberechtigte Grunddaten', array(
//                'Anrede'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Titel'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Vorname'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Zweiter-Vorname'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Nachname'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//            ), Panel::PANEL_TYPE_PRIMARY),
//            new Panel('Sorgeberechtigte Kontaktdaten', array(
//                'Straße'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Straßennr.'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Ort'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Ortsteil'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'PLZ'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Bundesland'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Land'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'Telefonnummern'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//                'E-Mail Adressen'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
//            ), Panel::PANEL_TYPE_PRIMARY),
        ));
    }

    public function buildFilter()
    {

        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        $FormColumnAll = array();

        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {

                $FieldName = Individual::useService()->getFieldLabelByFieldName($tblWorkSpace->getField());
                $FormColumnAll[$tblWorkSpace->getPosition()] =
                    new FormColumn(new TextField($tblWorkSpace->getField()
                        , '', $FieldName), 2);
            }
            ksort($FormColumnAll);
        }

        $FormRowList = array();
        $FormRowCount = 0;
        $FormRow = null;
        if (!empty($FormColumnAll)) {
            /**
             * @var FormColumn $FormColumn
             */
            foreach ($FormColumnAll as $FormColumn) {
                if ($FormRowCount % 6 == 0) {
                    $FormRow = new FormRow(array());
                    $FormRowList[] = $FormRow;
                }
                $FormRow->addColumn($FormColumn);
                $FormRowCount++;
            }
            $FormRowList[] = new FormRow(new FormColumn((new Primary('Filtern', self::getEndpoint(),
                new Filter()))->setDisabled()));
        }

        if (!empty($FormRowList)) {
            $Form = new Form(
                new FormGroup(
                    $FormRowList
                )
            );
            $Panel = new Panel('Filter', $Form, Panel::PANEL_TYPE_INFO);
        }

        return (isset($Panel) ? $Panel : new InfoMessage('Bitte wählen Sie aus welche Spalten gefilter werden sollen'));
    }
}