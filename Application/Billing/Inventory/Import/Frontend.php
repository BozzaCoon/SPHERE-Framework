<?php
namespace SPHERE\Application\Billing\Inventory\Import;

use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Datenimport', 'Fakturierung ');

        $PanelImport[] =
            new Info('Bitte verwenden Sie die Vorlage, um ihre Daten korrekt in das Tool einzuspielen: &nbsp;&nbsp;&nbsp;&nbsp;'
                .new External('Download Import-Vorlage','/Api/Billing/Inventory/DownloadTemplateInvoice',
                    new Download(), array(), false), null, false, 5, 3)
            .new PullClear('Grundimport für Fakturierung: '.
            new Center(new Standard('', '/Billing/Inventory/Import/Prepare', new Upload()
                , array(), 'Hochladen, danach kontrollieren')));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Grunddaten', $PanelImport
                                , Panel::PANEL_TYPE_INFO)
                            , 6)
                    )
                )
            )
        );

        return $Stage;
    }

    public function frontendImportPrepare()
    {
        ini_set('memory_limit', '1G');

        $Stage = new Stage('Fakturierung', 'Import');
        $Stage->setMessage('Importvorbereitung / Daten importieren');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Import', new ChevronLeft()));

        $tblItemList = array();
        if(($tblItemAll = Item::useService()->getItemAll())){
            array_walk($tblItemAll, function(TblItem $tblItem) use (&$tblItemList){
                if($tblItem->getIsActive()){
                    $tblItemList[] = $tblItem;
                }
            });
        }

        $Stage->setContent(new Layout(new LayoutGroup(array(new LayoutRow(array(
            new LayoutColumn('', 2),
            new LayoutColumn(
                new Title('Nach Import', 'Zusatzinfo')
                .new Info(new Container('Import kann als "Nach Import" verwendet werden, dabei werden je nach Einstellung für die Beitragsart alle oder nur die betreffenden Einträge überschrieben.')
                    .new Container('Betreffende Einträge bedeutet dabei alle Zahlungseinstellungen an einem Schüler zu dieser Beitragsart, unabhängig von
                    eingestellten Zeiträumen.')
                    .new Container('Bereits erzeugte Abrechnungen werden davon nicht beeinflusst.')
                    , null, false, '15', '0')
                , 8)
            )),
            new LayoutRow(array(
                new LayoutColumn('', 2),
                new LayoutColumn(
                    new Title('Grunddaten', 'importieren')
                    .new Well(
                        new Form(new FormGroup(array(new FormRow(new FormColumn(
                            new Panel('Import',
                                array(
                                    (new SelectBox('Item', 'Beitragsart', array('{{ Name }}' => $tblItemList)))
                                        ->setRequired()
                                    .(new FileUpload('File', 'Datei auswählen', 'Datei auswählen '
                                        .new ToolTip(new InfoIcon(), 'Fakturierung Import.xlsx')
                                        , null, array('showPreview' => false)))->setRequired()
                                    .new CheckBox('CleanSelection', 'Entfernen aller vorhandenen Zahlungszuweisungen der Beitragsart', '1')
                                    . new Info(
                                        new Container('"Hochladen und Voransicht" führt noch keine Bereinigung aus.')
                                        .new Container(new Check().' Bereinigt alle Zahlungszuweisungen zur ausgewählten Beitragsart beim Durchführen des Imports.')
                                        .new Container(new Unchecked().' Bereinigt alle Zahlungszuweisungen aller gefundenen Schüler mit ausgewählter Beitragsart
                                        aus dem Import beim Durchführen des Imports. Schüler und ihre Zahlungszuweisungen, die nicht enthalten sind, bleiben
                                         unangetastet.')
                                        , null, false, '5', '5')
                                ), Panel::PANEL_TYPE_INFO)
                            )),)),
                            new Primary('Hochladen und Voransicht', new Upload()),
                            new Route(__NAMESPACE__.'/Upload')
                        )
                    ), 8
                )
            ))
        ))));

        return $Stage;
    }

    /**
     * @param null|UploadedFile $File
     * @param string            $Item
     * @param string            $CleanSelection
     *
     * @return Stage|string
     */
    public function frontendUpload(UploadedFile $File = null, string $Item = '', string $CleanSelection = ''):Stage|string
    {
        ini_set('memory_limit', '2G');

        $Stage = new Stage('Fakturierung Grunddaten', 'importieren');

        if ($File && !$File->getError()
            && (strtolower($File->getClientOriginalExtension()) == 'xlsx')
            && $Item
        ){
            if(($tblItem = Item::useService()->getItemById($Item))){
                $Item = $tblItem->getName();
            }

            // remove existing import
            Import::useService()->destroyImport();

            // match File
            $Extension = strtolower($File->getClientOriginalExtension());

            $Payload = new FilePointer($Extension);
            // ToDO eventuell wird hier auch das encoding benötigt.
//            $fileContent = file_get_contents($File->getRealPath());
//            $Payload->setFileContentWithEncoding($fileContent);
            $Payload->setFileContent(file_get_contents($File->getRealPath()));
            $Payload->saveFile();

            // Test
            $Control = new ImportControl($Payload->getRealPath());
            if (!$Control->getCompare()){
                $LayoutColumnList = array();
                $LayoutColumnList[] = new LayoutColumn(new Warning('Die Datei beinhaltet nicht alle benötigten Spalten'));
                $ColumnList = $Control->getDifferenceList();
                if (!empty($ColumnList)){
                    foreach ($ColumnList as $Value) {
                        $LayoutColumnList[] = new LayoutColumn(new Panel('Fehlende Spalte', $Value,
                            Panel::PANEL_TYPE_DANGER), 3);
                    }
                }

                $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Import/Prepare',
                    new ChevronLeft()));

                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(array(
                            new LayoutRow(
                                $LayoutColumnList
                            )
                        ))
                    )
                );
                return $Stage;
            }

            // add import
            $Gateway = new ImportGateway($Payload->getRealPath(), $Control, $Item);

            $ImportList = $Gateway->getImportList();
            if ($ImportList){
                Import::useService()->createImportBulk($ImportList);
            }
            $RemoveList = $Gateway->getRemoveList();

