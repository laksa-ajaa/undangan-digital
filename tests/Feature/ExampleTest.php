<?php

it('redirects the home page to the wedding invitation page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('invitation.index'));
});
