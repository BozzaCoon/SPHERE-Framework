<?php
namespace SPHERE\Application\Transfer\SaxSVS\Export\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblTransferSax")
 * @Cache(usage="READ_ONLY")
 */
class TblTransferSax extends Element
{

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_PERSON_UUID = 'PersonUuid';
    const ATTR_VALUE = 'Value';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $PersonUuid;
    /**
     * @Column(type="string")
     */
    protected $Value;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson(): null|TblPerson
    {
        if(null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson): void
    {
        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return string
     */
    public function getPersonUuid(): string
    {
        return $this->PersonUuid;
    }

    /**
     * @param string $PersonUuid
     */
    public function setPersonUuid($PersonUuid): void
    {
        $this->PersonUuid = $PersonUuid;
    }

    /**
     * @return string $Value
     */
    public function getValue($IsArray = false): string
    {
        if($IsArray){

            return json_decode($this->Value);
        }
        return $this->Value;
    }

    /**
     * @param mixed $Value
     */
    public function setValue($Value): void
    {
        $this->Value = $Value;
    }
}
