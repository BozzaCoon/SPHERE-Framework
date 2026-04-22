<?php
namespace SPHERE\Application\Transfer\SaxSVS\Export\Service;

use Doctrine\Entity;
use Doctrine\ORM\AbstractQuery;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Transfer\SaxSVS\Export\Service\Entity\TblTransferSax;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Transfer\SaxSVS\Export\Service
 */
class Data extends AbstractData
{
    
    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $PersonUuid
     * @param string    $Value
     *
     * @return ?TblTransferSax
     */
    public function createTransferSax(TblPerson $tblPerson, string $PersonUuid, string $Value): ?TblTransferSax
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblTransferSax')->findOneBy(array(TblTransferSax::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
        if (null === $Entity) {
            $Entity = new TblTransferSax();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setValue($Value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param array $ProcessList // array(transferSaxId, $Value)
     *
     * @return bool
     */
    public function createTransferSaxBulk($ProcessList = array())
    {

        $Manager = $this->getConnection()->getEntityManager();
        if(!empty($ProcessList)){
            foreach($ProcessList as $Transfer){
                /** @var TblTransferSax $Entity */
                $Entity = $Manager->getEntityById('TblTransferSax', $Transfer['TransferSaxId']);
                $Value = $Transfer['Value'];
                $Protocol = clone $Entity;

                if (null !== $Entity) {
                    $Entity->setValue($Value);
                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity, true);
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $Value
     *
     * @return bool
     */
    public function updateTransferSax(TblTransferSax $TblTransferSax, TblPerson $tblPerson, $Value)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblTransferSax $Entity */
        $Entity = $Manager->getEntityById('TblTransferSax', $TblTransferSax->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($Value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param array $ProcessList // array(transferSaxId, $Value)
     *
     * @return bool
     */
    public function updateTransferSaxBulk($ProcessList = array())
    {

        $Manager = $this->getConnection()->getEntityManager();
        if(!empty($ProcessList)){
            foreach($ProcessList as $Transfer){
                /** @var TblTransferSax $Entity */
                $Entity = $Manager->getEntityById('TblTransferSax', $Transfer['TransferSaxId']);
                $Value = $Transfer['Value'];
                $Protocol = clone $Entity;

                if (null !== $Entity) {
                    $Entity->setValue($Value);
                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity, true);
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @return bool|TblSalutation[]
     */
    public function getTransferSaxAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTransferSax');
    }

    /**
     * @param integer $Id
     * @param bool $IsForced
     *
     * @return bool|TblTransferSax
     */
    public function getTransferSaxById($Id, $IsForced = false)
    {

        if ($IsForced){
            return $this->getForceEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTransferSax', $Id);
        }
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTransferSax', $Id);
    }

    /**
     * @param $tblPerson
     *
     * @return false|TblTransferSax
     */
    public function getTransferSaxByPerson(TblPerson $tblPerson): false|TblTransferSax
    {

        /** @var bool|TblTransferSax[] $result */
        $result = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTransferSax', array(
            TblTransferSax::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
        ));
        return ($result? $result[0]: false);
    }

    /**
     * @param TblTransferSax $tblTransferSax
     *
     * @return bool
     */
    public function destroyTransferSax(TblTransferSax $tblTransferSax): bool
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblTransferSax $Entity */
        $Entity = $Manager->getEntityById('TblTransferSax', $tblTransferSax->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
