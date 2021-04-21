<?php

namespace SPHERE\Application\Education\Certificate\Reporting;

use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Reporting
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendSelect()
    {
        $Stage = new Stage('Zeugnisse auswerten', 'Übersicht');

        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Panel(
                    'Abschlusszeugnisse',
                    array(
                        'Hauptschule ' . new PullRight((new Standard('', '/Education/Certificate/Reporting/Diploma', new Select(), array(
                            'View' => View::HS
                        ), 'Hauptschulabschlusszeugnisse auswählen'))),
                        'Realschule ' . new PullRight((new Standard('', '/Education/Certificate/Reporting/Diploma', new Select(), array(
                            'View' => View::RS
                        ), 'Realschulabschlusszeugnisse auswählen'))),
                        'Gymnasium - Abitur ' . new PullRight((new Standard('', '/Education/Certificate/Reporting/Diploma', new Select(), array(
                            'View' => View::ABI
                        ), 'Abiturabschlusszeugnisse auswählen'))),
                    ),
                    Panel::PANEL_TYPE_INFO
                )
            , 4))))
        );

        return $Stage;
    }

    /**
     * @param $View
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendDiploma($View = null, $YearId = null)
    {
        switch ($View) {
            case View::HS: $description = 'Hauptschulabschlusszeugnisse'; break;
            case View::RS: $description = 'Realschulabschlusszeugnisse'; break;
            case View::ABI: $description = 'Abiturabschlusszeugnisse'; break;
            default: $description = '';
        }
        $Stage = new Stage('Zeugnisse auswerten', $description);
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $tblYearList = array();
        $generateList = array();
        if (($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('DIPLOMA'))
            && ($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAll())
        ) {
            foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                if (($tblGenerateYear = $tblGenerateCertificate->getServiceTblYear())) {
                    $tblYearList[$tblGenerateYear->getId()] = $tblGenerateYear;
                    $generateList[$tblGenerateYear->getId()][] = $tblGenerateCertificate;
                }
            }
        }

        $tblYear = Term::useService()->getYearById($YearId);
        if (!empty($tblYearList)) {
            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName', null, Sorter::ORDER_DESC);
            if (!$tblYear) {
                $tblYear = current($tblYearList);
            }
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $Stage->addButton(new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        '/Education/Certificate/Reporting/Diploma', new Edit(), array(
                            'View' => $View,
                            'YearId' => $tblYearItem->getId()
                        )));
                } else {
                    $Stage->addButton(new Standard($tblYearItem->getDisplayName(), '/Education/Certificate/Reporting/Diploma',
                        null, array('View' => $View,'YearId' => $tblYearItem->getId())));
                }
            }

            if ($tblYear && isset($generateList[$tblYear->getId()])) {
                $dataList = array();
                $sum = 0;
                $count = 0;
                foreach ($generateList[$tblYear->getId()] as $item) {
                    if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($item))) {
                        foreach ($tblPrepareList as $tblPrepare) {
                            if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))) {
                                foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                    if ($tblPrepareStudent->isPrinted()
                                        && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                                        && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                        && (($View == View::HS && ($tblCertificate->getCertificate() == 'MsAbsHs' || $tblCertificate->getCertificate() == 'MsAbsHsQ'))
                                            || ($View == View::RS && $tblCertificate->getCertificate() == 'MsAbsRs')
                                            || ($View == View::ABI && $tblCertificate->getCertificate() == 'GymAbitur')
                                        )
                                    ) {
                                        if ($View == View::ABI) {
                                            // Berechnung der Gesamtqualifikation und der Durchschnittsnote
                                            /** @noinspection PhpUnusedLocalVariableInspection */
                                            list($countCourses, $resultBlockI) = Prepare::useService()->getResultForAbiturBlockI(
                                                $tblPrepare,
                                                $tblPerson
                                            );
                                            $resultBlockII = Prepare::useService()->getResultForAbiturBlockII(
                                                $tblPrepare,
                                                $tblPerson
                                            );
                                            $resultPoints = $resultBlockI + $resultBlockII;
                                            if ($resultBlockI >= 200 && $resultBlockII >= 100) {
                                                $average = Prepare::useService()->getResultForAbiturAverageGrade($resultPoints);
                                            } else {
                                                $average = false;
                                            }
                                        } else {
                                            $average = $this->calcDiplomaAverageGrade($tblPrepare, $tblPerson);
                                        }

                                        if ($average) {
                                            $sum += $average;
                                            $count++;
                                        }
                                        $dataList[$tblPerson->getId()] = array(
                                            'Name' => $tblPerson->getLastFirstName(),
                                            'Average' => $average ? str_replace('.', ',', $average) : '&ndash;'
                                        );
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($dataList)) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                new Panel(
                                    $description,
                                    'Gesamtnotendurchschnitt: ' . ($count > 0 ? str_replace('.', ',', round(floatval($sum / $count), 1)) : '&ndash;'),
                                    Panel::PANEL_TYPE_INFO
                                )
                            )),
                            new LayoutRow(new LayoutColumn(
                                new TableData(
                                    $dataList,
                                    null,
                                    array(
                                        'Name' => 'Name',
                                        'Average' => 'Notendurchschnitt',
                                    ),
                                    array(
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                        ),
                                        'order' => array(
                                            array(1, 'asc'),
                                        )
                                    )
                                )
                            ))
                        )))
                    );
                } else {
                    $Stage->setContent(new Warning('Es sind noch keine gedruckten Abschlusszeugnisse für das Schuljahr: '
                        . $tblYear->getDisplayName() . ' vorhanden.', new Exclamation()));
                }
            }
        } else {
            $Stage->setContent(new Warning('Es sind noch keine Abschlusszeugnisse vorhanden.', new Exclamation()));
        }

        return $Stage;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return bool|false|float
     */
    public function calcDiplomaAverageGrade(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN'))
            && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
            ))
        ) {
            $gradeList = array();
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if ($tblPrepareAdditionalGrade->getGrade() != '') {
                    $grade = str_replace('+', '', $tblPrepareAdditionalGrade->getGrade());
                    $grade = str_replace('-', '', $grade);
                    if (is_numeric($grade)) {
                        $gradeList[] = $grade;
                    }
                }
            }

            if (!empty($gradeList)) {
                return round(floatval(array_sum($gradeList) / count($gradeList)), 1);
            }
        }

        return false;
    }
}