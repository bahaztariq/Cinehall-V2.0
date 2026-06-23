<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\reservation;
use App\Models\session;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    #[OA\Get(
        path: '/statistics',
        summary: 'Get system-wide statistics (Admin only)',
        tags: ['Admin'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation'),
            new OA\Response(response: 403, description: 'Forbidden - User is not an admin')
        ]
    )]
    public function statistics()
    {
        Gate::authorize('admin');

        $now = Carbon::now();
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        $startOfMonth = Carbon::now()->startOfMonth();

        return response()->json([
        
            'all_users'        => User::all(),
            'all_reservations' => reservation::with(['user', 'session.film'])->get(),

            'counts' => [
                'total_users'        => User::count(),
                'total_reservations' => reservation::count(),
                'pending_reservations'  => reservation::where('status', 'pending')->count(),
                'accepted_reservations' => reservation::where('status', 'accepted')->count(),
                'canceled_reservations' => reservation::where('status', 'canceled')->count(),
            ],

            'rankings' => [
                'all_time' => session::with('film')
                    ->withCount('reservations')
                    ->orderBy('reservations_count', 'desc')
                    ->take(5)->get(),

                'last_six_months' => session::with('film')
                    ->withCount(['reservations' => function ($query) use ($sixMonthsAgo) {
                        $query->where('created_at', '>=', $sixMonthsAgo);
                    }])
                    ->orderBy('reservations_count', 'desc')
                    ->take(5)->get(),

                'this_month' => session::with('film')
                    ->withCount(['reservations' => function ($query) use ($startOfMonth) {
                        $query->where('created_at', '>=', $startOfMonth);
                    }])
                    ->orderBy('reservations_count', 'desc')
                    ->take(5)->get(),
            ]
        ]);
    }



    #[OA\Patch(
        path: '/users/{user}/toggle-status',
        summary: 'Toggle user status between Active and Banned (Admin only)',
        tags: ['Admin'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, description: 'User ID', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Status toggled successfully'),
            new OA\Response(response: 403, description: 'Forbidden/Unauthorized')
        ]
    )]
    public function toggleUserStatus(User $user)
    {
        Gate::authorize('admin');

        if (Auth::id() === $user->id) {
            return response()->json(['message' => 'You cannot modify your own status.'], 403);
        }

        if ($user->status === 'active') {
            $user->update([
                'status'    => 'Banned',
                'banned_at' => now()
            ]);
            $message = "User '{$user->name}' is now Banned.";
        } else {
            $user->update([
                'status'    => 'active',
                'banned_at' => null
            ]);
            $message = "User '{$user->name}' is now active.";
        }

        return response()->json([
            'message' => $message,
            'user'    => $user
        ]);
    }

    /**
     * List all bookings (Admin only), optional status filter, paginated.
     */
    public function reservations(Request $request)
    {
        Gate::authorize('admin');

        $query = reservation::with(['user', 'session.film.image', 'session.room', 'seat'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        return response()->json([
            'reservations' => $query->paginate(20),
        ]);
    }

    /**
     * Admin cancel any booking (frees the seat).
     */
    public function cancelReservation(reservation $reservation)
    {
        Gate::authorize('admin');

        $reservation->update(['status' => 'canceled']);

        return response()->json([
            'message'     => 'Booking canceled.',
            'reservation' => $reservation->fresh(['user', 'session.film', 'seat']),
        ]);
    }

    /**
     * Payments / revenue overview (Admin only).
     */
    public function payments(Request $request)
    {
        Gate::authorize('admin');

        $startOfMonth = Carbon::now()->startOfMonth();

        return response()->json([
            'totals' => [
                'revenue_all_time' => (float) Payment::where('status', 'completed')->sum('amount'),
                'revenue_month'    => (float) Payment::where('status', 'completed')->where('created_at', '>=', $startOfMonth)->sum('amount'),
                'transactions'     => Payment::count(),
                'completed'        => Payment::where('status', 'completed')->count(),
                'failed'           => Payment::where('status', 'failed')->count(),
            ],
            'payments' => Payment::with(['reservation.user', 'reservation.session.film'])
                ->latest()
                ->paginate(20),
        ]);
    }
}