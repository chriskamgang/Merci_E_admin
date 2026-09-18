<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Admin\Setting;
use Illuminate\Http\Request;
use App\Models\ThirdPartySetting;
use App\Helpers\Rides\StorePaymentDetailForRideHelper;

class PaymentGatewayController extends Controller
{
    use StorePaymentDetailForRideHelper;

    public function index()
    {
        $settings = ThirdPartySetting::where('module', 'payment')
            ->pluck('value', 'name')
            ->toArray();
        // dd($settings);
        // Transform settings into a structured object
        $formattedSettings = [
            // KPay
            'enable_kpay'              => filter_var($settings['enable_kpay'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'kpay_environment'         => $settings['kpay_environment'] ?? 'test',
            'kpay_test_api_key'        => $settings['kpay_test_api_key'] ?? '',
            'kpay_test_secret_key'     => $settings['kpay_test_secret_key'] ?? '',
            'kpay_live_api_key'        => $settings['kpay_live_api_key'] ?? '',
            'kpay_live_secret_key'     => $settings['kpay_live_secret_key'] ?? '',

            // GFSolutions
            'enable_gfsolutions'        => filter_var($settings['enable_gfsolutions'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'gfsolutions_api_key'       => $settings['gfsolutions_api_key'] ?? '',
            'gfsolutions_api_secret'    => $settings['gfsolutions_api_secret'] ?? '',
            'gfsolutions_base_url'      => $settings['gfsolutions_base_url'] ?? 'https://backend.gfinancials.com/api/v1',
            'gfsolutions_callback_url'  => url('api/v1/payment/gfsolutions/callback'),

            'enable_paystack' => filter_var($settings['enable_paystack'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'paystack_test_publish_key' => $settings['paystack_test_publish_key'] ?? '',
            'paystack_production_publish_key' => $settings['paystack_production_publish_key'] ?? '',
            'paystack_test_secret_key' => $settings['paystack_test_secret_key'] ?? '',
            'paystack_production_secret_key' => $settings['paystack_production_secret_key'] ?? '',
            'paystack_environment' => $settings['paystack_environment'] ?? '',

            'enable_cashfree' => filter_var($settings['enable_cashfree'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'cash_free_environment' => $settings['cash_free_environment'] ?? '',
            'cash_free_secret_key' => $settings['cash_free_secret_key'] ?? '',
            'cash_free_production_secret_key' => $settings['cash_free_production_secret_key'] ?? '',
            'cash_free_app_id' => $settings['cash_free_app_id'] ?? '',
            'cash_free_production_app_id' => $settings['cash_free_production_app_id'] ?? '',

            'enable_mercadopago' => filter_var($settings['enable_mercadopago'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'mercadopago_environment' => $settings['mercadopago_environment'] ?? '',
            'mercadopago_test_public_key' => $settings['mercadopago_test_public_key'] ?? '',
            'mercadopago_live_public_key' => $settings['mercadopago_live_public_key'] ?? '',
            'mercadopago_test_access_token' => $settings['mercadopago_test_access_token'] ?? '',
            'mercadopago_live_access_token' => $settings['mercadopago_live_access_token'] ?? '',

            'enable_stripe' => filter_var($settings['enable_stripe'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'enable_stripe_authorization' => filter_var($settings['enable_stripe_authorization'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'stripe_environment' => $settings['stripe_environment'] ?? '',
            'stripe_test_secret_key' => $settings['stripe_test_secret_key'] ?? '',
            'stripe_live_secret_key' => $settings['stripe_live_secret_key'] ?? '',
            'stripe_test_publishable_key' => $settings['stripe_test_publishable_key'] ?? '',
            'stripe_live_publishable_key' => $settings['stripe_live_publishable_key'] ?? '',

            'enable_flutterwave' => filter_var($settings['enable_flutterwave'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'flutter_wave_environment' => $settings['flutter_wave_environment'] ?? '',
            'flutter_wave_test_secret_key' => $settings['flutter_wave_test_secret_key'] ?? '',
            'flutter_wave_production_secret_key' => $settings['flutter_wave_production_secret_key'] ?? '',

            'enable_razorpay' => filter_var($settings['enable_razorpay'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'razor_pay_environment' => $settings['razor_pay_environment'] ?? '',
            'razor_pay_test_api_key' => $settings['razor_pay_test_api_key'] ?? '',
            'razor_pay_live_api_key' => $settings['razor_pay_live_api_key'] ?? '',
            'razor_pay_secrect_key' => $settings['razor_pay_secrect_key'] ?? '',
            'razor_pay_test_secrect_key' => $settings['razor_pay_test_secrect_key'] ?? '',

            'enable_khalti' => filter_var($settings['enable_khalti'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'khalti_pay_environment' => $settings['khalti_pay_environment'] ?? '',
            'khalti_pay_test_api_key' => $settings['khalti_pay_test_api_key'] ?? '',
            'khalti_pay_live_api_key' => $settings['khalti_pay_live_api_key'] ?? '',

            // 'enable_easypaisa' => filter_var($settings['enable_easypaisa'] ?? false, FILTER_VALIDATE_BOOLEAN),
            // 'easypay_environment' => $settings['easypay_environment'] ?? '',
            // 'easypaisa_store_id' => $settings['easypaisa_store_id'] ?? '',
            // 'easypaisa_hash_key' => $settings['easypaisa_hash_key'] ?? '',

            'enable_xendit' => filter_var($settings['enable_xendit'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'xendi_pay_environment' => $settings['xendi_pay_environment'] ?? '',
            'xendi_pay_test_api_key' => $settings['xendi_pay_test_api_key'] ?? '',
            // 'xendi_pay_test_secrect_key' => $settings['xendi_pay_test_secrect_key'] ?? '',
            'xendit_pay_live_api_key' => $settings['xendit_pay_live_api_key'] ?? '',
            // 'xendit_pay_secrect_key' => $settings['xendit_pay_secrect_key'] ?? '',


            'enable_flexpaie' => filter_var($settings['enable_flexpaie'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'flexpaie_environment' => $settings['flexpaie_environment'] ?? '',
            'flexpaie_test_bearer_token' => $settings['flexpaie_test_bearer_token'] ?? '',
            'flexpaie_production_bearer_token' => $settings['flexpaie_production_bearer_token'] ?? '',


            'enable_openpix' => filter_var($settings['enable_openpix'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'openpix_environment' => $settings['openpix_environment'] ?? '',
            'openpix_test_api_key' => $settings['openpix_test_api_key'] ?? '',
            'openpix_live_api_key' => $settings['openpix_live_api_key'] ?? '',
            'openpix_webhook_url' => url('/api/v1/payment/openpix/webhook'),

            'enable_myfatoora' => filter_var($settings['enable_myfatoora'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'myfatoora_environment' => $settings['myfatoora_environment'] ?? '',
            'myfatoora_test_token' => $settings['myfatoora_test_token'] ?? '',
            'myfatoora_live_token' => $settings['myfatoora_live_token'] ?? '',

            'enable_paymongo' => filter_var($settings['enable_paymongo'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'paymongo_environment' => $settings['paymongo_environment'] ?? '',
            'paymongo_test_secret_key' => $settings['paymongo_test_secret_key'] ?? '',
            'paymongo_live_secret_key' => $settings['paymongo_live_secret_key'] ?? '',

            // 'enable_airtel' => filter_var($settings['enable_airtel'] ?? false, FILTER_VALIDATE_BOOLEAN),
            // 'airtel_environment' => $settings['airtel_environment'] ?? '',
            // 'airtel_test_client_id' => $settings['airtel_test_client_id'] ?? '',
            // 'airtel_test_client_secret_key' => $settings['airtel_test_client_secret_key'] ?? '',
            // 'airtel_live_client_id' => $settings['airtel_live_client_id'] ?? '',
            // 'airtel_live_client_secret_key' => $settings['airtel_live_client_secret_key'] ?? '',


            'enable_paypal' => filter_var($settings['enable_paypal'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'paypal_environment' => $settings['paypal_environment'] ?? '',
            'paypal_sandbox_client_id' => $settings['paypal_sandbox_client_id'] ?? '',
            'paypal_sandbox_client_secret' => $settings['paypal_sandbox_client_secret'] ?? '',
            'paypal_sandbox_app_id' => $settings['paypal_sandbox_app_id'] ?? '',
            'paypal_client_id' => $settings['paypal_client_id'] ?? '',
            'paypal_client_secret' => $settings['paypal_client_secret'] ?? '',
            'paypal_app_id' => $settings['paypal_app_id'] ?? '',
            'paypal_notify_url' => $settings['paypal_notify_url'] ?? '',


            'enable_fedapay' => filter_var($settings['enable_fedapay'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'fedapay_environment' => $settings['fedapay_environment'] ?? '',
            'fedapay_test_secret_key' => $settings['fedapay_test_secret_key'] ?? '',
            'fedapay_live_secret_key' => $settings['fedapay_live_secret_key'] ?? '',

            'enable_sslcommerz' => filter_var($settings['enable_sslcommerz'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'sslcommerz_environment' => $settings['sslcommerz_environment'] ?? '',
            'sslcommerz_store_id' => $settings['sslcommerz_store_id'] ?? '',
            'sslcommerz_store_password' => $settings['sslcommerz_store_password'] ?? '',

        ];

        return Inertia::render('pages/payment_gateway/index', [
            'app_for' => env('APP_FOR'),
            'settings' => $formattedSettings,
        ]);
    }


    public function update(Request $request)
    {
        // dd($request->all());
        $settings = $request->validate([
            // KPay (primary gateway)
            'enable_kpay'            => 'required',
            'kpay_environment'       => 'required|in:test,live',
            'kpay_test_api_key'      => 'sometimes|nullable',
            'kpay_test_secret_key'   => 'sometimes|nullable',
            'kpay_live_api_key'      => 'sometimes|nullable',
            'kpay_live_secret_key'   => 'sometimes|nullable',

            // GFSolutions
            'enable_gfsolutions'     => 'sometimes',
            'gfsolutions_api_key'    => 'sometimes|nullable',
            'gfsolutions_api_secret' => 'sometimes|nullable',
            'gfsolutions_base_url'   => 'sometimes|nullable',

            // Legacy gateways — all optional
            'enable_paystack' => "sometimes",
            'paystack_environment' => "sometimes",
            'paystack_test_secret_key' => "sometimes",
            'paystack_production_secret_key' => "sometimes",
            'paystack_test_publish_key' => "sometimes",
            'paystack_production_publish_key' => "sometimes",

            'enable_cashfree' => "sometimes",
            'cash_free_environment' => "sometimes",
            'cash_free_secret_key' => "sometimes",
            'cash_free_production_secret_key' => "sometimes",
            'cash_free_app_id' => "sometimes",
            'cash_free_production_app_id' => "sometimes",

            'enable_mercadopago' => "sometimes",
            'mercadopago_environment' => "sometimes",
            'mercadopago_test_public_key' => "sometimes",
            'mercadopago_live_public_key' => "sometimes",
            'mercadopago_test_access_token' => "sometimes",
            'mercadopago_live_access_token' => "sometimes",

            'enable_stripe' => "sometimes",
            'enable_stripe_authorization' => "sometimes",
            'stripe_environment' => "sometimes",
            'stripe_test_secret_key' => "sometimes",
            'stripe_live_secret_key' => "sometimes",
            'stripe_test_publishable_key' => "sometimes",
            'stripe_live_publishable_key' => "sometimes",

            'enable_flutterwave' => "sometimes",
            'flutter_wave_environment' => "sometimes",
            'flutter_wave_test_secret_key' => "sometimes",
            'flutter_wave_production_secret_key' => "sometimes",

            'enable_razorpay' => "sometimes",
            'razor_pay_environment' => "sometimes",
            'razor_pay_test_api_key' => "sometimes",
            'razor_pay_live_api_key' => "sometimes",
            'razor_pay_secrect_key' => "sometimes",
            'razor_pay_test_secrect_key' => "sometimes",

            'enable_khalti' => "sometimes",
            'khalti_pay_environment' => "sometimes",
            'khalti_pay_test_api_key' => "sometimes",
            'khalti_pay_live_api_key' => "sometimes",

            'enable_xendit' => "sometimes",
            'xendi_pay_environment' => "sometimes",
            'xendi_pay_test_api_key' => "sometimes",
            'xendit_pay_live_api_key' => "sometimes",

            'enable_flexpaie' => "sometimes",
            'flexpaie_environment' => "sometimes",
            'flexpaie_test_bearer_token' => "sometimes",
            'flexpaie_production_bearer_token' => "sometimes",

            'enable_openpix' => "sometimes",
            'openpix_environment' => "sometimes",
            'openpix_test_api_key' => "sometimes",
            'openpix_live_api_key' => "sometimes",

            'enable_myfatoora' => "sometimes",
            'myfatoora_environment' => "sometimes",
            'myfatoora_test_token' => "sometimes",
            'myfatoora_live_token' => "sometimes",

            'enable_paymongo' => "sometimes",
            'paymongo_environment' => "sometimes",
            'paymongo_test_secret_key' => "sometimes",
            'paymongo_live_secret_key' => "sometimes",

            'enable_paypal' => "sometimes",
            'paypal_environment' => "sometimes",
            'paypal_sandbox_client_id' => "sometimes",
            'paypal_sandbox_client_secret' => "sometimes",
            'paypal_sandbox_app_id' => "sometimes",
            'paypal_client_id' => "sometimes",
            'paypal_client_secret' => "sometimes",
            'paypal_app_id' => "sometimes",
            'paypal_notify_url' => "sometimes",

            'enable_fedapay' => "sometimes",
            'fedapay_environment' => "sometimes",
            'fedapay_test_secret_key' => "sometimes",
            'fedapay_live_secret_key' => "sometimes",

            'enable_sslcommerz' => "sometimes",
            'sslcommerz_environment' => "sometimes",
            'sslcommerz_store_id' => "sometimes",
            'sslcommerz_store_password' => "sometimes",

        ]);

        // dd($settings);

        // Only update submitted settings, preserve others
        if ($request->has('paypal_environment')) {
            $paypal_settings = [
                'paypal_environment' => $request->paypal_environment,
                'paypal_sandbox_client_id' => $request->paypal_sandbox_client_id,
                'paypal_sandbox_client_secret' => $request->paypal_sandbox_client_secret,
                'paypal_sandbox_app_id' => $request->paypal_sandbox_app_id,
                'paypal_live_client_id' => $request->paypal_client_id,
                'paypal_live_client_secret' => $request->paypal_client_secret,
                'paypal_live_app_id' => $request->paypal_app_id,
                'paypal_notify_url' => $request->paypal_notify_url,
            ];
            $this->updateEnvFile($paypal_settings);
        }

        // Sync KPay keys to .env based on selected environment
        $kpayApiKey    = $request->kpay_environment === 'live'
            ? $request->kpay_live_api_key
            : $request->kpay_test_api_key;
        $kpaySecretKey = $request->kpay_environment === 'live'
            ? $request->kpay_live_secret_key
            : $request->kpay_test_secret_key;

        $this->updateEnvFile([
            'KPAY_API_KEY'    => $kpayApiKey ?? '',
            'KPAY_SECRET_KEY' => $kpaySecretKey ?? '',
        ]);


        // Sync GFSolutions keys to .env
        if ($request->has('gfsolutions_api_key')) {
            $this->updateEnvFile([
                'GFSOLUTIONS_API_KEY'    => $request->gfsolutions_api_key ?? '',
                'GFSOLUTIONS_API_SECRET' => $request->gfsolutions_api_secret ?? '',
                'GFSOLUTIONS_BASE_URL'   => $request->gfsolutions_base_url ?? 'https://backend.gfinancials.com/api/v1',
            ]);
        }

        $skipKeys = ['gfsolutions_callback_url'];

        foreach ($settings as $key => $setting) {
            if (in_array($key, $skipKeys)) continue;
            ThirdPartySetting::updateOrCreate(
                ['name' => $key, 'module' => 'payment'],
                ['value' => $setting]
            );
        }

        return response()->json(['message' => 'Sms  Destails updated successfully'], 201);

    }

    /**
     * Update the .env file with new settings.
     *
     * @param array $settings
     * @return void
     */
    private function updateEnvFile(array $settings)
    {
        // Get the path to the .env file
        $envPath = base_path('.env');

        // Check if the .env file exists
        if (file_exists($envPath)) {
            // Read the current content of the .env file
            $envContent = file_get_contents($envPath);

            // Update or add each setting in the .env file
            foreach ($settings as $key => $value) {
                $envKey = strtoupper($key); // Convert the key to uppercase to match the .env convention

                // Create a regex pattern to match the existing key-value pair
                $pattern = "/^{$envKey}=[^\r\n]*/m";

                // If the key exists, replace it; otherwise, append the new key-value pair
                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, "{$envKey}={$value}", $envContent);
                }
                else {
                    $envContent .= "\n{$envKey}={$value}";
                }
            }

            // Write the updated content back to the .env file
            file_put_contents($envPath, $envContent);
        }
    }

    public function storePayment($request)
    {
        $payment = $this->validatePay($request);
        return $payment;
    }


    public function getPaymentDetail($transaction_id)
    {
        $payment = $this->getPayment($transaction_id);
        // dd($payment);
        return $payment;
    }


    public function payNow($transaction_id, $database)
    {
        $payment = $this->makePayment($transaction_id, $database);
        return $payment;
    }
}