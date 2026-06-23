<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;
use OpenApi\Attributes as OA;

class StripeController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Create a Stripe Checkout Session for one or more reservations.
     * Returns a hosted checkout URL the SPA redirects to. The authoritative
     * reservation set + amount are computed server-side and stored in the
     * session metadata so the later verify step cannot be tampered with.
     */
    #[OA\Post(path: '/transactions/stripe', summary: 'Create a Stripe Checkout Session', tags: ['Payments'], security: [['bearerAuth' => []]])]
    public function createSession(Request $request)
    {
        $data = $request->validate([
            'reservation_ids'   => 'required_without:reservation_id|array|min:1',
            'reservation_ids.*' => 'integer',
            'reservation_id'    => 'required_without:reservation_ids|integer',
        ]);

        $ids = $data['reservation_ids'] ?? [$data['reservation_id']];
        $user = Auth::user();

        $reservations = reservation::with('session')
            ->whereIn('id', $ids)
            ->where('user_id', $user->id)
            ->get();

        if ($reservations->isEmpty()) {
            return response()->json(['error' => 'No valid reservations found.'], 404);
        }
        if ($reservations->contains(fn ($r) => $r->status === 'accepted')) {
            return response()->json(['error' => 'One or more reservations are already paid.'], 400);
        }

        $unitAmount = (int) round($reservations->first()->session->price * 100);
        $count = $reservations->count();

        $session = Cashier::stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency'     => strtolower(config('cashier.currency', 'usd')),
                    'product_data' => ['name' => "Cinehall — {$count} seat(s)"],
                    'unit_amount'  => $unitAmount,
                ],
                'quantity' => $count,
            ]],
            'success_url' => config('app.url') . '/booking/success?provider=stripe&cs={CHECKOUT_SESSION_ID}',
            'cancel_url'  => config('app.url') . '/booking/cancel',
            'metadata'    => [
                'user_id'         => $user->id,
                'reservation_ids' => $reservations->pluck('id')->implode(','),
            ],
        ]);

        return response()->json(['url' => $session->url]);
    }

    /**
     * Verify a completed Stripe Checkout Session and confirm the reservations.
     * Called by the SPA (with JWT) after Stripe redirects back. Confirms only
     * if Stripe reports the session as paid and the metadata user matches.
     */
    #[OA\Post(path: '/transactions/stripe/verify', summary: 'Verify Stripe payment and issue tickets', tags: ['Payments'], security: [['bearerAuth' => []]])]
    public function verify(Request $request)
    {
        $request->validate(['cs' => 'required|string']);
        $user = Auth::user();

        $cs = Cashier::stripe()->checkout->sessions->retrieve($request->cs);

        if (!$cs || ($cs->metadata['user_id'] ?? null) != $user->id) {
            return response()->json(['error' => 'Payment session not found.'], 404);
        }
        if ($cs->payment_status !== 'paid') {
            return response()->json(['error' => 'Payment not completed.', 'status' => $cs->payment_status], 402);
        }

        $ids = array_filter(explode(',', $cs->metadata['reservation_ids'] ?? ''));
        $reservations = reservation::whereIn('id', $ids)
            ->where('user_id', $user->id)
            ->get();

        $tickets = [];
        foreach ($reservations as $reservation) {
            Payment::firstOrCreate(
                ['transaction_id' => $cs->id . '-' . $reservation->id],
                [
                    'reservation_id' => $reservation->id,
                    'amount'         => $reservation->session->price ?? 0,
                    'status'         => 'completed',
                    'payment_method' => 'stripe',
                ]
            );
            $ticket = $this->reservationService->confirmPayment($reservation);
            if ($ticket) {
                $tickets[] = $ticket->id;
            }
        }

        return response()->json([
            'status'           => 'success',
            'message'          => 'Payment confirmed. Your tickets are ready.',
            'reservation_ids'  => $reservations->pluck('id'),
            'ticket_ids'       => $tickets,
        ]);
    }
}
