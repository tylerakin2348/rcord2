<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Plan;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $planId = $request->input('plan_id');
        $plan = Plan::find($planId);
        if (!$plan || $plan->name === 'free') {
            return response()->json(['error' => 'Invalid plan selected.'], 400);
        }
        // Example: Set price based on plan (replace with your actual pricing logic)
        $amount = match($plan->name) {
            'base' => 1000, // $10.00
            'full' => 2000, // $20.00
            default => 0,
        };
        if ($amount <= 0) {
            return response()->json(['error' => 'Invalid plan amount.'], 400);
        }
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $intent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'metadata' => [
                'user_id' => $request->user()->id,
                'plan_id' => $planId,
            ],
        ]);
        return response()->json(['client_secret' => $intent->client_secret]);
    }
}
