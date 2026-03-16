<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Address as LayoutAddress;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCountry")
 * @Cache(usage="READ_ONLY")
 * required for SaxSVS
 */
class TblCountry extends Element
{

    // GuiStringOrder (Consumer Setting)

    const ATTR_NAME = 'Name';
    const ATTR_EXTERN = 'Extern';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Extern;

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
    public function getExtern()
    {

        return $this->Extern;
    }

    /**
     * @param string $Extern
     */
    public function setExtern($Extern)
    {

        $this->Extern = $Extern;
    }
}
