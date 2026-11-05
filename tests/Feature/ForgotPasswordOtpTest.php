<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_request_forgot_password_otp()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'forgotcust@grafika.com',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'forgotcust@grafika.com',
        ]);

        $response->assertRedirect('/forgot-password/verify-otp?email=forgotcust%40grafika.com');
        $response->assertSessionHas('success');

        $otpRecord = PasswordOtp::where('email', 'forgotcust@grafika.com')->first();
        $this->assertNotNull($otpRecord);
        $this->assertEquals(6, strlen($otpRecord->otp));

        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo('forgotcust@grafika.com') && $mail->user->id === $user->id;
        });
    }

    public function test_cannot_verify_invalid_or_expired_otp()
    {
        $user = User::factory()->create([
            'email' => 'forgotcust2@grafika.com',
        ]);

        PasswordOtp::create([
            'email' => 'forgotcust2@grafika.com',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->post('/forgot-password/verify-otp', [
            'email' => 'forgotcust2@grafika.com',
            'otp' => '999999',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull(session('otp_verified_email'));
    }

    public function test_can_verify_valid_otp_and_reset_password()
    {
        $user = User::factory()->create([
            'email' => 'forgotcust3@grafika.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        PasswordOtp::create([
            'email' => 'forgotcust3@grafika.com',
            'otp' => '654321',
            'expires_at' => now()->addMinutes(15),
        ]);

        // Step 1: Verify OTP
        $response1 = $this->post('/forgot-password/verify-otp', [
            'email' => 'forgotcust3@grafika.com',
            'otp' => '654321',
        ]);

        $response1->assertRedirect('/forgot-password/reset');
        $this->assertEquals('forgotcust3@grafika.com', session('otp_verified_email'));
        $this->assertDatabaseMissing('password_otps', ['email' => 'forgotcust3@grafika.com']);

        // Step 2: Reset Password
        $response2 = $this->post('/forgot-password/reset', [
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response2->assertRedirect('/login');
        $response2->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newsecretpassword123', $user->password));
    }
}
