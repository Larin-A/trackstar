<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Pdf_Exception */
require_once 'Zend/Pdf/Exception.php';

/** Zend_Pdf_Resource_Font */
require_once 'Zend/Pdf/Resource/Font.php';

/** Zend_Pdf_Style */
require_once 'Zend/Pdf/Style.php';

/** Zend_Pdf_Element_Dictionary */
require_once 'Zend/Pdf/Element/Dictionary.php';

/** Zend_Pdf_Element_Reference */
require_once 'Zend/Pdf/Element/Reference.php';

/** Zend_Pdf_ElementFactory */
require_once 'Zend/Pdf/ElementFactory.php';

/** Zend_Pdf_Color */
require_once 'Zend/Pdf/Color.php';

/** Zend_Pdf_Color_GrayScale */
require_once 'Zend/Pdf/Color/GrayScale.php';

/** Zend_Pdf_Color_Rgb */
require_once 'Zend/Pdf/Color/Rgb.php';

/** Zend_Pdf_Color_Cmyk */
require_once 'Zend/Pdf/Color/Cmyk.php';

/**
 * PDF Page
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Page
{
  /**** Class Constants ****/


  /* Page Sizes */

    /**
     * Size representing an A4 page in portrait (tall) orientation.
     */
    const SIZE_A4                = '595:842:';

    /**
     * Size representing an A4 page in landscape (wide) orientation.
     */
    const SIZE_A4_LANDSCAPE      = '842:595:';

    /**
     * Size representing a US Letter page in portrait (tall) orientation.
     */
    const SIZE_LETTER            = '612:792:';

    /**
     * Size representing a US Letter page in landscape (wide) orientation.
     */
    const SIZE_LETTER_LANDSCAPE  = '792:612:';


  /* Shape Drawing */

    /**
     * Stroke the path only. Do not fill.
     */
    const SHAPE_DRAW_STROKE      = 0;

    /**
     * Fill the path only. Do not stroke.
     */
    const SHAPE_DRAW_FILL        = 1;

    /**
     * Fill and stroke the path.
     */
    const SHAPE_DRAW_FILL_AND_STROKE = 2;


  /* Shape Filling Methods */

    /**
     * Fill the path using the non-zero winding rule.
     */
    const FILL_METHOD_NON_ZERO_WINDING = 0;

    /**
     * Fill the path using the even-odd rule.
     */
    const FILL_METHOD_EVEN_ODD        = 1;


  /* Line Dash Types */

    /**
     * Solid line dash.
     */
    const LINE_DASHING_SOLID = 0;



    /**
     * Reference to the object with page dictionary.
     *
     * @var Zend_Pdf_Element_Reference
     */
    private $_pageDictionary;

    /**
     * PDF objects factory.
     *
     * @var Zend_Pdf_ElementFactory_Interface
     */
    private $_objFactory = null;

    /**
     * Flag which signals, that page is created separately from any PDF document or
     * attached to anyone.
     *
     * @var boolean
     */
    private $_attached;

    /**
     * Stream of the drawing instractions.
     *
     * @var string
     */
    private $_contents = '';

    /**
     * Current style
     *
     * @var Zend_Pdf_Style
     */
    private $_style = null;

    /**
     * Counter for the "Save" operations
     *
     * @var integer
     */
    private $_saveCount = 0;

    /**
     * Safe Graphics State semafore
     * 
     * If it's false, than we can't be sure Graphics State is restored withing 
     * context of previous contents stream (ex. drawing coordinate system may be rotated).
     * We should encompass existing content with save/restore GS operators
     * 
     * @var boolean 
     */
    private $_safeGS;

    /**
     * Current font
     *
     * @var Zend_Pdf_Resource_Font
     */
    private $_font = null;

    /**
     * Current font size
     *
     * @var float
     */
    private $_fontSize;

    /**
     * Object constructor.
     * Constructor signatures:
     *
     * 1. Load PDF page from a parsed PDF file.
     *    Object factory is created by PDF parser.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(Zend_Pdf_Element_Dictionary       $pageDict,
     *                   Zend_Pdf_ElementFactory_Interface $factory);
     * ---------------------------------------------------------
     *
     * 2. Clone PDF page.
     *    New page is created in the same context as source page. Object factory is shared.
     *    Thus it will be attached to the document, but need to be placed into Zend_Pdf::$pages array
     *    to be included into output.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(Zend_Pdf_Page $page);
     * ---------------------------------------------------------
     *
     * 3. Create new page with a specified pagesize.
     *    If $factory is null then it will be created and page must be attached to the document to be
     *    included into output.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(string $pagesize, Zend_Pdf_ElementFactory_Interface $factory = null);
     * ---------------------------------------------------------
     *
     * 4. Create new page with a specified pagesize (in default user space units).
     *    If $factory is null then it will be created and page must be attached to the document to be
     *    included into output.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(numeric $width, numeric $height, Zend_Pdf_ElementFactory_Interface $factory = null);
     * ---------------------------------------------------------
     *
     *
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     * @throws Zend_Pdf_Exception
     */
    public function __construct($param1, $param2 = null, $param3 = null)
    {
        if ($param1 instanceof Zend_Pdf_Element_Reference &&
            $param1->getType() == Zend_Pdf_Element::TYPE_DICTIONARY &&
            $param2 instanceof Zend_Pdf_ElementFactory_Interface &&
            $param3 === null
           ) {
            $this->_pageDictionary = $param1;
            $this->_objFactory     = $param2;
            $this->_attached       = true;
            $this->_safeGS         = false;

            return;

        } else if ($param1 instanceof Zend_Pdf_Page && $param2 === null && $param3 === null) {
            // Clone existing page.
            // Let already existing content and resources to be shared between pages
            // We don't give existing content modification functionality, so we don't need "deep copy"
            $this->_objFactory = $param1->_objFactory;
            $this->_attached   = &$param1->_attached;
            $this->_safeGS     = false;
            
            $this->_pageDictionary = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());

            foreach ($param1->_pageDictionary->getKeys() as $key) {
                if ($key == 'Contents') {
                    // Clone Contents property

                    $this->_pageDictionary->Contents = new Zend_Pdf_Element_Array();

                    if ($param1->_pageDictionary->Contents->getType() != Zend_Pdf_Element::TYPE_ARRAY) {
                        // Prepare array of content streams and add existing stream
                        $this->_pageDictionary->Contents->items[] = $param1->_pageDictionary->Contents;
                    } else {
                        // Clone array of the content streams
                        foreach ($param1->_pageDictionary->Contents->items as $srcContentStream) {
                            $this->_pageDictionary->Contents->items[] = $srcContentStream;
                        }
                    }
                } else {
                    $this->_pageDictionary->$key = $param1->_pageDictionary->$key;
                }
            }

            return;
        } else if (is_string($param1) &&
                   ($param2 === null || $param2 instanceof Zend_Pdf_ElementFactory_Interface) &&
                   $param3 === null) {
            $this->_objFactory = ($param2 !== null)? $param2 : Zend_Pdf_ElementFactory::createFactory(1);
            $this->_attached   = false;
            $this->_safeGS     = true; /** New page created. That's users App responsibility to track GS changes */

            switch (strtolower($param1)) {
                case 'a4':
                    $param1 = Zend_Pdf_Page::SIZE_A4;
                    break;
                case 'a4-landscape':
                    $param1 = Zend_Pdf_Page::SIZE_A4_LANDSCAPE;
                    break;
                case 'letter':
                    $param1 = Zend_Pdf_Page::SIZE_LETTER;
                    break;
                case 'letter-landscape':
                    $param1 = Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE;
                    break;
                default:
                    // should be in "x:y" form
            }

            $pageDim = explode(':', $param1);
            if(count($pageDim) == 3) {
                $pageWidth  = $pageDim[0];
                $pageHeight = $pageDim[1];
            } else {
                /**
                 * @todo support of user defined pagesize notations, like:
                 *       "210x297mm", "595x842", "8.5x11in", "612x792"
                 */
                throw new Zend_Pdf_Exception('Wrong pagesize notation.');
            }
            /**
             * @todo support of pagesize recalculation to "default user space units"
             */

        } else if (is_numeric($param1) && is_numeric($param2) &&
                   ($param3 === null || $param3 instanceof Zend_Pdf_ElementFactory_Interface)) {
            $this->_objFactory = ($param3 !== null)? $param3 : Zend_Pdf_ElementFactory::createFactory(1);
            $this->_attached = false;
            $this->_safeGS     = true; /** New page created. That's users App responsibility to track GS changes */
            $pageWidth  = $param1;
            $pageHeight = $param2;

        } else {
            throw new Zend_Pdf_Exception('Unrecognized method signature, wrong number of arguments or wrong argument types.');
        }

        $this->_pageDictionary = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());
        $this->_pageDictionary->Type         = new Zend_Pdf_Element_Name('Page');
        $this->_pageDictionary->LastModified = new Zend_Pdf_Element_String(Zend_Pdf::pdfDate());
        $this->_pageDictionary->Resources    = new Zend_Pdf_Element_Dictionary();
        $this->_pageDictionary->MediaBox     = new Zend_Pdf_Element_Array();
        $this->_pageDictionary->MediaBox->items[] = new Zend_Pdf_Element_Numeric(0);
        $this->_pageDictionary->MediaBox->items[] = new Zend_Pdf_Element_Numeric(0);
        $this->_pageDictionary->MediaBox->items[] = new Zend_Pdf_Element_Numeric($pageWidth);
        $this->_pageDictionary->MediaBox->items[] = new Zend_Pdf_Element_Numeric($pageHeight);
        $this->_pageDictionary->Contents     = new Zend_Pdf_Element_Array();
    }


    /**
     * Clone operator
     *
     * @throws Zend_Pdf_Exception
     */
    public function __clone()
    {
        throw new Zend_Pdf_Exception('Cloning Zend_Pdf_Page object using \'clone\' keyword is not supported. Use \'new Zend_Pdf_Page($srcPage)\' syntax');
    }

    /**
     * Attach resource to the page
     *
     * @param string $type
     * @param Zend_Pdf_Resource $resource
     * @return string
     */
    private function _attachResource($type, Zend_Pdf_Resource $resource)
    {
        // Check that Resources dictionary contains appropriate resource set
        if ($this->_pageDictionary->Resources->$type === null) {
            $this->_pageDictionary->Resources->touch();
            $this->_pageDictionary->Resources->$type = new Zend_Pdf_Element_Dictionary();
        } else {
            $this->_pageDictionary->Resources->$type->touch();
        }

        // Check, that resource is already attached to resource set.
        $resObject = $resource->getResource();
        foreach ($this->_pageDictionary->Resources->$type->getKeys() as $ResID) {
            if ($this->_pageDictionary->Resources->$type->$ResID === $resObject) {
                return $ResID;
            }
        }

        $idCounter = 1;
        do {
            $newResName = $type[0] . $idCounter++;
        } while ($this->_pageDictionary->Resources->$type->$newResName !== null);

        $this->_pageDictionary->Resources->$type->$newResName = $resObject;
        $this->_objFactory->attach($resource->getFactory());

        return $newResName;
    }

    /**
     * Add procedureSet to the Page description
     *
     * @param string $procSetName
     */
    private function _addProcSet($procSetName)
    {
        // Check that Resources dictionary contains ProcSet entry
        if ($this->_pageDictionary->Resources->ProcSet === null) {
            $this->_pageDictionary->Resources->touch();
            $this->_pageDictionary->Resources->ProcSet = new Zend_Pdf_Element_Array();
        } else {
            $this->_pageDictionary->Resources->ProcSet->touch();
        }

        foreach ($this->_pageDictionary->Resources->ProcSet->items as $procSetEntry) {
            if ($procSetEntry->value == $procSetName) {
                // Procset is already included into a ProcSet array
                return;
            }
        }

        $this->_pageDictionary->Resources->ProcSet->items[] = new Zend_Pdf_Element_Name($procSetName);
    }

    /**
     * Retrive PDF file reference to the page
     *
     * @return Zend_Pdf_Element_Dictionary
     */
    public function getPageDictionary()
    {
        return $this->_pageDictionary;
    }

    /**
     * Dump current drawing instructions into the content stream.
     *
     * @todo Don't forget to close all current graphics operations (like path drawing)
     *
     * @throws Zend_Pdf_Exception
     */
    public function flush()
    {
        if ($this->_saveCount != 0) {
            throw new Zend_Pdf_Exception('Saved graphics state is not restored');
        }

        if ($this->_contents == '') {
            return;
        }

        if ($this->_pageDictionary->Contents->getType() != Zend_Pdf_Element::TYPE_ARRAY) {
            /**
             * It's a stream object.
             * Prepare Contents page attribute for update.
             */
            $this->_pageDictionary->touch();

            $currentPageContents = $this->_pageDictionary->Contents;
            $this->_pageDictionary->Contents = new Zend_Pdf_Element_Array();
            $this->_pageDictionary->Contents->items[] = $currentPageContents;
        } else {
            $this->_pageDictionary->Contents->touch();
        }

        if ((!$this->_safeGS)  &&  (count($this->_pageDictionary->Contents->items) != 0)) {
        	/**
        	 * Page already has some content which is not treated as safe.
        	 * 
        	 * Add save/restore GS operators
        	 */
            $this->_addProcSet('PDF');
        	
        	$newContentsArray = new Zend_Pdf_Element_Array();
        	$newContentsArray->items[] = $this->_objFactory->newStreamObject(" q\n");
        	foreach ($this->_pageDictionary->Contents->items as $contentStream) {
        		$newContentsArray->items[] = $contentStream;
        	}
            $newContentsArray->items[] = $this->_objFactory->newStreamObject(" Q\n");

        	$this->_pageDictionary->touch();
        	$this->_pageDictionary->Contents = $newContentsArray;
        	
        	$this->_safeGS = true;
        }
        
        $this->_pageDictionary->Contents->items[] =
                $this->_objFactory->newStreamObject($this->_contents);

        $this->_contents = '';
    }

    /**
     * Prepare page to be rendered into PDF.
     *
     * @todo Don't forget to close all current graphics operations (like path drawing)
     *
     * @param Zend_Pdf_ElementFactory_Interface $objFactory
     * @throws Zend_Pdf_Exception
     */
    public function render(Zend_Pdf_ElementFactory_Interface $objFactory)
    {
        $this->flush();

        if ($objFactory === $this->_objFactory) {
            // Page is already attached to the document.
            return;
        }

        if ($this->_attached) {
            throw new Zend_Pdf_Exception('Page is attached to one documen, but rendered in context of another.');
            /**
             * @todo Page cloning must be implemented here instead of exception.
             *       PDF objects (ex. fonts) can be shared between pages.
             *       Thus all referenced objects, which can be modified, must be cloned recursively,
             *       to avoid producing wrong object references in a context of source PDF.
             */

            //...
        } else {
            $objFactory->attach($this->_objFactory);
        }

    }



    /**
     * Set fill color.
     *
     * @param Zend_Pdf_Color $color
     */
    public function setFillColor(Zend_Pdf_Color $color)
    {
        $this->_addProcSet('PDF');
        $this->_contents .= $color->instructions(false);
    }

    /**
     * Set line color.
     *
     * @param Zend_Pdf_Color $color
     */
    public function setLineColor(Zend_Pdf_Color $color)
    {
        $this->_addProcSet('PDF');
        $this->_contents .= $color->instructions(true);
    }

    /**
     * Set line width.
     *
     * @param float $width
     */
    public function setLineWidth($width)
    {
        $this->_addProcSet('PDF');
        $widthObj = new Zend_Pdf_Element_Numeric($width);
        $this->_contents .= $widthObj->toString() . " w\n";
    }

    /**
     * Set line dashing pattern
     *
     * Pattern is an array of floats: array(on_length, off_length, on_length, off_length, ...)
     * Phase is shift from the beginning of line.
     *
     * @param array $pattern
     * @param array $phase
     */
    public function setLineDashingPattern($pattern, $phase = 0)
    {
        $this->_addProcSet('PDF');

        if ($pattern === Zend_Pdf_Page::LINE_DASHING_SOLID) {
            $pattern = array();
            $phase   = 0;
        }

        $dashPattern  = new Zend_Pdf_Element_Array();
        $phaseEleemnt = new Zend_Pdf_Element_Numeric($phase);

        foreach ($pattern as $dashItem) {
            $dashElement = new Zend_Pdf_Element_Numeric($dashItem);
            $dashPattern->items[] = $dashElement;
        }

        $this->_contents .= $dashPattern->toString() . ' '
                         . $phaseEleemnt->toString() . " d\n";
    }

    /**
     * Set current font.
     *
     * @param Zend_Pdf_Resource_Font $font
     * @param float $fontSize
     */
    public function setFont(Zend_Pdf_Resource_Font $font, $fontSize)
    {
        $this->_addProcSet('Text');
        $fontName = $this->_attachResource('Font', $font);

        $this->_font     = $font;
        $this->_fontSize = $fontSize;

        $fontNameObj = new Zend_Pdf_Element_Name($fontName);
        $fontSizeObj = new Zend_Pdf_Element_Numeric($fontSize);
        $this->_contents .= $fontNameObj->toString() . ' ' . $fontSizeObj->toString() . " Tf\n";
    }

    /**
     * Set the style to use for future drawing operations on this page
     *
     * @param Zend_Pdf_Style $style
     */
    public function setStyle(Zend_Pdf_Style $style)
    {
        $this->_style = $style;

        $this->_addProcSet('Text');
        $this->_addProcSet('PDF');
        if ($style->getFont() !== null) {
            $this->setFont($style->getFont(), $style->getFontSize());
        }
        $this->_contents .= $style->instructions($this->_pageDictionary->Resources);
    }


    /**
     * Get current font.
     *
     * @return Zend_Pdf_Resource_Font $font
     */
    public function getFont()
    {
        return $this->_font;
    }

    /**
     * Get current font size
     *
     * @return float $fontSize
     */
    public function getFontSize()
    {
        return $this->_fontSize;
    }

    /**
     * Return the style, applied to the page.
     *
     * @return Zend_Pdf_Style|null
     */
    public function getStyle()
    {
        return $this->_style;
    }


    /**
     * Save the graphics state of this page.
     * This takes a snapshot of the currently applied style, position, clipping area and
     * any rotation/translation/scaling that has been applied.
     *
     * @todo check for the open paths
     * @throws Zend_Pdf_Exception    - if a save is performed with an open path
     */
    public function saveGS()
    {
        $this->_saveCount++;

        $this->_addProcSet('PDF');
        $this->_contents .= " q\n";
    }

    /**
     * Restore the graphics state that was saved with the last call to saveGS().
     *
     * @throws Zend_Pdf_Exception   - if there is no previously saved state
     */
    public function restoreGS()
    {
        if ($this->_saveCount-- <= 0) {
            throw new Zend_Pdf_Exception('Restoring graphics state which is not saved');
        }
        $this->_contents .= " Q\n";
    }


    /**
     * Intersect current clipping area with a circle.
     *
     * @param float $x
     * @param float $y
     * @param float $radius
     * @param float $startAngle
     * @param float $endAngle
     */
    public function clipCircle($x, $y, $radius, $startAngle = null, $endAngle = null)
    {
        $this->clipEllipse($x - $radius, $y - $radius,
                           $x + $radius, $y + $radius,
                           $startAngle, $endAngle);
    }

    /**
     * Intersect current clipping area with a polygon.
     *
     * Method signatures:
     * drawEllipse($x1, $y1, $x2, $y2);
     * drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle);
     *
     * @todo process special cases with $x2-$x1 == 0 or $y2-$y1 == 0
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $startAngle
     * @param float $endAngle
     */
    public function clipEllipse($x1, $y1, $x2, $y2, $startAngle = null, $endAngle = null)
    {
        $this->_addProcSet('PDF');

        if ($x2 < $x1) {
            $temp = $x1;
            $x1   = $x2;
            $x2   = $temp;
        }
        if ($y2 < $y1) {
            $temp = $y1;
            $y1   = $y2;
            $y2   = $temp;
        }

        $x = ($x1 + $x2)/2.;
        $y = ($y1 + $y2)/2.;

        $xC = new Zend_Pdf_Element_Numeric($x);
        $yC = new Zend_Pdf_Element_Numeric($y);

        if ($startAngle !== null) {
            if ($startAngle != 0) { $startAngle = fmod($startAngle, M_PI*2); }
            if ($endAngle   != 0) { $endAngle   = fmod($endAngle,   M_PI*2); }

            if ($startAngle > $endAngle) {
                $endAngle += M_PI*2;
            }

            $clipPath    = $xC->toString() . ' ' . $yC->toString() . " m\n";
            $clipSectors = (int)ceil(($endAngle - $startAngle)/M_PI_4);
            $clipRadius  = max($x2 - $x1, $y2 - $y1);

            for($count = 0; $count <= $clipSectors; $count++) {
                $pAngle = $startAngle + ($endAngle - $startAngle)*$count/(float)$clipSectors;

                $pX = new Zend_Pdf_Element_Numeric($x + cos($pAngle)*$clipRadius);
                $pY = new Zend_Pdf_Element_Numeric($y + sin($pAngle)*$clipRadius);
                $clipPath .= $pX->toString() . ' ' . $pY->toString() . " l\n";
            }

            $this->_contents .= $clipPath . "h\nW\nn\n";
        }

        $xLeft  = new Zend_Pdf_Element_Numeric($x1);
        $xRight = new Zend_Pdf_Element_Numeric($x2);
        $yUp    = new Zend_Pdf_Element_Numeric($y2);
        $yDown  = new Zend_Pdf_Element_Numeric($y1);

        $xDelta  = 2*(M_SQRT2 - 1)*($x2 - $x1)/3.;
        $yDelta  = 2*(M_SQRT2 - 1)*($y2 - $y1)/3.;
        $xr = new Zend_Pdf_Element_Numeric($x + $xDelta);
        $xl = new Zend_Pdf_Element_Numeric($x - $xDelta);
        $yu = new Zend_Pdf_Element_Numeric($y + $yDelta);
        $yd = new Zend_Pdf_Element_Numeric($y - $yDelta);

        $this->_contents .= $xC->toString() . ' ' . $yUp->toString() . " m\n"
                         .  $xr->toString() . ' ' . $yUp->toString() . ' '
                         .    $xRight->toString() . ' ' . $yu->toString() . ' '
                         .      $xRight->toString() . ' ' . $yC->toString() . " c\n"
                         .  $xRight->toString() . ' ' . $yd->toString() . ' '
                         .    $xr->toString() . ' ' . $yDown->toString() . ' '
                         .      $xC->toString() . ' ' . $yDown->toString() . " c\n"
                         .  $xl->toString() . ' ' . $yDown->toString() . ' '
                         .    $xLeft->toString() . ' ' . $yd->toString() . ' '
                         .      $xLeft->toString() . ' ' . $yC->toString() . " c\n"
                         .  $xLeft->toString() . ' ' . $yu->toString() . ' '
                         .    $xl->toString() . ' ' . $yUp->toString() . ' '
                         .      $xC->toString() . ' ' . $yUp->toString() . " c\n"
                         .  "h\nW\nn\n";
    }


    /**
     * Intersect current clipping area with a polygon.
     *
     * @param array $x  - array of float (the X co-ordinates of the vertices)
     * @param array $y  - array of float (the Y co-ordinates of the vertices)
     * @param integer $fillMethod
     */
    public function clipPolygon($x, $y, $fillMethod = Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING)
    {
        $this->_addProcSet('PDF');

        $firstPoint = true;
        foreach ($x as $id => $xVal) {
            $xObj = new Zend_Pdf_Element_Numeric($xVal);
            $yObj = new Zend_Pdf_Element_Numeric($y[$id]);

            if ($firstPoint) {
                $path = $xObj->toString() . ' ' . $yObj->toString() . " m\n";
                $firstPoint = false;
            } else {
                $path .= $xObj->toString() . ' ' . $yObj->toString() . " l\n";
            }
        }

        $this->_contents .= $path;

        if ($fillMethod == Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING) {
            $this->_contents .= " h\n W\n";
        } else {
            // Even-Odd fill method.
            $this->_contents .= " h\n W*\n";
        }
    }

    /**
     * Intersect current clipping area with a rectangle.
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     */
    public function clipRectangle($x1, $y1, $x2, $y2)
    {
        $this->_addProcSet('PDF');

        $x1Obj      = new Zend_Pdf_Element_Numeric($x1);
        $y1Obj      = new Zend_Pdf_Element_Numeric($y1);
        $widthObj   = new Zend_Pdf_Element_Numeric($x2 - $x1);
        $height2Obj = new Zend_Pdf_Element_Numeric($y2 - $y1);

        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                         .      $widthObj->toString() . ' ' . $height2Obj->toString() . " re\n"
                         .  " W\n";
    }

    /**
     * Draw a Zend_Pdf_ContentStream at the specified position on the page
     *
     * @param ZPdfContentStream $cs
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     */
    public function drawContentStream($cs, $x1, $y1, $x2, $y2)
    {
    }

    /**
     * Draw a circle centered on x, y with a radius of radius.
     *
     * Method signatures:
     * drawCircle($x, $y, $radius);
     * drawCircle($x, $y, $radius, $fillType);
     * drawCircle($x, $y, $radius, $startAngle, $endAngle);
     * drawCircle($x, $y, $radius, $startAngle, $endAngle, $fillType);
     *
     *
     * It's not a really circle, because PDF supports only cubic Bezier curves.
     * But _very_ good approximation.
     * It differs from a real circle on a maximum 0.00026 radiuses
     * (at PI/8, 3*PI/8, 5*PI/8, 7*PI/8, 9*PI/8, 11*PI/8, 13*PI/8 and 15*PI/8 angles).
     * At 0, PI/4, PI/2, 3*PI/4, PI, 5*PI/4, 3*PI/2 and 7*PI/4 it's exactly a tangent to a circle.
     *
     * @param float $x
     * @param float $y
     * @param float $radius
     * @param mixed $param4
     * @param mixed $param5
     * @param mixed $param6
     */
    public function  drawCircle($x, $y, $radius, $param4 = null, $param5 = null, $param6 = null)
    {
        $this->drawEllipse($x - $radius, $y - $radius,
                           $x + $radius, $y + $radius,
                           $param4, $param5, $param6);
    }

    /**
     * Draw an ellipse inside the specified rectangle.
     *
     * Method signatures:
     * drawEllipse($x1, $y1, $x2, $y2);
     * drawEllipse($x1, $y1, $x2, $y2, $fillType);
     * drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle);
     * drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle, $fillType);
     *
     * @todo process special cases with $x2-$x1 == 0 or $y2-$y1 == 0
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param mixed $param5
     * @param mixed $param6
     * @param mixed $param7
     */
    public function drawEllipse($x1, $y1, $x2, $y2, $param5 = null, $param6 = null, $param7 = null)
    {
        if ($param5 === null) {
            // drawEllipse($x1, $y1, $x2, $y2);
            $startAngle = null;
            $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE;
        } else if ($param6 === null) {
            // drawEllipse($x1, $y1, $x2, $y2, $fillType);
            $startAngle = null;
            $fillType = $param5;
        } else {
            // drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle);
            // drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle, $fillType);
            $startAngle = $param5;
            $endAngle   = $param6;

            if ($param7 === null) {
                $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE;
            } else {
                $fillType = $param7;
            }
        }

        $this->_addProcSet('PDF');

        if ($x2 < $x1) {
            $temp = $x1;
            $x1   = $x2;
            $x2   = $temp;
        }
        if ($y2 < $y1) {
            $temp = $y1;
            $y1   = $y2;
            $y2   = $temp;
        }

        $x = ($x1 + $x2)/2.;
        $y = ($y1 + $y2)/2.;

        $xC = new Zend_Pdf_Element_Numeric($x);
        $yC = new Zend_Pdf_Element_Numeric($y);

        if ($startAngle !== null) {
            if ($startAngle != 0) { $startAngle = fmod($startAngle, M_PI*2); }
            if ($endAngle   != 0) { $endAngle   = fmod($endAngle,   M_PI*2); }

            if ($startAngle > $endAngle) {
                $endAngle += M_PI*2;
            }

            $clipPath    = $xC->toString() . ' ' . $yC->toString() . " m\n";
            $clipSectors = (int)ceil(($endAngle - $startAngle)/M_PI_4);
            $clipRadius  = max($x2 - $x1, $y2 - $y1);

            for($count = 0; $count <= $clipSectors; $count++) {
                $pAngle = $startAngle + ($endAngle - $startAngle)*$count/(float)$clipSectors;

                $pX = new Zend_Pdf_Element_Numeric($x + cos($pAngle)*$clipRadius);
                $pY = new Zend_Pdf_Element_Numeric($y + sin($pAngle)*$clipRadius);
                $clipPath .= $pX->toString() . ' ' . $pY->toString() . " l\n";
            }

            $this->_contents .= "q\n" . $clipPath . "h\nW\nn\n";
        }

        $xLeft  = new Zend_Pdf_Element_Numeric($x1);
        $xRight = new Zend_Pdf_Element_Numeric($x2);
        $yUp    = new Zend_Pdf_Element_Numeric($y2);
        $yDown  = new Zend_Pdf_Element_Numeric($y1);

        $xDelta  = 2*(M_SQRT2 - 1)*($x2 - $x1)/3.;
        $yDelta  = 2*(M_SQRT2 - 1)*($y2 - $y1)/3.;
        $xr = new Zend_Pdf_Element_Numeric($x + $xDelta);
        $xl = new Zend_Pdf_Element_Numeric($x - $xDelta);
        $yu = new Zend_Pdf_Element_Numeric($y + $yDelta);
        $yd = new Zend_Pdf_Element_Numeric($y - $yDelta);

        $this->_contents .= $xC->toString() . ' ' . $yUp->toString() . " m\n"
                         .  $xr->toString() . ' ' . $yUp->toString() . ' '
                         .    $xRight->toString() . ' ' . $yu->toString() . ' '
                         .      $xRight->toString() . ' ' . $yC->toString() . " c\n"
                         .  $xRight->toString() . ' ' . $yd->toString() . ' '
                         .    $xr->toString() . ' ' . $yDown->toString() . ' '
                         .      $xC->toString() . ' ' . $yDown->toString() . " c\n"
                         .  $xl->toString() . ' ' . $yDown->toString() . ' '
                         .    $xLeft->toString() . ' ' . $yd->toString() . ' '
                         .      $xLeft->toString() . ' ' . $yC->toString() . " c\n"
                         .  $xLeft->toString() . ' ' . $yu->toString() . ' '
                         .    $xl->toString() . ' ' . $yUp->toString() . ' '
                         .      $xC->toString() . ' ' . $yUp->toString() . " c\n";

        switch ($fillType) {
            case Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE:
                $this->_contents .= " B*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_FILL:
                $this->_contents .= " f*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_STROKE:
                $this->_contents .= " S\n";
                break;
        }

        if ($startAngle !== null) {
            $this->_contents .= "Q\n";
        }
    }

    /**
     * Draw an image at the specified position on the page.
     *
     * @param Zend_Pdf_Image $image
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     */
    public function drawImage(Zend_Pdf_Resource_Image $image, $x1, $y1, $x2, $y2)
    {
        $this->_addProcSet('PDF');

        $imageName    = $this->_attachResource('XObject', $image);
        $imageNameObj = new Zend_Pdf_Element_Name($imageName);

        $x1Obj     = new Zend_Pdf_Element_Numeric($x1);
        $y1Obj     = new Zend_Pdf_Element_Numeric($y1);
        $widthObj  = new Zend_Pdf_Element_Numeric($x2 - $x1);
        $heightObj = new Zend_Pdf_Element_Numeric($y2 - $y1);

        $this->_contents .= "q\n"
                         .  '1 0 0 1 ' . $x1Obj->toString() . ' ' . $y1Obj->toString() . " cm\n"
                         .  $widthObj->toString() . ' 0 0 ' . $heightObj->toString() . " 0 0 cm\n"
                         .  $imageNameObj->toString() . " Do\n"
                         .  "Q\n";
    }

    /**
     * Draw a LayoutBox at the specified position on the page.
     *
     * @param Zend_Pdf_Element_LayoutBox $box
     * @param float $x
     * @param float $y
     */
    public function drawLayoutBox($box, $x, $y)
    {
    }

    /**
     * Draw a line from x1,y1 to x2,y2.
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     */
    public function drawLine($x1, $y1, $x2, $y2)
    {
        $this->_addProcSet('PDF');

        $x1Obj = new Zend_Pdf_Element_Numeric($x1);
        $y1Obj = new Zend_Pdf_Element_Numeric($y1);
        $x2Obj = new Zend_Pdf_Element_Numeric($x2);
        $y2Obj = new Zend_Pdf_Element_Numeric($y2);

        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " m\n"
                         .  $x2Obj->toString() . ' ' . $y2Obj->toString() . " l\n S\n";
    }

    /**
     * Draw a polygon.
     *
     * If $fillType is Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE or
     * Zend_Pdf_Page::SHAPE_DRAW_FILL, then polygon is automatically closed.
     * See detailed description of these methods in a PDF documentation
     * (section 4.4.2 Path painting Operators, Filling)
     *
     * @param array $x  - array of float (the X co-ordinates of the vertices)
     * @param array $y  - array of float (the Y co-ordinates of the vertices)
     * @param integer $fillType
     * @param integer $fillMethod
     */
    public function drawPolygon($x, $y,
                                $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE,
                                $fillMethod = Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING)
    {
        $this->_addProcSet('PDF');

        $firstPoint = true;
        foreach ($x as $id => $xVal) {
            $xObj = new Zend_Pdf_Element_Numeric($xVal);
            $yObj = new Zend_Pdf_Element_Numeric($y[$id]);

            if ($firstPoint) {
                $path = $xObj->toString() . ' ' . $yObj->toString() . " m\n";
                $firstPoint = false;
            } else {
                $path .= $xObj->toString() . ' ' . $yObj->toString() . " l\n";
            }
        }

        $this->_contents .= $path;

        switch ($fillType) {
            case Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE:
                if ($fillMethod == Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING) {
                    $this->_contents .= " b\n";
                } else {
                    // Even-Odd fill method.
                    $this->_contents .= " b*\n";
                }
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_FILL:
                if ($fillMethod == Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING) {
                    $this->_contents .= " h\n f\n";
                } else {
                    // Even-Odd fill method.
                    $this->_contents .= " h\n f*\n";
                }
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_STROKE:
                $this->_contents .= " S\n";
                break;
        }
    }

    /**
     * Draw a rectangle.
     *
     * Fill types:
     * Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE - fill rectangle and stroke (default)
     * Zend_Pdf_Page::SHAPE_DRAW_STROKE      - stroke rectangle
     * Zend_Pdf_Page::SHAPE_DRAW_FILL        - fill rectangle
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param integer $fillType
     */
    public function drawRectangle($x1, $y1, $x2, $y2, $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE)
    {
        $this->_addProcSet('PDF');

        $x1Obj      = new Zend_Pdf_Element_Numeric($x1);
        $y1Obj      = new Zend_Pdf_Element_Numeric($y1);
        $widthObj   = new Zend_Pdf_Element_Numeric($x2 - $x1);
        $height2Obj = new Zend_Pdf_Element_Numeric($y2 - $y1);

        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                             .  $widthObj->toString() . ' ' . $height2Obj->toString() . " re\n";

        switch ($fillType) {
            case Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE:
                $this->_contents .= " B*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_FILL:
                $this->_contents .= " f*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_STROKE:
                $this->_contents .= " S\n";
                break;
        }
    }

    /**
     * Draw a line of text at the specified position.
     *
     * @param string $text
     * @param float $x
     * @param float $y
     * @param string $charEncoding (optional) Character encoding of source text.
     *   Defaults to current locale.
     * @throws Zend_Pdf_Exception
     */
    public function drawText($text, $x, $y, $charEncoding = '')
    {
        if ($this->_font === null) {
            throw new Zend_Pdf_Exception('Font has not been set');
        }

        $this->_addProcSet('Text');

        $textObj = new Zend_Pdf_Element_String($this->_font->encodeString($text, $charEncoding));
        $xObj    = new Zend_Pdf_Element_Numeric($x);
        $yObj    = new Zend_Pdf_Element_Numeric($y);

        $this->_contents .= "BT\n"
                         .  $xObj->toString() . ' ' . $yObj->toString() . " Td\n"
                         .  $textObj->toString() . " Tj\n"
                         .  "ET\n";
    }

    /**
     * Return the height of this page in points.
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->_pageDictionary->MediaBox->items[3]->value -
               $this->_pageDictionary->MediaBox->items[1]->value;
    }

    /**
     * Return the width of this page in points.
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->_pageDictionary->MediaBox->items[2]->value -
               $this->_pageDictionary->MediaBox->items[0]->value;
    }

     /**
     * Close the path by drawing a straight line back to it's beginning.
     *
     * @throws Zend_Pdf_Exception    - if a path hasn't been started with pathMove()
     */
    public function pathClose()
    {
    }

    /**
     * Continue the open path in a straight line to the specified position.
     *
     * @param float $x  - the X co-ordinate to move to
     * @param float $y  - the Y co-ordinate to move to
     */
    public function pathLine($x, $y)
    {
    }

    /**
     * Start a new path at the specified position. If a path has already been started,
     * move the cursor without drawing a line.
     *
     * @param float $x  - the X co-ordinate to move to
     * @param float $y  - the Y co-ordinate to move to
     */
    public function pathMove($x, $y)
    {
    }

    /**
     * Write raw PDF commands to the page.
     *
     * @param string $data
     */
    public function rawWrite($data)
    {
    }

    /**
     * Rotate the page.
     *
     * @param float $angle
     */
    public function rotate($x, $y, $angle)
    {
        $cos  = new Zend_Pdf_Element_Numeric(cos($angle));
        $sin  = new Zend_Pdf_Element_Numeric(sin($angle));
        $mSin = new Zend_Pdf_Element_Numeric(-$sin->value);

        $xObj = new Zend_Pdf_Element_Numeric($x);
        $yObj = new Zend_Pdf_Element_Numeric($y);

        $mXObj = new Zend_Pdf_Element_Numeric(-$x);
        $mYObj = new Zend_Pdf_Element_Numeric(-$y);


        $this->_addProcSet('PDF');
        $this->_contents .= '1 0 0 1 ' . $xObj->toString() . ' ' . $yObj->toString() . " cm\n"
                         . $cos->toString() . ' ' . $sin->toString()
                         . ' ' . $mSin->toString() . ' ' . $cos->toString() . " 0 0 cm\n"
                         .'1 0 0 1 ' . $mXObj->toString() . ' ' . $mYObj->toString() . " cm\n";
    }


}

