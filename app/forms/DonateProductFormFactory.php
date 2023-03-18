<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 30.09.2017
 * Time: 16:58
 */

namespace App\Forms;

use App\Model\DonateManager;
use Nette\Application\UI\Form;

class DonateProductFormFactory extends BaseFormFactory
{
    /** @var DonateManager */
    private $donateManager;

    public function __construct(DonateManager $donateManager)
    {
        $this->donateManager = $donateManager;
    }

    public function create($editing = FALSE)
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addText('price', 'forms.donate_products.price')
            ->setRequired('forms.donate_products.price_empty')
            ->addRule(Form::INTEGER, 'forms.donate_products.price_numeric')
            ->addRule(Form::MIN, 'forms.donate_products.price_min', 0);

        $form->addText('coins', 'forms.donate_products.points')
            ->setRequired('forms.donate_products.points_empty')
            ->addRule(Form::INTEGER, 'forms.donate_products.points_numeric')
            ->addRule(Form::MIN, 'forms.donate_products.points_min', 0);

        $form->addText('bonus_coins', 'forms.donate_products.bonus_points')
            ->setRequired(FALSE)
            ->addRule(Form::INTEGER, 'forms.donate_products.bonus_points_numeric')
            ->addRule(Form::MIN, 'forms.donate_products.bonus_points_min', 0);

        $form->addGroup('');

        $form->addSubmit('submit', ($editing ? 'forms.donate_products.confirm' : 'forms.donate_products.add'))
            ->setAttribute('class', 'btn-lg btn-success btn-block');

        $form->addProtection('forms.global.csrf_protection');

        return $form;
    }
}