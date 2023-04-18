<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiYearChange;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Blackboard;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Stage;

class FrontendYearChange extends FrontendTeacher
{
    /**
     * @return Stage
     */
    public function frontendYearChange(): Stage
    {
        $stage = new Stage('Schuljahreswechsel', '');
        $stage->setContent(
            new Panel(new Calendar() . ' Schuljahreswechsel', $this->formYearChange(), Panel::PANEL_TYPE_INFO)
            . ApiYearChange::receiverBlock($this->loadYearChangeContent(null), 'YearChangeContent')
        );

        return $stage;
    }

    /**
     * @return Form
     */
    public function formYearChange(): Form
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[SchoolType]', 'Schulart', array('{{ Name }}' => Type::useService()->getTypeAll())))
                        ->ajaxPipelineOnChange(ApiYearChange::pipelineLoadYearChangeContent())
                        ->setRequired()
                    , 6),
            )),
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[YearSource]', 'von Schuljahr', array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                        ->ajaxPipelineOnChange(ApiYearChange::pipelineLoadYearChangeContent())
                        ->setRequired()
                    , 6),
                new FormColumn(
                    (new SelectBox('Data[YearTarget]', 'nach Schuljahr', array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(0))))
                        ->ajaxPipelineOnChange(ApiYearChange::pipelineLoadYearChangeContent())
                        ->setRequired()
                    , 6),
            )),
            new FormRow(array(
                new FormColumn(
                    (new CheckBox('Data[HasTeacherLectureship]', 'Lehraufträge werden mit ins neue Schuljahr übernommen.', 1))
                        ->ajaxPipelineOnChange(ApiYearChange::pipelineLoadYearChangeContent())
                )
            ))
        )));
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function loadYearChangeContent($Data): string
    {
        $content = '';
        $tblSchoolType = false;
        $tblYearSource = false;
        $tblYearTarget = false;
        if (!isset($Data['SchoolType']) || !($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))) {
            $content .= new Warning('Bitte wählen Sie eine Schulart aus.', new Exclamation());
        }
        if (!isset($Data['YearSource']) || !($tblYearSource = Term::useService()->getYearById($Data['YearSource']))) {
            $content .= new Warning('Bitte wählen Sie ein Quell-Schuljahr aus.', new Exclamation());
        }
        if (!isset($Data['YearTarget']) || !($tblYearTarget = Term::useService()->getYearById($Data['YearTarget']))) {
            $content .= new Warning('Bitte wählen Sie ein Ziel-Schuljahr aus.', new Exclamation());
        }
        $hasOptionTeacherLectureship = isset($Data['HasTeacherLectureship']);

        if ($tblSchoolType && $tblYearSource && $tblYearTarget) {
            if ($tblYearTarget->getName() <= $tblYearSource->getName()) {
                return new Warning('Bitte wählen Sie neueres Ziel-Schuljahr aus.', new Exclamation());
            }

            $left = '';
            $right = '';

            list(
                $hasAddStudentEducationList, $dataSourceList, $dataTargetList,
                $hasAddCoursesList, $dataCourseLeft, $dataCourseRight,
                $hasAddTeacherLectureshipList, $dataTeacherLectureshipLeft, $dataTeacherLectureshipRight
            ) = DivisionCourse::useService()->getYearChangeData($tblSchoolType, $tblYearSource, $tblYearTarget, $hasOptionTeacherLectureship, false);

            /**
             * Schüler-Bildung anzeigen
             */
            ksort($dataSourceList);
            foreach ($dataSourceList as $levelKey => $nameList) {
                $left .= new Panel('Klassenstufe: ' . $levelKey, $nameList, Panel::PANEL_TYPE_DEFAULT);
            }
            ksort($dataTargetList);
            foreach ($dataTargetList as $levelKey => $nameList) {
                $right .= new Panel('Klassenstufe: ' . $levelKey, $nameList,
                    isset($hasAddStudentEducationList[$levelKey]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
            }
            $content .= new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($left, 6),
                new LayoutColumn($right, 6)
            )), new Title(new PersonGroup() . ' Schüler-Bildung')));

            /*
             * Kurse anzeigen
             */
            $contentCourseLeft = '';
            $contentCourseRight = '';
            $identifier = TblDivisionCourseType::TYPE_DIVISION;
            if (isset($dataCourseLeft[$identifier])) {
                $contentCourseLeft .= new Panel('Klassen', $dataCourseLeft[$identifier], Panel::PANEL_TYPE_DEFAULT);
                $contentCourseRight .= new Panel('Klassen', $dataCourseRight[$identifier],
                    isset($hasAddCoursesList[$identifier]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
            }
            $identifier = TblDivisionCourseType::TYPE_CORE_GROUP;
            if (isset($dataCourseLeft[$identifier])) {
                $contentCourseLeft .= new Panel('Stammgruppen', $dataCourseLeft[$identifier], Panel::PANEL_TYPE_DEFAULT);
                $contentCourseRight .= new Panel('Stammgruppen', $dataCourseRight[$identifier],
                    isset($hasAddCoursesList[$identifier]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
            }
            $identifier = TblDivisionCourseType::TYPE_TEACHING_GROUP;
            if (isset($dataCourseLeft[$identifier])) {
                $contentCourseLeft .= new Panel('Unterrichtsgruppen', $dataCourseLeft[$identifier], Panel::PANEL_TYPE_DEFAULT);
                $contentCourseRight .= new Panel('Unterrichtsgruppen', $dataCourseRight[$identifier],
                    isset($hasAddCoursesList[$identifier]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
            }
            $content .= new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($contentCourseLeft, 6),
                new LayoutColumn($contentCourseRight, 6)
            )), new Title(new Blackboard() . ' Kurse der Schüler')));

            /*
             * Lehraufträge anzeigen
             */
            if ($hasOptionTeacherLectureship) {
                $panelTeacherListLeft = array();
                foreach ($dataTeacherLectureshipLeft as $teacherId => $subjectList) {
                    if (($tblTeacherPerson = Person::useService()->getPersonById($teacherId))) {
                        $panelName = $tblTeacherPerson->getLastFirstName()
                            . (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblTeacherPerson)) ? ' (' . $tblTeacher->getAcronym() . ')' : '');
                        $panelData = array();
                        foreach ($subjectList as $subjectId => $courseList) {
                            if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                                $panelData[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn($tblSubject->getDisplayName() . ':', 6),
                                    new LayoutColumn(implode(', ', $courseList), 6),
                                ))));
                            }
                        }
                        asort($panelData);
                        $panelTeacherListLeft[] = new Panel($panelName, $panelData, Panel::PANEL_TYPE_DEFAULT);
                    }
                }
                $panelTeacherListRight = array();
                foreach ($dataTeacherLectureshipRight as $teacherId => $subjectList) {
                    if (($tblTeacherPerson = Person::useService()->getPersonById($teacherId))) {
                        $panelName = $tblTeacherPerson->getLastFirstName()
                            . (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblTeacherPerson)) ? ' (' . $tblTeacher->getAcronym() . ')' : '');
                        $panelData = array();
                        foreach ($subjectList as $subjectId => $courseList) {
                            if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                                $panelData[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn($tblSubject->getDisplayName() . ':', 6),
                                    new LayoutColumn(implode(', ', $courseList), 6),
                                ))));
                            }
                        }
                        asort($panelData);
                        $panelTeacherListRight[] = new Panel($panelName, $panelData,
                            isset($hasAddTeacherLectureshipList[$teacherId]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
                    }
                }
                asort($panelTeacherListLeft);
                asort($panelTeacherListRight);
                $content .= new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(implode('<br/>', $panelTeacherListLeft), 6),
                    new LayoutColumn(implode('<br/>', $panelTeacherListRight), 6)
                )), new Title(new Education() . ' Lehraufräge')));
            }

            $content .= (new Primary('Speichern', ApiYearChange::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiYearChange::pipelineSaveYearChangeContent(
                    $tblSchoolType->getId(), $tblYearSource->getId(), $tblYearTarget->getId(), $hasOptionTeacherLectureship
                ));
        }

        return $content;
    }

    /**
     * @param $SchoolTypeId
     * @param $YearSourceId
     * @param $YearTargetId
     * @param $hasOptionTeacherLectureship
     *
     * @return string
     */
    public function saveYearChangeContent($SchoolTypeId, $YearSourceId, $YearTargetId, $hasOptionTeacherLectureship): string
    {
        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            && ($tblYearSource = Term::useService()->getYearById($YearSourceId))
            && ($tblYearTarget = Term::useService()->getYearById($YearTargetId))
        ) {

            DivisionCourse::useService()->getYearChangeData($tblSchoolType, $tblYearSource, $tblYearTarget, $hasOptionTeacherLectureship, true);

            return new Success(
                'Die Schulart wurde erfolgreich ins neue Schuljahr übertragen.',
                new SuccessIcon()
            );
        }

        return new Danger('Die Schulart konnte nicht ins neue Schuljahr übertragen werden');
    }
}