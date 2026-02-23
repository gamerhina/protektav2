<?php
/**
 * phpMyAdmin configuration file
 */

// Use this file for online configuration
if (is_readable('config.upload.inc.php')) {
    include 'config.upload.inc.php';
}

// Set your secret blowfish for cookie authentication
$cfg['blowfish_secret'] = 'laragonblowfishsecret123456789012';

// Servers configuration
$i = 0;
$i++;

/* Server: localhost [1] */
$cfg['Servers'][$i]['auth_type'] = 'cookie';
$cfg['Servers'][$i]['host'] = 'localhost';
$cfg['Servers'][$i]['connect_type'] = 'tcp';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = true;
$cfg['Servers'][$i]['user'] = 'root';
$cfg['Servers'][$i]['password'] = '';
$cfg['Servers'][$i]['hide_db'] = '(mysql|information_schema|performance_schema|sys)';

// Set default collation
$cfg['DefaultConnectionCollation'] = 'utf8mb4_unicode_ci';
$cfg['DefaultTableCollation'] = 'utf8mb4_unicode_ci';

// Upload directory
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';

// Theme
$cfg['ThemeDefault'] = 'pmahomme';

// Features
$cfg['ShowPhpInfo'] = false;
$cfg['ShowServerInfo'] = true;
$cfg['ShowChgPassword'] = true;
$cfg['LoginCookieValidity'] = 1800;
$cfg['LoginCookieStore'] = 0;
$cfg['LoginCookieDeleteAll'] = true;

// Disable some potentially dangerous features
$cfg['AllowUserDropDatabase'] = false;
$cfg['TrustedProxies'] = array();
$cfg['CheckConfigurationPermissions'] = false;

// Enable compression for export
$cfg['CompressOnFly'] = true;

// Show all databases by default
$cfg['Servers'][$i]['pmadb'] = '';
$cfg['Servers'][$i]['relation'] = '';
$cfg['Servers'][$i]['table_info'] = '';
$cfg['Servers'][$i]['table_coords'] = '';
$cfg['Servers'][$i]['pdf_pages'] = '';
$cfg['Servers'][$i]['column_info'] = '';
$cfg['Servers'][$i]['bookmarktable'] = '';
$cfg['Servers'][$i]['history'] = '';
$cfg['Servers'][$i]['recent'] = '';
$cfg['Servers'][$i]['favorite'] = '';
$cfg['Servers'][$i]['table_uiprefs'] = '';
$cfg['Servers'][$i]['tracking'] = '';
$cfg['Servers'][$i]['userconfig'] = '';
$cfg['Servers'][$i]['users'] = '';
$cfg['Servers'][$i]['usergroups'] = '';
$cfg['Servers'][$i]['navigationhiding'] = '';
$cfg['Servers'][$i]['central_columns'] = '';
$cfg['Servers'][$i]['designer_settings'] = '';
$cfg['Servers'][$i]['export_templates'] = '';

?>