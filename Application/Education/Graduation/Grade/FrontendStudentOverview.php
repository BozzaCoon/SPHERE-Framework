<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiStudentOverview;
use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineGradebook;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

abstract class FrontendStudentOverview extends FrontendScoreType
{
    /**
     * @param null $Filter
     *
     * @return string
     */
    public function loadViewStudentOverviewCourseSelect($Filter = null): string
    {
        $role = Grade::useService()->getRole();
        $isTeacher = $role == "Teacher";
        if (($tblYear = Grade::useService()->getYear())) {
            $dataList = array();
            // Lehrer
            if ($isTeacher && ($tblPersonLogin = Account::useService()->getPersonByLogin())) {
                // Klassenlehrer aus den Lehraufträgen der Lehrer
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPersonLogin, $tblYear))) {
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        $dataList[] = $this->getDivisionCourseSelectData($tblDivisionCourse, $tblYear);
                    }
                }

                $content = $this->getTable($dataList);
            } else {
                $content = $this->getSelectStudentOverviewHeadmaster($Filter);
            }
        } else {
            $content = new Danger("Schuljahr nicht gefunden", new Exclamation());
        }

        return new Title("Schülerübersicht", "Klasse des Schülers auswählen") . $content;
    }

    private function getDivisionCourseSelectData(TblDivisionCourse $tblDivisionCourse, TblYear $tblYear, $Filter = null): array
    {
        return array(
            'Year' => $tblYear->getDisplayName(),
            'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
            'CourseType' => $tblDivisionCourse->getTypeName(),
            'Option' => (new Standard("", ApiStudentOverview::getEndpoint(), new Check(), array(), "Auswählen"))
                ->ajaxPipelineOnClick(ApiStudentOverview::pipelineLoadViewStudentOverviewCourseContent($tblDivisionCourse->getId(), $Filter))
        );
    }

    /**
     * @param array $dataList
     *
     * @return TableData
     */
    private function getTable(array $dataList): TableData
    {
        return new TableData(
            $dataList,
            null,
            array(
                'Year' => 'Schuljahr',
                'DivisionCourse' => 'Kurs',
                'CourseType' => 'Kurs-Typ',
                'Option' => ''
            ),
            array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'width' => '30px', 'targets' => -1),
                )
            )
        );
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    private function getSelectStudentOverviewHeadmaster($Filter): string
    {
        return
            new Panel(
                new Filter() . " Filter",
                $this->formFilter($Filter),
                Panel::PANEL_TYPE_INFO
            )
            . ApiGradeBook::receiverBlock($Filter == null ? $this->loadGradeBookSelectFilterContent($Filter) : "", "StudentOverviewSelectCourseFilterContent");
    }

    /**
     * @param null $Filter
     *
     * @return Form
     */
    private function formFilter($Filter = null): Form
    {
        if ($Filter) {
            $global = $this->getGlobal();
            if (isset($Filter["SchoolType"])) {
                $global->POST["Filter"]["SchoolType"] = $Filter["SchoolType"];
            }
            $global->savePost();
        }

        $tblSchoolTypeList = School::useService()->getConsumerSchoolTypeCommonAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Filter[SchoolType]', 'Schulart', array('{{ Name }}' => $tblSchoolTypeList)))
                        ->ajaxPipelineOnChange(ApiStudentOverview::pipelineLoadStudentOverviewSelectCourseFilterContent($Filter))
                    , 12),
            )),
        )));
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    public function loadStudentOverviewSelectCourseFilterContent($Filter): string
    {
        $tblSchoolType = isset($Filter["SchoolType"]) ? Type::useService()->getTypeById($Filter["SchoolType"]) : false;
        if ($tblSchoolType
            && ($tblYear = Grade::useService()->getYear())
        ) {
            $dataList = array();
            if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, '', true))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    if (!($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                        || !isset($tblSchoolTypeList[$tblSchoolType->getId()])
                    ) {
                        continue;
                    }

                    $dataList[] = $this->getDivisionCourseSelectData($tblDivisionCourse, $tblYear, $Filter);
                }
            }

            // bei der DataTable dürfen als Key nur Zahlen verwenden
            $dataList = array_values($dataList);
            $content = $this->getTable($dataList);

        } else {
            $content = new Warning("Bitte filtern Sie nach einer Schulart.", new Exclamation());
        }

        return $content;
    }

    /**
     * @param $DivisionCourseId
     * @param $Filter
     *
     * @return string
     */
    public function loadViewStudentOverviewCourseContent($DivisionCourseId, $Filter): string
    {
        $textCourse = "";
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $textCourse = new Bold($tblDivisionCourse->getDisplayName());

            $integrationList = array();
            $pictureList = array();
            $courseList = array();

            $tblSubjectList = array();

            if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                foreach ($tblPersonList as $tblPerson) {
                    // Schüler-Informationen
                    Grade::useService()->setStudentInfo($tblPerson, $tblYear, $integrationList, $pictureList, $courseList);
                }

                $tblSubjectList = DivisionCourse::useService()->getSubjectListByPersonListAndYear($tblPersonList, $tblYear);
            }

            $hasPicture = !empty($pictureList);
            $hasIntegration = !empty($integrationList);
            $hasCourse = !empty($courseList);
            $headerList = $this->getGradeBookPreHeaderList($hasPicture, $hasIntegration, $hasCourse);
            if ($tblSubjectList) {
                $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
                /** @var TblSubject $tblSubject */
                foreach ($tblSubjectList as $tblSubject) {
                    $headerList[$tblSubject->getId()] = $this->getTableColumnHead($tblSubject->getAcronym());
                }
            } else {
                $tblSubjectList = array();
            }
            $headerList['Option'] = $this->getTableColumnHead('');

            $bodyList = array();
            $averageSumList = array();
            $averageCountList = array();
            if ($tblPersonList) {
                $count = 0;
                foreach ($tblPersonList as $tblPerson) {
                    $bodyList[$tblPerson->getId()] = $this->getGradeBookPreBodyList($tblPerson, ++$count, $hasPicture, $hasIntegration, $hasCourse,
                        $pictureList, $integrationList, $courseList);

                    foreach ($tblSubjectList as $tblSubject) {
                        // Schüler Berechnungsvorschrift ermitteln
                        $tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $tblDivisionCourse);
                        if (($tblTestGradeList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject(
                            $tblPerson, $tblYear, $tblSubject
                        ))) {
                            list ($average, $scoreRuleText, $error) = Grade::useService()->calcStudentAverage($tblPerson, $tblYear, $tblTestGradeList, $tblScoreRule ?? null);
                            $contentSubject = '&#216; ' . Grade::useService()->getCalcStudentAverageToolTipByAverage($average, $scoreRuleText, $error);
                            $average = Grade::useService()->getGradeNumberValue($average);
                            if (isset($averageSumList[$tblSubject->getId()])) {
                                $averageSumList[$tblSubject->getId()] += $average;
                            } else {
                                $averageSumList[$tblSubject->getId()] = $average;
                            }
                            if (isset($averageCountList[$tblSubject->getId()])) {
                                $averageCountList[$tblSubject->getId()]++;
                            } else {
                                $averageCountList[$tblSubject->getId()] = 1;
                            }
                        } else {
                            $contentSubject = '';
                        }

                        $bodyList[$tblPerson->getId()][$tblSubject->getId()] = $this->getTableColumnBody($contentSubject);
                    }

                    $bodyList[$tblPerson->getId()]['Option'] = $this->getTableColumnBody((new Standard("", ApiStudentOverview::getEndpoint(), new EyeOpen(), array(), "Schülerübersicht anzeigen"))
                        ->ajaxPipelineOnClick(ApiStudentOverview::pipelineLoadViewStudentOverviewStudentContent($tblDivisionCourse->getId(), $tblPerson->getId(), $Filter, 'All')));
                }
            }

            // Fach-Klassen-Durchschnitt
            $rowDataList = array();
            foreach ($headerList as $key => $value) {
                $contentTemp = '';
                if ($key == 'Person') {
                    $contentTemp = $this->getTableColumnBody(new Muted('&#216; Fach-Klasse'));
                } elseif (isset($averageSumList[$key])) {
                    $contentTemp = $this->getTableColumnBody('&#216; ' . Grade::useService()->getGradeAverage($averageSumList[$key], $averageCountList[$key]));
                }
                $rowDataList[$key] = $contentTemp;
            }
            $bodyList[-1] = $rowDataList;

            $content = $this->getTableCustom($headerList, $bodyList);
        } else {
            $content = new Danger("Der Kurs wurde nicht gefunden.", new Exclamation());
        }

        return new Title(
                (new Standard("Zurück", ApiGradeBook::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiStudentOverview::pipelineLoadViewStudentOverviewCourseSelect($Filter))
                . "&nbsp;&nbsp;&nbsp;&nbsp;Schülerübersicht"
                . new Muted(new Small(" für Kurs: ")) . $textCourse
                . new Muted(new Small(" Schüler auswählen"))
            )
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . $content;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $Filter
     * @param string $View
     *
     * @return string
     */
    public function loadViewStudentOverviewStudentContent($DivisionCourseId, $PersonId, $Filter, string $View = 'Parent'): string
    {
        $content = "";
        $supportButton = "";
        $isParentView = $View != 'All';
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                $content = Grade::useService()->getStudentOverviewDataByPerson($tblPerson, $tblYear, $tblStudentEducation, $isParentView, false);
                // Anzeige Klasse + Stammgruppe
                $textCourse = DivisionCourse::useService()->getCurrentMainCoursesByStudentEducation($tblStudentEducation);
            } else {
                $textCourse = $tblDivisionCourse->getDisplayName();
            }

            // Integrationsbutton
            if(Student::useService()->getIsSupportByPerson($tblPerson)) {
                $supportButton = (new Standard('Integration', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                    ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
            }

            $textAll = !$isParentView ? new Info(new Edit() . new Bold(' Ansicht: Alle Zensuren')) : 'Ansicht: Alle Zensuren';
            $textParent = $isParentView ? new Info(new Edit() . new Bold(' Ansicht: Eltern/Schüler')) : 'Ansicht: Eltern/Schüler';

            return
                (new Standard("Zurück", ApiStudentOverview::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiStudentOverview::pipelineLoadViewStudentOverviewCourseContent($DivisionCourseId, $Filter))
                . (new Standard($textAll, ApiStudentOverview::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiStudentOverview::pipelineLoadViewStudentOverviewStudentContent($DivisionCourseId, $PersonId, $Filter, 'All'))
                . (new Standard($textParent, ApiStudentOverview::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiStudentOverview::pipelineLoadViewStudentOverviewStudentContent($DivisionCourseId, $PersonId, $Filter, 'Parent'))
                . $supportButton
                . new PullRight(new External(
                    'Schülerübersicht herunterladen', 'SPHERE\Application\Api\Document\Standard\GradebookOverview\Create',
                    new Download(), array('PersonId' => $PersonId, 'YearId' => $tblYear->getId(), 'Notenübersicht herunterladen')
                    , false
                ))
                . new Title($tblPerson->getLastFirstName() . " " . new Muted(new Small($textCourse)))
                . ApiOnlineGradebook::receiverModal()
                . ApiSupportReadOnly::receiverOverViewModal()
                . $content;
        } else {
            return new Danger("Kurs oder Person nicht gefunden.", new Exclamation());
        }
    }
}