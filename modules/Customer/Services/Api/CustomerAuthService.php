<?php

namespace Modules\Customer\Services\Api;

use App\Support\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Cart\Services\CartService;
use Modules\Compare\Services\CompareService;
use Modules\Customer\Auth\OpenCartPasswordHasher;
use Modules\Customer\Http\Resources\CustomerAuthResource;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerOtpCode;
use Modules\Customer\Rules\PasswordPolicyRule;
use Modules\Customer\Services\CustomerOtpService;
use Modules\Favorite\Services\FavoriteService;

final class CustomerAuthService
{
    public function __construct(
        private readonly CustomerOtpService     $otpService,
        private readonly CartService            $cartService,
        private readonly FavoriteService        $favoriteService,
        private readonly CompareService         $compareService,
        private readonly OpenCartPasswordHasher $openCartPasswordHasher
    )
    {
    }

    public function register(array $data, ?string $guestToken = null): array
    {
        (new PasswordPolicyRule())->validate('password', $data['password'], function ($message): void {
            throw ValidationException::withMessages(['password' => [$message]]);
        });

        $result = DB::transaction(function () use ($data, $guestToken): array {
            $customer = Customer::query()
                ->where('email', $data['email'])
                ->lockForUpdate()
                ->first();

            if ($customer && $customer->email_verified_at !== null) {
                throw ValidationException::withMessages([
                    'email' => [__('This email is already registered.')],
                ]);
            }

            if ($customer) {
                $customer->forceFill([
                    'name'              => $data['name'],
                    'surname'           => $data['surname'] ?? null,
                    'passport_fin'      => $data['passport_fin'] ?? null,
                    'date_of_birth'     => $data['date_of_birth'] ?? null,
                    'phone'             => $data['phone'] ?? null,
                    'country_id'        => $data['country_id'] ?? null,
                    'password'          => Hash::make($data['password']),
                    'opencart_password' => null,
                    'opencart_salt'     => null,
                    'password_driver'   => 'laravel',
                    'is_active'         => true,
                    'email_verified_at' => null,
                ])->save();

                $customer->tokens()->delete();

                CustomerOtpCode::query()
                    ->where('customer_id', $customer->id)
                    ->whereNull('used_at')
                    ->delete();
            } else {
                $customer = Customer::create([
                    'name'              => $data['name'],
                    'surname'           => $data['surname'] ?? null,
                    'passport_fin'      => $data['passport_fin'] ?? null,
                    'date_of_birth'     => $data['date_of_birth'] ?? null,
                    'email'             => $data['email'],
                    'phone'             => $data['phone'] ?? null,
                    'country_id'        => $data['country_id'] ?? null,
                    'password'          => Hash::make($data['password']),
                    'opencart_password' => null,
                    'opencart_salt'     => null,
                    'password_driver'   => 'laravel',
                    'is_active'         => true,
                    'email_verified_at' => null,
                ]);
            }

            $token = is_string($guestToken) ? trim($guestToken) : '';

            if ($token !== '') {
                $this->cartService->mergeGuestIntoCustomer($token, $customer);
                $this->favoriteService->mergeGuestIntoCustomer($token, $customer);
                $this->compareService->mergeGuestIntoCustomer($token, $customer);
            }

            $authToken = $customer->createToken('customer-auth')->plainTextToken;

            return [
                'token'    => $authToken,
                'customer' => $customer,
            ];
        });

        $this->otpService->generateAndSendForEmailVerification($result['customer']);

        return [
            'token'   => $result['token'],
            'user'    => new CustomerAuthResource($result['customer']->fresh()),
            'message' => __('Your account has been created successfully. A verification code has been sent to your email.'),
        ];
    }

