<?php

namespace SPHERE\Application\Education\Absence;

use DateTime;
use SPHERE\Application\Education\Absence\Service\Data;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsenceLesson;
use SPHERE\Application\Education\Absence\Service\Setup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function migrateYear(TblYear $tblYear): array
    {
        return (new Data($this->getBinding()))->migrateYear($tblYear);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByPerson(TblPerson $tblPerson, bool $isForced = false)
    {
        return (new Data($this->getBinding()))->getAbsenceAllByPerson($tblPerson, $isForced);
    }

    /**
     * @param $Id
     *
     * @return false|TblAbsence
     */
    public function getAbsenceById($Id)
    {
        return (new Data($this->getBinding()))->getAbsenceById($Id);
    }

    /**
     * @return false|TblAbsence[]
     */
    public function getAbsenceAll()
    {
        return (new Data($this->getBinding()))->getAbsenceAll();
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     *
     * @return TblAbsence[]|bool
     */
    public function getAbsenceAllBetween(DateTime $fromDate, DateTime $toDate)
    {
        return (new Data($this->getBinding()))->getAbsenceAllBetween($fromDate, $toDate);
    }

    /**
     * @param DateTime $fromDate
     * @param TblPerson $tblPerson
     * @param DateTime|null $toDate
     *
     * @return TblAbsence[]|bool
     */
    public function getAbsenceAllBetweenByPerson(TblPerson $tblPerson, DateTime $fromDate, DateTime $toDate = null)
    {
        return (new Data($this->getBinding()))->getAbsenceAllBetweenByPerson($tblPerson, $fromDate, $toDate);
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return false|TblAbsenceLesson[]
     */
    public function getAbsenceLessonAllByAbsence(TblAbsence $tblAbsence)
    {
        return (new Data($this->getBinding()))->getAbsenceLessonAllByAbsence($tblAbsence);
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return false|int[]
     */
    public function getLessonAllByAbsence(TblAbsence $tblAbsence)
    {
        $result = array();
        if (($list = $this->getAbsenceLessonAllByAbsence($tblAbsence))) {
            foreach ($list as $tblAbsenceLesson) {
                $result[] = $tblAbsenceLesson->getLesson();
            }
        }

        return  empty($result) ? false : $result;
    }

    /**
     * @param $Data
     * @param $Search
     * @param TblAbsence|null $tblAbsence
     * @param null $PersonId
     * @param null $DivisionCourseId
     * @param bool $hasSearch
     *
     * @return bool|Form
     */
    public function checkFormAbsence(
        $Data,
        $Search,
        TblAbsence $tblAbsence = null,
        $PersonId = null,
        $DivisionCourseId = null,
        bool $hasSearch = false
    ) {
        $error = false;
        $messageSearch = null;
        $messageLesson = null;

        $tblPerson = false;
        if ($PersonId) {
            $tblPerson = Person::useService()->getPersonById($PersonId);
        } elseif ($tblAbsence) {
            $tblPerson = $tblAbsence->getServiceTblPerson();
        } else {
            if(!isset($Data['PersonId']) || !($tblPerson = Person::useService()->getPersonById($Data['PersonId']))) {
                $messageSearch = new Danger('Bitte wählen Sie einen Schüler aus.', new Exclamation());
                $error = true;
            }
        }

        // Prüfung ob Unterrichtseinheiten ausgewählt wurden
        if (!isset($Data['IsFullDay']) && !isset($Data['UE'])) {
            $messageLesson = new Danger('Bitte wählen Sie mindestens eine Unterrichtseinheit aus.', new Exclamation());
            $error = true;
        }

        $form = Absence::useFrontend()->formAbsence(
            $tblAbsence ? $tblAbsence->getId() : null,
            $hasSearch,
            $Search,
            $Data,
            $tblPerson ? $tblPerson->getId() : null,
            $DivisionCourseId,
            $messageSearch,
            $messageLesson
        );

        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $form->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }

//        if ($Type) {
//            if(!isset($Data['PersonId']) || !($tblPerson = Person::useService()->getPersonById($Data['PersonId']))) {
//                $form->setError('Data[PersonId]', 'Bitte wählen Sie einen Schüler aus.');
//                $error = true;
//            }
//        }

        $fromDate = null;
        $toDate = null;
        if (isset($Data['FromDate']) && !empty($Data['FromDate'])) {
            $fromDate = new DateTime($Data['FromDate']);
        }
        if (isset($Data['ToDate']) && !empty($Data['ToDate'])) {
            $toDate = new DateTime($Data['ToDate']);
        }

        if ($fromDate && $toDate) {
            if ($toDate->format('Y-m-d') < $fromDate->format('Y-m-d')){
                $form->setError('Data[ToDate]', 'Das "Datum bis" darf nicht kleiner sein Datum als das "Datum von"');
                $error = true;
            }
        }

        // Prüfung ob in diesem Zeitraum bereits eine Fehlzeit existiert
        if (!$error && $tblPerson && $fromDate) {
            if (($resultList = (new Data($this->getBinding()))->getAbsenceAllBetweenByPerson($tblPerson, $fromDate, $toDate == $fromDate ? null : $toDate))) {
                foreach ($resultList as $item) {
                    // beim Bearbeiten der Fehlzeit, die zu bearbeitende Fehlzeit ignorieren
                    if ($tblAbsence && $tblAbsence->getId() == $item->getId()) {
                        continue;
                    }

                    $form->setError('Data[FromDate]', 'Es existiert bereits eine Fehlzeit im Bereich dieses Zeitraums');
                    $error = true;
                    break;
                }

            }
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblPerson|null $tblPerson
     *
     * @return bool
     */
    public function createAbsence($Data, TblPerson &$tblPerson = null): bool
    {
        if ($tblPerson == null) {
            $tblPerson = Person::useService()->getPersonById($Data['PersonId']);
        }
        $tblPersonStaff = Account::useService()->getPersonByLogin();

        if ($tblPerson) {
            if (($tblAbsence = (new Data($this->getBinding()))->createAbsence(
                $tblPerson,
                $Data['FromDate'],
                $Data['ToDate'],
                $Data['Status'],
                $Data['Remark'],
                $Data['Type'] ?? TblAbsence::VALUE_TYPE_NULL,
                isset($Data['IsCertificateRelevant']),
                // Ersteller
                $tblPersonStaff ?: null,
                // letzter Bearbeiter
                $tblPersonStaff ?: null
            ))) {
                if (isset($Data['UE'])) {
                    foreach ($Data['UE'] as $lesson => $value) {
                        (new Data($this->getBinding()))->addAbsenceLesson($tblAbsence, $lesson);
                    }
                }

                return  true;
            }
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param $Data
     *
     * @return bool
     */
    public function updateAbsenceService(TblAbsence $tblAbsence, $Data): bool
    {
        $tblPersonStaff = Account::useService()->getPersonByLogin();

        if ((new Data($this->getBinding()))->updateAbsence(
            $tblAbsence,
            $Data['FromDate'],
            $Data['ToDate'],
            $Data['Status'],
            $Data['Remark'],
            $Data['Type'] ?? TblAbsence::VALUE_TYPE_NULL,
            $tblPersonStaff ?: null,
            isset($Data['IsCertificateRelevant'])
        )) {
            for ($i = 0; $i < 13; $i++) {
                if (isset($Data['UE'][$i])) {
                    (new Data($this->getBinding()))->addAbsenceLesson($tblAbsence, $i);
                } else {
                    (new Data($this->getBinding()))->removeAbsenceLesson($tblAbsence, $i);
                }
            }

            return  true;
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyAbsence(TblAbsence $tblAbsence, bool $IsSoftRemove = false): bool
    {
        return (new Data($this->getBinding()))->destroyAbsence($tblAbsence, $IsSoftRemove);
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return bool
     */
    public function restoreAbsence(TblAbsence $tblAbsence): bool
    {
        return (new Data($this->getBinding()))->restoreAbsence($tblAbsence);
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime|null $toDate
     * @param TblType|null $tblSchoolType
     * @param array $tblDivisionCourseList
     * @param bool $hasAbsenceTypeOptions
     * @param bool|null $IsCertificateRelevant
     * @param bool $IsOnlineAbsenceOnly
     *
     * @return array
     */
    public function getAbsenceAllByDay(
        DateTime $fromDate,
        DateTime $toDate = null,
        TblType $tblSchoolType = null,
        array $tblDivisionCourseList = array(),
        bool &$hasAbsenceTypeOptions = false,
        ?bool $IsCertificateRelevant = true,
        bool $IsOnlineAbsenceOnly = false
    ): array {
        $resultList = array();
        $tblAbsenceList = array();

        if ($toDate == null) {
            $toDate = $fromDate;
        }

        if (!empty($tblDivisionCourseList)) {
            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                    foreach ($tblPersonList as $tblPersonItem) {
                        if (($tblAbsencePersonList = $this->getAbsenceAllBetweenByPerson($tblPersonItem, $fromDate, $toDate))) {
                            $tblAbsenceList = array_merge($tblAbsenceList, $tblAbsencePersonList);
                        }
                    }
                }
            }
        } else {
            $tblAbsenceList = $this->getAbsenceAllBetween($fromDate, $toDate);
        }

        if ($tblAbsenceList) {
            foreach ($tblAbsenceList as $tblAbsence) {
                if (($tblPerson = $tblAbsence->getServiceTblPerson())) {
                    // Zeugnisrelevant filtern
                    if ($IsCertificateRelevant !== null && $IsCertificateRelevant !== $tblAbsence->getIsCertificateRelevant()) {
                        continue;
                    }

                    // Nur Online Fehlzeiten filtern
                    if ($IsOnlineAbsenceOnly && !$tblAbsence->getIsOnlineAbsence()) {
                        continue;
                    }

                    $tblSchoolTypePerson = false;
                    $tblDivisionCoursePerson = false;
                    if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson, $tblAbsence->getFromDate()))) {
                        $tblSchoolTypePerson = $tblStudentEducation->getServiceTblSchoolType();
                        if ($tblStudentEducation->getTblDivision()) {
                            $tblDivisionCoursePerson = $tblStudentEducation->getTblDivision();
                        } elseif ($tblStudentEducation->getTblCoreGroup()) {
                            $tblDivisionCoursePerson = $tblStudentEducation->getTblCoreGroup();
                        }
                    }

                    if (!$tblSchoolType || ($tblSchoolTypePerson && $tblSchoolType->getId() == $tblSchoolTypePerson->getId())) {
                        $resultList = $this->setAbsenceContent($tblSchoolTypePerson, $tblDivisionCoursePerson ?: null, $tblPerson, $tblAbsence, $resultList);
                    }

                    if (!$hasAbsenceTypeOptions && $tblSchoolTypePerson && $tblSchoolTypePerson->isTechnical()) {
                        $hasAbsenceTypeOptions = true;
                    }
                }
            }
        }

        // Liste sortieren
        if (!empty($resultList)) {
            $type = $division = $group = $person = array();
            foreach ($resultList as $key => $row) {
                $type[$key] = strtoupper($row['Type']);
                $division[$key] = strtoupper($row['Division']);
                $person[$key] = strtoupper($row['Person']);
                $date[$key] = $row['DateSort'];
            }

            array_multisort($type, SORT_ASC, $division, SORT_NATURAL, $person, SORT_ASC, $date, SORT_ASC, $resultList);
        }

        return $resultList;
    }

    /**
     * @param TblType $tblSchoolType
     * @param ?TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param TblAbsence $tblAbsence
     * @param array $resultList
     *
     * @return array
     */
    public function setAbsenceContent(
        TblType $tblSchoolType,
        ?TblDivisionCourse $tblDivisionCourse,
        TblPerson $tblPerson,
        TblAbsence $tblAbsence,
        array $resultList
    ): array {

        $isOnlineAbsence = $tblAbsence->getIsOnlineAbsence();

        $resultList[] = array(
            'AbsenceId' => $tblAbsence->getId(),
            'Type' => $tblSchoolType->getName(),
            'TypeExcel' => $tblSchoolType->getShortName(),
            'Division' => $tblDivisionCourse->getName(),
            'Person' => $tblPerson->getLastFirstNameWithCallNameUnderline(),
            'PersonExcel' => $tblPerson->getLastFirstName(),
            'DateSpan' => $tblAbsence->getDateSpan(),
            'DateSort' => $tblAbsence->getFromDate('Y.m.d'),
            'DateFrom' => ($isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getFromDate() . '</span>' : $tblAbsence->getFromDate()),
            'DateTo' => ($isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getToDate() . '</span>' : $tblAbsence->getToDate()),
            'PersonCreator' => $tblAbsence->getDisplayPersonCreator(false),
            'Status' => $tblAbsence->getStatusDisplayName(),
            'StatusExcel' => $tblAbsence->getStatusDisplayShortName(),
            'Remark' => $tblAbsence->getRemark(),
            'AbsenceType' => $tblAbsence->getTypeDisplayName(),
            'AbsenceTypeExcel' => $tblAbsence->getTypeDisplayShortName(),
            'Lessons' => $tblAbsence->getLessonStringByAbsence(),
            'IsCertificateRelevant' => $tblAbsence->getIsCertificateRelevant() ? 'ja' : 'nein'
        );

        return $resultList;
    }
}