<?php

namespace App\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Adds a company relationship and a scope to filter models by company.
 *
 * Implemented by tenant-scoped models. Authorization across companies is
 * enforced by policies that compare the authenticated user's company with the
 * model's company.
 */
trait BelongsToCompany
{
    /**
     * The company the model belongs to.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope query to a given company.
     *
     * @param  Builder<*>  $query
     */
    public function scopeForCompany(Builder $query, Company|int $company): Builder
    {
        $companyId = $company instanceof Company ? $company->getKey() : $company;

        return $query->where('company_id', $companyId);
    }
}
