<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationTask")
 * @Cache(usage="READ_ONLY")
 */
class TblTask extends Element
{
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblYear;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsTypeBehavior;
    /**
     * @Column(type="string")
     */
    protected string $Name;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $Date = null;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $FromDate = null;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $ToDate = null;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsAllYears;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreType;
    
    /**
     * @return false|TblYear
     */
    public function getServiceTblYear()
    {
        return Term::useService()->getYearById($this->serviceTblYear);
    }

    /**
     * @param TblYear $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear)
    {
        $this->serviceTblYear = $tblYear->getId();
    }

    /**
     * @return bool
     */
    public function getIsTypeBehavior(): bool
    {
        return $this->IsTypeBehavior;
    }

    /**
     * @param bool $IsTypeBehavior
     */
    public function setIsTypeBehavior(bool $IsTypeBehavior): void
    {
        $this->IsTypeBehavior = $IsTypeBehavior;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName(string $Name): void
    {
        $this->Name = $Name;
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->Date;
    }

    /**
     * @return string
     */
    public function getDateString(): string
    {
        return $this->Date instanceof DateTime ? $this->Date->format('d.m.Y') : '';
    }

    /**
     * @param DateTime|null $Date
     */
    public function setDate(?DateTime $Date): void
    {
        $this->Date = $Date;
    }

    /**
     * @return DateTime|null
     */
    public function getFromDate(): ?DateTime
    {
        return $this->FromDate;
    }

    /**
     * @return string
     */
    public function getFromDateString(): string
    {
        return $this->FromDate instanceof DateTime ? $this->FromDate->format('d.m.Y') : '';
    }

    /**
     * @param DateTime|null $FromDate
     */
    public function setFromDate(?DateTime $FromDate): void
    {
        $this->FromDate = $FromDate;
    }

    /**
     * @return DateTime|null
     */
    public function getToDate(): ?DateTime
    {
        return $this->ToDate;
    }

    /**
     * @return string
     */
    public function getToDateString(): string
    {
        return $this->ToDate instanceof DateTime ? $this->ToDate->format('d.m.Y') : '';
    }

    /**
     * @param DateTime|null $ToDate
     */
    public function setToDate(?DateTime $ToDate)
    {
        $this->ToDate = $ToDate;
    }

    /**
     * @return bool
     */
    public function getIsAllYears(): bool
    {
        return $this->IsAllYears;
    }

    /**
     * @param bool $IsAllYears
     */
    public function setIsAllYears(bool $IsAllYears): void
    {
        $this->IsAllYears = $IsAllYears;
    }

    /**
     * @return TblScoreType
     */
    public function getTblScoreType(): TblScoreType
    {
        return Grade::useService()->getScoreTypeById($this->tblGraduationScoreType);
    }

    /**
     * @param TblScoreType $tblScoreType
     */
    public function setTblScoreType(TblScoreType $tblScoreType)
    {
        $this->tblGraduationScoreType = $tblScoreType->getId();
    }
}