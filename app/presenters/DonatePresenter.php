<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 23.09.2017
 * Time: 17:56
 */

namespace App\Presenters;

use App\Model\DonateManager;
use App\Model\LogRepository;
use App\Model\SettingsManager;
use MetisFW\PayPal\Payment\SimplePaymentOperationFactory;
use MetisFW\PayPal\UI\PaymentControl;
use Nette\Object;
use PayPal\Api\Payment;

class DonatePresenter extends BasePresenter
{
    /** @var SimplePaymentOperationFactory @inject */
    public $simplePaymentOperationFactory;

    /** @persistent */
    public $product;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
        $this->checkModuleEnabled(SettingsManager::MODULE_DONATE);
    }

    public function createComponentPayPalPayment()
    {
        $product = $this->donateManager->findOneById($this->product);
        if(!$product) {
            $this->flashMessage('messages.donate.product_notfound', 'danger');
            $this->redirect('default', ['product' => NULL]);
        }

        $points = $product->{DonateManager::PRODUCT_COLUMN_COINS} + $product->{DonateManager::PRODUCT_COLUMN_BONUS_COINS};
        $operation = $this->simplePaymentOperationFactory->create($this->translator->trans('pages.global.donate_points') . ' x' . $points, $product->{DonateManager::PRODUCT_COLUMN_PRICE});

        $control = new PaymentControl($operation);

        $control->setTemplateFilePath(__DIR__ . '/templates/Donate/paymentButton.latte');

        $control->onCheckout[] = [$this, 'payPalPaymentCheckout'];
        $control->onSuccess[] = [$this, 'payPalPaymentSucceeded'];
        $control->onCancel[] = [$this, 'payPalPaymentCancelled'];

        return $control;
    }

    public function payPalPaymentCheckout(PaymentControl $control, Payment $created)
    {
    }

    public function payPalPaymentSucceeded(PaymentControl $control, Payment $paid)
    {
        $amount = floatval($paid->getTransactions()[0]->amount->total);

        $product = $this->donateManager->findOneById($this->product);
        if(!$product) {
            $this->flashMessage('messages.global.something_wrong', 'danger');
            $this->redirect('default', ['product' => NULL]);
        }

        $points = $product->{DonateManager::PRODUCT_COLUMN_COINS} + $product->{DonateManager::PRODUCT_COLUMN_BONUS_COINS};
        $this->userManager->addDonatePoints($this->user, $points);

        // log
        $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_DONATE_DONATED, $product->{DonateManager::PRODUCT_COLUMN_PRICE} . '-' . $points);

        $this->flashMessage('messages.donate.successfuly_purchased', 'success');
        $this->redirect('default', ['product' => NULL]);
    }

    public function payPalPaymentCancelled(PaymentControl $control)
    {
        $this->flashMessage('messages.donate.purchase_cancelled', 'warning');
        $this->redirect('default', ['product' => NULL]);
    }

    public function actionDefault()
    {
        $selected = NULL;

        if($this->product && $this->product != 'vip') {
            $selected = $this->donateManager->findOneById($this->product);

            if(!$selected) {
                $this->flashMessage('messages.donate.product_notfound', 'danger');
                $this->redirect('default', ['product' => NULL]);
            }
        } elseif ($this->product && $this->product == 'vip') {
            // temporary solution
            $selected = new \stdClass();
            $selected->id = 'vip';
            $selected->price = DonateManager::VIP_PRICE;
        }

        $products = $this->donateManager->findAll();
        $this->template->products = $products;
        $this->template->selected = $selected;
    }
}