    public function login(array $credentials, ?string $guestToken = null, ?string $ipAddress = null): array
    {
        $rateLimitKey = $this->loginRateLimitKey($credentials['email'], $ipAddress);

        $this->ensureLoginIsNotLocked($rateLimitKey);

        $customer = Customer::query()
            ->where('email', $credentials['email'])
            ->first();

        if (!$customer) {
            $this->incrementLoginAttempts($rateLimitKey);

            throw ValidationException::withMessages([
                'message' => [__('Email or password is incorrect.')],
            ]);
        }

        $passwordMatched = Hash::check($credentials['password'], $customer->password);

        if (
            !$passwordMatched
            && $customer->password_driver === 'opencart'
            && $this->openCartPasswordHasher->check(
                $credentials['password'],
                $customer->opencart_password,
                $customer->opencart_salt
            )
        ) {
            $passwordMatched = true;

            $customer->forceFill([
                'password'          => Hash::make($credentials['password']),
                'opencart_password' => null,
                'opencart_salt'     => null,
                'password_driver'   => 'laravel',
            ])->save();
        }

        if (!$passwordMatched) {
            $this->incrementLoginAttempts($rateLimitKey);

            throw ValidationException::withMessages([
                'message' => [__('Email or password is incorrect.')],
            ]);
        }

        if (!$customer->is_active) {
            abort(403, __('Account is disabled.'));
        }

        if (!$customer->email_verified_at) {
            throw ValidationException::withMessages([
                'message'                     => [__('Email is not verified. Please verify your email using the OTP code.')],
                'requires_email_verification' => ['1'],
            ]);
        }

        RateLimiter::clear($rateLimitKey);

        $token = is_string($guestToken) ? trim($guestToken) : '';

        if ($token !== '') {
            $this->cartService->mergeGuestIntoCustomer($token, $customer);
            $this->favoriteService->mergeGuestIntoCustomer($token, $customer);
            $this->compareService->mergeGuestIntoCustomer($token, $customer);
        }

        $authToken = $customer->createToken('customer-auth')->plainTextToken;

        return [
            'token' => $authToken,
            'user'  => new CustomerAuthResource($customer),
        ];
    }

    public function logout(Customer $customer): void
    {
        $customer->tokens()->delete();
    }

    public function refreshToken(Customer $customer): string
    {
        $customer->tokens()->delete();

        return $customer->createToken('customer-auth')->plainTextToken;
    }

    public function sendPasswordResetOtp(string $email): void
    {
        $customer = Customer::query()->where('email', $email)->first();

        if (!$customer) {
            return;
        }

        $this->otpService->generateAndSendForPasswordReset($customer);
    }

    public function verifyOtp(array $data): array
    {
        return match ($data['type']) {
            CustomerOtpCode::TYPE_EMAIL_VERIFICATION => $this->verifyEmailOtp($data),
            CustomerOtpCode::TYPE_PASSWORD_RESET     => $this->resetPasswordWithOtp($data),
            default                                  => throw ValidationException::withMessages([
                'type' => [__('Selected verification type is invalid.')],
            ]),
        };
    }

