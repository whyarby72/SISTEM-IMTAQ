<?php

namespace Tests\Unit\Shared\Platform\Presentation;

use App\Shared\Platform\Presentation\UiLabel;
use PHPUnit\Framework\TestCase;

class UiLabelTest extends TestCase
{
    public function test_technical_sample_suffixes_are_hidden_from_ui(): void
    {
        $this->assertSame('Kelas 3A', UiLabel::clean('Kelas 3A (Pilot)'));
        $this->assertSame('Tahfizh', UiLabel::clean('Tahfizh Sample Pilot'));
        $this->assertSame('Tingkat 3', UiLabel::clean('Tingkat 3 Sample'));
    }

    public function test_normal_labels_are_unchanged(): void
    {
        $this->assertSame('Ust. Azhar', UiLabel::clean('Ust. Azhar'));
    }
}
