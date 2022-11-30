<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\VirtualSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceStudentSubject extends AbstractService
{
    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param bool|null $hasGrading
     *
     * @return false|TblStudentSubject[]
     */
    public function getStudentSubjectListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear, ?bool $hasGrading = null)
    {
        return (new Data($this->getBinding()))->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear, $hasGrading);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return false|TblStudentSubject
     */
    public function getStudentSubjectByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getStudentSubjectByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
    }

    /**
     * prüft ob der Schüler das Fach hat oder ob er es virtuell über Stundentafel-Schülerakte hat
     *
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return false|VirtualSubject
     */
    public function getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        // gespeichertes Fach - StudentSubject
        if (($tblStudentSubject = (new Data($this->getBinding()))->getStudentSubjectByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
            return new VirtualSubject($tblSubject, $tblStudentSubject->getHasGrading(), null);
        }

        // Stundentafel
        if (($tblSubjectTable = DivisionCourse::useService()->getSubjectTableByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
            return new VirtualSubject($tblSubject, $tblSubjectTable->getHasGrading(), $tblSubjectTable);
        }

        // Stundentafel - Schülerakte
        if (($tblVirtualSubjectList = $this->getVirtualSubjectListFromStudentMetaIdentifierListByPersonAndYear($tblPerson, $tblYear))) {
            return isset($tblVirtualSubjectList[$tblSubject->getId()]) ? $tblVirtualSubjectList[$tblSubject->getId()] : false;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubjectTable $tblSubjectTable
     *
     * @return false|TblStudentSubject
     */
    public function getStudentSubjectByPersonAndYearAndSubjectTable(TblPerson $tblPerson, TblYear $tblYear, TblSubjectTable $tblSubjectTable)
    {
        return (new Data($this->getBinding()))->getStudentSubjectByPersonAndYearAndSubjectTable($tblPerson, $tblYear, $tblSubjectTable);
    }

    /**
     * SekII
     *
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivisionCourse $tblSubjectDivisionCourse
     *
     * @return false|TblStudentSubject[]
     */
    private function getStudentSubjectListByPersonAndYearAndDivisionCourse(TblPerson $tblPerson, TblYear $tblYear, TblDivisionCourse $tblSubjectDivisionCourse)
    {
        return (new Data($this->getBinding()))->getStudentSubjectListByPersonAndYearAndDivisionCourse($tblPerson, $tblYear, $tblSubjectDivisionCourse);
    }

    /**
     * SekII
     *
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivisionCourse $tblSubjectDivisionCourse
     * @param $Period
     * @return TblStudentSubject|void
     */
    public function getStudentSubjectByPersonAndYearAndDivisionCourseAndPeriod(TblPerson $tblPerson, TblYear $tblYear, TblDivisionCourse $tblSubjectDivisionCourse, $Period)
    {
        if (($tblStudentSubjectList = $this->getStudentSubjectListByPersonAndYearAndDivisionCourse($tblPerson, $tblYear, $tblSubjectDivisionCourse))) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($list = explode('/', $tblStudentSubject->getPeriodIdentifier()))
                    && isset($list[1])
                    && $Period  == $list[1]
                ) {
                    return $tblStudentSubject;
                }
            }
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return TblStudentSubject[]|false
     */
    private function getStudentSubjectListByDivisionCourseAndSubject(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        $tblStudentSubjectList = array();
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentList = DivisionCourse::useService()->getStudentListBy($tblDivisionCourse))
        ) {
            foreach ($tblStudentList as $tblPerson) {
                if (($tblStudentSubject = $this->getStudentSubjectByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                    $tblStudentSubjectList[$tblPerson->getId()] = $tblStudentSubject;
                }
            }
        }

        return empty($tblStudentSubjectList) ? false : $tblStudentSubjectList;
    }

    /**
     * SekII
     *
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblSubjectDivisionCourse
     * @param $Period
     *
     * @return array|false
     */
    private function getStudentSubjectListByDivisionCourseAndSubjectDivisionCourseAndPeriod(TblDivisionCourse $tblDivisionCourse,
        TblDivisionCourse $tblSubjectDivisionCourse, $Period)
    {
        $tblStudentSubjectList = array();
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentList = DivisionCourse::useService()->getStudentListBy($tblDivisionCourse))
        ) {
            foreach ($tblStudentList as $tblPerson) {
                if (($tblStudentSubject = $this->getStudentSubjectByPersonAndYearAndDivisionCourseAndPeriod($tblPerson, $tblYear, $tblSubjectDivisionCourse, $Period))) {
                    $tblStudentSubjectList[$tblPerson->getId()] = $tblStudentSubject;
                }
            }
        }

        return empty($tblStudentSubjectList) ? false : $tblStudentSubjectList;
    }

    /**
     * @param TblDivisionCourse $tblSubjectDivisionCourse
     *
     * @return false|TblStudentSubject[]
     */
    public function getStudentSubjectListBySubjectDivisionCourse(TblDivisionCourse $tblSubjectDivisionCourse)
    {
        return (new Data($this->getBinding()))->getStudentSubjectListBySubjectDivisionCourse($tblSubjectDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblSubjectDivisionCourse
     * @param $Period
     *
     * @return false|TblStudentSubject[]
     */
    public function getStudentSubjectListBySubjectDivisionCourseAndPeriod(TblDivisionCourse $tblSubjectDivisionCourse, $Period)
    {
        return (new Data($this->getBinding()))->getStudentSubjectListBySubjectDivisionCourseAndPeriod($tblSubjectDivisionCourse, $Period);
    }

    /**
     * @param TblDivisionCourse $tblSubjectDivisionCourse
     * @param int $Period
     *
     * @return int
     */
    public function getCountStudentsBySubjectDivisionCourseAndPeriod(TblDivisionCourse $tblSubjectDivisionCourse, int $Period): int
    {
        if (($tblStudentSubjectList = $this->getStudentSubjectListBySubjectDivisionCourseAndPeriod($tblSubjectDivisionCourse, $Period))) {
            return count ($tblStudentSubjectList);
        }

        return 0;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param int $Period
     *
     * @return TblStudentSubject[]|false
     */
    public function getStudentSubjectListByStudentDivisionCourseAndPeriod(TblDivisionCourse $tblDivisionCourse, int $Period)
    {
        $resultList = array();
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
            $this->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblDivisionCourseList);

            foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                if (($tblStudentMemberList = $this->getDivisionCourseMemberListBy($tblDivisionCourseItem, TblDivisionCourseMemberType::TYPE_STUDENT))) {
                    foreach ($tblStudentMemberList as $tblPerson) {
                        if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear))) {
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if (($list = explode('/', $tblStudentSubject->getPeriodIdentifier()))
                                    && isset($list[1])
                                    && $list[1] == $Period
                                ) {
                                    $resultList[] = $tblStudentSubject;
                                }
                            }
                        }
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * SekI
     *
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Data
     *
     * @return bool
     */
    public function createStudentSubjectList(TblDivisionCourse $tblDivisionCourse, $Data): bool
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblSubject = Subject::useService()->getSubjectById($Data['Subject']))
        ) {
            $hasGrading = isset($Data['HasGrading']);

            $createList = array();
            $updateList = array();
            $destroyList = array();

            if (($tblStudentSubjectList = $this->getStudentSubjectListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject))) {
                foreach ($tblStudentSubjectList as $personId => $tblStudentSubject) {
                    // löschen
                    if (!isset($Data['StudentList'][$personId])) {
                        $destroyList[] = $tblStudentSubject;
                    // update
                    } elseif ($tblStudentSubject->getHasGrading() != $hasGrading) {
                        $tblStudentSubject->setHasGrading($hasGrading);
                        $updateList[] = $tblStudentSubject;
                    }
                }
            }

            // neu
            if (isset($Data['StudentList'])) {
                foreach ($Data['StudentList'] as $personId => $value) {
                    if (($tblPerson = Person::useService()->getPersonById($personId))) {
                        if (($tblStudentSubjectList && !isset($tblStudentSubjectList[$personId]))
                            || !$tblStudentSubjectList
                        ) {
                            // Fach der Schülerakte beim Speichern berücksichtigen
                            $tblSubjectTable = false;
                            if (($virtualSubjectListFromStudentMeta = DivisionCourse::useService()->getVirtualSubjectListFromStudentMetaIdentifierListByPersonAndYear($tblPerson, $tblYear))
                                && (isset($virtualSubjectListFromStudentMeta[$tblSubject->getId()]))
                                && ($virtualSubject = $virtualSubjectListFromStudentMeta[$tblSubject->getId()])
                            ) {
                                $tblSubjectTable = $virtualSubject->getTblSubjectTable();
                            }

                            $createList[] = TblStudentSubject::withParameter($tblPerson, $tblYear, $tblSubject, $hasGrading, $tblSubjectTable ?: null);
                        }
                    }
                }
            }

            if (!empty($createList)) {
                (new Data($this->getBinding()))->createStudentSubjectBulkList($createList);
            }
            if (!empty($updateList)) {
                (new Data($this->getBinding()))->updateStudentSubjectBulkList($updateList);
            }
            if (!empty($destroyList)) {
                (new Data($this->getBinding()))->destroyStudentSubjectBulkList($destroyList);
            }

            return true;
        }

        return false;
    }

    /**
     * SekII
     *
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Period
     * @param $Data
     *
     * @return bool
     */
    public function createStudentSubjectDivisionCourseList(TblDivisionCourse $tblDivisionCourse, $Period, $Data): bool
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblSubjectDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($Data['SubjectDivisionCourse']))
        ) {
            // SekII-Kurse habe immer eine Benotung
            $hasGrading = true;

            $createList = array();
            $updateList = array();
            $destroyList = array();

            if (($tblStudentSubjectList = $this->getStudentSubjectListByDivisionCourseAndSubjectDivisionCourseAndPeriod($tblDivisionCourse, $tblSubjectDivisionCourse, $Period))) {
                foreach ($tblStudentSubjectList as $personId => $tblStudentSubject) {
                    // löschen
                    if (!isset($Data['StudentList'][$personId])) {
                        $destroyList[] = $tblStudentSubject;
                        // update
                    } elseif ($tblStudentSubject->getHasGrading() != $hasGrading) {
                        $tblStudentSubject->setHasGrading($hasGrading);
                        $updateList[] = $tblStudentSubject;
                    }
                }
            }

            // neu
            if (isset($Data['StudentList'])) {
                foreach ($Data['StudentList'] as $personId => $value) {
                    if (($tblPerson = Person::useService()->getPersonById($personId))
                        && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                        && ($level = $tblStudentEducation->getLevel())
                    ) {
                        if (($tblStudentSubjectList && !isset($tblStudentSubjectList[$personId]))
                            || !$tblStudentSubjectList
                        ) {
                            $createList[] = TblStudentSubject::withParameter($tblPerson, $tblYear, null, $hasGrading, null, $tblSubjectDivisionCourse,
                                $level . '/' . $Period);
                        }
                    }
                }
            }

            if (!empty($createList)) {
                (new Data($this->getBinding()))->createStudentSubjectBulkList($createList);
            }
            if (!empty($updateList)) {
                (new Data($this->getBinding()))->updateStudentSubjectBulkList($updateList);
            }
            if (!empty($destroyList)) {
                (new Data($this->getBinding()))->destroyStudentSubjectBulkList($destroyList);
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblSubjectTable $tblSubjectTable
     * @param TblPerson $tblPerson
     *
     * @return false|TblSubject
     */
    public function getSubjectFromStudentMetaIdentifier(TblSubjectTable $tblSubjectTable, TblPerson $tblPerson)
    {
        $tblSubject = $tblSubjectTable->getServiceTblSubject();
        // Spezialfall: Fremdsprache
        if (strpos($tblSubjectTable->getStudentMetaIdentifier(), 'FOREIGN_LANGUAGE_') !== false) {
            $identifier = 'FOREIGN_LANGUAGE';
            $ranking = substr($tblSubjectTable->getStudentMetaIdentifier(), strlen('FOREIGN_LANGUAGE_'));
            $maxRanking = $ranking;
        // Spezialfall: Wahlfach (kann in 1 bis 5 stehen)
        } elseif (strpos($tblSubjectTable->getStudentMetaIdentifier(), 'ELECTIVE') !== false) {
            $identifier = $tblSubjectTable->getStudentMetaIdentifier();
            $ranking = '1';
            $maxRanking = '5';
        } else {
            $identifier = $tblSubjectTable->getStudentMetaIdentifier();
            $ranking = '1';
            $maxRanking = $ranking;
        }

        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier($identifier))
        ) {
            for ($i = intval($ranking); $i <= intval($maxRanking); $i++) {
                if (($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i))
                    && ($tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                        $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking))
                    && ($tblSubjectFromMeta = $tblStudentSubject->getServiceTblSubject())
                ) {
                    if (!$tblSubject || ($tblSubject->getId() == $tblSubjectFromMeta->getId())) {
                        return $tblSubjectFromMeta;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return VirtualSubject[]|false
     */
    public function getVirtualSubjectListFromStudentMetaIdentifierListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($level = $tblStudentEducation->getLevel())
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
        ) {
            return $this->getVirtualSubjectListFromStudentMetaIdentifierListByPersonAndSchoolTypeAndLevel($tblPerson, $tblSchoolType, $level);
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType $tblSchoolType
     * @param int $level
     *
     * @return VirtualSubject[]|false
     */
    public function getVirtualSubjectListFromStudentMetaIdentifierListByPersonAndSchoolTypeAndLevel(TblPerson $tblPerson, TblType $tblSchoolType, int $level)
    {
        $tblSubjectList = array();
        if (($tblSubjectTableList = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType, $level))) {
            foreach ($tblSubjectTableList as $tblSubjectTable) {
                if ($tblSubjectTable->getStudentMetaIdentifier() && ($tblSubject = DivisionCourse::useService()->getSubjectFromStudentMetaIdentifier($tblSubjectTable, $tblPerson))) {
                    $tblSubjectList[$tblSubject->getId()] = new VirtualSubject($tblSubject, $tblSubjectTable->getHasGrading(), $tblSubjectTable);
                }
            }
        }

        return empty($tblSubjectList) ? false : $tblSubjectList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function copySubjectDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
            DivisionCourse::useService()->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblDivisionCourseList);

            $createList = array();
            foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                if (($tempList = $this->getStudentSubjectListByStudentDivisionCourseAndPeriod($tblDivisionCourseItem, 1))) {
                    foreach ($tempList as $tblStudentSubject) {
                        if (($tblPerson = $tblStudentSubject->getServiceTblPerson())
                            && ($tblSubjectDivisionCourse = $tblStudentSubject->getTblDivisionCourse())
                            && !$this->getStudentSubjectByPersonAndYearAndDivisionCourseAndPeriod($tblPerson, $tblYear, $tblSubjectDivisionCourse, 2)
                            && ($list = explode('/', $tblStudentSubject->getPeriodIdentifier()))
                            && isset($list[0])
                        ) {
                            $createList[] = TblStudentSubject::withParameter($tblPerson, $tblYear, null, $tblStudentSubject->getHasGrading(),
                                null, $tblSubjectDivisionCourse, $list[0] . '/' . 2);
                        }
                    }
                }
            }
        }


        if (!empty($createList)) {
            return (new Data($this->getBinding()))->createStudentSubjectBulkList($createList);
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $hasGrading
     *
     * @return array|false
     */
    public function getSubjectListByDivisionCourse(TblDivisionCourse $tblDivisionCourse, bool $hasGrading = true)
    {
        $tblSubjectList = array();
        // Fach hängt direkt am Kurs (SekII-Kurse, Lerngruppen)
        if (($tblSubject = $tblDivisionCourse->getServiceTblSubject())) {
            $tblSubjectList[$tblSubject->getId()] = $tblSubject;
        } elseif (($tblPersonList = $tblDivisionCourse->getStudents())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $schoolTypeLevelList = array();
            $tblSubjectTableListNotFixed = array();
            foreach ($tblPersonList as $tblPerson) {
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    && ($level = $tblStudentEducation->getLevel())
                ) {
                    // Stundentafel
                    if (!isset($schoolTypeLevelList[$tblSchoolType->getId()][$level])) {
                        $schoolTypeLevelList[$tblSchoolType->getId()][$level] = 1;
                        if (($tblSubjectTableList = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType, $level))) {
                            foreach ($tblSubjectTableList as $tblSubjectTable) {
                                // Benotung
                                if ($hasGrading && !$tblSubjectTable->getHasGrading()) {
                                    continue;
                                }

                                // feste Fächer der Stundentafel
                                if ($tblSubjectTable->getIsFixed()) {
                                    if (($tblSubject = $tblSubjectTable->getServiceTblSubject()) && !isset($tblSubjectList[$tblSubject->getId()])) {
                                        $tblSubjectList[$tblSubject->getId()] = $tblSubject;
                                    }
                                // Virtuelle Fächer der Schülerakte
                                } else {
                                    if ($tblSubjectTable->getStudentMetaIdentifier() && !isset($tblSubjectTableListNotFixed[$tblSubjectTable->getId()])) {
                                        $tblSubjectTableListNotFixed[$tblSubjectTable->getId()] = $tblSubjectTable;
                                    }
                                }
                            }
                        }
                    }

                    // feste Fächer am Schüler
                    if (($tblSubjectStudentList = $this->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear, $hasGrading ?: null))) {
                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                            if (($tblSubject = $tblSubjectStudent->getServiceTblSubject()) && !isset($tblSubjectList[$tblSubject->getId()])) {
                                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
                            }
                        }
                    }
                }
            }

            if (!empty($tblSubjectTableListNotFixed)) {
                 foreach ($tblSubjectTableListNotFixed as $tblSubjectTable)
                 {
                     foreach ($tblPersonList as $tblPerson) {
                         if (($tblSubject = DivisionCourse::useService()->getSubjectFromStudentMetaIdentifier($tblSubjectTable, $tblPerson))
                             && !isset($tblSubjectList[$tblSubject->getId()])
                         ) {
                             $tblSubjectList[$tblSubject->getId()] = $tblSubject;
                         }
                     }
                 }
            }
        }

        return empty($tblSubjectList) ? false : $tblSubjectList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param bool $hasGrading
     *
     * @return TblSubject[]|false
     */
    public function getSubjectListByStudentAndYear(TblPerson $tblPerson, TblYear $tblYear, bool $hasGrading = true)
    {
        $tblSubjectList = array();
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel())
        ) {
            if (($tblSubjectTableList = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType, $level))) {
                foreach ($tblSubjectTableList as $tblSubjectTable) {
                    // Benotung
                    if ($hasGrading && !$tblSubjectTable->getHasGrading()) {
                        continue;
                    }

                    // feste Fächer der Stundentafel
                    if ($tblSubjectTable->getIsFixed()) {
                        if (($tblSubject = $tblSubjectTable->getServiceTblSubject()) && !isset($tblSubjectList[$tblSubject->getId()])) {
                            $tblSubjectList[$tblSubject->getId()] = $tblSubject;
                        }
                        // Virtuelle Fächer der Schülerakte
                    } else {
                        if ($tblSubjectTable->getStudentMetaIdentifier()
                            && ($tblSubject = DivisionCourse::useService()->getSubjectFromStudentMetaIdentifier($tblSubjectTable, $tblPerson))
                            && !isset($tblSubjectList[$tblSubject->getId()])
                        ) {
                            $tblSubjectList[$tblSubject->getId()] = $tblSubject;
                        }
                    }
                }
            }

            // feste Fächer am Schüler
            if (($tblSubjectStudentList = $this->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear, $hasGrading ?: null))) {
                foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                    if (($tblSubject = $tblSubjectStudent->getServiceTblSubject()) && !isset($tblSubjectList[$tblSubject->getId()])) {
                        $tblSubjectList[$tblSubject->getId()] = $tblSubject;
                    }
                }
            }
        }

        return empty($tblSubjectList) ? false : $tblSubjectList;
    }
}