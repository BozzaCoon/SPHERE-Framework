<?php
namespace SPHERE\Application\Education\Certificate\Generator\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;

class Page
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /** @var array $Slices */
    private $Slices = array();

    /**
     * Document constructor.
     */
    public function __construct()
    {

        $this->Template = Template::getTwigTemplateString('<div class="Page">{{ Slices }}</div>');
    }

    /**
     * @param Slice $Slice
     *
     * @return $this
     */
    public function addSlice(Slice $Slice)
    {

        $this->Slices[] = $Slice;
        return $this;
    }

    /**
     * @param array $SliceArray
     * @return $this
     */
    public function addSliceArray($SliceArray)
    {

        foreach ($SliceArray as $Slice){
            $this->Slices[] = $Slice;
        }

        return $this;
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

        $this->Template->setVariable('Slices', implode("\n", $this->Slices));
        return $this->Template->getContent();
    }
}
