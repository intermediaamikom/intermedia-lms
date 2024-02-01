<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::table('users', function (Blueprint $table) {
        $table->uuid('uuid')->first();
      });
    
      $users = User::all();
      if($users) {
        foreach($users as $user) {
          $user->uuid = Str::uuid();
          $user->update();
        }
      }
    
      Schema::table('users', function (Blueprint $table) {
        $table->dropPrimary();
        $table->unsignedInteger('id')->change();
        $table->dropColumn('id');
      });
    
      Schema::table('users', function (Blueprint $table) {
        $table->primary('uuid');
        $table->renameColumn('uuid', 'id');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
          $table->id()->change();
        });
    }
};
