<?php
return [
    //------------------------//
    // SYSTEM SETTINGS
    //------------------------//
        /**
         * Registration Needs Activation.
         *
         * If set to true, upon registration, users will have to activate their accounts using email account activation.
         */
        'rna' => true,

        /**
         * Login With [ Email => e, username => u, Email or username = > eu]
         *
         * If set to true, users will have to login using email/password combo.
         */
        'lw' => 'eu',

        /**
         * Force Strong Password.
         *
         * If set to true, users will have to use passwords with strength determined by StrengthValidator.
         */
        'fsp' => true,

        /**
         * Set the password reset token expiration time.
         */
        'user.passwordResetTokenExpire' => 3600,

        /**
         * Set the list of usernames that we do not want to allow to users to take upon registration or profile change.
         */
        'user.spamNames' => 'admin|superadmin|creator|thecreator|username',

        'bsVersion' => '4.x', // this will set globally `bsVersion` to Bootstrap 4.x for all Krajee Extensions

    //------------------------//
    // EMAILS
    //------------------------//
        /**
         * Email used in contact form.
         * Users will send you emails to this address.
         */
        'adminEmail' => 'contact@reembolso.mx',

        /**
         * Email used in sign up form, when we are sending email with account activation link.
         * You will send emails to users from this address.
         */
        'supportEmail' => 'support@reembolso.mx',

    //------------------------//
    // Settings Web Site
    //------------------------//
        'settings' => [
            'img-ico' => '@web/img/icon_menu.svg',
            'avg_interval' => 30000,
        ],

    //------------------------//
    // Grupos de Perfiles
    //------------------------//
        'auth_item_group' => [
            'admin'   => 'Administración',
            'cliente' => 'Clientes',
        ],


    //------------------------//
    // Listas desplegables
    //------------------------//
        'listas-desplegables' => require(__DIR__ . '/listas-desplegables.php'),

    'advans' => [
        //'wsdl_url'   => 'https://dev.advans.mx/ws/awscfdi.php?wsdl',
        'wsdl_url'   => 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc',
        'keyPem'     => 'key.pem',
        'cerCer'     => 'cue0304255ra.cer',
        'rfc'        => 'CUE0304255RA',
        'credential' => '1f01dd2fc4c3c1718841eac423c345dc',
    ],


];
