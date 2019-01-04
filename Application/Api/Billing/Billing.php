<?php
namespace SPHERE\Application\Api\Billing;

use SPHERE\Application\Api\Billing\Accounting\ApiBankAccount;
use SPHERE\Application\Api\Billing\Accounting\ApiBankReference;
use SPHERE\Application\Api\Billing\Accounting\ApiCreditor;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtor;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtorSelection;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasket;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasketVerification;
use SPHERE\Application\Api\Billing\Inventory\ApiItem;
use SPHERE\Application\Api\Billing\Inventory\ApiSetting;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Reporting
 *
 * @package SPHERE\Application\Api\Billing
 */
class Billing implements IApplicationInterface
{

    public static function registerApplication()
    {

//        Invoice::registerModule();
        ApiSetting::registerApi();
        ApiItem::registerApi();
        ApiCreditor::registerApi();
        ApiDebtor::registerApi();
        ApiDebtorSelection::registerApi();
        ApiBankAccount::registerApi();
        ApiBankReference::registerApi();
        ApiBasket::registerApi();
        ApiBasketVerification::registerApi();
    }
}
