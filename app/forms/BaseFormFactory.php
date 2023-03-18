<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 25.02.2017
 * Time: 14:40
 */

namespace App\Forms;

use Minetro\ReCaptcha\Forms\ReCaptchaField;
use Nette;
use Nette\Forms\Controls;

/**
 * Class BaseFormFactory
 * @package App\Forms
 *
 * Bootstrap 3 rendering for forms.
 * Source: https://github.com/nette/forms/blob/master/examples/bootstrap3-rendering.php
 */
abstract class BaseFormFactory extends Nette\Object
{
    // form methods
    const
        METHOD_POST = 'POST',
        METHOD_GET = 'GET';

    /** @var \Kdyby\Translation\Translator */
    public $translator;

    public function setTranslator(\Kdyby\Translation\Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Nette\Application\UI\Form $form
     * @param bool $form_group
     * @param bool $in_container
     * @return Nette\Application\UI\Form
     */
    public static function addBootstrap3Styles(Nette\Application\UI\Form $form, $form_group = TRUE, $in_container = FALSE) {
        //$form = new Nette\Application\UI\Form;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;

        if($form_group)
            $renderer->wrappers['pair']['container'] = 'div class=form-group';

        $renderer->wrappers['pair']['.error'] = 'has-error';

        if($in_container)
            $renderer->wrappers['control']['container'] = 'div class=col-sm-3';
        else
            $renderer->wrappers['control']['container'] = 'div';

        $renderer->wrappers['label']['container'] = 'div class="control-label"';
        //$renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

        // make form and controls compatible with Twitter Bootstrap
        //$form->getElementPrototype()->class('form-horizontal');
        foreach ($form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->getControlPrototype()->addClass('btn');
                //$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                //$usedPrimary = TRUE;
            } elseif (($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) && !($control instanceof ReCaptchaField)) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            } elseif ($control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass('checkbox-inline');
            }

        }

        return $form;

    }
}