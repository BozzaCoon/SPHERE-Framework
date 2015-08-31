<?php
namespace SPHERE\Common\Frontend\Message\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Message\IMessageInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Danger
 *
 * @package SPHERE\Common\Frontend\Message\Repository
 */
class Danger extends Extension implements IMessageInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param string         $Content
     * @param IIconInterface $Icon
     */
    public function __construct($Content, IIconInterface $Icon = null)
    {

        $this->Template = $this->getTemplate(__DIR__.'/Message.twig');
        $this->Template->setVariable('Type', 'danger');
        $this->Template->setVariable('Content', $Content);
        if (null !== $Icon) {
            $this->Template->setVariable('Icon', $Icon);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getName()
    {

        return null;
    }
}
