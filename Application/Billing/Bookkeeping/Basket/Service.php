<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Setup;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Basket
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function getBasketById($Id)
    {

        return (new Data($this->getBinding()))->getBasketById($Id);
    }

    /**
     * @param $Name
     *
     * @return bool|TblBasket
     */
    public function getBasketByName($Name)
    {

        return (new Data($this->getBinding()))->getBasketByName($Name);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketItem
     */
    public function getBasketItemById($Id)
    {

        return (new Data($this->getBinding()))->getBasketItemById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketVerification
     */
    public function getBasketVerificationById($Id)
    {

        return (new Data($this->getBinding()))->getBasketVerificationById($Id);
    }

    /**
     * @return bool|TblBasket[]
     */
    public function getBasketAll()
    {

        return (new Data($this->getBinding()))->getBasketAll();
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblItem[]
     */
    public function getItemAllByBasket(TblBasket $tblBasket)
    {

        $tblBasketItemList = $this->getBasketItemAllByBasket($tblBasket);
        $tblItemList = array();
        if ($tblBasketItemList) {
            foreach ($tblBasketItemList as $tblBasketItem) {
                if(($tblItem = $tblBasketItem->getServiceTblItem())){
                    $tblItemList[] = $tblItem;
                }
            }

        }
        return ( empty( $tblItemList ) ? false : $tblItemList );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketItemAllByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketItem[]
     */
    public function getBasketItemByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketItemByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketVerificationAllByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|\SPHERE\System\Database\Fitting\Element
     */
    public function countDebtorSelectionCountByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->countDebtorSelectionCountByBasket($tblBasket);
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblBasket
     */
    public function createBasket($Name = '', $Description = '')
    {

        return (new Data($this->getBinding()))->createBasket($Name, $Description);
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return TblBasketItem
     */
    public function createBasketItem(TblBasket $tblBasket, TblItem $tblItem)
    {
        return (new Data($this->getBinding()))->createBasketItem($tblBasket, $tblItem);
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return bool
     */
    public function createBasketVerificationBulk(TblBasket $tblBasket, TblItem $tblItem)
    {

        $tblGroupList = array();
        if(($tblItemGroupList = Item::useService()->getItemGroupByItem($tblItem))){
            foreach($tblItemGroupList as $tblItemGroup){
                $tblGroupList[] = $tblItemGroup->getServiceTblGroup();
            }
        }
        $tblPersonList = array();
        if(!empty($tblGroupList)){
            foreach($tblGroupList as $tblGroup){
                if($tblPersonFromGroup = Group::useService()->getPersonAllByGroup($tblGroup)){
                    foreach($tblPersonFromGroup as $tblPersonFrom){
                        $tblPersonList[] = $tblPersonFrom;
                    }
                }
            }
        }

        $DebtorDataArray = array();
        if(!empty($tblPersonList)){
            /** @var TblPerson $tblPerson */
            foreach($tblPersonList as $tblPerson){
                if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson, $tblItem))){
                    foreach($tblDebtorSelectionList as $tblDebtorSelection) {
                        $Error = false;
                        $Item = array();
                        if (!$tblDebtorSelection->getServiceTblPersonCauser()) {
                            //BasketVerification doesn't work without Causer
                            $Item['Causer'] = '';
                            $Error = true;
                        } else {
                            $Item['Causer'] = $tblDebtorSelection->getServiceTblPersonCauser()->getId();
                        }
                        if (!$tblDebtorSelection->getServiceTblPerson()) {
                            $Item['Debtor'] = '';
                        } else {
                            $Item['Debtor'] = $tblDebtorSelection->getServiceTblPerson()->getId();
                        }
                        // insert payment from DebtorSelection
                        if(!$tblDebtorSelection->getTblBankAccount()){
                            $Item['BankAccount'] = null;
                        } else {
                            $Item['BankAccount'] = $tblDebtorSelection->getTblBankAccount()->getId();
                        }
                        if(!$tblDebtorSelection->getTblBankReference()){
                            $Item['BankReference'] = null;
                        } else {
                            $Item['BankReference'] = $tblDebtorSelection->getTblBankReference()->getId();
                        }
                        $Item['PaymentType'] = $tblDebtorSelection->getServiceTblPaymentType()->getId();
                        // default special price value
                        $Item['Price'] = $tblDebtorSelection->getValue();
                        // change to selected variant
                        if (($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())) {
                            if (($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))) {
                                $Item['Price'] = $tblItemCalculation->getValue();
                            }
                        }
                        if(!$Error){
                            array_push($DebtorDataArray, $Item);
                        }
                    }
                } else {
                    // entry without DebtorSelection
                    $Item['Causer'] = $tblPerson->getId();
                    $Item['Debtor'] = '';
                    $Item['BankAccount'] = null;
                    $Item['BankReference'] = null;
                    $Item['PaymentType'] = null;
                    // default special price value
                    $Item['Price'] = '0.00';
                    array_push($DebtorDataArray, $Item);
                }
            }
        }
        if(!empty($DebtorDataArray)){
            return (new Data($this->getBinding()))->createBasketVerificationBulk($tblBasket, $tblItem, $DebtorDataArray);
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param string    $Name
     * @param string    $Description
     *
     * @return IFormInterface|string
     */
    public function changeBasket(TblBasket $tblBasket, $Name, $Description)
    {

        return (new Data($this->getBinding()))->updateBasket($tblBasket, $Name, $Description);
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param string                $Price
     * @param string                $Quantity
     *
     * @return bool
     */
    public function changeBasketVerification(TblBasketVerification $tblBasketVerification, $Price, $Quantity)
    {

        return (new Data($this->getBinding()))->updateBasketVerification($tblBasketVerification, $Price, $Quantity);
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param TblPerson             $tblPersonDebtor
     * @param TblPaymentType        $tblPaymentType
     * @param TblBankAccount|null   $tblBankAccount
     * @param TblBankReference|null $tblBankReference
     * @param string                $Value
     *
     * @return bool
     */
    public function changeBasketVerificationDebtor(TblBasketVerification $tblBasketVerification, TblPerson $tblPersonDebtor,
        TblPaymentType $tblPaymentType, TblBankAccount $tblBankAccount = null, TblBankReference $tblBankReference = null,
        $Value = '')
    {

        return (new Data($this->getBinding()))->updateBasketVerificationDebtor($tblBasketVerification, $tblPersonDebtor,
        $tblPaymentType, $tblBankAccount, $tblBankReference, $Value);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasket(TblBasket $tblBasket)
    {

        // remove all BasketItem
        $this->destroyBasketItemBulk($tblBasket);
        // remove all BasketVerification
        $this->destroyBasketVerificationBulk($tblBasket);
        
        return (new Data($this->getBinding()))->destroyBasket($tblBasket);
    }

    /**
     * @param TblBasketItem $tblBasketItem
     *
     * @return string
     */
    public function destroyBasketItem(TblBasketItem $tblBasketItem)
    {

        return (new Data($this->getBinding()))->destroyBasketItem($tblBasketItem);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasketItemBulk(TblBasket $tblBasket)
    {

        $BasketItemIdList = array();
        if(($tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket))){
            foreach($tblBasketItemList as $tblBasketItem){
                $BasketItemIdList[$tblBasketItem->getId()] = $tblBasketItem->getId();
            }
        }
        return (new Data($this->getBinding()))->destroyBasketItemBulk($BasketItemIdList);
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return string
     */
    public function destroyBasketVerification(TblBasketVerification $tblBasketVerification)
    {

        return (new Data($this->getBinding()))->destroyBasketVerification($tblBasketVerification);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasketVerificationBulk(TblBasket $tblBasket)
    {

        $BasketVerificationIdList = array();
        if(($tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket))){
            foreach($tblBasketVerificationList as $tblBasketVerification){
                $BasketVerificationIdList[$tblBasketVerification->getId()] = $tblBasketVerification->getId();
            }
        }
        return (new Data($this->getBinding()))->destroyBasketVerificationBulk($BasketVerificationIdList);
    }
}
