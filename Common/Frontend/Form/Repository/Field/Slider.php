<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractTextField;
use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class Slider
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class Slider extends AbstractTextField implements IFieldInterface
{

    /**
     * @param string         $Name
     * @param null|string    $Placeholder
     * @param null|string    $Label
     * @param IIconInterface $Icon
     */
    public function __construct(
        $Name,
        $Placeholder = '',
        $Label = '',
        IIconInterface $Icon = null
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/Slider.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementPlaceholder', $Placeholder);
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
    }

}
