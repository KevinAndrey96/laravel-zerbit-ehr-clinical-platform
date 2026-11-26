<?php

namespace App\Models;

use App\Enums\DeliveryChannel;
use App\Enums\LabStatus;
use App\Enums\SampleType;
use Carbon\CarbonImmutable;
use Database\Factories\LabSampleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $code
 * @property int $patient_id
 * @property int $registered_by
 * @property SampleType $sample_type
 * @property string $exam
 * @property string|null $authorization_number
 * @property CarbonImmutable $taken_on
 * @property LabStatus $status
 * @property bool $notify_by_email
 * @property string|null $result_path
 * @property CarbonImmutable|null $result_ready_at
 * @property CarbonImmutable|null $delivered_at
 * @property DeliveryChannel|null $delivery_channel
 * @property-read Patient $patient
 * @property-read User $registeredBy
 */
#[Fillable(['code', 'patient_id', 'registered_by', 'sample_type', 'exam', 'authorization_number', 'taken_on', 'status', 'notify_by_email'])]
class LabSample extends Model
{
    /** @use HasFactory<LabSampleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sample_type' => SampleType::class,
            'taken_on' => 'immutable_date',
            'status' => LabStatus::class,
            'notify_by_email' => 'boolean',
            'result_ready_at' => 'immutable_datetime',
            'delivered_at' => 'immutable_datetime',
            'delivery_channel' => DeliveryChannel::class,
        ];
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** @return BelongsTo<User, $this> */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function downloadName(): string
    {
        return 'resultado-'.$this->code.'.pdf';
    }
}
