<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.07.2016
 * Time: 09:05
 */

namespace SPHERE\Application\Education\ClassRegister\Absence;

use SPHERE\Application\Education\ClassRegister\Absence\Service\Data;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence
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
     * @param TblPerson $tblPerson
     * @param TblDivision|null $tblDivision
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByPerson(TblPerson $tblPerson, TblDivision $tblDivision = null)
    {

        return (new Data($this->getBinding()))->getAbsenceAllByPerson($tblPerson, $tblDivision);
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
     * @param IFormInterface|null $Stage
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createAbsence(IFormInterface $Stage = null, TblPerson $tblPerson, TblDivision $tblDivision, $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $Stage->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        // ToDo setError for RadioBox
        if (!isset($Data['Status'])) {
            $Stage->setError('Data[Status]', 'Bitte geben Sie einen Status an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createAbsence(
                $tblPerson,
                $tblDivision,
                $Data['FromDate'],
                $Data['ToDate'],
                $Data['Status'],
                $Data['Remark']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Fehlzeit ist erfasst worden.')
            . new Redirect('/Education/ClassRegister/Absence', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblDivision->getId(),
                'PersonId' => $tblPerson->getId()
            ));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblAbsence $tblAbsence
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateAbsence(IFormInterface $Stage = null, TblAbsence $tblAbsence, $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $Stage->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        // ToDo setError for RadioBox
        if (!isset($Data['Status'])) {
            $Stage->setError('Data[Status]', 'Bitte geben Sie einen Status an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateAbsence(
                $tblAbsence,
                $Data['FromDate'],
                $Data['ToDate'],
                $Data['Status'],
                $Data['Remark']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Fehlzeit ist geändert worden.')
            . new Redirect('/Education/ClassRegister/Absence', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblAbsence->getServiceTblDivision()->getId(),
                'PersonId' => $tblAbsence->getServiceTblPerson()->getId()
            ));
        }

        return $Stage;
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return bool
     */
    public function destroyAbsence(TblAbsence $tblAbsence)
    {

        return (new Data($this->getBinding()))->destroyAbsence($tblAbsence);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param \DateTime|null $tillDate
     *
     * @return int
     */
    function getUnexcusedDaysByPerson(TblPerson $tblPerson, TblDivision $tblDivision, \DateTime $tillDate = null)
    {

        $list = $this->getAbsenceAllByPerson($tblPerson, $tblDivision);
        $days = 0;
        if ($list) {
            foreach ($list as $item) {
                if ($item->getStatus() == TblAbsence::VALUE_STATUS_UNEXCUSED) {
                    $days += $item->getDays($tillDate);
                }
            }
        }

        return $days;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param \DateTime|null $tillDate
     *
     * @return int
     */
    public function getExcusedDaysByPerson(TblPerson $tblPerson, TblDivision $tblDivision, \DateTime $tillDate = null)
    {

        $list = $this->getAbsenceAllByPerson($tblPerson, $tblDivision);
        $days = 0;
        if ($list) {
            foreach ($list as $item) {
                if ($item->getStatus() == TblAbsence::VALUE_STATUS_EXCUSED) {
                    $days += $item->getDays($tillDate);
                }
            }
        }

        return $days;
    }
}