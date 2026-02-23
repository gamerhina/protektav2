<?php

if (!function_exists('formatWhatsAppNumber')) {
    /**
     * Format phone number for WhatsApp
     * 
     * @param string $phone
     * @return string
     */
    function formatWhatsAppNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        // If starts with 62, keep it as is
        elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}
