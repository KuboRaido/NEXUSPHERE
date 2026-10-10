<?php

use Illuminate\Support\Facades\Schema;

it('access_logs テーブルが存在しない', function () {
    expect(Schema::hasTable('access_logs'))->toBeFalse();
});