<?php

namespace Tests\Unit;

use App\Enums\ReadingPlanStatus;
use PHPUnit\Framework\TestCase;

class ReadingPlanStatusTest extends TestCase
{
    public function test_全ステータスの値が正しい(): void
    {
        $this->assertEquals('reading', ReadingPlanStatus::Reading->value);
        $this->assertEquals('completed', ReadingPlanStatus::Completed->value);
        $this->assertEquals('expired', ReadingPlanStatus::Expired->value);
    }

    public function test_labelメソッドが日本語を返す(): void
    {
        $this->assertEquals('進行中', ReadingPlanStatus::Reading->label());
        $this->assertEquals('完了', ReadingPlanStatus::Completed->label());
        $this->assertEquals('期限切れ', ReadingPlanStatus::Expired->label());
    }

    public function test_badgeClassメソッドがCSSクラスを返す(): void
    {
        $this->assertStringContainsString('blue', ReadingPlanStatus::Reading->badgeClass());
        $this->assertStringContainsString('green', ReadingPlanStatus::Completed->badgeClass());
        $this->assertStringContainsString('red', ReadingPlanStatus::Expired->badgeClass());
    }

    public function test_文字列からEnumにキャストできる(): void
    {
        $this->assertEquals(ReadingPlanStatus::Reading, ReadingPlanStatus::from('reading'));
        $this->assertEquals(ReadingPlanStatus::Completed, ReadingPlanStatus::from('completed'));
        $this->assertEquals(ReadingPlanStatus::Expired, ReadingPlanStatus::from('expired'));
    }
}
