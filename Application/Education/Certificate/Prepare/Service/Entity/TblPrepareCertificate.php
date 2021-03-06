<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 11:19
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblPrepareCertificate")
 * @Cache(usage="READ_ONLY")
 */
class TblPrepareCertificate extends Element
{

    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_IS_GRADE_INFORMATION = 'IsGradeInformation';
    const ATTR_SERVICE_TBL_GENERATE_CERTIFICATE = 'serviceTblGenerateCertificate';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAppointedDateTask;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblBehaviorTask;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonSigner;

    /**
     * @Column(type="boolean")
     */
    protected $IsGradeInformation;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGenerateCertificate;

    /**
     * @return string
     */
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {

        $this->Date = $Date;
    }

    /**
     * @return bool|TblDivision
     */
    public function getServiceTblDivision()
    {

        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblDivision(TblDivision $tblDivision = null)
    {

        $this->serviceTblDivision = (null === $tblDivision ? null : $tblDivision->getId());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return bool|TblTask
     */
    public function getServiceTblAppointedDateTask()
    {

        if (null === $this->serviceTblAppointedDateTask) {
            return false;
        } else {
            return Evaluation::useService()->getTaskById($this->serviceTblAppointedDateTask);
        }
    }

    /**
     * @param TblTask|null $tblTask
     */
    public function setServiceTblAppointedDateTask(TblTask $tblTask = null)
    {

        $this->serviceTblAppointedDateTask = (null === $tblTask ? null : $tblTask->getId());
    }

    /**
     * @return bool|TblTask
     */
    public function getServiceTblBehaviorTask()
    {

        if (null === $this->serviceTblBehaviorTask) {
            return false;
        } else {
            return Evaluation::useService()->getTaskById($this->serviceTblBehaviorTask);
        }
    }

    /**
     * @param TblTask|null $tblTask
     */
    public function setServiceTblBehaviorTask(TblTask $tblTask = null)
    {

        $this->serviceTblBehaviorTask = (null === $tblTask ? null : $tblTask->getId());
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonSigner()
    {

        if (null === $this->serviceTblPersonSigner) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonSigner);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonSigner(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonSigner = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return boolean
     */
    public function isGradeInformation()
    {
        return $this->IsGradeInformation;
    }

    /**
     * @param boolean $IsGradeInformation
     */
    public function setIsGradeInformation($IsGradeInformation)
    {
        $this->IsGradeInformation = $IsGradeInformation;
    }

    /**
     * @return bool|TblGenerateCertificate
     */
    public function getServiceTblGenerateCertificate()
    {

        if (null === $this->serviceTblGenerateCertificate) {
            return false;
        } else {
            return Generate::useService()->getGenerateCertificateById($this->serviceTblGenerateCertificate);
        }
    }

    /**
     * @param TblGenerateCertificate|null $tblGenerateCertificate
     */
    public function setServiceTblGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate = null)
    {

        $this->serviceTblGenerateCertificate = (null === $tblGenerateCertificate ? null : $tblGenerateCertificate->getId());
    }

    /**
     * @return string
     */
    public function getDisplayTypeName()
    {

        return $this->isGradeInformation() ? 'Noteninformation' : 'Zeugnis';
    }

    /**
     * @return bool|TblCertificateType
     */
    public function getCertificateType()
    {

        if (($tblCertificateType = $this->getServiceTblGenerateCertificate())) {
            return $tblCertificateType->getServiceTblCertificateType();
        }

        return false;
    }
}