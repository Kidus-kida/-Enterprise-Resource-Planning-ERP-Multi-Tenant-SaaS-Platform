<?php

namespace App\Mail;

use App\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BusinessOwnerSetup extends Mailable
{
    use Queueable, SerializesModels;

    public $business;
    public $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Business $business, string $token)
    {
        $this->business = $business;
        
        // Generate password reset URL for tenant subdomain
        $subdomain = $business->subdomain ?: \Illuminate\Support\Str::slug($business->name);
        // Use env variable directly as config might be missing/defaulting
        $centralDomain = env('CENTRAL_DOMAIN', config('tenancy.central_domains.0', 'ettech.et'));
        
        $domain = $subdomain . '.' . $centralDomain;
        // Corrected route path to match auth.php: 'reset-password/{token}'
        $this->resetUrl = "https://{$domain}/reset-password/{$token}?email=" . urlencode($business->owner_email);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to ' . $this->business->name . ' - Setup Your Account')
            ->markdown('emails.business-owner-setup');
    }
}
