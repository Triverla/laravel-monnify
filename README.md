# laravel-monnify

## Installation

[PHP](https://php.net) 5.4+ or [HHVM](http://hhvm.com) 3.3+, and [Composer](https://getcomposer.org) are required.

To get the latest version of Laravel Monnify, simply require it

```bash
composer require triverla/laravel-monnify
```

Or add the following line to the require block of your `composer.json` file.

```
"triverla/laravel-monnify": "1.0.*"
```

You'll then need to run `composer install` or `composer update` to download it and have the autoloader updated.



Once Laravel Monnify is installed, you need to register the service provider. Open up `config/app.php` and add the following to the `providers` key.

```php
'providers' => [
    ...
    Triverla\LaravelMonnify\MonnifyServiceProvider::class,
    ...
]
```

> If you use **Laravel >= 5.5** you can skip this step and go to [**`configuration`**](https://github.com/triverla/laravel-monnify#configuration)

* `Triverla\LaravelMonnify\MonnifyServiceProvider::class`

Also, register the Facade like so:

```php
'aliases' => [
    ...
    'Monnify' => Triverla\LaravelMonnify\Facades\Monnify::class,
    ...
]
```

## Configuration

You can publish the configuration file using this command:

```bash
php artisan vendor:publish --provider="Triverla\LaravelMonnify\MonnifyServiceProvider"
```
A configuration file `monnify.php` with some sensible defaults will be placed in your `config` directory as displayed below:

```php
<?php

return [

    'base_url' => env('MONNIFY_BASE_URL', 'https://sandbox.monnify.com'),

    'api_key' => env('MONNIFY_API_KEY', ''),

    'secret_key' => env('MONNIFY_SECRET_KEY', ''),

    'contract_code' => env('MONNIFY_CONTRACT_CODE', ''),

    'source_account_number' => env('MONNIFY_SOURCE_ACCOUNT_NUMBER', ''),

    'default_split_percentage' => env('MONNIFY_DEFAULT_SPLIT_PERCENTAGE', 20),

    'default_currency_code' => env('MONNIFY_DEFAULT_CURRENCY_CODE', 'NGN'),

    'redirect_url' => env('MONNIFY_DEFAULT_REDIRECT_URL', env('APP_URL')),

];
```

## General Payment Flow

Though there are multiple ways to pay an order, most payment gateways expect you to follow the following flow in your checkout process:

### 1. The customer is redirected to the payment provider
After the customer has gone through the checkout process and is ready to pay, the customer must be redirected to the site of the payment provider.

The redirection is accomplished by submitting a form with some hidden fields. The form must send a POST request to the site of the payment provider. The hidden fields minimally specify the amount that must be paid, customer's name, email and a reference.

The reference is used by the payment provider as payment reference and can be used to verify the transaction.


### 2. The customer pays on the site of the payment provider
The customer visits the payment provider's site and gets to choose a payment method as specified in the request. All steps necessary to pay the order are taken care of by the payment provider.

### 3. The customer gets redirected back to your site
After having paid the order the customer is redirected back. In the redirection request to the website some values are returned. The values are usually the paymentReference or a transactionReference.

The payment reference is gotten from the request sent and it is used to verify if the transaction is valid and comes from the payment provider. It is paramount that this reference is thoroughly checked.


## Usage

Open your .env file and add the following keys. You can get them at ([https://app.monnify.com/developer](https://app.monnify.com/developer)):

```php
MONNIFY_BASE_URL=https://sandbox.monnify.com
MONNIFY_API_KEY=xxxxxxxxxx
MONNIFY_SECRET_KEY=xxxxxxxxxx
MONNIFY_CONTRACT_CODE=xxxxxxxxxx
MONNIFY_SOURCE_ACCOUNT_NUMBER=xxxxxxxxxx
MONNIFY_DEFAULT_SPLIT_PERCENTAGE=20
MONNIFY_DEFAULT_CURRENCY_CODE=NGN
MONNIFY_DEFAULT_REDIRECT_URL=http://localhost:8000/payment/callback
```
Set up routes and controller methods like so:

Note: Make sure you have `/payment/callback` added as `redirect_url` in your payment request

##### Example

```html
  <input type="hidden" name="redirect_url" value="http://localhost:8000/payment/callback">
```
Or
```php
$data = [
'redirectUrl' => 'http://localhost:8000/payment/callback'
];
```

```php
Route::post('/pay', [
    'uses' => 'PaymentController@redirectToMonnifyGateway',
    'as' => 'pay'
]);
```
OR

```php
// Laravel 8+
Route::post('/pay', [App\Http\Controllers\PaymentController::class, 'redirectToMonnifyGateway'])->name('pay');
```

```php
// Laravel 5.0+
Route::get('payment/callback', [
    'uses' => 'PaymentController@handlePaymentCallback'
]);
```

OR

```php
// Laravel 8+
Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'handlePaymentCallback']);
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Triverla\LaravelMonnify\Facades\Monnify;

class PaymentController extends Controller
{

    /**
     * Redirect the Customer to Monnify Payment Page
     * @return Url
     */
    public function redirectToMonnifyGateway()
    {
        try{
            return Monnify::Payment()->makePaymentRequest()->redirectNow();
        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['message'=>'The Monnify token has expired. Please refresh the page and try again.', 'type'=>'error']);
        }        
    }

    /**
     * Get payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = Monnify::Payment()->getPaymentData();

        dd($paymentDetails);
        // Now you have the payment details,
        // you can then redirect or do whatever you want
    }
}
```
Alternatively,
```php
/**
 *  In the case where you need to pass the data from your 
 *  controller instead of a form
 *  Make sure to send:
 *  required: name, email, amount, reference, description
 *  optionally: currency, redirect_url, payment_methods
 *  e.g:
 *  
 */
$data = array(
        "amount" => 1000,
        "customerName" => 'John Doe',
        "customerEmail" => 'john.doe@mail.com',
        "paymentReference" => 'abcd12345678efghi',
        "paymentDescription" => 'Payment for goods & services',
        "currencyCode" => "NGN",
        "redirectUrl" => 'http://localhost:8000/payment/callback',
        "paymentMethods" => ['CARD', 'ACCOUNT_TRANSFER']
    );

return  Monnify::Payment()->makePaymentRequest($data)->redirectNow();

```

```html
<form method="POST" action="{{ route('pay') }}" accept-charset="UTF-8" class="form-horizontal" role="form">
    {{ csrf_field() }} {{-- or @csrf--}}
    <div class="flex flex-col items-center mt-8">
        <div class="mb-4">
          Roasted Corn
            ₦ 550
        </div>
        <input type="hidden" name="customer_email" value="john.doe@gmail.com"> {{-- required --}}
        <input type="hidden" name="customer_name" value="John Doe"> {{-- required --}}
        <input type="hidden" name="amount" value="550"> {{-- required --}}
        <input type="hidden" name="currency" value="NGN"> 
        <input type="hidden" name="description" value="Bill Payment"> {{-- required --}}
        <input type="hidden" name="reference" value="{{ Monnify::genTransactionReference() }}"> {{-- required --}}
        <input type="hidden" name="redirect_url" value="http://localhost:8000/payment/callback">{{-- optional if you intend to use default redirect url from .env --}} 

        <button class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg" type="submit" value="Pay">
            <i class="fa fa-plus-circle fa-lg"></i> Proceed to Payment
        </button>
    </div>
</form>
```

## Usage
- import the Monnify Facades with the import statement below;
- Also import the FailedRequestException that handles the exceptions thrown from failed requests. This exception returns the corresponding monnify error message and code [Learn More](https://docs.teamapt.com/display/MON/Transaction+Responses)


```php
    ...
    
    use Triverla\LaravelMonnify\Facades\Monnify;
    use Triverla\LaravelMonnify\Exceptions\FailedRequestException;
    
    ...
```

Other methods include
```php

    use Triverla\LaravelMonnify\Facades\Monnify;
    
    //Banks
    $response = Monnify::Bank()->getBanks();
    $response = Monnify::Bank()->getBanksWithUSSDShortCode();
    $response = Monnify::Bank()->validateBankAccount(BankAccount $bankAccount);
    
    
    //Reserved Accounts
    $response = Monnify::ReservedAccount()->getAllTransactions(array $queryParams);
    $response = Monnify::ReservedAccount()->reserveAccount(string $accountReference, string $accountName, string $customerEmail, string $customerName = null, string $customerBvn = null, string $currencyCode = null, bool $restrictPaymentSource = false, AllowedPaymentSources $allowedPaymentSources = null, IncomeSplitConfig $incomeSplitConfig = null);
    $response = Monnify::ReservedAccount()->getAccountDetails(string $accountReference);
    $response = Monnify::ReservedAccount()->updateSplitConfig(string $accountReference, IncomeSplitConfig $incomeSplitConfig);


    //Disbursements
    $response = Monnify::Disbursement()->initiateTransferSingle(float $amount, string $reference, string $narration, MonnifyBankAccount $bankAccount, string $currencyCode = null);
    $response = Monnify::Disbursement()->initiateTransferSingleWithMonnifyTransaction(Tranx $tranx);
    $response = Monnify::Disbursement()->initiateTransferBulk(string $title, string $batchReference, string $narration, OnFailureValidate $onFailureValidate, int $notificationInterval, TranxList $tranxList);
    $response = Monnify::Disbursement()->authorizeTransfer2FA(string $authorizationCode, string $reference, string $path);
    
    
    //Invoices
    $response = Monnify::Invoice()->getAllInvoices();
    $response = Monnify::Invoice()->createAnInvoice(float $amount, $expiryDateTime, string $customerName, string $customerEmail, string $invoiceReference, string $invoiceDescription, string $redirectUrl, PaymentMethods $paymentMethods, IncomeSplitConfig $incomeSplitConfig = null, string $currencyCode = null);
    $response = Monnify::Invoice()->viewInvoiceDetails(string $invoiceReference);
    $response = Monnify::Invoice()->cancelInvoice();
    $response = Monnify::Invoice()->reservedAccountInvoicing(string $accountName, string $customerName, string $customerEmail, string $accountReference, string $currencyCode = null);
    $response = Monnify::Invoice()->attachReservedAccountToInvoice(float $amount, $expiryDateTime, string $customerName, string $customerEmail, string $invoiceReference, string $accountReference, string $invoiceDescription, IncomeSplitConfig $incomeSplitConfig = null, string $currencyCode = null);


    //SubAccounts
    $response = Monnify::SubAccount()->createSubAccount(string $bankCode, string $accountNumber, string $email, string $currencyCode = null, string $splitPercentage = null);
    $response = Monnify::SubAccount()->createSubAccounts(array $accounts);
    $response = Monnify::SubAccount()->getSubAccounts();
    $response = Monnify::SubAccount()->deleteSubAccount(string $subAccountCode);


    //Refunds
     $response = Monnify::Refund()->initiateRefund(string $transactionReference, string $refundReference, float $refundAmount, string $refundReason, string $customerNote, string $destinationAccountNumber, string $destinationAccountBankCode);
     $response = Monnify::Refund()->getRefundStatus(string $refundReference);
     $response = Monnify::Refund()->getAllRefunds(int $pageNo = 0, int $pageSize = 10);


    //Transactions
    $response = Monnify::Transaction()->getAllTransactions(array $queryParams);
    $response = Monnify::Transaction()->initializeTransaction(float $amount, string $customerName, string $customerEmail, string $paymentReference, string $paymentDescription, string $redirectUrl, PaymentMethods $paymentMethods, IncomeSplitConfig $incomeSplitConfig = null, string $currencyCode = null);
    $response = Monnify::Transaction()->calculateHash(string $paymentReference, $amountPaid, string $paidOn, string $transactionReference);
    $response = Monnify::Transaction()->getTransactionStatus(string $transactionReference);
    $response = Monnify::Transaction()->getTransactionStatusByPaymentReference(string $paymentReference);
    $response = Monnify::Transaction()->payWithBankTransfer(string $transactionReference, string $bankCode);


    //Verification APIs
     $response = Monnify::Verify()->bvn(string $bvnNo, string $accountName, string $dateOfBirth, string $mobileNo);
     $response = Monnify::Verify()->bvnAccountMatch(string $bvnNo, string $accountNumber, string $bankCode);
     $response = Monnify::Verify()->validateBankAccount(string $accountNumber, string $bankCode);

    // and many more
```

### Testing

``` bash
composer test
```

### Todo

* Webhook Events

### Contributing

Please feel free to fork this package and contribute by submitting a pull request to enhance the functionalities.

### Bugs & Issues

If you notice any bug or issues with this package kindly create and issues here [ISSUES](https://github.com/triverla/laravel-monnify/issues)

### Security

If you discover any security related issues, please email yusufbenaiah@gmail.com.

## How can I thank you?

Why not star the github repo and share the link for this repository on Twitter or other social platforms.

Don't forget to [follow me on twitter](https://twitter.com/benaiah_yusuf)!

Thanks!
Benaiah Yusuf

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
