<?php
namespace SPHERE\Application\Document\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblDirectory")
 * @Cache(usage="READ_ONLY")
 */
class TblDirectory extends Element
{

    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_IS_LOCKED = 'IsLocked';
    const ATTR_TBL_DIRECTORY = 'tblDirectory';
    const ATTR_TBL_PARTITION = 'tblPartition';
    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Identifier;
    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;
    /**
     * @Column(type="bigint")
     */
    protected $tblDirectory;
    /**
     * @Column(type="bigint")
     */
    protected $tblPartition;
    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return strtoupper($this->Identifier);
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = strtoupper($Identifier);
    }

    /**
     * @return bool
     */
    public function isLocked()
    {

        return (bool)$this->IsLocked;
    }

    /**
     * @param bool $IsLocked
     */
    public function setLocked($IsLocked)
    {

        $this->IsLocked = (bool)$IsLocked;
    }

    /**
     * Parent Directory
     *
     * @return bool|TblDirectory
     */
    public function getTblDirectory()
    {

        if (null === $this->tblDirectory) {
            return false;
        } else {
            return Storage::useService()->getDirectoryById($this->tblDirectory);
        }
    }

    /**
     * Parent Directory
     * 
     * @param null|TblDirectory $tblDirectory
     */
    public function setTblDirectory(TblDirectory $tblDirectory = null)
    {

        $this->tblDirectory = ( null === $tblDirectory ? null : $tblDirectory->getId() );
    }

    /**
     * @return bool|TblPartition
     */
    public function getTblPartition()
    {

        if (null === $this->tblPartition) {
            return false;
        } else {
            return Storage::useService()->getPartitionById($this->tblPartition);
        }
    }

    /**
     * @param null|TblPartition $tblPartition
     */
    public function setTblPartition(TblPartition $tblPartition = null)
    {

        $this->tblPartition = ( null === $tblPartition ? null : $tblPartition->getId() );
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
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }


}
