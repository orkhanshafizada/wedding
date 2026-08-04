<?php

namespace Modules\Order\Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Notification\Models\NotificationTemplate;

class OrderStatusTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('notification_templates') || !Schema::hasTable('notification_template_translations') || !Schema::hasTable('languages')) {
            return;
        }

        $languages = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'code', 'name']);

        if ($languages->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($languages): void {
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
            'order_status_processing',
            'order_status_preparing',
            'order_status_cancelled',
            'order_status_rejected',
            'order_status_completed',
            'order_status_delivered',
            'order_status_returned',
            'order_status_failed',
        ];
    }

    private function contentFor(string $key, string $languageCode): array
    {
        $normalizedCode = mb_strtolower(trim($languageCode));

        return match ($key) {
            'order_status_processing' => $this->orderStatusProcessingContent($normalizedCode),
            'order_status_preparing' => $this->orderStatusPreparingContent($normalizedCode),
            'order_status_cancelled' => $this->orderStatusCancelledContent($normalizedCode),
            'order_status_rejected' => $this->orderStatusRejectedContent($normalizedCode),
            'order_status_completed' => $this->orderStatusCompletedContent($normalizedCode),
            'order_status_delivered' => $this->orderStatusDeliveredContent($normalizedCode),
            'order_status_returned' => $this->orderStatusReturnedContent($normalizedCode),
            'order_status_failed' => $this->orderStatusFailedContent($normalizedCode),
            default => $this->orderStatusProcessingContent($normalizedCode),
        };
    }
    private function orderStatusProcessingContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Sifariş statusu - Processing',
                'email_subject' => 'Sifarişiniz qəbul edildi',
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
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Sifarişiniz qəbul edildi</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Hörmətli {customer_name},
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            {order_number} nömrəli sifarişiniz yaradıldı və hazırda emal olunur.
                        </div>
                        <div style="padding:18px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:15px;line-height:26px;color:#1e3a8a;margin-bottom:24px;">
                            Sifariş nömrəsi: {order_number}<br>
                            Status: {order_status}<br>
                            Ümumi məbləğ: {order_total}<br>
                            Ödəniş statusu: {payment_status}<br>
                            Tarix: {placed_at}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            Sifarişinizlə bağlı növbəti yenilənmələri sizə e-poçt vasitəsilə göndərəcəyik.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Sifarişiniz yaradıldı. Sifariş nömrəsi: {order_number}. Status: {order_status}. Məbləğ: {order_total}.',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Статус заказа - Processing',
                'email_subject' => 'Ваш заказ принят',
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
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Ваш заказ принят</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Уважаемый(ая) {customer_name},
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Ваш заказ № {order_number} создан и сейчас обрабатывается.
                        </div>
                        <div style="padding:18px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:15px;line-height:26px;color:#1e3a8a;margin-bottom:24px;">
                            Номер заказа: {order_number}<br>
                            Статус: {order_status}<br>
                            Сумма: {order_total}<br>
                            Статус оплаты: {payment_status}<br>
                            Дата: {placed_at}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            Мы отправим вам следующие обновления по заказу по электронной почте.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Ваш заказ создан. Номер заказа: {order_number}. Статус: {order_status}. Сумма: {order_total}.',
            ],
            default => [
                'name' => 'Order status - Processing',
                'email_subject' => 'Your order has been placed',
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
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Your order has been placed</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Dear {customer_name},
                        </div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Your order #{order_number} has been created and is now being processed.
                        </div>
                        <div style="padding:18px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:15px;line-height:26px;color:#1e3a8a;margin-bottom:24px;">
                            Order number: {order_number}<br>
                            Status: {order_status}<br>
                            Total: {order_total}<br>
                            Payment status: {payment_status}<br>
                            Date: {placed_at}
                        </div>
                        <div style="font-size:14px;line-height:24px;color:#6b7280;">
                            We will send your next order updates by email.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Your order has been created. Order number: {order_number}. Status: {order_status}. Total: {order_total}.',
            ],
        };
    }

    private function orderStatusPreparingContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Sifariş statusu - Preparing',
                'email_subject' => 'Sifarişiniz hazırlanır',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#1d4ed8;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Sifarişiniz hazırlanır</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Hörmətli {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            {order_number} nömrəli sifarişiniz hazırda hazırlanır.
                        </div>
                        <div style="padding:18px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:15px;line-height:26px;color:#1e3a8a;margin-bottom:24px;">
                            Sifariş nömrəsi: {order_number}<br>
                            Status: {order_status}<br>
                            Ümumi məbləğ: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Sifarişiniz hazırlanır. Sifariş nömrəsi: {order_number}. Status: {order_status}.',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Статус заказа - Preparing',
                'email_subject' => 'Ваш заказ готовится',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#1d4ed8;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Ваш заказ готовится</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Уважаемый(ая) {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Ваш заказ № {order_number} сейчас готовится.
                        </div>
                        <div style="padding:18px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:15px;line-height:26px;color:#1e3a8a;margin-bottom:24px;">
                            Номер заказа: {order_number}<br>
                            Статус: {order_status}<br>
                            Сумма: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Ваш заказ готовится. Номер заказа: {order_number}. Статус: {order_status}.',
            ],
            default => [
                'name' => 'Order status - Preparing',
                'email_subject' => 'Your order is being prepared',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#1d4ed8;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                        <img src="{logo_dark}" alt="Logo" style="max-width:180px;max-height:56px;display:none;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#111827;margin-bottom:16px;">Your order is being prepared</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Dear {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Your order #{order_number} is currently being prepared.
                        </div>
                        <div style="padding:18px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:15px;line-height:26px;color:#1e3a8a;margin-bottom:24px;">
                            Order number: {order_number}<br>
                            Status: {order_status}<br>
                            Total: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Your order is being prepared. Order number: {order_number}. Status: {order_status}.',
            ],
        };
    }

    private function orderStatusCancelledContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Sifariş statusu - Cancelled',
                'email_subject' => 'Sifarişiniz ləğv edildi',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#fef2f2;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#dc2626;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#991b1b;margin-bottom:16px;">Sifarişiniz ləğv edildi</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Hörmətli {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            {order_number} nömrəli sifarişiniz ləğv edildi.
                        </div>
                        <div style="padding:18px 20px;background:#fef2f2;border:1px solid #fecaca;border-radius:14px;font-size:15px;line-height:26px;color:#991b1b;margin-bottom:24px;">
                            Sifariş nömrəsi: {order_number}<br>
                            Status: {order_status}<br>
                            Ümumi məbləğ: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Sifarişiniz ləğv edildi. Sifariş nömrəsi: {order_number}. Status: {order_status}.',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Статус заказа - Cancelled',
                'email_subject' => 'Ваш заказ отменён',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#fef2f2;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#dc2626;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#991b1b;margin-bottom:16px;">Ваш заказ отменён</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Уважаемый(ая) {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Ваш заказ № {order_number} был отменён.
                        </div>
                        <div style="padding:18px 20px;background:#fef2f2;border:1px solid #fecaca;border-radius:14px;font-size:15px;line-height:26px;color:#991b1b;margin-bottom:24px;">
                            Номер заказа: {order_number}<br>
                            Статус: {order_status}<br>
                            Сумма: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Ваш заказ отменён. Номер заказа: {order_number}. Статус: {order_status}.',
            ],
            default => [
                'name' => 'Order status - Cancelled',
                'email_subject' => 'Your order has been cancelled',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#fef2f2;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#dc2626;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#991b1b;margin-bottom:16px;">Your order has been cancelled</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Dear {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Your order #{order_number} has been cancelled.
                        </div>
                        <div style="padding:18px 20px;background:#fef2f2;border:1px solid #fecaca;border-radius:14px;font-size:15px;line-height:26px;color:#991b1b;margin-bottom:24px;">
                            Order number: {order_number}<br>
                            Status: {order_status}<br>
                            Total: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Your order has been cancelled. Order number: {order_number}. Status: {order_status}.',
            ],
        };
    }

    private function orderStatusRejectedContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Sifariş statusu - Rejected',
                'email_subject' => 'Sifarişiniz rədd edildi',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#fff7ed;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#ea580c;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#9a3412;margin-bottom:16px;">Sifarişiniz rədd edildi</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Hörmətli {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            {order_number} nömrəli sifarişiniz rədd edildi.
                        </div>
                        <div style="padding:18px 20px;background:#fff7ed;border:1px solid #fdba74;border-radius:14px;font-size:15px;line-height:26px;color:#9a3412;margin-bottom:24px;">
                            Sifariş nömrəsi: {order_number}<br>
                            Status: {order_status}<br>
                            Ümumi məbləğ: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Sifarişiniz rədd edildi. Sifariş nömrəsi: {order_number}. Status: {order_status}.',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Статус заказа - Rejected',
                'email_subject' => 'Ваш заказ отклонён',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#fff7ed;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#ea580c;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#9a3412;margin-bottom:16px;">Ваш заказ отклонён</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Уважаемый(ая) {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Ваш заказ № {order_number} был отклонён.
                        </div>
                        <div style="padding:18px 20px;background:#fff7ed;border:1px solid #fdba74;border-radius:14px;font-size:15px;line-height:26px;color:#9a3412;margin-bottom:24px;">
                            Номер заказа: {order_number}<br>
                            Статус: {order_status}<br>
                            Сумма: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Ваш заказ отклонён. Номер заказа: {order_number}. Статус: {order_status}.',
            ],
            default => [
                'name' => 'Order status - Rejected',
                'email_subject' => 'Your order has been rejected',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#fff7ed;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#ea580c;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#9a3412;margin-bottom:16px;">Your order has been rejected</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Dear {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Your order #{order_number} has been rejected.
                        </div>
                        <div style="padding:18px 20px;background:#fff7ed;border:1px solid #fdba74;border-radius:14px;font-size:15px;line-height:26px;color:#9a3412;margin-bottom:24px;">
                            Order number: {order_number}<br>
                            Status: {order_status}<br>
                            Total: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Your order has been rejected. Order number: {order_number}. Status: {order_status}.',
            ],
        };
    }

    private function orderStatusCompletedContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Sifariş statusu - Completed',
                'email_subject' => 'Sifarişiniz tamamlandı',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f0fdf4;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#16a34a;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#166534;margin-bottom:16px;">Sifarişiniz tamamlandı</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Hörmətli {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            {order_number} nömrəli sifarişiniz uğurla tamamlandı.
                        </div>
                        <div style="padding:18px 20px;background:#f0fdf4;border:1px solid #86efac;border-radius:14px;font-size:15px;line-height:26px;color:#166534;margin-bottom:24px;">
                            Sifariş nömrəsi: {order_number}<br>
                            Status: {order_status}<br>
                            Ümumi məbləğ: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Sifarişiniz tamamlandı. Sifariş nömrəsi: {order_number}. Status: {order_status}.',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Статус заказа - Completed',
                'email_subject' => 'Ваш заказ завершён',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f0fdf4;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#16a34a;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#166534;margin-bottom:16px;">Ваш заказ завершён</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Уважаемый(ая) {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Ваш заказ № {order_number} успешно завершён.
                        </div>
                        <div style="padding:18px 20px;background:#f0fdf4;border:1px solid #86efac;border-radius:14px;font-size:15px;line-height:26px;color:#166534;margin-bottom:24px;">
                            Номер заказа: {order_number}<br>
                            Статус: {order_status}<br>
                            Сумма: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Ваш заказ завершён. Номер заказа: {order_number}. Статус: {order_status}.',
            ],
            default => [
                'name' => 'Order status - Completed',
                'email_subject' => 'Your order has been completed',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f0fdf4;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#16a34a;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#166534;margin-bottom:16px;">Your order has been completed</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Dear {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Your order #{order_number} has been completed successfully.
                        </div>
                        <div style="padding:18px 20px;background:#f0fdf4;border:1px solid #86efac;border-radius:14px;font-size:15px;line-height:26px;color:#166534;margin-bottom:24px;">
                            Order number: {order_number}<br>
                            Status: {order_status}<br>
                            Total: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Your order has been completed. Order number: {order_number}. Status: {order_status}.',
            ],
        };
    }

    private function orderStatusDeliveredContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Sifariş statusu - Delivered',
                'email_subject' => 'Sifarişiniz çatdırıldı',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#ecfeff;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#0891b2;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#155e75;margin-bottom:16px;">Sifarişiniz çatdırıldı</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Hörmətli {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            {order_number} nömrəli sifarişiniz uğurla çatdırıldı.
                        </div>
                        <div style="padding:18px 20px;background:#ecfeff;border:1px solid #67e8f9;border-radius:14px;font-size:15px;line-height:26px;color:#155e75;margin-bottom:24px;">
                            Sifariş nömrəsi: {order_number}<br>
                            Status: {order_status}<br>
                            Ümumi məbləğ: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Sifarişiniz çatdırıldı. Sifariş nömrəsi: {order_number}. Status: {order_status}.',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Статус заказа - Delivered',
                'email_subject' => 'Ваш заказ доставлен',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#ecfeff;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#0891b2;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#155e75;margin-bottom:16px;">Ваш заказ доставлен</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Уважаемый(ая) {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Ваш заказ № {order_number} успешно доставлен.
                        </div>
                        <div style="padding:18px 20px;background:#ecfeff;border:1px solid #67e8f9;border-radius:14px;font-size:15px;line-height:26px;color:#155e75;margin-bottom:24px;">
                            Номер заказа: {order_number}<br>
                            Статус: {order_status}<br>
                            Сумма: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Ваш заказ доставлен. Номер заказа: {order_number}. Статус: {order_status}.',
            ],
            default => [
                'name' => 'Order status - Delivered',
                'email_subject' => 'Your order has been delivered',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#ecfeff;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#0891b2;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#155e75;margin-bottom:16px;">Your order has been delivered</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Dear {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Your order #{order_number} has been delivered successfully.
                        </div>
                        <div style="padding:18px 20px;background:#ecfeff;border:1px solid #67e8f9;border-radius:14px;font-size:15px;line-height:26px;color:#155e75;margin-bottom:24px;">
                            Order number: {order_number}<br>
                            Status: {order_status}<br>
                            Total: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Your order has been delivered. Order number: {order_number}. Status: {order_status}.',
            ],
        };
    }

    private function orderStatusReturnedContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Sifariş statusu - Returned',
                'email_subject' => 'Sifarişiniz geri qaytarıldı',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#475569;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#334155;margin-bottom:16px;">Sifarişiniz geri qaytarıldı</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Hörmətli {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            {order_number} nömrəli sifarişiniz geri qaytarıldı.
                        </div>
                        <div style="padding:18px 20px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:14px;font-size:15px;line-height:26px;color:#334155;margin-bottom:24px;">
                            Sifariş nömrəsi: {order_number}<br>
                            Status: {order_status}<br>
                            Ümumi məbləğ: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Sifarişiniz geri qaytarıldı. Sifariş nömrəsi: {order_number}. Status: {order_status}.',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Статус заказа - Returned',
                'email_subject' => 'Ваш заказ возвращён',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#475569;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#334155;margin-bottom:16px;">Ваш заказ возвращён</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Уважаемый(ая) {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Ваш заказ № {order_number} был возвращён.
                        </div>
                        <div style="padding:18px 20px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:14px;font-size:15px;line-height:26px;color:#334155;margin-bottom:24px;">
                            Номер заказа: {order_number}<br>
                            Статус: {order_status}<br>
                            Сумма: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Ваш заказ возвращён. Номер заказа: {order_number}. Статус: {order_status}.',
            ],
            default => [
                'name' => 'Order status - Returned',
                'email_subject' => 'Your order has been returned',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#475569;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#334155;margin-bottom:16px;">Your order has been returned</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Dear {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Your order #{order_number} has been returned.
                        </div>
                        <div style="padding:18px 20px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:14px;font-size:15px;line-height:26px;color:#334155;margin-bottom:24px;">
                            Order number: {order_number}<br>
                            Status: {order_status}<br>
                            Total: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Your order has been returned. Order number: {order_number}. Status: {order_status}.',
            ],
        };
    }

    private function orderStatusFailedContent(string $languageCode): array
    {
        return match ($languageCode) {
            'az', 'az-az', 'aze' => [
                'name' => 'Sifariş statusu - Failed',
                'email_subject' => 'Sifariş yenilənməsi uğursuz oldu',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#fff1f2;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#e11d48;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#9f1239;margin-bottom:16px;">Sifariş yenilənməsi uğursuz oldu</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Hörmətli {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            {order_number} nömrəli sifarişiniz üçün uğursuz status qeydə alındı.
                        </div>
                        <div style="padding:18px 20px;background:#fff1f2;border:1px solid #fda4af;border-radius:14px;font-size:15px;line-height:26px;color:#9f1239;margin-bottom:24px;">
                            Sifariş nömrəsi: {order_number}<br>
                            Status: {order_status}<br>
                            Ümumi məbləğ: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Sifarişiniz üçün uğursuz status qeydə alındı. Sifariş nömrəsi: {order_number}. Status: {order_status}.',
            ],
            'ru', 'ru-ru' => [
                'name' => 'Статус заказа - Failed',
                'email_subject' => 'Не удалось обновить заказ',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#fff1f2;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#e11d48;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#9f1239;margin-bottom:16px;">Не удалось обновить заказ</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Уважаемый(ая) {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            Для вашего заказа № {order_number} был зафиксирован неуспешный статус.
                        </div>
                        <div style="padding:18px 20px;background:#fff1f2;border:1px solid #fda4af;border-radius:14px;font-size:15px;line-height:26px;color:#9f1239;margin-bottom:24px;">
                            Номер заказа: {order_number}<br>
                            Статус: {order_status}<br>
                            Сумма: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'Для вашего заказа зафиксирован неуспешный статус. Номер заказа: {order_number}. Статус: {order_status}.',
            ],
            default => [
                'name' => 'Order status - Failed',
                'email_subject' => 'Your order update failed',
                'email_body' => <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;background-color:#fff1f2;font-family:Arial,Helvetica,sans-serif;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 32px 12px 32px;text-align:center;background:#e11d48;">
                        <img src="{logo_light}" alt="Logo" style="max-width:180px;max-height:56px;display:block;margin:0 auto 8px auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 20px 32px;">
                        <div style="font-size:28px;line-height:36px;font-weight:700;color:#9f1239;margin-bottom:16px;">Your order update failed</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">Dear {customer_name},</div>
                        <div style="font-size:16px;line-height:26px;color:#4b5563;margin-bottom:18px;">
                            A failed status has been recorded for your order #{order_number}.
                        </div>
                        <div style="padding:18px 20px;background:#fff1f2;border:1px solid #fda4af;border-radius:14px;font-size:15px;line-height:26px;color:#9f1239;margin-bottom:24px;">
                            Order number: {order_number}<br>
                            Status: {order_status}<br>
                            Total: {order_total}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
HTML,
                'simple_body' => 'A failed status has been recorded for your order. Order number: {order_number}. Status: {order_status}.',
            ],
        };
    }
}
