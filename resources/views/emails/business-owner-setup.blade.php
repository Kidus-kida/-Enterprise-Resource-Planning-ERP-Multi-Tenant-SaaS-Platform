@component('mail::message')
# Welcome to {{ $business->name }}!

Hello {{ $business->owner_firstname }},

Your business account has been successfully created on our platform. To get started, please set up your password by clicking the button below.

@component('mail::button', ['url' => $resetUrl])
Set Up My Password
@endcomponent

**Your Account Details:**
- **Business Name:** {{ $business->name }}
- **Email:** {{ $business->owner_email }}
- **Access URL:** https://{{ $business->subdomain ?: \Illuminate\Support\Str::slug($business->name) }}.{{ config('tenancy.central_domains.0', 'ettech.et') }}

This password setup link will expire in 24 hours. If you didn't request this or have any questions, please contact our support team.

Thanks,<br>
{{ appBrandName() }} Team
@endcomponent
