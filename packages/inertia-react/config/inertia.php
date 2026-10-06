<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'assetEntry' => Env::string('INERTIA_REACT_CLIENT_ENTRY', 'app/react-web/resources/js/app.jsx'),
];
