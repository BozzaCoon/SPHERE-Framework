<?php
namespace SPHERE\Application\Api\People;

use SPHERE\Application\Api\People\Meta\Agreement\ApiAgreementReadOnly;
use SPHERE\Application\Api\People\Meta\MedicalRecord\MedicalRecordReadOnly;
use SPHERE\Application\Api\People\Meta\Student\ApiStudent;
use SPHERE\Application\Api\People\Meta\Support\ApiSupport;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Api\People\Person\ApiFamilyEdit;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\People
 */
class Person implements IApplicationInterface
{

    public static function registerApplication()
    {
        ApiStudent::registerApi();
        ApiSupport::registerApi();
        ApiSupportReadOnly::registerApi();
        MedicalRecordReadOnly::registerApi();
        ApiAgreementReadOnly::registerApi();
        ApiPersonEdit::registerApi();
        ApiPersonReadOnly::registerApi();
        ApiFamilyEdit::registerApi();
    }
}