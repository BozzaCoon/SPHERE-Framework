<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use DateTime;
use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Graduation\Grade\Service\Setup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param $doSimulation
     * @param $withData
     * @param $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol = '';
        if (!$withData) {
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblYear $tblYear
     * @param array $tblDivisionList
     *
     * @return float
     */
    public function migrateTests(TblYear $tblYear, array $tblDivisionList): float
    {
        return (new Data($this->getBinding()))->migrateTests($tblYear, $tblDivisionList);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return float
     */
    public function migrateTasks(TblYear $tblYear): float
    {
        return (new Data($this->getBinding()))->migrateTasks($tblYear);
    }

    /**
     * @param $id
     *
     * @return false|TblGradeType
     */
    public function getGradeTypeById($id)
    {
        return (new Data($this->getBinding()))->getGradeTypeById($id);
    }

    /**
     * @param bool $withInActive
     *
     * @return false|TblGradeType[]
     */
    public function getGradeTypeAll(bool $withInActive = false)
    {
        return (new Data($this->getBinding()))->getGradeTypeAll($withInActive);
    }

    /**
     * @param bool $isTypeBehavior
     *
     * @return false|TblGradeType[]
     */
    public function getGradeTypeList(bool $isTypeBehavior = false)
    {
        return (new Data($this->getBinding()))->getGradeTypeList($isTypeBehavior);
    }

    /**
     * @param $id
     *
     * @return false|TblGradeText
     */
    public function getGradeTextById($id)
    {
        return (new Data($this->getBinding()))->getGradeTextById($id);
    }

    /**
     * @return false|TblGradeText[]
     */
    public function getGradeTextAll()
    {
        return (new Data($this->getBinding()))->getGradeTextAll();
    }

    /**
     * @param $id
     *
     * @return false|TblTest
     */
    public function getTestById($id)
    {
        return (new Data($this->getBinding()))->getTestById($id);
    }

    /**
     * @param TblTest $tblTest
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListByTest(TblTest $tblTest)
    {
        return (new Data($this->getBinding()))->getDivisionCourseListByTest($tblTest);
    }

    /**
     * @param TblTest $tblTest
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblTestCourseLink
     */
    public function getTestCourseLinkBy(TblTest $tblTest, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getTestCourseLinkBy($tblTest, $tblDivisionCourse);
    }

    /**
     * @param $id
     *
     * @return false|TblScoreType
     */
    public function getScoreTypeById($id)
    {
        return (new Data($this->getBinding()))->getScoreTypeById($id);
    }

    /**
     * @return false|TblScoreType[]
     */
    public function getScoreTypeAll()
    {
        return (new Data($this->getBinding()))->getScoreTypeAll();
    }

    /**
     * @param $id
     *
     * @return false|TblTask
     */
    public function getTaskById($id)
    {
        return (new Data($this->getBinding()))->getTaskById($id);
    }

    /**
     * @return false|TblYear
     */
    public function getYear()
    {
        if (($tblAccountSetting = Consumer::useService()->getAccountSettingValue("GradeBookSelectedYearId"))
            && ($tblYear = Term::useService()->getYearById($tblAccountSetting))
        ) {
            return $tblYear;
        }

        if (($tblYearList = Term::useService()->getYearByNow())) {
            return current($tblYearList);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        if (($role = Consumer::useService()->getAccountSettingValue("GradeBookRole"))) {
            // zur Sicherheit prüfen, ob das erforderliche Recht noch vorhanden ist
            if ($role == "Headmaster" && Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/Headmaster')) {
                return $role;
            }
            // zur Sicherheit prüfen, ob das erforderliche Recht noch vorhanden ist
            if ($role == "AllReadonly" && Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/AllReadOnly')) {
                return $role;
            }
        }

        return "Teacher";
    }

    /**
     * @param array $columnList
     * @param int $size
     *
     * @return array
     */
    public function getLayoutRowsByLayoutColumnList(array $columnList, int $size): array
    {
        $rowList = array();
        $rowCount = 0;
        $row = null;
        foreach ($columnList as $column) {
            if ($rowCount % (12 / $size) == 0) {
                $row = new LayoutRow(array());
                $rowList[] = $row;
            }
            $row->addColumn($column);
            $rowCount++;
        }

        return $rowList;
    }

    /**
     * @param $Data
     * @param TblDivisionCourse|null $tblDivisionCourse
     *
     * @return false|Form
     */
    public function checkFormTeacherGroup($Data, TblDivisionCourse $tblDivisionCourse = null)
    {
        $error = false;
        $form = Grade::useFrontend()->formTeacherGroup($tblDivisionCourse ? $tblDivisionCourse->getId() : null, false, $Data);

        $tblYear = $tblDivisionCourse ? $tblDivisionCourse->getServiceTblYear() : $this->getYear();

        if (!$tblDivisionCourse) {
            if (!isset($Data['Subject']) || !(Subject::useService()->getSubjectById($Data['Subject']))) {
                $form->setError('Data[Subject]', 'Bitte wählen Sie ein Fach aus');
                $error = true;
            }
        }

        if (!isset($Data['Name']) || empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Name ein');
            $error = true;
        }
        if (isset($Data['Name']) && $Data['Name'] != '') {
            // Prüfung ob name schon mal verwendet wird
            if ($tblYear && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                    if ($tblDivisionCourse && $tblDivisionCourse->getId() == $tblDivisionCourseItem->getId()) {
                        continue;
                    }

                    if (strtolower($Data['Name']) == strtolower($tblDivisionCourseItem->getName())) {
                        $form->setError('Data[Name]', 'Ein Kurs mit diesem Name existiert bereits im Schuljahr');
                        $error = true;
                    }
                }
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     *
     * @return false|Form
     */
    public function checkFormTest($Data, $DivisionCourseId, $SubjectId, $Filter, $TestId)
    {
        $error = false;
        $form = Grade::useFrontend()->formTest($DivisionCourseId, $SubjectId, $Filter, $TestId, false, $Data);

        if (!isset($Data['GradeType']) || !(Grade::useService()->getGradeTypeById($Data['GradeType']))) {
            $form->setError('Data[GradeType]', 'Bitte wählen Sie einen Zensuren-Typ aus');
            $error = true;
        }
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }

        return $error ? $form : false;
    }

    /**
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @param DateTime|null $Date
     * @param DateTime|null $FinishDate
     * @param DateTime|null $CorrectionDate
     * @param DateTime|null $ReturnDate
     * @param bool $IsContinues
     * @param string $Description
     *
     * @return TblTest
     */
    public function createTest(TblYear $tblYear, TblSubject $tblSubject, TblGradeType $tblGradeType,
        ?DateTime $Date, ?DateTime $FinishDate, ?DateTime $CorrectionDate, ?DateTime $ReturnDate, bool $IsContinues, string $Description): TblTest
    {
        return (new Data($this->getBinding()))->createTest($tblYear, $tblSubject, $tblGradeType, $Date, $FinishDate, $CorrectionDate, $ReturnDate, $IsContinues,
            $Description);
    }

    /**
     * @param TblTest $tblTest
     * @param TblGradeType $tblGradeType
     * @param DateTime|null $Date
     * @param DateTime|null $FinishDate
     * @param DateTime|null $CorrectionDate
     * @param DateTime|null $ReturnDate
     * @param bool $IsContinues
     * @param string $Description
     *
     * @return bool
     */
    public function updateTest(TblTest $tblTest, TblGradeType $tblGradeType,
        ?DateTime $Date, ?DateTime $FinishDate, ?DateTime $CorrectionDate, ?DateTime $ReturnDate, bool $IsContinues, string $Description): bool
    {
        return (new Data($this->getBinding()))->updateTest($tblTest, $tblGradeType, $Date, $FinishDate, $CorrectionDate, $ReturnDate, $IsContinues, $Description);
    }

    /**
     * @param array $tblTestCourseLinkList
     *
     * @return bool
     */
    public function createTestCourseLinkBulk(array $tblTestCourseLinkList): bool
    {
        return (new Data($this->getBinding()))->createTestCourseLinkBulk($tblTestCourseLinkList);
    }

    /**
     * @param array $tblTestCourseLinkList
     *
     * @return bool
     */
    public function removeTestCourseLinkBulk(array $tblTestCourseLinkList): bool
    {
        return (new Data($this->getBinding()))->removeTestCourseLinkBulk($tblTestCourseLinkList);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return TblTest[]|false
     */
    public function getTestListByDivisionCourseAndSubject(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getTestListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return TblTestGrade[]|false
     */
    public function getTestGradeListByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
    }
}