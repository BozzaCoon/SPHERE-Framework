<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiStudentSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

class FrontendStudentSubject extends FrontendStudent
{
    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadStudentSubjectContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
            DivisionCourse::useService()->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblDivisionCourseList);
            $hasSubDivisionCourse = count($tblDivisionCourseList) > 1;

            $studentList = array();
            $subjectList = array();
            $subjectTableList = array();
            foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                if (($tblStudentMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourseItem,
                    TblDivisionCourseMemberType::TYPE_STUDENT, true, false))
                ) {
                    $count = 0;
                    foreach ($tblStudentMemberList as $tblStudentMember) {
                        if (($tblPerson = $tblStudentMember->getServiceTblPerson()) && !$tblStudentMember->isInActive()) {
                            // todo SEKII

                            $fullName = $tblPerson->getLastFirstName();

                            $item = array();
                            $item['Number'] = ++$count;
                            $item['FullName'] = $fullName;
                            if ($hasSubDivisionCourse) {
                                $item['DivisionCourse'] = $tblDivisionCourseItem->getName();
                            }

                            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                                && ($level = $tblStudentEducation->getLevel())
                                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                            ) {
                                /*
                                 * Stundentafel
                                 */
                                $addHeader = false;
                                if (!isset($subjectTableList[$tblSchoolType->getId()][$level])) {
                                    $addHeader = true;
                                    $subjectTableList[$tblSchoolType->getId()][$level] = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType, $level);
                                }
                                if ($subjectTableList[$tblSchoolType->getId()][$level]) {
                                    /** @var TblSubjectTable $tblSubjectTable */
                                    foreach ($subjectTableList[$tblSchoolType->getId()][$level] as $tblSubjectTable) {
                                        if (($tblSubjectTable->getIsFixed())
                                            && ($tblSubject = $tblSubjectTable->getServiceTblSubject())
                                        ) {
                                            if ($addHeader && !isset($subjectList[$tblSubject->getId()])) {
                                                $subjectList[$tblSubject->getId()] = $tblSubject->getAcronym()
                                                    . (new PullRight((new Link('', ApiStudentSubject::getEndpoint(), new Pen))
                                                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineEditStudentSubjectContent(
                                                            $tblDivisionCourse->getId(), $tblSubject->getId()
                                                        ))
                                                    ));
                                            }
                                            $item[$tblSubject->getId()] = new ToolTip(new Muted('ST'), 'Stundentafel');
                                        }
                                        // todo else -> überprüfen ob erfüllt oder Fallback auf die Schülerakte
                                    }
                                }

                                /*
                                 * gespeicherte Fächer am Schüler
                                 */
                                if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear))) {
                                    foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                        if (($tblSubjectItem = $tblStudentSubject->getServiceTblSubject())) {
                                            if (!isset($item[$tblSubjectItem->getId()])) {
                                                $item[$tblSubjectItem->getId()] = new Check();
                                            }

                                            if (!isset($subjectList[$tblSubjectItem->getId()])) {
                                                $subjectList[$tblSubjectItem->getId()] = $tblSubjectItem->getAcronym()
                                                    . (new PullRight((new Link('', ApiStudentSubject::getEndpoint(), new Pen))
                                                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineEditStudentSubjectContent(
                                                            $tblDivisionCourse->getId(), $tblSubjectItem->getId()
                                                        ))
                                                    ));
                                            }
                                        }
                                    }
                                }
                            }

                            $studentList[] = $item;
                        }
                    }
                }
            }

            $backgroundColor = '#E0F0FF';
            $headerColumnList[] = $this->getTableHeaderColumn('#', $backgroundColor);
            $headerColumnList[] = $this->getTableHeaderColumn('Schüler', $backgroundColor);
            if ($hasSubDivisionCourse) {
                $headerColumnList[] = $this->getTableHeaderColumn('Kurs', $backgroundColor);
            }
            foreach ($subjectList as $acronym)
            {
                $headerColumnList[] = $this->getTableHeaderColumn($acronym, $backgroundColor);
            }

            // reihenfolge inhalt und leere auffüllen
            $contentList = array();
            foreach ($studentList as $student)
            {
                $item = array();
                $item['Number'] = $student['Number'];
                $item['FullName'] = $student['FullName'];
                if ($hasSubDivisionCourse) {
                    $item['DivisionCourse'] = $student['DivisionCourse'];
                }
                foreach ($subjectList as $subjectId => $value) {
                    if (isset($student[$subjectId])) {
                        $item[$subjectId] = $student[$subjectId];
                    } else {
                        $item[$subjectId] = '&nbsp;';
                    }
                }

                $contentList[] = $item;
            }

            return empty($studentList)
                ? new Warning('Keine Schüler dem Kurs zugewiesen')
                : $this->getTableCustom($headerColumnList, $contentList);
        }

        return '';
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param null $Data
     *
     * @return string
     */
    public function editStudentSubjectContent($DivisionCourseId, $SubjectId, $Data = null): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblDivisionCourse->getServiceTblYear())
        ) {
            if ($SubjectId) {
                $global = $this->getGlobal();
                $global->POST['Data']['Subject'] = $SubjectId;
                $Data['Subject'] = $SubjectId;
                $global->savePost();
            }

            return new Well((new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(new Panel(
                                'Fach',
                                (new SelectBox('Data[Subject]', '', array('{{ Acronym }}-{{ Name }}' => Subject::useService()->getSubjectAll())))
                                    ->setRequired()
                                    ->ajaxPipelineOnChange(ApiStudentSubject::pipelineLoadCheckSubjectsContent($DivisionCourseId)),
                                Panel::PANEL_TYPE_INFO
                            ), 6),
                            new FormColumn(new Panel(
                                'Benotung',
                                new CheckBox('Data[HasGrading]', 'Benotung', 1),
                                Panel::PANEL_TYPE_INFO
                            ), 6)
                        )),
                        new FormRow(new FormColumn(
                            ApiStudentSubject::receiverBlock($SubjectId
                                ? $this->loadCheckSubjectsContent($DivisionCourseId, $Data)
                                : new Warning('Bitte wählen Sie zunächst ein Fach aus.'), 'CheckSubjectsContent')
                        ))
                    )),
                )))->disableSubmitAction());
        }

        return new Danger('Kurs oder Schuljahr nicht gefunden', new Exclamation());
    }

    /**
     * @param $DivisionCourseId
     * @param $Data
     *
     * @return string
     */
    public function loadCheckSubjectsContent($DivisionCourseId, $Data): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $dataList = array();
            $toggleList = array();
            $subjectTableFixedList = array();
            $tblStudentList = DivisionCourse::useService()->getStudentListBy($tblDivisionCourse);

            if (isset($Data['Subject']) && ($tblSubject = Subject::useService()->getSubjectById($Data['Subject']))) {
                $global = $this->getGlobal();
                $global->POST['Data']['StudentList'] = null;

                $hasGrading = false;
                if (($tblStudentList)) {
                    foreach ($tblStudentList as $tblPerson) {
                        $isPost = false;
                        if (($tblStudentSubject = DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                            $isPost = true;
                            if (!$hasGrading) {
                                $hasGrading = $tblStudentSubject->getHasGrading();
                            }
                        } elseif (($tblSubjectTable = DivisionCourse::useService()->getSubjectTableByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))
                            && ($tblSubjectTable->getIsFixed())
                        ) {
                            $isPost = true;
                            if (!$hasGrading) {
                                $hasGrading = $tblSubjectTable->getHasGrading();
                            }
                            $subjectTableFixedList[$tblPerson->getId()] = $tblSubjectTable;
                        }

                        if ($isPost) {
                            $global->POST['Data']['StudentList'][$tblPerson->getId()] = 1;
                        }
                    }
                }
                if ($hasGrading) {
                    $global->POST['Data']['HasGrading'] = 1;
                }
                $global->savePost();
            } else {
                return new Warning('Bitte wählen Sie zunächst ein Fach aus.');
            }

            if (($tblStudentList)) {
                foreach ($tblStudentList as $tblPerson) {
                    $name = 'Data[StudentList][' . $tblPerson->getId() . ']';
                    $checkBox = new CheckBox($name, $tblPerson->getLastFirstName() . (isset($subjectTableFixedList[$tblPerson->getId()]) ? ' (Stundentafel)' : ''), 1);
                    if (isset($subjectTableFixedList[$tblPerson->getId()])) {
                        $checkBox->setDisabled();
                    } else {
                        $toggleList[$tblPerson->getId()] = $name;
                    }

                    $dataList[$tblPerson->getId()] = $checkBox;
                }
            }

            if ($dataList) {
                $panel = new Panel('Schüler', $dataList, Panel::PANEL_TYPE_INFO);
                return new ToggleSelective( 'Alle wählen/abwählen', $toggleList)
                    . new Container('&nbsp;')
                    . $panel
                    . (new Primary('Speichern', ApiStudentSubject::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineSaveStudentSubjectList($DivisionCourseId))
                    . (new Primary('Abbrechen', ApiStudentSubject::getEndpoint(), new Disable()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineLoadStudentSubjectContent($tblDivisionCourse->getId()));
            } else {
                return new Warning('Keine Schüler gefunden', new Exclamation());
            }
        }

        return new Danger('Kurs oder Schuljahr nicht gefunden', new Exclamation());
    }
}