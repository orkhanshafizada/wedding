<?php

namespace Modules\Notification\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Notification\Enums\NotificationAudienceType;
use Modules\Notification\Enums\NotificationCampaignStatus;
use Modules\Notification\Enums\NotificationMessageStatus;
use Modules\Notification\Http\Requests\Admin\StoreNotificationCampaignRequest;
use Modules\Notification\Jobs\ProcessNotificationCampaignJob;
use Modules\Notification\Models\NotificationCampaign;
use Modules\Notification\Models\NotificationTemplate;
use Modules\Notification\Services\NotificationCampaignService;

class NotificationCampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = NotificationCampaign::query()
            ->with(['template', 'creator', 'starter'])
            ->orderByDesc('id')
            ->get();

        $columns = [
            ['label' => __('Id'), 'width' => '90'],
            ['label' => __('Template'), 'width' => '180'],
            ['label' => __('Audience'), 'width' => '180'],
            ['label' => __('Channels'), 'width' => '140'],
            ['label' => __('Status'), 'width' => '120'],
            ['label' => __('Created by'), 'width' => '180'],
            ['label' => __('Started by'), 'width' => '180'],
            ['label' => __('Totals'), 'width' => '220'],
            ['label' => __('Created at'), 'width' => '150'],
            ['label' => __('Actions'), 'width' => '260'],
        ];

        $formattedRows = $campaigns->map(function (NotificationCampaign $campaign) {
            $channels = [];

            if ($campaign->channel_email) {
                $channels[] = __('Email');
            }

            if ($campaign->channel_sms) {
                $channels[] = __('SMS');
            }

            if ($campaign->channel_push) {
                $channels[] = __('Push');
            }

            $audienceLabel = NotificationAudienceType::tryFrom((string) $campaign->audience_type)?->label() ?? (string) $campaign->audience_type;
            $statusLabel = NotificationCampaignStatus::tryFrom((string) $campaign->status)?->label() ?? (string) $campaign->status;

            $statusBadge = match ((string) $campaign->status) {
                NotificationCampaignStatus::DRAFT->value => 'bg-secondary-subtle text-secondary',
                NotificationCampaignStatus::QUEUED->value => 'bg-warning-subtle text-warning',
                NotificationCampaignStatus::SENDING->value => 'bg-info-subtle text-info',
                NotificationCampaignStatus::COMPLETED->value => 'bg-success-subtle text-success',
                NotificationCampaignStatus::FAILED->value => 'bg-danger-subtle text-danger',
                default => 'bg-secondary-subtle text-secondary',
            };

            $totals = sprintf(
                '%s: %d | %s: %d | %s: %d',
                __('Sent'),
                (int) $campaign->total_sent,
                __('Failed'),
                (int) $campaign->total_failed,
                __('Skipped'),
                (int) $campaign->total_skipped
            );

            $actions = '<div class="d-flex gap-2 flex-wrap">';
            $actions .= '<a href="' . route('admin.notification.campaigns.show', $campaign) . '" class="btn btn-sm btn-soft-primary">';
            $actions .= '<i class="ri-eye-line align-bottom me-1"></i>' . e(__('View'));
            $actions .= '</a>';

            if ((string) $campaign->status === NotificationCampaignStatus::DRAFT->value) {
                $actions .= '<form method="POST" action="' . route('admin.notification.campaigns.start', $campaign) . '" class="js-campaign-start-form">';
                $actions .= csrf_field();
                $actions .= '<button type="submit" class="btn btn-sm btn-success js-campaign-start-btn">';
                $actions .= '<span class="js-default-label"><i class="ri-play-line align-bottom me-1"></i>' . e(__('Start')) . '</span>';
                $actions .= '<span class="js-loading-label d-none"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' . e(__('Starting...')) . '</span>';
                $actions .= '</button>';
                $actions .= '</form>';
            }

            $actions .= '</div>';

            return [
                'id' => $campaign->id,
                'cells' => [
                    e($campaign->template?->key ?? ''),
                    e($audienceLabel),
                    e(implode(', ', $channels)),
                    '<span class="badge ' . $statusBadge . '">' . e($statusLabel) . '</span>',
                    e($campaign->creator?->name ?? '-'),
                    e($campaign->starter?->name ?? '-'),
                    e($totals),
                    e($campaign->created_at?->format('d M Y H:i') ?? ''),
                    $actions,
                ],
            ];
        });

        return view('notification::admin.campaigns.index', compact('columns', 'formattedRows'));
    }

    public function create(): View
    {
        $templates = NotificationTemplate::query()
            ->with('translations')
            ->orderByDesc('id')
            ->get();

        $audiences = collect(NotificationAudienceType::cases())
            ->map(fn (NotificationAudienceType $audience) => [
                'value' => $audience->value,
                'label' => $audience->label(),
            ])
            ->values()
            ->all();

        return view('notification::admin.campaigns.create', compact('templates', 'audiences'));
    }

    public function store(StoreNotificationCampaignRequest $request, NotificationCampaignService $service): RedirectResponse
    {
        $data = $request->validated();

        $campaign = $service->createDraft([
            'notification_template_id' => (int) $data['notification_template_id'],
            'channels' => $data['channels'],
            'audience_type' => (string) $data['audience_type'],
            'filters' => $data['filters'] ?? [],
        ], auth()->id());

        return redirect()
            ->route('admin.notification.campaigns.show', $campaign)
            ->with('success', __('Campaign created successfully.'));
    }

    public function start(NotificationCampaign $campaign, NotificationCampaignService $service): RedirectResponse
    {
        $service->start($campaign, auth()->id());

        ProcessNotificationCampaignJob::dispatchAfterResponse($campaign->id);

        return redirect()
            ->route('admin.notification.campaigns.show', $campaign)
            ->with('success', __('Campaign started. Processing continues in background.'));
    }

    public function show(NotificationCampaign $campaign): View
    {
        $campaign->load(['template.translations', 'creator', 'starter']);

        $messages = $campaign->messages()
            ->orderByDesc('id')
            ->limit(5000)
            ->get();

        $columns = [
            ['label' => __('Id'), 'width' => '90'],
            ['label' => __('Channel'), 'width' => '90'],
            ['label' => __('Recipient'), 'width' => '160'],
            ['label' => __('To'), 'width' => '220'],
            ['label' => __('Status'), 'width' => '120'],
            ['label' => __('Attempts'), 'width' => '90'],
            ['label' => __('Error'), 'width' => '260'],
            ['label' => __('Sent/Failed at'), 'width' => '170'],
        ];

        $formattedRows = $messages->map(function ($message) {
            $status = NotificationMessageStatus::tryFrom((string) $message->status)?->label() ?? (string) $message->status;

            $badge = match ((string) $message->status) {
                NotificationMessageStatus::SENT->value => 'bg-success-subtle text-success',
                NotificationMessageStatus::FAILED->value => 'bg-danger-subtle text-danger',
                NotificationMessageStatus::SKIPPED->value => 'bg-secondary-subtle text-secondary',
                default => 'bg-warning-subtle text-warning',
            };

            $to = $message->to_email ?: ($message->to_phone ?: ($message->to_push_token ?: '-'));
            $error = $message->error_message ?: ($message->skipped_reason ?: '');
            $when = $message->sent_at
                ? $message->sent_at->format('d M Y H:i')
                : ($message->failed_at ? $message->failed_at->format('d M Y H:i') : '');

            return [
                'id' => $message->id,
                'cells' => [
                    e((string) $message->channel),
                    e(trim((string) $message->recipient_type) . '#' . (string) ($message->recipient_id ?? '')),
                    e((string) $to),
                    '<span class="badge ' . $badge . '">' . e($status) . '</span>',
                    (string) ((int) $message->attempts),
                    e(Str::limit((string) $error, 180)),
                    e((string) $when),
                ],
            ];
        });

        return view('notification::admin.campaigns.show', compact('campaign', 'columns', 'formattedRows'));
    }

    public function requeueFailed(NotificationCampaign $campaign, NotificationCampaignService $service): RedirectResponse
    {
        $service->requeueFailed($campaign);

        ProcessNotificationCampaignJob::dispatchAfterResponse($campaign->id);

        return redirect()
            ->route('admin.notification.campaigns.show', $campaign)
            ->with('success', __('Failed messages re-queued. Processing continues in background.'));
    }
}
