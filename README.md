Chapa Integration in Laravel Web Application
This guide will walk you through the steps required to integrate Chapa, an Ethiopian online payment gateway, into your Laravel web application.

1. Create a Chapa Account
Visit the Chapa website.
Click on "Create Account."
Fill in your details, including first name, last name, email address, and other required information.
After creating your account, log in to your Chapa dashboard.
Click on your name in the top-right corner to reveal a dropdown menu.
Select "Settings."
In the settings board, click on "API."
Copy your "Secret Key" from the API section.
2. Add Chapa Secret Key and Base URI to .env File
The Chapa secret key and base URI are crucial for making authorized API requests to Chapa.

Chapa Secret Key: This key is provided by Chapa and is used for authorization, ensuring that the request comes from the authorized user.
Base URI: The base URI is the web address that serves as an endpoint for Chapa's payment gateway API. Without this URI, you can't send any requests to Chapa.
Example of Adding to .env File:
env
Copy code
CHAPA_SECRET_KEY=your_chapa_secret_key_here
CHAPA_BASE_URI=https://api.chapa.co/v1
3. Fetching Keys from .env in Configuration
To access the Chapa keys defined in the .env file, create a configuration file at config/chapa.php:

php
Copy code
<?php

return [
    'secret_key' => env('CHAPA_SECRET_KEY'),
    'base_uri' => env('CHAPA_BASE_URI'),
];
4. Define Chapa Logic in a Service Class
To handle Chapa-related operations, you'll create a service class:

Create a Services directory inside the app directory.
Inside the Services directory, create a file named ChapaService.php.
Define the logic for making requests to Chapa's API, such as payment initialization.
Example ChapaService.php:
php
Copy code
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
5. Handle User Requests in a Controller
Create a controller to handle user requests:

Run the command to create a controller:
bash
Copy code
php artisan make:controller ChapaController
In the ChapaController, process requests coming from the front-end and send them to the ChapaService class for handling.
Example ChapaController.php:
php
Copy code
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
6. Create a Payment Form View
Create a view where users can input their payment details:

Example resources/views/payment.blade.php:
html
Copy code
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
7. Define Routes
Add routes to handle the payment process:

Example routes/web.php:
php
Copy code
use App\Http\Controllers\ChapaController;

Route::get('/payment', function () {
    return view('payment');
})->name('chapa.payment.form');

Route::post('/payment/initiate', [ChapaController::class, 'initiatePayment'])->name('chapa.payment.initiate');
8. Handling the Response
After sending the request:

If successful, you'll receive a "checkout URL" from Chapa.
If the request fails, you'll get an error message, either user-defined or from Chapa.
