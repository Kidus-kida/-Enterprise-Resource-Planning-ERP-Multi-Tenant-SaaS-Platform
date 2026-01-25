<?php

namespace App\Services;

use App\Business;
use App\Company;
use App\Exceptions\CompanyLimitExceededException;

class CompanyService
{
    /**
     * Create a new company for a business.
     *
     * @param Business $business
     * @param array $data
     * @return Company
     * @throws \Exception
     */
    public function createCompany(Business $business, array $data)
    {
        $this->checkCompanyLimit($business);

        $data['business_id'] = $business->id;
        
        // Ensure default logic (if this is first company, or explicit default)
        // If is_default is true, unset other defaults? 
        // For now simple creation.
        
        $company = Company::create($data);

        return $company;
    }

    /**
     * Check if the business can create more companies based on subscription.
     *
     * @param Business|int $business Business model or business_id
     * @return bool
     * @throws \Exception
     */
    public function checkCompanyLimit($business)
    {
        // Handle both Business model and business_id
        $business_id = is_object($business) ? $business->id : $business;
        
        // Get subscription from central DB (always use 'mysql' connection)
        $subscription = \DB::connection('mysql')
            ->table('subscriptions')
            ->where('business_id', $business_id)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$subscription) {
            // No subscription found - could be trial or legacy, allow 1 company as default
            $currentCount = Company::count();
            if ($currentCount >= 1) {
                throw new \Exception("No active subscription found. Please subscribe to a package to create more companies.");
            }
            return true;
        }

        $limit = $subscription->company_count ?? 1; // Default to 1 if not set
        
        if ($limit == 0) {
            return true; // Unlimited companies
        }

        // Count companies in current database context (tenant or central)
        $currentCount = Company::count();

        if ($currentCount >= $limit) {
            throw new \Exception("You have reached the maximum number of companies ({$limit}) allowed by your package. Please upgrade to create more companies.");
        }

        return true;
    }
}