//            if($Gateway->getErrorCount() > 0){
//                $Stage->setMessage(new DangerText(new Bold($Gateway->getErrorCount())
//                    .' Einträge (rot) verhindern den Import.<br/>
//                Bitte überarbeiten Sie die Excel-Vorlage und/oder prüfen Sie, ob die Daten in der Personenverwaltung
//                der Schulsoftware korrekt hinterlegt sind.'));
//            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($Gateway->getResultList(), null,
                                    array(
                                        'Number'              => 'Zeile',
                                        'IsError'             => 'Fehler',
                                        'PersonFrontend'      => 'Beitragsverursacher',
                                        'ValueFrontend'       => 'Betrag',
                                        'ItemVariantFrontend' => 'Preis-Variante',
                                        'ItemControl'         => 'Beitragsart',
                                        'Reference'           => 'Mandatsreferenz',
                                        'ReferenceDate'       => 'M.Ref. Gültig ab',
                                        'PaymentFromDate'     => 'Zahlung ab',
                                        'PaymentTillDate'     => 'Zahlung bis',
                                        'DebtorFrontend'      => 'Beitragszahler',
                                        'Owner'               => 'Kontoinhaber',
                                        'DebtorNumberControl' => 'Debitoren Nr.',
                                        'IBANControl'         => 'IBAN Kontrolle',
                                        'BICControl'          => 'BIC',
                                        'Bank'                => 'Bank',
                                    ),
                                    array(
                                        'order'      => array(array(1, 'desc'),array(0, 'asc')),
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => 0),
                                        ),
                                        'responsive' => false,
                                        'pageLength' => -1,
                                    )
                                )
                            ),
                            new LayoutColumn(
                                new DangerLink('Abbrechen', '/Billing/Inventory/Import/Prepare').
                                new Standard('Weiter', '/Billing/Inventory/Import/Do', new ChevronRight(),
                                    array('ItemId' => $tblItem->getId(), 'CleanSelection' => $CleanSelection, 'RemoveList' => $RemoveList))
//                                ($Gateway->getErrorCount() == 0
//                                    ? new Standard('Weiter', '/Billing/Inventory/Import/Do', new ChevronRight(),
//                                        array('ItemId' => $tblItem->getId(), 'CleanSelection' => $CleanSelection))
//                                    : ''
//                                )
                            )
                        ))
                    )
                )
            );
        } else {
            if($Item){
                return $Stage->setContent(new Warning('Ungültige Dateiendung!'))
                    .new Redirect('/Billing/Inventory/Import/Prepare', Redirect::TIMEOUT_ERROR);
            } else {
                if($File && !$File->getError()
                    && (strtolower($File->getClientOriginalExtension()) == 'xlsx')){
                    return $Stage->setContent(new Warning('Bitte füllen Sie die Beitragsart aus.'))
                        .new Redirect('/Billing/Inventory/Import/Prepare', Redirect::TIMEOUT_ERROR);
                } else {
                    return $Stage->setContent(new Warning('Bitte füllen Sie die Beitragsart aus.')
                        .new Warning('Ungültige Dateiendung!'))
                        .new Redirect('/Billing/Inventory/Import/Prepare', Redirect::TIMEOUT_ERROR);
                }
            }
        }

        return $Stage;
    }

    /**
     * @param string $ItemId
     * @param string $CleanSelection
     *
     * @return Stage
     */
    public function frontendDoImport(string $ItemId = '', string $CleanSelection = '', array $RemoveList = array()):Stage
    {

        $Stage = new Stage('Import', 'Prozess');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory', new ChevronLeft(), array(),
            'Zurück zum Import'));

        if($CleanSelection && ($tblItem = Item::useService()->getItemById($ItemId))){
            Debtor::useService()->destroyDebtorSelectionBulkByItem($tblItem);
        } elseif(!empty($RemoveList)) {
            Debtor::useService()->destroyDebtorSelectionBulkByIdArray($RemoveList);
        }
        Import::useService()->importBillingData();
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Success('Import wurde erfolgreich durchgeführt.')
                    ),
                    new LayoutColumn(
                        new Redirect('/Billing/Inventory/Import', Redirect::TIMEOUT_SUCCESS)
                    )
                ))
            )
        ));
        return $Stage;
    }
}