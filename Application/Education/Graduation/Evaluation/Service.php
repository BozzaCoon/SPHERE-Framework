<?php

namespace SPHERE\Application\Education\Graduation\Evaluation;

use SPHERE\Application\Education\Graduation\Evaluation\Service\Data;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestLink;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Setup;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
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
     * @return bool|TblTestType[]
     */
    public function getTestTypesForGradeTypes()
    {

        return (new Data($this->getBinding()))->getTestTypesForGradeTypes();
    }

    /**
     * @return bool|TblTestType[]
     */
    public function getTestTypeAllWhereTask()
    {

        return (new Data($this->getBinding()))->getTestTypeAllWhereTask();
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param null|TblPeriod $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|TblTest[]
     */
    public function getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType = null,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision, $tblSubject, $tblTestType, $tblPeriod, $tblSubjectGroup
        );
    }

    /**
     * @param TblTestType $tblTestType
     *
     * @return bool|TblTest[]
     */
    public function getTestAllByTestType(TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getTestAllByTestType($tblTestType);
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @return bool|TblTest[]
     */
    public function getTestAllByTestTypeAndDivision(TblTestType $tblTestType, TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getTestAllByTestTypeAndDivision($tblTestType, $tblDivision);
    }

    /**
     * @return bool|TblTask[]
     */
    public function getTaskAll()
    {

        return (new Data($this->getBinding()))->getTaskAll();
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblYear $tblYear
     *
     * @return bool|TblTask[]
     */
    public function getTaskAllByTestType(TblTestType $tblTestType, TblYear $tblYear = null)
    {

        return (new Data($this->getBinding()))->getTaskAllByTestType($tblTestType, $tblYear);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $DivisionSubjectId
     * @param null $Test
     * @param string $BasicRoute
     *
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
        if (!($tblPeriod = Term::useService()->getPeriodById($Test['Period']))) {
            $Stage->setError('Test[Period]', 'Bitte wählen Sie einen Zeitraum aus');
            $Error = true;
        }
        if (!($tblGradeType = Gradebook::useService()->getGradeTypeById($Test['GradeType']))) {
            $Stage->setError('Test[GradeType]', 'Bitte wählen Sie einen Zensuren-Typ aus');
            $Error = true;
        }
        if (isset($Test['Date']) && empty($Test['Date'])) {
            $Stage->setError('Test[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if ($Error) {
            return $Stage;
        }

        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);

        if (!$tblDivisionSubject) {
            return new Danger(new Ban() . ' Fach-Klasse nicht gefunden')
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        if (!$tblDivisionSubject->getTblDivision()) {
            return new Danger(new Ban() . ' Klasse nicht gefunden')
                . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_ERROR,
                    array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        }

        if (!$tblDivisionSubject->getServiceTblSubject()) {
            return new Danger(new Ban() . ' Fach nicht gefunden')
                . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_ERROR,
                    array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        }

        if (!$tblGradeType) {
            return new Danger(new Ban() . ' Zensuren-Typ nicht gefunden')
                . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_ERROR,
                    array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        }

        $tblTest = (new Data($this->getBinding()))->createTest(
            $tblDivisionSubject->getTblDivision(),
            $tblDivisionSubject->getServiceTblSubject(),
            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
            $tblPeriod,
            $tblGradeType,
            $this->getTestTypeByIdentifier('TEST'),
            null,
            $Test['Description'],
            isset($Test['IsContinues']) ? null : $Test['Date'],
            isset($Test['IsContinues']) ? null : $Test['CorrectionDate'],
            isset($Test['IsContinues']) ? null : $Test['ReturnDate'],
            isset($Test['IsContinues']),
            isset($Test['FinishDate']) ? $Test['FinishDate'] : null
        );
        if (isset($Test['Link']) && $tblTest) {
            $LinkId = $this->getNextLinkId();
            $this->createTestLink($tblTest, $LinkId);
            foreach ($Test['Link'] as $divisionSubjectToLinkId => $value) {
                if (($tblDivisionSubjectToLink = Division::useService()->getDivisionSubjectById($divisionSubjectToLinkId))) {
                    $tblTestAdd = (new Data($this->getBinding()))->createTest(
                        $tblDivisionSubjectToLink->getTblDivision(),
                        $tblDivisionSubjectToLink->getServiceTblSubject(),
                        $tblDivisionSubjectToLink->getTblSubjectGroup() ? $tblDivisionSubjectToLink->getTblSubjectGroup() : null,
                        $tblPeriod,
                        $tblGradeType,
                        $this->getTestTypeByIdentifier('TEST'),
                        null,
                        $Test['Description'],
                        isset($Test['IsContinues']) ? null : $Test['Date'],
                        isset($Test['IsContinues']) ? null : $Test['CorrectionDate'],
                        isset($Test['IsContinues']) ? null : $Test['ReturnDate'],
                        isset($Test['IsContinues']),
                        isset($Test['FinishDate']) ? $Test['FinishDate'] : null
                    );

                    $this->createTestLink($tblTestAdd, $LinkId);
                }
            }
        }

        return new Success('Die Leistungsüberprüfung ist angelegt worden',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_SUCCESS,
                array('DivisionSubjectId' => $tblDivisionSubject->getId()));

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
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $Test
     * @param $BasicRoute
     *
     * @return IFormInterface|string
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
        $Error = false;
        if (!($tblGradeType = Gradebook::useService()->getGradeTypeById($Test['GradeType']))) {
            $Stage->setError('Test[GradeType]', 'Bitte wählen Sie einen Zensuren-Typ aus');
            $Error = true;
        }
        if (isset($Test['Date']) && empty($Test['Date'])) {
            $Stage->setError('Test[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if ($tblTest && $tblTest->getFinishDate() && isset($Test['FinishDate']) && empty($Test['FinishDate'])) {
            $Stage->setError('Test[FinishDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if ($Error) {
            return $Stage;
        }

        if ($tblTest) {
            // Change GradeType of Grades
            if ($tblTest->getServiceTblGradeType()
                && $tblGradeType
                && $tblGradeType->getId() != $tblTest->getServiceTblGradeType()->getId()
            ) {
                $isChangeGradesGradeType = true;
                Gradebook::useService()->updateGradesGradeTypeByTest($tblTest, $tblGradeType);
            } else {
                $isChangeGradesGradeType = false;
            }
            (new Data($this->getBinding()))->updateTest(
                $tblTest,
                $Test['Description'],
                isset($Test['Date']) ? $Test['Date'] : null,
                isset($Test['CorrectionDate']) ? $Test['CorrectionDate'] : null,
                isset($Test['ReturnDate']) ? $Test['ReturnDate'] : null,
                isset($Test['FinishDate']) ? $Test['FinishDate'] : null,
                $tblGradeType ? $tblGradeType : null
            );
            if (($tblTestLinkList = $tblTest->getLinkedTestAll())) {
                foreach ($tblTestLinkList as $tblTestItem) {
                    if ($isChangeGradesGradeType) {
                        Gradebook::useService()->updateGradesGradeTypeByTest($tblTestItem, $tblGradeType);
                    }
                    (new Data($this->getBinding()))->updateTest(
                        $tblTestItem,
                        $Test['Description'],
                        isset($Test['Date']) ? $Test['Date'] : null,
                        isset($Test['CorrectionDate']) ? $Test['CorrectionDate'] : null,
                        isset($Test['ReturnDate']) ? $Test['ReturnDate'] : null,
                        isset($Test['FinishDate']) ? $Test['FinishDate'] : null,
                        $tblGradeType ? $tblGradeType : null
                    );
                }
            }
        }

        if (!$tblTest->getServiceTblDivision()) {
            return new Danger(new Ban() . ' Klasse nicht gefunden')
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }
        if (!$tblTest->getServiceTblSubject()) {
            return new Danger(new Ban() . ' Fach nicht gefunden')
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblTest->getServiceTblDivision(),
            $tblTest->getServiceTblSubject(),
            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
        );

        return new Success('Test erfolgreich geändert.', new \SPHERE\Common\Frontend\Icon\Repository\Success()) .
            new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_SUCCESS,
                array('DivisionSubjectId' => $tblDivisionSubject->getId()));
    }

    /**
     * @param $Id
     *
     * @return bool|TblTest
     */
    public function getTestById($Id)
    {

        return (new Data($this->getBinding()))->getTestById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Task
     * @param TblYear $tblYear
     *
     * @return IFormInterface|string
     */
    public function createTask(IFormInterface $Stage = null, $Task, TblYear $tblYear = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Task) {
            return $Stage;
        }

        if (null === $tblYear) {
            return new Danger('Kein Schuljahr ausgewählt', new Exclamation());
        }

        $Error = false;
        if (!($tblTestType = Evaluation::useService()->getTestTypeById($Task['Type']))) {
            $Stage->setError('Task[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $Error = true;
        }
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
        } else {
            $nowDate = (new \DateTime('now'))->format("Y-m-d");
            $toDate = new \DateTime($Task['ToDate']);
            $toDate = $toDate->format('Y-m-d');
            if ($nowDate && $toDate) {
                if ($nowDate < $toDate) {

                } else {
                    $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum in der Zukunft an');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            $tblPeriod = Term::useService()->getPeriodById($Task['Period']);
            $tblScoreType = Gradebook::useService()->getScoreTypeById($Task['ScoreType']);
            (new Data($this->getBinding()))->createTask(
                $tblTestType, $Task['Name'], $Task['Date'], $Task['FromDate'], $Task['ToDate'],
                $tblPeriod ? $tblPeriod : null, $tblScoreType ? $tblScoreType : null, $tblYear ? $tblYear : null
            );
            $Stage .= new Success('Notenauftrag erfolgreich angelegt',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_SUCCESS, array('YearId' => $tblYear->getId()));
        }

        return $Stage;
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
     * @param IFormInterface|null $Stage
     * @param                     $Id
     * @param                     $Task
     *
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
        $Error = false;
        if (!($tblTestType = Evaluation::useService()->getTestTypeById($Task['Type']))) {
            $Stage->setError('Task[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $Error = true;
        }
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
        } else {
            $nowDate = (new \DateTime('now'))->format("Y-m-d");
            $toDate = new \DateTime($Task['ToDate']);
            $toDate = $toDate->format('Y-m-d');
            if ($nowDate && $toDate) {
                if ($nowDate < $toDate) {

                } else {
                    $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum in der Zukunft an');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            $tblTask = $this->getTaskById($Id);
            $tblPeriod = Term::useService()->getPeriodById($Task['Period']);
            $tblScoreType = Gradebook::useService()->getScoreTypeById($Task['ScoreType']);
            (new Data($this->getBinding()))->updateTask(
                $tblTask,
                $this->getTestTypeById($Task['Type']),
                $Task['Name'],
                $Task['Date'],
                $Task['FromDate'],
                $Task['ToDate'],
                $tblPeriod ? $tblPeriod : null,
                $tblScoreType ? $tblScoreType : null,
                $tblTask->isLocked()
            );

            $Stage .= new Success('Notenauftrag erfolgreich geändert',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_SUCCESS);

        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblTask
     */
    public function getTaskById($Id)
    {

        return (new Data($this->getBinding()))->getTaskById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function updateDivisionTasks(IFormInterface $Stage = null, $Id, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Stage;
        }

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {
            if ($tblTask->getTblTestType()->getIdentifier() == 'BEHAVIOR_TASK') {
                $isBehaviorTask = true;
            } else {
                $isBehaviorTask = false;
            }

            if ($isBehaviorTask) {

                $behaviorTaskAddList = array();
                $behaviorTaskRemoveTestList = array();

                // add
                if ($Data && isset($Data['GradeType'])) {
                    foreach ($Data['GradeType'] as $gradeTypeId => $value) {
                        $tblGradeType = Gradebook::useService()->getGradeTypeById($gradeTypeId);
                        if ($tblGradeType) {
                            if ($Data && isset($Data['Division'])) {
                                foreach ($Data['Division'] as $divisionId => $divisionValue) {
                                    $tblDivision = Division::useService()->getDivisionById($divisionId);
                                    if ($tblDivision) {
                                        $behaviorTaskAddList[] = array(
                                            'tblTask' => $tblTask,
                                            'tblDivision' => $tblDivision,
                                            'tblGradeType' => $tblGradeType
                                        );
                                    }
                                }
                            }
                        }
                    }
                }

                // remove
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);
                if ($tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblDivision = $tblTest->getServiceTblDivision();
                        if ($tblDivision) {
                            if (!isset($Data['Division'][$tblDivision->getId()])) {
                                $behaviorTaskRemoveTestList[] = $tblTest;
                            } elseif ($tblTest->getServiceTblGradeType()
                                && !isset($Data['GradeType'][$tblTest->getServiceTblGradeType()->getId()])
                            ) {
                                // delete single
                                $behaviorTaskRemoveTestList[] = $tblTest;
                            }
                        }
                    }
                }

                (new Data($this->getBinding()))->updateDivisionBehaviorTaskAsBulk($behaviorTaskAddList,
                    $behaviorTaskRemoveTestList);

            } else {

                $tblDivisionList = array();
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);
                if ($tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblDivision = $tblTest->getServiceTblDivision();
                        if ($tblDivision) {
                            $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                        }
                    }
                }

                $addList = array();
                $removeList = array();

                // remove
                if (!empty($tblDivisionList)) {
                    /** @var TblDivision $tblDivision */
                    foreach ($tblDivisionList as $tblDivision) {
                        if (!isset($Data['Division'][$tblDivision->getId()])) {
                            if (($tblTestAllByTask = $this->getTestAllByTask($tblTask, $tblDivision))) {
                                foreach ($tblTestAllByTask as $tblTest) {
                                    $removeList[] = $tblTest;
                                }
                            }
                        }
                    }
                }

                // add
                if ($Data && isset($Data['Division'])) {
                    foreach ($Data['Division'] as $divisionId => $value) {
                        $tblDivision = Division::useService()->getDivisionById($divisionId);
                        if ($tblDivision) {
                            $addList[] = array(
                                'tblTask' => $tblTask,
                                'tblDivision' => $tblDivision
                            );
                        }
                    }
                }

                (new Data($this->getBinding()))->updateDivisionAppointedDateTaskAsBulk($addList, $removeList);

            }
        }

        return new Success('Daten erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster/Division', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblTask->getId()));
    }

    public function addBehaviorGradeTypeToDivisionAndTask(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblGradeType $tblGradeType
    ) {

        $tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision(
            $tblDivision
        );

        if ($tblDivisionSubjectAll) {
            foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                if ($tblDivisionSubject->getTblSubjectGroup()) {
                    if (!$this->existsTestByTaskAndGradeType(
                        $tblTask, $tblDivision, $tblDivisionSubject->getServiceTblSubject(), $tblGradeType,
                        $tblDivisionSubject->getTblSubjectGroup()
                    )
                    ) {
                        (new Data($this->getBinding()))->createTest(
                            $tblDivision,
                            $tblDivisionSubject->getServiceTblSubject(),
                            $tblDivisionSubject->getTblSubjectGroup(),
                            null,
                            $tblGradeType,
                            $tblTask->getTblTestType(),
                            $tblTask,
                            '',
                            $tblTask->getDate()
                        );
                    }
                } else {
                    if (!Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                        $tblDivision, $tblDivisionSubject->getServiceTblSubject()
                    )
                    ) {
                        if (!$this->existsTestByTaskAndGradeType(
                            $tblTask, $tblDivision, $tblDivisionSubject->getServiceTblSubject(), $tblGradeType)
                        ) {
                            (new Data($this->getBinding()))->createTest(
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                null,
                                null,
                                $tblGradeType,
                                $tblTask->getTblTestType(),
                                $tblTask,
                                '',
                                $tblTask->getDate()
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool
     */
    public function existsTestByTaskAndGradeType(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblGradeType $tblGradeType,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->existsTestByTaskAndGradeType($tblTask, $tblDivision, $tblSubject,
            $tblGradeType, $tblSubjectGroup);
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision|null $tblDivision
     *
     * @return bool|Service\Entity\TblTest[]
     */
    public function getTestAllByTask(TblTask $tblTask, TblDivision $tblDivision = null)
    {

        $tblTestList = (new Data($this->getBinding()))->getTestAllByTask($tblTask, $tblDivision);
        if ($tblTestList) {
            $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('GradeTypeName');
        }

        return $tblTestList;
    }

    /**
     * @param TblTask $tblTask
     * @return false|TblDivision[]
     */
    public function getDivisionAllByTask(TblTask $tblTask)
    {

        $resultList = array();
        $tblTestList = $this->getTestAllByTask($tblTask);
        if ($tblTestList) {
            foreach ($tblTestList as $tblTest) {
                if ($tblTest->getServiceTblDivision()) {
                    $resultList[$tblTest->getServiceTblDivision()->getId()] = $tblTest->getServiceTblDivision();
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     */
    public function removeDivisionFromTask(
        TblTask $tblTask,
        TblDivision $tblDivision
    ) {

        $tblTestAllByTask = $this->getTestAllByTask($tblTask, $tblDivision);
        if ($tblTestAllByTask) {
            foreach ($tblTestAllByTask as $tblTest) {
                (new Data($this->getBinding()))->destroyTest($tblTest);
            }
        }
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     */
    public function addDivisionToAppointedDateTask(
        TblTask $tblTask,
        TblDivision $tblDivision
    ) {

        $tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision(
            $tblDivision
        );

        if ($tblDivisionSubjectAll) {
            foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                if ($tblDivisionSubject->getServiceTblSubject()) {
                    if ($tblTask->getTblTestType()->getId() == $this->getTestTypeByIdentifier('APPOINTED_DATE_TASK')) {
                        if ($tblDivisionSubject->getTblSubjectGroup()) {
                            if (!$this->existsTestByTask($tblTask, $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(), $tblDivisionSubject->getTblSubjectGroup())
                            ) {
                                (new Data($this->getBinding()))->createTest(
                                    $tblDivision,
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblDivisionSubject->getTblSubjectGroup(),
                                    null,
                                    null,
                                    $tblTask->getTblTestType(),
                                    $tblTask,
                                    '',
                                    $tblTask->getDate()
                                );
                            }
                        } else {
                            if (!Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                $tblDivision, $tblDivisionSubject->getServiceTblSubject()
                            )
                            ) {
                                if (!$this->existsTestByTask($tblTask, $tblDivision,
                                    $tblDivisionSubject->getServiceTblSubject())
                                ) {
                                    (new Data($this->getBinding()))->createTest(
                                        $tblDivision,
                                        $tblDivisionSubject->getServiceTblSubject(),
                                        null,
                                        null,
                                        null,
                                        $tblTask->getTblTestType(),
                                        $tblTask,
                                        '',
                                        $tblTask->getDate()
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function existsTestByTask(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->existsTestByTask($tblTask, $tblDivision, $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDivision[]
     */
    public function getTestAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getTestAllByDivision($tblDivision);
    }

    /**
     * @param TblTest $tblTest
     *
     * @return bool
     */
    public function destroyTest(TblTest $tblTest)
    {
        if (($tblTestLinkList = $tblTest->getLinkedTestAll())) {
            foreach ($tblTestLinkList as $tblTestItem) {
                (new Data($this->getBinding()))->destroyTest($tblTestItem);
            }
        }

        return (new Data($this->getBinding()))->destroyTest($tblTest);
    }

    /**
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function destroyTask(TblTask $tblTask)
    {

        return (new Data($this->getBinding()))->destroyTask($tblTask);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @return false|TblTask[]
     */
    public function getTaskAllByDivision(TblDivision $tblDivision, TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getTaskAllByDivision($tblDivision, $tblTestType);
    }

    /**
     * @param TblYear $tblYear
     * @param TblDivisionSubject $tblDivisionSubjectSelected
     *
     * @return bool|Panel
     */
    public function getTestLinkPanel(
        TblYear $tblYear,
        TblDivisionSubject $tblDivisionSubjectSelected
    ) {
        $panel = false;
        if ($tblDivisionSubjectSelected !== null) {
            $tblPerson = false;
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
                if ($tblPersonAllByAccount) {
                    $tblPerson = $tblPersonAllByAccount[0];
                }
            }

            $list = array();
            if ($tblPerson) {
                $tblSubjectTeacherList = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson);
                if ($tblSubjectTeacherList) {
                    foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                        if (($tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject())) {
                            if (($tblDivision = $tblDivisionSubject->getTblDivision())
                                && $tblDivision->getServiceTblYear()
                                && $tblYear->getId() == $tblDivision->getServiceTblYear()
                                && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                                && ($tblDivisionSubjectSelected->getServiceTblSubject())
                                && ($tblSubject->getId() == $tblDivisionSubjectSelected->getServiceTblSubject()->getId())
                            ) {

                                if (!$tblDivisionSubject->getTblSubjectGroup()
                                    && ($tblDivisionSubjectListHavingGroup = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                        $tblDivision,
                                        $tblSubject))
                                ) {
                                    foreach ($tblDivisionSubjectListHavingGroup as $groupDivisionSubject) {
                                        if ($groupDivisionSubject->getId() !== $tblDivisionSubjectSelected->getId()) {
                                            $list[$groupDivisionSubject->getId()] = array(
                                                'tblDivision' => $groupDivisionSubject->getTblDivision(),
                                                'tblSubject' => $groupDivisionSubject->getServiceTblSubject(),
                                                'tblSubjectGroup' => $groupDivisionSubject->getTblSubjectGroup()
                                            );
                                        }
                                    }
                                } else {
                                    if ($tblDivisionSubject->getId() !== $tblDivisionSubjectSelected->getId()) {
                                        $list[$tblDivisionSubject->getId()] = array(
                                            'tblDivision' => $tblDivision,
                                            'tblSubject' => $tblSubject,
                                            'tblSubjectGroup' => $tblDivisionSubject->getTblSubjectGroup()
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($list)) {
                $itemList = array();
                foreach ($list as $key => $item) {
                    /** @var TblDivision $division */
                    $division = $item['tblDivision'];
                    /** @var TblSubject $subject */
                    $subject = $item['tblSubject'];
                    /** @var TblSubjectGroup | false $group */
                    $group = $item['tblSubjectGroup'];
                    $name = $division->getDisplayName() . ' - ' . $subject->getAcronym()
                        . ($group ? ' - ' . $group->getName() : '');
                    $itemList[$name] =
                        new CheckBox(
                            'Test[Link][' . $key . ']',
                            $name,
                            1
                        );
                }
                ksort($itemList);
                $panel = new Panel(
                    'Leistungsüberprüfungen verknüpfen',
                    $itemList,
                    Panel::PANEL_TYPE_PRIMARY
                );
            }
        }

        return $panel;
    }

    /**
     * @param TblTest $tblTest
     * @param int $LinkId
     *
     * @return TblTestLink
     */
    public function createTestLink(TblTest $tblTest, $LinkId)
    {

        return (new Data($this->getBinding()))->createTestLink($tblTest, $LinkId);
    }

    /**
     * @return int
     */
    public function getNextLinkId()
    {

        return (new Data($this->getBinding()))->getNextLinkId();
    }

    /**
     * @param TblTest $tblTest
     * @return false | TblTest[]
     */
    public function getTestLinkAllByTest(TblTest $tblTest)
    {

        return (new Data($this->getBinding()))->getTestLinkAllByTest($tblTest);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTask $tblTask
     *
     * @return false|TblTest[]
     */
    public function getTestListBy(TblDivision $tblDivision, TblSubject $tblSubject, TblTask $tblTask)
    {

        return (new Data($this->getBinding()))->getTestListBy($tblDivision, $tblSubject, $tblTask);
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType)
    {

        return (new Data($this->getBinding()))->isGradeTypeUsed($tblGradeType);
    }

    /**
     * @param TblTask $tblTask
     * @param bool $IsLocked
     *
     * @return bool
     */
    public function setTaskLocked(TblTask $tblTask, $IsLocked = true)
    {

        return (new Data($this->getBinding()))->updateTask(
            $tblTask,
            $tblTask->getTblTestType(),
            $tblTask->getName(),
            $tblTask->getDate() ? $tblTask->getDate() : null,
            $tblTask->getFromDate() ? $tblTask->getFromDate() : null,
            $tblTask->getToDate() ? $tblTask->getToDate() : null,
            $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : null,
            $tblTask->getServiceTblScoreType() ? $tblTask->getServiceTblScoreType() : null,
            $IsLocked
        );
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function existsTestByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->existsTestByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|Layout
     */
    public function getTeacherWelcome(TblPerson $tblPerson)
    {

        $appointedDateTaskList = array();
        $behaviorTask = array();
        $futureAppointedDateTaskList = array();
        $futureBehaviorTask = array();
        if (($tblSubjectTeacherList = Division::useService()->getSubjectTeacherAllByPerson($tblPerson))) {
            foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                if (($tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject())
                    && ($tblDivision = $tblDivisionSubject->getTblDivision())
                    && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                ) {

                    $appointedDateTaskList = $this->setCurrentTaskList(
                        $tblDivision,
                        $tblSubject,
                        ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                            ? $tblSubjectGroup : null, $this->getTestTypeByIdentifier('APPOINTED_DATE_TASK'),
                        $appointedDateTaskList);

                    $behaviorTask = $this->setCurrentTaskList(
                        $tblDivision,
                        $tblSubject,
                        ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                            ? $tblSubjectGroup : null, $this->getTestTypeByIdentifier('BEHAVIOR_TASK'),
                        $behaviorTask);

                    $futureAppointedDateTaskList = $this->setFutureTaskList(
                        $tblDivision,
                        $tblSubject,
                        ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                            ? $tblSubjectGroup : null, $this->getTestTypeByIdentifier('APPOINTED_DATE_TASK'),
                        $futureAppointedDateTaskList);

                    $futureBehaviorTask = $this->setFutureTaskList(
                        $tblDivision,
                        $tblSubject,
                        ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                            ? $tblSubjectGroup : null, $this->getTestTypeByIdentifier('BEHAVIOR_TASK'),
                        $futureBehaviorTask);
                }
            }
        }


        $columns = array();
        $columns = $this->setWelcomeContent($appointedDateTaskList,
            $columns);
        $columns = $this->setWelcomeContent($behaviorTask,
            $columns);
        $columns = $this->setWelcomeContent($futureAppointedDateTaskList,
            $columns, true);
        $columns = $this->setWelcomeContent($futureBehaviorTask,
            $columns, true);

        if (empty($columns)) {
            return false;
        } else {
            return new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow($columns)
                ))
            ));
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param TblTestType $tblTestType
     * @param $taskList
     *
     * @return array
     */
    private function setCurrentTaskList(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblTestType $tblTestType,
        $taskList
    ) {
        $tblTestList = $this->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision,
            $tblSubject,
            $tblTestType,
            null,
            $tblSubjectGroup
        );

        $resultList = $taskList;
        $now = new \DateTime('now');
        if ($tblTestList) {
            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                $tblSubjectGroup = $tblTest->getServiceTblSubjectGroup();
                if (($tblTask = $tblTest->getTblTask())
                    && $tblTask->getFromDate()
                    && $tblTask->getToDate()
                    && ($fromDate = new \DateTime($tblTask->getFromDate()))
                    && ($toDate = new \DateTime($tblTask->getToDate()))
                    && $now > $fromDate
                    && $now < ($toDate->add(new \DateInterval('P1D')))
                ) {

                    $countPersons = 0;
                    if ($tblSubjectGroup
                        && ($tblDivisionSubjectTemp = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                            $tblDivision,
                            $tblSubject,
                            $tblSubjectGroup
                        ))
                    ) {
                        if (($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectTemp))) {
                            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                if (($tblSubjectStudent->getServiceTblPerson())) {
                                    $countPersons++;
                                }
                            }
                        }
                    } elseif (!$tblSubjectGroup
                        && ($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByDivision($tblDivision))
                    ) {
                        foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                            if (($tblDivisionStudent->getServiceTblPerson())) {
                                $countPersons++;
                            }
                        }
                    }

                    $countGrades = 0;
                    if (($tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest))) {
                        foreach ($tblGradeList as $tblGrade) {
                            if ($tblGrade->getServiceTblPerson()
                                && $tblGrade !== null & $tblGrade !== ''
                            ) {
                                $countGrades++;
                            }
                        }
                    }

                    $tblGradeType = $tblTest->getServiceTblGradeType();

                    if ($tblTestType->getIdentifier() == 'APPOINTED_DATE_TASK') {
                        $text = ' ' . $tblDivision->getDisplayName()
                            . ' ' . $tblSubject->getAcronym()
                            . ' ' . $tblSubject->getName()
//                            . ($tblGradeType ? ' ' . $tblGradeType->getName() : '')
                            . ($tblSubjectGroup ? ' (' . $tblSubjectGroup->getName() . ')' : '')
                            . ': ' . $countGrades . ' von ' . $countPersons . ' Zensuren vergeben';
                        $taskList[$tblTask->getId()][$tblDivision->getDisplayName()
                        . $tblSubject->getAcronym()
                        . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['Message'] =
                            new PullClear(($countGrades < $countPersons
                                    ? new Warning(new Exclamation() . $text)
                                    : new \SPHERE\Common\Frontend\Text\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                        . $text))
                                . new PullRight(new Standard(
                                    '',
                                    '/Education/Graduation/Evaluation/Test/Teacher/Grade/Edit',
                                    new Extern(),
                                    array(
                                        'Id' => $tblTest->getId()
                                    ),
                                    'Zur Noteneingabe wechseln'
                                )));
                    } else {
                        if ($tblGradeType && $tblGradeType->getName() == 'Betragen') {
                            $taskList[$tblTask->getId()]
                            [$tblDivision->getDisplayName()
                            . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]
                            ['LinkId'] = $tblTest->getId();
                        }

                        if (!isset($taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['DivisionSubject'])
                        ) {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['DivisionSubject']
                                = ' ' . $tblDivision->getDisplayName()
                                . ' ' . $tblSubject->getAcronym()
                                . ' ' . $tblSubject->getName()
                                . ($tblSubjectGroup ? ' (' . $tblSubjectGroup->getName() . ')' : '');
                        }

                        if (isset($taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountPersons'])
                        ) {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountPersons'] += $countPersons;
                        } else {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountPersons'] = $countPersons;
                        }

                        if (isset($taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountGrades'])
                        ) {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountGrades'] += $countGrades;
                        } else {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountGrades'] = $countGrades;
                        }
                    }
                }
            }
        }

        if ($tblTestType->getIdentifier() == 'BEHAVIOR_TASK') {
            foreach ($taskList as $taskId => $divisionSubjectArray) {
                if (($tblTask = Evaluation::useService()->getTaskById($taskId))) {
                    foreach ($divisionSubjectArray as $key => $testArray) {
                        $name = isset($testArray['DivisionSubject']) ? $testArray['DivisionSubject'] : '';
                        if (isset($testArray['CountPersons']) && isset($testArray['CountGrades'])) {
                            $countPersons = $testArray['CountPersons'];
                            $countGrades = $testArray['CountGrades'];
                            if (isset($testArray['LinkId'])) {
                                $link = new PullRight(new Standard(
                                    '',
                                    '/Education/Graduation/Evaluation/Test/Teacher/Grade/Edit',
                                    new Extern(),
                                    array(
                                        'Id' => $testArray['LinkId']
                                    ),
                                    'Zur Noteneingabe wechseln'
                                ));
                            } else {
                                $link = false;
                            }

                            $name .= ': ' . $countGrades . ' von ' . $countPersons . ' Zensuren vergeben';
                            $name = ($countGrades < $countPersons
                                ? new Warning(new Exclamation() . $name)
                                : new \SPHERE\Common\Frontend\Text\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . $name));

                            $resultList[$tblTask->getId()][$key]['Message'] = $name . ($link ? $link : '');
                        }
                    }
                }
            }

            return $resultList;
        }

        return $taskList;
    }

    /**
     * @param $taskList
     * @param $columns
     * @param bool $isFuture
     *
     * @return array
     */
    private function setWelcomeContent($taskList, $columns, $isFuture = false)
    {
        foreach ($taskList as $taskId => $list) {
            if (($tblTask = Evaluation::useService()->getTaskById($taskId))
                && $tblTestType = $tblTask->getTblTestType()
            ) {
                if ($isFuture) {
                    $panel = new Panel(
                        ($tblTestType->getIdentifier() == 'APPOINTED_DATE_TASK'
                            ? 'Nächster Stichtagsnotenauftrag '
                            : 'Nächster Kopfnotenauftrag '),
                        array(
                            new Muted('Stichtag: ' . $tblTask->getDate()),
                            new Muted('Bearbeitungszeitraum: ' . $tblTask->getFromDate() . ' - ' . $tblTask->getToDate())
                        ),
                        Panel::PANEL_TYPE_INFO
                    );
                    $columns[] = new LayoutColumn($panel, 6);
                } else {
                    ksort($list);
                    $messageList = array();
                    foreach ($list as $divisionSubject) {
                        if (isset($divisionSubject['Message'])) {
                            $messageList[] = $divisionSubject['Message'];
                        }
                    }
                    array_unshift($messageList,
                        new Muted('Bearbeitungszeitraum: ' . $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()));
                    array_unshift($messageList, new Muted('Stichtag: ' . $tblTask->getDate()));
                    $panel = new Panel(
                        ($tblTestType->getIdentifier() == 'APPOINTED_DATE_TASK'
                            ? 'Aktueller Stichtagsnotenauftrag '
                            : 'Aktueller Kopfnotenauftrag '),
                        $messageList,
                        Panel::PANEL_TYPE_INFO
                    );
                    $columns[] = new LayoutColumn($panel, 6);
                }
            }
        }
        return $columns;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param TblTestType $tblTestType
     * @param $taskList
     * @return mixed
     */
    private function setFutureTaskList(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblTestType $tblTestType,
        $taskList
    ) {
        $tblTestList = $this->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision,
            $tblSubject,
            $tblTestType,
            null,
            $tblSubjectGroup
        );

        $now = new \DateTime('now');
        if ($tblTestList) {
            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                if (($tblTask = $tblTest->getTblTask())
                    && $tblTask->getFromDate()
                    && $tblTask->getToDate()
                    && ($fromDate = new \DateTime($tblTask->getFromDate()))
                    && $now < $fromDate
                    && $now > ($fromDate->sub(new \DateInterval('P7D')))
                ) {
                    $taskList[$tblTask->getId()] = $tblTask;
                }
            }
        }

        return $taskList;
    }
}