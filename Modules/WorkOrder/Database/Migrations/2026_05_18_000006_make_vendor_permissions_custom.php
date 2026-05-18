<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;

return new class extends Migration
{
    public function up()
    {
        Permission::whereIn('name', [
            'add_vendor',
            'view_vendor',
            'edit_vendor',
            'delete_vendor'
        ])->update(['is_custom' => 1]);
    }

    public function down()
    {
        Permission::whereIn('name', [
            'add_vendor',
            'view_vendor',
            'edit_vendor',
            'delete_vendor'
        ])->update(['is_custom' => 0]);
    }
};
