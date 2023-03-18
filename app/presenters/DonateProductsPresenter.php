<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 30.09.2017
 * Time: 16:13
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\DonateProductFormFactory;
use App\Model\SettingsManager;
use Nette\Application\UI\Form;

class DonateProductsPresenter extends BasePresenter
{
    /** @var DonateProductFormFactory @inject */
    public $donateProductFormFactory;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
        $this->checkModuleEnabled(SettingsManager::MODULE_DONATE);
    }

    public function createComponentDonateProductForm()
    {
        $id = $this->getParameter('id');
        if($id)
            $form = $this->donateProductFormFactory->create(TRUE);
        else
            $form = $this->donateProductFormFactory->create();

        $form->onSuccess[] = [$this, 'donateProductFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function donateProductFormSucceeded(Form $form, $values)
    {
        $id = $this->getParameter('id');

        if($id) {
            if($this->donateManager->updateDonateProduct($id, $values->price, $values->coins, $values->bonus_coins)) {
                $this->flashMessage('messages.donate_products.successfuly_changed', 'success');
            } else {
                $this->flashMessage('messages.global.something_wrong', 'danger');
            }
        } else {
            if($this->donateManager->addDonateProduct($values->price, $values->coins, $values->bonus_coins)) {
                $this->flashMessage('messages.donate_products.successfuly_added', 'success');
                $this->redirect('default');
            } else {
                $this->flashMessage('messages.global.something_wrong', 'danger');
            }
        }
    }

    /** @secured */
    public function handleDelete($product_id)
    {
        if(!$this->isAjax())
            return;

        $product = $this->donateManager->findOneById($product_id);

        if(!$product) {
            $this->flashMessage('messages.donate_products.product_notfound', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        $product->delete();

        $this->flashMessage('messages.donate_products.successfuly_deleted', 'success');
        $this->redrawControl('flashes');
        $this->redrawControl('productsList');
    }

    public function renderEdit($id)
    {
        $product = $this->donateManager->findOneById($id);

        if(!$product) {
            $this->flashMessage('messages.donate_products.product_notfound', 'danger');
            $this->redirect('default');
        }

        $this['donateProductForm']->setDefaults($product->toArray());
    }

    public function renderDefault()
    {
        $this->template->products = $this->donateManager->findAll();
    }
}