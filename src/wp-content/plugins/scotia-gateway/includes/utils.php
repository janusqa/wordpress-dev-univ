<?php

function get_extended_hash($order_details)
{
    $order_details_compact = implode("|", $order_details);
    $hash = hash_hmac('sha256', $order_details_compact, get_secret_key(), true);
    $encoded_hash = base64_encode($hash);

    return $encoded_hash;
}
