<?php
require_once 'vendor/autoload.php';

class Payments {
    private $stripe;

    public function __construct() {
        \Stripe\Stripe::setApiKey('your_stripe_secret_key');
        $this->stripe = new \Stripe\StripeClient('your_stripe_secret_key');
    }

    public function createSession($user_id) {
        try {
            $session = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => 'price_xxxxxx', // Create this in Stripe dashboard
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => 'https://yourdomain.com/success',
                'cancel_url' => 'https://yourdomain.com/cancel',
                'client_reference_id' => $user_id,
            ]);
            return json_encode(['sessionId' => $session->id]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $payments = new Payments();
    echo $payments->createSession($data['user_id']);
}
?>
