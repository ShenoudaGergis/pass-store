<?php
namespace encryption;

function encrypt($phrase , $key) {
    return @openssl_encrypt($phrase , "AES-128-ECB" , $key);
}

//---------------------------------------------------------------------------------------

function decrypt($phrase , $key) {
    return @openssl_decrypt($phrase , "AES-128-ECB" , $key);
}


// print(decrypt("O+1DfdmI+Xc94e2ZcfZvow==" , "1234asdcx#"));