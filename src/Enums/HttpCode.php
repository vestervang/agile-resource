<?php

namespace Vestervang\AgileResource\Enums;

abstract class HttpCode
{
    // If the request is successful
    const OK = 200;

    // If the entity is created correctly
    const CREATED = 201;

    // The action is successful but there is no content to display
    const NO_CONTENT = 204;

    // Use if the content is split up in parts eg. pagination
    const PARTIAL_CONTENT = 206;

    // The request came through but fail validation
    const BAD_REQUEST = 400;

    // The user isn't authorized
    const UNAUTHORIZED = 401;

    // The user is authenticated but doesn't have the correct permissions
    const FORBIDDEN = 403;

    // The resource wasn't found
    const NOT_FOUND = 404;

    // You shouldn't return this yourself
    const INTERNAL_SERVER_ERROR = 500;

    // You shouldn't return this yourself
    const SERVICE_UNAVAILABLE = 503;
}