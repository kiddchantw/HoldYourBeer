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

];
