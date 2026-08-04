<?php

namespace Modules\Notification\Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Notification\Models\NotificationTemplate;

class NotificationSystemTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('notification_templates') || !Schema::hasTable('notification_template_translations') || !Schema::hasTable('languages')) {
            return;
        }

        $languages = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->get(['id', 'code', 'name']);

        if ($languages->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($languages) {
            foreach ($this->templateKeys() as $key) {
                $template = NotificationTemplate::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'is_active' => true,
                        'system_template' => true,
                    ]
                );

                foreach ($languages as $language) {
                    $content = $this->contentFor($key, (string) $language->code);

                    $template->translations()->updateOrCreate(
                        ['language_id' => (int) $language->id],
                        [
                            'name' => $content['name'],
                            'email_subject' => $content['email_subject'],
                            'email_body' => $content['email_body'],
                            'simple_body' => $content['simple_body'],
                        ]
                    );
                }
            }
        });
    }

    private function templateKeys(): array
    {
        return [
            'email_confirm',
            'forgot_password',
            'welcome',
        ];
    }

    private function contentFor(string $key, string $languageCode): array
    {
        $normalizedCode = mb_strtolower(trim($languageCode));

        return match ($key) {
            'email_confirm' => $this->emailConfirmContent($normalizedCode),
            'forgot_password' => $this->forgotPasswordContent($normalizedCode),
            'welcome' => $this->welcomeContent($normalizedCode),
            default => $this->welcomeContent($normalizedCode),
        };
    }

    private function emailConfirmContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'E-poçt təsdiqi',
                'email_subject' => 'E-poçt ünvanınızı təsdiqləyin',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#0f172a;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">E-poçtunuzu təsdiqləyin</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Hörmətli {customer_name} {customer_surname},
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Qeydiyyat prosesini tamamlamaq və hesabınızı aktivləşdirmək üçün e-poçt ünvanınızı təsdiqləməyiniz lazımdır.
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:10px;">
                            Təsdiq kodunuz:
                        </div>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;padding:14px 28px;background:#f3f4f6;border:1px dashed #d1d5db;border-radius:14px;font-size:30px;line-height:34px;font-weight:700;letter-spacing:8px;color:#111827;">
                                {code}
                            </div>
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;margin-bottom:24px;text-align:center;">
                            Bu kod qısa müddət ərzində etibarlıdır.
                        </div>
                        <div style="text-align:center;margin-bottom:28px;">
                            <a href="{link}" style="display:inline-block;background:#111827;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:600;">
                                E-poçtu təsdiqlə
                            </a>
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;margin-bottom:8px;">
                            Əgər düymə işləmirsə, aşağıdakı linki brauzerinizə köçürüb aça bilərsiniz:
                        </div>
                        <div style="font-size:14px;line-height:24px;word-break:break-all;color:#2563eb;margin-bottom:24px;">
                            {link}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            Bu sorğunu siz etməmisinizsə, məktuba məhəl qoymadan hesabınızın təhlükəsizliyi üçün e-poçtunuzu yoxlamağınız tövsiyə olunur.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:13px;line-height:22px;color:#9ca3af;">
                        Bu məktub {customer_email} ünvanı üçün göndərilib.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Hörmətli {customer_name} {customer_surname}, e-poçt təsdiq kodunuz: {code}. Təsdiq linki: {link}',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Подтверждение электронной почты',
                'email_subject' => 'Подтвердите ваш адрес электронной почты',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#0f172a;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Подтвердите вашу почту</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Уважаемый(ая) {customer_name} {customer_surname},
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Для завершения регистрации и активации аккаунта необходимо подтвердить ваш адрес электронной почты.
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:10px;">
                            Ваш код подтверждения:
                        </div>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;padding:14px 28px;background:#f3f4f6;border:1px dashed #d1d5db;border-radius:14px;font-size:30px;line-height:34px;font-weight:700;letter-spacing:8px;color:#111827;">
                                {code}
                            </div>
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;margin-bottom:24px;text-align:center;">
                            Код действителен ограниченное время.
                        </div>
                        <div style="text-align:center;margin-bottom:28px;">
                            <a href="{link}" style="display:inline-block;background:#111827;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:600;">
                                Подтвердить почту
                            </a>
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;margin-bottom:8px;">
                            Если кнопка не работает, скопируйте и откройте ссылку ниже в браузере:
                        </div>
                        <div style="font-size:14px;line-height:24px;word-break:break-all;color:#2563eb;margin-bottom:24px;">
                            {link}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            Если вы не запрашивали это действие, просто проигнорируйте письмо и при необходимости проверьте безопасность вашей учетной записи.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:13px;line-height:22px;color:#9ca3af;">
                        Это письмо отправлено для адреса {customer_email}.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Здравствуйте, {customer_name} {customer_surname}. Ваш код подтверждения электронной почты: {code}. Ссылка: {link}',
            ],
            default => [
                'name' => 'Email confirmation',
                'email_subject' => 'Confirm your email address',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#0f172a;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Confirm your email</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Dear {customer_name} {customer_surname},
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Please confirm your email address to complete registration and activate your account.
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:10px;">
                            Your verification code:
                        </div>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;padding:14px 28px;background:#f3f4f6;border:1px dashed #d1d5db;border-radius:14px;font-size:30px;line-height:34px;font-weight:700;letter-spacing:8px;color:#111827;">
                                {code}
                            </div>
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;margin-bottom:24px;text-align:center;">
                            This code is valid for a limited time.
                        </div>
                        <div style="text-align:center;margin-bottom:28px;">
                            <a href="{link}" style="display:inline-block;background:#111827;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:600;">
                                Confirm email
                            </a>
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;margin-bottom:8px;">
                            If the button does not work, copy and open the link below in your browser:
                        </div>
                        <div style="font-size:14px;line-height:24px;word-break:break-all;color:#2563eb;margin-bottom:24px;">
                            {link}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            If you did not request this action, you can safely ignore this email.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:13px;line-height:22px;color:#9ca3af;">
                        This email was sent for {customer_email}.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Dear {customer_name} {customer_surname}, your email verification code is {code}. Verification link: {link}',
            ],
        };
    }

    private function forgotPasswordContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Şifrə sıfırlama',
                'email_subject' => 'Şifrənizi sıfırlayın',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#111827;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Şifrə yeniləmə sorğusu</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Hörmətli {customer_name} {customer_surname},
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Hesabınız üçün şifrə yeniləmə sorğusu qəbul edildi. Əgər bu sorğunu siz etmisinizsə, aşağıdakı kod və ya link vasitəsilə davam edin.
                        </div>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;padding:14px 28px;background:#fff7ed;border:1px dashed #fdba74;border-radius:14px;font-size:30px;line-height:34px;font-weight:700;letter-spacing:8px;color:#9a3412;">
                                {code}
                            </div>
                        </div>
                        <div style="text-align:center;margin-bottom:28px;">
                            <a href="{link}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:600;">
                                Şifrəni sıfırla
                            </a>
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;margin-bottom:8px;">
                            Əgər düymə işləmirsə, bu linkdən istifadə edin:
                        </div>
                        <div style="font-size:14px;line-height:24px;word-break:break-all;color:#2563eb;margin-bottom:24px;">
                            {link}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            Əgər bu sorğu sizə aid deyilsə, hesabınızın təhlükəsizliyi üçün şifrənizi dəyişmədən məktubu görməzdən gələ bilərsiniz.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:13px;line-height:22px;color:#9ca3af;">
                        Sorğu {customer_email} hesabı üçün yaradılıb.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Hörmətli {customer_name} {customer_surname}, şifrə sıfırlama kodunuz: {code}. Sıfırlama linki: {link}',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Сброс пароля',
                'email_subject' => 'Сбросьте ваш пароль',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#111827;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Запрос на сброс пароля</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Уважаемый(ая) {customer_name} {customer_surname},
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Мы получили запрос на сброс пароля для вашей учетной записи. Если это были вы, используйте код или ссылку ниже для продолжения.
                        </div>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;padding:14px 28px;background:#fff7ed;border:1px dashed #fdba74;border-radius:14px;font-size:30px;line-height:34px;font-weight:700;letter-spacing:8px;color:#9a3412;">
                                {code}
                            </div>
                        </div>
                        <div style="text-align:center;margin-bottom:28px;">
                            <a href="{link}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:600;">
                                Сбросить пароль
                            </a>
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;margin-bottom:8px;">
                            Если кнопка не работает, используйте ссылку ниже:
                        </div>
                        <div style="font-size:14px;line-height:24px;word-break:break-all;color:#2563eb;margin-bottom:24px;">
                            {link}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:13px;line-height:22px;color:#9ca3af;">
                        Запрос создан для аккаунта {customer_email}.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Здравствуйте, {customer_name} {customer_surname}. Ваш код для сброса пароля: {code}. Ссылка: {link}',
            ],
            default => [
                'name' => 'Forgot password',
                'email_subject' => 'Reset your password',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#111827;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Password reset request</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Dear {customer_name} {customer_surname},
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            We received a request to reset the password for your account. If this was you, use the code or the link below to continue.
                        </div>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;padding:14px 28px;background:#fff7ed;border:1px dashed #fdba74;border-radius:14px;font-size:30px;line-height:34px;font-weight:700;letter-spacing:8px;color:#9a3412;">
                                {code}
                            </div>
                        </div>
                        <div style="text-align:center;margin-bottom:28px;">
                            <a href="{link}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:600;">
                                Reset password
                            </a>
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;margin-bottom:8px;">
                            If the button does not work, use the link below:
                        </div>
                        <div style="font-size:14px;line-height:24px;word-break:break-all;color:#2563eb;margin-bottom:24px;">
                            {link}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            If you did not request a password reset, you can safely ignore this email.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:13px;line-height:22px;color:#9ca3af;">
                        This request was created for {customer_email}.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Dear {customer_name} {customer_surname}, your password reset code is {code}. Reset link: {link}',
            ],
        };
    }

    private function welcomeContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Xoş gəlmisiniz',
                'email_subject' => 'Xoş gəlmisiniz',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:30px;line-height:38px;font-weight:700;color:#111827;margin-bottom:16px;">Xoş gəlmisiniz, {customer_name}!</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Qeydiyyatınız uğurla tamamlandı və hesabınız istifadəyə hazırdır.
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Profilinizi tamamlaya, sifarişlərinizi izləyə və bütün imkanlardan rahat şəkildə istifadə edə bilərsiniz.
                        </div>
                        <div style="padding:18px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:15px;line-height:24px;color:#1e3a8a;margin-bottom:24px;">
                            Qeydiyyatdan keçən e-poçt ünvanı: {customer_email}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            Sizi aramızda görməyə şadıq. Uğurlu və rahat istifadə arzulayırıq.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:13px;line-height:22px;color:#9ca3af;">
                        Bu mesaj yeni hesab yaradıldıqdan sonra avtomatik göndərildi.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Xoş gəlmisiniz, {customer_name} {customer_surname}. Qeydiyyatınız tamamlandı.',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Добро пожаловать',
                'email_subject' => 'Добро пожаловать',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:30px;line-height:38px;font-weight:700;color:#111827;margin-bottom:16px;">Добро пожаловать, {customer_name}!</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Ваша регистрация успешно завершена, и учетная запись готова к использованию.
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Теперь вы можете заполнить профиль, отслеживать заказы и пользоваться всеми возможностями сервиса.
                        </div>
                        <div style="padding:18px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:15px;line-height:24px;color:#1e3a8a;margin-bottom:24px;">
                            Email, указанный при регистрации: {customer_email}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            Рады видеть вас среди наших пользователей.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:13px;line-height:22px;color:#9ca3af;">
                        Это сообщение было отправлено автоматически после регистрации.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Добро пожаловать, {customer_name} {customer_surname}. Ваша регистрация завершена.',
            ],
            default => [
                'name' => 'Welcome',
                'email_subject' => 'Welcome',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:30px;line-height:38px;font-weight:700;color:#111827;margin-bottom:16px;">Welcome, {customer_name}!</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Your registration is complete and your account is now ready to use.
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            You can now complete your profile, track orders and enjoy the full experience.
                        </div>
                        <div style="padding:18px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:15px;line-height:24px;color:#1e3a8a;margin-bottom:24px;">
                            Registered email: {customer_email}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            We are glad to have you with us.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:13px;line-height:22px;color:#9ca3af;">
                        This message was sent automatically after registration.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Welcome, {customer_name} {customer_surname}. Your registration is complete.',
            ],
        };
    }
}