    public function resetPasswordWithOtp(array $data): array
    {
        (new PasswordPolicyRule())->validate('password', $data['password'], function ($message): void {
            throw ValidationException::withMessages(['password' => [$message]]);
        });

        $customer = Customer::query()->where('email', $data['email'])->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'email' => [__('User with this email was not found.')],
            ]);
        }

        $result = $this->otpService->verify(
            $customer,
            CustomerOtpCode::TYPE_PASSWORD_RESET,
            $data['code']
        );

        if (!$result['success']) {
            throw ValidationException::withMessages([
                'code' => [$this->otpFailureMessage($result['reason'])],
            ]);
        }

        DB::transaction(function () use ($customer, $data): void {
            $customer->forceFill([
                'password'          => Hash::make($data['password']),
                'opencart_password' => null,
                'opencart_salt'     => null,
                'password_driver'   => 'laravel',
            ])->save();

            $customer->tokens()->delete();
        });

        return [
            'data'    => [],
            'message' => __('Password has been successfully reset.'),
        ];
    }

    public function verifyEmailOtp(array $data): array
    {
        $customer = Customer::query()->where('email', $data['email'])->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'email' => [__('User with this email was not found.')],
            ]);
        }

        if ($customer->email_verified_at) {
            return [
                'data'    => [],
                'message' => __('Email has already been verified.'),
            ];
        }

        $result = $this->otpService->verify(
            $customer,
            CustomerOtpCode::TYPE_EMAIL_VERIFICATION,
            $data['code']
        );

        if (!$result['success']) {
            throw ValidationException::withMessages([
                'code' => [$this->otpFailureMessage($result['reason'])],
            ]);
        }

        DB::transaction(function () use ($customer): void {
            $customer->forceFill([
                'email_verified_at' => now(),
            ])->save();

            $this->otpService->sendWelcome($customer);
        });

        return [
            'data'    => [],
            'message' => __('Email has been successfully verified.'),
        ];
    }

    public function resendEmailVerificationOtp(string $email): void
    {
        $customer = Customer::query()->where('email', $email)->first();

        if (!$customer) {
            return;
        }

        if ($customer->email_verified_at) {
            return;
        }

        $this->otpService->generateAndSendForEmailVerification($customer);
    }

    public function changePassword(Customer $customer, array $data): void
    {
        $currentPasswordMatched = Hash::check($data['current_password'], $customer->password);

        if (
            !$currentPasswordMatched
            && $customer->password_driver === 'opencart'
            && $this->openCartPasswordHasher->check(
                $data['current_password'],
                $customer->opencart_password,
                $customer->opencart_salt
            )
        ) {
            $currentPasswordMatched = true;
        }

        if (!$currentPasswordMatched) {
            throw ValidationException::withMessages([
                'current_password' => [__('Current password is incorrect.')],
            ]);
        }

        (new PasswordPolicyRule())->validate('password', $data['password'], function ($message): void {
            throw ValidationException::withMessages(['password' => [$message]]);
        });

        DB::transaction(function () use ($customer, $data): void {
            $customer->forceFill([
                'password'          => Hash::make($data['password']),
                'opencart_password' => null,
                'opencart_salt'     => null,
                'password_driver'   => 'laravel',
            ])->save();

            $customer->tokens()->delete();
        });
    }

    private function ensureLoginIsNotLocked(string $rateLimitKey): void
    {
        if (!RateLimiter::tooManyAttempts($rateLimitKey, $this->maxLoginAttempts())) {
            return;
        }

        $seconds = RateLimiter::availableIn($rateLimitKey);
        $minutes = max(1, (int)ceil($seconds / 60));

        throw ValidationException::withMessages([
            'message' => [
                trans_choice(
                    'Too many login attempts. Please try again in :minutes minute.|Too many login attempts. Please try again in :minutes minutes.',
                    $minutes,
                    ['minutes' => $minutes]
                )
            ],
        ]);
    }

    private function incrementLoginAttempts(string $rateLimitKey): void
    {
        RateLimiter::hit($rateLimitKey, $this->lockMinutes() * 60);
    }

    private function loginRateLimitKey(string $email, ?string $ipAddress): string
    {
        return 'customer-login:' . Str::lower(trim($email)) . '|' . ($ipAddress ?: 'unknown');
    }

    private function maxLoginAttempts(): int
    {
        return max(1, (int)Settings::get('security', 'max_login_attempts', 5));
    }

    private function lockMinutes(): int
    {
        return max(1, (int)Settings::get('security', 'lock_minutes', 15));
    }

    private function otpFailureMessage(?string $reason): string
    {
        return match ($reason) {
            'not_found'         => __('Active verification code was not found. Please request a new code.'),
            'already_used'      => __('This verification code has already been used.'),
            'expired'           => __('Verification code has expired.'),
            'too_many_attempts' => __('Too many invalid attempts. Please request a new code.'),
            'invalid_code'      => __('Verification code is incorrect.'),
            default             => __('Verification code could not be verified.'),
        };
    }
}
