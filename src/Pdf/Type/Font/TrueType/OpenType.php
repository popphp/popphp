<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Pdf\Type\Font\TrueType;

/**
 * OpenType font class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class OpenType extends \Pop\Pdf\Type\Font\TrueType
{

    /**
     * Constructor
     *
     * Instantiate a OpenType font file object based on a pre-existing font file on disk.
     *
     * @param  string $font
     * @return OpenType
     */
    public function __construct($font)
    {
        parent::__construct($font);
    }

    /**
     * Method to parse the required tables of the OpenType font file.
     *
     * @return void
     */
    protected function parseRequiredTables()
    {
        // OS/2
        if (isset($this->tableInfo['OS/2'])) {
            $this->tables['OS/2'] = new Table\Os2($this);

            $this->flags->isSerif = $this->tables['OS/2']->flags->isSerif;
            $this->flags->isScript = $this->tables['OS/2']->flags->isScript;
            $this->flags->isSymbolic = $this->tables['OS/2']->flags->isSymbolic;
            $this->flags->isNonSymbolic = $this->tables['OS/2']->flags->isNonSymbolic;
            $this->capHeight = $this->tables['OS/2']->capHeight;
        }
    }

}
