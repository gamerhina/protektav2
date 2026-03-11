<?php

namespace App\Traits;

use App\Models\LandingPageSetting;
use Illuminate\Notifications\Messages\MailMessage;

trait CustomNotificationSalutation
{
    /**
     * Get the default salutation for mail notifications.
     */
    protected function getSalutation(): string
    {
        $brandingSettings = LandingPageSetting::first();
        $appName = optional($brandingSettings)->app_name ?? config('app.name');
        
        return "Regards,\nAdmin " . $appName;
    }

    /**
     * Create a new MailMessage with the custom salutation.
     */
    protected function newMailMessage(): MailMessage
    {
        return (new MailMessage)->salutation($this->getSalutation());
    }
}
