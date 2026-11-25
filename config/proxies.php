<?php

return [
    'trusted' => array_values(array_filter(array_map('trim', explode(',', env('TRUSTED_PROXIES', ''))))),
    'hosts' => array_values(array_filter(array_map('trim', explode(',', env('TRUSTED_PROXY_HOSTS', ''))))),
];
