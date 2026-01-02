<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | outcome such as failure due to an invalid password / reset token.
    |
    */

    'reset' => 'Your password has been reset.',
    'sent' => 'We have emailed your password reset link.',
    'throttled' => 'Too many password reset attempts. Please wait before retrying.',
    'token' => 'This password reset token is invalid or has expired.',
    'user' => "We can't find a user with that email address.",

    // Custom error messages
    'mail_error' => 'Unable to send password reset email. Please try again later or contact support.',
    'reset_error' => 'Unable to reset password. Please try again or request a new reset link.',

    // OAuth user hint
    'oauth_hint' => 'If this email is registered, you will receive a password reset email. If you signed up using a third-party login (such as Google), please use that method to sign in directly.',

];
