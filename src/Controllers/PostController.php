<?php

namespace Controllers;

use Services\UserService;
use Services\PostService;
use Exception;
use E_SEND_MAIL_RETURN_CODES;

readonly class PostController {
    public function __construct(private UserService $postService) {
        
    }

    public function addNewPost() {
    }
}