<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.12.2015
 * Time: 09:41
 */

namespace SPHERE\Application\Education\Graduation\Evaluation;

use SPHERE\Application\Education\Graduation\Evaluation\Service\Data;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Setup;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Education\Graduation\Evaluation
 */
class Service extends AbstractService
{
    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblTestType
     */
    public function getTestTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTestTypeById($Id);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblTestType
     */
    public function getTestTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getTestTypeByIdentifier($Identifier);
    }

    /**
     * @param $Id
     * @return bool|TblTest
     */
    public function getTestById($Id)
    {

        return (new Data($this->getBinding()))->getTestById($Id);
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param null|TblPeriod $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @return bool|TblTest[]
     */
    public function getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
        TblTestType $tblTestType,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblTestType, $tblDivision, $tblSubject, $tblPeriod, $tblSubjectGroup
        );
    }

    /**
     * @param TblTestType $tblTestType
     * @return bool|TblTest[]
     */
    public function getTestAllByTestType(TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getTestAllByTestType($tblTestType);
    }

    /**
     * @param $Id
     * @return bool|TblTask
     */
    public function getTaskById($Id)
    {

        return (new Data($this->getBinding()))->getTaskById($Id);
    }

    /**
     * @param TblTestType $tblTestType
     * @return bool|TblTask[]
     */
    public function getTaskAllByTestType(TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getTaskAllByTestType($tblTestType);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $DivisionSubjectId
     * @param null $Test
     * @param string $BasicRoute
     * @return IFormInterface|string
     */
    public function createTest(IFormInterface $Stage = null, $DivisionSubjectId = null, $Test = null, $BasicRoute)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Test || $DivisionSubjectId === null) {
            return $Stage;
        }

        $Error = false;
        if (!isset($Test['Period'])) {
            $Error = true;
            $Stage .= new Warning('Zeitraum nicht gefunden');
        }
        if (!isset($Test['GradeType'])) {
            $Error = true;
            $Stage .= new Warning('Zensuren-Typ nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);

        (new Data($this->getBinding()))->createTest(
            $tblDivisionSubject->getTblDivision(),
            $tblDivisionSubject->getServiceTblSubject(),
            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
            Term::useService()->getPeriodById($Test['Period']),
            Gradebook::useService()->getGradeTypeById($Test['GradeType']),
            $this->getTestTypeByIdentifier('TEST'),
            $Test['Description'],
            $Test['Date'],
            $Test['CorrectionDate'],
            $Test['ReturnDate']
        );

        return new Stage('Der Test ist erfasst worden')
        . new Redirect($BasicRoute . '/Selected', 0,
            array('DivisionSubjectId' => $tblDivisionSubject->getId()));

    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $Test
     * @param string $BasicRoute
     * @return IFormInterface|Redirect
     */
    public function updateTest(IFormInterface $Stage = null, $Id, $Test, $BasicRoute)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Test) {
            return $Stage;
        }

        $tblTest = $this->getTestById($Id);
        (new Data($this->getBinding()))->updateTest(
            $tblTest,
            $Test['Description'],
            $Test['Date'],
            $Test['CorrectionDate'],
            $Test['ReturnDate']
        );

        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblTest->getServiceTblDivision(),
            $tblTest->getServiceTblSubject(),
            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
        );

        return new Redirect($BasicRoute . '/Selected', 0,
            array('DivisionSubjectId' => $tblDivisionSubject->getId()));
    }


    /**
     * @param IFormInterface|null $Stage
     * @param $Task
     * @return IFormInterface|string
     */
    public function createTask(IFormInterface $Stage = null, $Task)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Task) {
            return $Stage;
        }

        $Error = false;
        if (isset($Task['Name']) && empty($Task['Name'])) {
            $Stage->setError('Task[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($Task['Date']) && empty($Task['Date'])) {
            $Stage->setError('Task[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['FromDate']) && empty($Task['FromDate'])) {
            $Stage->setError('Task[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['ToDate']) && empty($Task['ToDate'])) {
            $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }

        if (!$Error) {
            $tblTestType = $this->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
            (new Data($this->getBinding()))->createTask(
                $tblTestType, $Task['Name'], $Task['Date'], $Task['FromDate'],
                $Task['ToDate']
            );
            $Stage .= new Success('Erfolgreich angelegt')
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate', 0);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $Task
     * @return IFormInterface|Redirect
     */
    public function updateTask(IFormInterface $Stage = null, $Id, $Task)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Task) {
            return $Stage;
        }

        $tblTask = $this->getTaskById($Id);
        (new Data($this->getBinding()))->updateTask(
            $tblTask,
            $Task['Name'],
            $Task['Date'],
            $Task['FromDate'],
            $Task['ToDate']
        );

        $Stage .= new Success('Erfolgreich geändert')
            . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate', 0);

        return $Stage;
    }

    /**
     * @param TblTask $tblTask
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @return bool|Service\Entity\TblTest[]
     */
    public function getTestAllByTaskAndTestType(TblTask $tblTask, TblTestType $tblTestType, TblDivision $tblDivision = null)
    {

        return (new Data($this->getBinding()))->getTestAllByTaskAndTestType($tblTask, $tblTestType, $tblDivision);
    }

}