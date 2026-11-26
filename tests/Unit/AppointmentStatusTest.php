<?php

namespace Tests\Unit;

use App\Enums\AppointmentStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AppointmentStatusTest extends TestCase
{
    /**
     * @return iterable<string, array{AppointmentStatus, AppointmentStatus, bool}>
     */
    public static function transitions(): iterable
    {
        yield 'confirm a scheduled one' => [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed, true];
        yield 'attend a confirmed one' => [AppointmentStatus::Confirmed, AppointmentStatus::Attended, true];
        yield 'cancel a confirmed one' => [AppointmentStatus::Confirmed, AppointmentStatus::Cancelled, true];
        yield 'unconfirm' => [AppointmentStatus::Confirmed, AppointmentStatus::Scheduled, false];
        yield 'reopen a cancelled one' => [AppointmentStatus::Cancelled, AppointmentStatus::Scheduled, false];
        yield 'rewrite an outcome' => [AppointmentStatus::Attended, AppointmentStatus::NoShow, false];
    }

    #[DataProvider('transitions')]
    public function test_only_moves_forward(AppointmentStatus $from, AppointmentStatus $to, bool $allowed): void
    {
        $this->assertSame($allowed, in_array($to, $from->transitions(), true));
    }

    public function test_only_pending_appointments_hold_the_slot(): void
    {
        $holding = array_filter(AppointmentStatus::cases(), fn (AppointmentStatus $status) => $status->holdsSlot());

        $this->assertSame([AppointmentStatus::Scheduled, AppointmentStatus::Confirmed], array_values($holding));
        $this->assertSame(['programada', 'confirmada'], AppointmentStatus::slotValues());
    }
}
