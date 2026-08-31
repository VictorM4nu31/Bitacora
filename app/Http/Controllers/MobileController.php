<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MobileController extends Controller
{
    /**
     * Mobile login screen (rendered as a Blade view for NativePHP).
     */
    public function login(): View
    {
        return view('mobile.login');
    }

    /**
     * Handle a mobile login attempt.
     */
    public function submitLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('Invalid credentials.')]);
        }

        $request->session()->regenerate();

        return redirect()->route('mobile.index');
    }

    /**
     * Log the user out of the mobile view.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mobile.login');
    }

    /**
     * Mobile home: list the user's company service orders.
     */
    public function index(Request $request): View
    {
        $orders = ServiceOrder::query()
            ->forCompany($request->user()->company_id)
            ->with(['customer:id,name', 'equipment:id,name', 'report:id,status'])
            ->orderByDesc('created_at')
            ->take(30)
            ->get()
            ->map(fn (ServiceOrder $order) => [
                'id' => $order->id,
                'customer' => $order->customer?->name,
                'equipment' => $order->equipment?->name,
                'status' => $order->status->label(),
                'report' => $order->report?->status?->value,
            ]);

        return view('mobile.index', ['orders' => $orders, 'user' => $request->user()]);
    }

    /**
     * Mobile details for a single service order.
     */
    public function show(Request $request, ServiceOrder $serviceOrder): View
    {
        $serviceOrder->load(['customer', 'equipment', 'technician']);

        return view('mobile.show', ['order' => $serviceOrder]);
    }
}
