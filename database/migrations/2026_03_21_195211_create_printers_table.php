<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['80mm', '58mm', 'a4']);
            $table->enum('connection_type', ['usb', 'network', 'serial', 'bluetooth'])->default('usb');
            $table->string('ip_address', 45)->nullable();  // supports IPv4 & IPv6
            $table->string('port', 20)->nullable();         // COM3, 9100, etc.
            $table->string('mac_address', 17)->nullable();  // AA:BB:CC:DD:EE:FF — Bluetooth only
            $table->boolean('is_connected')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};