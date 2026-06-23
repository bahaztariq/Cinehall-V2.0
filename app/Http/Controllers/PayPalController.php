<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use OpenApi\Attributes as OA;

class PayPalController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Create a PayPal order for one or more reservations.
     * Authoritative reservation set + amount are computed server-side and
     * stored in the order's custom_id for tamper-proof capture.
     */
    #[OA\Post(path: '/transactions/paypal', summary: 'Create a PayPal order', tags: ['Payments'], security: [['bearerAuth' => []]])]
    public function createTransaction(Request $request)
    {
        $data = $request->validate([
            'reservation_ids'   => 'required_without:reservation_id|array|min:1',
            'reservation_ids.*' => 'integer',
            'reservation_id'    => 'required_without:reservation_ids|integer',
        ]);

        $ids = $data['reservation_ids'] ?? [$data['reservation_id']];

        $reservations = reservation::with('session')
            ->whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->get();

        if ($reservations->isEmpty()) {
            return response()->json(['error' => 'No valid reservations found.'], 404);
        }
        if ($reservations->contains(fn ($r) => $r->status === 'accepted')) {
            return response()->json(['error' => 'One or more reservations are already paid.'], 400);
        }

        $amount = number_format($reservations->sum(fn ($r) => $r->session->price), 2, '.', '');
        $idList = $reservations->pluck('id')->implode(',');

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->createOrder([
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => config('app.url') . '/booking/success?provider=paypal',
                'cancel_url' => config('app.url') . '/booking/cancel',
            ],
            'purchase_units' => [[
                'custom_id' => $idList,
                'amount' => [
                    'currency_code' => config('paypal.currency', 'USD'),
                    'value'         => $amount,
                ],
            ]],
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return response()->json(['approval_url' => $link['href']]);
                }
            }
        }

        return response()->json(['error' => $response['message'] ?? 'Unable to create PayPal order.'], 500);
    }

    /**
     * Capture an approved PayPal order and confirm the reservations.
     * Called by the SPA (with JWT) after PayPal redirects back with a token.
     */
    #[OA\Post(path: '/transactions/paypal/capture', summary: 'Capture PayPal payment and issue tickets', tags: ['Payments'], security: [['bearerAuth' => []]])]
    public function captureTransaction(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->capturePaymentOrder($request->token);

        if (($response['status'] ?? null) !== 'COMPLETED') {
            return response()->json(['error' => $response['message'] ?? 'Payment capture failed.'], 402);
        }

        $transactionId = $response['id'];
        $customId = $response['purchase_units'][0]['custom_id'] ?? '';
        $ids = array_filter(explode(',', $customId));

        $reservations = reservation::whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->get();

        $tickets = [];
        foreach ($reservations as $reservation) {
            Payment::firstOrCreate(
                ['transaction_id' => $transactionId . '-' . $reservation->id],
                [
                    'reservation_id' => $reservation->id,
                    'amount'         => $reservation->session->price ?? 0,
                    'status'         => 'completed',
                    'payment_method' => 'paypal',
                ]
            );
            $ticket = $this->reservationService->confirmPayment($reservation);
            if ($ticket) {
                $tickets[] = $ticket->id;
            }
        }

        return response()->json([
            'status'          => 'success',
            'message'         => 'Payment confirmed. Your tickets are ready.',
            'reservation_ids' => $reservations->pluck('id'),
            'ticket_ids'      => $tickets,
        ]);
    }
}
