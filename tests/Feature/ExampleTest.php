<?php

test('the application returns a successful response', function () {
    $this->get(route('dashboard'))->assertSuccessful();
});
