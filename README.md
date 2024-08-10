

🌐 Chapa Integration in Laravel Web Application
Welcome to the step-by-step guide for integrating Chapa, the Ethiopian online payment gateway, into your Laravel web application. Follow the instructions below to set up secure and seamless payment processing for your users.

📋 Table of Contents
Create a Chapa Account
Add Chapa Secret Key and Base URI to .env
Fetch Keys from .env in Configuration
Define Chapa Logic in a Service Class
Handle User Requests in a Controller
Create a Payment Form View
Define Routes
Handling the Response
1. ✨ Create a Chapa Account
A.Visit the Chapa Website: Start by heading over to Chapa’s official website.
B.Create an Account: Click on "Create Account" and provide your details, including first name, last name, email address, and more.
Access API Settings:
->Log in to your account.
->Click on your name in the top-right corner and select "Settings" from the dropdown menu.
->Navigate to the "API" tab and copy your Secret Key.
2. 🔑 Add Chapa Secret Key and Base URI to .env
Next, you need to store your Chapa credentials in the .env file:

Chapa Secret Key
* Description: A unique key provided by Chapa to authorize API requests.
Usage: Ensures that requests are made by an authorized user.
Base URI
* Description: The web address that serves as the endpoint for Chapa's payment gateway API.
* Usage: Directs where the API requests should be sent.
Add to .env:
env

CHAPA_SECRET_KEY=your_chapa_secret_key_here
CHAPA_BASE_URI=https://api.chapa.co/v1

3. 🔧 Fetch Keys from .env in Configuration
To access the Chapa credentials from anywhere in your application, configure them in config/chapa.php:

php

<?php

return [
    'secret_key' => env('CHAPA_SECRET_KEY'),
    'base_uri' => env('CHAPA_BASE_URI'),
];


4. ⚙️ Define Chapa Logic in a Service Class
Let's encapsulate all Chapa-related operations within a dedicated service class:

Steps:
A.Create a Services Directory: Inside the app directory.

B.Create ChapaService.php: In the Services directory.

C. Define Logic: Implement methods for interacting with Chapa’s API.

Example ChapaService.php:
php

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChapaService
{
    protected $secretKey;
    protected $baseUri;

    public function __construct()
    {
        $this->secretKey = config('chapa.secret_key');
        $this->baseUri = config('chapa.base_uri');
    }

    public function initializePayment($data)
    {
        $response = Http::withToken($this->secretKey)
                        ->post("{$this->baseUri}/transaction/initialize", $data);

        return $response->json();
    }
}


5. 🗂 Handle User Requests in a Controller

Create a controller to process user requests:

A. Generate Controller:

php artisan make:controller ChapaController

B. Implement Logic: Handle requests and pass them to the ChapaService for processing.

Example ChapaController.php:
php

<?php

namespace App\Http\Controllers;

use App\Services\ChapaService;
use Illuminate\Http\Request;

class ChapaController extends Controller
{
    protected $chapaService;

    public function __construct(ChapaService $chapaService)
    {
        $this->chapaService = $chapaService;
    }

    public function initiatePayment(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'email' => 'required|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'tx_ref' => 'required|string|unique:transactions,tx_ref',
        ]);

        $response = $this->chapaService->initializePayment($data);

        if ($response['status'] == 'success') {
            return redirect($response['data']['checkout_url']);
        }

        return back()->withErrors('Payment initialization failed.');
    }
}
6. 📝 Create a Payment Form View

Design a simple form to collect payment details from users:

Example resources/views/payment.blade.php:
html

<form action="{{ route('chapa.payment.initiate') }}" method="POST">
    @csrf
    <label for="amount">Amount</label>
    <input type="number" name="amount" required>
    
    <label for="email">Email</label>
    <input type="email" name="email" required>
    
    <label for="first_name">First Name</label>
    <input type="text" name="first_name" required>
    
    <label for="last_name">Last Name</label>
    <input type="text" name="last_name" required>
    
    <label for="tx_ref">Transaction Reference</label>
    <input type="text" name="tx_ref" required>
    
    <button type="submit">Pay Now</button>
</form>
7. 🌐 Define Routes
Set up routes for displaying the payment form and processing the payment:

Example routes/web.php:
php

use App\Http\Controllers\ChapaController;

Route::get('/payment', function () {
    return view('payment');
})->name('chapa.payment.form');

Route::post('/payment/initiate', [ChapaController::class, 'initiatePayment'])->name('chapa.payment.initiate');
8. 🚀 Handling the Response
Once the payment request is sent:

A. Success: You will receive a "checkout URL" from Chapa.
B. Failure: An error message will be returned, either user-defined or from Chapa.

have you liked it?am happy for that,please give it a star⭐
Thank you!
