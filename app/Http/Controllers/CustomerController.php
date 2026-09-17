<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers within the user's company.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->forCompany($request->user()->company_id)
            ->when($request->string('search')->trim()->value(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('customers/index', [
            'customers' => $customers,
            'filters' => [
                'search' => $request->string('search')->trim()->value(),
            ],
            'storeUrl' => route('customers.store'),
        ]);
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Gate::authorize('create', Customer::class);

        Customer::create($request->validated() + [
            'company_id' => $request->user()->company_id,
        ]);

        return to_route('customers.index');
    }

    /**
     * Display the specified customer.
     */
    public function show(Request $request, Customer $customer): Response
    {
        Gate::authorize('view', $customer);

        return Inertia::render('customers/show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        Gate::authorize('update', $customer);

        $customer->update($request->validated());

        return to_route('customers.show', $customer);
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);

        $customer->delete();

        return to_route('customers.index');
    }
}
