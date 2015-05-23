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

class Clockworkgeek_Futureslider_Block_Adminhtml_Slide_Chooser_Massaction
extends Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('futureslider/chooser/massaction.phtml');
    }

    public function getApplyButtonHtml()
    {
        return $this->getButtonHtml($this->__('Select Slides'), $this->getJsObjectName() . ".apply()", 'save');
    }

    public function getJavaScript()
    {
        $parentId = $this->getParentBlock()->getHtmlId();
        return "{$this->getJsObjectName()} = new varienGridMassaction('{$this->getHtmlId()}', "
                . "{$this->getGridJsObjectName()}, {$parentId}.getElementValue()"
                . ", '{$this->getFormFieldNameInternal()}', '{$this->getFormFieldName()}');"
                . "{$this->getJsObjectName()}.setItems({}); "
                . "{$this->getJsObjectName()}.setGridIds('{$this->getGridIdsJson()}');"
                . ($this->getUseAjax() ? "{$this->getJsObjectName()}.setUseAjax(true);" : '')
                . ($this->getUseSelectAll() ? "{$this->getJsObjectName()}.setUseSelectAll(true);" : '')
                . "{$this->getJsObjectName()}.errorText = '{$this->getErrorText()}';"

                // override normal apply behaviour to support multi-select
                . "{$this->getJsObjectName()}.apply = function() {
                    if(varienStringArray.count(this.checkedString) == 0) { alert(this.errorText); return; }
                    var checked = $('modal_dialog_message').select('input[checked]'),
                        names = checked.map(function(input) {return input.up('td').next().textContent.trim();});
                    {$parentId}.setElementValue(checked.pluck('value').join());
                    {$parentId}.setElementLabel(names.join(', '));
                    {$parentId}.close(); };";
    }
}
