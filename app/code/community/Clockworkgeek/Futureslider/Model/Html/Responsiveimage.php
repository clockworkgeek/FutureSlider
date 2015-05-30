<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Daniel Deady
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Resizes image automatically.
 *
 * Scaled images are prepared in powers of two for simplicity.
 * The image used by browser should be no smaller than needed
 * and less than twice as big as needed.
 *
 * The source image is only ever scaled down since scaling up
 * adds no quality, so the source should be as big as it can.
 *
 * @method string getFilename()
 * @method Clockworkgeek_Futureslider_Model_Html_Responsiveimage setFilename(string)
 */
class Clockworkgeek_Futureslider_Model_Html_Responsiveimage extends Varien_Object
{

    const MIN_WIDTH = 128;
    const MIN_HEIGHT = 128;

    /**
     * Keys are filenames relative to media dir.
     * Values are width/height tuples.
     *
     * @var array[]
     */
    protected $_sizes = array();

    /**
     * Convert CMS directives to local, absolute filename
     *
     * @return string
     */
    public function getSourceFilename()
    {
        $template = Mage::getSingleton('formelements/template_localdir');
        return $template->filter($this->getFilename());
    }

    /**
     * Convert CMS directives to absolute URL
     *
     * @return string
     */
    public function getSourceUrl()
    {
        $filename = $this->getFilename();
        if (strpos($filename, '{{media ') !== false) {
            $template = Mage::getSingleton('cms/template_filter');
            $filename = $template->filter($filename);
        }
        else {
            $mediaDir = Mage::getBaseDir('media') . DS;
            $mediaUrl = Mage::getBaseUrl('media', true);
            $filename = str_replace($mediaDir, $mediaUrl, $filename);
        }
        return $filename;
    }

    public function getResizedDir()
    {
        return Mage::getStoreConfig('media_storage_configuration/allowed_resources/futureslider');
    }

    /**
     * Save scaled images in powers of two.
     * That means 1/2, 1/4, 1/8, etc.
     * Available files are stored in $this->_sizes
     *
     * @return Clockworkgeek_Futureslider_Model_Html_Responsiveimage
     */
    public function prepareImages()
    {
        $filename = $this->getSourceFilename();
        if (! file_exists($filename)) {
            return $this;
        }
        $source = new Varien_Image($filename);
        $width = $source->getOriginalWidth();
        $height = $source->getOriginalHeight();
        $this->setWidth($width)->setHeight($height);
        $dir = $this->getResizedDir() . DS . md5_file($filename) . DS;
        $dest = $dir . '%dx%d' . image_type_to_extension($source->getMimeType());
        $mediaDir = Mage::getBaseDir('media') . DS;

        while ($width > self::MIN_WIDTH && $height > self::MIN_HEIGHT) {
            $width /= 2;
            $height /= 2;
            $filename = sprintf($dest, $width, $height);
            $this->_sizes[$filename] = array($width, $height);

            if (! file_exists($mediaDir . $filename)) {
                $image = clone $source;
                $image->resize($width); // height is extrapolated
                $image->save($mediaDir . $filename);
            }
        }

        return $this;
    }

    /**
     * Build appropriate media queries for available $_sizes
     *
     * @return string
     */
    public function getCss($selector)
    {
        $mediaUrl = Mage::getBaseUrl('media', true);

        // first rule without media query
        $css = $selector . '{background:';
        $css .= 'url(' . $this->getSourceUrl() . ')';
        $css .= 'center';
        if ($this->getBackgroundSize()) {
            $css .= '/' . $this->getBackgroundSize();
        }
        $css .= ' no-repeat}';

        switch ($this->getBackgroundSize()) {
            case null:
            case '':
            case 'auto':
            case 'auto auto':
                // nothing to scale
                return $css;
            case '100% auto':
                $query = '(max-width:%1$.2fin),(max-width:%3$.2fin) and (min-resolution:192dpi)';
                break;
            case 'auto 100%':
                $query = '(max-height:%2$.2fin),(max-height:%4$.2fin) and (min-resolution:192dpi)';
                break;
            case 'contain':
                $query = '(max-width:%1$.2fin),(max-height:%2$.2fin),(max-width:%3$.2fin) and (min-resolution:192dpi),(max-height:%4$.2fin) and (min-resolution:192dpi)';
                break;
            case 'cover':
            default:
                $query = '(max-width:%1$.2fin) and (max-height:%2$.2fin),(max-width:%3$.2fin) and (max-height:%4$.2fin) and (min-resolution:192dpi)';
                break;
        }

        foreach ($this->_sizes as $filename => $size) {
            list($width, $height) = $size;
            $css .= sprintf(
                "@media{$query}{%5\$s{background-image:url(%6\$s)}}",
                $width/96,
                $height/96,
                $width/192,
                $height/192,
                $selector,
                $mediaUrl . $filename
                );
        }

        // prevent smallest being preloaded and save an HTTP request
        // when embedded in <object> ordinary reflows trigger media querying
        // so there must be a safety net
        $css .= "@media(max-width:10px){{$selector}{background-image:none}}";

        return $css;
    }

    public function toSvg()
    {
        return sprintf(
            '<svg width="%d" height="%d" xmlns="http://www.w3.org/2000/svg"><style type="text/css">%s</style></svg>',
            $this->getWidth(),
            $this->getHeight(),
            $this->getCss('svg'));
    }

    public function toHtml($attributes = array())
    {
        if (is_array($attributes)) {
            $attributes = new Clockworkgeek_Futureslider_Model_Html_Attributes($attributes);
        }

        $attributes->setData('data-size', $this->getBackgroundSize());
        $dataSizes = sprintf('%s;%d;%d', $this->getSourceUrl(), $this->getWidth(), $this->getHeight());
        foreach ($this->_sizes as $filename => $size) {
            $dataSizes .= sprintf(';%s;%d;%d', Mage::getBaseUrl('media', true).$filename, $size[0], $size[1]);
        }
        $attributes->setData('data-sizes', $dataSizes);

        return sprintf(
            '<object data="data:image/svg+xml;base64,%s"%s>'.
            // non-vector fallback for IE8, which also cannot use background-size
            '<!--[if lte IE 8]><noscript><img src="%s" class="raster-fallback" /></noscript><![endif]-->'.
            '</object>',
            base64_encode($this->toSvg()),
            $attributes,
            $this->getSourceUrl());
    }

    public function __toString()
    {
        return $this->toHtml();
    }
}
