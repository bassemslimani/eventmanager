<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR Code Format
    |--------------------------------------------------------------------------
    |
    | The default format for the generated QR code image.
    | Supported: "png", "eps", "svg"
    |
    */
    'format' => 'png',

    /*
    |--------------------------------------------------------------------------
    | QR Code Size
    |--------------------------------------------------------------------------
    |
    | The default size of the QR code in pixels.
    |
    */
    'size' => 300,

    /*
    |--------------------------------------------------------------------------
    | Error Correction Level
    |--------------------------------------------------------------------------
    |
    | The error correction level for the QR code.
    | Supported: "L", "M", "Q", "H"
    |
    */
    'error_correction' => 'H',

    /*
    |--------------------------------------------------------------------------
    | Image Backend
    |--------------------------------------------------------------------------
    |
    | The image backend to use for generating QR codes.
    | "gd" is more widely available than "imagick"
    | Supported: "gd", "imagick", "svg"
    |
    */
    'image_backend' => 'gd',
];
