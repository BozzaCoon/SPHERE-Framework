<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Latte;

use Nette;

/**
 * HTML element node.
 *
 * @author     David Grudl
 */
class HtmlNode extends Nette\Object
{

    /** @var string */
    public $name;

    /** @var bool */
    public $isEmpty = false;

    /** @var array */
    public $attrs = array();

    /** @var array */
    public $macroAttrs = array();

    /** @var bool */
    public $closing = false;

    /** @var string */
    public $attrCode;

    /** @var int */
    public $offset;


    public function __construct($name)
    {

        $this->name = $name;
    }

}
