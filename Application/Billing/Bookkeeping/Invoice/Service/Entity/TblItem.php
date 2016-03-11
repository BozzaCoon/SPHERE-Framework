<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem as InventoryTblItem;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItem")
 * @Cache(usage="READ_ONLY")
 */
class TblItem extends Element
{

    const ATTR_SERVICE_TBL_ITEM = 'serviceTblItem';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="string")
     */
    protected $Value;
    /**
     * @Column(type="bigint")
     */
    protected $Quantity;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblItem;

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

    /**
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return bool|InventoryTblItem
     */
    public function getServiceTblItem()
    {

        if (null === $this->serviceTblItem) {
            return false;
        } else {
            return Item::useService()->getItemById($this->serviceTblItem);
        }
    }

    /**
     * @param InventoryTblItem|null $tblItem
     */
    public function setServiceTblItem(InventoryTblItem $tblItem = null)
    {

        $this->serviceTblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }
}