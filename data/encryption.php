<?php
namespace encryption;

function enc($phrase , $key) {
    return @openssl_encrypt($phrase , "AES-128-ECB" , $key);
}

//---------------------------------------------------------------------------------------

function dec($phrase , $key) {
    return @openssl_decrypt($phrase , "AES-128-ECB" , $key);
}


// print(decrypt("O+1DfdmI+Xc94e2ZcfZvow==" , "1234asdcx#"));