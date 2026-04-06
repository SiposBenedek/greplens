<?php

namespace Tests\Unit;

use App\Models\Finding;
use PHPUnit\Framework\TestCase;

class FindingModelTest extends TestCase
{
    public function test_severity_color_returns_correct_classes(): void
    {
        $finding = new Finding();

        $finding->severity = 'ERROR';
        $this->assertEquals('danger', $finding->severityColor());

        $finding->severity = 'WARNING';
        $this->assertEquals('warning', $finding->severityColor());

        $finding->severity = 'INFO';
        $this->assertEquals('info', $finding->severityColor());

        $finding->severity = 'UNKNOWN';
        $this->assertEquals('secondary', $finding->severityColor());
    }

    public function test_status_icon_returns_correct_icons(): void
    {
        $finding = new Finding();

        $finding->status = Finding::STATUS_FLAGGED;
        $this->assertEquals('bi-flag-fill', $finding->statusIcon());

        $finding->status = Finding::STATUS_SUPPRESSED;
        $this->assertEquals('bi-eye-slash-fill', $finding->statusIcon());

        $finding->status = Finding::STATUS_UNREVIEWED;
        $this->assertEquals('bi-circle', $finding->statusIcon());
    }

    public function test_statuses_constant_contains_all_values(): void
    {
        $this->assertCount(3, Finding::STATUSES);
        $this->assertContains('unreviewed', Finding::STATUSES);
        $this->assertContains('flagged', Finding::STATUSES);
        $this->assertContains('suppressed', Finding::STATUSES);
    }
}
